<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Nuevo Núcleo Familiar</h1>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="guardar_nucleo.php" method="POST" class="needs-validation" novalidate>
            
            <h5 class="text-primary mb-3 pb-2 border-bottom">Datos del Entrevistado</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Primer Nombre *</label>
                    <input type="text" name="Nombre" class="form-control" required>
                    <div class="invalid-feedback">Requerido</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Segundo Nombre</label>
                    <input type="text" name="Segundo_Nombre" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tercer Nombre</label>
                    <input type="text" name="Tercer_Nombre" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Primer Apellido *</label>
                    <input type="text" name="Primer_Apellido" class="form-control" required>
                    <div class="invalid-feedback">Requerido</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Segundo Apellido</label>
                    <input type="text" name="Segundo_Apellido" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Apellido de Casada</label>
                    <input type="text" name="Apellido_Casada" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">DPI *</label>
                    <input type="text" name="DPI" class="form-control" maxlength="13" required>
                    <div class="invalid-feedback">13 dígitos sin espacios</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de Nacimiento *</label>
                    <input type="date" name="Fecha_Nacimiento" id="Fecha_Nacimiento" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="Desconoce_Fecha" id="Desconoce_Fecha" value="1">
                        <label class="form-check-label small" for="Desconoce_Fecha">Desconoce fecha</label>
                    </div>
                </div>
                <div class="col-md-2" id="div_edad">
                    <label class="form-label text-nowrap">Edad Aproximada</label>
                    <input type="number" name="Edad" id="Edad" class="form-control" min="0" max="120" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sexo</label>
                    <select name="Sexo" class="form-select" require>
                        <option value="">Seleccione...</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-4 mt-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="Telefono" class="form-control" maxlength="15">
                </div>
            </div>

            <h5 class="text-primary mt-5 mb-3 pb-2 border-bottom">Datos Culturales y Representación</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Persona Beneficiaria MC *</label>
                    <input type="text" name="Persona_Beneficiaria_MC" class="form-control" required>
                    <div class="invalid-feedback">Requerido</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parentesco MC</label>
                    <select name="Parentesco_MC"  class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Madre">Madre</option>
                        <option value="Padre">Padre</option>
                        <option value="Hijo/a">Hijo/a</option>
                        <option value="Hermano/a">Hermano/a</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Afinidad MC</label>
                    <select name="Afinidad_MC" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Esposa/o">Esposa/o</option>
                        <option value="Suegro/a">Suegro/a</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="col-md-6 mt-3">
                    <label class="form-label">Pertenencia Cultural</label>
                    <select name="Pertenencia_Cultural" require class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Ladino">Ladino / Mestizo</option>
                        <option value="Maya">Maya</option>
                        <option value="Xinca">Xinca</option>
                        <option value="Garifuna">Garifuna</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">Comunidad Lingüística</label>
                    <select name="Comunidad_Linguistica" require class="form-select">

                        <option value="">Seleccione...</option>
                        <option value="Español">Español</option>
                        <option value="Achi’">Achi’</option>
                        <option value="Akateko">Akateko</option>
                        <option value="Awakateko">Awakateko</option>
                        <option value="Chuj">Chuj</option>
                        <option value="Ch’orti’">Ch’orti’</option>
                        <option value="Itzá">Itzá</option>
                        <option value="Ixil">Ixil</option>
                        <option value="Kaqchikel">Kaqchikel</option>
                        <option value="K’iche’">K’iche’</option>
                        <option value="Mam">Mam</option>
                        <option value="Mopán">Mopán</option>
                        <option value="Poptí (Jakalteko)">Poptí (Jakalteko)</option>
                        <option value="Poqomam">Poqomam</option>
                        <option value="Poqomchí">Poqomchí</option>
                        <option value="Q’anjob’al">Q’anjob’al</option>
                        <option value="Q’eqchi’">Q’eqchi’</option>
                        <option value="Sakapulteko">Sakapulteko</option>
                        <option value="Sipakapense">Sipakapense</option>
                        <option value="Tektiteko">Tektiteko</option>
                        <option value="Tz’utujil">Tz’utujil</option>
                        <option value="Uspanteko">Uspanteko</option>
                        <option value="Chalchiteko">Chalchiteko</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-save"></i> Guardar Núcleo Familiar
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
        dateInput.required = true;
        edadInput.disabled = true;
        edadInput.required = false;
        edadInput.value = '';
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>
