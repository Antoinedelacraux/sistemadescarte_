# Flujo iterativo y aprobaciones

Fase 0 - Preparación: validar carpeta, permisos y herramientas. No desarrollar hasta recibir y aprobar requisitos.
Fase 1 - Análisis: procesar TXT, aclarar reglas y entregar historias y criterios comprobables. **Gate 1: aprobación de alcance.**
Fase 2 - Arquitectura: comparar alternativas de stack, hosting, módulos, datos y operación; registrar ADR. **Gate 2: aprobación de arquitectura/costo.**
Fase 3 - UX: flujos/wireframes y validaciones por rol. **Gate 3: aprobación de prototipos.**
Fase 4 - Desarrollo: implementar vertical slices pequeños (datos+API+UI+pruebas), revisados en cada iteración.
Fase 5 - QA: pruebas funcionales, integración, regresión, seguridad y aceptación con usuarios. **Gate 4: QA suficiente.**
Fase 6 - Piloto: staging con datos ficticios o autorizados, revisión operativa, backups y restauración.
Fase 7 - Producción: solo con aprobación de lanzamiento y plan de contingencia.
Fase 8 - Mantenimiento: cambios trazados, regresión, despliegue controlado y documentación.

## Convención Git inicial
- `main`: rama estable. No trabajar directamente aquí en cambios de implementación.
- `feature/ID-descripcion`, `fix/ID-descripcion`, `docs/ID-descripcion`: cambios aislados de corta duración.
- Un agente modifica archivos de su rama/worktree y no pisa archivos de otro.
- Integrar después de revisión de diferencias, pruebas y resolución explícita de conflictos.
- No introducir credenciales, archivos reales, backups o dumps en commits.
- Si se usa repositorio remoto, habilitar políticas de revisión según plataforma; nunca dar por hecho que ya se configuraron.

## DoD
Ver `docs/04-calidad/DEFINITION_OF_DONE.md`.
