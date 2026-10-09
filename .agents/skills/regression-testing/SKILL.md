---
name: regression-testing
description: "Comprueba que nuevas modificaciones, migraciones o refactorizaciones no alteren funciones existentes ni comprometan la disponibilidad de registros históricos."
---

# Skill: Regression Testing (Pruebas de Regresión y Persistencia)

## Propósito
Verificar sistemáticamente que cualquier incorporación de código, corrección de errores, refactorización o actualización estructural no degrade, rompa ni altere las funcionalidades existentes ni la accesibilidad de los datos históricos del fundo.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Tras modificar cualquier modelo, controlador, servicio, vista o regla de validación.
- Tras la creación de nuevas migraciones que alteren tablas con dependencias activas.
- Al refactorizar lógica de cálculo de precios, kilogramos, exportaciones o permisos.
- Como paso obligatorio previo a cualquier entrega, commit de release o despliegue.

---

## Batería de Pruebas de Regresión Obligatorias

### 1. Disponibilidad y Persistencia de Registros Históricos
- Ejecutar pruebas automatizadas que demuestren que los registros creados con esquemas o versiones anteriores siguen siendo consultables, editables y exportables.
- **Caso de Prueba Clave:**
  ```php
  public function test_existing_historical_sales_remain_intact_after_updates(): void
  {
      // 1. Arrange: Registro histórico existente
      $ventaPrevia = VentaDescarte::factory()->create([
          'precio' => 2.50,
          'kilogramos' => 1000.00,
          'valor_total' => 2500.00,
          'estado' => 'activo',
      ]);

      // 2. Act: Consultar a través de las rutas actuales
      $response = $this->actingAs($this->admin)->get(route('ventas.show', $ventaPrevia));

      // 3. Assert: Verificar que no hay errores 500 y que los datos coinciden exactamente
      $response->assertOk();
      $response->assertSee('2,500.00');
  }
  ```

### 2. No-Regresión en Cálculos Agrícolas y Reglas de Negocio
- Comprobar que el cálculo en tiempo real y persistido continúe siendo exacto: `precio * kilogramos = valor_total`.
- Verificar que las reglas específicas del fundo no se hayan relajado:
  - Cosecha Nacional restringida exclusivamente a Racimos y Granos con cuartel obligatorio.
  - Campo y Packing con cuartel opcional.
  - Ciclo de vida en dos pasos: un registro activo no puede eliminarse directamente; debe ser anulado primero.

### 3. Aislamiento Multitenant (FundoScope) y RBAC
- Comprobar que los usuarios con rol `Individual` solo tengan visibilidad y acceso a los registros del fundo que tienen asignado en `fundo_user`.
- Verificar que el usuario no pueda forzar el acceso cambiando IDs en las rutas HTTP.

### 4. Generación y Descarga de Reportes
- Verificar que la exportación a Excel (.xlsx) genere un paquete binario válido que inicie con los bytes mágicos `PK\x03\x04` y contenga todas las filas y totales esperados.

---

## Procedimiento de Ejecución
1. Correr la suite de pruebas automatizadas con reporte detallado:
   ```bash
   php artisan test --stop-on-failure
   ```
2. Si se produjo una modificación en un módulo específico, correr la suite completa de ese módulo más las pruebas de integración globales:
   ```bash
   php artisan test --filter=VentaDescarteTest
   php artisan test --filter=ReporteTest
   php artisan test --filter=AdminTest
   ```
3. Documentar en el reporte de entrega:
   - Número de pruebas ejecutadas y aserciones comprobadas.
   - Estado de paso (100% verde).
   - Cualquier caso límite identificado durante la ejecución.

---

## Criterios de Aceptación
- Cero pruebas rotas o ignoradas (*zero skipped/failing tests*).
- Confirmación explícita de que los registros históricos se mantienen íntegros y accesibles.
- Evidencia de ejecución con comando y resultado incluido en la documentación del cambio.
