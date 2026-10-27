<?php
require_once 'includes/db.php';
require_once 'includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'Nombre', 'Segundo_Nombre', 'Tercer_Nombre', 'Primer_Apellido', 'Segundo_Apellido', 'Apellido_Casada',
        'DPI', 'Fecha_Nacimiento', 'Sexo', 'Telefono',
        'Persona_Beneficiaria_MC', 'Parentesco_MC', 'Afinidad_MC',
        'Pertenencia_Cultural', 'Comunidad_Linguistica'
    ];

    foreach ($fields as $field) {
        $val = trim($_POST[$field] ?? '');
        $data[":$field"] = $val === '' ? null : $val;
    }

    $data[':Desconoce_Fecha'] = isset($_POST['Desconoce_Fecha']) ? 1 : 0;
    $data[':Edad'] = trim($_POST['Edad'] ?? '') === '' ? null : $_POST['Edad'];
    
    $id = generate_uuid();
    $data[':id'] = $id;
    $data[':creado_por'] = PC_ID;

    $sql = "INSERT INTO Nucleo (
        id, Nombre, Segundo_Nombre, Tercer_Nombre, Primer_Apellido, Segundo_Apellido, Apellido_Casada,
        DPI, Fecha_Nacimiento, Sexo, Telefono,
        Persona_Beneficiaria_MC, Parentesco_MC, Afinidad_MC,
        Pertenencia_Cultural, Comunidad_Linguistica, creado_por,
        Desconoce_Fecha, Edad
    ) VALUES (
        :id, :Nombre, :Segundo_Nombre, :Tercer_Nombre, :Primer_Apellido, :Segundo_Apellido, :Apellido_Casada,
        :DPI, :Fecha_Nacimiento, :Sexo, :Telefono,
        :Persona_Beneficiaria_MC, :Parentesco_MC, :Afinidad_MC,
        :Pertenencia_Cultural, :Comunidad_Linguistica, :creado_por,
        :Desconoce_Fecha, :Edad
    )";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        flash_error_con_datos(mensaje_amigable($e), $_POST);
        header('Location: crear_nucleo.php');
        exit;
    }
}
?>
