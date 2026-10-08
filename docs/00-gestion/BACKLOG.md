# Backlog por aprobar

| ID | Necesidad / tarea | Fuente | Prioridad | Dependencias | Criterios de aceptación | Estado |
|---|---|---|---|---|---|---|
| PREP-001 | Validar archivos de configuración y capacidades de la instalación Antigravity | Inicio del proyecto | Alta | Ninguna | Agentes, skills y reglas enumerados y reconocidos donde aplique | Completada |
| ANA-001 | Procesar TXT completo de requisitos | Propietario | Alta | Recibir TXT | Matriz de requisitos, ambigüedades y criterios documentados | Bloqueado |
| ARQ-001 | Comparar alternativas de stack y despliegue | Pendiente | Media | ANA-001 | ADR con costes/riesgos y aprobación | Bloqueado |
| ARQ-002 | Diseñar motor de sincronización y PWA | Arquitecto | Alta | Ninguna | ADR creado | Completada |
| FE-001 | Implementar PWA e IndexedDB para descarte | Frontend | Alta | Requisitos form | Venta registrada offline, sincronizada online | Pendiente |
| BE-001 | API Sincronización idempotente descarte | Backend | Alta | Requisitos form | API recibe UUID, valida y guarda sin duplicar | Pendiente |

No inventar historias funcionales hasta que lleguen los requisitos completos.
