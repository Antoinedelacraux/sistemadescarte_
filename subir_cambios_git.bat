@echo off
title Subir Cambios a Git - Sistema Fundo TALSA
echo ============================================================
echo   SISTEMA FUNDO TALSA - SUBIR CAMBIOS A GIT (v1.0.8)
echo ============================================================
echo.
echo [1/4] Verificando estado actual del repositorio...
git status --short
echo.
echo [2/4] Preparando archivos modificados y documentacion...
git add -u
git add apps\web\public\propuestas-notificacion-vencimiento.html
git add subir_cambios_git.bat
echo Archivos preparados correctamente.
echo.
echo [3/4] Creando commit local seguro...
git commit -m "feat(ventas): bloqueo en tiempo real de edicion y anulacion al expirar 24h (v1.0.8)"
echo.
echo [4/4] Subiendo cambios al repositorio remoto (git push)...
git push
echo.
echo ============================================================
echo   PROCESO COMPLETADO SATISFACTORIAMENTE
echo ============================================================
pause
