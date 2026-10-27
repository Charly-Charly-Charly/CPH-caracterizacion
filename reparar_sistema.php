<?php
// reparar_sistema.php - Aplica a la base SQLite local las columnas que falten
// según includes/schema.php (reemplaza a los antiguos scripts de reparación sueltos).
require_once 'includes/db.php';
require_once 'includes/schema.php';

echo "<h2>🔧 Reparador de Estructura (SQLite local)</h2>";
echo "<p>Revisando y agregando las columnas que falten en esta base de datos...</p>";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_TIMEOUT, 10);

reparar_schema_sqlite($pdo, function ($mensaje) {
    echo htmlspecialchars($mensaje) . "<br>\n";
});

echo "<h3>✅ Proceso de reparación finalizado.</h3>";
echo "<p><a href='sincronizar_nube.php' style='padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Ir a Sincronizar</a></p>";
?>
