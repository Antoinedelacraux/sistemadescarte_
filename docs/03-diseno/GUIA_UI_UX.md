# Guía UI/UX (pendiente de requisitos completos)

Por cada pantalla: rol, objetivo, navegación, campos, validación, filtros, acciones, estados vacío/carga/error/éxito, mensajes, responsive, accesibilidad y criterios de aceptación.

## Requisitos de UI para Offline-First (Venta de Descarte)
- **Indicador de Red:** Debe haber un indicador global siempre visible que muestre el estado de conexión ("Online" o "Modo Offline").
- **Estados Visuales del Registro:**
  - `Pendiente`: Icono o color (ej. gris) indicando que está guardado localmente en cola.
  - `Sincronizando`: Animación o indicador de proceso de envío activo.
  - `Sincronizado`: Aprobación visual (ej. verde), luego el registro pasa al historial o desaparece de pendientes.
  - `Error`: Rechazado o conflicto (ej. rojo). Mensaje de error claro para el usuario.
- **Acciones y Controles:**
  - Botón explícito de "Sincronizar Ahora" o "Reintentar" disponible para la cola.
  - Alerta bloqueante o advertencia severa si el usuario intenta hacer Logout, cerrar o limpiar caché habiendo registros pendientes o en error en la cola. No permitir limpieza invisible.
- **Feedback de Safari/iOS:** Informar al usuario que mantenga la aplicación abierta (foreground) durante la sincronización si hay una gran cantidad de registros atrasados.
