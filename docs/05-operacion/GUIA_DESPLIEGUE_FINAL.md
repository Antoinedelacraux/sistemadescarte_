# Runbook final de despliegue — Sistema Fundo

Destino: DonWeb Ferozo / hosting compatible con Laravel.

Este documento es la guía operativa final para instalar o actualizar el sistema. Está escrito para proteger ventas, usuarios, fundos, lotes y configuraciones existentes.

## Estado de la versión

- Pruebas automatizadas: 94 pruebas, 504 aserciones, código de salida 0.
- PHP soportado: 8.3 u 8.4.
- Paquete: `dist/sistema-fundo-ferozo.zip`.
- Dependencias PHP: incluidas dentro de `vendor/`; no es necesario ejecutar Composer en el servidor.
- Migraciones nuevas en esta versión: ninguna.
- La nueva portada se encuentra en `public/images/login-bg.jpg`.

Regla principal: `schema_produccion_limpio.sql` se utiliza únicamente para una instalación nueva. Nunca se importa sobre una base que ya contiene ventas reales.

## 1. Requisitos del servidor

Confirma en Ferozo antes de subir archivos:

- PHP 8.3 u 8.4.
- Extensiones `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `bcmath`, `fileinfo`, `gd` y `zip`.
- MySQL 8.0 o MariaDB 10.4 o superior.
- HTTPS activo con certificado válido.
- DocumentRoot apuntando a la carpeta `public/` del proyecto.
- Escritura habilitada para `storage/` y `bootstrap/cache/`.
- Acceso a SSH o terminal web recomendado para generar la clave, limpiar cachés y ejecutar pruebas.

Si el hosting no permite apuntar el DocumentRoot a `public/`, se puede utilizar el `.htaccess` incluido en la raíz, pero debe probarse específicamente en Ferozo. La opción recomendada sigue siendo `.../sistema-fundo/public`.

## 2. Validación local antes de subir

Desde la raíz del proyecto en Windows:

```powershell
cd "D:\$MCHUMBES\OneDrive - TAL S.A\Escritorio\sistema-fundo"
cd apps/web
php artisan test
composer check-platform-reqs --no-dev
php artisan view:cache
cd ../..
.\scripts\empaquetar_produccion_ferozo.ps1
```

El empaquetador no debe generar el ZIP si las pruebas fallan o si faltan archivos esenciales. Sube únicamente `dist/sistema-fundo-ferozo.zip`; no subas `.env`, bases SQLite, logs, backups ni la carpeta temporal de empaquetado.

## 3. Instalación nueva, sin ventas históricas

Usa esta sección solo si la base de datos está recién creada y no contiene información real.

### 3.1 Crear la base de datos

En Ferozo:

1. Crea una base MySQL y un usuario exclusivo.
2. Asigna al usuario permisos sobre esa base.
3. Registra host, puerto, nombre, usuario y contraseña sin publicarlos en Git ni en tickets.

### 3.2 Subir el paquete

1. Sube `dist/sistema-fundo-ferozo.zip`.
2. Descomprímelo, por ejemplo, en `/home/USUARIO/sistema-fundo/`.
3. Configura el DocumentRoot en `/home/USUARIO/sistema-fundo/public`.
4. Si el panel exige `public_html`, utiliza el `.htaccess` raíz incluido y prueba que `/app`, `/database`, `/storage` y `.env` no sean descargables.

### 3.3 Crear y proteger `.env`

1. Copia `.env.production.example` como `.env`.
2. Configura `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` con HTTPS, `APP_KEY` y los datos MySQL.
3. Genera una clave exclusiva para esta instalación:

```bash
php artisan key:generate
```

Si no existe SSH, genera el valor en un entorno local con `php artisan key:generate --show` y cópialo únicamente al `.env` del servidor. No reutilices la clave de otro ambiente.

4. Define `ADMIN_EMAIL`, `ADMIN_NAME` y una contraseña única en `ADMIN_PASSWORD` antes de ejecutar el seed por consola.
5. No dejes los valores de ejemplo del archivo `.env.production.example`.

### 3.4 Inicializar el esquema: elige una sola alternativa

Alternativa A — phpMyAdmin, sin SSH:

1. Importa `schema_produccion_limpio.sql` en la base recién creada.
2. No ejecutes `migrate:fresh` después de importar el esquema.
3. Ejecuta `php artisan fundo:crear-admin` si tienes terminal para reemplazar inmediatamente la credencial administrativa inicial.

Alternativa B — SSH o terminal web:

```bash
cd /ruta/hacia/sistema-fundo
php artisan migrate --seed --force
php artisan fundo:crear-admin
```

El comando de administrador solicita el correo, nombre y contraseña de forma interactiva. No uses `fundo:resetear-usuarios` en producción: está bloqueado para ese entorno y es un comando de desarrollo.

### 3.5 Permisos y cachés

```bash
chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Comprueba que `.env` no sea descargable y que `storage/logs` pueda recibir errores.

