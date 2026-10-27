<?php
// includes/flash.php - Guarda un error "para la próxima carga de página" junto con los datos
// que la persona ya había llenado, para no perder su trabajo si algo falla al guardar.

function flash_error_con_datos($mensaje, array $datos) {
    $_SESSION['flash_error'] = $mensaje;
    $_SESSION['flash_datos'] = $datos;
}

// Traduce los errores técnicos de PDO a mensajes que la persona que captura datos pueda entender.
function mensaje_amigable(PDOException $e) {
    $msg = $e->getMessage();

    if (strpos($msg, 'UNIQUE constraint failed') !== false && strpos($msg, 'DPI') !== false) {
        return 'Ya existe una persona registrada con ese número de DPI. Verifica el número o revisa si el núcleo ya fue creado.';
    }
    if (strpos($msg, 'UNIQUE constraint failed') !== false) {
        return 'Ya existe un registro con esos mismos datos.';
    }
    if (strpos($msg, 'NOT NULL constraint failed') !== false) {
        return 'Falta llenar un campo obligatorio. Revisa el formulario e intenta de nuevo.';
    }
    if (strpos($msg, 'database is locked') !== false) {
        return 'La base de datos está en uso por otro programa en esta PC (por ejemplo, un visor de SQLite abierto). Cierra ese programa e intenta de nuevo.';
    }
    if (strpos($msg, 'no column named') !== false || strpos($msg, 'no such column') !== false) {
        return 'La estructura de la base de datos está desactualizada. Ve al menú y ejecuta "reparar_sistema.php" (Reparador de Sistema), luego vuelve a intentarlo.';
    }

    return 'Ocurrió un error al guardar los datos. Intenta de nuevo; si se repite, avisa al encargado del sistema.';
}
