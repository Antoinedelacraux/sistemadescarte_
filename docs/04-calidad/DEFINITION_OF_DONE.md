# Definition of Done (DoD)

Un módulo o cambio está listo para ser considerado para integración solo si:

- [ ] Está asociado a un requisito aprobado, criterio de aceptación y ticket.
- [ ] Respeta arquitectura/contratos o documenta ADR aprobada.
- [ ] No añade credenciales ni datos reales sensibles.
- [ ] Tiene validaciones, permisos y manejo de errores acordes al riesgo.
- [ ] Tiene pruebas apropiadas ejecutadas y evidencia del resultado.
- [ ] QA verificó el flujo principal y escenarios de regresión.
- [ ] Revisión de código completada y defectos críticos resueltos.
- [ ] Manuales, API, migrations y registro de cambios están actualizados.
- [ ] Existe plan de rollback cuando afecta despliegue/datos.
- [ ] Propietario aprobó alcance y, para producción, el release.

No equivale a 'cero fallos futuros': las pruebas reducen riesgo, no lo eliminan.
