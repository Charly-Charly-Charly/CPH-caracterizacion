<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Error: ID de vivienda no proporcionado.");
}

// Fetch Vivienda
$stmt = $pdo->prepare("SELECT * FROM Vivienda WHERE id = ?");
$stmt->execute([$id]);
$viv = $stmt->fetch();

if (!$viv) {
    die("Error: Vivienda no encontrada.");
}

$nucleo_id = $viv['nucleo_id'];

// Catalogs
$energias = $pdo->query("SELECT * FROM Energia_Cocina ORDER BY rowid")->fetchAll();
$alumbrados = $pdo->query("SELECT * FROM Alumbrado ORDER BY rowid")->fetchAll();
$aguasHogar = $pdo->query("SELECT * FROM Agua_Hogar ORDER BY rowid")->fetchAll();
$aguasConsumo = $pdo->query("SELECT * FROM Agua_Consumo ORDER BY rowid")->fetchAll();
$animales_cat = $pdo->query("SELECT * FROM Ubicacion_Animales ORDER BY rowid")->fetchAll();
$sanitarios = $pdo->query("SELECT * FROM Tipo_Sanitario ORDER BY rowid")->fetchAll();
$aguasGrises = $pdo->query("SELECT * FROM Aguas_Grises ORDER BY rowid")->fetchAll();
$basuras = $pdo->query("SELECT * FROM Manejo_Basura ORDER BY rowid")->fetchAll();

