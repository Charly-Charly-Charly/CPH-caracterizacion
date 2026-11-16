<?php
require_once __DIR__ . '/includes/admin_auth.php';
admin_cerrar_sesion();
header('Location: login.php');
exit;
