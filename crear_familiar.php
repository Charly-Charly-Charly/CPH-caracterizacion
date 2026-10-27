<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$nucleo_id = $_GET['nucleo_id'] ?? null;
if (!$nucleo_id) {
    echo "<div class='alert alert-danger'>ID de Núcleo Familiar no proporcionado.</div>";
    require_once 'includes/footer.php';
    exit;
}

// Fetch catalogs
$enfermedades = $pdo->query("SELECT * FROM Enfermedades ORDER BY rowid")->fetchAll(PDO::FETCH_ASSOC);
$trabajos = $pdo->query("SELECT * FROM Trabajos ORDER BY rowid")->fetchAll(PDO::FETCH_ASSOC);
$fuentes = $pdo->query("SELECT * FROM Fuentes_Ingreso ORDER BY rowid")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Agregar Familiar a Núcleo #<?= htmlspecialchars($nucleo_id) ?></h1>
    
    <a href="ver_nucleo.php?id=<?= htmlspecialchars($nucleo_id) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Núcleo
    </a>
</div>
<div class="alert alert-info" role="alert">
    <h4>Instrucciones:</h4>
    <p class="m-0">•Por favor, complete el formulario con la información de cada persona que integra el núcleo familiar.</p>

    <p class="m-0">•Incluir a la persona entrevistada</p>
</div>

