<?php
// includes/db.php
require_once __DIR__ . '/../config.php';

$db_file = __DIR__ . '/../database.sqlite';
$needs_setup = !file_exists($db_file);

// Función para generar UUID V4 (Segura para evitar colisiones entre computadoras)
function generate_uuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // versión 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variante RFC 4122
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON;");

    if ($needs_setup) {
        $sql = "
        CREATE TABLE IF NOT EXISTS Nucleo (
            id TEXT PRIMARY KEY, Nombre TEXT NOT NULL, Segundo_Nombre TEXT, Primer_Apellido TEXT NOT NULL,
            Segundo_Apellido TEXT, Apellido_Casada TEXT, DPI TEXT NOT NULL UNIQUE, Fecha_Nacimiento TEXT NOT NULL,
            Sexo TEXT, Telefono TEXT, Persona_Beneficiaria_MC TEXT NOT NULL, Parentesco_MC TEXT, Afinidad_MC TEXT,
            Pertenencia_Cultural TEXT, Comunidad_Linguistica TEXT, creado_por TEXT,
            Desconoce_Fecha INTEGER DEFAULT 0, Edad INTEGER,
            sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS Familiares (
            id TEXT PRIMARY KEY, nucleo_id TEXT NOT NULL, Nombre TEXT NOT NULL, Nacionalidad TEXT,
            Parentesco TEXT, Afinidad TEXT, Estado_Civil TEXT, Fecha_Nacimiento TEXT, Sexo TEXT,
            Embarazada INTEGER, Atencion_Prenatal INTEGER, Tipo_Atencion_Prenatal TEXT,
            Tiene_Discapacidad INTEGER, Tipo_Discapacidad TEXT, Sabe_Leer INTEGER, Sabe_Escribir INTEGER,
            Escolaridad TEXT, Estudia_Actualmente INTEGER, Razon_Abandono TEXT, Actualmente_Labora INTEGER,
            Ingresos_Mensuales REAL, Viajan_Trabajo INTEGER, Tiempo_Viaje INTEGER, creado_por TEXT,
            Tiene_DPI INTEGER DEFAULT 0, Tiene_Certificado_Nacimiento INTEGER DEFAULT 0, Tiene_Pasaporte INTEGER DEFAULT 0,
            Desconoce_Fecha INTEGER DEFAULT 0, Edad INTEGER,
            sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS Enfermedades (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, categoria TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Familiar_Enfermedades (id TEXT PRIMARY KEY, familiar_id TEXT NOT NULL, enfermedad_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (enfermedad_id) REFERENCES Enfermedades(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Trabajos (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Familiar_Trabajos (id TEXT PRIMARY KEY, familiar_id TEXT NOT NULL, trabajo_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (trabajo_id) REFERENCES Trabajos(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Fuentes_Ingreso (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Familiar_Fuentes (id TEXT PRIMARY KEY, familiar_id TEXT NOT NULL, fuente_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (familiar_id) REFERENCES Familiares(id) ON DELETE CASCADE, FOREIGN KEY (fuente_id) REFERENCES Fuentes_Ingreso(id) ON DELETE CASCADE);

        CREATE TABLE IF NOT EXISTS Vivienda (
            id TEXT PRIMARY KEY, nucleo_id TEXT, Cantidad_Ambientes INTEGER, Techo TEXT, Paredes TEXT, Suelo TEXT, Tiene_Sanitario INTEGER,
            creado_por TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS Energia_Cocina (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Energia_Cocina (id TEXT PRIMARY KEY, vivienda_id TEXT, energia_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (energia_id) REFERENCES Energia_Cocina(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Alumbrado (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Alumbrado (id TEXT PRIMARY KEY, vivienda_id TEXT, alumbrado_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (alumbrado_id) REFERENCES Alumbrado(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Agua_Hogar (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Agua_Hogar (id TEXT PRIMARY KEY, vivienda_id TEXT, agua_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (agua_id) REFERENCES Agua_Hogar(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Agua_Consumo (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Agua_Consumo (id TEXT PRIMARY KEY, vivienda_id TEXT, agua_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (agua_id) REFERENCES Agua_Consumo(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Ubicacion_Animales (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Animales (id TEXT PRIMARY KEY, vivienda_id TEXT, ubicacion_id TEXT, conviven_hogar INTEGER DEFAULT 0, espacio_aislado INTEGER DEFAULT 0, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (ubicacion_id) REFERENCES Ubicacion_Animales(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Tipo_Sanitario (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Sanitario (id TEXT PRIMARY KEY, vivienda_id TEXT, sanitario_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (sanitario_id) REFERENCES Tipo_Sanitario(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Aguas_Grises (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Aguas_Grises (id TEXT PRIMARY KEY, vivienda_id TEXT, aguas_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (aguas_id) REFERENCES Aguas_Grises(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Manejo_Basura (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Vivienda_Basura (id TEXT PRIMARY KEY, vivienda_id TEXT, basura_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (vivienda_id) REFERENCES Vivienda(id) ON DELETE CASCADE, FOREIGN KEY (basura_id) REFERENCES Manejo_Basura(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Animales_Domesticos (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Nucleo_Animales_Domesticos (id TEXT PRIMARY KEY, nucleo_id TEXT, animal_id TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (animal_id) REFERENCES Animales_Domesticos(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Ganado (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Nucleo_Ganado (id TEXT PRIMARY KEY, nucleo_id TEXT, ganado_id TEXT, es_personal INTEGER DEFAULT 0, es_comercial INTEGER DEFAULT 0, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (ganado_id) REFERENCES Ganado(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Cultivos (id TEXT PRIMARY KEY, nombre TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Nucleo_Cultivos (id TEXT PRIMARY KEY, nucleo_id TEXT, cultivo_id TEXT, es_personal INTEGER DEFAULT 0, es_comercial INTEGER DEFAULT 0, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (cultivo_id) REFERENCES Cultivos(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Arboles (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Cultivo_Arboles (id TEXT PRIMARY KEY, nucleo_id TEXT NOT NULL, tiene_cultivo INTEGER DEFAULT 0, creado_por TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Cultivo_Arboles_Detalle (id TEXT PRIMARY KEY, cultivo_id TEXT NOT NULL, arbol_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (cultivo_id) REFERENCES Cultivo_Arboles(id) ON DELETE CASCADE, FOREIGN KEY (arbol_id) REFERENCES Arboles(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Actividades_Bosque (id TEXT PRIMARY KEY, nombre TEXT NOT NULL, descripcion TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE IF NOT EXISTS Nucleo_Actividades_Bosque (id TEXT PRIMARY KEY, nucleo_id TEXT NOT NULL, actividad_id TEXT NOT NULL, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE, FOREIGN KEY (actividad_id) REFERENCES Actividades_Bosque(id) ON DELETE CASCADE);
        CREATE TABLE IF NOT EXISTS Fallecidos (
            id TEXT PRIMARY KEY, nucleo_id TEXT, Nombre TEXT, Sexo TEXT, Parentesco TEXT, Fecha_Nacimiento TEXT, Fecha_Fallecimiento TEXT, Causa_Fallecimiento TEXT, Lugar_Inhumacion TEXT,
            Desconoce_Fecha INTEGER DEFAULT 0, Edad INTEGER,
            creado_por TEXT, sincronizado INTEGER DEFAULT 0, actualizado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (nucleo_id) REFERENCES Nucleo(id) ON DELETE CASCADE
        );
        ";
        $pdo->exec($sql);

        // Semillas (Catalogos con UUIDs fijos o generados)
        function seed($pdo, $table, $names, $extra = []) {
            $stmt = $pdo->prepare("INSERT INTO $table (id, nombre" . (empty($extra) ? "" : ", ".implode(", ", array_keys($extra))) . ") VALUES (?, ?" . (empty($extra) ? "" : ", ".str_repeat("?,", count($extra)-1)."?") . ")");
            foreach ($names as $name) {
                $params = [generate_uuid(), $name];
                foreach ($extra as $val) $params[] = $val;
                $stmt->execute($params);
            }
        }

        seed($pdo, 'Enfermedades', ['Gripe', 'Gastritis', 'Infección Urinaria', 'Infección Vaginal'], ['categoria' => 'COMUN']);
        seed($pdo, 'Enfermedades', ['Dengue', 'Sarampion', 'Viruela'], ['categoria' => 'VIRAL']);
        seed($pdo, 'Enfermedades', ['Diabetes', 'Hipertensión', 'Enfermedades Renales'], ['categoria' => 'CRONICA']);
        seed($pdo, 'Enfermedades', ['Enfermedades de transmisión sexual'], ['categoria' => 'TRANSMISION']);
        seed($pdo, 'Trabajos', ['Agricultura', 'Ganadería', 'Comercio', 'Albañilería', 'Docencia', 'Ama de casa']);
        seed($pdo, 'Fuentes_Ingreso', ['Salario Diario', 'Sueldo Mensual', 'Remesas', 'Venta de Cosecha', 'Negocio Propio']);
        seed($pdo, 'Energia_Cocina', ['Leña', 'Electricidad', 'Gas', 'Otro']);
        seed($pdo, 'Alumbrado', ['Panel Solar', 'Red Eléctrica', 'Candela', 'Planta Eléctrica', 'Otro']);
        seed($pdo, 'Agua_Hogar', ['Pipa/Compra', 'Río/Nacimiento', 'Pozo Bombeado', 'Pozo Artesanal', 'Captación Lluvia', 'Otro']);
        seed($pdo, 'Agua_Consumo', ['Purificada', 'Río/Nacimiento/Pozo', 'Otro']);
        seed($pdo, 'Ubicacion_Animales', ['Perros', 'Gatos', 'Gallinas', 'Patos', 'Cerdos', 'Caballos', 'Vacas', 'Otro']);
        seed($pdo, 'Tipo_Sanitario', ['Inodoro', 'Letrina ciega', 'Letrina lavable', 'Otro']);
        seed($pdo, 'Aguas_Grises', ['Desagüe al aire libre', 'Pozo de absorción', 'Río/Quebrada', 'Otro']);
        seed($pdo, 'Manejo_Basura', ['Servicio de recolección', 'Quema', 'Entierra', 'Tira a un barranco/río', 'Abono/Compostaje', 'Otro']);
        seed($pdo, 'Animales_Domesticos', ['Perros', 'Gatos', 'Loros', 'Conejos', 'Peces', 'Aves de corral menores', 'Otro']);
        seed($pdo, 'Ganado', ['Bovino (Vacas/Toros)', 'Porcino (Cerdos)', 'Equino (Caballos/Mulas)', 'Ovino (Ovejas)', 'Caprino (Cabras)', 'Avícola (Gallinas/Patos)', 'Otro']);
        seed($pdo, 'Cultivos', [' Maíz', 'Frijol', 'Pepitoria', 'Ajonjolí', 'Tomate', 'Café', 'Cardamomo', 'Hortalizas', 'Frutales', 'Otro']);
        seed($pdo, 'Arboles', ['Cedro', 'Caoba', 'Pino', 'Ciprés', 'Frutales (Naranja/Limón)', 'Aguacate', 'Otro']);
        seed($pdo, 'Actividades_Bosque', ['Recolección de Leña', 'Extracción de Madera', 'Caza', 'Crianza de animales silvestres', 'Recolección de frutos silvestres', 'Pesca en ríos/lagunas', 'Otro']);
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