## 4. Actualización de una producción existente

Esta es la ruta correcta para una instalación que ya tiene ventas, usuarios o catálogos reales.

### 4.1 Respaldo previo obligatorio

Realiza un dump transaccional antes de modificar archivos:

```bash
mysqldump -u USUARIO -p --single-transaction --quick --routines --triggers BASE_DATOS > /ruta/segura/fundo_YYYYMMDD_HHMMSS.sql
gzip /ruta/segura/fundo_YYYYMMDD_HHMMSS.sql
```

Conserva una copia fuera del servidor. Un respaldo solo se considera válido después de restaurarlo en una base aislada y comprobar, como mínimo, el conteo de `ventas_descarte`, los totales, usuarios, fundos y lotes.

### 4.2 Publicar la nueva versión

1. Define una ventana fuera del horario de pesaje y despacho.
2. Activa mantenimiento si tienes SSH:

```bash
php artisan down --secret="CLAVE_TEMPORAL_UNICA"
```

3. Conserva el `.env` actual. No lo reemplaces con `.env.production.example`.
4. Conserva una copia de la versión anterior para rollback.
5. Descomprime el nuevo ZIP en una carpeta de versión nueva o reemplaza únicamente el código aprobado.
6. Verifica que `vendor/`, `public/`, `storage/` y `bootstrap/cache/` tengan permisos correctos.
7. Para esta versión, no importes `schema_produccion_limpio.sql`, no ejecutes `migrate:fresh`, no ejecutes seeds y no necesitas ejecutar `php artisan migrate`.
8. Reconstruye cachés:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

9. Ejecuta las comprobaciones posteriores de la sección 5.
10. Si todo está correcto, desactiva mantenimiento:

```bash
php artisan up
```

## 5. Pruebas posteriores al despliegue

Registra evidencia de cada verificación:

- La URL responde por HTTPS y muestra código HTTP 200.
- El certificado no presenta advertencias.
- El login funciona con una cuenta real autorizada.
- El Registrador ve Panel, Registro e Historial, limitado a sus fundos asignados.
- El Panel de Control conserva los totales históricos.
- La tabla por lote y la exportación XLSX funcionan.
- La exportación conserva encabezados y no incluye la fila `TOTAL GENERAL`.
- El registro de una venta de prueba controlada calcula el total correctamente.
- El historial mantiene los registros existentes.
- La PWA carga desde celular y el Service Worker funciona bajo HTTPS.
- `storage/logs/laravel.log` no muestra errores nuevos después de las pruebas.

No dejes una venta de prueba en producción. Si fue necesario crearla, elimínala únicamente con el procedimiento autorizado por el propietario y deja constancia de la operación.

## 6. Rollback

Si falla el smoke test:

1. Activa mantenimiento.
2. Conserva logs y la evidencia del error.
3. Restaura el código anterior y el `.env` anterior.
4. Limpia y reconstruye cachés.
5. Repite las pruebas de login e historial.
6. No restaures la base de datos salvo que exista evidencia de daño y autorización explícita; el rollback de código de esta versión no requiere modificar el esquema.
7. Si fuera necesario restaurar la base, utiliza únicamente un respaldo previamente ensayado y una ventana aprobada.

## 7. Criterio de aprobación

El despliegue queda en estado **GO** solo cuando el propietario confirma:

- respaldo restaurable,
- staging o prueba equivalente,
- configuración PHP/MySQL/HTTPS,
- smoke test exitoso,
- cambio de la credencial administrativa inicial,
- ausencia de errores en logs.

Si cualquiera queda pendiente, el estado es **NO-GO** y no debe exponerse el sistema a usuarios reales.

## 8. Prohibiciones

Nunca ejecutes en producción:

```bash
php artisan migrate:fresh
php artisan migrate:reset
php artisan db:wipe
php artisan fundo:resetear-usuarios
```

Tampoco importes el esquema limpio sobre una base existente ni compartas el `.env`, respaldos o contraseñas por Git, correo o capturas de pantalla.
