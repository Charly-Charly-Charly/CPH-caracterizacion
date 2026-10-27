<?php
require_once 'includes/db.php';

// 1. Estadísticas Generales
$totalNucleos = $pdo->query("SELECT COUNT(*) FROM Nucleo")->fetchColumn();
$totalFamiliares = $pdo->query("SELECT COUNT(*) FROM Familiares")->fetchColumn();
$totalFallecidos = $pdo->query("SELECT COUNT(*) FROM Fallecidos")->fetchColumn();
$totalIntegrantes = $totalNucleos + $totalFamiliares;

// 2. Distribución por Género (Combinada)
$generoSql = "
    SELECT Sexo, COUNT(*) as total FROM (
        SELECT Sexo FROM Nucleo WHERE Sexo IS NOT NULL
        UNION ALL
        SELECT Sexo FROM Familiares WHERE Sexo IS NOT NULL
    ) GROUP BY Sexo
";
$generos = $pdo->query($generoSql)->fetchAll(PDO::FETCH_ASSOC);

// 3. Alfabetismo (Solo familiares para este ejemplo, se puede ampliar)
$alfabetismoSql = "
    SELECT 
        SUM(CASE WHEN Sabe_Leer = 1 THEN 1 ELSE 0 END) as leen,
        SUM(CASE WHEN Sabe_Leer = 0 THEN 1 ELSE 0 END) as no_leen
    FROM Familiares
";
$alfabetismo = $pdo->query($alfabetismoSql)->fetch(PDO::FETCH_ASSOC);

// 4. Estado Laboral
$laboralSql = "
    SELECT 
        SUM(CASE WHEN Actualmente_Labora = 1 THEN 1 ELSE 0 END) as laboran,
        SUM(CASE WHEN Actualmente_Labora = 0 THEN 1 ELSE 0 END) as no_laboran
    FROM Familiares
";
$laboral = $pdo->query($laboralSql)->fetch(PDO::FETCH_ASSOC);

// 5. Top 5 Cultivos
$cultivosSql = "
    SELECT c.nombre, COUNT(*) as total 
    FROM Nucleo_Cultivos nc 
    JOIN Cultivos c ON nc.cultivo_id = c.id 
    GROUP BY c.id 
    ORDER BY total DESC 
    LIMIT 5
";
$topCultivos = $pdo->query($cultivosSql)->fetchAll(PDO::FETCH_ASSOC);

// 6. Análisis de Edades
$edadesSql = "
    SELECT Fecha_Nacimiento FROM Nucleo WHERE Fecha_Nacimiento IS NOT NULL AND Fecha_Nacimiento != ''
    UNION ALL
    SELECT Fecha_Nacimiento FROM Familiares WHERE Fecha_Nacimiento IS NOT NULL AND Fecha_Nacimiento != ''
";
$fechas = $pdo->query($edadesSql)->fetchAll(PDO::FETCH_COLUMN);

$buckets = [
    '0-4' => 0, '5-9' => 0, '10-14' => 0, '15-19' => 0, '20-24' => 0,
    '25-29' => 0, '30-34' => 0, '35-39' => 0, '40-44' => 0, '45-49' => 0,
    '50-54' => 0, '55-59' => 0, '60-64' => 0, '65-69' => 0, '70-74' => 0,
    '75-79' => 0, '80-84' => 0, '85-89' => 0, '90-94' => 0, '95-99' => 0,
    '100+' => 0
];

$hoy = new DateTime();
foreach ($fechas as $f) {
    try {
        $fnac = new DateTime($f);
        $edad = $hoy->diff($fnac)->y;
        
        if ($edad >= 100) {
            $buckets['100+']++;
        } else {
            $base = floor($edad / 5) * 5;
            $label = $base . '-' . ($base + 4);
            if (isset($buckets[$label])) {
                $buckets[$label]++;
            }
        }
    } catch (Exception $e) { /* Saltar fechas inválidas */ }
}

function calcularEdad($fecha) {
    if (!$fecha) return 'N/A';
    try {
        $fnac = new DateTime($fecha);
        $hoy = new DateTime();
        return $hoy->diff($fnac)->y;
    } catch (Exception $e) { return 'Error'; }
}

