<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';

$comision_id = $_GET['id'] ?? $_POST['id'] ?? '';
$stmt = $admin_pdo->prepare("SELECT * FROM Comisiones WHERE id = ?");
$stmt->execute([$comision_id]);
$comision = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comision) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $mysql_host = trim($_POST['mysql_host'] ?? '');
    $mysql_user = trim($_POST['mysql_user'] ?? '');
    $mysql_pass = $_POST['mysql_pass'] ?? '';
    $mysql_name = trim($_POST['mysql_name'] ?? '');

    if ($nombre === '' || $mysql_host === '' || $mysql_user === '' || $mysql_name === '') {
        $error = 'Completa el nombre de la comisión y los datos de MySQL (host, usuario, nombre de base).';
    } else {
        // El slug no cambia: ya se usó para generar los PC_ID existentes y
        // cambiarlo rompería la relación con las PCs ya creadas.
        try {
            $stmt = $admin_pdo->prepare("UPDATE Comisiones SET nombre = ?, mysql_host = ?, mysql_user = ?, mysql_pass = ?, mysql_name = ? WHERE id = ?");
            $stmt->execute([$nombre, $mysql_host, $mysql_user, $mysql_pass, $mysql_name, $comision_id]);
            header('Location: comision.php?id=' . urlencode($comision_id));
            exit;
        } catch (PDOException $e) {
            $error = 'No se pudo guardar: ' . $e->getMessage();
        }
        $comision['nombre'] = $nombre;
        $comision['mysql_host'] = $mysql_host;
        $comision['mysql_user'] = $mysql_user;
        $comision['mysql_pass'] = $mysql_pass;
        $comision['mysql_name'] = $mysql_name;
    }
}

$titulo = 'Editar comisión';
require __DIR__ . '/includes/admin_header.php';
?>

<p><a href="comision.php?id=<?= urlencode($comision_id) ?>">&larr; <?= htmlspecialchars($comision['nombre']) ?></a></p>
<h2 class="mb-4">Editar comisión</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($comision_id) ?>">
            <div class="mb-3">
                <label class="form-label">Nombre de la comisión</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($comision['nombre']) ?>" required autofocus>
            </div>
            <p class="text-muted small">Slug: <code><?= htmlspecialchars($comision['slug']) ?></code> (no se puede cambiar, ya se usó para generar los identificadores de PC).</p>

            <hr>
            <h6 class="text-muted">Base de datos MySQL (ya creada en cPanel/phpMyAdmin)</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Host</label>
                    <input type="text" name="mysql_host" class="form-control" value="<?= htmlspecialchars($comision['mysql_host']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de la base</label>
                    <input type="text" name="mysql_name" class="form-control" value="<?= htmlspecialchars($comision['mysql_name']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Usuario MySQL</label>
                    <input type="text" name="mysql_user" class="form-control" value="<?= htmlspecialchars($comision['mysql_user']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña MySQL</label>
                    <input type="text" name="mysql_pass" class="form-control" value="<?= htmlspecialchars($comision['mysql_pass']) ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="comision.php?id=<?= urlencode($comision_id) ?>" class="btn btn-link">Cancelar</a>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
