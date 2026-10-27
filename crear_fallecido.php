<?php
require_once 'includes/db.php';

$nucleo_id = $_GET['nucleo_id'] ?? null;
if (!$nucleo_id) {
    die("Error: Se requiere el ID del núcleo.");
}

$stmt = $pdo->prepare("SELECT Nombre, Primer_Apellido FROM Nucleo WHERE id = ?");
$stmt->execute([$nucleo_id]);
$nucleo = $stmt->fetch();
if (!$nucleo) {
    die("Error: Núcleo no encontrado.");
}

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Registrar Familiar Fallecido - Familia <?= htmlspecialchars($nucleo['Nombre'].' '.$nucleo['Primer_Apellido']) ?></h2>
    <a href="ver_nucleo.php?id=<?= $nucleo_id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
</div>

<div class="card shadow-sm mb-4 border-dark">
    <div class="card-body p-4">
        <form action="guardar_fallecido.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="nucleo_id" value="<?= htmlspecialchars($nucleo_id) ?>">
            
            <h5 class="text-dark mb-3 pb-2 border-bottom"><i class="bi bi-person-x"></i> Datos Personales del Fallecido</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" name="Nombre" class="form-control" required placeholder="Nombres y Apellidos">
                    <div class="invalid-feedback">Por favor, ingrese el nombre.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sexo</label>
                    <select name="Sexo" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Hombre">Hombre</option>
                        <option value="Mujer">Mujer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parentesco</label>
                    <select name="Parentesco" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Madre">Madre</option>
                        <option value="Padre">Padre</option>
                        <option value="Estaca/o">Esposa/o</option>
                        <option value="Hijo/a">Hijo/a</option>
                        <option value="Hermano/a">Hermano/a</option>
                        <option value="Abuelo/a">Abuelo/a</option>
                        <option value="Sobrino/a">Sobrino/a</option>
                        <option value="Suegro/a">Suegro/a</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="Fecha_Nacimiento" id="Fecha_Nacimiento" class="form-control" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="Desconoce_Fecha" id="Desconoce_Fecha" value="1">
                        <label class="form-check-label small" for="Desconoce_Fecha">Desconoce fecha nac.</label>
                    </div>
                </div>
                <div class="col-md-3" id="div_edad">
                    <label class="form-label text-nowrap">Edad Aproximada (al morir)</label>
                    <input type="number" name="Edad" id="Edad" class="form-control" min="0" max="150" disabled>
                </div>
            </div>

            <h5 class="text-dark mt-5 mb-3 pb-2 border-bottom"><i class="bi bi-journal-medical"></i> Detalles del Deceso</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha de Fallecimiento</label>
                    <input type="date" name="Fecha_Fallecimiento" class="form-control" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Causa del Fallecimiento</label>
                    <input type="text" name="Causa_Fallecimiento" class="form-control" placeholder="Ej. Enfermedad respiratoria, Accidente, Muerte natural">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Esta sepultado en la comunidad</label>
                    <select name="Lugar_Inhumacion" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Sí">Sí</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>

            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-dark btn-lg px-5 text-white">
                    <i class="bi bi-save"></i> Guardar Registro de Fallecido
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Bootstrap form validation
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()

document.getElementById('Desconoce_Fecha').addEventListener('change', function() {
    const dateInput = document.getElementById('Fecha_Nacimiento');
    const edadInput = document.getElementById('Edad');
    
    if (this.checked) {
        dateInput.value = '';
        dateInput.disabled = true;
        edadInput.disabled = false;
        edadInput.required = true;
        edadInput.focus();
    } else {
        dateInput.disabled = false;
        edadInput.disabled = true;
        edadInput.required = false;
        edadInput.value = '';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
