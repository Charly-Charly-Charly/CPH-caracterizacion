<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) . ' - ' : '' ?>Panel de Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-shield-lock"></i> Panel de Admin</a>
            <div>
                <a class="nav-link d-inline text-light" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Salir<?= isset($_SESSION['admin_usuario']) ? ' (' . htmlspecialchars($_SESSION['admin_usuario']) . ')' : '' ?>
                </a>
            </div>
        </div>
    </nav>
    <div class="container my-5">
