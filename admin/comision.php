<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';

$comision_id = $_GET['id'] ?? '';
$stmt = $admin_pdo->prepare("SELECT * FROM Comisiones WHERE id = ?");
$stmt->execute([$comision_id]);
$comision = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comision) {
    header('Location: index.php');
    exit;
}

$stmt = $admin_pdo->prepare("SELECT * FROM Comision_PCs WHERE comision_id = ? ORDER BY creado_el ASC");
$stmt->execute([$comision_id]);
$pcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$siguiente_numero = count($pcs) + 1;

// No se limpia aquí: se deja para que hoja_credenciales.php la lea una sola vez
// (si el admin recarga esta página sin haber visto la hoja, todavía puede verla).
// Solo se muestra si pertenece a ESTA comisión (evita que credenciales de otra
// comisión, nunca vistas en la hoja imprimible, aparezcan aquí por error).
$flash = $_SESSION['flash_credenciales'] ?? null;
$flash_credenciales = (isset($flash['comision_id'], $flash['pcs']) && $flash['comision_id'] === $comision_id) ? $flash['pcs'] : null;

$titulo = $comision['nombre'];
require __DIR__ . '/includes/admin_header.php';
?>

<p><a href="index.php">&larr; Comisiones</a></p>
<div class="d-flex justify-content-between align-items-start">
    <div>
        <h2 class="mb-1"><?= htmlspecialchars($comision['nombre']) ?></h2>
        <p class="text-muted">
            Slug: <code><?= htmlspecialchars($comision['slug']) ?></code> &middot;
            MySQL: <?= htmlspecialchars($comision['mysql_host']) ?> / <?= htmlspecialchars($comision['mysql_name']) ?>
        </p>
    </div>
    <div>
        <a href="crear_tablas_comision.php?id=<?= urlencode($comision_id) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-database-add"></i> Crear/verificar base de datos
        </a>
        <a href="catalogos.php?id=<?= urlencode($comision_id) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list-check"></i> Campos colectivos
        </a>
        <a href="editar_comision.php?id=<?= urlencode($comision_id) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil"></i> Editar comisión
        </a>
    </div>
</div>

<?php if ($flash_credenciales): ?>
<div class="alert alert-success">
    <h5><i class="bi bi-check-circle"></i> PCs creadas — anota estas contraseñas, no se volverán a mostrar</h5>
    <table class="table table-sm bg-white mb-2">
        <thead><tr><th>PC</th><th>Usuario</th><th>Contraseña</th></tr></thead>
        <tbody>
            <?php foreach ($flash_credenciales as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['nombre']) ?> (<code><?= htmlspecialchars($f['pc_id']) ?></code>)</td>
                <td><?= htmlspecialchars($f['auth_user']) ?></td>
                <td><code><?= htmlspecialchars($f['password']) ?></code></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="hoja_credenciales.php" target="_blank" class="btn btn-sm btn-outline-success">
        <i class="bi bi-printer"></i> Ver hoja imprimible
    </a>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">Computadoras de esta comisión</div>
    <div class="card-body">
        <?php if (empty($pcs)): ?>
            <p class="text-muted">Todavía no hay PCs registradas.</p>
        <?php else: ?>
        <table class="table">
            <thead><tr><th>Nombre</th><th>PC ID</th><th>Usuario</th><th>ZIP</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($pcs as $pc): ?>
                <tr>
                    <td><?= htmlspecialchars($pc['nombre']) ?></td>
                    <td><code><?= htmlspecialchars($pc['pc_id']) ?></code></td>
                    <td><?= htmlspecialchars($pc['auth_user']) ?></td>
                    <td>
                        <?= $pc['zip_generado_el'] ? htmlspecialchars($pc['zip_generado_el']) : '<span class="text-muted">no generado</span>' ?>
                    </td>
                    <td class="text-end">
                        <a href="descargar_zip.php?pc_id=<?= urlencode($pc['id']) ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-file-earmark-zip"></i> <?= $pc['zip_generado_el'] ? 'Regenerar y descargar' : 'Generar ZIP' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">Agregar PCs</div>
    <div class="card-body">
        <div class="mb-3" style="max-width: 300px;">
            <label class="form-label">¿Cuántas computadoras van a territorio?</label>
            <div class="input-group">
                <input type="number" id="cantidad_pcs" class="form-control" min="1" max="50" value="1">
                <button type="button" class="btn btn-outline-secondary" onclick="generarFilas()">Generar filas</button>
            </div>
        </div>

        <form method="POST" action="guardar_pcs.php">
            <input type="hidden" name="comision_id" value="<?= htmlspecialchars($comision_id) ?>">
            <table class="table" id="tabla_pcs" style="display:none;">
                <thead><tr><th>Nombre de la PC</th><th>Usuario</th><th>Contraseña</th></tr></thead>
                <tbody id="filas_pcs"></tbody>
            </table>
            <button type="submit" class="btn btn-success" id="btn_guardar" style="display:none;">
                <i class="bi bi-save"></i> Guardar PCs
            </button>
        </form>
    </div>
</div>

<script>
const siguienteNumero = <?= (int)$siguiente_numero ?>;

function generarContrasenaAleatoria() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    let pass = '';
    for (let i = 0; i < 10; i++) pass += chars[Math.floor(Math.random() * chars.length)];
    return pass;
}

function generarFilas() {
    const cantidad = parseInt(document.getElementById('cantidad_pcs').value, 10) || 1;
    const filas = document.getElementById('filas_pcs');
    filas.innerHTML = '';
    for (let i = 0; i < cantidad; i++) {
        const numero = String(siguienteNumero + i).padStart(2, '0');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="nombre[]" class="form-control" value="PC-${numero}" required></td>
            <td><input type="text" name="usuario[]" class="form-control" value="usuario${numero}" required></td>
            <td><input type="text" name="password[]" class="form-control" value="${generarContrasenaAleatoria()}" required></td>
        `;
        filas.appendChild(tr);
    }
    document.getElementById('tabla_pcs').style.display = '';
    document.getElementById('btn_guardar').style.display = '';
}
</script>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