// 7. Obtener datos para exportación completa (Flat Table)
// Unimos Nucleo y Familiares para tener una lista de todas las personas
$exportSql = "
    SELECT 
        'Jefe' as Rol, n.id as Nucleo_ID, n.Nombre || ' ' || n.Primer_Apellido as Nombre_Completo, 
        n.Sexo, n.Fecha_Nacimiento, n.DPI, 'N/A' as Parentesco, 'N/A' as Escolaridad, 
        n.Pertenencia_Cultural, n.Comunidad_Linguistica
    FROM Nucleo n
    UNION ALL
    SELECT 
        'Familiar' as Rol, f.nucleo_id as Nucleo_ID, f.Nombre as Nombre_Completo, 
        f.Sexo, f.Fecha_Nacimiento, 'N/A' as DPI, f.Parentesco, f.Escolaridad,
        'N/A' as Pertenencia_Cultural, 'N/A' as Comunidad_Linguistica
    FROM Familiares f
    ORDER BY Nucleo_ID ASC, Rol DESC
";
$exportData = $pdo->query($exportSql)->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Análisis de Datos y Estadísticas</h2>
    <div class="btn-group">
        <button onclick="exportarExcel()" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
        </button>
        <a href="index.php" class="btn btn-outline-secondary ms-2"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- Tarjetas de Resumen -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card h-100 border-start border-primary border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Total Familias</h6>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0 me-3"><?= $totalNucleos ?></h2>
                    <i class="bi bi-house-door text-primary fs-1 opacity-25"></i>
                </div>
                <p class="text-muted small mb-0 mt-2">Núcleos familiares registrados</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-success border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Población Total</h6>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0 me-3"><?= $totalIntegrantes ?></h2>
                    <i class="bi bi-people text-success fs-1 opacity-25"></i>
                </div>
                <p class="text-muted small mb-0 mt-2">Incluye jefes e integrantes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-danger border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Fallecidos</h6>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0 me-3"><?= $totalFallecidos ?></h2>
                    <i class="bi bi-heartbreak text-danger fs-1 opacity-25"></i>
                </div>
                <p class="text-muted small mb-0 mt-2">Registros históricos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-warning border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small fw-bold">Promedio Familiar</h6>
                <div class="d-flex align-items-center">
                    <h2 class="mb-0 me-3"><?= $totalNucleos > 0 ? round($totalIntegrantes / $totalNucleos, 1) : 0 ?></h2>
                    <i class="bi bi-calculator text-warning fs-1 opacity-25"></i>
                </div>
                <p class="text-muted small mb-0 mt-2">Personas por hogar</p>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <i class="bi bi-gender-ambiguous text-primary me-2"></i> Distribución por Género
            </div>
            <div class="card-body d-flex flex-column align-items-center">
                <div style="width: 100%; max-width: 300px;">
                    <canvas id="chartGenero"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <i class="bi bi-book text-success me-2"></i> Tasa de Alfabetismo (Integrantes)
            </div>
            <div class="card-body d-flex flex-column align-items-center">
                <div style="width: 100%; max-width: 300px;">
                    <canvas id="chartAlfabetismo"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <i class="bi bi-briefcase text-warning me-2"></i> Estado Laboral (Integrantes)
            </div>
            <div class="card-body d-flex flex-column align-items-center">
                <div style="width: 100%; max-width: 300px;">
                    <canvas id="chartLaboral"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <i class="bi bi-tree text-success me-2"></i> Top 5 Cultivos en la Comunidad
            </div>
            <div class="card-body">
                <canvas id="chartCultivos"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN: EDADES -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <i class="bi bi-bar-chart-steps text-info me-2"></i> Distribución de la Población por Edades (Rangos de 5 años)
            </div>
            <div class="card-body">
                <div style="height: 400px; width: 100%;">
                    <canvas id="chartEdades"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla oculta para exportación fácil (Opcional, pero SheetsJS puede trabajar con JSON directamente) -->
