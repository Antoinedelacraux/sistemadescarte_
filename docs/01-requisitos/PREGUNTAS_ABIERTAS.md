# Preguntas Abiertas y Ambigüedades (Prioridad Alta)

A continuación, las decisiones que deben responderse para poder iniciar el desarrollo:

1. **Registro sin Internet (Resuelto):** Se confirmó el uso de PWA offline-first con IndexedDB y sincronización posterior.
2. **Alcance del Rol General:** El TXT dice "pueden ver los registros de su fundo como el de los otros fundos". Pero al registrar, ¿pueden *crear* registros a nombre de otros fundos o solo en el propio? ¿Quién puede editar o anular registros una vez guardados?
3. **Edición / Anulación:** El TXT menciona "Fecha de producción... también se puede editar". ¿Existe un flujo formal de edición/anulación o cualquier usuario puede modificar un registro histórico en cualquier momento?
4. **Tipos de Cosecha Nacional:** El TXT menciona tipos para Cosecha Nacional: `"Racimos y granos"`. ¿Es una opción compuesta única ("Racimos y granos" juntos en un solo checkbox/select) o son dos opciones separadas ("Racimos", "Granos")?
5. **Moneda y Precio:** El TXT indica "Precio - Obligatorio". ¿En qué moneda es (Soles, Dólares)? ¿Con cuántos decimales de precisión se debe guardar y mostrar el precio y el peso? ¿Cómo se redondea el "Valor de la venta"?
6. **Relación Lote-Cuartel:** ¿Un Cuartel le pertenece a un Lote, o le pertenece directamente al Fundo?
7. **Diferencias con Propuestas Anteriores:** Los archivos propuestos (`ANALISIS_V1`) no resolvían si "General" exportaba o editaba. El TXT tiene prioridad. Queda bloqueado el desarrollo de edición/anulación hasta aclarar la regla 3.