<div class="card shadow-sm border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Datos del Familiar</h5>
    </div>
    <div class="card-body p-4">
        <form action="guardar_familiar.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="nucleo_id" value="<?= htmlspecialchars($nucleo_id) ?>">
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" name="Nombre" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nacionalidad</label>
                    <select name="Nacionalidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Guatemalteco">Guatemalteco</option>
                        <option value="Mexicano">Mexicano</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tiene Doble Nacionalidad</label>
                    <select name="Doble_Nacionalidad" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sexo</label>
                    <select name="Sexo" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="Fecha_Nacimiento" id="Fecha_Nacimiento" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="Desconoce_Fecha" id="Desconoce_Fecha" value="1">
                        <label class="form-check-label small" for="Desconoce_Fecha">Desconoce fecha</label>
                    </div>
                </div>
                <div class="col-md-2" id="div_edad">
                    <label class="form-label text-nowrap">Edad</label>
                    <input type="number" name="Edad" id="Edad" class="form-control" min="0" max="120" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parentesco</label>
                    <select name="Parentesco" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Entrevistado">Entrevistado</option>
                        <option value="Madre">Madre</option>
                        <option value="Padre">Padre</option>
                        <option value="Hijo/a">Hijo/a</option>
                        <option value="Hermano/a">Hermano/a</option>
                        <option value="Abuelo/a">Abuelo/a</option>
                        <option value="Sobrino/a">Sobrino/a</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Afinidad</label>
                    <select name="Afinidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Esposa/o">Esposa/o</option>
                        <option value="Suegro/a">Suegro/a</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado Civil</label>
                    <select name="Estado_Civil" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Soltero/a">Soltero/a</option>
                        <option value="Casado/a">Casado/a</option>
                        <option value="Unido/a">Unido/a</option>
                        <option value="Viudo/a">Viudo/a</option>
                    </select>
                </div>
                    <div class="col-md-3">
                    <label class="form-label">Donde Reside Actualmente</label>
                    <select name="Donde_reside" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Comunidad">Comunidad</option>
                        <option value="Petén">Petén</option>
                        <option value="México">México</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tiempo Fuera de la Comunidad</label>
                    <select name="Fuera_comunidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Menos de 6 meses">Menos de 6 meses</option>
                        <option value="6 meses a 1 año">6 meses a 1 año</option>
                        <option value="1 año a 5 años">1 año</option>
                        <option value="Más de 5 años">Más de un año</option>
                    </select>
                </div>
                
            </div>

            <h5 class="text-success mt-4 mb-3 pb-2 border-bottom">Documentos de Identificación</h5>
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Tiene_DPI" id="Tiene_DPI" value="1">
                        <label class="form-check-label fs-6" for="Tiene_DPI">Tiene DPI</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Tiene_Certificado_Nacimiento" id="Tiene_Certificado_Nacimiento" value="1">
                        <label class="form-check-label fs-6" for="Tiene_Certificado_Nacimiento">Tiene Certificado de Nacimiento</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Tiene_Pasaporte" id="Tiene_Pasaporte" value="1">
                        <label class="form-check-label fs-6" for="Tiene_Pasaporte">Tiene Pasaporte</label>
                    </div>
                </div>
            </div>

            <h5 class="text-success mt-4 mb-3 pb-2 border-bottom">Salud</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Embarazada</label>
                    <select name="Embarazada" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                        <option value="2">No Aplica</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Atención Prenatal</label>
                    <select name="Atencion_Prenatal" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                        <option value="2">No Aplica</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Donde Recibe Atención Prenatal</label>
                    <select name="Tipo_Atencion_Prenatal" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Hospital">Hospital</option>
                        <option value="Centro de Salud">Centro de Salud</option>
                        <option value="Comadrona">Comadrona</option>
                        
                    </select>
                </div>
                
                <div class="col-md-6 mt-3">
                    <label class="form-label">Tiene Discapacidad</label>
                    <select name="Tiene_Discapacidad" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Tipo de Discapacidad</label>
                    <select name="Tipo_Discapacidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Física">Física</option>
                        <option value="Sensorial">Auditiva</option>
                        <option value="Visual">Visual</option>
                        <option value="Intelectual">Psicosocial/Intelectual</option>
                        <option value="Mental">Ninguna</option>
                        <option value="OTRO">OTRO</option>
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Enfermedades Padecidas</h6>
                            <div class="row g-2">
                                <?php foreach($enfermedades as $enf): ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="Enfermedades[]" value="<?= $enf['id'] ?>" id="enf_<?= $enf['id'] ?>">
                                        <label class="form-check-label" for="enf_<?= $enf['id'] ?>">
                                            <?= htmlspecialchars($enf['nombre']) ?> 
                                            <small class="text-muted">(<?= $enf['categoria'] ?>)</small>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="text-success mt-4 mb-3 pb-2 border-bottom">Educación</h5>
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Sabe_Leer" id="Sabe_Leer" value="1">
                        <label class="form-check-label fs-6" for="Sabe_Leer">Sabe Leer</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Sabe_Escribir" id="Sabe_Escribir" value="1">
                        <label class="form-check-label fs-6" for="Sabe_Escribir">Sabe Escribir</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Estudia_Actualmente" id="Estudia_Actualmente" value="1">
                        <label class="form-check-label fs-6" for="Estudia_Actualmente">Estudia Actualmente</label>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Escolaridad alcanzada</label>
                    <select name="Escolaridad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Sin estudios">Sin estudios</option>
                        <option value="Preprimaria">Preprimaria</option>
                        <option value="Primaria">Primaria</option>
                        <option value="Basicos">Basicos</option>
                        <option value="Diversificado">Diversificado</option>
                        <option value="Universitario">Universitario</option>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Razón abandono de estudio</label>
                    <select name="Razon_Abandono" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Trabajo">Trabajo</option>
                        <option value="Decisión Propia">Decisión Propia</option>
                        <option value="Migración">Migración</option>
                        <option value="Economico">Economico</option>
                        <option value="Salud">Salud</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            <h5 class="text-success mt-4 mb-3 pb-2 border-bottom">Laboral e Ingresos</h5>
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-6">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Actualmente_Labora" id="Actualmente_Labora" value="1">
                        <label class="form-check-label fs-6" for="Actualmente_Labora">Actualmente labora</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Viajan_Trabajo" id="Viajan_Trabajo" value="1">
                        <label class="form-check-label fs-6" for="Viajan_Trabajo">Se desplaza por trabajo</label>
                    </div>
                </div>

                <div class="col-md-6 mt-3">
                    <label class="form-label">Ingresos Diarios Estimados (Q)</label>
                    <select name="Ingresos_Mensuales" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="0-50">Q0-Q50</option>
                        <option value="50-100">Q50-Q100</option>
                        <option value="100-150">Q100-Q150</option>
                        <option value="150-200">Q150-Q200</option>
                        <option value="200+">Q200+</option>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Tiempo de desplazamiento</label>
                    <select name="Tiempo_Viaje" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="menos de una semana">menos de una semana</option>
                        <option value="1 Semana">1 Semana</option>
                        <option value="2 Semanas">2 Semanas</option>
                        <option value="3 Semanas">3 Semanas</option>
                        <option value="4 Semanas">4 Semanas</option>
                        <option value="más de un mes">más de un mes</option>
                    </select>
                </div>
                
                <!-- Pivot selects -->
                <div class="col-md-6 mt-4">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Trabajos / Ocupaciones</h6>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach($trabajos as $trab): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Trabajos[]" value="<?= $trab['id'] ?>" id="trab_<?= $trab['id'] ?>">
                                    <label class="form-check-label" for="trab_<?= $trab['id'] ?>">
                                        <?= htmlspecialchars($trab['nombre']) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-4">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Otras Fuentes de Ingreso</h6>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach($fuentes as $fte): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Fuentes[]" value="<?= $fte['id'] ?>" id="fte_<?= $fte['id'] ?>">
                                    <label class="form-check-label" for="fte_<?= $fte['id'] ?>">
                                        <?= htmlspecialchars($fte['nombre']) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-save"></i> Guardar Familiar
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
document.getElementById('Desconoce_Fecha').addEventListener('change', function() {
    const dateInput = document.getElementById('Fecha_Nacimiento');
    const edadInput = document.getElementById('Edad');
    
    if (this.checked) {
        dateInput.value = '';
        dateInput.disabled = true;
        dateInput.required = false;
        edadInput.disabled = false;
        edadInput.required = true;
        edadInput.focus();
    } else {
        dateInput.disabled = false;
        dateInput.required = false; // Optional in this form
        edadInput.disabled = true;
        edadInput.required = false;
        edadInput.value = '';
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>
