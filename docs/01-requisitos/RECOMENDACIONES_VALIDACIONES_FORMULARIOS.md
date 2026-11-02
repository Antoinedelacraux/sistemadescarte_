# Recomendaciones de validación para formularios de registro

**Estado:** Implementado en entorno local; pendiente de revisión funcional del propietario  
**Fecha:** 2026-11-02  
**Alcance:** Formulario de Registro de Venta de Descarte y envío/confirmación en Garita.

## Objetivo

Reducir registros incompletos, errores de digitación y duplicidad de viajes sin bloquear el trabajo operativo. Las reglas deberán aplicarse en el backend; la validación del navegador será una ayuda visual adicional.

## Decisiones aplicadas

- Los borradores conservan los totales actuales y los nuevos pueden editarse sin vencimiento; los registros históricos mantienen su regla de 24 horas.
- La diferencia entre jabas, peso promedio y kilogramos se muestra como advertencia y no bloquea el registro.
- La placa se convierte a mayúsculas sin imponer un formato adicional.
- Antes del envío a Garita se solicita revisar la información y el servidor comprueba que los datos estén completos y sean válidos.
- No se definieron máximos operativos para kilos, precio o jabas; por ello no se impusieron topes arbitrarios.

## Situación comprobada

El sistema ya valida fundo accesible, lote perteneciente al fundo, motivo, tipo de descarte, kilogramos y precio. También aplica reglas especiales para Cosecha Nacional y conserva la lógica de Garita por fundo, fecha, viaje y placa.

Actualmente varios datos de trazabilidad y transporte pueden quedar vacíos, por lo que se propone diferenciar entre:

- **Guardar como borrador:** permite avanzar con un registro preliminar cuando todavía no se tienen el conductor, la placa, el viaje u otros datos de transporte. Debe quedar claramente identificado como incompleto y editable.
- **Enviar a Garita:** exige la información mínima completa del despacho y cambia el registro a estado pendiente de validación.

### Regla confirmada: borradores por información pendiente

Si en el momento del pesaje todavía no se conocen los datos del conductor, el usuario podrá guardar el registro como **Borrador**. El borrador:

- No se considera un envío listo para Garita.
- No aparece como pendiente de validación en Garita.
- Puede editarse para completar conductor, placa, viaje, cliente u otros datos faltantes.
- Debe mostrar qué campos faltan para poder enviarlo.
- No debe afectar los totales operativos que representan despachos confirmados, salvo que el propietario decida incluir preliminares en un reporte separado.

Para evitar confusiones con los estados actuales, la implementación deberá definir un estado de registro preliminar separado de `estado_garita` y de `activo/anulado`, o documentar formalmente una reutilización compatible antes de modificar el esquema.

## Lotes propuestos

### Lote 1 — Validaciones básicas y datos obligatorios

**Objetivo:** impedir registros evidentemente inválidos.

Reglas propuestas:

- Fundo obligatorio y limitado a los fundos asignados al usuario.
- Lote obligatorio y perteneciente al fundo seleccionado.
- Fecha de producción obligatoria y sin fechas futuras.
- Motivo y tipo de descarte únicamente de los valores permitidos.
- Kilogramos y precio mayores que cero, con máximo de dos decimales.
- Jabas como entero mayor o igual a cero; definir si operacionalmente debe ser estrictamente mayor que cero.
- Límites máximos configurables para evitar errores de digitación extremos.

**Criterios de aceptación:**

1. El servidor rechaza valores negativos, texto en campos numéricos y fechas futuras.
2. Un lote de otro fundo nunca puede guardarse manipulando el formulario.
3. Los mensajes indican claramente el campo que debe corregirse.

**Decisiones pendientes:** máximo permitido de kilogramos, precio y jabas; si jabas puede ser cero.

### Lote 2 — Coherencia entre pesaje y jabas

**Objetivo:** detectar cantidades físicamente improbables.

Reglas propuestas:

- Si se informan jabas y peso promedio, calcular `jabas × peso_jaba`.
- Comparar el resultado con kilogramos registrados.
- Mostrar advertencia cuando la diferencia supere un margen configurable.
- Bloquear el envío a Garita si la diferencia supera un margen crítico.
- Peso por jaba y kilogramos con valores positivos y dos decimales.

**Criterios de aceptación:**

1. El cálculo visible usa los mismos valores que valida el backend.
2. Una diferencia dentro del margen permitido no bloquea el flujo.
3. Una diferencia crítica explica los valores esperados y los registrados.

**Decisiones pendientes:** margen de advertencia y margen de bloqueo, según el proceso real de pesaje.

### Lote 3 — Cliente, RUC y normalización de texto

**Objetivo:** evitar clientes duplicados y datos de identificación inconsistentes.

Reglas propuestas:

- Recortar espacios al inicio y al final y consolidar espacios repetidos.
- Comparar clientes sin distinguir mayúsculas/minúsculas para detectar duplicados.
- Rechazar nombres compuestos únicamente por números o símbolos.
- RUC: exactamente 11 dígitos cuando se registre.
- Mantener RUC como texto para no perder ceros ni generar notación científica en Excel.
- Limitar longitudes y rechazar HTML o contenido de scripts.

**Criterios de aceptación:**

