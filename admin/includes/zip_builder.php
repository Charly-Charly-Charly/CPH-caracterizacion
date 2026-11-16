<?php
// admin/includes/zip_builder.php - Empaqueta el código del sistema + una base de datos
// SQLite nueva (desde seed.sql) + el .env de una PC específica, listo para copiar a esa laptop.

define('PROYECTO_RAIZ', dirname(__DIR__, 2));

// Rutas (relativas a la raíz del proyecto) que NUNCA deben ir dentro de un ZIP de campo.
function zip_rutas_excluidas() {
    return [
        'admin',           // el panel de admin no se reparte a las PCs de campo
        '.git',
        '.env',            // cada PC lleva su propio .env, generado aparte
        'database.sqlite',
        'scratch',
    ];
}

function zip_debe_excluirse($ruta_relativa) {
    foreach (zip_rutas_excluidas() as $excluida) {
        if ($ruta_relativa === $excluida || str_starts_with($ruta_relativa, $excluida . '/')) {
            return true;
        }
    }
    // backups de base de datos / merges temporales, cualquiera que sea su sufijo
    if (preg_match('/^database_backup_.*\.sqlite$/', $ruta_relativa) || preg_match('/^temp_merge_.*\.sqlite$/', $ruta_relativa)) {
        return true;
    }
    return false;
}

function zip_listar_archivos_proyecto() {
    $archivos = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(PROYECTO_RAIZ, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $ruta_relativa = str_replace('\\', '/', substr($item->getPathname(), strlen(PROYECTO_RAIZ) + 1));
        if (zip_debe_excluirse($ruta_relativa)) {
            continue;
        }
        if ($item->isFile()) {
            $archivos[] = $ruta_relativa;
        }
    }
    return $archivos;
}

function zip_generar_env($comision, $pc) {
    return <<<ENV
# .env generado automáticamente por el panel de admin para esta PC. NO subir a git.

PC_ID={$pc['pc_id']}

MYSQL_HOST={$comision['mysql_host']}
MYSQL_USER={$comision['mysql_user']}
MYSQL_PASS={$comision['mysql_pass']}
MYSQL_NAME={$comision['mysql_name']}

AUTH_USER={$pc['auth_user']}
AUTH_PASS_HASH={$pc['auth_pass_hash']}
ENV;
}

// Construye el ZIP de una PC en $zip_destino (ruta de archivo). Devuelve true/false.
function zip_construir_para_pc($comision, $pc, $zip_destino) {
    $zip = new ZipArchive();
    if ($zip->open($zip_destino, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }

    // Código del sistema (sin admin/, sin .git, sin bases de datos ni .env de este checkout)
    foreach (zip_listar_archivos_proyecto() as $ruta_relativa) {
        $zip->addFile(PROYECTO_RAIZ . '/' . $ruta_relativa, $ruta_relativa);
    }

    // Base de datos nueva y vacía para esta PC, construida desde seed.sql
    $db_temp = tempnam(sys_get_temp_dir(), 'copadeh_db_');
    unlink($db_temp); // PDO sqlite necesita crear el archivo él mismo
    $pdo_temp = new PDO("sqlite:" . $db_temp);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec(file_get_contents(PROYECTO_RAIZ . '/seed.sql'));
    unset($pdo_temp); // cierra la conexión para poder leer el archivo abajo

    $zip->addFile($db_temp, 'database.sqlite');

    // .env específico de esta PC
    $zip->addFromString('.env', zip_generar_env($comision, $pc));

    $zip->close();
    unlink($db_temp);

    return true;
}
