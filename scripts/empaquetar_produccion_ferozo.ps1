<#
.SYNOPSIS
    Script automatizado de empaquetado para despliegue en producción en DonWeb Ferozo.
.DESCRIPTION
    1. Ejecuta las pruebas automatizadas (php artisan test).
    2. Valida la integridad de la base de datos limpia y configuraciones.
    3. Empaqueta la aplicación web (incluyendo vendor) en dist/sistema-fundo-ferozo.zip listo para subir a Ferozo.
#>

$ErrorActionPreference = "Stop"
$rootPath = (Resolve-Path "$PSScriptRoot\..").Path
$webAppPath = Join-Path $rootPath "apps\web"
$distPath = Join-Path $rootPath "dist"
$zipOutput = Join-Path $distPath "sistema-fundo-ferozo.zip"

Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host "   EMPAQUETADOR DE PRODUCCIÓN — SISTEMA FUNDO (DONWEB FEROZO)   " -ForegroundColor Cyan
Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Validar que la carpeta de la app web exista
if (-not (Test-Path $webAppPath)) {
    Write-Error "No se encontró la carpeta apps/web en $webAppPath"
    exit 1
}

# 2. Ejecutar pruebas automatizadas
Write-Host "--> [1/4] Ejecutando suite de pruebas automatizadas..." -ForegroundColor Yellow
Push-Location $webAppPath
try {
    & php artisan test
    if ($LASTEXITCODE -ne 0) {
        throw "php artisan test terminó con código de salida $LASTEXITCODE."
    }
    Write-Host "    Pruebas completadas con éxito." -ForegroundColor Green
} catch {
    Write-Error "Las pruebas automatizadas fallaron. Corrige los errores antes de empaquetar para producción."
    exit 1
} finally {
    Pop-Location
}

# 3. Preparar directorio dist/
Write-Host "--> [2/4] Preparando carpeta de distribución (dist/)..." -ForegroundColor Yellow
if (-not (Test-Path $distPath)) {
    New-Item -ItemType Directory -Path $distPath | Out-Null
}
if (Test-Path $zipOutput) {
    Remove-Item $zipOutput -Force
}

# 4. Crear carpeta temporal para compresión limpia
$tempDir = Join-Path $distPath "temp_build"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir | Out-Null

Write-Host "--> [3/4] Copiando archivos esenciales para producción..." -ForegroundColor Yellow

# Copiar carpetas del sistema
$foldersToCopy = @("app", "bootstrap", "config", "database", "lang", "public", "resources", "routes", "storage", "vendor")
foreach ($folder in $foldersToCopy) {
    $src = Join-Path $webAppPath $folder
    $dest = Join-Path $tempDir $folder
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $dest -Recurse -Force
    }
}

# Copiar archivos individuales clave
$filesToCopy = @("artisan", "composer.json", ".htaccess")
foreach ($file in $filesToCopy) {
    $src = Join-Path $webAppPath $file
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $tempDir -Force
    }
}

# Preparar archivo .env para producción listo para DonWeb
$prodEnvExample = Join-Path $webAppPath ".env.production.example"
$localEnv = Join-Path $webAppPath ".env"
$destEnv = Join-Path $tempDir ".env"

$appKey = ""
if (Test-Path $localEnv) {
    $matchedKey = (Get-Content $localEnv -ErrorAction SilentlyContinue | Where-Object { $_ -match "^APP_KEY=(.+)$" })
    if ($matchedKey) {
        $appKey = ($matchedKey -replace "^APP_KEY=", "").Trim()
    }
}

if (-not $appKey) {
    # Generar una clave AES-256 criptográficamente segura si no existe una previa
    $bytes = New-Object byte[] 32
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    $rng.GetBytes($bytes)
    $appKey = "base64:" + [Convert]::ToBase64String($bytes)
}

if (Test-Path $prodEnvExample) {
    $envContent = Get-Content $prodEnvExample -Raw
    $envContent = $envContent -replace "APP_KEY=.*", "APP_KEY=$appKey"
    Set-Content -Path $destEnv -Value $envContent -Encoding UTF8
    Copy-Item -Path $prodEnvExample -Destination (Join-Path $tempDir ".env.production.example") -Force
}

# Copiar esquema SQL limpio a la raíz del ZIP para acceso inmediato
$schemaSql = Join-Path $webAppPath "database\schema_produccion_limpio.sql"
if (Test-Path $schemaSql) {
    Copy-Item -Path $schemaSql -Destination (Join-Path $tempDir "schema_produccion_limpio.sql") -Force
}

Write-Host "--> [3.5/4] Limpiando archivos no esenciales (markdown, txt, tests, mocks, dev)..." -ForegroundColor Yellow

# 1. Eliminar archivos markdown (.md, .markdown) en todo el paquete (incluido vendor)
Get-ChildItem -Path $tempDir -Recurse -Filter "*.md" -File | Remove-Item -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path $tempDir -Recurse -Filter "*.markdown" -File | Remove-Item -Force -ErrorAction SilentlyContinue

# 2. Eliminar archivos .txt (conservando únicamente public/robots.txt para el servidor web)
Get-ChildItem -Path $tempDir -Recurse -Filter "*.txt" -File | Where-Object { $_.FullName -notmatch "public[\\/]robots\.txt" } | Remove-Item -Force -ErrorAction SilentlyContinue

