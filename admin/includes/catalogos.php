<?php
// admin/includes/catalogos.php - Catálogos de campos colectivos que cada
// comisión puede personalizar (enfermedades, trabajos, animales, cultivos, etc.)
// antes de generar las bases de datos en blanco de sus PCs.

// Tabla => etiqueta visible y si usa la columna "categoria" (solo Enfermedades).
function catalogos_definiciones() {
    return [
        'Enfermedades'         => ['etiqueta' => 'Enfermedades', 'categoria' => true],
        'Trabajos'             => ['etiqueta' => 'Trabajos', 'categoria' => false],
        'Fuentes_Ingreso'      => ['etiqueta' => 'Fuentes de ingreso', 'categoria' => false],
        'Energia_Cocina'       => ['etiqueta' => 'Energía para cocinar', 'categoria' => false],
        'Alumbrado'            => ['etiqueta' => 'Alumbrado', 'categoria' => false],
        'Agua_Hogar'           => ['etiqueta' => 'Agua en el hogar', 'categoria' => false],
        'Agua_Consumo'         => ['etiqueta' => 'Agua de consumo', 'categoria' => false],
        'Ubicacion_Animales'   => ['etiqueta' => 'Animales en el hogar (ubicación)', 'categoria' => false],
        'Tipo_Sanitario'       => ['etiqueta' => 'Tipo de sanitario', 'categoria' => false],
        'Aguas_Grises'         => ['etiqueta' => 'Manejo de aguas grises', 'categoria' => false],
        'Manejo_Basura'        => ['etiqueta' => 'Manejo de basura', 'categoria' => false],
        'Animales_Domesticos'  => ['etiqueta' => 'Animales domésticos', 'categoria' => false],
        'Ganado'               => ['etiqueta' => 'Ganado', 'categoria' => false],
        'Cultivos'             => ['etiqueta' => 'Cultivos', 'categoria' => false],
        'Arboles'              => ['etiqueta' => 'Árboles', 'categoria' => false],
        'Actividades_Bosque'   => ['etiqueta' => 'Actividades de bosque', 'categoria' => false],
    ];
}

// Valores por defecto (los de seed.sql), leídos levantando una base SQLite en
// memoria y corriendo seed.sql ahí, para no duplicar la lista en PHP.
function catalogos_valores_por_defecto() {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(file_get_contents(dirname(__DIR__, 2) . '/seed.sql'));

    $cache = [];
    foreach (catalogos_definiciones() as $tabla => $def) {
        $columna_categoria = $def['categoria'] ? 'categoria' : 'NULL AS categoria';
        $cache[$tabla] = $pdo->query("SELECT nombre, $columna_categoria FROM $tabla ORDER BY rowid")->fetchAll(PDO::FETCH_ASSOC);
    }
    return $cache;
}

// Valores personalizados guardados por la comisión para una tabla (vacío si no personalizó).
function catalogos_valores_personalizados($admin_pdo, $comision_id, $tabla) {
    $stmt = $admin_pdo->prepare("SELECT nombre, categoria FROM Comision_Catalogos WHERE comision_id = ? AND tabla = ? ORDER BY orden ASC");
    $stmt->execute([$comision_id, $tabla]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Valores que realmente se usarán para esta comisión: los personalizados si existen, si no los de seed.sql.
function catalogos_valores_efectivos($admin_pdo, $comision_id, $tabla) {
    $personalizados = catalogos_valores_personalizados($admin_pdo, $comision_id, $tabla);
    if (!empty($personalizados)) {
        return $personalizados;
    }
    $defaults = catalogos_valores_por_defecto();
    return $defaults[$tabla] ?? [];
}

// true si la comisión tiene al menos un catálogo personalizado.
function catalogos_tiene_personalizados($admin_pdo, $comision_id) {
    $stmt = $admin_pdo->prepare("SELECT COUNT(*) FROM Comision_Catalogos WHERE comision_id = ?");
    $stmt->execute([$comision_id]);
    return (int)$stmt->fetchColumn() > 0;
}
