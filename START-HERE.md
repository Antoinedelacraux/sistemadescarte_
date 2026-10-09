# INICIO — Sistema Web del Fundo en Antigravity

**Fase actual:** FASE 6 COMPLETADA — Módulos listos para revisión y feedback del propietario.

---

## 1. Estado y Funcionalidades del Sistema
La plataforma ha completado su fase de desarrollo e implementación en `apps/web`:
- **Aplicación:** Laravel 12 (PHP 8.4) con arquitectura limpia por módulos.
- **Base de Datos:** SQLite en desarrollo / MySQL 8.0 para despliegue en VPS DonWeb.
- **PWA:** Funcional con Service Worker ([sw.js](file:///apps/web/public/sw.js)) y [manifest.json](file:///apps/web/public/manifest.json) para Android, iPhone y Escritorio.
- **Calidad y Tests:** 29 pruebas automatizadas PHPUnit pasando con éxito (111 aserciones).

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
     - **General (Santa Sofía):** `general.sofia@fundo.test` (acceso multi-fundo asignado).
     - **General (Santa Elena):** `general.elena@fundo.test`.
     - **Individual (Santa Sofía):** `individual.sofia@fundo.test` (solo registra en su propio fundo).
     - **Analista:** `analista@fundo.test` (lectura transversal y exportación Excel).

4. **Ejecutar la suite de pruebas automatizadas:**
   ```bash
   php artisan test
   ```

---

## 3. Próximo Paso: Feedback del Propietario
El propietario del proyecto puede navegar por cada uno de los módulos (Registro de Venta, Historial, Reportes analíticos, Exportador a Excel, Administración y UI móvil) para brindar sus observaciones o solicitar ajustes específicos.

