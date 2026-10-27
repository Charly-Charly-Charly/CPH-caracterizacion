<?php
require_once 'includes/db.php';
require_once 'includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nucleo_id = $_POST['nucleo_id'] ?? null;
    if (!$nucleo_id) {
        flash_error_con_datos('Se requiere el núcleo familiar para poder guardar la vivienda.', $_POST);
        header('Location: index.php');
        exit;
    }

    $fields = ['Cantidad_Ambientes', 'Techo', 'Paredes', 'Suelo'];
    $data = [':nucleo_id' => $nucleo_id];

    foreach ($fields as $field) {
        $val = trim($_POST[$field] ?? '');
        $data[":$field"] = $val === '' ? null : $val;
    }

    $data[':Tiene_Sanitario'] = isset($_POST['Tiene_Sanitario']) ? 1 : 0;

    $vivienda_id = generate_uuid();
    $data[':id'] = $vivienda_id;
    $data[':creado_por'] = PC_ID;

    $sql = "INSERT INTO Vivienda (
        id, nucleo_id, Cantidad_Ambientes, Techo, Paredes, Suelo, Tiene_Sanitario, creado_por
    ) VALUES (
        :id, :nucleo_id, :Cantidad_Ambientes, :Techo, :Paredes, :Suelo, :Tiene_Sanitario, :creado_por
    )";

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        // Helper function for standard pivots
        $insertPivot = function($postKey, $table, $col2) use ($pdo, $vivienda_id) {
            if (!empty($_POST[$postKey]) && is_array($_POST[$postKey])) {
                $stmt = $pdo->prepare("INSERT INTO {$table} (id, vivienda_id, {$col2}) VALUES (?, ?, ?)");
                foreach ($_POST[$postKey] as $val_id) {
                    $stmt->execute([generate_uuid(), $vivienda_id, $val_id]);
                }
            }
        };

        $insertPivot('Energia_Cocina', 'Vivienda_Energia_Cocina', 'energia_id');
        $insertPivot('Alumbrado', 'Vivienda_Alumbrado', 'alumbrado_id');
        $insertPivot('Agua_Hogar', 'Vivienda_Agua_Hogar', 'agua_id');
        $insertPivot('Agua_Consumo', 'Vivienda_Agua_Consumo', 'agua_id');
        $insertPivot('Tipo_Sanitario', 'Vivienda_Sanitario', 'sanitario_id');
        $insertPivot('Aguas_Grises', 'Vivienda_Aguas_Grises', 'aguas_id');
        $insertPivot('Manejo_Basura', 'Vivienda_Basura', 'basura_id');

        // Special pivot for Animales
        if (!empty($_POST['Animales']) && is_array($_POST['Animales'])) {
            $stmtAnim = $pdo->prepare("INSERT INTO Vivienda_Animales (id, vivienda_id, ubicacion_id, conviven_hogar, espacio_aislado) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['Animales'] as $anim_id) {
                $conviven = isset($_POST["conviven_{$anim_id}"]) ? 1 : 0;
                $aislado = isset($_POST["aislado_{$anim_id}"]) ? 1 : 0;
                $stmtAnim->execute([generate_uuid(), $vivienda_id, $anim_id, $conviven, $aislado]);
            }
        }

        $pdo->commit();
        header("Location: ver_nucleo.php?id=" . urlencode($nucleo_id));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        flash_error_con_datos(mensaje_amigable($e), $_POST);
        header('Location: crear_vivienda.php?nucleo_id=' . urlencode($nucleo_id));
        exit;
    }
}
?>
