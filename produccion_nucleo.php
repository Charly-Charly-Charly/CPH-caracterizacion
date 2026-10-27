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

// Catálogos

$ganado = $pdo->query("SELECT * FROM Ganado ORDER BY id")->fetchAll();
$cultivos = $pdo->query("SELECT * FROM Cultivos ORDER BY id")->fetchAll();
$arboles = $pdo->query("SELECT * FROM Arboles ORDER BY id")->fetchAll();

// Guardados actualmente para árboles
$stmt_cultivo_arbol = $pdo->prepare("SELECT id, tiene_cultivo FROM Cultivo_Arboles WHERE nucleo_id = ?");
$stmt_cultivo_arbol->execute([$nucleo_id]);
$cultivo_arbol = $stmt_cultivo_arbol->fetch(PDO::FETCH_ASSOC);

$cur_arboles = [];
if ($cultivo_arbol) {
    $stmt_detalle = $pdo->prepare("SELECT arbol_id FROM Cultivo_Arboles_Detalle WHERE cultivo_id = ?");
    $stmt_detalle->execute([$cultivo_arbol['id']]);
    $cur_arboles = $stmt_detalle->fetchAll(PDO::FETCH_COLUMN);
}

// Catálogo y guardados para actividades bosque
$actividades_bosque = $pdo->query("SELECT * FROM Actividades_Bosque ORDER BY id")->fetchAll();
$sel_act_bosque = $pdo->prepare("SELECT actividad_id FROM Nucleo_Actividades_Bosque WHERE nucleo_id = ?");
$sel_act_bosque->execute([$nucleo_id]);
$cur_act_bosque = $sel_act_bosque->fetchAll(PDO::FETCH_COLUMN);

// Guardados actualmente
$sel_domesticos = $pdo->prepare("SELECT animal_id FROM Nucleo_Animales_Domesticos WHERE nucleo_id = ?");
$sel_domesticos->execute([$nucleo_id]);
$cur_dom = $sel_domesticos->fetchAll(PDO::FETCH_COLUMN);

$sel_ganado = $pdo->prepare("SELECT ganado_id, es_personal, es_comercial FROM Nucleo_Ganado WHERE nucleo_id = ?");
$sel_ganado->execute([$nucleo_id]);
$cur_gan = [];
foreach ($sel_ganado->fetchAll() as $row) {
    $cur_gan[$row['ganado_id']] = $row;
}

