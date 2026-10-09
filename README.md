# Sistema Web del Fundo — Gestión Agrícola y Venta de Descarte

Sistema web empresarial y progresivo (PWA) para el registro, control, análisis y exportación de ventas de descarte agrícola en fundos, diseñado para operar en campo tanto en dispositivos móviles (Android / iOS) como en estaciones de escritorio.

- **Versión:** 0.3.1 (Fase 6 Completada — Listo para Feedback del Propietario)
- **Framework & Backend:** Laravel 12 (PHP 8.4)
- **Frontend & UI:** Blade + Vanilla CSS moderno con Design Tokens (sin Tailwind) + JavaScript Vanilla
- **Base de Datos:** SQLite local (desarrollo) / MySQL 8.0 (producción en VPS DonWeb)
- **Cobertura de Pruebas:** 29 pruebas automatizadas PHPUnit pasando (111 aserciones)
- **Ubicación de la App:** `apps/web/`

---

## Módulos y Funcionalidades Implementadas

1. **Autenticación y Control de Acceso por Roles (RBAC):**
   - 4 roles con permisos diferenciados: `Administrador`, `General`, `Individual` y `Analista`.
   - Cuentas activas/inactivas con bloqueo automático.
   - Cuentas de demostración de un solo clic para pruebas en [login](file:///apps/web/resources/views/auth/login.blade.php).

2. **Aislamiento Multi-Tenant (`FundoScope`):**
   - Filtrado transparente y seguro a nivel de base de datos (`Eloquent Global Scope`).
   - El rol `Individual` solo opera sobre su fundo asignado.
   - El rol `General` visualiza únicamente los fundos que tiene asignados.
   - `Administrador` y `Analista` poseen acceso transversal a todos los fundos.

3. **Catálogos Dinámicos de Campo:**
   - Carga reactiva de Lotes y Cuarteles por Fundo (`GET /api/catalogo/lotes`).
   - Filtrado automático de cuarteles al seleccionar el lote correspondiente.

4. **Registro y Edición de Venta de Descarte:**
   - Validación estricta por motivo: `Campo`, `Packing` y `Cosecha Nacional` (este último exige Cuartel y Tipo de descarte: *Racimos*, *Racimos con plaga*, *Granos*).
   - Cálculo automático en tiempo real del valor total (`Precio (S/) × Kilogramos`).
   - Auditoría completa de creación (`created_by`) y modificación (`updated_by`).

5. **Historial de Ventas:**
   - Filtros por Fundo, Motivo, Rango de fechas (`fecha_desde` / `fecha_hasta`).
   - Paginación y visualización clara con badges de estado y tipo.

6. **Reportes y Exportación Excel:**
   - KPIs consolidados: Kilogramos totales, Valor de venta acumulado y Total de pesajes.
   - Tablas de distribución analítica por Motivo y por Fundo.
   - Exportador a Excel (.csv compatible con UTF-8 BOM) con **selector dinámico de columnas**.

7. **Progresive Web App (PWA) & Modo Offline:**
   - [manifest.json](file:///apps/web/public/manifest.json) con iconos adaptativos para Android e iPhone.
   - Service Worker ([sw.js](file:///apps/web/public/sw.js)) con estrategia de caché offline para recursos estáticos y visualización fuera de línea.

8. **Módulo de Administración:**
   - Gestión de Fundos: creación de nuevas sedes agrícolas y conmutación de estado.
   - Gestión de Usuarios: creación con asignación de roles y vinculación multi-fundo.

9. **Optimización Responsive Integral para Celulares:**
   - Tipografía fluida con `clamp()` que previene solapamiento y saltos de línea irregulares.
   - Tablas blindadas con scroll horizontal suave (`.table-wrapper`) y `min-width` calibrado.
   - Cuadrículas simétricas de botones táctiles (44px - 48px) para operación con una mano en campo.
   - Prevención de auto-zoom invasivo en navegadores móviles (iOS Safari).

---

## Ejecución Local

```bash
# Acceder a la aplicación
cd apps/web

# Iniciar servidor local
php artisan serve

# Ejecutar las 29 pruebas automatizadas
php artisan test
```

Acceder en el navegador a: `http://127.0.0.1:8000`

---

## Estructura de Documentación (`docs/`)

- [docs/00-gestion/](file:///docs/00-gestion/): Estado actual ([ESTADO_PROYECTO.md](file:///docs/00-gestion/ESTADO_PROYECTO.md)), [BACKLOG.md](file:///docs/00-gestion/BACKLOG.md), [REGISTRO_CAMBIOS.md](file:///docs/00-gestion/REGISTRO_CAMBIOS.md).
- [docs/01-requisitos/](file:///docs/01-requisitos/): Requisitos aprobados, matrices de requerimientos y trazabilidad.
- [docs/02-arquitectura/](file:///docs/02-arquitectura/): ADRs, contratos API y modelo ER de datos.
- [docs/03-diseno/](file:///docs/03-diseno/): Guía de UI/UX, tokens CSS y wireframes.
- [docs/04-calidad/](file:///docs/04-calidad/): Matriz de pruebas automatizadas y checklist de release.
- [docs/05-operacion/](file:///docs/05-operacion/): Plan de despliegue para VPS DonWeb, Runbook y seguridad.
- [docs/06-mantenimiento/](file:///docs/06-mantenimiento/): Procedimiento de gestión de cambios posteriores.

