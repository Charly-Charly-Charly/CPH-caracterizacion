<?php
require_once 'includes/db.php';

$tablas = [
    'Enfermedades', 'Trabajos', 'Fuentes_Ingreso', 'Energia_Cocina', 
    'Alumbrado', 'Agua_Hogar', 'Agua_Consumo', 'Ubicacion_Animales', 
    'Tipo_Sanitario', 'Aguas_Grises', 'Manejo_Basura', 
    'Animales_Domesticos', 'Ganado', 'Cultivos', 'Arboles', 'Actividades_Bosque',
    'Nucleo', 'Familiares', 'Vivienda', 'Fallecidos', 'Cultivo_Arboles',
    'Familiar_Enfermedades', 'Familiar_Trabajos', 'Familiar_Fuentes',
    'Vivienda_Energia_Cocina', 'Vivienda_Alumbrado', 'Vivienda_Agua_Hogar',
    'Vivienda_Agua_Consumo', 'Vivienda_Animales', 'Vivienda_Sanitario',
    'Vivienda_Aguas_Grises', 'Vivienda_Basura', 'Nucleo_Animales_Domesticos',
    'Nucleo_Ganado', 'Nucleo_Cultivos', 'Cultivo_Arboles_Detalle',
    'Nucleo_Actividades_Bosque'
];

echo "<h3>Diagnóstico de Tablas</h3>";
echo "<table border='1'><tr><th>Tabla</th><th>Tiene 'sincronizado'?</th></tr>";

foreach ($tablas as $tabla) {
    echo "<tr><td>$tabla</td>";
    try {
        $stmt = $pdo->query("PRAGMA table_info($tabla)");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $found = false;
        foreach ($cols as $col) {
            if ($col['name'] === 'sincronizado') {
                $found = true;
                break;
            }
        }
        echo "<td>" . ($found ? "✅ SI" : "❌ NO") . "</td>";
    } catch (Exception $e) {
        echo "<td>⚠️ Error: " . $e->getMessage() . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>
