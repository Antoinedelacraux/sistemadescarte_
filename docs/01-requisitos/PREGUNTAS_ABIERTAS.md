# Preguntas Abiertas y Ambigüedades

**Estado: TODAS LAS PREGUNTAS RESUELTAS Y REGLAS DE NEGOCIO EN PRODUCCIÓN.**

El propietario y los analistas del fundo aclararon exitosamente las reglas operativas aplicadas en el sistema:
1. **Roles y Permisos:** 4 roles confirmados (`Administrador`, `Individual`, `General`, `Analista`).
2. **Edición/Anulación:** Ciclo en 2 pasos: primero Anulación (descuenta de totales manteniendo trazabilidad) y eliminación física protegida.
3. **Tipos de Cosecha Nacional:** Restringida exclusivamente a 2 opciones: *Racimos* y *Granos*. (Campo admite *Racimos*, *Racimos con plaga* y *Granos*; Packing admite *Racimos* y *Granos*).
4. **Moneda y Decimales:** Soles (S/), 2 decimales exactos contables, sin redondeo artificial.
5. **Relación Lote/Cuartel:** 34 lotes oficiales precargados en catálogo por fundo (AGRITAC: 18, PROCOM: 9, EL NEGRO: 7). Cuartel obligatorio en Cosecha Nacional y con catálogo dinámico inteligente.
6. **Modo Offline:** PWA y caché con Service Worker aprobados.
7. **Formato de Exportación:** Hojas de cálculo binarias nativas `.xlsx` de Microsoft Excel con tipos numéricos contables y RUC protegido como texto.
8. **Fundos Oficiales:** Diferenciación de nombres de fundo operativos (`AGRITAC`, `PROCOM`, `EL NEGRO`) y razones sociales completas.

No existen bloqueos funcionales pendientes.
