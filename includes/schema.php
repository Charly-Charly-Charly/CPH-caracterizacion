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

// Esquema base MySQL: crea las tablas si todavía no existen en el servidor de
// la comisión. Usado tanto por preparar_nube.php (base ya configurada en
// .env) como por el panel de admin al crear una comisión nueva.
function crear_tablas_mysql($pdo_mysql, callable $log) {
    $sql = "
    SET FOREIGN_KEY_CHECKS = 0;

    CREATE TABLE IF NOT EXISTS Enfermedades (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), categoria VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Trabajos (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Fuentes_Ingreso (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Energia_Cocina (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Alumbrado (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Agua_Hogar (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Agua_Consumo (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Ubicacion_Animales (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Tipo_Sanitario (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Aguas_Grises (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Manejo_Basura (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Animales_Domesticos (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Ganado (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Cultivos (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Arboles (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS Actividades_Bosque (id VARCHAR(50) PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT, sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);

    CREATE TABLE IF NOT EXISTS Nucleo (
        id VARCHAR(50) PRIMARY KEY,
        correlativo INT AUTO_INCREMENT UNIQUE,
        Nombre VARCHAR(100), Segundo_Nombre VARCHAR(100), Tercer_Nombre VARCHAR(100),
        Primer_Apellido VARCHAR(100), Segundo_Apellido VARCHAR(100), Apellido_Casada VARCHAR(100),
        DPI VARCHAR(15) UNIQUE, Fecha_Nacimiento DATE, Sexo VARCHAR(20),
        Telefono VARCHAR(20), Persona_Beneficiaria_MC VARCHAR(100),
        Parentesco_MC VARCHAR(50), Afinidad_MC VARCHAR(50),
        Pertenencia_Cultural VARCHAR(50), Comunidad_Linguistica VARCHAR(50),
        creado_por VARCHAR(50),
        Desconoce_Fecha TINYINT DEFAULT 0, Edad INT,
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS Familiares (
        id VARCHAR(50) PRIMARY KEY,
        nucleo_id VARCHAR(50),
        Nombre VARCHAR(100), Nacionalidad VARCHAR(100), Parentesco VARCHAR(50),
        Afinidad VARCHAR(50), Estado_Civil VARCHAR(50), Fecha_Nacimiento DATE,
        Sexo VARCHAR(20), Embarazada TINYINT, Atencion_Prenatal TINYINT,
        Tipo_Atencion_Prenatal VARCHAR(50), Tiene_Discapacidad TINYINT,
        Tipo_Discapacidad VARCHAR(50), Sabe_Leer TINYINT, Sabe_Escribir TINYINT,
        Escolaridad VARCHAR(50), Estudia_Actualmente TINYINT, Razon_Abandono VARCHAR(100),
        Actualmente_Labora TINYINT, Ingresos_Mensuales DECIMAL(10,2),
        Viajan_Trabajo TINYINT, Tiempo_Viaje INT, creado_por VARCHAR(50),
        Doble_Nacionalidad TINYINT DEFAULT 0, Fuera_comunidad VARCHAR(100), Donde_reside VARCHAR(100),
        Tiene_DPI INT DEFAULT 0, Tiene_Certificado_Nacimiento INT DEFAULT 0, Tiene_Pasaporte INT DEFAULT 0,
        Desconoce_Fecha TINYINT DEFAULT 0, Edad INT,
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS Vivienda (
        id VARCHAR(50) PRIMARY KEY,
        nucleo_id VARCHAR(50), Cantidad_Ambientes INT,
        Techo VARCHAR(100), Paredes VARCHAR(100), Suelo VARCHAR(100),
        Tiene_Sanitario TINYINT, creado_por VARCHAR(50),
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS Fallecidos (
        id VARCHAR(50) PRIMARY KEY,
        nucleo_id VARCHAR(50), Nombre VARCHAR(255), Sexo VARCHAR(20), Parentesco VARCHAR(50),
        Fecha_Nacimiento DATE, Fecha_Fallecimiento DATE,
        Causa_Fallecimiento VARCHAR(255), Lugar_Inhumacion VARCHAR(255), creado_por VARCHAR(50),
        Desconoce_Fecha TINYINT DEFAULT 0, Edad INT,
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS Cultivo_Arboles (
        id VARCHAR(50) PRIMARY KEY,
        nucleo_id VARCHAR(50), tiene_cultivo TINYINT, creado_por VARCHAR(50),
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS Familiar_Enfermedades (id VARCHAR(50) PRIMARY KEY, familiar_id VARCHAR(50), enfermedad_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (enfermedad_id) REFERENCES Enfermedades(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Familiar_Trabajos (id VARCHAR(50) PRIMARY KEY, familiar_id VARCHAR(50), trabajo_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (trabajo_id) REFERENCES Trabajos(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Familiar_Fuentes (id VARCHAR(50) PRIMARY KEY, familiar_id VARCHAR(50), fuente_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (fuente_id) REFERENCES Fuentes_Ingreso(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Energia_Cocina (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), energia_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (energia_id) REFERENCES Energia_Cocina(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Alumbrado (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), alumbrado_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (alumbrado_id) REFERENCES Alumbrado(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Agua_Hogar (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), agua_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (agua_id) REFERENCES Agua_Hogar(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Agua_Consumo (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), agua_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (agua_id) REFERENCES Agua_Consumo(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Animales (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), ubicacion_id VARCHAR(50), conviven_hogar TINYINT, espacio_aislado TINYINT, sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (ubicacion_id) REFERENCES Ubicacion_Animales(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Sanitario (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), sanitario_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (sanitario_id) REFERENCES Tipo_Sanitario(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Aguas_Grises (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), aguas_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (aguas_id) REFERENCES Aguas_Grises(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Vivienda_Basura (id VARCHAR(50) PRIMARY KEY, vivienda_id VARCHAR(50), basura_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (basura_id) REFERENCES Manejo_Basura(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Nucleo_Animales_Domesticos (id VARCHAR(50) PRIMARY KEY, nucleo_id VARCHAR(50), animal_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (animal_id) REFERENCES Animales_Domesticos(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Nucleo_Ganado (id VARCHAR(50) PRIMARY KEY, nucleo_id VARCHAR(50), ganado_id VARCHAR(50), es_personal TINYINT DEFAULT 0, es_comercial TINYINT DEFAULT 0, sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (ganado_id) REFERENCES Ganado(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Nucleo_Cultivos (id VARCHAR(50) PRIMARY KEY, nucleo_id VARCHAR(50), cultivo_id VARCHAR(50), es_personal TINYINT, es_comercial TINYINT, sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (cultivo_id) REFERENCES Cultivos(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Cultivo_Arboles_Detalle (id VARCHAR(50) PRIMARY KEY, cultivo_id VARCHAR(50), arbol_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (cultivo_id) REFERENCES Cultivo_Arboles(id) ON DELETE CASCADE, FOREIGN KEY (arbol_id) REFERENCES Arboles(id) ON DELETE CASCADE);
    CREATE TABLE IF NOT EXISTS Nucleo_Actividades_Bosque (id VARCHAR(50) PRIMARY KEY, nucleo_id VARCHAR(50), actividad_id VARCHAR(50), sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (actividad_id) REFERENCES Actividades_Bosque(id) ON DELETE CASCADE);

    SET FOREIGN_KEY_CHECKS = 1;
    ";

    foreach (explode(";", $sql) as $consulta) {
        $consulta = trim($consulta);
        if ($consulta) {
            $pdo_mysql->exec($consulta);
        }
    }
    $log("✅ Tablas base verificadas/creadas.");
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
