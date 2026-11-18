<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';

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
        $slug = admin_slug($nombre);
        $id = admin_generar_uuid();
        try {
            $stmt = $admin_pdo->prepare("INSERT INTO Comisiones (id, nombre, slug, mysql_host, mysql_user, mysql_pass, mysql_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $nombre, $slug, $mysql_host, $mysql_user, $mysql_pass, $mysql_name]);
            header('Location: crear_tablas_comision.php?id=' . urlencode($id) . '&recien_creada=1');
            exit;
        } catch (PDOException $e) {
            $error = (strpos($e->getMessage(), 'UNIQUE') !== false)
                ? 'Ya existe una comisión con un nombre muy similar (el identificador generado se repite). Usa un nombre distinto.'
                : 'No se pudo crear la comisión: ' . $e->getMessage();
        }
    }
}

$titulo = 'Nueva comisión';
require __DIR__ . '/includes/admin_header.php';
?>

<h2 class="mb-4">Nueva comisión</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nombre de la comisión</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required autofocus>
            </div>

            <hr>
            <h6 class="text-muted">Base de datos MySQL (ya creada en cPanel/phpMyAdmin)</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Host</label>
                    <input type="text" name="mysql_host" class="form-control" value="<?= htmlspecialchars($_POST['mysql_host'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de la base</label>
                    <input type="text" name="mysql_name" class="form-control" value="<?= htmlspecialchars($_POST['mysql_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Usuario MySQL</label>
                    <input type="text" name="mysql_user" class="form-control" value="<?= htmlspecialchars($_POST['mysql_user'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña MySQL</label>
                    <input type="text" name="mysql_pass" class="form-control" value="<?= htmlspecialchars($_POST['mysql_pass'] ?? '') ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Crear comisión</button>
            <a href="index.php" class="btn btn-link">Cancelar</a>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
