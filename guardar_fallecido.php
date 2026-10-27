<?php
require_once 'includes/db.php';
require_once 'includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nucleo_id = $_POST['nucleo_id'] ?? null;

    if (!$nucleo_id) {
        flash_error_con_datos('Se requiere el núcleo familiar para poder guardar el fallecido.', $_POST);
        header('Location: index.php');
        exit;
    }

    $fields = [
        'Nombre', 'Sexo', 'Parentesco', 'Fecha_Nacimiento', 'Fecha_Fallecimiento', 
        'Causa_Fallecimiento', 'Lugar_Inhumacion'
    ];

    $id = generate_uuid();
    $data = [':id' => $id, ':nucleo_id' => $nucleo_id, ':creado_por' => PC_ID];

    foreach ($fields as $field) {
        $val = trim($_POST[$field] ?? '');
        $data[":$field"] = $val === '' ? null : $val;
    }

    $data[':Desconoce_Fecha'] = isset($_POST['Desconoce_Fecha']) ? 1 : 0;
    $data[':Edad'] = trim($_POST['Edad'] ?? '') === '' ? null : $_POST['Edad'];

    $sql = "INSERT INTO Fallecidos (
        id, nucleo_id, Nombre, Sexo, Parentesco, Fecha_Nacimiento, 
        Fecha_Fallecimiento, Causa_Fallecimiento, Lugar_Inhumacion, creado_por,
        Desconoce_Fecha, Edad
    ) VALUES (
        :id, :nucleo_id, :Nombre, :Sexo, :Parentesco, :Fecha_Nacimiento, 
        :Fecha_Fallecimiento, :Causa_Fallecimiento, :Lugar_Inhumacion, :creado_por,
        :Desconoce_Fecha, :Edad
    )";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        
        header("Location: ver_nucleo.php?id=" . urlencode($nucleo_id));
        exit;
    } catch (PDOException $e) {
        flash_error_con_datos(mensaje_amigable($e), $_POST);
        header('Location: crear_fallecido.php?nucleo_id=' . urlencode($nucleo_id));
        exit;
    }
}
?>
