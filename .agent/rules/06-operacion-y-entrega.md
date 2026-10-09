---
trigger: model_decision
description: "Aplicar en respaldos, actualizaciones de sistema, despliegues sin pérdida de datos y entrega al cliente."
---

# Respaldos, actualizaciones seguras y entrega a producción

## 1. Procedimiento de respaldos y recuperación ante desastres
- **Frecuencia:** Copias de seguridad automáticas diarias para datos operativos y transaccionales, y respaldos ad-hoc inmediatos antes de cualquier cambio de versión o migración en producción.
- **Almacenamiento independiente:** Los respaldos deben guardarse en un destino físico o de almacenamiento en la nube completamente aislado del servidor de aplicación (off-site / S3 bucket seguro / servidor de backup dedicado).
- **Política de retención:** Conservar respaldos diarios por 30 días, respaldos semanales por 12 semanas y respaldos mensuales por al menos 1 año.
- **Verificación real de integridad:** Nunca asumir que un respaldo es válido solo porque el archivo existe en el disco o tiene un tamaño mayor a cero. Es obligatorio ejecutar pruebas periódicas automatizadas o asistidas de restauración sobre una base de datos aislada y verificar la coherencia de los datos restaurados.
- **Respaldo pre-migración obligatorio:** Antes de aplicar cualquier migración que altere tablas con datos reales, verificar la existencia y recuperabilidad del último respaldo confirmado.

## 2. Flujo obligatorio de 10 pasos para actualizaciones sin pérdida de datos
Cada actualización de producción debe seguir estrictamente este ciclo:
1. **Analizar los cambios solicitados:** Definir requerimientos y alcance funcional.
2. **Identificar módulos y tablas afectadas:** Mapear impacto en el esquema y modelos.
3. **Evaluar compatibilidad:** Asegurar que los registros históricos se mantienen compatibles con el nuevo código.
4. **Preparar migraciones:** Diseñar migraciones versionadas y reversibles (usar *expand-and-contract* si aplica).
5. **Ejecutar pruebas automatizadas:** Correr suite de tests (unitarias, integración, persistencia).
6. **Validar en staging:** Ejecutar la migración sobre datos sintéticos/anonimizados y probar la aplicación completa.
7. **Revisar respaldos y estrategia de recuperación:** Confirmar snapshot/dump reciente y plan de marcha atrás.
8. **Preparar plan de despliegue:** Redactar comandos ordenados, tiempos estimados y ventana de bajo tráfico.
9. **Solicitar autorización humana:** Presentar el plan al propietario y obtener aprobación formal antes de tocar producción.
10. **Verificar funcionamiento posterior (Post-Deploy Checks):** Validar rutas críticas, integridad de registros previos y monitoreo de logs.

> ⛔ **PROHIBICIONES CRÍTICAS EN DESPLIEGUE:**
> - Jamás volver a crear la base de datos (`migrate:fresh`) al desplegar una versión.
> - Jamás reemplazar la base de datos de producción por una base de datos de desarrollo o staging.
> - Jamás ejecutar operaciones destructivas automáticas durante el proceso de despliegue.

## 3. Checklist obligatoria de entrega al cliente (Production Readiness)
Antes de declarar el sistema listo para operación comercial con usuarios reales, debe verificarse:
- [ ] Base de datos de producción inicializada limpiamente mediante migraciones oficiales.
- [ ] Ausencia total de datos ficticios, mocks o semillas de prueba.
- [ ] Roles y permisos del sistema configurados e idempotentes.
- [ ] Usuario administrador inicial configurado mediante procedimiento seguro sin credenciales públicas.
- [ ] Certificado SSL/HTTPS instalado y activo con renovación automática.
- [ ] Archivo `.env` de producción configurado con `APP_DEBUG=false`, claves únicas y secretos protegidos con permisos `600`/`640`.
- [ ] Rutina de respaldos automáticos programada y probada con restauración exitosa.
- [ ] Monitoreo de recursos y registro de errores configurado sin exposición de datos sensibles.
- [ ] Manual de instalación y arquitectura documentado.
- [ ] Manual de usuario y guías operativas para pesadores y administradores entregadas.
- [ ] Manual técnico y procedimientos de actualización y rollback documentados.
- [ ] Responsables de infraestructura, soporte técnico y ventanas de atención formalmente designados.
