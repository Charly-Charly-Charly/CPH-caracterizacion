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

// Fetch Catalogos
$energias = $pdo->query("SELECT * FROM Energia_Cocina ORDER BY rowid")->fetchAll();
$alumbrados = $pdo->query("SELECT * FROM Alumbrado ORDER BY rowid")->fetchAll();
$aguasHogar = $pdo->query("SELECT * FROM Agua_Hogar ORDER BY rowid")->fetchAll();
$aguasConsumo = $pdo->query("SELECT * FROM Agua_Consumo ORDER BY rowid")->fetchAll();
$animales = $pdo->query("SELECT * FROM Ubicacion_Animales ORDER BY rowid")->fetchAll();
$sanitarios = $pdo->query("SELECT * FROM Tipo_Sanitario ORDER BY rowid")->fetchAll();
$aguasGrises = $pdo->query("SELECT * FROM Aguas_Grises ORDER BY rowid")->fetchAll();
$basuras = $pdo->query("SELECT * FROM Manejo_Basura ORDER BY rowid")->fetchAll();

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Registrar Vivienda - Familia <?= htmlspecialchars($nucleo['Nombre'].' '.$nucleo['Primer_Apellido']) ?></h2>
    <a href="ver_nucleo.php?id=<?= $nucleo_id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
</div>
<div class="alert alert-info" role="alert">
    <h4>Instrucciones:</h4>
    <p class="m-0">•Por favor, complete el formulario con la información de cada hogar que integra el núcleo familiar.</p>

</div>

