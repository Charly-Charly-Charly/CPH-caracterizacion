<?php
// preparar_nube.php - Crea/actualiza el esquema en el servidor MySQL online (Hostinger/phpMyAdmin)
// Punto único de preparación de la nube (reemplaza a preparar_nube2.php, preparar_nube3.php
// y migrar_columnas_mysql.php, que duplicaban esta misma lógica).
require_once 'config.php';
require_once 'includes/schema.php';

echo "<h2>Preparando Base de Datos MySQL Online...</h2>";

try {
    $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_NAME . ";charset=utf8mb4";
    $pdo_mysql = new PDO($dsn, MYSQL_USER, MYSQL_PASS);
    $pdo_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p>✅ Conexión exitosa a " . htmlspecialchars(MYSQL_HOST) . "</p>";

    // 1. Esquema base: crea las tablas si todavía no existen en el servidor.
    crear_tablas_mysql($pdo_mysql, function ($mensaje) {
        echo "<p>" . htmlspecialchars($mensaje) . "</p>";
    });

    // 2. Columnas evolutivas (las que se agregaron después de la versión inicial del esquema).
    echo "<h3>Verificando columnas adicionales...</h3><ul>";
    reparar_schema_mysql($pdo_mysql, function ($mensaje) {
        echo "<li>" . htmlspecialchars($mensaje) . "</li>";
    });
    echo "</ul>";

    echo "<p>✅ Preparación de la nube finalizada.</p>";
    echo "<p><a href='sincronizar_nube.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ir al Sincronizador de Datos</a></p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
