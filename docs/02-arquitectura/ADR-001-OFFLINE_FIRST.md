# ADR-001: Arquitectura Offline-First para Venta de Descarte

- **Estado:** Aprobada.
- **Fecha:** 2026-10-08
- **Decisor:** Propietario

## Contexto
Los trabajadores operan frecuentemente en zonas sin conectividad a internet. Se requiere que puedan registrar ventas de descarte de forma ininterrumpida y sincronizarlas cuando la red esté disponible, utilizando tanto dispositivos Android como iPhone.

## Alternativas
| Opción | Ventajas | Desventajas | Operación / costo | Riesgos |
|---|---|---|---|---|
| A) App Nativa o Híbrida (Flutter/React Native) + SQLite | Máximo rendimiento offline, acceso profundo al SO y background robusto. | Mantenimiento de repositorios o frameworks paralelos. Proceso de publicación en tiendas. | Alto costo de desarrollo, mantenimiento y despliegue. | Aprobación de tiendas y demoras en distribución de actualizaciones. |
| B) PWA + IndexedDB + Motor Sync Custom (Elegida) | Única base de código web (Laravel/Blade/Alpine). Despliegue sin pasar por tiendas. | Safari en iOS limita estrictamente el Background Sync API y almacenamiento. | Costo medio. Aprovecha al 100% el stack web elegido. | Limpieza de caché puede borrar IndexedDB si no se maneja cuidadosamente. |

## Decisión y justificación
Se decide implementar una **PWA (Progressive Web App)** respaldada por un Service Worker y almacenamiento en **IndexedDB**. 
Dado que el stack es Laravel + Blade + Alpine.js, se integrará un motor de sincronización nativo en Javascript que orqueste la cola de subida y el estado de los catálogos. 
Debido a las severas limitaciones de Background Sync en Safari/iOS, la sincronización se apoyará firmemente en eventos de primer plano (abrir la app, eventos `online` del navegador) y un mecanismo de reintento manual.
Para asegurar la integridad, el cliente generará **UUIDs v4** para cada registro de descarte antes de guardarlo en IndexedDB, garantizando **idempotencia** al enviarse al servidor.

## Consecuencias
- **Arquitectura:** Desacopla la vista (UI) de la lógica de red. Las peticiones de guardado van a IndexedDB, y un proceso asíncrono se encarga de vaciar la cola hacia la API de Laravel de forma paralela.
- **Mantenibilidad:** Requiere código JS robusto e independiente del Backend para gestionar la cola y los estados.
- **Validación Dual:** El cliente valida con catálogos cacheados, pero Laravel es la fuente definitiva de verdad e implementará re-validación completa.

## Plan de reversión
Al ser un requerimiento crítico, la reversión implicaría retroceder al registro en papel. En caso de emergencia o bug crítico en la red, se deberá proveer un mecanismo (ej. exportación local) para extraer la cola de IndexedDB local a un archivo JSON y cargarlo manualmente en el servidor.
