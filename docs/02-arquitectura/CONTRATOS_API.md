# Contratos de API del Sistema Web del Fundo

**Estado: IMPLEMENTADOS, OPERATIVOS Y VERIFICADOS.**

---

## 1. Endpoints de Catálogos y Datos Maestros

### Carga Dinámica de Lotes y Cuarteles por Fundo
- **Ruta:** `GET /api/catalogo/lotes`
- **Autenticación:** Requerida (`auth` middleware).
- **Parámetros de Consulta (Query):**
  - `fundo_id` *(integer, obligatorio)*: ID del fundo del cual se solicitan los lotes.
- **Respuesta Exitosa (200 OK):**
  ```json
  [
    {
      "id": 1,
      "fundo_id": 1,
      "nombre": "H01",
      "cuarteles": [
        { "id": 101, "lote_id": 1, "nombre": "Cuartel V1" },
        { "id": 102, "lote_id": 1, "nombre": "Cuartel 4" }
      ]
    }
  ]
  ```
- **Control de Acceso:** Si el usuario tiene rol `Individual` o `General`, el backend valida que el `fundo_id` esté asignado a su cuenta; de lo contrario retorna un array vacío o `403 Forbidden`.

---

## 2. Endpoints Transaccionales de Venta de Descarte

### Registro de Venta
- **Ruta:** `POST /ventas`
- **Autenticación:** Requerida (`auth` middleware).
- **Cuerpo de la Petición (Form Data / JSON):**
  - `fundo_id` *(integer, requerido)*
  - `fecha_produccion` *(date YYYY-MM-DD, requerido)*
  - `motivo` *(string, requerido, in: Campo, Packing, Cosecha Nacional)*
  - `lote_id` *(integer, requerido)*
  - `cuartel_manual` *(string, requerido si motivo = Cosecha Nacional, opcional en otros)*
  - `cuartel_id` *(integer nullable)*
  - `tipo_descarte` *(string, requerido)*:
    - Si motivo = `Cosecha Nacional`: *Racimos*, *Granos* (exclusivamente dos opciones).
    - Si motivo = `Packing`: *Racimos*, *Granos*.
    - Si motivo = `Campo`: *Racimos*, *Racimos con plaga*, *Granos*.
  - `precio` *(numeric min: 0.01, requerido)*
  - `kilogramos` *(numeric min: 0.01, requerido)*
  - `cliente` *(string nullable, max: 255)*: Nombre o Razón Social del cliente/comprador.
  - `ruc` *(string nullable, max: 20)*
  - `jabas` *(integer nullable, min: 0)*
  - `peso_jaba` *(numeric nullable, min: 0)*
  - `placa`, `conductor`, `brevete`, `viaje`, `observacion` *(string nullable)*
- **Cálculo Backend:** `valor_venta = round(precio * kilogramos, 2)` (almacenado con 2 decimales exactos).
- **Auditoría:** Se asigna automáticamente `created_by = Auth::id()`, `estado = 'activo'`.

### Actualización de Venta
- **Ruta:** `PUT /ventas/{venta}`
- **Autenticación:** Requerida (`auth` middleware con FundoScope).
- **Auditoría:** Se actualiza automáticamente `updated_by = Auth::id()`.

### Ciclo de Vida y Eliminación en 2 Pasos
- **Anular Registro:** `POST /ventas/{venta}/anular`
  - Cambia `estado = 'anulado'`, registra `anulado_by = Auth::id()` y `anulado_at = now()`. Descuenta el pesaje de los totales del dashboard y reportes sin perder la trazabilidad física.
- **Reactivar Registro:** `POST /ventas/{venta}/reactivar`
  - Restaura `estado = 'activo'` y limpia la anulación.
- **Eliminación Definitiva:** `DELETE /ventas/{venta}`
  - Bloqueada por defecto: solo se permite si el registro ya se encuentra previamente anulado.

---

## 3. Endpoints de Exportación Nativa a Excel (.xlsx)

### Exportación desde Historial de Ventas
- **Ruta:** `GET /ventas/exportar`
- **Autenticación:** Requerida (`auth`).
- **Parámetros (Filtros activos):** `fundo_id`, `motivo`, `tipo_descarte`, `estado`, `fecha_desde`, `fecha_hasta`, `buscar`.
- **Formato de Respuesta:** Archivo binario nativo Microsoft Excel OpenXML (`.xlsx`) con Content-Type `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, 21 columnas con anchos calibrados, tipos contables y fila de totales general destacada.

### Exportación Contextual desde Panel de Control (Dashboard)
- **Ruta:** `GET /dashboard/exportar`
- **Autenticación:** Requerida (`auth`).
- **Parámetros:** `seccion` (`consolidado` | `segregacion` | `clientes` | `recientes`), `motivo_cliente`, `buscar_cliente`.
- **Formato de Respuesta:** Archivo binario `.xlsx` respetando los filtros instantáneos aplicados por el usuario.
