<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db_path = __DIR__ . '/database.sqlite';
    if (!file_exists($db_path)) {
        die("Error: No se encontró el archivo database.sqlite en $db_path");
    }

    $db = new PDO("sqlite:" . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_TIMEOUT, 5); // Timeout de 5 segundos

    echo "<h3>Iniciando reparación de base de datos...</h3>";

    $columnas_a_agregar = [
        'Tiene_DPI' => 'INTEGER DEFAULT 0',
        'Tiene_Certificado_Nacimiento' => 'INTEGER DEFAULT 0',
        'Tiene_Pasaporte' => 'INTEGER DEFAULT 0'
    ];

    foreach ($columnas_a_agregar as $col => $tipo) {
        try {
            // Verificar si la columna ya existe
            $check = $db->query("PRAGMA table_info(Familiares)");
            $existe = false;
            while ($row = $check->fetch(PDO::FETCH_ASSOC)) {
                if ($row['name'] === $col) {
                    $existe = true;
                    break;
                }
            }

            if (!$existe) {
                $db->exec("ALTER TABLE Familiares ADD COLUMN $col $tipo");
                echo "✅ Columna '$col' añadida con éxito.<br>";
            } else {
                echo "ℹ️ La columna '$col' ya existe.<br>";
            }
        } catch (PDOException $e) {
            echo "❌ Error al agregar '$col': " . $e->getMessage() . "<br>";
            if (strpos($e->getMessage(), 'database is locked') !== false) {
                echo "<strong>⚠️ ADVERTENCIA: La base de datos está bloqueada por otro programa. Por favor cierra DB Browser u otros editores de SQLite.</strong><br>";
            }
        }
    }

    echo "<h4>Proceso finalizado.</h4>";
    echo "<p><a href='ver_nucleo.php'>Volver al inicio</a></p>";

} catch (Exception $e) {
    die("Error crítico: " . $e->getMessage());
}
?>
