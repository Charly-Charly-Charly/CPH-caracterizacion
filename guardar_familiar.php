<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nucleo_id = $_POST['nucleo_id'] ?? null;
    
    if (!$nucleo_id) {
        die("Error: Se requiere el ID del núcleo.");
    }

    $boolean_fields = [
        'Sabe_Leer', 'Sabe_Escribir', 'Estudia_Actualmente', 'Actualmente_Labora', 'Viajan_Trabajo',
        'Tiene_DPI', 'Tiene_Certificado_Nacimiento', 'Tiene_Pasaporte', 'Desconoce_Fecha'
    ];

    $fields = [
        'Nombre', 'Nacionalidad', 'Parentesco', 'Afinidad', 'Estado_Civil', 
        'Fecha_Nacimiento', 'Sexo', 'Embarazada', 'Atencion_Prenatal', 
        'Tipo_Atencion_Prenatal', 'Tiene_Discapacidad', 'Tipo_Discapacidad', 
        'Escolaridad', 'Razon_Abandono', 'Ingresos_Mensuales', 'Tiempo_Viaje',
        'Donde_reside', 'Fuera_comunidad', 'Doble_Nacionalidad', 'Edad'
    ];

    $data = [];
    $data[':nucleo_id'] = $nucleo_id;

    foreach ($fields as $field) {
        $val = trim($_POST[$field] ?? '');
        $data[":$field"] = $val === '' ? null : $val;
    }

    foreach ($boolean_fields as $field) {
        $data[":$field"] = isset($_POST[$field]) ? 1 : 0;
    }

    $familiar_id = generate_uuid();
    $data[':id'] = $familiar_id;
    $data[':creado_por'] = PC_ID;

    $sql = "INSERT INTO Familiares (
        id, nucleo_id, Nombre, Nacionalidad, Parentesco, Afinidad, Estado_Civil,
        Fecha_Nacimiento, Sexo, Embarazada, Atencion_Prenatal, Tipo_Atencion_Prenatal,
        Tiene_Discapacidad, Tipo_Discapacidad, Sabe_Leer, Sabe_Escribir,
        Escolaridad, Estudia_Actualmente, Razon_Abandono, Actualmente_Labora, Doble_Nacionalidad,
        Tiene_DPI, Tiene_Certificado_Nacimiento, Tiene_Pasaporte,
        Ingresos_Mensuales, Viajan_Trabajo, Tiempo_Viaje, creado_por, Fuera_comunidad, Donde_reside,
        Desconoce_Fecha, Edad
    ) VALUES (
        :id, :nucleo_id, :Nombre, :Nacionalidad, :Parentesco, :Afinidad, :Estado_Civil,
        :Fecha_Nacimiento, :Sexo, :Embarazada, :Atencion_Prenatal, :Tipo_Atencion_Prenatal,
        :Tiene_Discapacidad, :Tipo_Discapacidad, :Sabe_Leer, :Sabe_Escribir,
        :Escolaridad, :Estudia_Actualmente, :Razon_Abandono, :Actualmente_Labora, :Doble_Nacionalidad,
        :Tiene_DPI, :Tiene_Certificado_Nacimiento, :Tiene_Pasaporte,
        :Ingresos_Mensuales, :Viajan_Trabajo, :Tiempo_Viaje, :creado_por, :Fuera_comunidad, :Donde_reside,
        :Desconoce_Fecha, :Edad
    )";

    try {
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } catch (PDOException $e) {
            // Self-healing: Si faltan las columnas de documentos, las agregamos
            if (strpos($e->getMessage(), 'no column named Tiene_DPI') !== false) {
                $pdo->rollBack();
                $pdo->exec("ALTER TABLE Familiares ADD COLUMN Tiene_DPI INTEGER DEFAULT 0");
                $pdo->exec("ALTER TABLE Familiares ADD COLUMN Tiene_Certificado_Nacimiento INTEGER DEFAULT 0");
                $pdo->exec("ALTER TABLE Familiares ADD COLUMN Tiene_Pasaporte INTEGER DEFAULT 0");
                
                // Re-iniciar transacción y re-intentar
                $pdo->beginTransaction();
                $stmt = $pdo->prepare($sql);
                $stmt->execute($data);
            } else {
                throw $e;
            }
        }

        // Enfermedades pivot
        if (!empty($_POST['Enfermedades']) && is_array($_POST['Enfermedades'])) {
            $stmtEnf = $pdo->prepare("INSERT INTO Familiar_Enfermedades (id, familiar_id, enfermedad_id) VALUES (?, ?, ?)");
            foreach ($_POST['Enfermedades'] as $enf_id) {
                $stmtEnf->execute([generate_uuid(), $familiar_id, $enf_id]);
            }
        }

        // Trabajos pivot
        if (!empty($_POST['Trabajos']) && is_array($_POST['Trabajos'])) {
            $stmtTrab = $pdo->prepare("INSERT INTO Familiar_Trabajos (id, familiar_id, trabajo_id) VALUES (?, ?, ?)");
            foreach ($_POST['Trabajos'] as $trab_id) {
                $stmtTrab->execute([generate_uuid(), $familiar_id, $trab_id]);
            }
        }

        // Fuentes pivot
        if (!empty($_POST['Fuentes']) && is_array($_POST['Fuentes'])) {
            $stmtFte = $pdo->prepare("INSERT INTO Familiar_Fuentes (id, familiar_id, fuente_id) VALUES (?, ?, ?)");
            foreach ($_POST['Fuentes'] as $fte_id) {
                $stmtFte->execute([generate_uuid(), $familiar_id, $fte_id]);
            }
        }

        $pdo->commit();

        header("Location: ver_nucleo.php?id=" . urlencode($nucleo_id));
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Error al guardar familiar: " . $e->getMessage());
    }
}
?>
