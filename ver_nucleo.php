<?php
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch Nucleo
$stmt = $pdo->prepare("SELECT * FROM Nucleo WHERE id = ?");
$stmt->execute([$id]);
$nucleo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nucleo) {
    header('Location: index.php');
    exit;
}

// Helper function to calculate age
function calcularEdad($fecha, $fechaReferencia = null) {
    if (empty($fecha) || $fecha === '0000-00-00') return 0;

    try {
        $fechaNacimiento = new DateTime($fecha);
        $referencia = $fechaReferencia ? new DateTime($fechaReferencia) : new DateTime();

        if ($fechaNacimiento > $referencia) return 0;

        return $fechaNacimiento->diff($referencia)->y;
    } catch (Exception $e) {
        return 0;
    }
}
// Fetch Familiares
$stmtFam = $pdo->prepare("SELECT * FROM Familiares WHERE nucleo_id = ? ORDER BY id ASC");
$stmtFam->execute([$id]);
$familiares = $stmtFam->fetchAll(PDO::FETCH_ASSOC);
// Fetch Viviendas
$stmtViv = $pdo->prepare("SELECT * FROM Vivienda WHERE nucleo_id = ? ORDER BY id ASC");
$stmtViv->execute([$id]);
$viviendas = $stmtViv->fetchAll(PDO::FETCH_ASSOC);

// Fetch Fallecidos
$stmtFall = $pdo->prepare("SELECT * FROM Fallecidos WHERE nucleo_id = ? ORDER BY id ASC");
$stmtFall->execute([$id]);
$fallecidos = $stmtFall->fetchAll(PDO::FETCH_ASSOC);

// Fetch Producción Agropecuaria
$stmtDom = $pdo->prepare("SELECT ad.nombre FROM Nucleo_Animales_Domesticos nad JOIN Animales_Domesticos ad ON nad.animal_id = ad.id WHERE nad.nucleo_id = ?");
$stmtDom->execute([$id]);
$animales_domesticos = $stmtDom->fetchAll(PDO::FETCH_COLUMN);

$stmtGan = $pdo->prepare("SELECT g.nombre, ng.es_personal, ng.es_comercial FROM Nucleo_Ganado ng JOIN Ganado g ON ng.ganado_id = g.id WHERE ng.nucleo_id = ?");
$stmtGan->execute([$id]);
$ganado = $stmtGan->fetchAll(PDO::FETCH_ASSOC);

$stmtCul = $pdo->prepare("SELECT c.nombre, nc.es_personal, nc.es_comercial FROM Nucleo_Cultivos nc JOIN Cultivos c ON nc.cultivo_id = c.id WHERE nc.nucleo_id = ?");
$stmtCul->execute([$id]);
$cultivos = $stmtCul->fetchAll(PDO::FETCH_ASSOC);

$stmtArb = $pdo->prepare("SELECT a.nombre FROM Cultivo_Arboles_Detalle cad JOIN Arboles a ON cad.arbol_id = a.id JOIN Cultivo_Arboles ca ON cad.cultivo_id = ca.id WHERE ca.nucleo_id = ?");
$stmtArb->execute([$id]);
$arboles = $stmtArb->fetchAll(PDO::FETCH_COLUMN);

