<?php
// actualizar_schema_documentos.php - Migración para añadir campos de documentos de identificación a Familiares

$files = glob(__DIR__ . '/*.sqlite');

foreach ($files as $db_file) {
    echo "Actualizando: " . basename($db_file) . "<br>\n";
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Añadir documentos a Familiares
        try {
            $pdo->exec("ALTER TABLE Familiares ADD COLUMN Tiene_DPI INTEGER DEFAULT 0");
            echo " - Añadido 'Tiene_DPI' a Familiares<br>\n";
        } catch (PDOException $e) { /* Posiblemente ya existe */ }

        try {
            $pdo->exec("ALTER TABLE Familiares ADD COLUMN Tiene_Certificado_Nacimiento INTEGER DEFAULT 0");
            echo " - Añadido 'Tiene_Certificado_Nacimiento' a Familiares<br>\n";
        } catch (PDOException $e) { /* Posiblemente ya existe */ }

        try {
            $pdo->exec("ALTER TABLE Familiares ADD COLUMN Tiene_Pasaporte INTEGER DEFAULT 0");
            echo " - Añadido 'Tiene_Pasaporte' a Familiares<br>\n";
        } catch (PDOException $e) { /* Posiblemente ya existe */ }

        echo "✅ " . basename($db_file) . " actualizado correctamente.<br><br>\n";
    } catch (PDOException $e) {
        echo "❌ Error en " . basename($db_file) . ": " . $e->getMessage() . "<br>\n";
    }
}

echo "Migración completada.";
?>
