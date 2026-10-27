<?php
$flash_error = $_SESSION['flash_error'] ?? null;
$flash_datos = $_SESSION['flash_datos'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_datos']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Caracterizaciones COPADEH</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary premium-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-people-fill"></i> Caracterizaciones COPADEH</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analisis_datos.php">Análisis de Datos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="unificar.php"><i class="bi bi-merge"></i> Unificar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sincronizar_nube.php"><i class="bi bi-cloud-arrow-up text-info"></i> Sincronizar Cloud</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <span class="nav-link badge bg-light text-primary px-3 py-2 mt-1">
                            <i class="bi bi-laptop me-1"></i> <?= defined('PC_ID') ? PC_ID : 'Sin Nombre' ?>
                        </span>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link" href="logout.php" title="Cerrar sesión">
                            <i class="bi bi-box-arrow-right"></i> Salir<?= isset($_SESSION['usuario']) ? ' (' . htmlspecialchars($_SESSION['usuario']) . ')' : '' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php if ($flash_error): ?>
    <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flash_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($flash_datos): ?>
    <script>window.__datosFormulario = <?= json_encode($flash_datos) ?>;</script>
    <?php endif; ?>
    <div class="container my-5">
