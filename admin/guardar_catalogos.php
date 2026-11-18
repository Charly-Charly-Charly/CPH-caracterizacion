<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';
require_once __DIR__ . '/includes/catalogos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$comision_id = $_POST['comision_id'] ?? '';
$stmt = $admin_pdo->prepare("SELECT id FROM Comisiones WHERE id = ?");
$stmt->execute([$comision_id]);
if (!$stmt->fetch()) {
    header('Location: index.php');
    exit;
}

$definiciones = catalogos_definiciones();
$catalogos = $_POST['catalogos'] ?? [];

// Restaurar valores por defecto de una sola tabla (botón individual): se trata
// como si esa casilla llegara vacía, y el resto del formulario se guarda igual.
$restaurando = isset($_POST['restaurar']) && isset($definiciones[$_POST['restaurar']]);
if ($restaurando) {
    $catalogos[$_POST['restaurar']] = '';
}
$delete = $admin_pdo->prepare("DELETE FROM Comision_Catalogos WHERE comision_id = ? AND tabla = ?");
$insert = $admin_pdo->prepare("INSERT INTO Comision_Catalogos (id, comision_id, tabla, nombre, categoria, orden) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($definiciones as $tabla => $def) {
    if (!array_key_exists($tabla, $catalogos)) {
        continue;
    }
    $lineas = preg_split('/\r\n|\r|\n/', $catalogos[$tabla]);
    $valores = [];
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '') {
            continue;
        }
        $categoria = null;
        $nombre = $linea;
        if ($def['categoria'] && str_contains($linea, '|')) {
            [$nombre, $categoria] = array_map('trim', explode('|', $linea, 2));
        }
        if ($nombre === '') {
            continue;
        }
        $valores[] = [$nombre, $categoria];
    }

    // Si el admin deja el campo vacío, se interpreta como "usar los valores por
    // defecto" (no se guarda nada personalizado), no como "catálogo sin opciones".
    $delete->execute([$comision_id, $tabla]);
    if (!empty($valores)) {
        foreach ($valores as $orden => [$nombre, $categoria]) {
            $insert->execute([admin_generar_uuid(), $comision_id, $tabla, $nombre, $categoria, $orden]);
        }
    }
}

header('Location: catalogos.php?id=' . urlencode($comision_id) . '&guardado=' . ($restaurando ? 'restaurado' : '1'));
exit;
