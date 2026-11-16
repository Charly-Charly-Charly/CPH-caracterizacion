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
        $pdo->exec(file_get_contents(__DIR__ . '/../seed.sql'));
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
