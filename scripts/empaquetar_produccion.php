<?php
/**
 * Script de Empaquetado de Producción para DonWeb Ferozo (100% PHP y CMD)
 * No requiere permisos de administrador ni PowerShell.
 */

declare(strict_types=1);

echo "=================================================================\n";
echo "   EMPAQUETADOR DE PRODUCCIÓN — SISTEMA FUNDO (DONWEB FEROZO)   \n";
echo "   Modo: CMD / PHP nativo (Sin requerir permisos de admin)       \n";
echo "=================================================================\n\n";

$rootDir = realpath(__DIR__ . '/..');
$webAppDir = $rootDir . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'web';
$distDir = $rootDir . DIRECTORY_SEPARATOR . 'dist';
$tempDir = $distDir . DIRECTORY_SEPARATOR . 'temp_build';
$zipOutput = $distDir . DIRECTORY_SEPARATOR . 'sistema-fundo-ferozo.zip';

if (!is_dir($webAppDir)) {
    fwrite(STDERR, "ERROR: No se encontró la carpeta apps/web en: $webAppDir\n");
    exit(1);
}

// -----------------------------------------------------------------------------
// 1. Ejecutar pruebas automatizadas
// -----------------------------------------------------------------------------
echo "--> [1/4] Ejecutando suite de pruebas automatizadas...\n";
$prevCwd = getcwd();
chdir($webAppDir);

$testOutput = [];
$testExitCode = 0;
exec('php artisan test', $testOutput, $testExitCode);

if ($testExitCode !== 0) {
    echo implode("\n", array_slice($testOutput, -15)) . "\n";
    fwrite(STDERR, "\nERROR: Las pruebas automatizadas fallaron (código $testExitCode). Corrige los errores antes de empaquetar.\n");
    chdir($prevCwd);
    exit(1);
}
echo "    ✓ Pruebas completadas con éxito.\n";
chdir($prevCwd);

// -----------------------------------------------------------------------------
// 2. Preparar directorios de trabajo
// -----------------------------------------------------------------------------
echo "--> [2/4] Preparando carpetas de distribución...\n";
if (!is_dir($distDir)) {
    mkdir($distDir, 0777, true);
}
if (file_exists($zipOutput)) {
    @unlink($zipOutput);
}
if (is_dir($tempDir)) {
    deleteDirectoryRecursively($tempDir);
}
mkdir($tempDir, 0777, true);

// -----------------------------------------------------------------------------
// 3. Copiar componentes esenciales
// -----------------------------------------------------------------------------
echo "--> [3/4] Copiando componentes del sistema...\n";

$foldersToCopy = ['app', 'bootstrap', 'config', 'database', 'lang', 'public', 'resources', 'routes', 'storage', 'vendor'];
foreach ($foldersToCopy as $folder) {
    $src = $webAppDir . DIRECTORY_SEPARATOR . $folder;
    $dst = $tempDir . DIRECTORY_SEPARATOR . $folder;
    if (is_dir($src)) {
        copyDirectoryRecursively($src, $dst);
    }
}

// Copiar archivos individuales clave
$filesToCopy = ['artisan', 'composer.json', '.htaccess'];
foreach ($filesToCopy as $file) {
    $src = $webAppDir . DIRECTORY_SEPARATOR . $file;
    $dst = $tempDir . DIRECTORY_SEPARATOR . $file;
    if (file_exists($src)) {
        copy($src, $dst);
    }
}

// Copiar esquema SQL limpio a la raíz del paquete
$schemaSql = $webAppDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema_produccion_limpio.sql';
if (file_exists($schemaSql)) {
    copy($schemaSql, $tempDir . DIRECTORY_SEPARATOR . 'schema_produccion_limpio.sql');
}

// -----------------------------------------------------------------------------
// 3.1 Preparar archivo .env para producción listo para DonWeb
// -----------------------------------------------------------------------------
echo "    -> Configurando archivo .env para producción...\n";
$prodExample = $webAppDir . DIRECTORY_SEPARATOR . '.env.production.example';
$localEnv = $webAppDir . DIRECTORY_SEPARATOR . '.env';

