# Requisitos aprobados

**Estado: PARCIAL (Módulo Venta de Descarte).** 

## Información que se incorporará
- Objetivo y alcance.
- Actores, roles y permisos.
- Módulos, campos y flujos por rol.
- Reglas de negocio y cálculos, con unidades y precisión.
- Reportes, importaciones, exportaciones e integraciones.
- Restricciones no funcionales: conectividad, equipos, seguridad, rendimiento, presupuesto, mantenimiento.
- Casos de aceptación.

## Módulo: Venta de Descarte (Modo Offline-First)
- **Objetivo**: Permitir a los trabajadores registrar ventas de descarte sin conexión a internet y sincronizarlas al recuperar conectividad.
- **Plataforma**: PWA compatible con Android y iPhone.
- **Sesión Offline**: Permitir acceso a usuarios previamente autenticados sin internet.
- **Almacenamiento Local**: Uso de IndexedDB para registros pendientes y catálogos autorizados (fundos, lotes, cuarteles, motivos, tipos de descarte).
- **Estados Visibles**: Pendiente, sincronizando, sincronizado, error.
- **Sincronización**: Automática (al recuperar conexión, al abrir la app) y Manual (botón de reintento). Background Sync no es exclusivo debido a iOS/Safari.
- **Trazabilidad**: Fecha/hora de captura local vs. recepción en el servidor. UUID únicos por registro.
- **Resolución de Conflictos**: Los registros rechazados en el backend no se eliminan localmente; se quedan en cola local con estado error para revisión. No se permite el borrado por cierre de sesión o caché sin estrategia segura.

## Historial de aprobaciones
- 2026-10-08: Aprobada arquitectura y requisito Offline-First para Venta de Descarte.
