# Guía Maestra de Despliegue en Producción — DonWeb Ferozo
## Sistema Web de Gestión Agrícola — Venta de Fruta de Descarte (TALSA)

Este manual detalla paso a paso el procedimiento oficial para subir, configurar y poner en marcha el sistema en un servicio de hosting o servidor VPS con panel **Ferozo (DonWeb)**, asegurando que inicie **desde cero** con 0 ventas históricas, listo para la operación diaria del personal agrícola.

---

## 1. Requisitos Previos en el Panel Ferozo

Verifica en tu panel de control Ferozo:
1. **Versión de PHP:** PHP 8.2, 8.3 o 8.4 (en Ferozo: *Avanzado* -> *Configuración de PHP*).
2. **Extensiones PHP activas:** `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `cURL`, `bcmath`, `fileinfo`, `gd`. (Vienen activadas por defecto en Ferozo).
3. **Motor de Base de Datos:** MySQL 8.0 o MariaDB 10.4+.
4. **Certificado SSL / HTTPS:** Indispensable para la PWA y funcionamiento offline. Ferozo incluye Let's Encrypt gratuito (*Seguridad* -> *Certificados SSL*).

---

## 2. Paso a Paso para la Puesta en Marcha

### Paso 1: Crear la Base de Datos en Ferozo
1. Ingresa a tu panel Ferozo y ve a **Bases de Datos** -> **Bases de Datos MySQL**.
2. Haz clic en **Crear Base de Datos** (por ejemplo: `talsa_descarte`).
3. Crea un usuario MySQL exclusivo (por ejemplo: `talsa_user`) con una contraseña segura.
4. Asigna el usuario a la base de datos con **todos los privilegios**.
5. Anota los datos:
   - **Servidor:** `127.0.0.1` o `localhost`
   - **Base de Datos:** `usuario_talsa_descarte` (Ferozo suele anteponer el prefijo de tu cuenta)
   - **Usuario:** `usuario_talsa_user`
   - **Contraseña:** `TuContrasenaSegura2026!`

---

### Paso 2: Generar el Paquete de Producción
En tu equipo local de desarrollo:
1. Abre la carpeta del proyecto.
2. Ejecuta el script de empaquetado:
   - Haciendo doble clic directamente en **`empaquetar_produccion.bat`** (en la raíz del proyecto)
   - O en PowerShell:
     ```powershell
     .\scripts\empaquetar_produccion_ferozo.ps1
     ```
3. El script ejecutará automáticamente las pruebas (`php artisan test`) y generará el archivo:
   `dist/sistema-fundo-ferozo.zip` (incluye toda la aplicación, librerías de producción `vendor`, archivo de blindaje `.htaccess` y el esquema SQL limpio).

---

### Paso 3: Subir y Descomprimir en Ferozo
1. En el panel Ferozo, ve a **Archivos** -> **Administrador de Archivos** (o conéctate por FTP/SFTP mediante FileZilla).
2. Sube el archivo `dist/sistema-fundo-ferozo.zip`.
3. **Estructura según tu tipo de cuenta:**

#### Opción A (Recomendada: Subdominio o Dominio con DocumentRoot personalizable):
- Descomprime el ZIP en una carpeta propia, por ejemplo: `/home/tu_usuario/sistema-fundo/`.
- En Ferozo -> **Dominios / Subdominios**, edita el subdominio (ej. `descarte.talsa.com.pe`) y configura el **Directorio Raíz (DocumentRoot)** apuntando a:
  `/home/tu_usuario/sistema-fundo/public`
- *De esta forma, los archivos de backend (`.env`, `app`, `storage`) quedan 100% protegidos fuera del alcance público web.*

#### Opción B (Hosting Compartido con DocumentRoot fijo a `public_html`):
- Si tu plan de Ferozo no permite cambiar el DocumentRoot y exige usar `/public_html`:
  - Descomprime el ZIP directamente en `/public_html/`.
  - El sistema incluye un archivo `.htaccess` en la raíz que redirige automáticamente todo el tráfico web a `/public` y bloquea cualquier intento de descarga de `.env`, `.git` o logs.
  - Alternativamente, puedes mover el contenido de `public/` al interior de `public_html/` y el resto de carpetas un nivel arriba.

---

### Paso 4: Configurar el Archivo `.env`
1. En el directorio de la aplicación en Ferozo, renombra o copia `.env.production.example` como **`.env`**.
   *(Nota de seguridad: `.env.production.example` ya viene con una `APP_KEY` segura preconfigurada de 32 bytes, por lo que el sistema no fallará ni requerirá consola SSH obligatoria).*
2. Edita los valores principales de tu conexión a base de datos y URL:
   ```env
   APP_NAME="TALSA Fundo"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://descarte.tudominio.com
   APP_TIMEZONE=America/Lima

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nombre_de_tu_base_en_ferozo
   DB_USERNAME=usuario_mysql_ferozo
   DB_PASSWORD=contrasena_mysql_ferozo

   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   ```
3. **Permisos de carpetas:**
   Asegúrate de que las siguientes carpetas tengan permisos de escritura (`chmod 775` o `755`):
   - `storage/` (y todas sus subcarpetas: `logs`, `framework/cache`, `framework/sessions`, `framework/views`)
   - `bootstrap/cache/`

---

### Paso 5: Inicializar la Base de Datos Limpia (Cero Ventas)

Tienes dos alternativas igualmente rápidas:

#### Alternativa A — Mediante phpMyAdmin en Ferozo (Sin necesidad de SSH):
1. En Ferozo, ve a **Bases de Datos** -> **phpMyAdmin**.
2. Selecciona tu base de datos creada en el menú de la izquierda.
3. Haz clic en la pestaña superior **Importar**.
4. Selecciona el archivo **`schema_produccion_limpio.sql`** (incluido tanto en la raíz del ZIP como en la carpeta `database/`).
5. Haz clic en **Continuar / Importar**.
6. ¡Listo! Se habrán creado todas las tablas, los 5 roles oficiales, los 3 fundos, los 34 lotes oficiales y el Administrador inicial, con **0 registros en ventas**.

#### Alternativa B — Mediante Consola SSH o Terminal Web de Ferozo:
Si tu servidor cuenta con acceso a terminal:
```bash
cd /ruta/hacia/sistema-fundo
php artisan key:generate
php artisan migrate --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Paso 6: Activar Certificado SSL Gratuito
1. En el panel Ferozo, ve a **Seguridad** -> **Certificados SSL**.
2. Selecciona tu dominio/subdominio.
3. Elige la opción **Let's Encrypt (Gratuito)** y haz clic en **Instalar / Generar**.
4. Activa la opción **Forzar HTTPS** en Ferozo.  
   *(Esto es obligatorio para que los celulares reconozcan la aplicación como PWA instalable y el Service Worker guarde datos offline en campo).*

