@echo off
:: Solicitar permisos de Administrador si no los tiene
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Solicitando permisos de Administrador...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

title Habilitar acceso de red local - Sistema Fundo (TALSA)
echo ============================================================
echo   Habilitando Acceso para Laptops y Celulares en Red Local
echo ============================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command "Set-NetFirewallRule -DisplayName 'php.exe' -Action Allow -ErrorAction SilentlyContinue; $r = Get-NetFirewallRule -DisplayName 'Sistema Fundo Web (Puerto 8000)' -ErrorAction SilentlyContinue; if (-not $r) { New-NetFirewallRule -DisplayName 'Sistema Fundo Web (Puerto 8000)' -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -Profile Any } else { Set-NetFirewallRule -DisplayName 'Sistema Fundo Web (Puerto 8000)' -Action Allow -Enabled True }"

echo.
echo ============================================================
echo  [EXITO] Firewall configurado correctamente.
echo.
echo  Cualquier laptop o celular conectado a este mismo Wi-Fi
echo  puede ingresar en su navegador a:
echo.
echo      http://192.168.1.113:8000
echo.
echo ============================================================
echo.
pause
