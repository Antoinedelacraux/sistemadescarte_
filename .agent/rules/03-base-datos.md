---
trigger: model_decision
description: "Aplicar en diseño de esquemas, migraciones versionadas, integridad referencial, seeds y consultas a base de datos."
---

# Gestión de bases de datos, migraciones y datos iniciales

## 1. Migraciones versionadas y no destructivas
- **Control de cambios estricto:** Toda modificación en el esquema de base de datos (tablas, columnas, índices, llaves foráneas) debe implementarse mediante un archivo de migración versionado en el código fuente.
- **Prohibida la edición manual en producción:** No ejecutar sentencias `ALTER TABLE` o modificaciones estructurales manuales en producción fuera del pipeline de migraciones.
- **Compatibilidad retrospectiva (Backwards Compatibility):** Toda nueva migración debe ser compatible con los registros ya almacenados en producción.
- **Estrategia Expand-and-Contract:**
  1. *Expand:* Agregar nuevas columnas o estructuras sin eliminar las anteriores.
  2. *Migrate/Sync:* Trasladar o sincronizar la información existente con la nueva estructura.
  3. *Contract:* Solo tras validar en producción que el código actualizado no utiliza la columna antigua y que no hay dependencias pendientes, programar su retiro en un release posterior previa autorización.
- **Prohibición de eliminación o renombrado directo:** No renombrar ni borrar columnas en uso directo sin análisis de impacto y periodo de transición.
- **Reversibilidad y mitigación de fallos:** No asumir que el método `down()` de una migración recupera datos borrados. Para cambios estructurales de riesgo, diseñar un plan de contingencia y verificar la disponibilidad de respaldos antes de aplicar la migración.
- **Advertencia obligatoria:** Los agentes deben advertir explícitamente y detener el flujo si detectan una migración o comando potencialmente destructivo.

## 2. Separación de Seeds: Configuración vs. Desarrollo

### A. Seeds de Configuración (Idempotentes para todo entorno)
- **Alcance:** Roles del sistema (`Admin`, `Analista`, etc.), permisos base, catálogos indispensables (tipos de venta, categorías de descarte esenciales) y parámetros del sistema.
- **Requisito de idempotencia:** Deben poder ejecutarse múltiples veces consecutivas sin generar registros duplicados ni errores de clave única (utilizar `firstOrCreate`, `updateOrCreate` o cláusulas equivalentes).
- **Aptitud:** Pueden ejecutarse en desarrollo, staging y producción.

### B. Seeds de Desarrollo (Datos simulados / Mocks)
- **Alcance:** Productores simulados, registros de venta de prueba, pesajes ficticios, conductores ficticios y clientes de muestra.
- **Prohibición absoluta en producción:** Jamás deben ejecutarse en entornos de producción.
- **Barrera de seguridad obligatoria:** Los seeders de desarrollo deben incluir una verificación programática que aborte la ejecución si el entorno no es estrictamente `local` o `testing`:
  ```php
  if (app()->environment('production')) {
      throw new \RuntimeException("PELIGRO: Intento de ejecución de datos ficticios en entorno de PRODUCCIÓN.");
  }
  ```
- **Sin datos sensibles:** No incluir contraseñas reales, tokens ni llaves en los seeds. Usar credenciales seguras autogeneradas o de prueba identificadas para desarrollo.

## 3. Usuario administrador inicial de producción
- El administrador de producción no debe crearse con contraseñas fijas o predeterminadas en los archivos del repositorio.
- Debe inicializarse mediante un comando de consola seguro (`php artisan app:crear-admin`) que solicite o genere credenciales temporales robustas y obligue al cambio de contraseña en el primer inicio de sesión.