<div id="data-to-export" style="display: none;">
    <table>
        <thead>
            <tr>
                <th>Rol</th>
                <th>Nucleo ID</th>
                <th>Nombre Completo</th>
                <th>Edad</th>
                <th>Sexo</th>
                <th>Fecha Nacimiento</th>
                <th>DPI</th>
                <th>Parentesco</th>
                <th>Escolaridad</th>
                <th>Pertenencia Cultural</th>
                <th>Comunidad Linguistica</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exportData as $row): ?>
                <tr>
                    <td><?= $row['Rol'] ?></td>
                    <td><?= $row['Nucleo_ID'] ?></td>
                    <td><?= $row['Nombre_Completo'] ?></td>
                    <td><?= calcularEdad($row['Fecha_Nacimiento']) ?></td>
                    <td><?= $row['Sexo'] ?></td>
                    <td><?= $row['Fecha_Nacimiento'] ?></td>
                    <td><?= $row['DPI'] ?></td>
                    <td><?= $row['Parentesco'] ?></td>
                    <td><?= $row['Escolaridad'] ?></td>
                    <td><?= $row['Pertenencia_Cultural'] ?></td>
                    <td><?= $row['Comunidad_Linguistica'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Gráfico Género
    const ctxGenero = document.getElementById('chartGenero').getContext('2d');
    new Chart(ctxGenero, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($generos, 'Sexo')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($generos, 'total')) ?>,
                backgroundColor: ['#0d6efd', '#fd3550', '#ffc107', '#20c997']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // 2. Gráfico Alfabetismo
    const ctxAlfa = document.getElementById('chartAlfabetismo').getContext('2d');
    new Chart(ctxAlfa, {
        type: 'doughnut',
        data: {
            labels: ['Saben Leer/Escribir', 'No Saben'],
            datasets: [{
                data: [<?= $alfabetismo['leen'] ?: 0 ?>, <?= $alfabetismo['no_leen'] ?: 0 ?>],
                backgroundColor: ['#198754', '#dee2e6']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // 3. Gráfico Laboral
    const ctxLab = document.getElementById('chartLaboral').getContext('2d');
    new Chart(ctxLab, {
        type: 'pie',
        data: {
            labels: ['Trabajan actualmente', 'No trabajan'],
            datasets: [{
                data: [<?= $laboral['laboran'] ?: 0 ?>, <?= $laboral['no_laboran'] ?: 0 ?>],
                backgroundColor: ['#ffc107', '#adb5bd']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // 4. Gráfico Cultivos
    const ctxCult = document.getElementById('chartCultivos').getContext('2d');
    new Chart(ctxCult, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($topCultivos, 'nombre')) ?>,
            datasets: [{
                label: 'Cantidad de Productores',
                data: <?= json_encode(array_map('intval', array_column($topCultivos, 'total'))) ?>,
                backgroundColor: '#198754'
            }]
        },
        options: { 
            indexAxis: 'y',
            responsive: true, 
            scales: { x: { beginAtZero: true } }
        }
    });

    // 5. Gráfico Edades
    const ctxEdad = document.getElementById('chartEdades').getContext('2d');
    new Chart(ctxEdad, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($buckets)) ?>,
            datasets: [{
                label: 'Cantidad de Personas',
                data: <?= json_encode(array_values($buckets)) ?>,
                backgroundColor: '#0dcaf0',
                borderColor: '#0baccc',
                borderWidth: 1
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { 
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
});

function exportarExcel() {
    const table = document.querySelector('#data-to-export table');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Censo Laguna Larga" });
    
    // Añadir una hoja de resumen también
    const summaryData = [
        ["Categoría", "Total"],
        ["Total Familias", <?= $totalNucleos ?>],
        ["Total Integrantes", <?= $totalIntegrantes ?>],
        ["Total Fallecidos", <?= $totalFallecidos ?>]
    ];
    const wsSummary = XLSX.utils.aoa_to_sheet(summaryData);
    XLSX.utils.book_append_sheet(wb, wsSummary, "Resumen");
    
    XLSX.writeFile(wb, "Datos_Censo_Laguna_Larga.xlsx");
}
</script>

<?php require_once 'includes/footer.php'; ?>
