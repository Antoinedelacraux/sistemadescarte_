# ADR-001 — Stack Principal y Arquitectura

**Estado:** APROBADA (Implementada y Verificada).

## Contexto
El sistema requiere funcionamiento offline parcial mediante PWA, responsividad extrema en smartphones de campo y estaciones de escritorio, alta mantenibilidad a futuro y despliegue sin dependencias complejas de Node.js en el VPS de producción (Ubuntu 24.04 LTS, Apache/Nginx, PHP 8.4, MySQL 8.0 en DonWeb).

## Decisión Técnica
- **Framework Base:** Monolito Modular en Laravel 12 (PHP 8.4) para Backend y renderizado del lado servidor (SSR con Blade).
- **Frontend & UI:** Blade + Vanilla CSS moderno estructurado con Design Tokens (sin Tailwind CSS ni frameworks pesados para evitar desbordes y colisiones visuales) y JavaScript Vanilla para interactividad fluida.
- **Autenticación:** Sesión/cookies seguras con PWA (RBAC con 4 roles: Administrador, General, Individual, Analista).
- **Base de Datos:** SQLite local en desarrollo / MySQL 8.0 en VPS productivo.
- **Aislamiento Multi-tenant:** Uso de *Global Scopes* en Laravel (`FundoScope`) por Fundo. Esto blinda a la aplicación contra inyecciones y alteraciones no autorizadas de URL.
- **Sincronización:** Frontend PWA con Service Worker ([sw.js](file:///apps/web/public/sw.js)) y manifest adaptativo; Backend expone APIs y transacciones protegidas vía UUID v4.
- **Exportación:** Motor nativo binario OpenXML Microsoft Excel (`.xlsx`) con estilos corporativos y tipado contable estricto.

## Método de Entrega
Se estructura el repositorio en `apps/web/` conteniendo toda la solución Laravel autocontenida. Se provee `PLAN_DESPLIEGUE.md` dirigido al administrador de infraestructura con requerimientos y pasos exactos sin requerir compilación Node.js en caliente en el VPS.

## Estructura del repositorio
```
.agents/             (reglas, roles de agentes y skills persistentes de Antigravity)
docs/                (especificación, arquitectura, calidad, gestión y operación)
apps/web/            (Laravel 12: app/, database/, resources/, public/, tests/)
scripts/             (utilidades de verificación de estructura)
```

## Modelo de datos
- `roles`, `users`, `fundos`, `fundo_user`.
- `lotes`, `cuarteles` (catálogos dinámicos vinculados por sede).
- `ventas_descarte`: fundo_id, lote_id, cuartel_manual/cuartel_id, cliente, ruc, motivo, tipo_descarte, fecha_produccion, precio, kilogramos, jabas, peso_jaba, valor_venta, placa, conductor, brevete, viaje, observacion, estado (activo/anulado), created_by, updated_by, anulado_by, anulado_at.

## Restricciones del despliegue
- Laravel debe exponerse exclusivamente a través de `public/`, nunca toda la raíz del proyecto.
- Permisos de escritura asegurados en `storage/` y `bootstrap/cache/`.
- Precompilación y versionado estático de assets, manteniendo `.env` fuera del repositorio.
- Procedimientos de backup, recuperación y rollback probados antes de puesta en producción.

## Pruebas no negociables
Permisos cross-fundo y exportación, exactitud decimal contable, filtros reactivos, auditoría de 2 pasos (anular y eliminar), responsive en móviles, migraciones seguras y suite completa de 55 pruebas automatizadas pasando.
