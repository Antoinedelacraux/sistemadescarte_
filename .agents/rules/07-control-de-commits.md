---
trigger: always_on
description: "Política de commits Git locales por hitos y seguridad del historial en el sistema del fundo."
---

# Control de versiones y commits por hitos

- Al terminar una tarea autorizada y verificable, evaluar **automáticamente** si corresponde crear un commit local; aplicar la skill `.agents/skills/commits-inteligentes/SKILL.md`.
- La autorización del propietario cubre commits Git **locales** y acotados de trabajo solicitado, siempre que se revisen diff, secretos, pruebas y staging; no cubre push, merge, rebase, eliminación, reset ni alteración del historial remoto.
- No realizar commits por cada edición pequeña; preferir unidades funcionales coherentes y completas.
- No mezclar archivos de distintos agentes o tareas; preparar solo rutas explícitas y revisar `git diff --cached` antes de confirmar.
- Nunca afirmar que se creó un commit si Git no devolvió éxito. Comunicar hash y mensaje únicamente tras comprobarlos.
- En fase 0, hacer el primer commit tras revisar que la configuración inicial es correcta y segura; no hace falta esperar los requisitos del sistema.


