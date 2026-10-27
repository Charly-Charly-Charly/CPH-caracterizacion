<?php
require_once 'includes/db.php';

$sql = "
SELECT 
    n.*,
    (SELECT GROUP_CONCAT(f.Nombre || ' (' || f.Parentesco || ')', ', ') 
     FROM Familiares f WHERE f.nucleo_id = n.id) AS lista_familiares,
     
    (SELECT GROUP_CONCAT(f.Escolaridad, ', ') 
     FROM Familiares f WHERE f.nucleo_id = n.id AND f.Escolaridad IS NOT NULL) AS educacion_familiares,
     
    (SELECT GROUP_CONCAT(v.Cantidad_Ambientes || ' amb. Techo:' || v.Techo || ' Paredes:' || v.Paredes || ' Suelo:' || v.Suelo, ' | ') 
     FROM Vivienda v WHERE v.nucleo_id = n.id) AS caracteristicas_vivienda,
     
    (SELECT GROUP_CONCAT(a.nombre, ', ') 
     FROM Nucleo_Animales_Domesticos na JOIN Animales_Domesticos a ON na.animal_id = a.id 
     WHERE na.nucleo_id = n.id) AS lista_animales,
     
    (SELECT GROUP_CONCAT(g.nombre, ', ') 
     FROM Nucleo_Ganado ng JOIN Ganado g ON ng.ganado_id = g.id 
     WHERE ng.nucleo_id = n.id) AS lista_ganado,
     
    (SELECT GROUP_CONCAT(c.nombre || CASE WHEN nc.es_personal=1 AND nc.es_comercial=1 THEN ' (Consumo/Venta)' WHEN nc.es_personal=1 THEN ' (Consumo)' WHEN nc.es_comercial=1 THEN ' (Venta)' ELSE '' END, ', ') 
     FROM Nucleo_Cultivos nc JOIN Cultivos c ON nc.cultivo_id = c.id 
     WHERE nc.nucleo_id = n.id) AS lista_cultivos,

    (SELECT GROUP_CONCAT(fa.Nombre || ' (' || COALESCE(fa.Causa_Fallecimiento,'Sin causa') || ')', ', ') 
     FROM Fallecidos fa WHERE fa.nucleo_id = n.id) AS lista_fallecidos
FROM Nucleo n
ORDER BY n.id ASC
";

$stmt = $pdo->query($sql);
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Vista General del Censo (Estilo Excel)</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm border-success">
    <div class="card-body p-0">
        <!-- Contenedor con scroll horizontal para permitir muchas columnas sin romper layout -->
        <div class="table-responsive" style="max-height: 75vh; overflow-y: auto;">
            <table class="table table-bordered table-hover table-striped table-sm mb-0 text-nowrap align-middle">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th class="ps-3">ID Núcleo</th>
                        <th>Jefe de Familia</th>
                        <th>DPI</th>
                        <th>Teléfono</th>
                        <th>Cultura / Comunidad</th>
                        <th class="bg-primary text-white">Familiares (Parentesco)</th>
                        <th class="bg-primary text-white">Nivel Educativo (Familia)</th>
                        <th class="bg-info text-white">Características Vivienda(s)</th>
                        <th class="bg-success text-white">Animales Domésticos</th>
                        <th class="bg-success text-white">Ganado</th>
                        <th class="bg-success text-white">Cultivos</th>
                        <th class="bg-danger text-white">Fallecidos (Causa)</th>
                        <th class="text-center pe-3 bg-secondary text-white">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($filas) > 0): ?>
                        <?php foreach ($filas as $fila): 
                            $nombreCompleto = trim(sprintf("%s %s %s %s", 
                                $fila['Nombre'], $fila['Segundo_Nombre'], 
                                $fila['Primer_Apellido'], $fila['Segundo_Apellido']
                            ));
                        ?>
                            <tr>
                                <td class="ps-3 fw-bold">#<?= htmlspecialchars($fila['id']) ?></td>
                                <td><?= htmlspecialchars($nombreCompleto) ?></td>
                                <td><?= htmlspecialchars($fila['DPI']) ?></td>
                                <td><?= htmlspecialchars($fila['Telefono'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($fila['Pertenencia_Cultural'] . ' - ' . $fila['Comunidad_Linguistica']) ?></td>
                                
                                <td style="max-width: 300px; white-space: normal; font-size: 0.85em;"><?= htmlspecialchars($fila['lista_familiares'] ?: 'Ninguno') ?></td>
                                <td style="max-width: 200px; white-space: normal; font-size: 0.85em;"><?= htmlspecialchars($fila['educacion_familiares'] ?: 'Ninguno') ?></td>
                                
                                <td style="max-width: 300px; white-space: normal; font-size: 0.85em;"><?= htmlspecialchars($fila['caracteristicas_vivienda'] ?: 'Sin vivienda registrada') ?></td>
                                
                                <td style="max-width: 200px; white-space: normal; font-size: 0.85em; text-transform: capitalize;"><?= htmlspecialchars($fila['lista_animales'] ?: 'No') ?></td>
                                <td style="max-width: 200px; white-space: normal; font-size: 0.85em; text-transform: capitalize;"><?= htmlspecialchars($fila['lista_ganado'] ?: 'No') ?></td>
                                <td style="max-width: 200px; white-space: normal; font-size: 0.85em; text-transform: capitalize;"><?= htmlspecialchars($fila['lista_cultivos'] ?: 'No') ?></td>
                                
                                <td style="max-width: 250px; white-space: normal; font-size: 0.85em;"><?= htmlspecialchars($fila['lista_fallecidos'] ?: 'Ninguno') ?></td>
                                
                                <td class="text-center pe-3">
                                    <a href="ver_nucleo.php?id=<?= $fila['id'] ?>" class="btn btn-sm btn-outline-dark" title="Abrir Registro">
                                        <i class="bi bi-box-arrow-in-right"></i> Abrir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="text-center py-5 text-muted">
                                <i class="bi bi-table fs-1 d-block mb-3 opacity-50"></i>
                                La base de datos está vacía. Registre núcleos para popular la tabla.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light text-muted small d-flex justify-content-between">
        <span><i class="bi bi-info-circle"></i> Esta tabla permite un vistazo rápido estilo Excel. Desplácese horizontal o verticalmente.</span>
        <span>Total de Registros: <strong><?= count($filas) ?></strong></span>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
