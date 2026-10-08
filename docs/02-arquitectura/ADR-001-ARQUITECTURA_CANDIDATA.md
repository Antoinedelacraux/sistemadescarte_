# ADR-001 — Arquitectura candidata, DonWeb/Ferozo

**Estado:** PROPUESTO — sujeto a validación técnica y aprobación.

## Contexto
Servidor empresarial compartido con aplicaciones existentes. Capturas: Ferozo, Linux, Apache 2.4.68, PHP 8.4 FPM, MySQL 8.0.44. Sin comprobación de recursos, extensiones, Composer, SSH ni configuración de DocumentRoot del subdominio.

## Decisión candidata
- Monolito modular en Laravel 13 (PHP 8.4), MySQL 8.0.
- Blade + Alpine.js + Tailwind CSS; assets compilados en desarrollo/build, no Node.js residente en producción.
- Autenticación por sesión/cookies seguras; autorización por políticas, permisos y alcance de fundo aplicado en backend.
- Módulos de dominio separados por responsabilidad, **sin microservicios**.
- Base MySQL y usuario dedicados al sistema; no modificar bases existentes.
- HTTPS obligatorio para sitio nuevo; PWA instalable con Service Worker seguro.
- Registro offline queda pendiente de decisión; NO presentar cache como sincronización transaccional.
- Proceso de despliegue reproducible y reversible; sin instalar nada en VPS sin autorización.

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
