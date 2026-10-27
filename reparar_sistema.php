<?php
// reparar_sistema.php - Script maestro para corregir la estructura de la base de datos
require_once 'includes/db.php';

$tablas = [
    'Enfermedades', 'Trabajos', 'Fuentes_Ingreso', 'Energia_Cocina', 
    'Alumbrado', 'Agua_Hogar', 'Agua_Consumo', 'Ubicacion_Animales', 
    'Tipo_Sanitario', 'Aguas_Grises', 'Manejo_Basura', 
    'Animales_Domesticos', 'Ganado', 'Cultivos', 'Arboles', 'Actividades_Bosque',
    'Nucleo', 'Familiares', 'Vivienda', 'Fallecidos', 'Cultivo_Arboles',
    'Familiar_Enfermedades', 'Familiar_Trabajos', 'Familiar_Fuentes',
    'Vivienda_Energia_Cocina', 'Vivienda_Alumbrado', 'Vivienda_Agua_Hogar',
    'Vivienda_Agua_Consumo', 'Vivienda_Animales', 'Vivienda_Sanitario',
    'Vivienda_Aguas_Grises', 'Vivienda_Basura', 'Nucleo_Animales_Domesticos',
    'Nucleo_Ganado', 'Nucleo_Cultivos', 'Cultivo_Arboles_Detalle',
    'Nucleo_Actividades_Bosque'
];

echo "<h2>🔧 Reparador Maestro de Estructura (SQLite)</h2>";
echo "<p>Este script intentará añadir todas las columnas faltantes necesarias para la sincronización y nuevos campos.</p>";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_TIMEOUT, 10); // 10 segundos de espera por bloqueos

foreach ($tablas as $tabla) {
    echo "<strong>Analizando tabla: $tabla</strong><br>";
    
    // 1. Columnas generales de sincronización
    $cols_sync = [
        'sincronizado' => 'INTEGER DEFAULT 0',
        'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
    ];

    foreach ($cols_sync as $col => $def) {
        try {
            $check = $pdo->query("PRAGMA table_info($tabla)");
            $existe = false;
            while ($r = $check->fetch(PDO::FETCH_ASSOC)) {
                if ($r['name'] === $col) { $existe = true; break; }
            }
            if (!$existe) {
                $pdo->exec("ALTER TABLE $tabla ADD COLUMN $col $def");
                echo " -- ✅ Columna '$col' añadida.<br>";
            } else { echo " -- Column '$col' already exists.<br>"; }
        } catch (PDOException $e) {
            echo " -- ❌ Error en '$col': " . $e->getMessage() . "<br>";
        }
    }

    // 2. Campos específicos para Familiares
    if ($tabla === 'Familiares') {
        $cols_fam = [
            'Tiene_DPI' => 'INTEGER DEFAULT 0',
            'Tiene_Certificado_Nacimiento' => 'INTEGER DEFAULT 0',
            'Tiene_Pasaporte' => 'INTEGER DEFAULT 0',
            'Doble_Nacionalidad' => 'INTEGER DEFAULT 0',
            'Fuera_comunidad' => 'TEXT',
            'Donde_reside' => 'TEXT',
            'Desconoce_Fecha' => 'INTEGER DEFAULT 0',
            'Edad' => 'INTEGER'
        ];
        foreach ($cols_fam as $col => $def) {
            try {
                $check = $pdo->query("PRAGMA table_info(Familiares)");
                $existe = false;
                while ($r = $check->fetch(PDO::FETCH_ASSOC)) {
                    if ($r['name'] === $col) { $existe = true; break; }
                }
                if (!$existe) {
                    $pdo->exec("ALTER TABLE Familiares ADD COLUMN $col $def");
                    echo " -- ✅ Columna especial '$col' añadida.<br>";
                }
            } catch (PDOException $e) {
                echo " -- ❌ Error en campo especial '$col': " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // 3. Campos específicos para Nucleo
    if ($tabla === 'Nucleo') {
        $cols_nuc = [
            'Tercer_Nombre' => 'TEXT',
            'Desconoce_Fecha' => 'INTEGER DEFAULT 0',
            'Edad' => 'INTEGER'
        ];
        foreach ($cols_nuc as $col => $def) {
            try {
                $check = $pdo->query("PRAGMA table_info(Nucleo)");
                $existe = false;
                while ($r = $check->fetch(PDO::FETCH_ASSOC)) {
                    if ($r['name'] === $col) { $existe = true; break; }
                }
                if (!$existe) {
                    $pdo->exec("ALTER TABLE Nucleo ADD COLUMN $col $def");
                    echo " -- ✅ Columna especial '$col' añadida a Nucleo.<br>";
                }
            } catch (PDOException $e) { echo " -- ❌ Error en Nucleo campo '$col': " . $e->getMessage() . "<br>"; }
        }
    }

    // 4. Campos específicos para Ganado
    if ($tabla === 'Nucleo_Ganado') {
        $cols_gan = [
            'es_personal' => 'INTEGER DEFAULT 0',
            'es_comercial' => 'INTEGER DEFAULT 0'
        ];
        foreach ($cols_gan as $col => $def) {
            try {
                $check = $pdo->query("PRAGMA table_info(Nucleo_Ganado)");
                $existe = false;
                while ($r = $check->fetch(PDO::FETCH_ASSOC)) {
                    if ($r['name'] === $col) { $existe = true; break; }
                }
                if (!$existe) {
                    $pdo->exec("ALTER TABLE Nucleo_Ganado ADD COLUMN $col $def");
                    echo " -- ✅ Columna especial '$col' añadida a Nucleo_Ganado.<br>";
                }
            } catch (PDOException $e) { echo " -- ❌ Error en Nucleo_Ganado campo '$col': " . $e->getMessage() . "<br>"; }
        }
    }

    // 5. Campos específicos para Fallecidos
    if ($tabla === 'Fallecidos') {
        $cols_fal = [
            'Desconoce_Fecha' => 'INTEGER DEFAULT 0',
            'Edad' => 'INTEGER',
            'Parentesco' => 'TEXT'
        ];
        foreach ($cols_fal as $col => $def) {
            try {
                $check = $pdo->query("PRAGMA table_info(Fallecidos)");
                $existe = false;
                while ($r = $check->fetch(PDO::FETCH_ASSOC)) {
                    if ($r['name'] === $col) { $existe = true; break; }
                }
                if (!$existe) {
                    $pdo->exec("ALTER TABLE Fallecidos ADD COLUMN $col $def");
                    echo " -- ✅ Columna especial '$col' añadida a Fallecidos.<br>";
                }
            } catch (PDOException $e) { echo " -- ❌ Error en Fallecidos campo '$col': " . $e->getMessage() . "<br>"; }
        }
    }

    echo "<hr>";
}

echo "<h3>✅ Proceso de reparación finalizado.</h3>";
echo "<p><a href='sincronizar_nube.php' style='padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Ir a Sincronizar</a></p>";
?>
