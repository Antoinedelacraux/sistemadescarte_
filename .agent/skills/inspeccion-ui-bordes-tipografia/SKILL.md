---
name: inspeccion-ui-bordes-tipografia
description: "Audita y corrige colisiones de bordes, curvaturas de tarjetas, clipping, paddings asfixiados y escalas tipográficas adaptativas en vistas web y móviles."
---

# Skill: Inspección de UI, Bordes y Tipografía

## Propósito
Esta skill previene y erradica errores visuales frecuentes en componentes web donde el texto choca con esquinas redondeadas (`border-radius`), sufre clipping por `overflow: hidden`, o presenta paddings insuficientes y tamaños de fuente desproporcionados en distintas resoluciones.

## Procedimiento de Auditoría Visual

### 1. Regla de Esquinas y Curvaturas (Border-Radius vs Padding)
- **Problema común:** Asignar `padding: 0` a un contenedor `.card` con `border-radius` y no darle padding interior al `.card-header`. Esto hace que el título se estrelle contra la curva de la esquina superior izquierda.
- **Solución obligatoria:**
  - Todo contenedor de cabecera (`.card-header`, `.section-header-box`) que anteceda a una tabla o contenido debe tener un padding explícito generoso:
    ```css
    padding: clamp(1rem, 3vw, 1.25rem) clamp(1.125rem, 3.5vw, 1.5rem);
    ```
  - El padding lateral y superior NUNCA debe ser inferior al radio de curvatura del contenedor (ej. si `border-radius: 16px`, el padding mínimo debe ser de `18px` a `24px`).

### 2. Jerarquía Tipográfica y Adaptabilidad (`clamp()`)
- No usar tamaños estáticos fijos en px que se desborden o rompan en pantallas móviles pequeñas (< 400px).
- Escala recomendada:
  - Títulos de tarjeta/sección: `font-size: clamp(1rem, 3.2vw, 1.1875rem);` con `font-weight: 700; line-height: 1.3;`.
  - Subtítulos explicativos: `font-size: clamp(0.72rem, 2.5vw, 0.8125rem);` con `color: var(--txt-muted); margin-top: 0.25rem;`.
  - Celdas de tablas: `font-size: clamp(0.75rem, 2.6vw, 0.8125rem);` con `padding: 0.625rem 0.875rem;`.

### 3. Envolventes de Tabla (`.table-wrapper`)
- El wrapper de la tabla debe llevar `overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;`.
- Para evitar que los bordes inferiores de la última fila se recorten bruscamente, la tabla debe usar `border-spacing: 0;` y el contenedor debe mantener el radio de esquina inferior si aplica.

### 4. Checklist de Validación
- [ ] ¿El texto del título tiene al menos 18px de respiro respecto al borde curvado?
- [ ] ¿El subtítulo está alineado correctamente con el título sin márgenes negativos?
- [ ] ¿En un viewport móvil de 360px de ancho el título se lee sin solaparse ni tocar bordes?
- [ ] ¿Los botones o controles dentro del header tienen suficiente espacio y no colisionan con el título?
