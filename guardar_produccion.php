<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nucleo_id = $_POST['nucleo_id'] ?? null;
    if (!$nucleo_id) {
        die("Error: Se requiere el ID del núcleo.");
    }

    try {
        $pdo->beginTransaction();

        // Limpiar pivots anteriores para actualizar en masa de forma segura
        $pdo->prepare("DELETE FROM Nucleo_Ganado WHERE nucleo_id = ?")->execute([$nucleo_id]);
        $pdo->prepare("DELETE FROM Nucleo_Cultivos WHERE nucleo_id = ?")->execute([$nucleo_id]);
        $pdo->prepare("DELETE FROM Nucleo_Actividades_Bosque WHERE nucleo_id = ?")->execute([$nucleo_id]);


        // Guardar Ganado
        if (!empty($_POST['Ganado']) && is_array($_POST['Ganado'])) {
            $stmtGan = $pdo->prepare("INSERT INTO Nucleo_Ganado (id, nucleo_id, ganado_id, es_personal, es_comercial) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['Ganado'] as $gan_id) {
                $per = isset($_POST["personal_gan_{$gan_id}"]) ? 1 : 0;
                $com = isset($_POST["comercial_gan_{$gan_id}"]) ? 1 : 0;
                $stmtGan->execute([generate_uuid(), $nucleo_id, $gan_id, $per, $com]);
            }
        }

        // Guardar Cultivos
        if (!empty($_POST['Cultivos']) && is_array($_POST['Cultivos'])) {
            $stmtCul = $pdo->prepare("INSERT INTO Nucleo_Cultivos (id, nucleo_id, cultivo_id, es_personal, es_comercial) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['Cultivos'] as $cul_id) {
                $per = isset($_POST["personal_{$cul_id}"]) ? 1 : 0;
                $com = isset($_POST["comercial_{$cul_id}"]) ? 1 : 0;
                $stmtCul->execute([generate_uuid(), $nucleo_id, $cul_id, $per, $com]);
            }
        }

        // --- MANEJO DE ÁRBOLES ---
        $arboles_seleccionados = (!empty($_POST['Arboles']) && is_array($_POST['Arboles'])) ? $_POST['Arboles'] : [];
        $tiene_arboles = !empty($arboles_seleccionados) ? 1 : 0;
        
        $stmtBaseArb = $pdo->prepare("SELECT id FROM Cultivo_Arboles WHERE nucleo_id = ?");
        $stmtBaseArb->execute([$nucleo_id]);
        $baseArb = $stmtBaseArb->fetch(PDO::FETCH_ASSOC);

        if ($baseArb) {
            $cultivo_id = $baseArb['id'];
            $pdo->prepare("UPDATE Cultivo_Arboles SET tiene_cultivo = ? WHERE id = ?")->execute([$tiene_arboles, $cultivo_id]);
        } else {
            $cultivo_id = generate_uuid();
            $pdo->prepare("INSERT INTO Cultivo_Arboles (id, nucleo_id, tiene_cultivo, creado_por) VALUES (?, ?, ?, ?)")->execute([$cultivo_id, $nucleo_id, $tiene_arboles, PC_ID]);
        }

        $pdo->prepare("DELETE FROM Cultivo_Arboles_Detalle WHERE cultivo_id = ?")->execute([$cultivo_id]);

        if ($tiene_arboles) {
            $stmtDetArb = $pdo->prepare("INSERT INTO Cultivo_Arboles_Detalle (id, cultivo_id, arbol_id) VALUES (?, ?, ?)");
            foreach ($arboles_seleccionados as $arb_id) {
                $stmtDetArb->execute([generate_uuid(), $cultivo_id, $arb_id]);
            }
        }

        // Guardar Actividades Bosque
        if (!empty($_POST['Actividades_Bosque']) && is_array($_POST['Actividades_Bosque'])) {
            $stmtActBos = $pdo->prepare("INSERT INTO Nucleo_Actividades_Bosque (id, nucleo_id, actividad_id) VALUES (?, ?, ?)");
            foreach ($_POST['Actividades_Bosque'] as $act_id) {
                $stmtActBos->execute([generate_uuid(), $nucleo_id, $act_id]);
            }
        }

        $pdo->commit();
        header("Location: ver_nucleo.php?id=" . urlencode($nucleo_id));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error al guardar producción: " . $e->getMessage());
    }
}
?>