// Current selections (pivot table IDs)
function getPivotIds($table, $col, $vivienda_id, $pdo) {
    $stmt = $pdo->prepare("SELECT $col FROM $table WHERE vivienda_id = ?");
    $stmt->execute([$vivienda_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$cur_ene = getPivotIds('Vivienda_Energia_Cocina', 'energia_id', $id, $pdo);
$cur_alu = getPivotIds('Vivienda_Alumbrado', 'alumbrado_id', $id, $pdo);
$cur_ah = getPivotIds('Vivienda_Agua_Hogar', 'agua_id', $id, $pdo);
$cur_ac = getPivotIds('Vivienda_Agua_Consumo', 'agua_id', $id, $pdo);
$cur_san = getPivotIds('Vivienda_Sanitario', 'sanitario_id', $id, $pdo);
$cur_ag = getPivotIds('Vivienda_Aguas_Grises', 'aguas_id', $id, $pdo);
$cur_ba = getPivotIds('Vivienda_Basura', 'basura_id', $id, $pdo);

// Animal data (IDs and flags)
$stmtAnim = $pdo->prepare("SELECT * FROM Vivienda_Animales WHERE vivienda_id = ?");
$stmtAnim->execute([$id]);
$cur_anim = [];
while ($row = $stmtAnim->fetch()) {
    $cur_anim[$row['ubicacion_id']] = $row;
}

function inArrayChecked($val, $arr) { echo in_array($val, $arr) ? 'checked' : ''; }
function isSelected($val, $dbVal) { echo $val == $dbVal ? 'selected' : ''; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Editar Vivienda</h2>
    <a href="ver_nucleo.php?id=<?= $nucleo_id ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
</div>

<div class="card shadow-sm mb-4 border-warning">
    <div class="card-body p-4">
        <form action="actualizar_vivienda.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="nucleo_id" value="<?= htmlspecialchars($nucleo_id) ?>">
            
            <h5 class="text-info mb-3 pb-2 border-bottom">Condición de la Vivienda</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cantidad de Ambientes</label>
                    <input type="number" name="Cantidad_Ambientes" class="form-control" min="1" value="<?= htmlspecialchars($viv['Cantidad_Ambientes']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Material de Paredes</label>
                    <select name="Paredes" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $mat_paredes = ['Block', 'Madera', 'Lámina', 'Adobe', 'Bambú/Paja', 'Otro'];
                        foreach($mat_paredes as $mp) { echo "<option value='$mp' ".(($viv['Paredes']==$mp)?'selected':'').">$mp</option>"; }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Material de Techo</label>
                    <select name="Techo" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $mat_techo = ['Lámina', 'Terraza', 'Palma/Paja', 'Madera', 'Otro'];
                        foreach($mat_techo as $mt) { echo "<option value='$mt' ".(($viv['Techo']==$mt)?'selected':'').">$mt</option>"; }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">Material de Suelo</label>
                    <select name="Suelo" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php 
                        $mat_suelo = ['Tierra', 'Torta de Cemento', 'Cerámica/Ladrillo', 'Madera'];
                        foreach($mat_suelo as $ms) { echo "<option value='$ms' ".(($viv['Suelo']==$ms)?'selected':'').">$ms</option>"; }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mt-3 d-flex align-items-end">
                    <div class="form-check fs-5">
                        <input class="form-check-input" type="checkbox" name="Tiene_Sanitario" id="sanitario" value="1" <?= $viv['Tiene_Sanitario'] ? 'checked' : '' ?>>
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
                                <input class="form-check-input" type="checkbox" name="Energia_Cocina[]" value="<?= $e['id'] ?>" id="ene<?= $e['id'] ?>" <?php inArrayChecked($e['id'], $cur_ene); ?>>
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
                                <input class="form-check-input" type="checkbox" name="Alumbrado[]" value="<?= $a['id'] ?>" id="alu<?= $a['id'] ?>" <?php inArrayChecked($a['id'], $cur_alu); ?>>
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
                                <input class="form-check-input" type="checkbox" name="Tipo_Sanitario[]" value="<?= $s['id'] ?>" id="san<?= $s['id'] ?>" <?php inArrayChecked($s['id'], $cur_san); ?>>
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
                            <?php foreach ($aguasHogar as $ah): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Agua_Hogar[]" value="<?= $ah['id'] ?>" id="ah<?= $ah['id'] ?>" <?php inArrayChecked($ah['id'], $cur_ah); ?>>
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
                                <input class="form-check-input" type="checkbox" name="Agua_Consumo[]" value="<?= $ac['id'] ?>" id="ac<?= $ac['id'] ?>" <?php inArrayChecked($ac['id'], $cur_ac); ?>>
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
                                <input class="form-check-input" type="checkbox" name="Aguas_Grises[]" value="<?= $ag['id'] ?>" id="ag<?= $ag['id'] ?>" <?php inArrayChecked($ag['id'], $cur_ag); ?>>
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
                                        <input class="form-check-input" type="checkbox" name="Manejo_Basura[]" value="<?= $ba['id'] ?>" id="ba<?= $ba['id'] ?>" <?php inArrayChecked($ba['id'], $cur_ba); ?>>
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
                                <?php foreach($animales_cat as $anim): 
                                    $has_anim = isset($cur_anim[$anim['id']]);
                                    $conviven = $has_anim && $cur_anim[$anim['id']]['conviven_hogar'] ? 'checked' : '';
                                    $aislado = $has_anim && $cur_anim[$anim['id']]['espacio_aislado'] ? 'checked' : '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input animal-check" type="checkbox" name="Animales[]" value="<?= $anim['id'] ?>" id="anim<?= $anim['id'] ?>" <?= $has_anim ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="anim<?= $anim['id'] ?>">
                                                <i class="bi bi-tencent-qq me-1"></i><?= htmlspecialchars($anim['nombre']) ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-check" type="checkbox" name="conviven_<?= $anim['id'] ?>" id="conv<?= $anim['id'] ?>" <?= $conviven ?> <?= $has_anim ? '' : 'disabled' ?>>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input prop-check" type="checkbox" name="aislado_<?= $anim['id'] ?>" id="ais<?= $anim['id'] ?>" <?= $aislado ?> <?= $has_anim ? '' : 'disabled' ?>>
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
                <button type="submit" class="btn btn-warning btn-lg px-5">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const animalChecks = document.querySelectorAll('.animal-check');
    animalChecks.forEach(check => {
        check.addEventListener('change', function() {
            const id = this.value;
            const conviven = document.getElementById('conv' + id);
            const aislado = document.getElementById('ais' + id);
            
            if (this.checked) {
                conviven.disabled = false;
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
