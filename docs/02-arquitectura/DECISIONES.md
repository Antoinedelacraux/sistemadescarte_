# Índice de Decisiones de Arquitectura (ADR)

| ADR / Decisión | Tema | Estado | Fecha | Aprobó | Resumen |
|---|---|---|---|---|---|
| [ADR-001](file:///docs/02-arquitectura/ADR-001-ARQUITECTURA_CANDIDATA.md) | Stack Web Principal | Aprobada | 2026-10-08 | Propietario | Laravel 12 (PHP 8.4) en `apps/web/`, Blade + Vanilla CSS moderno con Design Tokens (sin frameworks pesados), SQLite dev / MySQL prod. |
| [ADR-002](file:///docs/02-arquitectura/ADR-001-OFFLINE_FIRST.md) | Estrategia PWA y Modo Offline | Aprobada | 2026-10-08 | Propietario | PWA instalable con Service Worker para Android/iPhone/PC, caché de activos y resiliencia en conectividad intermitente de campo. |
| ADR-003 | Multi-tenancy con Eloquent Global Scope | Aprobada | 2026-10-08 | Propietario | Implementación de `FundoScope` para filtrado transparente por fundo en consultas de base de datos sin requerir múltiples esquemas. |
| ADR-004 | Exportación Excel compatible con UTF-8 BOM | Aprobada | 2026-10-08 | Propietario | Generación nativa de archivos .csv con byte order mark (BOM) y delimitador `;` para compatibilidad inmediata con MS Excel en español sin dependencias externas pesadas. |
| ADR-005 | Infraestructura de Despliegue en VPS DonWeb | Aprobada | 2026-10-08 | Propietario | Servidor VPS Ubuntu 24.04 LTS con Nginx, PHP 8.4-FPM, MySQL 8.0 y SSL Let's Encrypt administrado por el equipo de infraestructura. |
| ADR-006 | Sistema de Diseño Responsive Fluido (`clamp()`) | Aprobada | 2026-10-08 | Propietario | Tipografía adaptativa, scroll horizontal controlado en tablas (`.table-wrapper`) y orden simétrico en cuadrículas táctiles para smartphones de campo. |

