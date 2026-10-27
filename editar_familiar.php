<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<div class='alert alert-danger'>ID de Familiar no proporcionado.</div>";
    require_once 'includes/footer.php';
    exit;
}

// Fetch member data
$stmt = $pdo->prepare("SELECT * FROM Familiares WHERE id = ?");
$stmt->execute([$id]);
$fam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fam) {
    echo "<div class='alert alert-danger'>Familiar no encontrado.</div>";
    require_once 'includes/footer.php';
    exit;
}

$nucleo_id = $fam['nucleo_id'];

// Fetch catalogs
$enfermedades = $pdo->query("SELECT * FROM Enfermedades ORDER BY rowid")->fetchAll(PDO::FETCH_ASSOC);
$trabajos = $pdo->query("SELECT * FROM Trabajos ORDER BY rowid")->fetchAll(PDO::FETCH_ASSOC);
$fuentes = $pdo->query("SELECT * FROM Fuentes_Ingreso ORDER BY rowid")->fetchAll(PDO::FETCH_ASSOC);

// Fetch current selections
$cur_enf_ids = $pdo->prepare("SELECT enfermedad_id FROM Familiar_Enfermedades WHERE familiar_id = ?");
$cur_enf_ids->execute([$id]);
$cur_enf_ids = $cur_enf_ids->fetchAll(PDO::FETCH_COLUMN);

$cur_trab_ids = $pdo->prepare("SELECT trabajo_id FROM Familiar_Trabajos WHERE familiar_id = ?");
$cur_trab_ids->execute([$id]);
$cur_trab_ids = $cur_trab_ids->fetchAll(PDO::FETCH_COLUMN);

$cur_fte_ids = $pdo->prepare("SELECT fuente_id FROM Familiar_Fuentes WHERE familiar_id = ?");
$cur_fte_ids->execute([$id]);
$cur_fte_ids = $cur_fte_ids->fetchAll(PDO::FETCH_COLUMN);

