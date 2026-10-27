<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nucleo_id = $_POST['nucleo_id'] ?? null;

    if (!$id || !$nucleo_id) {
        die("Error: Información insuficiente.");
    }

    $nombre = $_POST['Nombre'] ?? '';
    $sexo = $_POST['Sexo'] ?? '';
    $parentesco = $_POST['Parentesco'] ?? '';
    $desconoce_fecha = isset($_POST['Desconoce_Fecha']) ? 1 : 0;
    $fecha_nac = $_POST['Fecha_Nacimiento'] ?? null;
    $edad = $_POST['Edad'] ?? null;
    $fecha_fal = $_POST['Fecha_Fallecimiento'] ?? null;
    $causa = $_POST['Causa_Fallecimiento'] ?? '';
    $lugar = $_POST['Lugar_Inhumacion'] ?? '';

    if ($desconoce_fecha) {
        $fecha_nac = null;
    } else {
        $edad = null;
    }

    $sql = "UPDATE Fallecidos SET 
            Nombre = ?, Sexo = ?, Parentesco = ?, Desconoce_Fecha = ?, Fecha_Nacimiento = ?, Edad = ?, 
            Fecha_Fallecimiento = ?, Causa_Fallecimiento = ?, Lugar_Inhumacion = ?,
            sincronizado = 0, actualizado_el = CURRENT_TIMESTAMP
            WHERE id = ?";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre, $sexo, $parentesco, $desconoce_fecha, $fecha_nac, $edad, 
            $fecha_fal, $causa, $lugar,
            $id
        ]);

        header("Location: ver_nucleo.php?id=" . urlencode($nucleo_id));
        exit;
    } catch (PDOException $e) {
        die("Error al actualizar fallecido: " . $e->getMessage());
    }
}
