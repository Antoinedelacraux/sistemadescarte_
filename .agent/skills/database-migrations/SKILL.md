---
name: database-migrations
description: "Gestiona cambios de esquema de base de datos seguros, versionados, reversibles y compatibles con versiones anteriores sin pérdida de información real."
---

# Skill: Database Migrations (Migraciones de Base de Datos Seguras)

## Propósito
Garantizar que toda evolución del esquema de la base de datos se realice mediante migraciones controladas, trazables, reversibles y estrictamente compatibles con la información histórica del cliente, impidiendo la pérdida accidental de datos.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Cuando se requiera añadir, modificar o descontinuar tablas, columnas, índices o restricciones foráneas.
- Antes de proponer o aplicar cualquier migración estructural en desarrollo, staging o producción.
- Para auditar el impacto de cambios de base de datos sobre consultas, modelos Eloquent o contratos de API existentes.

---

## Reglas y Advertencias Críticas
1. **Prohibición de modificaciones manuales:** Jamás ejecutar sentencias `ALTER TABLE` o cambios estructurales directos en bases de datos compartidas o productivas fuera del pipeline de migraciones.
2. **Prohibición de comandos destructivos en producción:** Queda terminantemente vetado ejecutar `migrate:fresh`, `migrate:reset`, `migrate:rollback --step=all` o `db:wipe` en entornos de producción o staging con datos persistentes.
3. **No asumir reversibilidad ingenua:** Nunca asumir que el método `down()` restaura columnas eliminadas con sus datos intactos; una columna borrada en base de datos destruye la información almacenada en ella.
4. **Alerta obligatoria de riesgo:** Si una migración requiere modificar tipos de columna, reducir tamaños o alterar restricciones de nulabilidad sobre tablas con datos existentes, el agente debe declarar la operación como de alto riesgo y requerir aprobación humana.

---

## Procedimiento de Ejecución

### Paso 1: Análisis de Impacto y Dependencias
- Identificar todas las tablas y columnas involucradas.
- Buscar referencias en modelos, controladores, repositorios, vistas y pruebas (`grep_search`).
- Comprobar si la tabla contiene datos históricos en producción y cuál es el volumen aproximado.

### Paso 2: Diseño de Migración Compatible (Expand-and-Contract)
Si se necesita modificar o reemplazar una columna utilizada:
1. **Fase Expand (Ampliación):** Añadir la nueva columna como `nullable` o con valor por defecto seguro, manteniendo intacta la columna anterior.
2. **Fase Sync (Sincronización):** Implementar la lógica de negocio para escribir en ambas columnas o migrar progresivamente los datos mediante un script de actualización idempotente.
3. **Fase Contract (Contracción):** Solo tras verificar en producción que el 100% de los accesos utilizan la nueva estructura y tras un periodo de observación documentado, programar la eliminación de la columna antigua en un release posterior independiente.

### Paso 3: Tipado, Índices y Restricciones
- Utilizar tipos de datos precisos para el dominio agrícola: `decimal('precio', 10, 2)`, `decimal('kilogramos', 10, 2)`, `date('fecha_produccion')`.
- Definir nombres explícitos y descriptivos para restricciones foráneas e índices compuestos.
- Establecer políticas de integridad referencial acordadas (`onDelete('restrict')` preferido para tablas transaccionales de venta y auditoría para impedir borrados accidentales en cascada).

### Paso 4: Implementación de Métodos `up()` y `down()`
- Escribir `up()` con validaciones de existencia previa si aplica (`hasTable`, `hasColumn`).
- Escribir `down()` simétrico y reversible para entornos de desarrollo.
- Si una operación en `up()` es irreversible por naturaleza (ej. compresión de históricos), documentar explícitamente en el docstring de la migración que la marcha atrás requiere restauración desde respaldo.

### Paso 5: Ensayo y Verificación en Aislamiento
- Probar la migración en la base de datos de desarrollo:
  ```bash
  php artisan migrate --pretend # Inspección previa de sentencias SQL
  php artisan migrate
  ```
- Verificar que las pruebas automatizadas del sistema sigan ejecutándose al 100% sin roturas.
- Comprobar que los registros preexistentes en la base continúan siendo legibles y consultables a través de la aplicación.

### Paso 6: Verificación de Respaldo Pre-Producción
- Antes de autorizar la aplicación de la migración en producción, verificar que exista una copia de seguridad recuperable reciente de la base de datos.

---

## Criterios de Aceptación
- La migración está versionada en `database/migrations/` con timestamp secuencial.
- No utiliza sentencias SQL crudas no portables a menos que sea estrictamente indispensable y justificado.
- Las pruebas automatizadas validan tanto la aplicación de la migración como el acceso a los datos preexistentes.
- Se documentó el impacto en `docs/00-gestion/REGISTRO_CAMBIOS.md`.