$appKey = '';
if (file_exists($localEnv)) {
    $lines = file($localEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), 'APP_KEY=')) {
            $appKey = trim(substr(trim($line), 8));
            break;
        }
    }
}

if (empty($appKey)) {
    $appKey = 'base64:' . base64_encode(random_bytes(32));
}

if (file_exists($prodExample)) {
    $content = file_get_contents($prodExample);
    $content = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . $appKey, $content);
    file_put_contents($tempDir . DIRECTORY_SEPARATOR . '.env', $content);
    copy($prodExample, $tempDir . DIRECTORY_SEPARATOR . '.env.production.example');
}

// -----------------------------------------------------------------------------
// 3.2 Limpieza estricta: purgar .md, .txt, tests, mocks y archivos dev
// -----------------------------------------------------------------------------
echo "--> [3.5/4] Depurando paquete (eliminando .md, .txt, tests, mocks y residuos dev)...\n";
purgeUnwantedFiles($tempDir);

// Asegurar subcarpetas limpias en storage
$storageSubs = [
    'app' . DIRECTORY_SEPARATOR . 'public',
    'framework' . DIRECTORY_SEPARATOR . 'cache',
    'framework' . DIRECTORY_SEPARATOR . 'sessions',
    'framework' . DIRECTORY_SEPARATOR . 'views',
    'logs'
];
foreach ($storageSubs as $sub) {
    $p = $tempDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $sub;
    if (!is_dir($p)) {
        mkdir($p, 0777, true);
    }
    $gi = $p . DIRECTORY_SEPARATOR . '.gitignore';
    if (!file_exists($gi)) {
        file_put_contents($gi, "*\n!.gitignore\n");
    }
}

// Limpiar archivos residuales en bootstrap/cache
$bootCache = $tempDir . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache';
if (is_dir($bootCache)) {
    foreach (scandir($bootCache) as $item) {
        if ($item !== '.' && $item !== '..' && $item !== '.gitignore') {
            @unlink($bootCache . DIRECTORY_SEPARATOR . $item);
        }
    }
}

// -----------------------------------------------------------------------------
// 4. Comprimir archivo ZIP
// -----------------------------------------------------------------------------
echo "--> [4/4] Comprimiendo archivo ZIP de producción...\n";

$zipSuccess = false;
if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    if ($zip->open($zipOutput, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($tempDir) + 1);
                // Normalizar separadores a barra diagonal para el estándar ZIP
                $normalizedPath = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $normalizedPath);
            }
        }
        $zip->close();
        $zipSuccess = file_exists($zipOutput) && filesize($zipOutput) > 0;
    }
}

// Si ZipArchive no está habilitado, intentar con el comando nativo tar de Windows
if (!$zipSuccess) {
    echo "    (Usando tar.exe nativo de Windows para compresión ZIP)...\n";
    // Usar "." incluye también archivos ocultos necesarios como .env y .htaccess.
    $cmd = sprintf('tar -a -c -f "%s" -C "%s" .', $zipOutput, $tempDir);
    exec($cmd, $tarOutput, $tarCode);
    $zipSuccess = ($tarCode === 0 && file_exists($zipOutput) && filesize($zipOutput) > 0);
}

if (!$zipSuccess) {
    fwrite(STDERR, "ERROR: No se pudo generar el archivo ZIP. Verifica que ZipArchive o tar.exe estén disponibles.\n");
    exit(1);
}

// Validar que el ZIP contenga los archivos esenciales
if (class_exists('ZipArchive')) {
    $verifyZip = new ZipArchive();
    if ($verifyZip->open($zipOutput) === true) {
        $requiredEntries = [
            'artisan',
            '.env',
            '.env.production.example',
            '.htaccess',
            'schema_produccion_limpio.sql',
            'app/Http/Controllers/DashboardController.php',
            'app/Http/Controllers/Api/VentasApiController.php',
            'app/Http/Middleware/ApiTokenAuth.php',
            'routes/api.php',
            'app/Models/User.php',
            'vendor/autoload.php'
        ];
        $missing = [];
        foreach ($requiredEntries as $req) {
            if ($verifyZip->locateName($req) === false) {
                $missing[] = $req;
            }
        }
        $verifyZip->close();
        if (!empty($missing)) {
            fwrite(STDERR, "ADVERTENCIA: El ZIP no contiene: " . implode(', ', $missing) . "\n");
        }
    }
}

