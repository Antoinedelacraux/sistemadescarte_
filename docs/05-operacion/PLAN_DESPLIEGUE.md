# Instrucciones de Despliegue para el Administrador del VPS

El proyecto está diseñado como un **monolito modular en Laravel 13 (PHP 8.4)**. 
Dado que el desarrollo y empaquetado se realiza en otro entorno, este documento provee los requerimientos e instrucciones exactas para que el administrador del VPS Ferozo/DonWeb ponga la aplicación en producción.

## 1. Requerimientos del Servidor Ferozo
- PHP >= 8.4.
- Extensiones PHP requeridas: `bcmath`, `ctype`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `cURL`.
- MySQL >= 8.0.
- Servidor web (Apache 2.4.x / Nginx).
- Soporte para **HTTPS obligatorio** (Certificado SSL válido requerido para la PWA y Service Workers).

## 2. Preparación de la Base de Datos
1. Crear una base de datos nueva exclusivamente para este sistema (ej. `fundo_prod`).
2. Crear un usuario de base de datos exclusivo y asignarle permisos completos solo a `fundo_prod`.
3. Anotar el nombre de la DB, usuario y contraseña.

## 3. Configuración del Servidor Web (DocumentRoot)
**CRÍTICO PARA LA SEGURIDAD:** 
El dominio o subdominio asignado al sistema (ej. `descarte.mifundo.com`) debe apuntar **exclusivamente** a la carpeta `/public` del proyecto, **NUNCA** a la raíz del repositorio.

**Estructura esperada en el VPS:**
```text
/home/usuario_ferozo/sistemas/sistema-fundo/        <-- Raíz del proyecto (fuera de public_html)
   ├── app/
   ├── bootstrap/
   ├── config/
   ├── public/                                      <-- El subdominio DEBE apuntar aquí
   │   ├── index.php
   │   ├── build/ (Assets compilados)
   ├── resources/
   ├── routes/
   ├── storage/
   ├── .env
```
Si el panel de Ferozo obliga a usar `/public_html` para el dominio principal, sugerimos crear el proyecto en `/home/.../sistema-fundo` y crear un *symlink* (acceso directo) desde `/public_html` hacia `/home/.../sistema-fundo/public`.

## 4. Pasos de Despliegue de la Primera Versión
El equipo de desarrollo enviará un paquete ZIP compilado (sin la carpeta `/node_modules` pero con la carpeta `/vendor` y `/public/build` ya precompilada).

1. Subir y extraer el ZIP en el directorio del proyecto en el servidor.
2. Copiar `.env.example` a `.env` y configurar las credenciales:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://descarte.mifundo.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fundo_prod
   DB_USERNAME=usuario_asignado
   DB_PASSWORD=contraseña_fuerte
   ```
3. Generar la clave de la aplicación (solo la primera vez):
   `php artisan key:generate`
4. Asignar permisos de escritura a las carpetas (importante en Ferozo/Apache):
   `chmod -R 775 storage bootstrap/cache`
5. Ejecutar las migraciones y seeders iniciales (roles y administrador):
   `php artisan migrate --seed`
6. Optimizar caché para producción:
   `php artisan config:cache`
   `php artisan route:cache`
   `php artisan view:cache`

## 5. Actualizaciones Futuras (Hoja de ruta)
Para nuevas versiones, el administrador del VPS solo deberá:
1. Reemplazar los archivos con el nuevo ZIP.
2. Ejecutar `php artisan migrate` (las migraciones están diseñadas para ser seguras).
3. Limpiar las cachés (`php artisan optimize:clear` y luego `optimize`).
