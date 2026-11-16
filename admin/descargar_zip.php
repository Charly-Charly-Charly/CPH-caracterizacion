<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';
require_once __DIR__ . '/includes/zip_builder.php';

$pc_id = $_GET['pc_id'] ?? '';
$stmt = $admin_pdo->prepare("SELECT * FROM Comision_PCs WHERE id = ?");
$stmt->execute([$pc_id]);
$pc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pc) {
    http_response_code(404);
    die('PC no encontrada.');
}

$stmt = $admin_pdo->prepare("SELECT * FROM Comisiones WHERE id = ?");
$stmt->execute([$pc['comision_id']]);
$comision = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comision) {
    http_response_code(404);
    die('Comisión no encontrada.');
}

$zip_temp = tempnam(sys_get_temp_dir(), 'copadeh_zip_');
if (!zip_construir_para_pc($comision, $pc, $zip_temp)) {
    die('No se pudo generar el ZIP.');
}

$admin_pdo->prepare("UPDATE Comision_PCs SET zip_generado_el = CURRENT_TIMESTAMP WHERE id = ?")->execute([$pc_id]);

$nombre_descarga = $pc['pc_id'] . '.zip';

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
header('Content-Length: ' . filesize($zip_temp));
readfile($zip_temp);
unlink($zip_temp);
exit;
