# Plan de desarrollo incremental — Sistema de Descarte

**Estado:** Plan propuesto, sujeto a aprobación.

## Fase 0 — Revisar preparación existente
- Validar que Antigravity haya leído AGENTS.md, agentes, skills, reglas y estado Git.
- Conservar instrucciones y commits; no duplicar archivos ni sobrescribir decisiones.
- Completar auditoría si solo se envió el prompt inicial y todavía no hay informe.

## Fase 1 — Requisitos y decisiones
- Incorporar el archivo TXT original al repositorio.
- Separar fuente confirmada / propuestas / ambigüedades.
- Crear matriz RF/RNF, roles y permisos, criterios de aceptación.
- Resolver decisiones P1 (offline, permisos, tipo, dinero) o aislarlas claramente.
- Hito: documento de requisitos revisado, sin generar código.

## Fase 2 — Arquitectura y datos
- ADR stack Laravel/PHP/MySQL con verificación del VPS pendiente.
- Modelo ER, relaciones por fundo, índices, restricciones, validaciones, esquema de auditoría.
- Wireframes responsive para registro, listado y gestión de usuarios/fundos.
- Plan de pruebas y despliegue seguro.
- Hito: aprobación del diseño lógico y técnico.

## Fase 3 — Fundación técnica local
- Proyecto Laravel dentro de ubicación acordada, repositorio limpio y .env.example sin secretos.
- Tests y estilo de código, conexión con BD **de desarrollo**, login e interfaz base.
- Implementar permisos y filtro de alcance por fundo, con pruebas automatizadas.
- No entrar en DonWeb aún.
- Hito: usuarios y autorizaciones funcionando localmente.

## Fase 4 — Catálogos
- Fundos y usuarios asignados, lotes/cuarteles, motivos/tipos.
- Validaciones, pruebas y seeder con datos ficticios.
- Hito: datos maestros listos para el registro.

## Fase 5 — Venta de descarte
- Crear/listar/ver registros, cálculos y validaciones; edición/anulación según reglas aprobadas.
- Registro de autor/fechas y auditoría.
- Testear aislamiento entre fundos y precisión del valor.
- Hito: registro de descarte útil en celular.

## Fase 6 — Reportes, Excel, UX y PWA
- Filtros por fundo/fecha/lote/motivo y selección de columnas.
- Excel sin usuario y hora en exportación operativa.
- Sidebar colapsable, tablas responsivas y ajustes de accesibilidad.
- PWA instalable; offline transaccional solo si se aprueba y diseña.
- Hito: flujo completo listo para pruebas de usuario.

## Fase 7 — QA, piloto y despliegue
- Tests de integración, seguridad, carga básica y recuperación.
- Aislamiento de subdominio/BD, DocumentRoot seguro y HTTPS.
- Copias de seguridad probadas y plan de rollback.
- Piloto con registros ficticios; validación de usuarios clave.
- Despliegue solo tras aprobación explícita del responsable de TI.

## Política operativa
- Orquestador define entregas pequeñas.
- Arquitecto revisa dependencias; QA y seguridad revisan antes de merge.
- Commits inteligentes por hito, no commits que mezclen cambios ajenos.
- Ningún agente hace push/despliega/migra producción sin autorización.
- Al final de cada fase: cambios, pruebas realizadas, pendientes y siguiente paso.
