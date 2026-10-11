# INICIO — Sistema Web del Fundo en Antigravity

**Versión actual:** 0.5.8 (Módulos completados, probados y verificados).

---

## 1. Estado y Funcionalidades del Sistema
La plataforma ha completado su fase de desarrollo e implementación en `apps/web`:
- **Aplicación:** Laravel 12 (PHP 8.4) con arquitectura limpia por módulos.
- **Fundos Oficiales:** AGRITAC, PROCOM y EL NEGRO con sus 34 lotes oficiales.
- **Base de Datos:** SQLite en desarrollo / MySQL 8.0 para despliegue en VPS DonWeb.
- **PWA:** Funcional con Service Worker ([sw.js](file:///apps/web/public/sw.js)) y [manifest.json](file:///apps/web/public/manifest.json) para Android, iPhone y Escritorio.
- **Exportación:** Binaria nativa Microsoft Excel (.xlsx) estructurada.
- **Calidad y Tests:** 55 pruebas automatizadas PHPUnit pasando con éxito (242 aserciones).

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
   - En la pantalla de login, dispones de una **tabla interactiva con cuentas de prueba** por rol con contraseña predeterminada (`password123`):
     - **Administrador:** `admin@fundo.test` (acceso a todos los fundos, usuarios y reportes).
     - **General (Multi-Fundo):** `general.agritac@fundo.test` (acceso a fundos asignados).
     - **Individual (AGRITAC):** `individual.agritac@fundo.test` (solo registra en AGRITAC).
     - **Individual (PROCOM):** `individual.procom@fundo.test` (solo registra en PROCOM).
     - **Analista:** `analista@fundo.test` (lectura transversal y exportación Excel).

4. **Ejecutar la suite de pruebas automatizadas:**
   ```bash
   php artisan test
   ```

---

## 3. Próximo Paso: Handoff y Despliegue
La plataforma se encuentra validada y lista para empaquetado y transferencia al servidor productivo VPS DonWeb.
