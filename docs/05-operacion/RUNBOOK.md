# Runbook de Operación — Sistema Web del Fundo

Procedimientos operativos estándar para el mantenimiento, arranque, monitoreo y soporte de la aplicación en desarrollo y producción (VPS DonWeb).

---

## 1. Operación en Entorno Local (Desarrollo)

### Iniciar y Detener la Aplicación
```bash
# Navegar al directorio de la aplicación
cd apps/web

# Iniciar servidor local
php artisan serve

# Para detener: presionar Ctrl + C en la terminal
```

### Ejecutar Pruebas Automatizadas
```bash
# Correr la suite completa de tests
php artisan test

# Correr solo pruebas unitarias o de un módulo específico
php artisan test --filter=VentaDescarteTest
```

### Limpieza de Caché Local
```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
```

---

## 2. Operación en Producción (VPS DonWeb)

### Comandos de Despliegue y Mantenimiento
```bash
# Poner en modo mantenimiento si se requiere intervención mayor
php artisan down --secret="mantenimiento-tal-2026"

# Ejecutar migraciones pendientes
php artisan migrate --force

# Optimizar cachés de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reactivar la aplicación
php artisan up
```

### Gestión de Servicios del Servidor
```bash
# Reiniciar Nginx
sudo systemctl restart nginx

# Reiniciar PHP 8.4-FPM
sudo systemctl restart php8.4-fpm

# Estado de la base de datos MySQL
sudo systemctl status mysql
```

---

## 3. Logs y Monitoreo de Incidentes
- **Logs de Laravel:** `apps/web/storage/logs/laravel.log`
- **Logs de Nginx (Producción):** `/var/log/nginx/fundo_error.log` y `/var/log/nginx/fundo_access.log`
- **Monitoreo en tiempo real:**
  ```bash
  tail -f storage/logs/laravel.log
  ```

---

## 4. Estrategia de Copias de Seguridad (Backups)
- **Base de Datos MySQL (Producción):** Dump diario automatizado vía Cron a las 02:00 AM guardado en `/backups/db/` con retención de 30 días.
  ```bash
  mysqldump -u fundo_user -p fundo_db | gzip > /backups/db/fundo_$(date +\%F).sql.gz
  ```
- **Restauración de Emergencia:**
  ```bash
  gunzip < /backups/db/fundo_YYYY-MM-DD.sql.gz | mysql -u fundo_user -p fundo_db
  ```