$stmtActBos = $pdo->prepare("SELECT ab.nombre FROM Nucleo_Actividades_Bosque nab JOIN Actividades_Bosque ab ON nab.actividad_id = ab.id WHERE nab.nucleo_id = ?");
$stmtActBos->execute([$id]);
$actividades_bosque = $stmtActBos->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">Detalles del Núcleo Familiar de <?= htmlspecialchars($nucleo['Nombre']) ?> <?= htmlspecialchars($nucleo['Primer_Apellido']) ?></h1>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row mb-5">
    <div class="col-12">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Información del Entrevistado</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalNucleo">
                        <i class="bi bi-info-circle"></i> Ver Todo
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <strong class="d-block text-muted small">Nombre Completo</strong>
                        <span><?= htmlspecialchars($nucleo['Nombre'] . ' ' . $nucleo['Primer_Apellido']) ?></span>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong class="d-block text-muted small">DPI</strong>
                        <span><?= htmlspecialchars($nucleo['DPI']) ?></span>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong class="d-block text-muted small">Fecha Nacimiento (Edad)</strong>
                        <span><?= $nucleo['Desconoce_Fecha'] ? 'Desconocida (' . $nucleo['Edad'] . ' años aprox.)' : htmlspecialchars($nucleo['Fecha_Nacimiento']) . ' (' . calcularEdad($nucleo['Fecha_Nacimiento']) . ' años)' ?></span>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong class="d-block text-muted small">Teléfono</strong>
                        <span><?= htmlspecialchars($nucleo['Telefono']) ?: '-' ?></span>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <strong class="d-block text-muted small">Beneficiaria MC</strong>
                        <span><?= htmlspecialchars($nucleo['Persona_Beneficiaria_MC']) ?></span>
                    </div>

                    <div class="col-md-4 mb-3">
                        <strong class="d-block text-muted small">Pertenencia Cultural</strong>
                        <span><?= htmlspecialchars($nucleo['Pertenencia_Cultural']) ?: '-' ?></span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong class="d-block text-muted small">Comunidad Lingüística</strong>
                        <span><?= htmlspecialchars($nucleo['Comunidad_Linguistica']) ?: '-' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-secondary"><i class="bi bi-people"></i> Personas que integran el núcleo familiar</h3>
    <a href="crear_familiar.php?nucleo_id=<?= $nucleo['id'] ?>" class="btn btn-success">
        <i class="bi bi-person-plus"></i> Agregar Familiar
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nombre</th>
                        <th>Parentesco</th>
                        <th>Fecha Nacimiento (Edad)</th>
                        <th>Sexo</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($familiares) > 0): ?>
                        <?php foreach ($familiares as $fam): ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars($fam['Nombre']) ?></td>
                                <td><?= htmlspecialchars($fam['Parentesco']) ?></td>
                                <td>
                                    <?php if ($fam['Desconoce_Fecha']): ?>
                                        Desconocida (<?= $fam['Edad'] ?> años aprox.)
                                    <?php else: ?>
                                        <?= htmlspecialchars($fam['Fecha_Nacimiento']) ?> (<?= calcularEdad($fam['Fecha_Nacimiento']) ?>)
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($fam['Sexo']) ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalFam<?= $fam['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="editar_familiar.php?id=<?= $fam['id'] ?>" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                No hay familiares registrados en este núcleo.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Fallecidos -->
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h3 class="text-secondary"><i class="bi bi-person-x"></i> Familiares Fallecidos</h3>
    <a href="crear_fallecido.php?nucleo_id=<?= $nucleo['id'] ?>" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Registrar Fallecido
    </a>
</div>

<div class="card shadow-sm mb-5 border-dark">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nombre Completo</th>
                        <th>Parentesco</th>
                        <th>Sexo</th>
                        <th>Fecha Nacimiento</th>
                        <th>Fecha Fallecimiento</th>
                        <th>Edad al morir</th>
                        <th>Causa</th>
                        <th>Lugar Inhumación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($fallecidos) > 0): ?>
                        <?php foreach ($fallecidos as $fal): ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars($fal['Nombre']) ?></td>
                                <td><?= htmlspecialchars($fal['Parentesco'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($fal['Sexo'] ?: '-') ?></td>
                                <td><?= $fal['Desconoce_Fecha'] ? 'Desconocida' : htmlspecialchars($fal['Fecha_Nacimiento'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($fal['Fecha_Fallecimiento'] ?: '-') ?></td>
                                <td>
                                    <?php if ($fal['Desconoce_Fecha']): ?>
                                        <?= $fal['Edad'] ?> años aprox.
                                    <?php else: ?>
                                        <?= ($fal['Fecha_Nacimiento'] && $fal['Fecha_Fallecimiento']) ? calcularEdad($fal['Fecha_Nacimiento'], $fal['Fecha_Fallecimiento']) : 0 ?> años
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($fal['Causa_Fallecimiento'] ?: '-') ?></td>
                                <td class="text-end pe-4">
                                    <a href="editar_fallecido.php?id=<?= $fal['id'] ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No hay familiares fallecidos registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Viviendas Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-secondary"><i class="bi bi-house"></i> Viviendas del Núcleo</h3>
    <a href="crear_vivienda.php?nucleo_id=<?= $nucleo['id'] ?>" class="btn btn-info text-white">
        <i class="bi bi-house-add"></i> Agregar Vivienda
    </a>
</div>

<div class="card shadow-sm mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Ambientes</th>
                        <th>Techo / Paredes</th>
                        <th class="text-center">Sanitario</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($viviendas) > 0): ?>
                        <?php foreach ($viviendas as $viv): ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars($viv['Cantidad_Ambientes'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($viv['Techo'] ?: '-') ?> / <?= htmlspecialchars($viv['Paredes'] ?: '-') ?></td>
                                <td class="text-center"><?= $viv['Tiene_Sanitario'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>' ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalViv<?= $viv['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="editar_vivienda.php?id=<?= $viv['id'] ?>" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-house-x fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                No hay viviendas registradas para este núcleo.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Medios de Vida Agropecuarios -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-secondary"><i class="bi bi-tree"></i> Medios de Vida</h3>
    <a href="produccion_nucleo.php?nucleo_id=<?= $nucleo['id'] ?>" class="btn btn-warning text-dark">
        <i class="bi bi-pencil-square"></i> Editar Producción
    </a>
</div>

<div class="card shadow-sm mb-5">
    <div class="card-body">
        <div class="row g-4">
            
            <div class="col-md-4">
                <h6 class="text-warning border-bottom pb-2">Animales Pecuarios</h6>
                <?php if(count($ganado) > 0): ?>
                    <ul class="mb-0">
                        <?php foreach($ganado as $g): 
                            $usos = [];
                            if($g['es_personal']) $usos[] = 'Consumo';
                            if($g['es_comercial']) $usos[] = 'Venta';
                        ?>
                            <li><?= htmlspecialchars($g['nombre']) ?> <span class="text-muted small">(<?= implode(', ', $usos) ?>)</span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small mb-0">No registrada.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <h6 class="text-warning border-bottom pb-2">Cultivos</h6>
                <?php if(count($cultivos) > 0): ?>
                    <ul class="mb-0">
                        <?php foreach($cultivos as $c): 
                            $usos = [];
                            if($c['es_personal']) $usos[] = 'Consumo';
                            if($c['es_comercial']) $usos[] = 'Venta';
                        ?>
                            <li><?= htmlspecialchars($c['nombre']) ?> <span class="text-muted small">(<?= implode(', ', $usos) ?>)</span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small mb-0">No registrados.</p>
                <?php endif; ?>
            </div>

            <!-- SECCIÓN: AGROFORESTERÍA -->
            <div class="col-md-4">
                <h6 class="text-warning border-bottom pb-2">Agroforestería y Bosque</h6>
                
                <div class="mb-3">
                    <strong class="text-muted small d-block mb-1">Árboles Cultivados:</strong>
                    <?php if(count($arboles) > 0): ?>
                        <ul class="mb-0 small">
                            <?php foreach($arboles as $a): ?>
                                <li><?= htmlspecialchars($a) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Ninguno registrado.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <strong class="text-muted small d-block mb-1">Actividades Forestales:</strong>
                    <?php if(count($actividades_bosque) > 0): ?>
                        <ul class="mb-0 small">
                            <?php foreach($actividades_bosque as $act): ?>
                                <li><?= htmlspecialchars($act) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Ninguna registrada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Núcleo -->
<div class="modal fade" id="modalNucleo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Información Completa del Núcleo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
            <div class="col-12"><h6 class="text-primary border-bottom pb-2">Datos Personales</h6></div>
            <div class="col-6"><strong>Primer Nombre:</strong><br><?= htmlspecialchars($nucleo['Nombre']) ?></div>
            <div class="col-6"><strong>Segundo Nombre:</strong><br><?= htmlspecialchars($nucleo['Segundo_Nombre']) ?: '-' ?></div>
            <div class="col-6"><strong>Tercer Nombre:</strong><br><?= htmlspecialchars($nucleo['Tercer_nombre']) ?: '-' ?></div>
            <div class="col-6"><strong>Primer Apellido:</strong><br><?= htmlspecialchars($nucleo['Primer_Apellido']) ?></div>
            <div class="col-6"><strong>Segundo Apellido:</strong><br><?= htmlspecialchars($nucleo['Segundo_Apellido']) ?: '-' ?></div>
            <div class="col-6"><strong>Apellido Casada:</strong><br><?= htmlspecialchars($nucleo['Apellido_Casada']) ?: '-' ?></div>
            <div class="col-6"><strong>Sexo:</strong><br><?= htmlspecialchars($nucleo['Sexo']) ?: '-' ?></div>
            <div class="col-6"><strong>Fecha Nacimiento:</strong><br><?= $nucleo['Desconoce_Fecha'] ? 'Desconocida' : htmlspecialchars($nucleo['Fecha_Nacimiento']) ?></div>
            <div class="col-6"><strong>Edad:</strong><br><?= $nucleo['Desconoce_Fecha'] ? $nucleo['Edad'] . ' años (aprox.)' : calcularEdad($nucleo['Fecha_Nacimiento']) . ' años' ?></div>
            
            <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">Información de Contacto y MC</h6></div>
            <div class="col-6"><strong>DPI:</strong><br><?= htmlspecialchars($nucleo['DPI']) ?></div>
            <div class="col-6"><strong>Teléfono:</strong><br><?= htmlspecialchars($nucleo['Telefono']) ?: '-' ?></div>
            <div class="col-6"><strong>Persona Beneficiaria MC:</strong><br><?= htmlspecialchars($nucleo['Persona_Beneficiaria_MC']) ?></div>
            <div class="col-6"><strong>Parentesco MC:</strong><br><?= htmlspecialchars($nucleo['Parentesco_MC']) ?: '-' ?></div>
            <div class="col-6"><strong>Afinidad MC:</strong><br><?= htmlspecialchars($nucleo['Afinidad_MC']) ?: '-' ?></div>
            <div class="col-6"><strong>Comunidad Lingüística:</strong><br><?= htmlspecialchars($nucleo['Comunidad_Linguistica']) ?: '-' ?></div>
            <div class="col-12"><strong>Pertenencia Cultural:</strong><br><?= htmlspecialchars($nucleo['Pertenencia_Cultural']) ?: '-' ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modales Familiares -->
<?php foreach ($familiares as $fam): 
  $fid = $fam['id'];
  $enf_stmt = $pdo->prepare("SELECT e.nombre, e.categoria FROM Familiar_Enfermedades fe JOIN Enfermedades e ON fe.enfermedad_id = e.id WHERE fe.familiar_id = ?");
  $enf_stmt->execute([$fid]);
  $enfermedades_familiares = $enf_stmt->fetchAll(PDO::FETCH_ASSOC);

  $trab_stmt = $pdo->prepare("SELECT t.nombre FROM Familiar_Trabajos ft JOIN Trabajos t ON ft.trabajo_id = t.id WHERE ft.familiar_id = ?");
  $trab_stmt->execute([$fid]);
  $trabajos_familiares = $trab_stmt->fetchAll(PDO::FETCH_COLUMN);

  $fte_stmt = $pdo->prepare("SELECT fi.nombre FROM Familiar_Fuentes ff JOIN Fuentes_Ingreso fi ON ff.fuente_id = fi.id WHERE ff.familiar_id = ?");
  $fte_stmt->execute([$fid]);
  $fuentes_familiares = $fte_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="modal fade" id="modalFam<?= $fam['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Detalles de Familiar: <?= htmlspecialchars($fam['Nombre']) ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-3"><strong>Nacionalidad:</strong> <br><?= htmlspecialchars($fam['Nacionalidad'] ?? '-') ?></div>
            <div class="col-md-3"><strong>Tiene Doble Nacionalidad:</strong> <br><?= $fam['Doble_Nacionalidad'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-3"><strong>Afinidad:</strong> <br><?= htmlspecialchars($fam['Afinidad'] ?? '-') ?></div>
            <div class="col-md-3"><strong>Fecha Nacimiento:</strong> <br><?= $fam['Desconoce_Fecha'] ? 'Desconocida' : htmlspecialchars($fam['Fecha_Nacimiento']) ?></div>
            <div class="col-md-3"><strong>Edad:</strong> <br><?= $fam['Desconoce_Fecha'] ? $fam['Edad'] . ' años (aprox.)' : calcularEdad($fam['Fecha_Nacimiento']) . ' años' ?></div>
            <div class="col-md-3"><strong>Estado Civil:</strong> <br><?= htmlspecialchars($fam['Estado_Civil'] ?? '-') ?></div>
            <div class="col-md-3"><strong>Embarazada:</strong> <br><?= $fam['Embarazada'] == 1 ? 'Sí' : ($fam['Embarazada'] == 2 ? 'No Aplica' : 'No') ?></div>
            
            <div class="col-md-4 mt-3"><strong>Atención Prenatal:</strong> <br><?= $fam['Atencion_Prenatal'] == 1 ? 'Sí' : ($fam['Atencion_Prenatal'] == 2 ? 'No Aplica' : 'No') ?></div>
            <div class="col-md-8 mt-3"><strong>Tipo Atención Prenatal:</strong> <br><?= htmlspecialchars($fam['Tipo_Atencion_Prenatal'] ?: '-') ?></div>

            <div class="col-md-4 mt-3"><strong>Tiene Discapacidad:</strong> <br><?= $fam['Tiene_Discapacidad'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-8 mt-3"><strong>Tipo de Discapacidad:</strong> <br><?= htmlspecialchars($fam['Tipo_Discapacidad'] ?: '-') ?></div>
            <div class="col-md-3"><strong>Donde Reside Actualmente:</strong> <br><?= htmlspecialchars($fam['Donde_reside'] ?: '-') ?></div>
            <div class="col-md-3"><strong>Tiempo Fuera de la Comunidad:</strong> <br><?= htmlspecialchars($fam['Fuera_comunidad'] ?: '-') ?></div>
            
            <div class="col-12 mt-4"><h6 class="text-success border-bottom pb-2">Documentos de Identificación</h6></div>
            <div class="col-md-4"><strong>DPI:</strong> <br><?= $fam['Tiene_DPI'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-4"><strong>Certificado Nacimiento:</strong> <br><?= $fam['Tiene_Certificado_Nacimiento'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-4"><strong>Pasaporte:</strong> <br><?= $fam['Tiene_Pasaporte'] ? 'Sí' : 'No' ?></div>

            <div class="col-12 mt-4"><h6 class="text-success border-bottom pb-2">Salud y Enfermedades</h6></div>
            <div class="col-12">
                <?php if ($enfermedades_familiares): ?>
                    <ul class="mb-0">
                    <?php foreach ($enfermedades_familiares as $enf): ?>
                        <li><?= htmlspecialchars($enf['nombre']) ?> <small class="text-muted">(<?= $enf['categoria'] ?>)</small></li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <span class="text-muted">Ninguna registrada</span>
                <?php endif; ?>
            </div>

            <div class="col-12 mt-4"><h6 class="text-success border-bottom pb-2">Educación</h6></div>
            <div class="col-md-3"><strong>Sabe Leer:</strong> <?= $fam['Sabe_Leer'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-3"><strong>Sabe Escribir:</strong> <?= $fam['Sabe_Escribir'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-6"><strong>Estudia Actualmente:</strong> <?= $fam['Estudia_Actualmente'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-6 mt-2"><strong>Escolaridad alcanzada:</strong> <br><?= htmlspecialchars($fam['Escolaridad'] ?: '-') ?></div>
            <div class="col-md-6 mt-2"><strong>Razón abandono de estudio:</strong> <br><?= htmlspecialchars($fam['Razon_Abandono'] ?: '-') ?></div>
            
            <div class="col-12 mt-4"><h6 class="text-success border-bottom pb-2">Laboral e Ingresos</h6></div>
            <div class="col-md-4"><strong>Actualmente labora:</strong> <?= $fam['Actualmente_Labora'] ? 'Sí' : 'No' ?></div>
            <div class="col-md-4"><strong>Ingresos:</strong> <?= htmlspecialchars($fam['Ingresos_Mensuales'] ? 'Q' . $fam['Ingresos_Mensuales'] : '-') ?></div>
            <div class="col-md-4"><strong>Viajan por trabajo:</strong> <?= $fam['Viajan_Trabajo'] ? 'Sí (' . htmlspecialchars($fam['Tiempo_Viaje'] ?: '-') . ')' : 'No' ?></div>

            <div class="col-md-6 mt-3">
                <strong>Trabajos / Ocupaciones:</strong>
                <ul class="mb-0 mt-1">
                    <?php if ($trabajos_familiares): ?>
                        <?php foreach ($trabajos_familiares as $trabajo): ?>
                            <li><?= htmlspecialchars($trabajo) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-muted">Ninguno registrado</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-6 mt-3">
                <strong>Fuentes de Ingreso:</strong>
                <ul class="mb-0 mt-1">
                    <?php if ($fuentes_familiares): ?>
                        <?php foreach ($fuentes_familiares as $fte): ?>
                            <li><?= htmlspecialchars($fte) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-muted">Ninguna registrada</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- Modales Viviendas -->
<?php foreach ($viviendas as $viv): 
  $vid = $viv['id'];
  
  $fetchPivotData = function($query, $id) use ($pdo) {
      $stmt = $pdo->prepare($query);
      $stmt->execute([$id]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  };

  $energias_viv = $fetchPivotData("SELECT ec.nombre FROM Vivienda_Energia_Cocina vec JOIN Energia_Cocina ec ON vec.energia_id = ec.id WHERE vec.vivienda_id = ?", $vid);
  $alumbrados_viv = $fetchPivotData("SELECT a.nombre FROM Vivienda_Alumbrado va JOIN Alumbrado a ON va.alumbrado_id = a.id WHERE va.vivienda_id = ?", $vid);
  $aguas_hogar_viv = $fetchPivotData("SELECT ah.nombre FROM Vivienda_Agua_Hogar vah JOIN Agua_Hogar ah ON vah.agua_id = ah.id WHERE vah.vivienda_id = ?", $vid);
  $aguas_consumo_viv = $fetchPivotData("SELECT ac.nombre FROM Vivienda_Agua_Consumo vac JOIN Agua_Consumo ac ON vac.agua_id = ac.id WHERE vac.vivienda_id = ?", $vid);
  $animales_viv = $fetchPivotData("SELECT ua.nombre, va.conviven_hogar, va.espacio_aislado FROM Vivienda_Animales va JOIN Ubicacion_Animales ua ON va.ubicacion_id = ua.id WHERE va.vivienda_id = ?", $vid);
  $sanitarios_viv = $fetchPivotData("SELECT ts.nombre FROM Vivienda_Sanitario vs JOIN Tipo_Sanitario ts ON vs.sanitario_id = ts.id WHERE vs.vivienda_id = ?", $vid);
  $aguas_grises_viv = $fetchPivotData("SELECT ag.nombre FROM Vivienda_Aguas_Grises vag JOIN Aguas_Grises ag ON vag.aguas_id = ag.id WHERE vag.vivienda_id = ?", $vid);
  $basuras_viv = $fetchPivotData("SELECT mb.nombre FROM Vivienda_Basura vb JOIN Manejo_Basura mb ON vb.basura_id = mb.id WHERE vb.vivienda_id = ?", $vid);
?>
<div class="modal fade" id="modalViv<?= $viv['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="bi bi-house"></i> Detalles de la Vivienda</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <h6 class="text-info border-bottom pb-2">Estructura</h6>
        <div class="row mb-4">
            <div class="col-md-3"><strong>Ambientes:</strong> <br><?= htmlspecialchars($viv['Cantidad_Ambientes'] ?: '-') ?></div>
            <div class="col-md-3"><strong>Techo:</strong> <br><?= htmlspecialchars($viv['Techo'] ?: '-') ?></div>
            <div class="col-md-3"><strong>Paredes:</strong> <br><?= htmlspecialchars($viv['Paredes'] ?: '-') ?></div>
            <div class="col-md-3"><strong>Suelo:</strong> <br><?= htmlspecialchars($viv['Suelo'] ?: '-') ?></div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="text-info border-bottom pb-2">Servicios Básicos</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Energía Cocina:</strong>
                        <?php if($energias_viv) { foreach($energias_viv as $e) echo '<span class="badge bg-secondary me-1">'.$e['nombre'].'</span>'; } else { echo '-'; } ?>
                    </li>
                    <li class="mb-2"><strong>Alumbrado:</strong>
                        <?php if($alumbrados_viv) { foreach($alumbrados_viv as $a) echo '<span class="badge bg-warning text-dark me-1">'.$a['nombre'].'</span>'; } else { echo '-'; } ?>
                    </li>
                </ul>
            </div>
            
            <div class="col-md-6">
                <h6 class="text-info border-bottom pb-2">Agua y Saneamiento</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Agua de Hogar:</strong>
                        <?php if($aguas_hogar_viv) { foreach($aguas_hogar_viv as $ah) echo '<span class="badge bg-primary me-1">'.$ah['nombre'].'</span>'; } else { echo '-'; } ?>
                    </li>
                    <li class="mb-2"><strong>Agua Consumo:</strong>
                        <?php if($aguas_consumo_viv) { foreach($aguas_consumo_viv as $ac) echo '<span class="badge bg-info text-dark me-1">'.$ac['nombre'].'</span>'; } else { echo '-'; } ?>
                    </li>
                    <li class="mb-2"><strong>Tipo Sanitario:</strong>
                        <?php if($sanitarios_viv) { foreach($sanitarios_viv as $ts) echo '<span class="badge bg-success me-1">'.$ts['nombre'].'</span>'; } else { echo '-'; } ?>
                        <?= $viv['Tiene_Sanitario'] ? '<i class="bi bi-check-circle-fill text-success" title="Sanitario domiciliario"></i>' : '' ?>
                    </li>
                    <li class="mb-2"><strong>Aguas Grises:</strong>
                        <?php if($aguas_grises_viv) { foreach($aguas_grises_viv as $ag) echo '<span class="badge bg-secondary me-1">'.$ag['nombre'].'</span>'; } else { echo '-'; } ?>
                    </li>
                    <li class="mb-2"><strong>Manejo de Basura:</strong>
                        <?php if($basuras_viv) { foreach($basuras_viv as $ba) echo '<span class="badge bg-dark me-1">'.$ba['nombre'].'</span>'; } else { echo '-'; } ?>
                    </li>
                </ul>
            </div>
        </div>

        <h6 class="text-info border-bottom pb-2 mt-4">Animales / Mascotas</h6>
        <?php if($animales_viv): ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Especie</th>
                            <th class="text-center">Convive en hogar</th>
                            <th class="text-center">Espacio aislado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($animales_viv as $an): ?>
                        <tr>
                            <td><?= htmlspecialchars($an['nombre']) ?></td>
                            <td class="text-center"><?= $an['conviven_hogar'] ? 'Sí' : 'No' ?></td>
                            <td class="text-center"><?= $an['espacio_aislado'] ? 'Sí' : 'No' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Ningún animal o mascota registrado.</p>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>
