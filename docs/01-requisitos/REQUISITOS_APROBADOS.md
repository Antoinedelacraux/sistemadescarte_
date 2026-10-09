# Requisitos Aprobados del Sistema Web del Fundo

**Estado: APROBADOS E IMPLEMENTADOS AL 100% (Fases 1 a 6).**

## 1. Alcance y Objetivos del Negocio
Sistema web para la gestión agrícola integral enfocada en el registro de pesaje y venta de descarte en fundos, consolidando información en tiempo real, garantizando aislamiento por sede y proveyendo reportería analítica y exportación personalizada.

## 2. Actores y Matriz de Roles (RBAC)
- **Administrador:** Acceso irrestricto a todos los fundos, usuarios, ventas, reportes y configuración.
- **General:** Visualiza y administra únicamente los fundos asignados a su cuenta.
- **Individual:** Operador de campo que registra ventas exclusivamente en su fundo asignado.
- **Analista:** Acceso transversal de solo lectura a todos los fundos para reportería y exportación a Excel.

## 3. Módulos y Reglas de Negocio Aprobadas
1. **Multi-tenancy (`FundoScope`):**
   - Filtrado automático por política de acceso a nivel de ORM Eloquent.
   - Prevención de fugas de información entre fundos.
2. **Catálogos Dinámicos:**
   - Lotes predefinidos por fundo (ej. Lote 1, Lote 2, Lote 3).
   - Cuarteles asociados dinámicamente al lote seleccionado.
3. **Venta de Descarte:**
   - Motivos aprobados: `Campo`, `Packing` y `Cosecha Nacional`.
   - Cosecha Nacional exige obligatoriamente Cuartel y solo admite dos Tipos de Descarte: `Racimos` y `Granos`. Campo admite `Racimos`, `Racimos con plaga` y `Granos`. Packing admite `Racimos` y `Granos`.
   - Cálculo del valor: `Precio en Soles × Kilogramos`, con 2 decimales exactos.
   - Auditoría estricta: `created_by`, `updated_by` y marcas de tiempo registradas en cada transacción.
4. **Historial y Filtros:**
   - Listado ordenado cronológicamente con paginación.
   - Filtros por fundo, motivo y rango de fechas.
5. **Reportes y Exportación Excel:**
   - Métricas agregadas: Kg acumulados, valor de venta total y cantidad de pesajes.
   - Distribución por motivo y distribución por fundo.
   - Exportación en formato compatible con Excel (.csv UTF-8 con BOM y separador `;`).
   - Selector dinámico de columnas para descarga a la medida del usuario.
6. **PWA y Soporte Móvil:**
   - Aplicación Web Progresiva instalable con Service Worker para funcionamiento en campo.
   - Interfaz responsive con tipografía fluida `clamp()` y tablas con scroll horizontal seguro.
7. **Administración:**
   - Registro de nuevas sedes agrícolas y conmutación de estado activo/inactivo.
   - Creación de usuarios con roles y asignación multi-fundo.

## 4. Historial de Aprobaciones
- **2026-10-08:** Aprobado documento de requisitos original y resolución de preguntas abiertas (`PREGUNTAS_ABIERTAS.md`).
- **2026-10-08:** Aprobada arquitectura web Laravel 12 + Blade + Design Tokens y estrategia PWA.
- **2026-10-08:** Aprobada implementación de los módulos y suite de 29 pruebas PHPUnit.