$sel_cultivos = $pdo->prepare("SELECT cultivo_id, es_personal, es_comercial FROM Nucleo_Cultivos WHERE nucleo_id = ?");
$sel_cultivos->execute([$nucleo_id]);
$cur_cul = [];
foreach ($sel_cultivos->fetchAll() as $row) {
    $cur_cul[$row['cultivo_id']] = $row;
}

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Medios de Vida  - Familia <?= htmlspecialchars($nucleo['Nombre'].' '.$nucleo['Primer_Apellido']) ?></h2>
    <a href="ver_nucleo.php?id=<?= $nucleo_id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm border-success mb-4">
    <div class="card-body p-4">
        <form action="guardar_produccion.php" method="POST">
            <input type="hidden" name="nucleo_id" value="<?= htmlspecialchars($nucleo_id) ?>">

            <div class="row g-4">
                <div class="col-md-5">
                    
                   

                    <h5 class="text-success border-bottom pb-2 mt-5"><i class="bi bi-bug"></i> Animales Pecuarios</h5>
                    <p class="text-muted small">Crianza de animales mayores para consumo o venta.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%;">Tipo de Animal</th>
                                    <th class="text-center">Consumo Personal</th>
                                    <th class="text-center">Venta Comercial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ganado as $gn): 
                                    $has_gan = isset($cur_gan[$gn['id']]);
                                    $is_per = $has_gan && $cur_gan[$gn['id']]['es_personal'] ? 'checked' : '';
                                    $is_com = $has_gan && $cur_gan[$gn['id']]['es_comercial'] ? 'checked' : '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input gan-check" type="checkbox" name="Ganado[]" value="<?= $gn['id'] ?>" id="gn<?= $gn['id'] ?>" <?= $has_gan ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="gn<?= $gn['id'] ?>">
                                                <?= htmlspecialchars($gn['nombre']) ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-gan-per" type="checkbox" name="personal_gan_<?= $gn['id'] ?>" id="pergan<?= $gn['id'] ?>" <?= $is_per ?> <?= $has_gan ? '' : 'disabled' ?>>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-gan-com" type="checkbox" name="comercial_gan_<?= $gn['id'] ?>" id="comgan<?= $gn['id'] ?>" <?= $is_com ?> <?= $has_gan ? '' : 'disabled' ?>>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-7">
                    <h5 class="text-success border-bottom pb-2"><i class="bi bi-tree"></i> Cultivos Agrícolas</h5>
                    <p class="text-muted small">Marque los cultivos que el núcleo produce y el destino de su cosecha.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%;">Tipo de Cultivo</th>
                                    <th class="text-center">Consumo Personal</th>
                                    <th class="text-center">Venta Comercial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cultivos as $cul): 
                                    $has_cul = isset($cur_cul[$cul['id']]);
                                    $is_per = $has_cul && $cur_cul[$cul['id']]['es_personal'] ? 'checked' : '';
                                    $is_com = $has_cul && $cur_cul[$cul['id']]['es_comercial'] ? 'checked' : '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input cul-check" type="checkbox" name="Cultivos[]" value="<?= $cul['id'] ?>" id="cul<?= $cul['id'] ?>" <?= $has_cul ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="cul<?= $cul['id'] ?>">
                                                <?= htmlspecialchars($cul['nombre']) ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-check-per" type="checkbox" name="personal_<?= $cul['id'] ?>" id="per<?= $cul['id'] ?>" <?= $is_per ?> <?= $has_cul ? '' : 'disabled' ?>>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-check-com" type="checkbox" name="comercial_<?= $cul['id'] ?>" id="com<?= $cul['id'] ?>" <?= $is_com ?> <?= $has_cul ? '' : 'disabled' ?>>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: CULTIVO DE ÁRBOLES -->
            <div class="row mt-5">
                <div class="col-12">
                    <h5 class="text-success border-bottom pb-2"><i class="bi bi-tree-fill"></i> Cultivo de Árboles</h5>
                    
                    <div id="section-arboles">
                        <p class="text-muted small mb-3">Selleccione los tipos de árboles que el núcleo cultiva:</p>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                            <?php foreach($arboles as $ar): $chk = in_array($ar['id'], $cur_arboles) ? 'checked' : ''; ?>
                            <div class="col text-nowrap">
                                <div class="form-check">
                                    <input class="form-check-input arb-check" type="checkbox" name="Arboles[]" value="<?= $ar['id'] ?>" id="arb<?= $ar['id'] ?>" <?= $chk ?>>
                                    <label class="form-check-label" for="arb<?= $ar['id'] ?>"><?= htmlspecialchars($ar['nombre']) ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN NUEVA: ACTIVIDADES EN EL BOSQUE -->
            <div class="row mt-5 pb-4">
                <div class="col-12">
                    <h5 class="text-success border-bottom pb-2"><i class="bi bi-compass"></i> ¿Realizan actividades en el bosque?</h5>
                    <p class="text-muted small mb-3">Marque las actividades que los integrantes del núcleo realizan en áreas boscosas:</p>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                        <?php foreach($actividades_bosque as $act): $chk = in_array($act['id'], $cur_act_bosque) ? 'checked' : ''; ?>
                        <div class="col text-nowrap">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Actividades_Bosque[]" value="<?= $act['id'] ?>" id="act<?= $act['id'] ?>" <?= $chk ?>>
                                <label class="form-check-label" for="act<?= $act['id'] ?>"><?= htmlspecialchars($act['nombre']) ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-end border-top pt-4">
                <button type="submit" class="btn btn-success btn-lg px-5 text-white">
                    <i class="bi bi-save"></i> Guardar Producción
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupToggle(checkClass, perPrefix, comPrefix) {
        const checks = document.querySelectorAll('.' + checkClass);
        checks.forEach(check => {
            check.addEventListener('change', function() {
                const id = this.value;
                const per = document.getElementById(perPrefix + id);
                const com = document.getElementById(comPrefix + id);
                
                if (this.checked) {
                    per.disabled = false;
                    com.disabled = false;
                } else {
                    per.disabled = true;
                    per.checked = false;
                    com.disabled = true;
                    com.checked = false;
                }
            });
        });
    }

    setupToggle('cul-check', 'per', 'com');
    setupToggle('gan-check', 'pergan', 'comgan');
});
</script>

<?php require_once 'includes/footer.php'; ?>