---

## 3. Acceso Inicial y Configuración del Personal

1. **Ingreso del Administrador Inicial:**
   - URL: `https://tu-dominio.com/login`
   - Usuario: `admin@fundo.test`
   - Contraseña inicial: `password123`
   *(Si utilizaste la Alternativa B por SSH, puedes personalizarlo con `php artisan fundo:crear-admin`).*
2. **Cambiar la contraseña del Administrador:**
   - Inicia sesión y ve al menú de administración para actualizar tu correo y contraseña definitiva.
3. **Crear las cuentas del personal de venta de descarte:**
   - Dirígete a la sección de usuarios y crea las cuentas según sus responsabilidades:
     - **Jefe de fundo:** Accede al Panel de control e Historial. Asignar su fundo.
     - **Registrador:** Accede al Registro e Historial. Asignar los fundos donde pesa y despacha fruta.
     - **Analista:** Accede al Historial consolidado de todos los fundos.
     - **Ventas:** Accede al Historial consolidado de todos los fundos.
4. **Comprobación:**
   - La tabla de ventas inicia en **cero (0) registros**.
   - Cada pesador o registrador comenzará a ingresar los pesajes reales de fruta en campo desde su celular o laptop.

---

## 4. Respaldos y Mantenimiento Periódico

- **Respaldos Automáticos:** En Ferozo -> **Copias de Seguridad**, programa un respaldo diario de la base de datos MySQL.
- **Modo Mantenimiento:** Para futuras actualizaciones menores, puedes ejecutar en SSH:
  ```bash
  php artisan down --secret="clave_temporal_para_ti"
  # Aplicar cambios
  php artisan up
  ```
- **Sin pérdida de datos:** Siguiendo las reglas del proyecto (`AGENTS.md`), ninguna actualización futura sobrescribirá la información de ventas ya registrada por el personal.
