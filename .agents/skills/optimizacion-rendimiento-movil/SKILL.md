---
name: optimizacion-rendimiento-movil
description: "Identifica cuellos de botella de rendimiento, lag y caídas de frames en interfaces móviles y web. Optimiza animaciones a 60 FPS con aceleración por GPU, transiciones limpias y eliminación de repaints costosos."
---

# Skill: Optimización de Rendimiento y Fluidez Móvil

## Propósito
Esta skill se activa para auditar y optimizar cualquier elemento de interfaz (como sidebars, drawers, modales, menús o tablas deslizantes) que presente lentitud, tirones (jank) o apertura tardía en navegadores móviles (iOS Safari, Chrome Android, WebViews).

## Principios Técnicos de Fluidez a 60 FPS

### 1. Aceleración por Hardware (GPU Compositing)
- **Regla:** NUNCA animar propiedades que disparen *Layout* o *Paint* (`width`, `height`, `left`, `right`, `top`, `bottom`, `margin`, `padding`).
- **Uso obligatorio:** Animar exclusivamente `transform` y `opacity`.
- Para forzar la capa compuesta en GPU en elementos deslizantes móviles:
  ```css
  transform: translate3d(-100%, 0, 0);
  will-change: transform;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  ```
  Al abrirse:
  ```css
  transform: translate3d(0, 0, 0);
  ```

### 2. Erradicación de `backdrop-filter: blur()` en Móviles
- **Causa común de lentitud extrema:** El filtro `backdrop-filter: blur(Npx)` obliga a la GPU de un smartphone a realizar convolución gaussiana en tiempo real de toda la pantalla en cada frame durante la animación de entrada. Esto colapsa el framerate a 15-20 FPS en dispositivos de gama media/baja.
- **Solución obligatoria:** Reemplazar el blur en overlays animados por un color plano semitransparente con transición pura de opacidad:
  ```css
  .sidebar-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.22s ease;
      z-index: 45;
  }
  .sidebar-overlay.active {
      opacity: 1;
      pointer-events: auto;
  }
  ```

### 3. Curvas de Tiempo (Easing) Nativas
- La duración de entrada de un menú móvil debe estar entre **200ms y 240ms** (no más de 250ms).
- Usar la curva de aceleración estándar de apps nativas modernas (Deceleration curve):
  ```css
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  ```
  Esto hace que el sidebar inicie velozmente al contacto y frene con suavidad natural.

### 4. Checklist de Detección y Optimización
- [ ] ¿El elemento móvil anima `width` o `left` en lugar de `transform: translate3d`? -> Convertir a `translate3d`.
- [ ] ¿El overlay utiliza `backdrop-filter: blur(...)`? -> Retirar y sustituir por `rgba()` con `opacity`.
- [ ] ¿Tiene `will-change: transform` para preparar la capa de GPU con anticipación?
- [ ] ¿La duración supera los 250ms? -> Reducir a 200-240ms con `cubic-bezier(0.16, 1, 0.3, 1)`.
- [ ] ¿Los eventos táctiles y clicks tienen respuesta inmediata sin delays de 300ms? (Asegurar `<meta name="viewport" content="width=device-width, initial-scale=1">`).