// Eliminar carpeta temporal
deleteDirectoryRecursively($tempDir);

$zipSizeMb = round(filesize($zipOutput) / (1024 * 1024), 2);

echo "\n=================================================================\n";
echo "  PAQUETE DE PRODUCCIÓN GENERADO CON ÉXITO VIA CMD\n";
echo "=================================================================\n";
echo "Archivo generado: $zipOutput\n";
echo "Tamaño:           $zipSizeMb MB\n\n";
echo "Pasos para tu hosting DonWeb Ferozo:\n";
echo "1. Sube '$zipOutput' al Administrador de Archivos de Ferozo.\n";
echo "2. Descomprímelo en tu carpeta del servidor (ej. /sistema-fundo o /public_html).\n";
echo "3. Abre '.env' (ya incluido y preconfigurado con APP_KEY) y ajusta tus credenciales MySQL.\n";
echo "4. Solo en una primera instalación, importa 'schema_produccion_limpio.sql' en phpMyAdmin.\n";
echo "5. Listo! El sistema se desplegará limpio y operativo.\n";
echo "=================================================================\n";


// =============================================================================
// Funciones auxiliares
// =============================================================================

function copyDirectoryRecursively(string $src, string $dst): void
{
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $srcFile = $src . DIRECTORY_SEPARATOR . $file;
        $dstFile = $dst . DIRECTORY_SEPARATOR . $file;
        if (is_dir($srcFile)) {
            copyDirectoryRecursively($srcFile, $dstFile);
        } else {
            copy($srcFile, $dstFile);
        }
    }
    closedir($dir);
}

function deleteDirectoryRecursively(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            deleteDirectoryRecursively($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

function purgeUnwantedFiles(string $baseDir): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getRealPath();
        $filename = $item->getFilename();

        if ($item->isDir()) {
            // Eliminar carpetas de pruebas, factories o stubs
            if (preg_match('/^(tests?|spec|factories|stubs)$/i', $filename)) {
                deleteDirectoryRecursively($path);
            }
            continue;
        }

        // Eliminar archivos .md y .markdown
        if (preg_match('/\.(md|markdown)$/i', $filename)) {
            @unlink($path);
            continue;
        }

        // Eliminar archivos .txt (excepto public/robots.txt)
        if (preg_match('/\.txt$/i', $filename)) {
            $norm = str_replace('\\', '/', $path);
            if (!str_ends_with($norm, 'public/robots.txt')) {
                @unlink($path);
                continue;
            }
        }

        // Eliminar archivos de suite de pruebas
        if (preg_match('/(Test\.php|TestCase\.php|phpunit\.xml|\.phpunit)/i', $filename)) {
            @unlink($path);
            continue;
        }

        // Eliminar archivos de desarrollo, Git, Node, IDE y temporales
        $devFiles = [
            '.git', '.github', '.gitattributes', '.editorconfig', '.npmrc',
            '.phpactor.json', 'package.json', 'package-lock.json', 'vite.config.js',
            'Homestead.json', 'Homestead.yaml', '_ide_helper.php', '_ide_helper_models.php',
            '.DS_Store', 'Thumbs.db'
        ];
        if (in_array($filename, $devFiles, true)) {
            @unlink($path);
            continue;
        }

        // Eliminar bases de datos SQLite dev, temporales y logs
        if (preg_match('/\.(sqlite|sqlite3|sqlite-journal|log|bak|tmp|old|orig)$/i', $filename)) {
            @unlink($path);
            continue;
        }

        // Eliminar prototipos HTML estáticos de public
        if (preg_match('/^propuesta-.*\.html$/i', $filename)) {
            @unlink($path);
            continue;
        }
    }
}
