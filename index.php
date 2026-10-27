<?php
require_once 'includes/db.php';

// Fetch all Nucleos
$stmt = $pdo->query("SELECT * FROM Nucleo ORDER BY id DESC");
$nucleos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Núcleos Familiares</h1>
    <div>
        <a href="analisis_datos.php" class="btn btn-info me-2 text-white"><i class="bi bi-graph-up"></i> Análisis</a>
        <a href="vista_general.php" class="btn btn-success me-2"><i class="bi bi-table"></i> Vista General (Excel)</a>
        <a href="crear_nucleo.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Núcleo</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                      
                        <th>Nombre</th>
                        <th>DPI</th>
                        <th>Teléfono</th>
                        <th>Comunidad</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($nucleos) > 0): ?>
                        <?php foreach ($nucleos as $nucleo): ?>
                            <tr>
                               
                                <td>
                                    <strong><?= htmlspecialchars($nucleo['Nombre']) ?> <?= htmlspecialchars($nucleo['Primer_Apellido']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($nucleo['DPI']) ?></td>
                                <td><?= htmlspecialchars($nucleo['Telefono']) ?></td>
                                <td><?= htmlspecialchars($nucleo['Comunidad_Linguistica']) ?></td>
                                <td class="text-end pe-4">
                                    <a href="ver_nucleo.php?id=<?= $nucleo['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Detalles">
                                        <i class="bi bi-eye"></i> Ver / Agregar Familiares
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No hay núcleos familiares registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