1. `QWER`, `qwer` y `QWER ` se reconocen como el mismo cliente para sugerencias y catálogo.
2. Un RUC con una longitud distinta de 11 dígitos muestra un mensaje específico.
3. La normalización no modifica el dato histórico ya guardado sin una acción explícita.

**Decisiones pendientes:** si el cliente y RUC serán obligatorios siempre o solo al enviar a Garita.

### Lote 4 — Transporte y número de viaje

**Objetivo:** asegurar trazabilidad del camión y evitar confundir viajes de distintas fechas.

Reglas propuestas:

- Al enviar a Garita, exigir conductor, placa, número de viaje, jabas y kilogramos.
- Conductor: mínimo dos palabras; aceptar letras, tildes, espacios, apóstrofes y guiones.
- Placa: convertir a mayúsculas, quitar espacios innecesarios y validar formatos permitidos.
- Viaje: entero positivo (`1`, `2`, `3`, etc.), sin textos como `Viaje 1` salvo aprobación del negocio.
- La identidad del viaje debe ser `fundo + fecha de producción + número de viaje`.
- Para agrupar físicamente un envío, usar además la placa.
- Advertir o bloquear duplicados exactos de `fundo + fecha + viaje + placa`, según la excepción operativa aprobada.

**Criterios de aceptación:**

1. El Viaje 1 de una fecha nunca se mezcla con el Viaje 1 de otra fecha.
2. La confirmación de Garita no acepta registros de fundos, fechas, viajes o placas mezclados.
3. La placa se muestra normalizada en tarjetas, historial, Excel y API.

**Decisiones pendientes:** formatos exactos de placa, formato de brevete y tratamiento de varios lotes dentro del mismo viaje.

### Lote 5 — Puerta de control antes de enviar a Garita

**Objetivo:** que Garita reciba únicamente despachos trazables y verificables.

Antes de enviar, exigir:

- Cliente.
- Conductor.
- Placa.
- Número de viaje.
- Cantidad de jabas.
- Kilogramos.
- Fecha, fundo y lote válidos.
- Registro activo, no anulado y no validado previamente.
- Todos los registros agrupados con el mismo fundo, fecha, viaje y placa.

El guardado preliminar puede continuar permitiendo campos incompletos, pero debe mostrar el estado **Incompleto** y explicar qué falta para enviar.

**Criterios de aceptación:**

1. El botón de envío informa todos los campos faltantes antes de enviar.
2. Un borrador sin conductor puede guardarse, pero no puede enviarse a Garita.
3. El backend vuelve a validar la agrupación aunque el usuario manipule la petición.
4. Un envío ya validado no puede reenviarse ni confirmarse nuevamente.

### Lote 6 — Experiencia de usuario, seguridad y pruebas

**Objetivo:** hacer las validaciones comprensibles y consistentes.

Reglas propuestas:

- Validación inmediata debajo del campo, además del resumen superior.
- Formato automático para placa, RUC y cantidades.
- Contador de caracteres para observaciones.
- Deshabilitar el botón mientras se procesa el guardado para prevenir dobles envíos.
- Mantener protección CSRF, autorización por fundo y validación de servidor.
- Probar cada regla con pruebas unitarias, integración, regresión y casos de manipulación de peticiones.
- Verificar que las validaciones no alteren registros históricos, Excel, API ni el flujo offline.

**Criterios de aceptación:**

1. Cada regla aprobada tiene al menos una prueba de aceptación positiva y una negativa.
2. La interfaz y el servidor muestran mensajes equivalentes.
3. La suite existente continúa pasando sin modificar datos reales.

## Orden recomendado de implementación

1. **Lote 1:** datos básicos y rangos numéricos.
2. **Lote 4:** transporte y viaje, por su impacto directo en Garita.
3. **Lote 5:** bloqueo de envíos incompletos.
4. **Lote 3:** normalización de clientes y RUC.
5. **Lote 2:** coherencia jabas/peso/kilogramos, después de definir márgenes.
6. **Lote 6:** mejoras de interfaz y cobertura completa de pruebas.

## Riesgos y precauciones

- Volver obligatorios todos los campos al guardar podría interrumpir la operación de campo; por eso se propone endurecer principalmente el paso **Enviar a Garita**.
- Las reglas de placa, brevete y márgenes de pesaje requieren confirmación del propietario para no rechazar casos válidos.
- No se propone borrar ni corregir automáticamente registros históricos.
- Cualquier cambio de esquema deberá tener migración reversible, respaldo y pruebas de compatibilidad con SQLite y MySQL.

## Flujo de guardado aplicado

- **Guardar borrador:** valida ubicación, clasificación, pesaje y precio; permite completar luego los datos de transporte.
- **Guardar registro:** valida además cliente, jabas mayores que cero, placa, conductor con nombre y apellido y número de viaje positivo. El registro queda guardado y todavía requiere el envío explícito a Garita.
- Cuando jabas por peso promedio difiere de los kilogramos, el sistema solicita confirmación antes de guardar, sin bloquear el flujo.

## Aprobación requerida

Antes de implementar, el propietario debe indicar:

1. Qué lotes aprueba para la primera etapa.
2. Si cliente, conductor, placa, viaje y jabas serán obligatorios solo para Garita o también para guardar.
3. Los rangos máximos y márgenes de diferencia del pesaje.
4. Los formatos aceptados para placas y brevete.
5. Qué excepciones operativas deben permitirse.
