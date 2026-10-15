# INICIO — Sistema Web del Fundo en Antigravity

**Versión actual:** 1.0.4 (Release Listo para Producción en DonWeb Ferozo).

---

## 1. Estado y Funcionalidades del Sistema
La plataforma ha completado su fase de desarrollo e implementación en `apps/web`:
- **Aplicación:** Laravel 12 con arquitectura limpia por módulos y Vanilla CSS corporativo.
- **Fundos Oficiales:** AGRITAC, PROCOM y EL NEGRO con sus 34 lotes oficiales.
- **Base de Datos:** SQLite en desarrollo / MySQL 8.0 para despliegue en VPS DonWeb Ferozo.
- **PWA:** Funcional con Service Worker v9 ([sw.js](file:///apps/web/public/sw.js)) y [manifest.json](file:///apps/web/public/manifest.json) para Android, iPhone y Escritorio con soporte offline-first.
- **Exportación:** Binaria nativa Microsoft Excel (.xlsx) estructurada sin dependencias pesadas.
- **Calidad y Tests:** 84 pruebas automatizadas PHPUnit pasando con éxito (418 aserciones).

---

## 2. Cómo Levantar y Probar la Aplicación

1. **Abrir la terminal en la carpeta de la web:**
   ```bash
   cd apps/web
   ```

2. **Iniciar el servidor local de desarrollo:**
   ```bash
   php artisan serve
   ```
   *(Actualmente el servidor corre en segundo plano en `http://127.0.0.1:8000`)*.

3. **Acceder desde el navegador:**
   - URL: `http://127.0.0.1:8000`
   - En la pantalla de login, dispones de una **tabla interactiva con las 5 cuentas oficiales** con contraseña predeterminada (`password123`):
     - **Administrador:** `admin@fundo.test` (acceso total a todos los fundos y módulos).
     - **Jefe de fundo:** `jefe@fundo.test` (acceso a fundos asignados, módulos Panel e Historial).
     - **Registrador:** `registrador@fundo.test` (acceso a fundos asignados, módulos Panel, Registro e Historial).
     - **Analista:** `analista@fundo.test` (acceso a todos los fundos, módulo Historial).
     - **Ventas:** `ventas@fundo.test` (acceso a todos los fundos, módulo Historial).

4. **Ejecutar la suite de pruebas automatizadas:**
   ```bash
   php artisan test
   ```

---

## 3. Despliegue en Producción (DonWeb Ferozo)
1. **Generar el ZIP de Producción:**
   Haz doble clic en **`empaquetar_produccion.bat`** en la raíz del proyecto.
   Generará `dist/sistema-fundo-ferozo.zip` con todo incluido.
2. **Guía Maestra Paso a Paso:**
   Consulta [GUIA_DESPLIEGUE_FEROZO_DONWEB.md](file:///docs/05-operacion/GUIA_DESPLIEGUE_FEROZO_DONWEB.md) para subirlo a Ferozo y arrancar desde cero.
