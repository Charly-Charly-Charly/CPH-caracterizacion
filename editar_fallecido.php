<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Error: ID del fallecido no proporcionado.");
}

$stmt = $pdo->prepare("SELECT * FROM Fallecidos WHERE id = ?");
$stmt->execute([$id]);
$fal = $stmt->fetch();

if (!$fal) {
    die("Error: Fallecido no encontrado.");
}

$nucleo_id = $fal['nucleo_id'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Editar Fallecido: <?= htmlspecialchars($fal['Nombre']) ?></h2>
    <a href="ver_nucleo.php?id=<?= $nucleo_id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
</div>

<div class="card shadow-sm border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Modificar Datos de Familiar Fallecido</h5>
    </div>
    <div class="card-body p-4">
        <form action="actualizar_fallecido.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="nucleo_id" value="<?= htmlspecialchars($nucleo_id) ?>">

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" name="Nombre" class="form-control" value="<?= htmlspecialchars($fal['Nombre']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sexo</label>
                    <select name="Sexo" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Masculino" <?= $fal['Sexo'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Femenino" <?= $fal['Sexo'] == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parentesco</label>
                    <select name="Parentesco" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $opts = ['Madre', 'Padre', 'Esposa/o', 'Hijo/a', 'Hermano/a', 'Abuelo/a', 'Sobrino/a', 'Suegro/a', 'Otro'];
                        foreach($opts as $o) {
                            $sel = ($fal['Parentesco'] == $o) ? 'selected' : '';
                            echo "<option value='$o' $sel>$o</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fecha Nacimiento</label>
                    <input type="date" name="Fecha_Nacimiento" id="Fecha_Nacimiento" class="form-control" value="<?= htmlspecialchars($fal['Fecha_Nacimiento']) ?>" <?= $fal['Desconoce_Fecha'] ? 'disabled' : '' ?>>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="Desconoce_Fecha" id="Desconoce_Fecha" value="1" <?= $fal['Desconoce_Fecha'] ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="Desconoce_Fecha">Desconoce fecha nac.</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Edad Aproximada</label>
                    <input type="number" name="Edad" id="Edad" class="form-control" value="<?= htmlspecialchars($fal['Edad']) ?>" <?= $fal['Desconoce_Fecha'] ? '' : 'disabled' ?>>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha Fallecimiento</label>
                    <input type="date" name="Fecha_Fallecimiento" class="form-control" value="<?= htmlspecialchars($fal['Fecha_Fallecimiento']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Causa Fallecimiento</label>
                    <input type="text" name="Causa_Fallecimiento" class="form-control" value="<?= htmlspecialchars($fal['Causa_Fallecimiento']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Lugar Inhumación</label>
                    <input type="text" name="Lugar_Inhumacion" class="form-control" value="<?= htmlspecialchars($fal['Lugar_Inhumacion']) ?>">
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
        edadInput.disabled = false;
        edadInput.focus();
    } else {
        dateInput.disabled = false;
        edadInput.disabled = true;
        edadInput.value = '';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
