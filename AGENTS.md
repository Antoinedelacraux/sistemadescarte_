# Reglas permanentes del sistema web del fundo

## Estado y propósito
Proyecto en fase de PREPARACIÓN. El documento definitivo de requisitos AÚN NO se ha recibido. No crear funcionalidades, elegir stack de manera definitiva ni instalar dependencias hasta que el propietario apruebe una propuesta documentada.

## Orden de autoridad
1. Instrucciones explícitas y actuales del propietario, sin contradecir medidas de seguridad.
2. Requisitos aprobados en `docs/01-requisitos/REQUISITOS_APROBADOS.md` (actualmente pendiente).
3. Decisiones de arquitectura aceptadas en `docs/02-arquitectura/DECISIONES.md`.
4. Estas reglas y las reglas especializadas de `.agents/rules/`.
5. Sugerencias de los agentes, siempre marcadas como propuestas.

## Método obligatorio
- Responder y documentar en español claro; código, identificadores y nombres técnicos consistentes con el stack que posteriormente se apruebe.
- Para cualquier trabajo: entender objetivo, identificar riesgos y archivos afectados, preparar plan breve, implementar cambio limitado, ejecutar verificaciones y documentar resultados.
- Nunca afirmar que una prueba pasó si no fue ejecutada. Informar comando, resultado y limitaciones.
- No inventar requisitos, permisos, actores, fórmulas, tasas, reglas de redondeo ni flujos de aprobación.
- Diseñar por módulos con responsabilidades definidas; evitar acoplamiento, duplicación, sobreingeniería, dependencias innecesarias y cambios masivos.
- Escribir código mantenible, legible, comprobable, con validación de errores, tipado/contratos apropiados y convenciones documentadas.
- Toda característica debe tener criterios de aceptación y pruebas adecuadas. Usar datos ficticios para ejemplos y pruebas.
- Mantener actualizados `docs/00-gestion/ESTADO_PROYECTO.md`, `BACKLOG.md`, `REGISTRO_CAMBIOS.md` y las decisiones correspondientes.
- Separar desarrollo, pruebas y producción. No desplegar ni conectar servicios reales sin aprobación explícita.

## Seguridad y datos
- Nunca introducir contraseñas, tokens, llaves ni información sensible en el repositorio, prompts, documentación o logs.
- No subir archivos reales del fundo, información personal, de clientes, productores o comercial a servicios externos sin autorización expresa y controles adecuados.
- No ejecutar borrados, reseteos, migraciones destructivas, acciones de producción ni comandos de alto impacto sin revisión humana explícita.
- No activar accesos amplios al equipo; trabajar únicamente en la carpeta del proyecto.
- Permisos por mínimo privilegio, auditoría cuando corresponda, copias de seguridad y restauración comprobables.
- Los cálculos de producción y balances requieren fórmulas y reglas de redondeo aprobadas, con pruebas de conciliación; no asumirlas.

## Coordinación multiagente
- Orquestador asigna tareas según especialidad y dependencias. Subagentes devuelven hallazgos y cambios acotados.
- No permitir cambios concurrentes en el mismo archivo. Trabajar por ramas o worktrees separados cuando la plataforma lo permita; revisar conflictos e integración manualmente.
- Si este Antigravity no tiene `invoke_subagent` ni soporte de agentes personalizados, coordinar roles secuencialmente mediante conversaciones/skills; NO simular ni afirmar invocaciones inexistentes.
- Ningún agente aprueba su propio trabajo de manera definitiva. La integración depende de revisión y, cuando aplique, QA y seguridad.

## Antes de empezar la implementación
Leer `START-HERE.md`, `docs/00-gestion/ESTADO_PROYECTO.md` y el material aportado por el propietario. Si no existe el TXT de requisitos, limitarse a auditar la estructura, proponer preguntas y esperar la especificación sin desarrollar la aplicación.
