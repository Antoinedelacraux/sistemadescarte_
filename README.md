# Sistema Web del Fundo — TALSA Grape Farms

Aplicación web empresarial y PWA para registrar, controlar, analizar y exportar ventas de descarte agrícola. Está diseñada para operación en campo desde celulares y para gestión desde equipos de escritorio.

## Estado actual

- **Versión documentada:** 1.1.47
- **Estado:** módulos funcionales completos, en etapa de feedback y validación móvil.
- **Aplicación:** `apps/web/`
- **Backend:** PHP 8.3+ y Laravel 13.
- **Frontend:** Blade, CSS responsive y JavaScript sin compilación obligatoria para ejecutar la interfaz actual.
- **Datos:** SQLite en desarrollo y MySQL 8.0 en producción.
- **PWA:** instalable en Android, iOS y escritorio, con soporte de caché y captura offline.
- **Calidad:** 120 pruebas automatizadas y 771 aserciones aprobadas en la última ejecución completa.

> La aplicación aún requiere validación visual final en Android y completar el procedimiento de despliegue antes de considerarse liberada en producción.

## Roles y acceso

El sistema dispone de seis roles oficiales:

| Rol | Alcance principal |
|---|---|
| Administrador | Configuración, usuarios, fundos y acceso integral. |
| Jefe de fundo | Consulta de los fundos asignados. |
| Registrador | Registro, edición permitida e historial de ventas de sus fundos. |
| Analista | Consulta transversal, API y exportaciones. |
| Ventas | Consulta comercial e historial transversal. |
| Garita | Control y confirmación de salidas en los fundos asignados. |

Los permisos de módulos se controlan mediante RBAC. `FundoScope` y las verificaciones del servidor restringen los datos según los fundos asignados al usuario.

## Funcionalidades principales

### Registro de descarte

- Registro por fundo, fecha, lote, cuartel, motivo y tipo de descarte.
- Motivos admitidos: Campo, Packing y Cosecha Nacional.
- Cálculo automático de kilogramos, precio y total liquidado.
- Catálogos dinámicos de lotes, clientes y RUC.
- Guardado como **borrador** cuando todavía falta información.
- Guardado como **registro oficial** cuando cumple los datos requeridos.
- Datos del viaje, cliente, carga y vehículo organizados en una cuadrícula compacta que se adapta a tabletas y celulares.
- RUC validado por longitud de 11 dígitos.
- Advertencia por diferencias entre el peso registrado y `jabas × peso promedio`.
- Auditoría de creación, modificación y marcas de tiempo.

### Viajes y relación entre lotes

Un viaje se identifica por la combinación:

```text
fundo + fecha de producción + número de viaje
```

- Un viaje puede incluir descarte procedente de varios lotes.
- Todos sus registros deben conservar el mismo cliente, RUC, conductor, brevete y placa.
- El servidor rechaza registros contradictorios para el mismo viaje.
- Cada envío dispone de un código estable con el formato `ENV-AAAAMMDD-FUNDO-VIAJE`.
- Los borradores incompletos o los lotes discordantes impiden enviar el viaje a Garita.

### Flujo Registrador–Garita

1. El Registrador guarda uno o más lotes como borrador u oficial.
2. Completa los datos requeridos y envía el viaje completo a Garita.
3. Garita recibe el pendiente únicamente si pertenece a uno de sus fundos asignados.
4. Garita revisa el detalle y confirma la salida, con una observación opcional.
5. La validación se aplica de forma transaccional a todos los registros activos del envío.
6. La fecha y hora de validación quedan disponibles en historial, API y exportación Excel.

Una venta oficial puede editarse antes de enviarse a Garita. Cuando el viaje está pendiente o validado, queda bloqueado para proteger la coherencia del despacho.

### Panel de Garita

