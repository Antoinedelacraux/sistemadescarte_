# Guía de Diseño UI/UX — Sistema Fundo

## 1. Principios de Diseño
- **Mobile-First para Trabajo en Campo:** Diseñado específicamente para que los operadores en campo (usuario Individual) puedan registrar ventas ágilmente desde teléfonos Android o iPhone con una sola mano.
- **Claridad Agrícola:** Paleta inspirada en tonalidades esmeralda y tierra (#16a34a, #15803d, #0f1f14), con alto contraste y jerarquía tipográfica limpia (Inter).
- **Control de Espacio:** Sidebar colapsable y completamente deslizante hacia afuera (`transform: translateX(-100%)`) que maximiza el espacio útil para tablas de datos y formularios en pantallas medianas y grandes.
- **Transparencia de Conectividad:** Indicador de estado de red (En línea / Sin conexión) permanentemente visible en la barra superior.

## 2. Tokens de Diseño CSS
- **Primarios:** `--clr-primary-50` (#f0fdf4) a `--clr-primary-900` (#14532d).
- **Superficies:** Fondos limpios `--clr-surface-50` (#f8faf9) y tarjetas `--clr-surface-0` (#ffffff).
- **Tipografía:** `Inter, system-ui, sans-serif`.
- **Transiciones:** `--transition-slow` (320ms ease) sincronizada entre sidebar y área de contenido principal (`.main-wrapper`).

## 3. Componentes Implementados
1. **Layout Shell:** Sidebar con navegación por rol, indicador de fundo activo y menú de usuario.
2. **Sidebar Deslizante:** Ocultamiento total y reaparición con deslizamiento lateral suave (`transform: translateX(-100%)`), recordando la preferencia en `localStorage`.
3. **Login Split:** Hero visual institucional a la izquierda y tarjeta de formulario a la derecha con botones rápidos de cuentas de prueba.
4. **Dashboard:** Métricas clave en tarjetas con microinteracciones y panel de perfil de acceso.
5. **Formulario de Registro de Campo:** Entradas táctiles de al menos 44px de altura, selector dinámico según motivo y cálculo en vivo del valor de venta.
6. **Módulo de Reportes:** Tablas de distribución y selector dinámico de columnas para exportación a Excel.

## 4. Adaptabilidad y Responsive Design para Celulares
- **Tipografía Proporcional (`clamp()`):**
  - Títulos: `clamp(1.2rem, 5vw, 1.5rem)`
  - Subtítulos: `clamp(0.72rem, 2.6vw, 0.8125rem)`
  - Valores numéricos en KPI: `clamp(1.25rem, 5vw, 1.85rem)` con elipsis o salto limpio sin solaparse.
- **Tablas con Scroll Horizontal Seguro:**
  - Todo elemento de tabla se encapsula en `.table-wrapper` con `-webkit-overflow-scrolling: touch` y un `min-width` (500px - 620px) para que las columnas nunca se aplasten ni se monten entre sí.
- **Estructura y Orden en Botones:**
  - Se eliminaron agrupaciones flotantes arbitrarias; en móviles menores a 640px los botones de acción se reorganizan en cuadrículas simétricas (`1fr 1fr` o `repeat(3, 1fr)`) que abarcan el ancho completo.
- **Prevención de Zoom Automático en iOS:**
  - Los campos `<input>`, `<select>` y `<textarea>` establecen `font-size: 16px` en vista móvil para evitar que Safari fuerce zoom al enfocar campos.