// Helper for selected/checked
function isSelected($val, $dbVal) { echo $val == $dbVal ? 'selected' : ''; }
function isChecked($val, $dbVal) { echo $val == $dbVal ? 'checked' : ''; }
function inArrayChecked($val, $arr) { echo in_array($val, $arr) ? 'checked' : ''; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Editar Familiar: <?= htmlspecialchars($fam['Nombre']) ?></h1>
    
    <a href="ver_nucleo.php?id=<?= htmlspecialchars($nucleo_id) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Núcleo
    </a>
</div>

<div class="card shadow-sm border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Modificar Datos del Familiar</h5>
    </div>
    <div class="card-body p-4">
        <form action="actualizar_familiar.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="nucleo_id" value="<?= htmlspecialchars($nucleo_id) ?>">
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" name="Nombre" class="form-control" value="<?= htmlspecialchars($fam['Nombre']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nacionalidad</label>
                    <select name="Nacionalidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Guatemalteco" <?php isSelected('Guatemalteco', $fam['Nacionalidad']); ?>>Guatemalteco</option>
                        <option value="Mexicano" <?php isSelected('Mexicano', $fam['Nacionalidad']); ?>>Mexicano</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tiene Doble Nacionalidad</label>
                    <select name="Doble_Nacionalidad" class="form-select">
                        <option value="0" <?php isSelected('0', $fam['Doble_Nacionalidad']); ?>>No</option>
                        <option value="1" <?php isSelected('1', $fam['Doble_Nacionalidad']); ?>>Sí</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sexo</label>
                    <select name="Sexo" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Masculino" <?php isSelected('Masculino', $fam['Sexo']); ?>>Masculino</option>
                        <option value="Femenino" <?php isSelected('Femenino', $fam['Sexo']); ?>>Femenino</option>
                        <option value="Otro" <?php isSelected('Otro', $fam['Sexo']); ?>>Otro</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="Fecha_Nacimiento" id="Fecha_Nacimiento" class="form-control" value="<?= htmlspecialchars($fam['Fecha_Nacimiento']) ?>" <?= $fam['Desconoce_Fecha'] ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="Desconoce_Fecha" id="Desconoce_Fecha" value="1" <?= $fam['Desconoce_Fecha'] ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="Desconoce_Fecha">Desconoce fecha</label>
                    </div>
                </div>
                <div class="col-md-2" id="div_edad">
                    <label class="form-label text-nowrap">Edad</label>
                    <input type="number" name="Edad" id="Edad" class="form-control" min="0" max="120" value="<?= htmlspecialchars($fam['Edad']) ?>" <?= $fam['Desconoce_Fecha'] ? 'required' : 'disabled' ?>>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parentesco</label>
                    <select name="Parentesco" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $parentescos = ['Entrevistado', 'Madre', 'Padre', 'Hijo/a', 'Hermano/a', 'Abuelo/a', 'Sobrino/a', 'Otro'];
                        foreach($parentescos as $p) { echo "<option value='$p' ".(($fam['Parentesco']==$p)?'selected':'').">$p</option>"; }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Afinidad</label>
                    <select name="Afinidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Esposa/o" <?php isSelected('Esposa/o', $fam['Afinidad']); ?>>Esposa/o</option>
                        <option value="Suegro/a" <?php isSelected('Suegro/a', $fam['Afinidad']); ?>>Suegro/a</option>
                        <option value="Otro" <?php isSelected('Otro', $fam['Afinidad']); ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado Civil</label>
                    <select name="Estado_Civil" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Soltero/a" <?php isSelected('Soltero/a', $fam['Estado_Civil']); ?>>Soltero/a</option>
                        <option value="Casado/a" <?php isSelected('Casado/a', $fam['Estado_Civil']); ?>>Casado/a</option>
                        <option value="Unido/a" <?php isSelected('Unido/a', $fam['Estado_Civil']); ?>>Unido/a</option>
                        <option value="Viudo/a" <?php isSelected('Viudo/a', $fam['Estado_Civil']); ?>>Viudo/a</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Donde Reside Actualmente</label>
                    <select name="Donde_reside" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Comunidad" <?php isSelected('Comunidad', $fam['Donde_reside']); ?>>Comunidad</option>
                        <option value="Petén" <?php isSelected('Petén', $fam['Donde_reside']); ?>>Petén</option>
                        <option value="México" <?php isSelected('México', $fam['Donde_reside']); ?>>México</option>
                        <option value="Otro" <?php isSelected('Otro', $fam['Donde_reside']); ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tiempo Fuera de la Comunidad</label>
                    <select name="Fuera_comunidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Menos de 6 meses" <?php isSelected('Menos de 6 meses', $fam['Fuera_comunidad']); ?>>Menos de 6 meses</option>
                        <option value="6 meses a 1 año" <?php isSelected('6 meses a 1 año', $fam['Fuera_comunidad']); ?>>6 meses a 1 año</option>
                        <option value="1 año a 5 años" <?php isSelected('1 año a 5 años', $fam['Fuera_comunidad']); ?>>1 año</option>
                        <option value="Más de 5 años" <?php isSelected('Más de 5 años', $fam['Fuera_comunidad']); ?>>Más de un año</option>
                    </select>
                </div>
                
            </div>

            <h5 class="text-success mt-4 mb-3 pb-2 border-bottom">Documentos de Identificación</h5>
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Tiene_DPI" id="Tiene_DPI" value="1" <?php isChecked('1', $fam['Tiene_DPI']); ?>>
                        <label class="form-check-label fs-6" for="Tiene_DPI">Tiene DPI</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Tiene_Certificado_Nacimiento" id="Tiene_Certificado_Nacimiento" value="1" <?php isChecked('1', $fam['Tiene_Certificado_Nacimiento']); ?>>
                        <label class="form-check-label fs-6" for="Tiene_Certificado_Nacimiento">Tiene Certificado de Nacimiento</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Tiene_Pasaporte" id="Tiene_Pasaporte" value="1" <?php isChecked('1', $fam['Tiene_Pasaporte']); ?>>
                        <label class="form-check-label fs-6" for="Tiene_Pasaporte">Tiene Pasaporte</label>
                    </div>
                </div>
            </div>

            <h5 class="text-success mt-4 mb-3 pb-2 border-bottom">Salud</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Embarazada</label>
                    <select name="Embarazada" class="form-select">
                        <option value="0" <?php isSelected('0', $fam['Embarazada']); ?>>No</option>
                        <option value="1" <?php isSelected('1', $fam['Embarazada']); ?>>Sí</option>
                        <option value="2" <?php isSelected('2', $fam['Embarazada']); ?>>No Aplica</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Atención Prenatal</label>
                    <select name="Atencion_Prenatal" class="form-select">
                        <option value="0" <?php isSelected('0', $fam['Atencion_Prenatal']); ?>>No</option>
                        <option value="1" <?php isSelected('1', $fam['Atencion_Prenatal']); ?>>Sí</option>
                        <option value="2" <?php isSelected('2', $fam['Atencion_Prenatal']); ?>>No Aplica</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Donde Recibe Atención Prenatal</label>
                    <select name="Tipo_Atencion_Prenatal" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Hospital" <?php isSelected('Hospital', $fam['Tipo_Atencion_Prenatal']); ?>>Hospital</option>
                        <option value="Centro de Salud" <?php isSelected('Centro de Salud', $fam['Tipo_Atencion_Prenatal']); ?>>Centro de Salud</option>
                        <option value="Comadrona" <?php isSelected('Comadrona', $fam['Tipo_Atencion_Prenatal']); ?>>Comadrona</option>
                    </select>
                </div>
                
                <div class="col-md-6 mt-3">
                    <label class="form-label">Tiene Discapacidad</label>
                    <select name="Tiene_Discapacidad" class="form-select">
                        <option value="0" <?php isSelected('0', $fam['Tiene_Discapacidad']); ?>>No</option>
                        <option value="1" <?php isSelected('1', $fam['Tiene_Discapacidad']); ?>>Sí</option>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Tipo de Discapacidad</label>
                    <select name="Tipo_Discapacidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $tipos_disc = ['Física', 'Sensorial', 'Visual', 'Intelectual', 'Mental', 'OTRO'];
                        foreach($tipos_disc as $td) { echo "<option value='$td' ".(($fam['Tipo_Discapacidad']==$td)?'selected':'').">$td</option>"; }
                        ?>
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
                                        <input class="form-check-input" type="checkbox" name="Enfermedades[]" value="<?= $enf['id'] ?>" id="enf_<?= $enf['id'] ?>" <?php inArrayChecked($enf['id'], $cur_enf_ids); ?>>
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
                        <input class="form-check-input" type="checkbox" role="switch" name="Sabe_Leer" id="Sabe_Leer" value="1" <?php isChecked('1', $fam['Sabe_Leer']); ?>>
                        <label class="form-check-label fs-6" for="Sabe_Leer">Sabe Leer</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Sabe_Escribir" id="Sabe_Escribir" value="1" <?php isChecked('1', $fam['Sabe_Escribir']); ?>>
                        <label class="form-check-label fs-6" for="Sabe_Escribir">Sabe Escribir</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Estudia_Actualmente" id="Estudia_Actualmente" value="1" <?php isChecked('1', $fam['Estudia_Actualmente']); ?>>
                        <label class="form-check-label fs-6" for="Estudia_Actualmente">Estudia Actualmente</label>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Escolaridad alcanzada</label>
                    <select name="Escolaridad" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $escolaridades = ['Sin estudios', 'Preprimaria', 'Primaria', 'Basicos', 'Diversificado', 'Universitario'];
                        foreach($escolaridades as $es) { echo "<option value='$es' ".(($fam['Escolaridad']==$es)?'selected':'').">$es</option>"; }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Razón abandono de estudio</label>
                    <select name="Razon_Abandono" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $razones = ['Trabajo', 'Decisión Propia', 'Migración', 'Economico', 'Salud', 'Otro'];
                        foreach($razones as $rz) { echo "<option value='$rz' ".(($fam['Razon_Abandono']==$rz)?'selected':'').">$rz</option>"; }
                        ?>
                    </select>
                </div>
            </div>

            <h5 class="text-success mt-4 mb-3 pb-2 border-bottom">Laboral e Ingresos</h5>
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-6">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Actualmente_Labora" id="Actualmente_Labora" value="1" <?php isChecked('1', $fam['Actualmente_Labora']); ?>>
                        <label class="form-check-label fs-6" for="Actualmente_Labora">Actualmente labora</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="Viajan_Trabajo" id="Viajan_Trabajo" value="1" <?php isChecked('1', $fam['Viajan_Trabajo']); ?>>
                        <label class="form-check-label fs-6" for="Viajan_Trabajo">Se desplaza por trabajo</label>
                    </div>
                </div>

                <div class="col-md-6 mt-3">
                    <label class="form-label">Ingresos Diarios Estimados (Q)</label>
                    <select name="Ingresos_Mensuales" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $opciones_ingreso = ['0-50', '50-100', '100-150', '150-200', '200+'];
                        foreach($opciones_ingreso as $oi) { echo "<option value='$oi' ".(($fam['Ingresos_Mensuales']==$oi)?'selected':'').">$oi</option>"; }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Tiempo de desplazamiento</label>
                    <select name="Tiempo_Viaje" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $tiempos = ['menos de una semana', '1 Semana', '2 Semanas', '3 Semanas', '4 Semanas', 'más de un mes'];
                        foreach($tiempos as $tj) { echo "<option value='$tj' ".(($fam['Tiempo_Viaje']==$tj)?'selected':'').">$tj</option>"; }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-6 mt-4">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Trabajos / Ocupaciones</h6>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach($trabajos as $trab): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="Trabajos[]" value="<?= $trab['id'] ?>" id="trab_<?= $trab['id'] ?>" <?php inArrayChecked($trab['id'], $cur_trab_ids); ?>>
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
                                    <input class="form-check-input" type="checkbox" name="Fuentes[]" value="<?= $fte['id'] ?>" id="fte_<?= $fte['id'] ?>" <?php inArrayChecked($fte['id'], $cur_fte_ids); ?>>
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
                <button type="submit" class="btn btn-warning btn-lg px-5">
                    <i class="bi bi-save"></i> Guardar Cambios
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
        dateInput.required = false; 
        edadInput.disabled = true;
        edadInput.required = false;
        edadInput.value = '';
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>
