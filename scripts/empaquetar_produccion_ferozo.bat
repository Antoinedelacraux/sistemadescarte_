@echo off
chcp 65001 > nul
echo =================================================================
echo   EMPAQUETANDO SISTEMA PARA PRODUCCION (DONWEB FEROZO)
echo =================================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0empaquetar_produccion_ferozo.ps1"
pause
