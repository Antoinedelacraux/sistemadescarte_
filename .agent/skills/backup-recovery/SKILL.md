---
name: backup-recovery
description: "Prepara, ejecuta y valida estrategias de respaldo y recuperación ante desastres en producción, verificando integridad mediante pruebas de restauración reales."
---

# Skill: Backup and Recovery (Estrategia y Verificación de Respaldos)

## Propósito
Diseñar, ejecutar y comprobar rigurosamente los mecanismos de copia de seguridad y recuperación ante desastres para los datos del sistema agrícola, garantizando que todo respaldo sea verdaderamente utilizable ante cualquier eventualidad.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Antes de aplicar cualquier migración estructural, actualización de versión o mantenimiento en producción.
- Al configurar o auditar las tareas programadas (cron jobs) de respaldo del servidor.
- Para ensayar la restauración periódica de datos en un entorno de pruebas o staging.
- Ante cualquier sospecha de inconsistencia, corrupción o solicitud de contingencia operativa.

---

## Regla de Oro de Confiabilidad
> ⚠️ **NUNCA considerar un respaldo como válido únicamente porque el archivo existe o tiene peso en disco.** Un archivo comprimido puede estar incompleto, corrupto, contener tablas vacías o carecer de sentencias válidas. Un respaldo solo es válido si ha sido restaurado exitosamente en un entorno de prueba y se ha verificado el contenido de sus tablas.

---

## Procedimiento de Respaldo

### 1. Frecuencia y Tipos de Copia
- **Respaldos Transaccionales Diarios:** Ejecutados automáticamente cada medianoche durante la ventana de menor actividad.
- **Respaldos Pre-Migración Inmediatos (Ad-Hoc):** Dump completo tomado minutos antes de cualquier intervención técnica en producción.
- **Respaldos Semanales y Mensuales:** Para archivo histórico y auditoría contable.

### 2. Generación Segura de la Copia
- Generar el volcado con consistencia transaccional (ej. `--single-transaction` en MySQL / volcado de SQLite con WAL checkpoint asegurado):
  ```bash
  # Ejemplo seguro en MySQL
  mysqldump -u [usuario] -p[password] --single-transaction --quick --routines --triggers sistema_fundo > /var/backups/fundo_$(date +%Y%m%d_%H%M%S).sql
  gzip /var/backups/fundo_$(date +%Y%m%d_%H%M%S).sql
  ```
- Comprobar código de salida `$? == 0` y verificar que el archivo generado tenga un tamaño coherente con el volumen de producción.

### 3. Almacenamiento Aislado e Independiente (Off-Site)
- La copia no debe permanecer únicamente en el mismo disco del servidor web.
- Transferir inmediatamente el respaldo cifrado a un almacenamiento secundario independiente (almacenamiento en la nube seguro con versionado, bucket S3 privado o servidor de respaldo dedicado).
- Configurar permisos estrictos `chmod 600` para que ningún usuario no privilegiado pueda leer el volcado.

### 4. Política de Retención
- Conservar los respaldos diarios durante 30 días.
- Conservar un respaldo semanal durante 12 semanas.
- Conservar un respaldo mensual durante 1 año mínimo.

---

## Procedimiento Obligatorio de Verificación de Integridad (Ensayo de Restauración)
Para validar la copia sin comprometer producción:
1. Crear una base de datos temporal de prueba en un entorno aislado (`fundo_test_restore`).
2. Restaurar el archivo de respaldo:
   ```bash
   gunzip -c /var/backups/fundo_YYYYMMDD_HHMMSS.sql.gz | mysql -u [usuario] -p[password] fundo_test_restore
   ```
3. Ejecutar consultas de validación de consistencia:
   - Conteo de registros en tablas críticas: `SELECT COUNT(*) FROM ventas_descarte;`
   - Conciliación de montos totales: `SELECT SUM(valor_total) FROM ventas_descarte;`
   - Integridad de catálogos: Verificar que los fundos, lotes y usuarios coincidan exactamente con la base original.
4. Si las consultas coinciden al 100%, certificar el respaldo como **VÁLIDO Y RECUPERABLE**. Si falla, generar alerta crítica inmediata.

---

## Procedimiento de Recuperación ante Fallos (Disaster Recovery Runbook)
Si una migración o fallo en producción corrompe los datos:
1. **Poner la aplicación en mantenimiento:** `php artisan down --secret="mantenimiento-tal-2026"`
2. **Identificar el último respaldo verificado:** Localizar el dump pre-migración o el último diario íntegro.
3. **Detener escrituras concurrentes en la base de datos.**
4. **Restaurar el volcado verificado** sobre la base de datos de producción.
5. **Ejecutar pruebas de sanidad:** Validar login de administrador y consulta del historial de ventas.
6. **Levantar el modo de mantenimiento:** `php artisan up`
7. **Documentar el incidente:** Causa raíz, tiempo de recuperación y medidas correctivas en `docs/00-gestion/REGISTRO_CAMBIOS.md`.

---

## Criterios de Aceptación
- La estrategia de respaldos está documentada con scripts reproducibles.
- Las copias se encuentran en almacenamiento aislado y con permisos restringidos.
- Se cuenta con evidencia documentada de pruebas de restauración exitosas.
