<?php
// unificar.php - Herramienta para fusionar bases de datos de diferentes computadoras
require_once 'includes/db.php';
require_once 'includes/header.php';

$mensaje = "";
$detalles = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['db_file'])) {
    $file = $_FILES['db_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $file['tmp_name'];
        $external_db_path = __DIR__ . '/temp_merge_' . time() . '.sqlite';
        
        if (move_uploaded_file($tmp_name, $external_db_path)) {
            try {
                // Adjuntar la base de datos externa
                $pdo->exec("ATTACH DATABASE '$external_db_path' AS external");
                
                // Lista de tablas a unificar (ordenadas por dependencias)
                $tablas = [
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

                $pdo->beginTransaction();
                foreach ($tablas as $tabla) {
                    $sql = "INSERT OR IGNORE INTO main.$tabla SELECT * FROM external.$tabla";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $count = $stmt->rowCount();
                    $detalles[$tabla] = $count;
                }
                $pdo->commit();
                
                $pdo->exec("DETACH DATABASE external");
                unlink($external_db_path);
                
                $mensaje = "Base de datos unificada con éxito.";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $mensaje = "Error al unificar: " . $e->getMessage();
            }
        } else {
            $mensaje = "Error al mover el archivo subido.";
        }
    } else {
        $mensaje = "Error en la subida del archivo.";
    }
}
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-merge me-2"></i> Unificador de Datos (Modo Offline)</h5>
    </div>
    <div class="card-body p-4">
        <p class="text-muted">Utiliza esta herramienta para integrar los datos recolectados en otras computadoras. Sube el archivo <code>database.sqlite</code> de tu compañero.</p>
        
        <?php if ($mensaje): ?>
            <div class="alert <?= strpos($mensaje, 'éxito') !== false ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                <?= $mensaje ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Seleccionar archivo database.sqlite</label>
                <input type="file" name="db_file" class="form-control" accept=".sqlite,.db" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-cloud-upload me-2"></i> Iniciar Unificación
            </button>
        </form>

        <?php if (!empty($detalles)): ?>
            <div class="mt-5">
                <h6 class="border-bottom pb-2 mb-3">Resultados de la última importación:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Tabla</th>
                                <th>Registros Nuevos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $t => $c): ?>
                                <tr>
                                    <td class="text-start"><?= $t ?></td>
                                    <td><span class="badge <?= $c > 0 ? 'bg-success' : 'bg-light text-muted' ?>"><?= $c ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card border-info h-100">
            <div class="card-body">
                <h6 class="text-info"><i class="bi bi-info-circle me-1"></i> Instrucciones</h6>
                <ul class="small mt-2 mb-0">
                    <li>Asegúrate de que el archivo que subes se llame exactamente <code>database.sqlite</code>.</li>
                    <li>El sistema saltará automáticamente cualquier registro que ya exista (evita duplicados).</li>
                    <li>Es recomendable hacer un respaldo de tu propia base de datos antes de unificar.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-warning h-100">
            <div class="card-body">
                <h6 class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Importante</h6>
                <p class="small mt-2 mb-0">Este proceso es irreversible. Al unificar, los datos de la otra computadora se mezclarán permanentemente con los tuyos.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
