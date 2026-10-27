<?php
// includes/schema.php - Definición única de la estructura evolutiva de la base de datos.
//
// Sustituye a los antiguos reparar_sistema.php / force_fix_db.php / actualizar_schema_*.php /
// preparar_nube2.php / preparar_nube3.php / migrar_columnas_mysql.php, que repetían la misma
// lógica de "agregar columna si no existe" de formas distintas para SQLite y para MySQL.
//
// Cualquier columna nueva que se agregue al sistema en el futuro se declara UNA sola vez aquí,
// con su tipo para cada motor, y tanto reparar_sistema.php (SQLite) como preparar_nube.php
// (MySQL) la aplican automáticamente.

function tablas_del_sistema() {
    return [
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
        'Nucleo_Actividades_Bosque',
    ];
}

// Columnas de control que deben existir en TODAS las tablas del sistema.
function columnas_control() {
    return [
        'sincronizado'   => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'INT DEFAULT 0'],
        'actualizado_el' => ['sqlite' => 'DATETIME DEFAULT CURRENT_TIMESTAMP', 'mysql' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'],
    ];
}

// Columnas que se agregaron después de la creación inicial de ciertas tablas.
// tabla => columna => tipo por motor.
function columnas_evolutivas() {
    return [
        'Nucleo' => [
            'Tercer_Nombre'   => ['sqlite' => 'TEXT', 'mysql' => 'VARCHAR(100)'],
            'Desconoce_Fecha' => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'TINYINT DEFAULT 0'],
            'Edad'            => ['sqlite' => 'INTEGER', 'mysql' => 'INT'],
        ],
        'Familiares' => [
            'Tiene_DPI'                    => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'INT DEFAULT 0'],
            'Tiene_Certificado_Nacimiento' => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'INT DEFAULT 0'],
            'Tiene_Pasaporte'              => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'INT DEFAULT 0'],
            'Doble_Nacionalidad'           => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'TINYINT DEFAULT 0'],
            'Fuera_comunidad'              => ['sqlite' => 'TEXT', 'mysql' => 'VARCHAR(100)'],
            'Donde_reside'                 => ['sqlite' => 'TEXT', 'mysql' => 'VARCHAR(100)'],
            'Desconoce_Fecha'              => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'TINYINT DEFAULT 0'],
            'Edad'                         => ['sqlite' => 'INTEGER', 'mysql' => 'INT'],
        ],
        'Fallecidos' => [
            'Desconoce_Fecha' => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'TINYINT DEFAULT 0'],
            'Edad'            => ['sqlite' => 'INTEGER', 'mysql' => 'INT'],
            'Parentesco'      => ['sqlite' => 'TEXT', 'mysql' => 'VARCHAR(50)'],
        ],
        'Nucleo_Ganado' => [
            'es_personal'  => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'TINYINT DEFAULT 0'],
            'es_comercial' => ['sqlite' => 'INTEGER DEFAULT 0', 'mysql' => 'TINYINT DEFAULT 0'],
        ],
    ];
}

function columna_existe_sqlite($pdo, $tabla, $columna) {
    // SQLite no distingue mayúsculas/minúsculas en nombres de columna, así que la
    // comparación tampoco debe hacerlo (si no, ALTER TABLE termina en "duplicate column name").
    $stmt = $pdo->query("PRAGMA table_info(\"$tabla\")");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        if (strcasecmp($fila['name'], $columna) === 0) {
            return true;
        }
    }
    return false;
}

function agregar_columna_sqlite($pdo, $tabla, $columna, $tipo, callable $log) {
    try {
        if (columna_existe_sqlite($pdo, $tabla, $columna)) {
            $log("ℹ️ $tabla.$columna ya existe.");
            return;
        }
        $pdo->exec("ALTER TABLE \"$tabla\" ADD COLUMN \"$columna\" $tipo");
        $log("✅ $tabla.$columna agregada.");
    } catch (PDOException $e) {
        $log("❌ Error en $tabla.$columna: " . $e->getMessage());
    }
}

function agregar_columna_mysql($pdo_mysql, $tabla, $columna, $tipo, callable $log) {
    try {
        $pdo_mysql->exec("ALTER TABLE `$tabla` ADD COLUMN `$columna` $tipo");
        $log("✅ $tabla.$columna agregada.");
    } catch (PDOException $e) {
        if ($e->getCode() === '42S21') {
            $log("⏭️ $tabla.$columna ya existe.");
        } else {
            $log("❌ Error en $tabla.$columna: " . $e->getMessage());
        }
    }
}

// Aplica columnas de control + columnas evolutivas a la base SQLite local.
function reparar_schema_sqlite($pdo, callable $log) {
    foreach (tablas_del_sistema() as $tabla) {
        foreach (columnas_control() as $columna => $tipos) {
            agregar_columna_sqlite($pdo, $tabla, $columna, $tipos['sqlite'], $log);
        }
    }
    foreach (columnas_evolutivas() as $tabla => $columnas) {
        foreach ($columnas as $columna => $tipos) {
            agregar_columna_sqlite($pdo, $tabla, $columna, $tipos['sqlite'], $log);
        }
    }
}

// Aplica columnas de control + columnas evolutivas a la base MySQL de la nube.
function reparar_schema_mysql($pdo_mysql, callable $log) {
    foreach (tablas_del_sistema() as $tabla) {
        foreach (columnas_control() as $columna => $tipos) {
            agregar_columna_mysql($pdo_mysql, $tabla, $columna, $tipos['mysql'], $log);
        }
    }
    foreach (columnas_evolutivas() as $tabla => $columnas) {
        foreach ($columnas as $columna => $tipos) {
            agregar_columna_mysql($pdo_mysql, $tabla, $columna, $tipos['mysql'], $log);
        }
    }
}
