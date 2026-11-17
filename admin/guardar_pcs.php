<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$comision_id = $_POST['comision_id'] ?? '';
$stmt = $admin_pdo->prepare("SELECT * FROM Comisiones WHERE id = ?");
$stmt->execute([$comision_id]);
$comision = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comision) {
    header('Location: index.php');
    exit;
}

$nombres = $_POST['nombre'] ?? [];
$usuarios = $_POST['usuario'] ?? [];
$passwords = $_POST['password'] ?? [];

$stmt_count = $admin_pdo->prepare("SELECT COUNT(*) FROM Comision_PCs WHERE comision_id = ?");
$stmt_count->execute([$comision_id]);
$siguiente_numero = (int)$stmt_count->fetchColumn() + 1;

$creadas = [];
$insert = $admin_pdo->prepare("INSERT INTO Comision_PCs (id, comision_id, nombre, pc_id, auth_user, auth_pass_hash) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($nombres as $i => $nombre) {
    $nombre = trim($nombre);
    $usuario = trim($usuarios[$i] ?? '');
    $password = trim($passwords[$i] ?? '');
    if ($nombre === '' || $usuario === '' || $password === '') {
        continue;
    }

    $pc_id = $comision['slug'] . '-pc-' . str_pad((string)$siguiente_numero, 2, '0', STR_PAD_LEFT);
    $siguiente_numero++;

    $insert->execute([
        admin_generar_uuid(),
        $comision_id,
        $nombre,
        $pc_id,
        $usuario,
        password_hash($password, PASSWORD_DEFAULT),
    ]);

    $creadas[] = ['nombre' => $nombre, 'pc_id' => $pc_id, 'auth_user' => $usuario, 'password' => $password];
}

$_SESSION['flash_credenciales'] = ['comision_id' => $comision_id, 'pcs' => $creadas];
header('Location: comision.php?id=' . urlencode($comision_id));
exit;
