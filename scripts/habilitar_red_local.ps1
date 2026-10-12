# Script para permitir conexiones entrantes en el puerto 8000 del Sistema Fundo
# Requiere ejecutarse con permisos de Administrador

Write-Host "=== Habilitando acceso de red local para el Sistema Fundo ===" -ForegroundColor Cyan

try {
    # 1. Desbloquear regla de php.exe en el firewall
    Set-NetFirewallRule -DisplayName "php.exe" -Action Allow -ErrorAction SilentlyContinue

    # 2. Crear o actualizar regla explicita de entrada para el puerto 8000 TCP
    $rule = Get-NetFirewallRule -DisplayName "Sistema Fundo Web (Puerto 8000)" -ErrorAction SilentlyContinue
    if (-not $rule) {
        New-NetFirewallRule -DisplayName "Sistema Fundo Web (Puerto 8000)" `
                            -Direction Inbound `
                            -LocalPort 8000 `
                            -Protocol TCP `
                            -Action Allow `
                            -Profile Any
    } else {
        Set-NetFirewallRule -DisplayName "Sistema Fundo Web (Puerto 8000)" -Action Allow -Enabled True
    }

    Write-Host "`n[EXITO] Puerto 8000 habilitado en el Firewall de Windows." -ForegroundColor Green
    Write-Host "Otras laptops y celulares en el mismo Wi-Fi ya pueden ingresar a:" -ForegroundColor Yellow
    Write-Host ">>> http://192.168.1.113:8000 <<<`n" -ForegroundColor White
} catch {
    Write-Host "`n[ERROR] No se pudo modificar el Firewall: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Asegurate de hacer clic derecho y 'Ejecutar con PowerShell' o como Administrador." -ForegroundColor Yellow
}
