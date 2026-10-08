# Procedimiento de cambios posteriores

1. Registrar solicitud con motivo, prioridad y criterios de aceptación.
2. Analizar impacto en UI, API, modelo de datos, reportes, permisos, pruebas y operación.
3. Proponer solución/estimación y obtener aprobación.
4. Implementar cambio pequeño en rama aislada, con pruebas de regresión.
5. QA + revisión seguridad según riesgo; documentar cambios y decisiones.
6. Planificar lanzamiento reversible, backup si corresponde y aprobación explícita.
7. Verificar resultado en entorno de destino y conservar evidencias.

Todo cambio debe evitar romper compatibilidad con datos históricos o integraciones sin migración acordada.
