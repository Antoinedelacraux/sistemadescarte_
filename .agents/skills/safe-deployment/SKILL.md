---
name: safe-deployment
description: "Ejecuta y verifica despliegues seguros en 10 pasos sin pérdida de datos, asegurando que las nuevas versiones no sobreescriban ni destruyan la información de producción."
---

# Skill: Safe Deployment (Despliegues Seguros sin Pérdida de Datos)

## Propósito
Guiar la publicación de nuevas versiones, parches o mejoras en el sistema web del fundo mediante un flujo estandarizado de 10 pasos que garantice cero tiempo de inactividad imprevisto, cero corrupción de esquemas y cero pérdida de información histórica del negocio.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Al planificar o ejecutar el paso de cambios desde desarrollo/staging hacia producción.
- Antes de aplicar actualizaciones de código que incluyan migraciones de base de datos.
- Para verificar que un release cumple todas las condiciones de seguridad antes de su publicación.

---

## Prohibiciones Críticas en Despliegue
- ❌ **JAMÁS volver a crear la base de datos:** Prohibido utilizar `php artisan migrate:fresh` o `migrate:reset` durante el despliegue en producción.
- ❌ **JAMÁS sobreescribir la base de producción con una base de desarrollo:** Cada entorno mantiene sus propios datos; la producción solo evoluciona mediante migraciones versionadas.
- ❌ **JAMÁS ejecutar operaciones destructivas automáticas:** Truncados, borrados masivos o reseteos de secuencias no pueden formar parte del script de release.

---

## El Flujo Obligatorio de 10 Pasos para Despliegues Seguros

```
[1. Analizar] ➔ [2. Mapear Tablas] ➔ [3. Compatibilidad] ➔ [4. Migraciones] ➔ [5. Tests]
                                                                                   │
[10. Post-Deploy] ⬅ [9. Autorización] ⬅ [8. Plan Release] ⬅ [7. Backup Previo] ⬅ [6. Staging]
```

### Paso 1: Analizar los cambios solicitados
- Revisar requerimientos funcionales, historias de usuario y tickets resueltos.
- Identificar dependencias nuevas de Composer/NPM o cambios en variables de entorno.

### Paso 2: Identificar módulos y tablas afectadas
- Listar los módulos que sufren modificaciones (ej. Ventas, Catálogos, Autenticación, Reportes).
- Mapear explícitamente todas las tablas de base de datos intervenidas.

### Paso 3: Evaluar compatibilidad con datos existentes
- Comprobar que los registros ya almacenados en producción satisfagan las nuevas restricciones o validaciones del código.
- Si se añade un campo nuevo obligatorio, verificar que cuente con valor predeterminado o que se aplique la estrategia *Expand-and-Contract*.

### Paso 4: Preparar migraciones versionadas
- Confirmar que toda modificación de base de datos esté contenida en archivos de migración no destructivos dentro de `database/migrations/`.
- Verificar que el método `up()` sea seguro y que existan planes de marcha atrás.

### Paso 5: Ejecutar pruebas automatizadas completas
- Ejecutar la suite completa de pruebas unitarias, de integración y de persistencia:
  ```bash
  php artisan test
  ```
- 100% de las pruebas deben pasar exitosamente. Cero aserciones fallidas toleradas.

### Paso 6: Validar en Staging (Homologación)
- Desplegar primero la versión en el entorno de staging con datos sintéticos/anonimizados.
- Comprobar flujos completos de usuario (login, registro de venta, cálculo automático, exportación Excel .xlsx, navegación móvil y administración).

### Paso 7: Revisar respaldos y estrategia de recuperación
- Verificar que el respaldo diario esté completado y generar un dump pre-migración inmediato de la base de datos de producción.
- Validar que el archivo de respaldo exista, esté completo y sea recuperable.

### Paso 8: Preparar el plan de despliegue
- Redactar la secuencia exacta de comandos a ejecutar en el servidor:
  1. Activación de modo mantenimiento: `php artisan down --secret="..."`
  2. Actualización de código: `git pull origin main` (rama aprobada)
  3. Instalación de dependencias optimizadas: `composer install --no-dev --optimize-autoloader`
  4. Ejecución de migraciones: `php artisan migrate --force`
  5. Optimización de cachés: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
  6. Desactivación de modo mantenimiento: `php artisan up`
- Definir la ventana horaria de menor impacto para el fundo (ej. fuera de horario de pesaje y despacho).

### Paso 9: Solicitar autorización humana formal
- Presentar el plan al propietario del proyecto detallando: versión, cambios incluidos, tiempo estimado, plan de rollback y confirmación del respaldo.
- Obtener el visto bueno explícito antes de intervenir producción.

### Paso 10: Verificar funcionamiento posterior (Smoke Testing)
- Realizar pruebas de sanidad inmediatamente después del despliegue:
  - Verificar que el sitio cargue con código HTTP 200 y certificado SSL válido.
  - Iniciar sesión con un usuario de prueba de producción.
  - Consultar el panel de control y verificar que los totales históricos se mantengan exactos.
  - Probar la exportación de reportes a Excel (.xlsx).
  - Inspeccionar los logs del servidor (`storage/logs/laravel.log`) para descartar advertencias o excepciones silenciosas.

---

## Criterios de Aceptación
- Los 10 pasos fueron seguidos y documentados secuencialmente.
- No se produjeron interrupciones inesperadas ni pérdidas de datos.
- El hito fue registrado en `docs/00-gestion/ESTADO_PROYECTO.md` y `REGISTRO_CAMBIOS.md`.
