# Prompt para pegar en el chat de Antigravity

Actúa como el agente ORQUESTADOR del proyecto Sistema Web del Fundo.

Estamos en FASE 0: preparación del entorno. Todavía estoy redactando los requisitos funcionales que entregaré después. NO PROGRAMAR LA APLICACIÓN, NO instalar dependencias y NO elegir todavía un stack definitivo.

Primero inspecciona el contenido REAL de la carpeta abierta: `AGENTS.md`, `.agents/agents/`, `.agents/skills/`, `.agents/rules/`, `START-HERE.md` y `docs/`.

Tareas autorizadas ahora:
1. Verificar la estructura y sintaxis YAML; confirmar que existen 9 agentes, 12 skills y 6 reglas.
2. Informar qué soporte exacto de agentes personalizados, subagentes y skills tiene ESTA versión de Antigravity (IDE, 2.0 o CLI). No afirmar que invocaste agentes que la herramienta no puede invocar.
3. Describir un flujo de trabajo para la llegada del TXT de requisitos: análisis -> aprobación -> arquitectura -> UI/UX -> desarrollo por módulos -> QA/seguridad -> despliegue -> mantenimiento.
4. Identificar documentación faltante sin inventar requisitos de negocio.
5. Proponer un primer backlog DE PREPARACIÓN con preguntas sobre usuarios, módulos, roles, datos, conectividad, backups y presupuesto.
6. Si la estructura requiere una corrección de compatibilidad, prepara un diff pequeño y explícame el motivo antes de editar.
7. Mantener registro del estado del proyecto.

POLÍTICAS:
- No ejecutar acciones destructivas ni tocar archivos fuera de la carpeta del proyecto.
- No crear aplicaciones demo ni funcionalidades supuestas del fundo.
- No conectar servicios externos, instalar paquetes o crear repositorios remotos sin mi aprobación.
- Presentar resultados en español, diferenciando comprobado, propuesto y pendiente.
- Evitar sobreingeniería y priorizar mantenibilidad, seguridad y futuras actualizaciones.

Entrega al finalizar una tabla de validación con: elemento, cantidad esperada, cantidad encontrada, estado y observaciones; luego el plan de preparación.
