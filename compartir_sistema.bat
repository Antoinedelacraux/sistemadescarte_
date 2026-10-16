@echo off
title Compartir Sistema Fundo - TALSA
echo ============================================================
echo   SISTEMA FUNDO TALSA - COMPARTIR CON ENLACE HTTPS SEGURO
echo ============================================================
echo.
echo Iniciando servidor local y creando enlace publico...
echo.

start "Laravel Server" /min cmd /c "cd /d apps\web && php artisan serve --host=127.0.0.1 --port=8080"
timeout /t 2 /nobreak >nul
start "Tunel Publico Cloudflare" cmd /k "scripts\bin\cloudflared.exe tunnel --url http://127.0.0.1:8080"
