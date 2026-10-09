# Wireframes y Diseño Funcional (UI/UX)

El sistema emplea plantillas Blade, JavaScript Vanilla y CSS puro con sistema de Design Tokens (sin dependencias de Tailwind ni Alpine), optimizado para renderizado ultra rápido, PWA y adaptabilidad total en smartphones.

## 1. Diseño Base (Layout Shell)
- **Barra Superior (Header):**
  - Izquierda: Botón de Hamburguesa (para deslizar/ocultar el menú lateral a pantalla completa).
  - Centro: Título fluido del módulo actual con elipsis protectora.
  - Derecha: Indicador de estado de red (`Online` / `Offline`) con reducción compacta en móviles angostos, badge de rol y avatar del usuario.
- **Menú Lateral (Sidebar Deslizante):**
  - Oculto por defecto en móviles; colapsable o deslizante en escritorio con persistencia en `localStorage`.
  - Opciones filtradas por rol: Panel de Control, Registrar Venta, Historial de Ventas, Reportes, Exportar Excel, Fundos y Usuarios.

## 2. Pantalla: Registro de Venta (PWA Mobile First)
```text
======================================
[=]   Venta de Descarte   [🟢 Online]
======================================
Fundo: Fundo Norte (Bloqueado por rol)

[Fecha Producción]  [ 08/10/2026 📅 ]
[Lote]              [ Lote 1      ▼ ]
[Motivo]            [ Cosecha Nac ▼ ]

-- Aparecen dinámicamente --
[Cuartel]           [ ______________ ] (Manual, req. Cosecha Nac.)
[Tipo Descarte]     [ Racimos     ▼ ] (Racimos, Plaga, Granos)

[Precio (Soles)]    [ _________ 0.00 ]
[Kilos Totales]     [ _________ 0.00 ]
--------------------------------------
VALOR DE LA VENTA:       S/ 0.00
--------------------------------------
[Datos Opcionales (Transporte)   ▼]
 (Acordeón colapsado para no estorbar)
 
[      GUARDAR REGISTRO VENTA      ]
======================================
```
**Comportamiento Offline:** Si está offline, el botón dirá `GUARDAR LOCALMENTE`. Al guardar, pasará a una pestaña de "Pendientes de Sincronizar".

## 3. Pantalla: Historial y Sincronización
```text
======================================
[=]      Historial de Ventas      
======================================
[ Filtro: Fecha, Lote, Motivo      ]

[ PENDIENTES (2) ] [ SINCRONIZADAS ]

 🔴 Registro pendiente - 10:45 AM
    Lote 1 - Racimos - S/ 150.00
    [ REINTENTAR SINCRONIZACIÓN ]

======================================
```

## 4. Pantalla: Tabla Principal (Desktop / Analista)
```text
========================================================================
[=]  Consulta de Registros                                [Exportar EXCEL]
========================================================================
| Fecha Prod | Lote  | Motivo  | Tipo    | Kilos | Precio | Total    |
|------------|-------|---------|---------|-------|--------|----------|
| 08/10/2026 | L-01  | Packing | Granos  | 10.00 |  5.00  | S/ 50.00 |
| 08/10/2026 | L-02  | Campo   | Racimos | 20.00 |  3.00  | S/ 60.00 |
------------------------------------------------------------------------
* Tabla con desplazamiento horizontal nativo si faltan columnas. 
* "Hora" y "Usuario creador" no se ven aquí por defecto (se ven en un modal de "Ver detalles" o están ocultos según rol).
========================================================================
```

## 5. Decisiones UX
- **Validación Instantánea:** Los campos numéricos impiden letras. El total se calcula en tiempo real al escribir Precio o Kilos.
- **Alertas de Bloqueo:** Al intentar "Cerrar sesión" con registros en "PENDIENTES", un pop-up gigante en rojo obligará a escribir la palabra "BORRAR" si el usuario realmente quiere destruir los datos no subidos.
