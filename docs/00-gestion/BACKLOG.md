# Backlog del Sistema Fundo

| ID | Módulo / Tarea | Prioridad | Dependencias | Criterios de Aceptación | Estado |
|---|---|---|---|---|---|
| PREP-001 | Validar entorno y capacidades de la instalación Antigravity | Alta | Ninguna | Agentes, skills y reglas reconocidos | Completada |
| ANA-001 | Análisis de requisitos a partir del documento original | Alta | Ninguna | Matriz RF/RNF y criterios documentados | Completada |
| ANA-002 | Resolución de decisiones abiertas (roles, moneda, auditoría) | Alta | ANA-001 | Documentado en PREGUNTAS_ABIERTAS.md | Completada |
| ARQ-001 | Definir Arquitectura, Wireframes y Modelo ER de Datos | Media | ANA-002 | ADRs, modelo ER y diseño UI documentados | Completada |
| ARQ-002 | Diseñar estrategia PWA y Modo Offline | Alta | Ninguna | ADR Offline-First y manifiesto definidos | Completada |
| F3-AUTH | Autenticación y RBAC (4 roles: Admin, General, Individual, Analista) | Alta | ARQ-001 | Login con rate limiting, bloqueo de inactivos, pruebas | Completada |
| F3-SCOPE | Multi-tenancy estricto (`FundoScope`) en base de datos | Alta | F3-AUTH | Aislamiento por fundo a nivel Eloquent Scope con pruebas | Completada |
| F4-CAT | Catálogos dinámicos de Lotes y Cuarteles por Fundo | Alta | F3-SCOPE | API reactiva y filtrado de cuarteles según lote | Completada |
| F4-FORM | Formulario de Registro de Venta con cálculo en tiempo real | Alta | F4-CAT | Cálculo automático precio × kg, validación por motivo | Completada |
| F4-AUDIT | Auditoría de creación y modificación en Ventas de Descarte | Media | F4-FORM | Registro de created_by, updated_by y timestamps | Completada |
| F5-HIST | Historial de Ventas con filtros avanzados y paginación | Alta | F4-FORM | Filtros por fundo, motivo y rango de fechas | Completada |
| F6-REP | Módulo de Reportes analíticos con KPIs consolidados | Alta | F5-HIST | Totales kg, soles y pesajes, distribución por motivo y fundo | Completada |
| F6-EXCEL | Exportador Excel (.csv UTF-8 BOM) con selector de columnas | Alta | F6-REP | Descarga compatible con Excel, columnas dinámicas | Completada |
| F6-PWA | Service Worker y Manifest para PWA instalable | Alta | ARQ-002 | Caché offline estático, instalable en Android/iPhone/PC | Completada |
| F6-ADM | Módulo de Administración de Fundos y Usuarios | Media | F3-AUTH | CRUD de fundos y usuarios con roles y fundos vinculados | Completada |
| F6-RESP | Optimización Responsive Integral para Celulares | Alta | F6-ADM | Tipografía clamp(), scroll táctil en tablas, botones simétricos | Completada |
| F7-FB | Revisión y feedback funcional con el propietario | Alta | F6-RESP | Observaciones y ajustes del propietario registrados | En curso |
| F7-PKG | Preparación del paquete de despliegue para VPS DonWeb | Alta | F7-FB | Zip precompilado con PLAN_DESPLIEGUE.md | Pendiente |

