# Matriz de QA

| ID prueba | Requisito | Tipo | Escenario | Esperado | Ejecutado | Resultado | Evidencia/issue |
|---|---|---|---|---|---|---|---|
| SYNC-01 | Offline-First (General) | Integración | Usuario registra venta de descarte sin internet, luego recupera conexión. | Los datos se guardan en IndexedDB, estado "Pendiente". Al reconectar o abrir app, se envía y cambia a "Sincronizado". | No | Sin ejecutar | — |
| SYNC-02 | Idempotencia | E2E | Interrupción de red justo después de enviar el registro pero antes del response. La app reintenta el mismo registro. | Servidor detecta UUID duplicado, responde éxito. La BD tiene 1 sola inserción. | No | Sin ejecutar | — |
| SYNC-03 | Fallo de Servidor / Reglas | Integración | Usuario registra en offline usando un catálogo desactualizado. El servidor lo rechaza. | Sincronización recibe error HTTP. La app local marca el registro "Error" pero no lo borra. | No | Sin ejecutar | — |
| SYNC-04 | Protección Datos Locales | UI / Funcional | Usuario intenta cerrar sesión teniendo ventas en estado "Pendiente" o "Error" en cola. | Se bloquea o muestra advertencia severa exigiendo confirmación explícita para evitar pérdida de datos. | No | Sin ejecutar | — |
| SYNC-05 | Compatibilidad Safari iOS | E2E en disp. real | Minimizar Safari en iOS mientras la cola tiene 100 registros pendientes; abrir la app tras 10 min. | Al traer a primer plano, la PWA retoma la sincronización sin corromper la IndexedDB. | No | Sin ejecutar | — |
