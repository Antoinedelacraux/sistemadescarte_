# ADR-001 — Stack Principal y Arquitectura

**Estado:** APROBADA (Con instrucciones para despliegue externo).

## Contexto
El sistema requiere funcionamiento offline parcial, responsividad extrema y un alto nivel de mantenimiento a futuro. El despliegue no lo realizaremos nosotros directamente en el VPS (Ferozo, Apache, PHP 8.4, MySQL 8.0), sino que proveeremos un empaquetado para que el administrador del VPS lo suba y ejecute los comandos iniciales.

## Decisión Técnica
- **Framework Base:** Monolito en Laravel 13 (PHP 8.4) para Backend y renderizado.
- **Frontend:** Blade + Alpine.js para interactividad liviana, y Tailwind CSS para diseño. No habrá Node.js corriendo en el VPS; los assets se entregarán pre-compilados mediante Vite (`npm run build`).
- **Autenticación:** Sesión/cookies con PWA. 
- **Base de Datos:** MySQL 8.0. Un motor de base de datos dedicada.
- **Aislamiento Multi-tenant:** Uso de *Global Scopes* en Laravel por Fundo. Esto blinda a la aplicación contra inyecciones y alteraciones de URL.
- **Sincronización:** Frontend PWA con IndexedDB; Backend expone API idempotente vía UUID v4.

## Método de Entrega
Se estructurará el repositorio dejando claro qué sube al servidor (todo Laravel compilado). Se ha redactado `PLAN_DESPLIEGUE.md` dirigido exclusivamente al administrador de TI del cliente con requerimientos y pasos exactos.

## Estructura candidata del repositorio
```
.AGENTS / .agents/  (configuración ya existente; conservar)
docs/
apps/web/         (Laravel: app/, database/, resources/, public/, tests/)
scripts/          (desarrollo/verificación; solo si hace falta)
```
No mover carpetas preexistentes sin revisar su contenido y sin aprobación. Si el repo ya contiene una estructura elegida, documentar diferencias y sugerir la adaptación mínima.

## Modelo de datos conceptual recomendado
- users, roles, permissions, role_user (según estrategia final de RBAC).
- fundos, usuario_fundo, lotes, cuarteles (FK y relaciones territoriales por confirmar).
- motivos_descarte, tipos_descarte, motivo_tipo_descarte (si un tipo se comparte entre varios motivos).
- ventas_descarte: fundo_id, lote_id, cuartel_id nullable, motivo_id, tipo_id, fecha_produccion, precio, kilogramos_totales, cantidad_jabas nullable, peso_jaba nullable, valor_venta, brevete nullable, ruc nullable, placa nullable, nombre_conductor nullable, viaje nullable, observacion nullable, created_by, updated_by, created_at, updated_at.
- audit_logs: usuario, entidad, identificador, acción, cambios relevantes, timestamp; minimización de secretos/datos delicados.

**Propuestas:** importes y pesos DECIMAL con escala acordada; precio/total calculados en backend; transacciones en cambios múltiples; índices para fundo + fecha; identificador idempotente si se aprueba sincronización offline; historial/auditoría separado de vista operativa.

## Restricciones del despliegue
- Laravel debe exponerse exclusivamente a través de `public/`, nunca toda la raíz del proyecto (documentación oficial).
- Verificar extensiones y CLI, escritura en `storage/` y `bootstrap/cache/`, restricciones de rutas y permisos, HTTPS, bases de datos, backups y restauración.
- Ni Git de Ferozo ni administrador de archivos significan que sea seguro desplegar todo el repositorio bajo `public_html`; confirmar posibilidad de apuntar DocumentRoot a la carpeta pública.
- Precompilar assets, mantener `.env` fuera de la raíz pública y del repositorio.
- Evitar depender de procesos worker permanentes, Redis o scheduler que no estén verificados.
- No desplegar hasta tener procedimientos de backup, recuperación y rollback probados.

## Alternativa si Laravel no puede desplegarse correctamente
Investigar con el responsable del VPS permisos o cuenta/subdominio aislado compatible. No degradar la seguridad publicando el proyecto entero. Documentar alternativas antes de elegir un stack diferente.

## Pruebas no negociables
Permisos cross-fundo y exportación, exactitud decimal, filtros, auditoría, responsive, migraciones seguras, rendimiento básico, restauración de backup y HTTPS/PWA.
