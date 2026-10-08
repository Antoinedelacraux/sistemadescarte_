# Contratos API

**Estado: PARCIAL.**

## API de Sincronización (Offline-First - Venta de Descarte)

- **Endpoint:** `POST /api/v1/ventas-descarte/sync` (o equivalente a definir).
- **Idempotencia:** El cliente (PWA) incluirá un identificador `uuid` generado localmente por cada registro. Si el backend recibe un `uuid` que ya fue procesado exitosamente, debe devolver `200 OK` (o código que confirme éxito) sin crear un duplicado.
- **Trazabilidad:**
  - `captured_at`: Fecha y hora exactas de captura local del registro (timestamp del dispositivo).
  - El backend asignará su propio `created_at` al momento de la recepción exitosa.
- **Validaciones:** Laravel ejecutará la validación completa del negocio. Si falla una validación (ej. motivo no autorizado o desactualizado), el endpoint debe retornar un error descriptivo para que la PWA lo marque como `error` sin eliminarlo de la cola.
