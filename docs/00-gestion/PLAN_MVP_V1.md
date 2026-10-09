# Plan de desarrollo incremental — Sistema de Descarte

**Estado:** Fases 0 a 6 COMPLETADAS. Fase 7 (Revisión y Empaquetado VPS) en curso.

## Fase 0 — Revisar preparación existente `[COMPLETADA]`
- Validado que Antigravity leyó AGENTS.md, agentes, skills, reglas y estado Git.
- Conservadas instrucciones y commits; no se duplicaron archivos ni se sobrescribieron decisiones.
- Hito: Estructura multiagente validada y lista.

## Fase 1 — Requisitos y decisiones `[COMPLETADA]`
- Incorporado el archivo de requisitos original al repositorio (`docs/01-requisitos/`).
- Matriz RF/RNF, roles y permisos, y criterios de aceptación formalizados.
- Resueltas decisiones operativas en `PREGUNTAS_ABIERTAS.md`.
- Hito: Requisitos aprobados formalmente.

## Fase 2 — Arquitectura y datos `[COMPLETADA]`
- ADRs aprobados: Arquitectura web Laravel 12 / PHP 8.4 y estrategia PWA/Offline-First.
- Modelo ER de datos formalizado en `MODELO_DATOS_ER.md`.
- Wireframes de alta fidelidad y tokens CSS documentados en `docs/03-diseno/`.
- Hito: Diseño lógico, técnico y arquitectónico aprobado.

## Fase 3 — Fundación técnica local `[COMPLETADA]`
- Proyecto Laravel 12 instalado en `apps/web/` con PHP 8.4.
- Migraciones y modelos para `roles`, `fundos`, `users`, `fundo_user` y `ventas_descarte`.
- Implementado Login, RBAC (4 roles) y aislamiento estricto `FundoScope` multi-tenant.
- Hito: 15 pruebas PHPUnit exitosas (55 aserciones).

## Fase 4 — Catálogos y Registro de Descarte `[COMPLETADA]`
- Catálogos dinámicos de Lotes y Cuarteles por fundo vía API (`/api/catalogo/lotes`).
- Formulario de Venta de Descarte con cálculo en tiempo real (`precio * kg`).
- Validaciones estrictas por motivo: Campo, Packing y Cosecha Nacional (Cuartel + Tipo de descarte).
- Auditoría de usuario creador y modificador (`created_by`, `updated_by`).
- Hito: 23 pruebas PHPUnit pasando (82 aserciones).

## Fase 5 — Historial y Auditoría de Ventas `[COMPLETADA]`
- Listado de ventas con paginación y filtros por Fundo, Motivo y Rango de Fechas.
- Formulario de edición con validación de FundoScope y auditoría de cambios.
- Hito: Gestión completa del ciclo de vida de la venta de descarte.

## Fase 6 — Reportes, Excel, PWA, Admin y Responsive `[COMPLETADA]`
- Módulo de Reportes analíticos con KPIs consolidados y tablas de distribución.
- Exportador a Excel (.csv compatible con UTF-8 BOM) con selector dinámico de columnas.
- PWA completa: `manifest.json`, iconos adaptativos y Service Worker (`sw.js`).
- Módulo de Administración de Fundos y Usuarios con asignación multi-fundo.
- Optimización responsive integral para smartphones (< 640px y < 440px) con tipografía `clamp()`, tablas en `table-wrapper` con scroll táctil y botones simétricos.
- Hito: 29 pruebas PHPUnit exitosas (111 aserciones).

## Fase 7 — QA, Feedback del Propietario y Empaquetado VPS `[EN CURSO]`
- Revisión funcional activa por parte del propietario del proyecto.
- Incorporación de feedback y ajustes solicitados.
- Preparación del paquete de despliegue precompilado (.zip) junto con `PLAN_DESPLIEGUE.md`.
- Hito final: Entrega del paquete al Administrador del VPS DonWeb para despliegue productivo.

