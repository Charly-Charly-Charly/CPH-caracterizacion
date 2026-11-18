<?php
// admin/includes/admin_db.php - Conexión a la base del panel de admin (separada del sistema de campo)

$admin_db_file = __DIR__ . '/../admin.sqlite';
$admin_needs_setup = !file_exists($admin_db_file);

try {
    $admin_pdo = new PDO("sqlite:" . $admin_db_file);
    $admin_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $admin_pdo->exec("PRAGMA foreign_keys = ON;");

    if ($admin_needs_setup) {
        $admin_pdo->exec("
            CREATE TABLE Comisiones (
                id TEXT PRIMARY KEY,
                nombre TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                mysql_host TEXT NOT NULL,
                mysql_user TEXT NOT NULL,
                mysql_pass TEXT NOT NULL,
                mysql_name TEXT NOT NULL,
                creado_el DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE Comision_PCs (
                id TEXT PRIMARY KEY,
                comision_id TEXT NOT NULL,
                nombre TEXT NOT NULL,
                pc_id TEXT NOT NULL UNIQUE,
                auth_user TEXT NOT NULL,
                auth_pass_hash TEXT NOT NULL,
                zip_generado_el DATETIME,
                creado_el DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (comision_id) REFERENCES Comisiones(id) ON DELETE CASCADE
            );
        ");
    }

    // CREATE TABLE IF NOT EXISTS (y no solo en $admin_needs_setup) para que una
    // base admin.sqlite ya existente también obtenga esta tabla al migrarse.
    $admin_pdo->exec("
        CREATE TABLE IF NOT EXISTS Comision_Catalogos (
            id TEXT PRIMARY KEY,
            comision_id TEXT NOT NULL,
            tabla TEXT NOT NULL,
            nombre TEXT NOT NULL,
            categoria TEXT,
            orden INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (comision_id) REFERENCES Comisiones(id) ON DELETE CASCADE
        );
    ");
} catch (PDOException $e) {
    die("No se pudo conectar a la base del panel de admin: " . $e->getMessage());
}

function admin_generar_uuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function admin_slug($texto) {
    $slug = mb_strtolower(trim($texto), 'UTF-8');
    $equivalencias = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
    ];
    $slug = strtr($slug, $equivalencias);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}
