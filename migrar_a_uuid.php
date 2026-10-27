<?php
// migrar_a_uuid.php - Script de emergencia para migrar datos de Enteros a UUIDs

require_once 'config.php';

$old_db = __DIR__ . '/database_backup_' . time() . '.sqlite';
$current_db = __DIR__ . '/database.sqlite';

if (!file_exists($current_db)) {
    die("No se encontró database.sqlite para migrar.\n");
}

echo "Iniciando migración...\n";

// 1. Respaldar base de datos actual
copy($current_db, $old_db);
echo "Respaldo creado en: $old_db\n";

// 2. Eliminar la base de datos actual para que db.php la recree con el nuevo esquema
unlink($current_db);
require_once 'includes/db.php'; // Esto crea la nueva DB con UUIDs y semillas

try {
    $pdo_old = new PDO("sqlite:" . $old_db);
    $pdo_new = $pdo; // Definido en includes/db.php

    // Mapeo de IDs viejos a nuevos UUIDS: [tabla][id_viejo] = id_nuevo
    $map = [];

    // Función para migrar una tabla simple (Catálogos) y llenar el mapa
    function migrar_catalogo($pdo_old, $pdo_new, $tabla, &$map) {
        echo "Migrando catálogo: $tabla...\n";
        $rows = $pdo_old->query("SELECT * FROM $tabla")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $old_id = $row['id'];
            // Buscamos si ya existe por nombre en la nueva (por las semillas)
            $stmt = $pdo_new->prepare("SELECT id FROM $tabla WHERE nombre = ?");
            $stmt->execute([$row['nombre']]);
            $new_id = $stmt->fetchColumn();

            if (!$new_id) {
                $new_id = generate_uuid();
                $cols = array_keys($row);
                unset($row['id']);
                $placeholders = str_repeat('?,', count($row)) . '?';
                $sql = "INSERT INTO $tabla (id, " . implode(', ', array_keys($row)) . ") VALUES ($placeholders)";
                $pdo_new->prepare($sql)->execute(array_merge([$new_id], array_values($row)));
            }
            $map[$tabla][$old_id] = $new_id;
        }
    }

    // Listado de catálogos
    $catalogos = [
        'Enfermedades', 'Trabajos', 'Fuentes_Ingreso', 'Energia_Cocina', 
        'Alumbrado', 'Agua_Hogar', 'Agua_Consumo', 'Ubicacion_Animales', 
        'Tipo_Sanitario', 'Aguas_Grises', 'Manejo_Basura', 
        'Animales_Domesticos', 'Ganado', 'Cultivos', 'Arboles', 'Actividades_Bosque'
    ];

    foreach ($catalogos as $cat) {
        migrar_catalogo($pdo_old, $pdo_new, $cat, $map);
    }

    // 3. Migrar Tablas Principales
    
    // NUCLEOS
    echo "Migrando Nucleos...\n";
    $nucleos = $pdo_old->query("SELECT * FROM Nucleo")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($nucleos as $n) {
        $old_id = $n['id'];
        $new_id = generate_uuid();
        unset($n['id']);
        $n['creado_por'] = PC_ID;
        $placeholders = rtrim(str_repeat('?,', count($n)), ',');
        $sql = "INSERT INTO Nucleo (id, " . implode(', ', array_keys($n)) . ") VALUES (?, $placeholders)";
        $pdo_new->prepare($sql)->execute(array_merge([$new_id], array_values($n)));
        $map['Nucleo'][$old_id] = $new_id;
    }

    // FAMILIARES
    echo "Migrando Familiares...\n";
    $familiares = $pdo_old->query("SELECT * FROM Familiares")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($familiares as $f) {
        $old_id = $f['id'];
        $new_id = generate_uuid();
        $f['nucleo_id'] = $map['Nucleo'][$f['nucleo_id']] ?? null;
        $f['creado_por'] = PC_ID;
        unset($f['id']);
        $placeholders = rtrim(str_repeat('?,', count($f)), ',');
        $sql = "INSERT INTO Familiares (id, " . implode(', ', array_keys($f)) . ") VALUES (?, $placeholders)";
        $pdo_new->prepare($sql)->execute(array_merge([$new_id], array_values($f)));
        $map['Familiares'][$old_id] = $new_id;
    }

    // VIVIENDA
    echo "Migrando Viviendas...\n";
    $viviendas = $pdo_old->query("SELECT * FROM Vivienda")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($viviendas as $v) {
        $old_id = $v['id'];
        $new_id = generate_uuid();
        $v['nucleo_id'] = $map['Nucleo'][$v['nucleo_id']] ?? null;
        $v['creado_por'] = PC_ID;
        unset($v['id']);
        $placeholders = rtrim(str_repeat('?,', count($v)), ',');
        $sql = "INSERT INTO Vivienda (id, " . implode(', ', array_keys($v)) . ") VALUES (?, $placeholders)";
        $pdo_new->prepare($sql)->execute(array_merge([$new_id], array_values($v)));
        $map['Vivienda'][$old_id] = $new_id;
    }

    // CULTIVO ARBOLES
    echo "Migrando Cultivo_Arboles...\n";
    if ($pdo_old->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='Cultivo_Arboles'")->fetchColumn() > 0) {
        $ca = $pdo_old->query("SELECT * FROM Cultivo_Arboles")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($ca as $row) {
            $old_id = $row['id'];
            $new_id = generate_uuid();
            $row['nucleo_id'] = $map['Nucleo'][$row['nucleo_id']] ?? null;
            $row['creado_por'] = PC_ID;
            unset($row['id']);
            $placeholders = rtrim(str_repeat('?,', count($row)), ',');
            $sql = "INSERT INTO Cultivo_Arboles (id, " . implode(', ', array_keys($row)) . ") VALUES (?, $placeholders)";
            $pdo_new->prepare($sql)->execute(array_merge([$new_id], array_values($row)));
            $map['Cultivo_Arboles'][$old_id] = $new_id;
        }
    }

    // 4. Migrar Tablas Pivot (Relaciones)
    // Lista de tablas pivot: [NombreTabla, FK1_col, FK1_table, FK2_col, FK2_table]
    $pivots = [
        ['Familiar_Enfermedades', 'familiar_id', 'Familiares', 'enfermedad_id', 'Enfermedades'],
        ['Familiar_Trabajos', 'familiar_id', 'Familiares', 'trabajo_id', 'Trabajos'],
        ['Familiar_Fuentes', 'familiar_id', 'Familiares', 'fuente_id', 'Fuentes_Ingreso'],
        ['Vivienda_Energia_Cocina', 'vivienda_id', 'Vivienda', 'energia_id', 'Energia_Cocina'],
        ['Vivienda_Alumbrado', 'vivienda_id', 'Vivienda', 'alumbrado_id', 'Alumbrado'],
        ['Vivienda_Agua_Hogar', 'vivienda_id', 'Vivienda', 'agua_id', 'Agua_Hogar'],
        ['Vivienda_Agua_Consumo', 'vivienda_id', 'Vivienda', 'agua_id', 'Agua_Consumo'],
        ['Vivienda_Animales', 'vivienda_id', 'Vivienda', 'ubicacion_id', 'Ubicacion_Animales'],
        ['Vivienda_Sanitario', 'vivienda_id', 'Vivienda', 'sanitario_id', 'Tipo_Sanitario'],
        ['Vivienda_Aguas_Grises', 'vivienda_id', 'Vivienda', 'aguas_id', 'Aguas_Grises'],
        ['Vivienda_Basura', 'vivienda_id', 'Vivienda', 'basura_id', 'Manejo_Basura'],
        ['Nucleo_Animales_Domesticos', 'nucleo_id', 'Nucleo', 'animal_id', 'Animales_Domesticos'],
        ['Nucleo_Ganado', 'nucleo_id', 'Nucleo', 'ganado_id', 'Ganado'],
        ['Nucleo_Cultivos', 'nucleo_id', 'Nucleo', 'cultivo_id', 'Cultivos'],
        ['Cultivo_Arboles_Detalle', 'cultivo_id', 'Cultivo_Arboles', 'arbol_id', 'Arboles'],
        ['Nucleo_Actividades_Bosque', 'nucleo_id', 'Nucleo', 'actividad_id', 'Actividades_Bosque']
    ];

    foreach ($pivots as $p) {
        $table = $p[0]; $fk1_col = $p[1]; $fk1_tab = $p[2]; $fk2_col = $p[3]; $fk2_tab = $p[4];
        echo "Migrando Pivot: $table...\n";
        try {
            $rows = $pdo_old->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $row[$fk1_col] = $map[$fk1_tab][$row[$fk1_col]] ?? null;
                $row[$fk2_col] = $map[$fk2_tab][$row[$fk2_col]] ?? null;
                unset($row['id']);
                $new_id = generate_uuid();
                $sql = "INSERT INTO $table (id, " . implode(', ', array_keys($row)) . ") VALUES (?, " . str_repeat('?,', count($row)-1) . "?)";
                $pdo_new->prepare($sql)->execute(array_merge([$new_id], array_values($row)));
            }
        } catch (Exception $e) { echo "Saltando $table (no existe o error)\n"; }
    }

    echo "Migración completada con éxito.\n";

} catch (PDOException $e) {
    die("Error en migración: " . $e->getMessage());
}
?>