<div class="card shadow-sm mb-4 border-info">
    <div class="card-body p-4">
        <form action="guardar_vivienda.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="nucleo_id" value="<?= htmlspecialchars($nucleo_id) ?>">
            
            <h5 class="text-info mb-3 pb-2 border-bottom">Condición de la Vivienda</h5>
            <div class="row g-3">
                
                <div class="col-md-3">
                    <label class="form-label">Cantidad de Ambientes</label>
                    <input type="number" name="Cantidad_Ambientes" class="form-control" min="1" placeholder="Ej. 3">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Material de Paredes</label>
                    <select name="Paredes" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Block">Block</option>
                        <option value="Madera">Madera / Tablas</option>
                        <option value="Lámina">Lámina</option>
                        <option value="Adobe">Adobe</option>
                        <option value="Nylon">Nylon</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Material de Techo</label>
                    <select name="Techo" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Lámina">Lámina</option>
                        <option value="Guano">Guano</option>
                        <option value="Nylon">Nylon</option>
                        <option value="Madera">Madera</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">Material de Suelo</label>
                    <select name="Suelo" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="Tierra">Tierra</option>
                        <option value="Torta de Cemento">Torta de Cemento</option>
                        <option value="Cerámica/Ladrillo">Cerámica / Ladrillo</option>
                        <option value="Madera">Madera</option>
                    </select>
                </div>
                <div class="col-md-3 mt-3 d-flex align-items-end">
                    <div class="form-check fs-5">
                        <input class="form-check-input" type="checkbox" name="Tiene_Sanitario" id="sanitario" value="1">
                        <label class="form-check-label" for="sanitario">Tiene Sanitario</label>
                    </div>
                </div>
            </div>

            <!-- Servicios -->
            <h5 class="text-info mt-5 mb-3 pb-2 border-bottom">Servicios Básicos y Energía</h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card bg-light h-100 border-0">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><i class="bi bi-fire"></i> Energía para Cocinar</h6>
                            <?php foreach ($energias as $e): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Energia_Cocina[]" value="<?= $e['id'] ?>" id="ene<?= $e['id'] ?>">
                                <label class="form-check-label" for="ene<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100 border-0">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><i class="bi bi-lightbulb"></i> Tipo de Alumbrado</h6>
                            <?php foreach ($alumbrados as $a): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Alumbrado[]" value="<?= $a['id'] ?>" id="alu<?= $a['id'] ?>">
                                <label class="form-check-label" for="alu<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100 border-0">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><i class="bi bi-water"></i> Tipo de Sanitario</h6>
                            <?php foreach ($sanitarios as $s): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Tipo_Sanitario[]" value="<?= $s['id'] ?>" id="san<?= $s['id'] ?>">
                                <label class="form-check-label" for="san<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agua y Saneamiento -->
            <h5 class="text-info mt-5 mb-3 pb-2 border-bottom">Agua y Saneamiento</h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card bg-light h-100 border-0">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><i class="bi bi-droplet"></i> Agua para uso Domestico</h6>
                            <p>Esta sección habla sobre el agua que se utiliza para el hogar, no para beber</p>
                            <?php foreach ($aguasHogar as $ah): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Agua_Hogar[]" value="<?= $ah['id'] ?>" id="ah<?= $ah['id'] ?>">
                                <label class="form-check-label" for="ah<?= $ah['id'] ?>"><?= htmlspecialchars($ah['nombre']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100 border-0">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><i class="bi bi-cup-straw"></i> Agua para Consumo</h6>
                            <?php foreach ($aguasConsumo as $ac): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Agua_Consumo[]" value="<?= $ac['id'] ?>" id="ac<?= $ac['id'] ?>">
                                <label class="form-check-label" for="ac<?= $ac['id'] ?>"><?= htmlspecialchars($ac['nombre']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100 border-0">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><i class="bi bi-trash"></i> Desagüe Aguas Grises</h6>
                            <?php foreach ($aguasGrises as $ag): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Aguas_Grises[]" value="<?= $ag['id'] ?>" id="ag<?= $ag['id'] ?>">
                                <label class="form-check-label" for="ag<?= $ag['id'] ?>"><?= htmlspecialchars($ag['nombre']) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-4">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="card-title text-muted"><i class="bi bi-recycle"></i> Manejo de Basura</h6>
                            <div class="row">
                                <?php foreach ($basuras as $ba): ?>
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="Manejo_Basura[]" value="<?= $ba['id'] ?>" id="ba<?= $ba['id'] ?>">
                                        <label class="form-check-label" for="ba<?= $ba['id'] ?>"><?= htmlspecialchars($ba['nombre']) ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mascotas -->
            <h5 class="text-info mt-5 mb-3 pb-2 border-bottom">Animales / Mascotas en Vivienda</h5>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">Especie Animal</th>
                                    <th class="text-center">¿Conviven dentro del Hogar?</th>
                                    <th class="text-center">¿Tienen espacio aislado?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($animales as $anim): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input animal-check" type="checkbox" name="Animales[]" value="<?= $anim['id'] ?>" id="anim<?= $anim['id'] ?>">
                                            <label class="form-check-label fw-bold" for="anim<?= $anim['id'] ?>">
                                                <i class="bi bi-tencent-qq me-1"></i><?= htmlspecialchars($anim['nombre']) ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-check" type="checkbox" name="conviven_<?= $anim['id'] ?>" id="conv<?= $anim['id'] ?>" disabled>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-check" type="checkbox" name="aislado_<?= $anim['id'] ?>" id="ais<?= $anim['id'] ?>" disabled>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-info btn-lg px-5 text-white">
                    <i class="bi bi-house-add"></i> Guardar Vivienda
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activar switches solo si el animal está seleccionado
    const animalChecks = document.querySelectorAll('.animal-check');
    animalChecks.forEach(check => {
        check.addEventListener('change', function() {
            const id = this.value;
            const conviven = document.getElementById('conv' + id);
            const aislado = document.getElementById('ais' + id);
            
            if (this.checked) {
                conviven.disabled = false;
                aislado.disabled = false;
            } else {
                conviven.disabled = true;
                conviven.checked = false;
                aislado.disabled = true;
                aislado.checked = false;
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
