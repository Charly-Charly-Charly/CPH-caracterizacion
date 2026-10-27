<?php
// preparar_nube.php - Configura el servidor MySQL online (Hostinger/phpMyAdmin)

require_once 'config.php';

echo "<h2>Preparando Base de Datos MySQL Online...</h2>";

try {
    $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_NAME . ";charset=utf8mb4";
    $pdo_mysql = new PDO($dsn, MYSQL_USER, MYSQL_PASS);
    $pdo_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p>✅ Conexión exitosa a " . MYSQL_HOST . "</p>";

        $sql = "
    -- Desactivar llaves foráneas para poder recrear tablas limpiamente
    SET FOREIGN_KEY_CHECKS = 0;

    -- CATALOGOS
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

    -- NUCLEO
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

    -- FAMILIARES
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

    -- VIVIENDA
    CREATE TABLE IF NOT EXISTS Vivienda (
        id VARCHAR(50) PRIMARY KEY,
        nucleo_id VARCHAR(50), Cantidad_Ambientes INT,
        Techo VARCHAR(100), Paredes VARCHAR(100), Suelo VARCHAR(100),
        Tiene_Sanitario TINYINT, creado_por VARCHAR(50),
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
    );

    -- FALLECIDOS
    CREATE TABLE IF NOT EXISTS Fallecidos (
        id VARCHAR(50) PRIMARY KEY,
        nucleo_id VARCHAR(50), Nombre VARCHAR(255), Sexo VARCHAR(20), Parentesco VARCHAR(50),
        Fecha_Nacimiento DATE, Fecha_Fallecimiento DATE,
        Causa_Fallecimiento VARCHAR(255), Lugar_Inhumacion VARCHAR(255), creado_por VARCHAR(50),
        Desconoce_Fecha TINYINT DEFAULT 0, Edad INT,
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
    );

    -- CULTIVO ARBOLES
    CREATE TABLE IF NOT EXISTS Cultivo_Arboles (
        id VARCHAR(50) PRIMARY KEY,
        nucleo_id VARCHAR(50), tiene_cultivo TINYINT, creado_por VARCHAR(50),
        sincronizado INT DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
    );

    -- PIVOTS (RELACIONES)
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

    -- MIGRACIONES DINÁMICAS (Añadir columnas si no existen en MySQL)
    -- Nota: Usamos bloques individuales más adelante en el PHP para mayor seguridad.
    ";

    $queries = explode(";", $sql);
    foreach ($queries as $q) {
        $q = trim($q);
        if ($q) {
            $pdo_mysql->exec($q);
        }
    }

    // Listado de columnas que podrían faltar en la nube si las tablas ya existían
    $migraciones = [
        'Nucleo' => [
            'Tercer_Nombre' => 'VARCHAR(100)',
            'Desconoce_Fecha' => 'TINYINT DEFAULT 0',
            'Edad' => 'INT',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'sincronizado' => 'INT DEFAULT 0'
        ],
        'Familiares' => [
            'Doble_Nacionalidad' => 'TINYINT DEFAULT 0',
            'Fuera_comunidad' => 'VARCHAR(100)',
            'Donde_reside' => 'VARCHAR(100)',
            'Tiene_DPI' => 'INT DEFAULT 0',
            'Tiene_Certificado_Nacimiento' => 'INT DEFAULT 0',
            'Tiene_Pasaporte' => 'INT DEFAULT 0',
            'Desconoce_Fecha' => 'TINYINT DEFAULT 0',
            'Edad' => 'INT',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'sincronizado' => 'INT DEFAULT 0'
        ],
        'Fallecidos' => [
            'Desconoce_Fecha' => 'TINYINT DEFAULT 0',
            'Edad' => 'INT',
            'Parentesco' => 'VARCHAR(50)',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'sincronizado' => 'INT DEFAULT 0'
        ],
        'Vivienda' => [
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'sincronizado' => 'INT DEFAULT 0'
        ],
        'Cultivo_Arboles' => [
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'sincronizado' => 'INT DEFAULT 0'
        ],
        'Nucleo_Ganado' => [
            'es_personal' => 'TINYINT DEFAULT 0',
            'es_comercial' => 'TINYINT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'sincronizado' => 'INT DEFAULT 0'
        ]
    ];

    // Añadir automáticamente actualizado_el y sincronizado a tablas de catálogo y pivote también
    $tablas_adicionales = [
        'Enfermedades', 'Trabajos', 'Fuentes_Ingreso', 'Energia_Cocina', 'Alumbrado', 
        'Agua_Hogar', 'Agua_Consumo', 'Ubicacion_Animales', 'Tipo_Sanitario', 
        'Aguas_Grises', 'Manejo_Basura', 'Animales_Domesticos', 'Ganado', 
        'Cultivos', 'Arboles', 'Actividades_Bosque',
        'Familiar_Enfermedades', 'Familiar_Trabajos', 'Familiar_Fuentes',
        'Vivienda_Energia_Cocina', 'Vivienda_Alumbrado', 'Vivienda_Agua_Hogar',
        'Vivienda_Agua_Consumo', 'Vivienda_Animales', 'Vivienda_Sanitario',
        'Vivienda_Aguas_Grises', 'Vivienda_Basura', 'Nucleo_Animales_Domesticos',
        'Nucleo_Cultivos', 'Cultivo_Arboles_Detalle', 'Nucleo_Actividades_Bosque'
    ];

    foreach ($tablas_adicionales as $t) {
        if (!isset($migraciones[$t])) {
            $migraciones[$t] = [
                'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
                'sincronizado' => 'INT DEFAULT 0'
            ];
        }
    }

    foreach ($migraciones as $tabla => $cols) {
        foreach ($cols as $col => $tipo) {
            try {
                $pdo_mysql->exec("ALTER TABLE $tabla ADD COLUMN $col $tipo");
                echo "<li>✅ Columna '$col' añadida a $tabla en la nube.</li>";
            } catch (PDOException $e) {
                // Si el error es 'Duplicate column name', lo ignoramos
                if ($e->getCode() == '42S21') {
                    // Ya existe, todo bien
                } else {
                    echo "<li>⚠️ Aviso en $tabla.$col: " . $e->getMessage() . "</li>";
                }
            }
        }
    }    

    echo "<p>✅ Tablas creadas con éxito.</p>";
    echo "<p><a href='sincronizar_nube.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ir al Sincronizador de Datos</a></p>";
}
catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
