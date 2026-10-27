<?php
// config.php - Configuración general del sistema (valores reales en .env, NO en este archivo)

require_once __DIR__ . '/includes/env.php';
load_env(__DIR__ . '/.env');

// Identificación de esta computadora (ayuda a saber quién ingresó qué dato al unificar)
define('PC_ID', env('PC_ID', 'PC-SIN-NOMBRE'));

// Configuración de la base de datos local (offline)
define('DB_TYPE', 'sqlite');
define('SQLITE_PATH', __DIR__ . '/database.sqlite');

// Datos para MySQL (usados solo al sincronizar, cuando haya internet)
define('MYSQL_HOST', env('MYSQL_HOST', ''));
define('MYSQL_USER', env('MYSQL_USER', ''));
define('MYSQL_PASS', env('MYSQL_PASS', ''));
define('MYSQL_NAME', env('MYSQL_NAME', ''));

// Autenticación local de esta PC (AUTH_PASS_HASH es un hash de password_hash(), no la contraseña en texto plano)
define('AUTH_USER', env('AUTH_USER', 'admin'));
define('AUTH_PASS_HASH', env('AUTH_PASS_HASH', ''));

require_once __DIR__ . '/includes/auth.php';
?>
