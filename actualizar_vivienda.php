<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nucleo_id = $_POST['nucleo_id'] ?? null;

    if (!$id || !$nucleo_id) {
        die("Error: Información insuficiente.");
    }

    $ambientes = $_POST['Cantidad_Ambientes'] ?? null;
    $techo = $_POST['Techo'] ?? '';
    $paredes = $_POST['Paredes'] ?? '';
    $suelo = $_POST['Suelo'] ?? '';
    $tiene_sanitario = isset($_POST['Tiene_Sanitario']) ? 1 : 0;

    try {
        $pdo->beginTransaction();

        // 1. Update main table
        $sql = "UPDATE Vivienda SET 
                Cantidad_Ambientes = ?, Techo = ?, Paredes = ?, Suelo = ?, Tiene_Sanitario = ?,
                sincronizado = 0, actualizado_el = CURRENT_TIMESTAMP
                WHERE id = ?";
        $pdo->prepare($sql)->execute([$ambientes, $techo, $paredes, $suelo, $tiene_sanitario, $id]);

        // Helper for standard pivots
        $syncPivot = function($postKey, $table, $col2) use ($pdo, $id) {
            $pdo->prepare("DELETE FROM {$table} WHERE vivienda_id = ?")->execute([$id]);
            if (!empty($_POST[$postKey]) && is_array($_POST[$postKey])) {
                $stmt = $pdo->prepare("INSERT INTO {$table} (id, vivienda_id, {$col2}) VALUES (?, ?, ?)");
                foreach ($_POST[$postKey] as $val_id) {
                    $stmt->execute([generate_uuid(), $id, $val_id]);
                }
            }
        };

        $syncPivot('Energia_Cocina', 'Vivienda_Energia_Cocina', 'energia_id');
        $syncPivot('Alumbrado', 'Vivienda_Alumbrado', 'alumbrado_id');
        $syncPivot('Agua_Hogar', 'Vivienda_Agua_Hogar', 'agua_id');
        $syncPivot('Agua_Consumo', 'Vivienda_Agua_Consumo', 'agua_id');
        $syncPivot('Tipo_Sanitario', 'Vivienda_Sanitario', 'sanitario_id');
        $syncPivot('Aguas_Grises', 'Vivienda_Aguas_Grises', 'aguas_id');
        $syncPivot('Manejo_Basura', 'Vivienda_Basura', 'basura_id');

        // Special pivot for Animales
        $pdo->prepare("DELETE FROM Vivienda_Animales WHERE vivienda_id = ?")->execute([$id]);
        if (!empty($_POST['Animales']) && is_array($_POST['Animales'])) {
            $stmtAnim = $pdo->prepare("INSERT INTO Vivienda_Animales (id, vivienda_id, ubicacion_id, conviven_hogar, espacio_aislado) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['Animales'] as $anim_id) {
                $conviven = isset($_POST["conviven_{$anim_id}"]) ? 1 : 0;
                $aislado = isset($_POST["aislado_{$anim_id}"]) ? 1 : 0;
                $stmtAnim->execute([generate_uuid(), $id, $anim_id, $conviven, $aislado]);
            }
        }

        $pdo->commit();
        header("Location: ver_nucleo.php?id=" . urlencode($nucleo_id));
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error al actualizar vivienda: " . $e->getMessage());
    }
}
