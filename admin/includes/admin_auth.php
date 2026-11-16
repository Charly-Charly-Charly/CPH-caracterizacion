<?php
// admin/includes/admin_auth.php - Protege el panel de admin con su propio usuario/contraseña,
// separado del usuario de campo (AUTH_USER/AUTH_PASS_HASH).

require_once __DIR__ . '/../../includes/env.php';
load_env(__DIR__ . '/../../.env');

define('ADMIN_USER', env('ADMIN_USER', 'admin'));
define('ADMIN_PASS_HASH', env('ADMIN_PASS_HASH', ''));

if (session_status() === PHP_SESSION_NONE) {
    session_name('COPADEH_ADMIN');
    session_start();
}

$admin_paginas_publicas = ['login.php'];
$admin_script_actual = basename($_SERVER['SCRIPT_NAME'] ?? '');

if (!in_array($admin_script_actual, $admin_paginas_publicas, true) && empty($_SESSION['admin_autenticado'])) {
    $destino = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header('Location: login.php?redirect=' . urlencode($destino));
    exit;
}

function admin_cerrar_sesion() {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
