<?php
// admin/crear_tablas_comision.php - Fase de "Crear la base": conecta a la base
// MySQL de la comisión (ya creada en cPanel/phpMyAdmin) y crea ahí todas las
// tablas del sistema, para que las PCs de campo no fallen al sincronizar por
// tablas inexistentes (ej. "Table '...Enfermedades' doesn't exist").
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/admin_db.php';
require_once dirname(__DIR__) . '/includes/schema.php';

$comision_id = $_GET['id'] ?? $_POST['id'] ?? '';
$stmt = $admin_pdo->prepare("SELECT * FROM Comisiones WHERE id = ?");
$stmt->execute([$comision_id]);
$comision = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comision) {
    header('Location: index.php');
    exit;
}

$recien_creada = isset($_GET['recien_creada']);
$log = [];
$error = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dsn = "mysql:host=" . $comision['mysql_host'] . ";dbname=" . $comision['mysql_name'] . ";charset=utf8mb4";
        $pdo_mysql = new PDO($dsn, $comision['mysql_user'], $comision['mysql_pass']);
        $pdo_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $log[] = "✅ Conexión exitosa a " . $comision['mysql_host'] . " / " . $comision['mysql_name'];

        crear_tablas_mysql($pdo_mysql, function ($mensaje) use (&$log) {
            $log[] = $mensaje;
        });

        reparar_schema_mysql($pdo_mysql, function ($mensaje) use (&$log) {
            $log[] = $mensaje;
        });

        $log[] = "✅ Base de datos lista para recibir sincronizaciones.";
        $exito = true;
    } catch (PDOException $e) {
        $error = "No se pudo conectar o crear las tablas: " . $e->getMessage();
    }
}

$titulo = 'Crear base de datos - ' . $comision['nombre'];
require __DIR__ . '/includes/admin_header.php';
?>

<p><a href="comision.php?id=<?= urlencode($comision_id) ?>">&larr; <?= htmlspecialchars($comision['nombre']) ?></a></p>
<h2 class="mb-1">Crear la base de datos</h2>
<p class="text-muted">
    Crea todas las tablas del sistema en la base MySQL de
    <strong><?= htmlspecialchars($comision['nombre']) ?></strong>
    (<?= htmlspecialchars($comision['mysql_host']) ?> / <?= htmlspecialchars($comision['mysql_name']) ?>).
    Es necesario hacerlo <strong>antes</strong> de que las PCs de campo intenten sincronizar,
    si no, la sincronización falla con un error de "tabla no existe".
</p>

<?php if ($recien_creada && !$exito): ?>
<div class="alert alert-info">
    La comisión se creó correctamente. Antes de generar PCs, crea la base de datos.
</div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($exito): ?>
    <div class="alert alert-success">Las tablas se crearon/verificaron correctamente.</div>
<?php endif; ?>

<?php if (!empty($log)): ?>
    <div class="card mb-4">
        <div class="card-header">Detalle</div>
        <ul class="list-group list-group-flush">
            <?php foreach ($log as $linea): ?>
                <li class="list-group-item small"><?= htmlspecialchars($linea) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($comision_id) ?>">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-database-add"></i> <?= $exito ? 'Volver a crear/verificar las tablas' : 'Crear la base de datos' ?>
    </button>
    <a href="comision.php?id=<?= urlencode($comision_id) ?>" class="btn btn-link">
        <?= $exito ? 'Continuar' : 'Omitir por ahora' ?>
    </a>
</form>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
