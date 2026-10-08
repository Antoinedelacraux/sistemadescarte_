# Registro de riesgos

| ID | Riesgo | Probabilidad | Impacto | Mitigación | Responsable | Estado |
|---|---|---|---|---|---|---|
| R-01 | Requisitos ambiguos | Media | Alta | Aprobar reglas e historias antes de programar | Analista | Abierto (Esperando respuestas) |
| R-02 | Exposición de datos/credenciales | Media | Alta | Mínimo privilegio, .gitignore, datos ficticios | Seguridad | Abierto |
| R-03 | Autorización por fundo rota (Cross-tenant) | Alta | Crítico | Aplicar validación estricta (Global Scopes en Laravel) asegurando que Individual no acceda a datos de otros fundos. Pruebas automatizadas. | Backend | Abierto |
| R-04 | Trazabilidad vulnerada o datos alterados | Media | Alta | Guardar fecha de sistema y usuario en Backend (no confiar en la UI). Mantener tabla de auditoría inmutable (audit_logs). | Arquitecto/Seguridad | Abierto |
| R-05 | Fallos en validación de datos Offline-Online | Alta | Alta | Validar campos de vuelta en backend tras sincronizar. Evitar corrupción por UUIDs mal generados o decimales alterados. | QA / Backend | Abierto |
