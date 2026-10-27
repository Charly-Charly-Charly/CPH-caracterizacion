<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nucleo_id = $_POST['nucleo_id'] ?? null;

    if (!$id || !$nucleo_id) {
        die("Error: Información insuficiente para actualizar.");
    }

    // Common fields
    $nombre = $_POST['Nombre'] ?? '';
    $nacionalidad = $_POST['Nacionalidad'] ?? '';
    $doble_nac = $_POST['Doble_Nacionalidad'] ?? 0;
    $sexo = $_POST['Sexo'] ?? '';
    $fecha_nac = $_POST['Fecha_Nacimiento'] ?? null;
    $desconoce_fecha = isset($_POST['Desconoce_Fecha']) ? 1 : 0;
    $edad = $_POST['Edad'] ?? null;
    $parentesco = $_POST['Parentesco'] ?? '';
    $afinidad = $_POST['Afinidad'] ?? '';
    $estado_civil = $_POST['Estado_Civil'] ?? '';
    $donde_reside = $_POST['Donde_reside'] ?? '';
    $fuera_comunidad = $_POST['Fuera_comunidad'] ?? '';

    $tiene_dpi = isset($_POST['Tiene_DPI']) ? 1 : 0;
    $tiene_cert = isset($_POST['Tiene_Certificado_Nacimiento']) ? 1 : 0;
    $tiene_pas = isset($_POST['Tiene_Pasaporte']) ? 1 : 0;

    $embarazada = $_POST['Embarazada'] ?? 0;
    $atencion_pre = $_POST['Atencion_Prenatal'] ?? 0;
    $tipo_atencion = $_POST['Tipo_Atencion_Prenatal'] ?? '';
    $tiene_disc = $_POST['Tiene_Discapacidad'] ?? 0;
    $tipo_disc = $_POST['Tipo_Discapacidad'] ?? '';

    $sabe_leer = isset($_POST['Sabe_Leer']) ? 1 : 0;
    $sabe_escribir = isset($_POST['Sabe_Escribir']) ? 1 : 0;
    $estudia = isset($_POST['Estudia_Actualmente']) ? 1 : 0;
    $escolaridad = $_POST['Escolaridad'] ?? '';
    $razon_abandono = $_POST['Razon_Abandono'] ?? '';

    $actualmente_labora = isset($_POST['Actualmente_Labora']) ? 1 : 0;
    $viajan_trabajo = isset($_POST['Viajan_Trabajo']) ? 1 : 0;
    $ingresos = $_POST['Ingresos_Mensuales'] ?? '';
    $tiempo_viaje = $_POST['Tiempo_Viaje'] ?? '';

    // If date is unknown, clear date field
    if ($desconoce_fecha) {
        $fecha_nac = null;
    } else {
        $edad = null;
    }

    try {
        $pdo->beginTransaction();

        // 1. Update primary table
        $sql = "UPDATE Familiares SET 
                Nombre = ?, Nacionalidad = ?, Doble_Nacionalidad = ?, Sexo = ?, 
                Fecha_Nacimiento = ?, Desconoce_Fecha = ?, Edad = ?, 
                Parentesco = ?, Afinidad = ?, Estado_Civil = ?, 
                Donde_reside = ?, Fuera_comunidad = ?, 
                Tiene_DPI = ?, Tiene_Certificado_Nacimiento = ?, Tiene_Pasaporte = ?,
                Embarazada = ?, Atencion_Prenatal = ?, Tipo_Atencion_Prenatal = ?, 
                Tiene_Discapacidad = ?, Tipo_Discapacidad = ?, 
                Sabe_Leer = ?, Sabe_Escribir = ?, Estudia_Actualmente = ?, 
                Escolaridad = ?, Razon_Abandono = ?, 
                Actualmente_Labora = ?, Viajan_Trabajo = ?, Ingresos_Mensuales = ?, Tiempo_Viaje = ?,
                sincronizado = 0, actualizado_el = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nombre, $nacionalidad, $doble_nac, $sexo, 
            $fecha_nac, $desconoce_fecha, $edad, 
            $parentesco, $afinidad, $estado_civil, 
            $donde_reside, $fuera_comunidad,
            $tiene_dpi, $tiene_cert, $tiene_pas,
            $embarazada, $atencion_pre, $tipo_atencion, 
            $tiene_disc, $tipo_disc,
            $sabe_leer, $sabe_escribir, $estudia, 
            $escolaridad, $razon_abandono,
            $actualmente_labora, $viajan_trabajo, $ingresos, $tiempo_viaje,
            $id
        ]);

        // 2. Sync Enfermedades
        $pdo->prepare("DELETE FROM Familiar_Enfermedades WHERE familiar_id = ?")->execute([$id]);
        if (!empty($_POST['Enfermedades'])) {
            $stmtEnf = $pdo->prepare("INSERT INTO Familiar_Enfermedades (id, familiar_id, enfermedad_id) VALUES (?, ?, ?)");
            foreach ($_POST['Enfermedades'] as $enf_id) {
                $stmtEnf->execute([generate_uuid(), $id, $enf_id]);
            }
        }

        // 3. Sync Trabajos
        $pdo->prepare("DELETE FROM Familiar_Trabajos WHERE familiar_id = ?")->execute([$id]);
        if (!empty($_POST['Trabajos'])) {
            $stmtTrab = $pdo->prepare("INSERT INTO Familiar_Trabajos (id, familiar_id, trabajo_id) VALUES (?, ?, ?)");
            foreach ($_POST['Trabajos'] as $trab_id) {
                $stmtTrab->execute([generate_uuid(), $id, $trab_id]);
            }
        }

        // 4. Sync Fuentes
        $pdo->prepare("DELETE FROM Familiar_Fuentes WHERE familiar_id = ?")->execute([$id]);
        if (!empty($_POST['Fuentes'])) {
            $stmtFte = $pdo->prepare("INSERT INTO Familiar_Fuentes (id, familiar_id, fuente_id) VALUES (?, ?, ?)");
            foreach ($_POST['Fuentes'] as $fte_id) {
                $stmtFte->execute([generate_uuid(), $id, $fte_id]);
            }
        }

        $pdo->commit();
        header("Location: ver_nucleo.php?id=" . urlencode($nucleo_id));
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error al actualizar familiar: " . $e->getMessage());
    }
}
