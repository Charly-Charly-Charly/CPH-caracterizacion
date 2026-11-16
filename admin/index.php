<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';

$comisiones = $admin_pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM Comision_PCs WHERE comision_id = c.id) AS total_pcs
    FROM Comisiones c ORDER BY c.creado_el DESC
")->fetchAll(PDO::FETCH_ASSOC);

$titulo = 'Comisiones';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Comisiones</h2>
    <a href="crear_comision.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva comisión</a>
</div>

<?php if (empty($comisiones)): ?>
    <div class="alert alert-info">Todavía no hay comisiones creadas.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($comisiones as $c): ?>
            <a href="comision.php?id=<?= urlencode($c['id']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                    <div class="text-muted small"><?= htmlspecialchars($c['slug']) ?> &middot; base MySQL: <?= htmlspecialchars($c['mysql_name']) ?></div>
                </div>
                <span class="badge bg-primary rounded-pill"><?= (int)$c['total_pcs'] ?> PC<?= $c['total_pcs'] == 1 ? '' : 's' ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
