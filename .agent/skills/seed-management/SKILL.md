---
name: seed-management
description: "Administra datos iniciales (seeds de configuración idempotentes) y datos ficticios (seeds de desarrollo), garantizando que los datos de prueba nunca se ejecuten en producción."
---

# Skill: Seed Management (Gestión Segura de Datos Iniciales y Pruebas)

## Propósito
Separar rigurosamente los datos esenciales de configuración requeridos para el funcionamiento del sistema de aquellos datos simulados o ficticios utilizados únicamente durante el desarrollo y pruebas locales, garantizando que ninguna información de prueba contamine la base de datos de producción.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Al crear o modificar seeders en Laravel (`database/seeders/`).
- Al inicializar catálogos indispensables (roles, permisos, categorías de descarte, fundos base).
- Al preparar datasets de prueba para desarrollo local, pruebas unitarias o de carga.
- Antes de desplegar una versión o ejecutar comandos de siembra en cualquier entorno.

---

## Dos Categorías de Seeds Estrictamente Independientes

### 1. SEEDS DE CONFIGURACIÓN (Production-Safe & Idempotentes)
- **Alcance:**
  - Roles del sistema (`Admin`, `Analista`, `Individual`, `Visualizador`).
  - Permisos y asignaciones esenciales.
  - Catálogos indispensables de la operación agrícola (tipos de motivo: Cosecha Nacional, Campo, Packing; tipos de descarte aprobados: Racimos, Granos, Racimos con plaga).
  - Parámetros de configuración del sistema.
- **Requisito de Idempotencia Absoluta:**
  - Deben poder ejecutarse múltiples veces consecutivas sin provocar duplicados, colisiones de clave primaria o violaciones de restricción única.
  - Utilizar obligatoriamente constructores como `firstOrCreate()`, `updateOrCreate()`, o cláusulas `upsert()` con claves naturales bien definidas.
  ```php
  // Ejemplo correcto de seed de configuración idempotente
  Role::firstOrCreate(
      ['name' => Role::ADMIN],
      ['display_name' => 'Administrador del Sistema']
  );
  ```
- **Aptitud de Entorno:** Autorizados para ejecutarse en Desarrollo, Staging y Producción.

---

### 2. SEEDS DE DESARROLLO (Mocks y Pruebas - Exclusivos de Dev/Testing)
- **Alcance:**
  - Productores ficticios, clientes inventados y conductores simulados.
  - Registros de ventas de prueba con pesajes aleatorios.
  - Lotes de prueba temporales.
- **Prohibición Absoluta en Producción:**
  - Jamás deben ejecutarse en entornos de producción ni en staging con datos de clientes.
- **Guarda de Seguridad Programática Obligatoria:**
  Todo seeder de prueba debe comenzar con una verificación estricta de entorno que aborte inmediatamente la ejecución si se detecta producción:
  ```php
  if (app()->environment('production')) {
      throw new \RuntimeException("BLOQUEO DE SEGURIDAD: Los datos de prueba no pueden ejecutarse en PRODUCCIÓN.");
  }
  ```
- **Protección de Credenciales:**
  - No incluir contraseñas reales, tokens de acceso ni datos personales reales en las semillas.
  - Los usuarios de prueba locales deben utilizar hashes estándar y públicos conocidos únicamente para desarrollo (ej. contraseñas de entorno local documentadas).

---

## Procedimiento de Inicialización del Administrador en Producción
- Prohibido sembrar usuarios administradores con contraseñas estáticas en `DatabaseSeeder.php` para entornos productivos.
- En producción, el primer usuario administrador debe generarse mediante un comando CLI protegido e interactivo:
  ```bash
  php artisan app:crear-admin
  ```
  Este comando solicita por consola interactiva:
  1. Nombre completo del responsable.
  2. Correo institucional.
  3. Contraseña robusta (o genera una temporal de alta entropía que debe cambiarse al primer login).
  4. Asignación inmediata del rol `Admin`.

---

## Protocolo de Verificación
1. Ejecutar el seeder dos veces seguidas en desarrollo y comprobar que el conteo de registros no se duplique:
   ```bash
   php artisan db:seed --class=ConfiguracionInicialSeeder
   php artisan db:seed --class=ConfiguracionInicialSeeder # Debe terminar sin errores ni registros duplicados
   ```
2. Verificar que las pruebas de integración que dependan de datos de prueba utilicen `RefreshDatabase` en SQLite en memoria o bases de datos de test temporales aisladas.

---

## Criterios de Aceptación
- Todo seed de catálogo es 100% idempotente.
- Todo seed de prueba contiene la guarda de rechazo para entorno de producción.
- No existen contraseñas reales ni datos personales de trabajadores en el repositorio.
