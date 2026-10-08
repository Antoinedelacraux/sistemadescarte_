# INICIO — Proyecto web del fundo en Google Antigravity

**Fase actual:** preparar la plataforma. Los requisitos completos llegarán después.

## 1. Abrir el proyecto en Antigravity
1. Crear una carpeta vacía `sistema-fundo` en tu computadora (por ejemplo `Documentos\sistema-fundo`).
2. Extraer TODO el contenido de este paquete directamente dentro de esa carpeta. Debes ver `AGENTS.md` y `.agents/` en la raíz, NO una carpeta anidada `antigravity-fundo-base/`.
3. Abrir esa carpeta con **File > Open Folder** en Antigravity IDE, o agregarla como carpeta en Antigravity 2.0.
4. Revisar que aparezcan `.agents/agents/`, `.agents/skills/` y `.agents/rules/` en el explorador; puede ser necesario mostrar carpetas ocultas.
5. Para Windows elegir **Request Review** o **Proceed in Sandbox** para comandos y NO habilitar **Always Proceed** ni acceso a archivos externos al workspace por defecto.
6. Inicializar Git desde el control de versiones de Antigravity o `git init` si Git está disponible. Revisar antes de crear el primer commit. No subir datos del fundo.
7. Abrir un chat nuevo de agente y pegar el contenido de `PROMPT-INICIAL.md`.

## 2. Qué capacidades están disponibles
- **Reglas:** `AGENTS.md` siempre activo y `.agents/rules/*.md` con YAML `trigger`.
- **Skills:** `.agents/skills/<nombre>/SKILL.md` seleccionadas según tarea.
- **Agentes personalizados:** `.agents/agents/<nombre>/agent.md`. Invocación nativa documentada en Antigravity 2.0 y CLI. El IDE tradicional puede usar conversaciones paralelas, pero NO se debe asumir soporte de `invoke_subagent` sin comprobarlo.
- En Antigravity 2.0/CLI, buscar el panel `/agents` y/o `invoke_subagent`. Si no existe, usar los archivos de rol como instrucciones para sesiones manuales sucesivas.
- No crear `workflows` antiguos de Antigravity IDE: están anunciados para dejar de funcionar el 19/10/2026; usar skills.

## 3. Primera validación
Pide al agente auditar rutas, YAML, lista de 9 roles, 12 skills y 6 reglas; comparar con documentación oficial. No instalar dependencias ni construir la web. El script `scripts/validar_estructura.ps1` también comprueba que los archivos básicos existen (no asegura compatibilidad con la app).

## 4. Lo que falta del propietario
- TXT de requisitos por módulo, roles y permisos.
- Datos estimados: usuarios simultáneos, frecuencia de uso, conectividad, equipos, almacenamiento, presupuesto.
- Condiciones de operación: responsables, disponibilidad, backup y recuperación.
- Integraciones, reglas de cálculo y estructuras de datos reales **sin subir información sensible**.

## 5. Puntos de decisión obligatorios
Requisitos aprobados -> alternativa de arquitectura -> stack y hosting -> prototipos -> primer módulo pequeño -> QA -> piloto -> producción (aprobación explícita).

## 6. Mantenimiento
Toda solicitud posterior debe seguir la skill `gestion-cambios-documentacion`. Una app con buen diseño requiere seguimiento: no existe garantía de cero caídas.
