---
trigger: always_on
description: "Protección de datos, secretos y permisos para cualquier tarea."
---

# Seguridad no negociable
- Nunca registrar secretos ni credenciales en archivos rastreados por Git o en el chat.
- Nunca reutilizar datasets reales para pruebas sin un proceso de anonimización autorizado.
- Validar autorización del lado servidor y aplicar mínimo privilegio; ocultar botones no reemplaza controles reales.
- No habilitar accesos, red, shell o MCP externos sin justificación y aprobación.
- Clasificar cualquier operación destructiva o irreversible como bloqueada hasta aprobación humana.
- No desplegar a producción automáticamente.