- Pestañas de pendientes y validados.
- Filtros principales por fecha y número de viaje.
- Tres tarjetas por fila en escritorio, dos en tableta y una en móvil.
- Vista móvil compacta con acciones táctiles accesibles.
- Historial tabular con fecha, viaje, cliente, conductor, placa, jabas, kilogramos, lotes y validación.
- Avisos PWA exclusivos del rol Garita cuando aparecen viajes pendientes.

Los avisos actuales consultan pendientes cada 30 segundos mientras la PWA está activa o vuelve al primer plano. Recibir notificaciones con la aplicación completamente cerrada requiere una futura implementación de Web Push con VAPID.

### Historial, análisis e integraciones

- Historial de ventas con filtros, paginación y detalle.
- Exportación de los historiales de ventas y validaciones de Garita a Excel, conservando filtros, orden de columnas y formato tabular.
- Columna de acciones compacta: detalle y acción principal visibles; edición, anulación y otras operaciones secundarias bajo un menú contextual.
- En móvil, el Registrador conserva visibles las fechas y abre los demás criterios desde **Más filtros**.
- Panel analítico con indicadores y matrices por tipo, descarte y cliente.
- Exportación nativa a archivos `.xlsx`.
- API v1 protegida por token para consumo analítico, incluido el campo de validación de Garita.

### PWA y operación sin conexión

- Manifest e iconos para instalación en dispositivos.
- Service Worker con caché de recursos y respuestas de contingencia válidas.
- Cola local para registros sin conexión e idempotencia para evitar duplicados al sincronizar.
- Separación por cuenta de los viajes ya notificados en dispositivos compartidos.

## Instalación local

Requisitos:

- PHP 8.3 o superior con extensiones requeridas por Laravel.
- Composer.
- SQLite para desarrollo o una conexión MySQL 8.0 configurada.

Desde la raíz del repositorio:

```bash
cd apps/web
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate
php artisan serve
```

La aplicación quedará disponible en `http://127.0.0.1:8000`.

Para trabajar con Vite en modo desarrollo:

```bash
npm install
npm run dev
```

## Verificación

```bash
cd apps/web

# Suite completa
php artisan test

# Pruebas enfocadas en el flujo operativo
php artisan test tests/Feature/Ventas/VentaDescarteTest.php
php artisan test tests/Feature/Garita/GaritaWorkflowTest.php

# Validar la compilación de vistas
php artisan view:cache
```

La última ejecución completa registrada aprobó 120 pruebas y 771 aserciones.

## Despliegue

La documentación operativa distingue entre instalación limpia y actualización con conservación de datos. Antes de desplegar:

1. Verificar las notas de la versión y migraciones pendientes.
2. Generar y comprobar un respaldo restaurable.
3. Ensayar el despliegue en un entorno de pruebas.
4. Ejecutar migraciones solamente mediante el procedimiento documentado.
5. Completar smoke tests y disponer de un plan de reversión.

Consulta la [Guía de despliegue en DonWeb Ferozo](docs/05-operacion/GUIA_DESPLIEGUE_FEROZO_DONWEB.md) y el [plan de despliegue](docs/05-operacion/PLAN_DESPLIEGUE.md).

## Documentación del proyecto

- [Estado del proyecto](docs/00-gestion/ESTADO_PROYECTO.md)
- [Registro de cambios](docs/00-gestion/REGISTRO_CAMBIOS.md)
- [Requisitos aprobados](docs/01-requisitos/REQUISITOS_APROBADOS.md)
- [Decisiones de arquitectura](docs/02-arquitectura/DECISIONES.md)
- [Documentación de calidad](docs/04-calidad/)
- [Documentación operativa](docs/05-operacion/)
- [Procedimientos de mantenimiento](docs/06-mantenimiento/)

## Protección de datos

El código puede evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible. No se deben ejecutar reinicios, semillas ficticias, eliminaciones históricas ni migraciones destructivas sobre producción sin autorización, respaldo verificado y plan de recuperación.
