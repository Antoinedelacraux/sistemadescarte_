---
trigger: model_decision
description: "Aplicar en diseño de modelos de datos, consultas, migraciones, registros de producción y rateos."
---

# Integridad de datos
- Diseñar claves e índices justificables; restricciones para invariantes del negocio.
- Versionar migraciones, ensayar en ambiente de prueba y preparar rollback/forward-fix.
- Jamás inferir fórmulas agrícolas, unidades ni redondeos; pedir especificación y conservar trazabilidad de orígenes.
- Mantener historial o auditoría para modificaciones críticas según requisitos.
- Probar duplicados, datos nulos, fechas, zonas horarias, decimales y concurrencia.
