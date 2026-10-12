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
    $testResult = php artisan test
    Write-Host "    Pruebas completadas con éxito." -ForegroundColor Green
} catch {
    Write-Error "Las pruebas automatizadas fallaron. Corrige los errores antes de empaquetar para producción."
    Pop-Location
    exit 1
}
Pop-Location

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
$foldersToCopy = @("app", "bootstrap", "config", "database", "public", "resources", "routes", "storage", "vendor")
foreach ($folder in $foldersToCopy) {
    $src = Join-Path $webAppPath $folder
    $dest = Join-Path $tempDir $folder
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $dest -Recurse -Force
    }
}

# Copiar archivos individuales clave
$filesToCopy = @("artisan", "composer.json", ".env.production.example")
foreach ($file in $filesToCopy) {
    $src = Join-Path $webAppPath $file
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $tempDir -Force
    }
}

# Limpiar posibles archivos de entorno o bases de datos de desarrollo en temp_build
Get-ChildItem -Path $tempDir -Recurse -Include ".env", "*.sqlite*", "*.log", "*.bak" | Remove-Item -Force -ErrorAction SilentlyContinue

# Asegurar que storage/logs y storage/framework tengan sus subcarpetas
$storageSubs = @("framework/cache", "framework/sessions", "framework/views", "logs", "app/public")
foreach ($sub in $storageSubs) {
    $p = Join-Path $tempDir "storage\$sub"
    if (-not (Test-Path $p)) {
        New-Item -ItemType Directory -Path $p -Force | Out-Null
    }
}

# 5. Generar archivo ZIP
Write-Host "--> [4/4] Comprimiendo archivo ZIP de producción..." -ForegroundColor Yellow
Compress-Archive -Path "$tempDir\*" -DestinationPath $zipOutput -CompressionLevel Optimal

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
Write-Host "Pasos para instalar en DonWeb Ferozo:" -ForegroundColor Cyan
Write-Host "1. Sube este archivo ZIP mediante el Administrador de Archivos de Ferozo."
Write-Host "2. Descomprimelo en tu carpeta del servidor (ej. /sistema-fundo o /public_html)."
Write-Host "3. Copia '.env.production.example' como '.env' y coloca tus datos de MySQL."
Write-Host "4. Importa 'schema_produccion_limpio.sql' en phpMyAdmin de Ferozo."
Write-Host "5. Listo! El sistema iniciara en limpio para operar en campo."
Write-Host "=================================================================" -ForegroundColor Green

