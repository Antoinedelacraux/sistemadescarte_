---
name: data-integrity
description: "Verifica integridad referencial, consistencia de datos, persistencia histórica, restricciones y compatibilidad con registros existentes tras cualquier cambio."
---

# Skill: Data Integrity (Integridad, Relaciones y Persistencia de Datos)

## Propósito
Asegurar que la base de datos mantenga total coherencia relacional, precisión numérica y disponibilidad de los registros históricos a lo largo de cualquier refactorización, actualización o migración, impidiendo la corrupción o desaparición de datos reales del cliente.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Al diseñar o modificar relaciones entre modelos (Fundos, Lotes, Cuarteles, Usuarios, Ventas de Descarte).
- Tras ejecutar migraciones para verificar que los registros antiguos no sufrieron pérdidas, truncamientos ni desvinculaciones.
- Al implementar o auditar cálculos agrícolas críticos (precios, kilogramos, valor de venta, porcentajes de merma).
- Para auditar la presencia de registros huérfanos o restricciones de clave foránea rotas.

---

## Verificaciones Obligatorias

### 1. Integridad Referencial y Restricciones
- **Relaciones Padre-Hijo:** Toda venta debe estar asociada a un fundo válido y un lote existente.
- **Políticas de Eliminación:**
  - Prohibido configurar `onDelete('cascade')` en tablas de negocio principales (como `ventas_descarte` o `lotes` con ventas asociadas).
  - La eliminación de un fundo o lote con historial debe estar bloqueada por base de datos o por controlador (`restrict`), exigiendo anulación lógica en lugar de borrado físico.
- **Detección de Huérfanos:** Comprobar periódicamente consultas que verifiquen que no existan ventas sin lote, ni lotes sin fundo:
  ```sql
  SELECT COUNT(*) FROM ventas_descarte vd LEFT JOIN fundos f ON vd.fundo_id = f.id WHERE f.id IS NULL;
  ```

### 2. Precisión Numérica y Cálculos Financieros
- **Decimales Exactos:** Los campos de pesaje (`kilogramos`), precio unitario (`precio`) y valor total (`valor_total`) deben almacenarse en tipos de coma fija (`DECIMAL(10, 2)` o `DECIMAL(12, 4)` si aplican tasas).
- **Prohibido el uso de tipos de coma flotante (`FLOAT`/`DOUBLE`):** Evitar acumulaciones de error de redondeo en balances y reportes agregados.
- **Fórmula Invariable:** `valor_total = round(precio * kilogramos, 2)`. Comprobar que ningún script o formulario altere esta relación sin recalcular o conciliar el total.

### 3. Persistencia Histórica Post-Actualización
- Toda actualización de código debe incluir una prueba automatizada que:
  1. Inserte un conjunto de registros representativos con la estructura previa.
  2. Ejecute la nueva versión del código / migración.
  3. Compruebe que cada uno de los registros insertados previamente continúa existiendo intacto, con sus mismos IDs, valores de pesaje, fechas y relaciones.
  4. Valide que los reportes, filtros y pantallas siguen mostrando estos datos históricos correctamente.

### 4. Trazabilidad y Auditoría de Estados
- **Ciclo de vida en dos pasos:**
  - Los registros activos tienen `estado = 'activo'`.
  - Para anular un registro, se actualiza `estado = 'anulado'`, registrando `anulado_at = now()` y `anulado_by = auth()->id()`.
  - Solo los registros previamente anulados pueden someterse a eliminación definitiva si existe autorización expresa.
- **Campos de Auditoría:** Toda tabla transaccional debe mantener `created_at`, `updated_at`, y cuando corresponda `user_id` (autor del pesaje o registro).

---

## Procedimiento de Auditoría de Integridad
1. Ejecutar la suite de pruebas unitarias y de características orientadas a persistencia:
   ```bash
   php artisan test --filter=DataIntegrity
   ```
2. Inspeccionar la base de datos en busca de valores nulos inesperados en columnas requeridas tras una migración.
3. Verificar que los reportes agregados concilien con la suma de las filas individuales exportadas a Excel (.xlsx).

---

## Criterios de Aceptación
- Cero registros huérfanos en tablas operativas.
- 100% de conciliación entre subtotales individuales y totales agregados de ventas.
- Pruebas automatizadas de persistencia histórica pasando exitosamente antes de cada entrega.
