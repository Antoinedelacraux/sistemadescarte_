@echo off
chcp 65001 > nul
echo =================================================================
echo   EMPAQUETANDO SISTEMA PARA PRODUCCION (DONWEB FEROZO)
echo   Ejecutando via CMD / PHP (Sin requerir PowerShell ni Admin)
echo =================================================================

php "%~dp0empaquetar_produccion.php"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Ocurrio un problema durante el empaquetado.
)
echo.
pause
