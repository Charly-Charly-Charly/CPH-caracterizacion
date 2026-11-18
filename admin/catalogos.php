<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';
require_once __DIR__ . '/includes/catalogos.php';

$comision_id = $_GET['id'] ?? '';
$stmt = $admin_pdo->prepare("SELECT * FROM Comisiones WHERE id = ?");
$stmt->execute([$comision_id]);
$comision = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comision) {
    header('Location: index.php');
    exit;
}

$mensaje = $_GET['guardado'] ?? '';
$definiciones = catalogos_definiciones();

$titulo = 'Campos de ' . $comision['nombre'];
require __DIR__ . '/includes/admin_header.php';
?>

<p><a href="comision.php?id=<?= urlencode($comision_id) ?>">&larr; <?= htmlspecialchars($comision['nombre']) ?></a></p>
<h2 class="mb-1">Campos colectivos de esta comisión</h2>
<p class="text-muted">
    Personaliza las opciones de enfermedades, trabajos, animales, cultivos, etc. que tendrán
    los formularios de las PCs de <strong><?= htmlspecialchars($comision['nombre']) ?></strong>.
    Un campo sin cambios usa los valores por defecto del sistema. Estos cambios solo afectan
    a los ZIPs que generes <strong>después</strong> de guardar (no a los ya descargados).
</p>

<?php if ($mensaje === '1'): ?>
    <div class="alert alert-success">Catálogos guardados.</div>
<?php elseif ($mensaje === 'restaurado'): ?>
    <div class="alert alert-success">Se restauraron los valores por defecto.</div>
<?php endif; ?>

<form method="POST" action="guardar_catalogos.php">
<input type="hidden" name="comision_id" value="<?= htmlspecialchars($comision_id) ?>">

<div class="accordion" id="acordeonCatalogos">
<?php foreach ($definiciones as $tabla => $def):
    $valores = catalogos_valores_efectivos($admin_pdo, $comision_id, $tabla);
    $es_personalizado = !empty(catalogos_valores_personalizados($admin_pdo, $comision_id, $tabla));
    $lineas = array_map(function ($v) use ($def) {
        if ($def['categoria'] && $v['categoria']) {
            return $v['nombre'] . ' | ' . $v['categoria'];
        }
        return $v['nombre'];
    }, $valores);
?>
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cat-<?= $tabla ?>">
                <?= htmlspecialchars($def['etiqueta']) ?>
                <?php if ($es_personalizado): ?>
                    <span class="badge bg-info text-dark ms-2">personalizado</span>
                <?php endif; ?>
            </button>
        </h2>
        <div id="cat-<?= $tabla ?>" class="accordion-collapse collapse">
            <div class="accordion-body">
                <?php if ($def['categoria']): ?>
                    <p class="text-muted small">Un valor por línea. Para incluir categoría, usa el formato <code>Nombre | CATEGORIA</code>.</p>
                <?php else: ?>
                    <p class="text-muted small">Un valor por línea.</p>
                <?php endif; ?>
                <textarea name="catalogos[<?= $tabla ?>]" class="form-control" rows="6"><?= htmlspecialchars(implode("\n", $lineas)) ?></textarea>
                <?php if ($es_personalizado): ?>
                    <button type="submit" name="restaurar" value="<?= $tabla ?>" class="btn btn-sm btn-outline-secondary mt-2" formnovalidate>
                        Restaurar valores por defecto
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<button type="submit" class="btn btn-primary mt-4"><i class="bi bi-save"></i> Guardar catálogos</button>
</form>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
