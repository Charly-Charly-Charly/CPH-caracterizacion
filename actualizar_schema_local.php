<?php
// actualizar_schema_local.php - Migración de base de datos para añadir campos de sincronización

$files = glob(__DIR__ . '/*.sqlite');

foreach ($files as $db_file) {
    echo "Actualizando: " . basename($db_file) . "<br>\n";
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Obtener todas las tablas
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tablas as $tabla) {
            // Añadir sincronizado
            try {
                $pdo->exec("ALTER TABLE $tabla ADD COLUMN sincronizado INTEGER DEFAULT 0");
                echo " - [$tabla] Añadido 'sincronizado'<br>\n";
            } catch (PDOException $e) {
                // Posiblemente ya existe
            }
            // Añadir actualizado_el
            try {
                $pdo->exec("ALTER TABLE $tabla ADD COLUMN actualizado_el DATETIME");
                echo " - [$tabla] Añadido 'actualizado_el'<br>\n";
            } catch (PDOException $e) {
                // Posiblemente ya existe
            }
        }
        echo "✅ " . basename($db_file) . " actualizado correctamente.<br><br>\n";
    } catch (PDOException $e) {
        echo "❌ Error en " . basename($db_file) . ": " . $e->getMessage() . "<br>\n";
    }
}

echo "Migración completada.";
?>
