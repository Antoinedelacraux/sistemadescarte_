---
name: commits-inteligentes
description: "Gestiona commits Git locales y automáticos por hitos verificables. Usar al finalizar una tarea, funcionalidad, corrección, actualización de documentación, refactorización o configuración del sistema del fundo, sin esperar una orden de commit explícita."
---

# Skill: Commits inteligentes por hitos

## Propósito
Hacer commits locales claros, pequeños, trazables y verificables **cuando sea conveniente** durante tareas autorizadas. Mantener un historial que facilite auditorías, modificaciones futuras y reversión de cambios. Este procedimiento opera solamente mientras un agente realiza una tarea: no es un observador en segundo plano.

## Autorización y límites
- El propietario autoriza commits Git **locales** automáticos de avances completos de las tareas que ya encargó, siempre que se cumplan las verificaciones siguientes.
- No ejecutar `git push`, `git pull` con merge, `git merge`, `git rebase`, `git reset --hard`, `git clean -fd`, `git commit --amend`, `git tag`, `git branch -D` ni `git push --force` sin autorización explícita.
- No modificar la configuración global de Git; si falta `user.name`/`user.email`, solicitar al propietario que los configure o autorice la configuración local antes del primer commit.
- Respetar permisos de ejecución y revisiones del entorno. Nunca eludir confirmaciones exigidas por Antigravity.
- No crear commits dentro de repositorios ajenos ni escribir fuera del workspace.

## Cuándo crear un commit
Realiza un commit local si se cumple **alguno** de estos hitos y el cambio está completo:
1. Se entrega una tarea o historia de usuario con sus criterios de aceptación validados.
2. Se corrige un error concreto y se verifica la solución.
3. Se termina una refactorización coherente manteniendo el comportamiento esperado.
4. Se completan pruebas, documentación, configuración o una mejora técnica significativa.
5. Se alcanza un punto de control consistente antes de una modificación amplia o riesgosa (si no hay trabajo incompleto mezclado).

**No hacer commits** por cada archivo guardado, en mitad de una tarea, por cambios cosméticos aislados sin valor, al existir conflictos, con secretos detectados, ni para aparentar avances.
En fase 0 se permite un commit de configuración inicial revisada aunque no haya pruebas de aplicación: registra qué verificaciones sí se realizaron.

## Procedimiento obligatorio
1. Confirmar que estás en el repositorio y en la rama de trabajo correcta con `git status --short --branch` y `git branch --show-current`. Si no hay Git inicializado, pedir al orquestador o usuario que autorice la inicialización; no crear repositorios anidados.
2. Identificar **exactamente** qué archivos cambiaron por la tarea actual. No incorporar archivos de otros agentes, ramas ni trabajo preexistente del usuario.
3. Inspeccionar el diff (`git diff --stat`, `git diff -- <rutas>`). Si hay cambios ya preparados en el índice (`git diff --cached --name-only`) que no pertenecen a esta tarea, detener el commit y solicitar que se resuelva la mezcla.
4. Comprobar `.gitignore`, la lista de archivos candidatos y el contenido del diff. Nunca incluir secretos, credenciales, `.env`, claves, tokens, datos reales del fundo, información personal, exports, backups, archivos grandes no justificados o artefactos temporales. Si detectas alguno, **detener el commit** y reportarlo, sin imprimir valores sensibles.
5. Ejecutar las verificaciones apropiadas al alcance (lint, typecheck, pruebas unitarias/integración, build o validación documental/configuración). Registrar qué comandos se ejecutaron y si pasaron. Si una verificación exigida falla, no crear el commit de entrega como si estuviera terminado; reparar o informar el bloqueo. No inventar resultados.
6. Seleccionar y preparar **solo los archivos de esta tarea** usando rutas explícitas: `git add -- <ruta1> <ruta2>`. No utilizar `git add .`, `git add -A`, `git add -u` ni `git commit -a` dentro del flujo automático.
7. Examinar de nuevo `git diff --cached --stat`, `git diff --cached --check` y `git diff --cached -- <rutas>`. Verificar que el índice únicamente contiene lo aprobado para este hito. Ante dudas o archivos de otros agentes, deshacer únicamente el staging propio sin alterar su contenido; informar al responsable.
8. Crear **un solo commit por unidad lógica** con mensaje Conventional Commits: `tipo(alcance): descripción breve` o `tipo: descripción breve`. Tipos: `feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `perf`, `build`, `ci`. Usar descripciones precisas y consistentes en español; sin afirmar pruebas inexistentes.
9. Ejecutar `git status --short --branch` y `git log -1 --format='%h %s'`. Informar hash corto, mensaje, archivos incluidos, pruebas y cualquier cambio que haya quedado sin guardar.
10. Actualizar el estado de la tarea y comunicar al orquestador qué hito queda registrado. No repetir commits si no hay cambios.

## Coordinación multiagente
- El agente responsable del trabajo propone o prepara la entrega; el orquestador coordina la integración.
- **Un solo agente designado crea el commit por conjunto de archivos/ramas**; otros agentes no preparan ni commitean simultáneamente el mismo índice Git.
- Para trabajos paralelos, preferir ramas/worktrees individuales. Antes de integrar se revisa el diff, pruebas y conflictos.
- Nunca asumir que un commit de un agente está integrado en la rama principal.

## Mensajes de ejemplo
- `chore: configurar agentes y skills de Antigravity`
- `docs(requisitos): registrar flujo de producción aprobado`
- `feat(lotes): agregar registro y validación de lotes`
- `fix(reportes): corregir cálculo del total exportado`
- `test(produccion): cubrir conciliación de kilogramos`
- `refactor(api): separar lógica de producción del controlador`

## Resultado obligatorio
Después de cada tarea, informar uno de estos estados:
- **Commit creado:** hash, mensaje, rama, pruebas ejecutadas, rutas incluidas.
- **Commit pendiente:** razón concreta (sin cambios, tarea incompleta, pruebas fallidas, secretos, conflicto, permisos o Git sin configurar) y siguiente acción sugerida.
