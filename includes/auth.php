<?php
// includes/auth.php - Protege el sistema con usuario/contraseña locales

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginas_publicas = ['login.php'];
$script_actual = basename($_SERVER['SCRIPT_NAME']);

if (!in_array($script_actual, $paginas_publicas, true) && empty($_SESSION['autenticado'])) {
    $destino = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header('Location: login.php?redirect=' . urlencode($destino));
    exit;
}

function cerrar_sesion() {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
