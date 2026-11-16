<?php
require_once __DIR__ . '/includes/admin_auth.php';

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'index.php';

    if (ADMIN_PASS_HASH !== '' && hash_equals(ADMIN_USER, $usuario) && password_verify($password, ADMIN_PASS_HASH)) {
        $_SESSION['admin_autenticado'] = true;
        $_SESSION['admin_usuario'] = $usuario;
        header('Location: ' . $redirect);
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Admin - Caracterizaciones COPADEH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
            <div class="card-body p-4">
                <h4 class="mb-3 text-center"><i class="bi bi-shield-lock"></i> Panel de Admin</h4>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (ADMIN_PASS_HASH === ''): ?>
                    <div class="alert alert-warning">
                        No hay contraseña de admin configurada. Edita el archivo <code>.env</code> y define <code>ADMIN_USER</code> / <code>ADMIN_PASS_HASH</code> (usa <code>php generar_hash_password.php "tu_contraseña"</code>).
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="usuario" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
