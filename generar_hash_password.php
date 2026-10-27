<?php
// generar_hash_password.php - Genera el hash para AUTH_PASS_HASH en .env
// Uso: php generar_hash_password.php "nueva_contraseña"

if ($argc < 2) {
    echo "Uso: php generar_hash_password.php \"tu_contraseña\"\n";
    exit(1);
}

echo password_hash($argv[1], PASSWORD_DEFAULT) . "\n";
