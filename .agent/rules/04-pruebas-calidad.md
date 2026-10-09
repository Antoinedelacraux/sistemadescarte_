---
trigger: model_decision
description: "Aplicar en programación, pruebas automatizadas, regresiones, integridad referencial y entregas de software."
---

# Calidad, pruebas y verificación continua

## 1. Alcance de las verificaciones requeridas
Toda modificación o nueva funcionalidad debe someterse a verificación en los siguientes niveles:
1. **Funcionalidad y lógica de negocio:** Coherencia de cálculos (kilogramos, precios, subtotales, totales y redondeos agrícolas aprobados).
2. **Validación de datos:** Pruebas de límites, campos obligatorios, valores numéricos negativos, cadenas excesivas y formatos de fecha.
3. **Integridad referencial y claves foráneas:** Confirmar que no queden registros huérfanos y que las restricciones de clave foránea operen adecuadamente en eliminaciones o anulaciones.
4. **Autenticación y autorización (RBAC):** Verificar que usuarios con rol `Individual` o `Visualizador` no puedan acceder ni mutar recursos no autorizados o pertenecientes a otros fundos.
5. **Operaciones CRUD:** Comprobar la creación, lectura, actualización y anulación controlada en dos pasos.
6. **Migraciones e integridad de esquema:** Ejecutar pruebas automatizadas que apliquen migraciones sobre esquemas existentes y verifiquen que la estructura resultante es idéntica a la esperada.
7. **Persistencia y disponibilidad de datos históricos:** Ejecutar pruebas específicas que demuestren que los registros creados antes de una modificación o actualización continúan íntegros, legibles y disponibles.

## 2. Prevención de regresiones
- Cada vez que se corrija un error, se debe incorporar una prueba automatizada que reproduzca el fallo para garantizar que no reaparezca en versiones futuras.
- Ningún cambio debe comprometer las pruebas existentes; si un test falla, debe investigarse la causa de fondo antes de asumir que el test debe modificarse.

## 3. Disciplina de entrega y evidencia
- **Prohibido asumir:** Nunca afirmar que un componente funciona solo porque no produjo errores en el editor de código o porque el servidor arrancó.
- **Evidencia requerida:** Todo reporte de tarea debe detallar:
  - Comando ejecutado (ej. `php artisan test --filter=...`).
  - Número de pruebas y aserciones completadas.
  - Limitaciones o pruebas que deban ejecutarse en un entorno superior (staging/producción).
- No declarar una tarea como finalizada si quedan comprobaciones críticas pendientes sin documentar.
