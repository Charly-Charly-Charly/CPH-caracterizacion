<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nucleo_id = $_POST['nucleo_id'] ?? null;
    
    if (!$nucleo_id) {
        die("Error: Se requiere el ID del núcleo.");
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
        die("Error al guardar fallecido: " . $e->getMessage());
    }
}
?>
