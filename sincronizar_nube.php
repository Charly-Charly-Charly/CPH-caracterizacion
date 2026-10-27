<?php
// sincronizar_nube.php - Sube los datos de SQLite a MySQL Online
require_once 'includes/db.php';
require_once 'config.php';
require_once 'includes/header.php';

$mensaje = "";
$detalles = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conexión a MySQL
        $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_NAME . ";charset=utf8mb4";
        $pdo_mysql = new PDO($dsn, MYSQL_USER, MYSQL_PASS);
        $pdo_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 0. Desactivar llaves foráneas para facilitar la subida masiva
        $pdo_mysql->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Lista de tablas a sincronizar (Ordenadas por dependencias)
        $tablas = [
            'Enfermedades', 'Trabajos', 'Fuentes_Ingreso', 'Energia_Cocina', 
            'Alumbrado', 'Agua_Hogar', 'Agua_Consumo', 'Ubicacion_Animales', 
            'Tipo_Sanitario', 'Aguas_Grises', 'Manejo_Basura', 
            'Animales_Domesticos', 'Ganado', 'Cultivos', 'Arboles', 'Actividades_Bosque',
            'Nucleo',
            'Familiares',
            'Vivienda',
            'Fallecidos',
            'Cultivo_Arboles',
            'Familiar_Enfermedades',
            'Familiar_Trabajos',
            'Familiar_Fuentes',
            'Vivienda_Energia_Cocina',
            'Vivienda_Alumbrado',
            'Vivienda_Agua_Hogar',
            'Vivienda_Agua_Consumo',
            'Vivienda_Animales',
            'Vivienda_Sanitario',
            'Vivienda_Aguas_Grises',
            'Vivienda_Basura',
            'Nucleo_Animales_Domesticos',
            'Nucleo_Ganado',
            'Nucleo_Cultivos',
            'Cultivo_Arboles_Detalle',
            'Nucleo_Actividades_Bosque'
        ];

        foreach ($tablas as $tabla) {
            try {
                // 1. Obtener datos de SQLite (solo nuevos o modificados)
                $stmt_sq = $pdo->query("SELECT * FROM $tabla WHERE sincronizado = 0");
                $rows = $stmt_sq->fetchAll(PDO::FETCH_ASSOC);

                if (empty($rows)) {
                    $detalles[$tabla] = 0;
                    continue;
                }

                // 2. Insertar en MySQL usando ON DUPLICATE KEY UPDATE (para aplicar ediciones)
                $cols = array_keys($rows[0]);
                $placeholders = ":" . implode(", :", $cols);
                
                $update_parts = [];
                foreach ($cols as $col) {
                    if ($col !== 'id') {
                        $update_parts[] = "$col = VALUES($col)";
                    }
                }
                $update_str = implode(", ", $update_parts);

                $sql = "INSERT INTO $tabla (" . implode(", ", $cols) . ") VALUES ($placeholders) ON DUPLICATE KEY UPDATE $update_str";
                $stmt_my = $pdo_mysql->prepare($sql);

                $count = 0;
                foreach ($rows as $row) {
                    if ($stmt_my->execute($row)) {
                        // 3. Marcar como sincronizado localmente
                        $stmt_update_local = $pdo->prepare("UPDATE $tabla SET sincronizado = 1 WHERE id = :id");
                        $stmt_update_local->execute([':id' => $row['id']]);
                        $count++;
                    }
                }
                $detalles[$tabla] = $count;
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'no such column: sincronizado') !== false) {
                    $detalles[$tabla] = "⚠️ Error: Falta columna de sincronización. Por favor ejecuta el <a href='reparar_sistema.php'>Reparador de Sistema</a>.";
                } else {
                    $detalles[$tabla] = "❌ Error: " . $e->getMessage();
                }
            }
        }

        // 4. Reactivar llaves foráneas
        $pdo_mysql->exec("SET FOREIGN_KEY_CHECKS = 1");
        $mensaje = "Proceso finalizado. Revisa los detalles abajo para confirmar qué tablas se sincronizaron.";

    } catch (PDOException $e) {
        $mensaje = "Error de conexión/sincronización: " . $e->getMessage();
    }
}
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white py-3">
        <h5 class="mb-0"><i class="bi bi-cloud-arrow-up me-2"></i> Sincronizar con MySQL Online (phpMyAdmin)</h5>
    </div>
    <div class="card-body p-4">
        <p class="text-muted">Este proceso toma los datos que tienes en esta computadora (y los que hayas unificado de otros compañeros) y los sube a tu servidor en Hostinger.</p>
        
        <div class="alert alert-info">
            <strong>Configuración Actual:</strong><br>
            <small>Host: <?= MYSQL_HOST ?> | Base de Datos: <?= MYSQL_NAME ?></small>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?= strpos($mensaje, 'éxito') !== false ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                <?= $mensaje ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <button type="submit" class="btn btn-success btn-lg px-5">
                <i class="bi bi-cloud-upload me-2"></i> Iniciar Subida a la Nube
            </button>
        </form>

        <?php if (!empty($detalles)): ?>
            <div class="mt-5">
                <h6 class="border-bottom pb-2 mb-3">Registros nuevos enviados a la nube:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Módulo / Tabla</th>
                                <th>Subidos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $t => $c): ?>
                                <?php if ($c > 0 || in_array($t, ['Nucleo', 'Familiares', 'Vivienda'])): ?>
                                <tr>
                                    <td class="text-start"><?= $t ?></td>
                                    <td><span class="badge <?= $c > 0 ? 'bg-success' : 'bg-light text-muted' ?>"><?= $c ?></span></td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-body">
                <h6><i class="bi bi-check-circle me-1"></i> ¿Qué hace este proceso?</h6>
                <ul class="small mt-2 mb-0">
                    <li>Verifica cada registro en tu computadora.</li>
                    <li>Si el registro (UUID) no existe en phpMyAdmin, lo crea.</li>
                    <li>Si ya existe, lo ignora (para no duplicar datos).</li>
                    <li>En la nube, MySQL le asignará automáticamente un **ID Secuencial** para que lo veas ordenado.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
