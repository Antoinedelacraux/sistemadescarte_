# Sistema Web del Fundo — TALSA Grape Farms (Gestión Agrícola y Venta de Descarte)

Sistema web empresarial y progresivo (PWA) para el registro, control, análisis y exportación de ventas de descarte agrícola en fundos, diseñado para operar en campo tanto en dispositivos móviles (Android / iOS) como en estaciones de escritorio.

- **Versión:** 1.0.4 (Release Listo para Producción en DonWeb Ferozo)
- **Framework & Backend:** Laravel 12
- **Frontend & UI:** Blade + Vanilla CSS moderno con Design Tokens (sin dependencias Node.js en servidor) + JavaScript Vanilla
- **Base de Datos:** SQLite local (desarrollo) / MySQL 8.0 (producción en DonWeb Ferozo)
- **Cobertura de Pruebas:** 84 pruebas automatizadas PHPUnit pasando (418 aserciones)
- **Ubicación de la App:** `apps/web/`

---

## Módulos y Funcionalidades Implementadas

1. **Autenticación y Control de Acceso por Roles (RBAC):**
   - 5 roles oficiales con permisos diferenciados: `Administrador`, `Jefe de fundo`, `Registrador`, `Analista` y `Ventas`.
   - Cuentas activas/inactivas con bloqueo automático y sesión segura.
   - Branding corporativo TALSA Grape Farms (emblemas oficiales en blanco con transparencia pura) con fondo panorámico de viñedos en [login](file:///apps/web/resources/views/auth/login.blade.php).

2. **Aislamiento Multi-Tenant (`FundoScope`):**
   - Filtrado transparente y seguro a nivel de base de datos (`Eloquent Global Scope`).
   - Fundos oficiales del negocio configurados con nombres diferenciados (operativos vs. razones sociales):
     - **AGRITAC** (`AGRICOLA TAMBO COLORADO`)
     - **PROCOM** (`AGRICOLA PROCOM`)
     - **EL NEGRO** (`TALSA GRAPE FARMS`)
   - El rol `Registrador` y `Jefe de fundo` operan sobre sus fundos asignados.
   - `Administrador`, `Analista` y `Ventas` poseen acceso transversal a todos los fundos.

3. **Catálogos Dinámicos de Campo:**
   - Carga reactiva de Lotes y Cuarteles por Fundo (`GET /api/catalogo/lotes`).
   - 34 lotes oficiales precargados (AGRITAC: 18 lotes, PROCOM: 9 lotes, EL NEGRO: 7 lotes).
   - Catálogo de Cuarteles dinámico: opcional en general y obligatorio para Cosecha Nacional, aprendiendo y guardando automáticamente cuarteles nuevos.

4. **Registro y Edición de Venta de Descarte:**
   - Validación estricta por motivo: `Campo`, `Packing` y `Cosecha Nacional`.
   - Regla de Cosecha Nacional: exige obligatoriamente Cuartel y está restringida exclusivamente a dos Tipos de Descarte: *Racimos* y *Granos*.
   - Campo admite *Racimos*, *Racimos con plaga* y *Granos*; Packing admite *Racimos* y *Granos*.
   - Cálculo automático en tiempo real del valor total (`Precio (S/) × Kilogramos`).
   - Campo de Cliente / Razón Social para trazabilidad comercial directa.
   - Auditoría completa de creación (`created_by`), edición (`updated_by`) y marcas de tiempo.

5. **Historial de Ventas y Ciclo de Vida en 2 Pasos:**
   - Filtros en tiempo real por Fundo, Motivo, Estado y Rango de fechas.
   - **Doble Paginación (Opción A):** Botonera compacta superior en cabecera de tabla (`Anterior`, `Pág. X de Y`, botones directos y `Siguiente`) para navegación inmediata en laptops y smartphones sin deslizar hacia abajo, más paginación inferior completa.
   - **Ciclo de eliminación seguro:** Primero *Anular* (descuenta totales sin destruir datos) y solo tras anular se habilita la *Eliminación física* definitiva.
   - Botón *Ver* con modal emergente accesible para inspección detallada sin recargar página, y ficha técnica standalone.

6. **Panel de Control Analítico (Dashboard):**
   - KPIs consolidados en tiempo real.
   - Tabla de segregación operativa (Venta Nacional, Campo y Packing por tipos de descarte con volúmenes, porcentajes y precios promedio).
   - Tabla dinámica e interactiva por Cliente con filtros instantáneos por motivo/tipo, búsqueda reactiva y modo drill-down agrupado.
   - Selector de pestañas interactivas ("Todas", "Por Tipo y Descarte", "Por Cliente", "Últimos Envíos") con persistencia en `localStorage`.

7. **Exportación Nativa a Microsoft Excel (.xlsx):**
   - Descarga directa en formato binario OpenXML `.xlsx` estructurado (no simple CSV).
   - Exportación desde Historial de Ventas (`/ventas/exportar`) con los 21 campos operativos, nombres limpios de fundos, RUCs protegidos como texto, formato contable y fila de TOTAL GENERAL bold.
   - Exportación contextual desde Panel de Control (`/dashboard/exportar`) respetando filtros activos en tiempo real.

8. **Progresive Web App (PWA) & Modo Offline:**
   - [manifest.json](file:///apps/web/public/manifest.json) e iconos en formato WebP optimizados para instalación en Android, iPhone y PC.
   - Service Worker ([sw.js](file:///apps/web/public/sw.js)) con estrategia de caché offline para recursos estáticos y resiliencia en campo.

9. **Módulo de Administración:**
   - Gestión de Fundos: creación de sedes con asignación inmediata de lotes y conmutación de estado.
   - Gestión de Usuarios: creación y edición con asignación de roles y vinculación multi-fundo.

10. **Optimización Responsive Integral y Selects Controlados:**
    - Motor `initCustomSelect` con altura máxima contenida (max-height: 200px), scroll vertical suave ("ir bajando") y buscador en tiempo real para listas con >6 opciones.
    - Tipografía fluida con `clamp()`, espaciado generoso anti-colisión en bordes curvados y aceleración GPU (60 FPS) en menú lateral.

---

## Ejecución Local

```bash
# Acceder a la aplicación
cd apps/web

# Iniciar servidor local
php artisan serve

# Ejecutar la suite completa de 84 pruebas automatizadas
php artisan test
```

Acceder en el navegador a: `http://127.0.0.1:8000`

---

## Empaquetado y Despliegue en DonWeb Ferozo

1. **Empaquetar para Producción:** Ejecuta el archivo `empaquetar_produccion.bat` en la raíz del proyecto para generar `dist/sistema-fundo-ferozo.zip`.
2. **Guía de Despliegue:** Consulta la [Guía Maestra de Despliegue en Ferozo](file:///docs/05-operacion/GUIA_DESPLIEGUE_FEROZO_DONWEB.md) para subirlo e inicializar la base de datos limpia con 0 ventas.

---

## Estructura de Documentación (`docs/`)

- [docs/00-gestion/](file:///docs/00-gestion/): Estado actual ([ESTADO_PROYECTO.md](file:///docs/00-gestion/ESTADO_PROYECTO.md)), [BACKLOG.md](file:///docs/00-gestion/BACKLOG.md), [REGISTRO_CAMBIOS.md](file:///docs/00-gestion/REGISTRO_CAMBIOS.md).
- [docs/01-requisitos/](file:///docs/01-requisitos/): Requisitos aprobados, matrices de requerimientos y trazabilidad.
- [docs/02-arquitectura/](file:///docs/02-arquitectura/): ADRs, contratos API y modelo ER de datos.
- [docs/03-diseno/](file:///docs/03-diseno/): Guía de UI/UX, tokens CSS y wireframes.
- [docs/04-calidad/](file:///docs/04-calidad/): Matriz de pruebas automatizadas y checklist de release.
- [docs/05-operacion/](file:///docs/05-operacion/): Plan de despliegue para VPS DonWeb, Runbook y seguridad.
- [docs/06-mantenimiento/](file:///docs/06-mantenimiento/): Procedimiento de gestión de cambios posteriores.
