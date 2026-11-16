<?php
require_once __DIR__ . '/includes/admin_auth.php';

$credenciales = $_SESSION['flash_credenciales'] ?? [];
unset($_SESSION['flash_credenciales']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de credenciales</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; }
        table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
        th, td { border: 1px solid #333; padding: 8px 12px; text-align: left; }
        th { background: #eee; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <h2>Credenciales de computadoras - Caracterizaciones COPADEH</h2>
    <p>Fecha de generación: <?= date('d/m/Y H:i') ?></p>

    <?php if (empty($credenciales)): ?>
        <p><strong>Esta hoja ya fue vista y las contraseñas ya no están disponibles aquí.</strong> Si necesitas verlas de nuevo, tendrás que regenerar las PCs con una contraseña nueva.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>PC</th><th>Identificador (PC_ID)</th><th>Usuario</th><th>Contraseña</th></tr></thead>
            <tbody>
                <?php foreach ($credenciales as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['pc_id']) ?></td>
                    <td><?= htmlspecialchars($c['auth_user']) ?></td>
                    <td><?= htmlspecialchars($c['password']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button onclick="window.print()">Imprimir</button>
    <?php endif; ?>
</body>
</html>