# 3. Eliminar carpetas de pruebas, factories, stubs y tests en librerías
Get-ChildItem -Path $tempDir -Recurse -Directory | Where-Object {
    $_.Name -match "^(tests?|Tests?|spec|factories|stubs)$"
} | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue

# 4. Eliminar archivos de suite de pruebas (PHPUnit, mocks, result cache)
Get-ChildItem -Path "$tempDir\*" -Recurse -Include "*Test.php", "*TestCase.php", "phpunit.xml*", ".phpunit*", ".phpunit.result.cache" -File | Remove-Item -Force -ErrorAction SilentlyContinue

# 5. Eliminar archivos de desarrollo, Git, Node, IDE y temporales
Get-ChildItem -Path "$tempDir\*" -Recurse -Include ".git", ".github", ".gitattributes", ".editorconfig", ".npmrc", ".phpactor.json", "package.json", "package-lock.json", "vite.config.js", "Homestead.*", "_ide_helper*.php", ".DS_Store", "Thumbs.db" | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path "$tempDir\*" -Recurse -Include "*.sqlite*", "*.log", "*.bak", "*.tmp", "*.old", "*.orig" | Remove-Item -Force -ErrorAction SilentlyContinue

# 6. Eliminar prototipos HTML estáticos de diseño en public
Get-ChildItem -Path (Join-Path $tempDir "public") -Filter "propuesta-*.html" -File | Remove-Item -Force -ErrorAction SilentlyContinue

# 7. Limpiar cachés residuales de desarrollo en bootstrap/cache
$bootCache = Join-Path $tempDir "bootstrap\cache"
if (Test-Path $bootCache) {
    Get-ChildItem -Path $bootCache -File | Where-Object { $_.Name -ne ".gitignore" } | Remove-Item -Force -ErrorAction SilentlyContinue
}

# Asegurar que storage/logs y storage/framework tengan sus subcarpetas limpias
$storageSubs = @("framework/cache", "framework/sessions", "framework/views", "logs", "app/public")
foreach ($sub in $storageSubs) {
    $p = Join-Path $tempDir "storage\$sub"
    if (-not (Test-Path $p)) {
        New-Item -ItemType Directory -Path $p -Force | Out-Null
    }
    $gi = Join-Path $p ".gitignore"
    if (-not (Test-Path $gi)) {
        New-Item -Path $gi -ItemType File -Force | Out-Null
    }
}

# 5. Generar archivo ZIP
Write-Host "--> [4/4] Comprimiendo archivo ZIP de producción..." -ForegroundColor Yellow
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory(
    $tempDir,
    $zipOutput,
    [System.IO.Compression.CompressionLevel]::Optimal,
    $false
)

# Verificar que el ZIP sea completo antes de eliminar la fuente temporal.
$archive = [System.IO.Compression.ZipFile]::OpenRead($zipOutput)
try {
    $entryNames = @($archive.Entries | ForEach-Object { $_.FullName -replace '\\', '/' })
    $requiredEntries = @(
        'artisan',
        'app/Http/Controllers/DashboardController.php',
        'app/Http/Controllers/Api/VentasApiController.php',
        'app/Http/Middleware/ApiTokenAuth.php',
        'routes/api.php',
        'app/Models/User.php',
        'app/Services/ExcelExporter.php',
        'resources/views/dashboard/_tabla_lotes.blade.php',
        'vendor/autoload.php',
        '.env',
        '.env.production.example',
        '.htaccess',
        'schema_produccion_limpio.sql'
    )
    $missingEntries = @($requiredEntries | Where-Object { $_ -notin $entryNames })
    if ($missingEntries.Count -gt 0) {
        throw "El ZIP no contiene archivos esenciales: $($missingEntries -join ', ')"
    }
} finally {
    $archive.Dispose()
}

# Eliminar carpeta temporal
Remove-Item $tempDir -Recurse -Force

$zipSizeMb = [Math]::Round(((Get-Item $zipOutput).Length / 1MB), 2)

Write-Host ""
Write-Host "=================================================================" -ForegroundColor Green
Write-Host "  PAQUETE DE PRODUCCION GENERADO CON EXITO!" -ForegroundColor Green
Write-Host "=================================================================" -ForegroundColor Green
Write-Host "Archivo generado: $zipOutput" -ForegroundColor White
Write-Host "Tamano:           $zipSizeMb MB" -ForegroundColor White
Write-Host ""
Write-Host "Pasos para tu hosting DonWeb Ferozo:" -ForegroundColor Cyan
Write-Host "1. Sube '$zipOutput' al Administrador de Archivos de Ferozo."
Write-Host "2. Descomprimelo en tu carpeta del servidor (ej. /sistema-fundo o /public_html)."
Write-Host "3. Abre '.env' (ya incluido y preconfigurado para produccion con APP_KEY) y ajusta tus credenciales MySQL."
Write-Host "4. Solo en una instalacion nueva, importa 'schema_produccion_limpio.sql' en phpMyAdmin de Ferozo."
Write-Host "5. Listo! El sistema se desplegara limpio y operativo."
Write-Host "=================================================================" -ForegroundColor Green
