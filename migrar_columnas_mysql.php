<?php
/**
 * migrar_columnas_mysql.php
 * 
 * Aplica las columnas faltantes a las tablas MySQL existentes.
 * Ejecutar UNA vez para corregir los errores de sincronización.
 */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración de Columnas MySQL</title>
    <style>
        body { font-family: monospace; background: #1a1a2e; color: #eee; padding: 2rem; }
        h2 { color: #48bb78; }
        .ok  { color: #48bb78; }
        .skip{ color: #718096; }
        .err { color: #fc8181; }
        .box { background: #2d3748; border-radius: 8px; padding: 1.5rem; margin-top: 1rem; }
        a.btn { display:inline-block; margin-top:1.5rem; padding:.7rem 1.5rem;
                background:#3182ce; color:#fff; border-radius:6px; text-decoration:none; }
    </style>
</head>
<body>
<h2>🛠️ Migración de Columnas MySQL</h2>
<div class="box">
<?php

try {
    $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_NAME . ";charset=utf8mb4";
    $pdo_mysql = new PDO($dsn, MYSQL_USER, MYSQL_PASS);
    $pdo_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p class='ok'>✅ Conectado a: " . MYSQL_HOST . " / " . MYSQL_NAME . "</p><hr>";

    // ── Columnas a agregar por tabla ──────────────────────────────────────────
    $migraciones = [
        'Nucleo' => [
            'Tercer_Nombre'   => 'VARCHAR(100)',
            'Desconoce_Fecha' => 'TINYINT DEFAULT 0',
            'Edad'            => 'INT',
            'sincronizado'    => 'INT DEFAULT 0',
            'actualizado_el'  => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Familiares' => [
            'Doble_Nacionalidad'          => 'TINYINT DEFAULT 0',
            'Fuera_comunidad'             => 'VARCHAR(100)',
            'Donde_reside'                => 'VARCHAR(100)',
            'Tiene_DPI'                   => 'INT DEFAULT 0',
            'Tiene_Certificado_Nacimiento'=> 'INT DEFAULT 0',
            'Tiene_Pasaporte'             => 'INT DEFAULT 0',
            'Desconoce_Fecha'             => 'TINYINT DEFAULT 0',
            'Edad'                        => 'INT',
            'sincronizado'                => 'INT DEFAULT 0',
            'actualizado_el'              => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Fallecidos' => [
            'Parentesco'      => 'VARCHAR(50)',
            'Desconoce_Fecha' => 'TINYINT DEFAULT 0',
            'Edad'            => 'INT',
            'sincronizado'    => 'INT DEFAULT 0',
            'actualizado_el'  => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Vivienda' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Nucleo_Ganado' => [
            'es_personal'    => 'TINYINT DEFAULT 0',
            'es_comercial'   => 'TINYINT DEFAULT 0',
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Nucleo_Cultivos' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Cultivo_Arboles' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Cultivo_Arboles_Detalle' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Familiar_Enfermedades' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Familiar_Trabajos' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Familiar_Fuentes' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Nucleo_Animales_Domesticos' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'Nucleo_Actividades_Bosque' => [
            'sincronizado'   => 'INT DEFAULT 0',
            'actualizado_el' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
    ];

    $total_added  = 0;
    $total_skipped = 0;
    $total_errors  = 0;

    foreach ($migraciones as $tabla => $columnas) {
        echo "<h4 style='color:#90cdf4; margin-top:1.2rem;'>📋 $tabla</h4><ul>";
        foreach ($columnas as $col => $tipo) {
            try {
                $pdo_mysql->exec("ALTER TABLE `$tabla` ADD COLUMN `$col` $tipo");
                echo "<li class='ok'>✅ Columna <strong>$col</strong> añadida.</li>";
                $total_added++;
            } catch (PDOException $e) {
                if ($e->getCode() === '42S21') {
                    // Duplicate column — already exists, that's fine
                    echo "<li class='skip'>⏭️ <strong>$col</strong> ya existe (omitido).</li>";
                    $total_skipped++;
                } else {
                    echo "<li class='err'>❌ Error en <strong>$col</strong>: " . htmlspecialchars($e->getMessage()) . "</li>";
                    $total_errors++;
                }
            }
        }
        echo "</ul>";
    }

    echo "<hr>";
    echo "<p class='ok'><strong>Resumen:</strong> $total_added columnas añadidas, $total_skipped ya existían, $total_errors errores.</p>";

    if ($total_errors === 0) {
        echo "<p class='ok'>✅ ¡Migración completada sin errores! Ya puedes sincronizar datos.</p>";
    } else {
        echo "<p class='err'>⚠️ Hubo $total_errors errores. Revisa los detalles arriba.</p>";
    }

} catch (PDOException $e) {
    echo "<p class='err'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
</div>

<a class="btn" href="sincronizar_nube.php">🔄 Ir al Sincronizador</a>
<a class="btn" style="background:#2d3748; margin-left:.5rem;" href="preparar_nube3.php">🛠️ Volver a Preparar Nube</a>
</body>
</html>
