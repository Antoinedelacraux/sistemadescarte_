# Contratos de API del Sistema Web del Fundo

**Estado: IMPLEMENTADOS Y OPERATIVOS.**

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
      "nombre": "Lote 1",
      "cuarteles": [
        { "id": 101, "lote_id": 1, "nombre": "Cuartel A" },
        { "id": 102, "lote_id": 1, "nombre": "Cuartel B" }
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
  - `cuartel` *(string, requerido solo si motivo = Cosecha Nacional)*
  - `tipo_descarte` *(string, requerido solo si motivo = Cosecha Nacional, in: Racimos, Racimos con plaga, Granos)*
  - `precio` *(numeric min: 0.01, requerido)*
  - `kilogramos` *(numeric min: 0.01, requerido)*
  - `jabas` *(integer nullable)*
  - `peso_jaba` *(numeric nullable)*
  - `placa`, `conductor`, `ruc`, `brevete`, `viaje`, `observacion` *(string nullable)*
- **Cálculo Backend:** `valor_venta = precio * kilogramos` (almacenado con 2 decimales exactos).
- **Auditoría:** Se asigna automáticamente `created_by = Auth::id()`.

### Actualización de Venta
- **Ruta:** `PUT /ventas/{venta}`
- **Autenticación:** Requerida (`auth` middleware con FundoScope).
- **Auditoría:** Se actualiza automáticamente `updated_by = Auth::id()`.

---

## 3. Endpoints de Reportes y Exportación

### Exportación a Excel (.csv compatible)
- **Ruta:** `GET /reportes/exportar`
- **Autenticación:** Requerida (Admin, Analista, General).
- **Parámetros de Consulta (Query):**
  - `fundo_id` *(integer nullable)*: Filtrar por sede específica o todos.
  - `motivo` *(string nullable)*: Filtrar por motivo.
  - `fecha_desde` *(date nullable)*
  - `fecha_hasta` *(date nullable)*
  - `columnas[]` *(array de strings)*: Lista de claves de columna seleccionadas (ej. `fundo`, `fecha_produccion`, `lote`, `cuartel`, `motivo`, `precio`, `kilogramos`, `valor_venta`).
- **Respuesta (200 OK):** Archivo descargable con encabezados `Content-Type: text/csv; charset=UTF-8`, `Content-Disposition: attachment; filename="reporte_ventas_YYYYMMDD_HHMM.csv"` con UTF-8 BOM (`\xEF\xBB\xBF`) y delimitador `;` para compatibilidad nativa con Microsoft Excel.

---

## 4. Endpoints de Administración (Solo Administrador)

- `POST /administracion/fundos`: Crea nuevo fundo (`name`, `code`).
- `POST /administracion/usuarios`: Crea usuario (`name`, `email`, `password`, `role_id`, `fundos[]`).
- `POST /administracion/usuarios/{user}/toggle`: Conmuta estado activo/inactivo del usuario.

---

## 5. Especificación de Sincronización Offline (PWA)

- **Idempotencia:** El cliente PWA asigna un identificador `UUID v4` a cada pesaje registrado en modo offline.
- **Trazabilidad:** Se registra la fecha y hora de captura local en el dispositivo del trabajador.
- **Resolución de Conflictos:** Si el backend recibe un `UUID` ya registrado, confirma el éxito sin generar registros duplicados.

