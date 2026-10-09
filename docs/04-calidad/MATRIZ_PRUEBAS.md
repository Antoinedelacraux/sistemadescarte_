# Matriz de Pruebas y Control de Calidad (QA)

**Estado:** 29 Pruebas Automatizadas Ejecutadas y Aprobadas (111 aserciones).

## 1. Pruebas Automatizadas de Backend y Funcionalidades (PHPUnit)

| Archivo de Prueba | Escenario / Caso | Tipo | Esperado | Ejecutado | Resultado |
|---|---|---|---|---|---|
| `AutenticacionTest` | Usuario con credenciales correctas inicia sesión | Feature | Sesión iniciada, redirección a dashboard | Sí | Exitoso |
| `AutenticacionTest` | Credenciales incorrectas deniegan acceso | Feature | Error de validación y sesión nula | Sí | Exitoso |
| `AutenticacionTest` | Usuario marcado como inactivo es bloqueado | Feature | Acceso bloqueado con mensaje explicativo | Sí | Exitoso |
| `AutenticacionTest` | Cierre de sesión invalida la sesión | Feature | Sesión destruida y redirección a login | Sí | Exitoso |
| `FundoScopeTest` | Usuario Individual solo accede a su fundo | Feature | Consultas filtradas a su único fundo | Sí | Exitoso |
| `FundoScopeTest` | Usuario General accede a sus fundos asignados | Feature | Consultas filtradas a la lista de fundos | Sí | Exitoso |
| `FundoScopeTest` | Administrador accede a todos los fundos | Feature | Acceso transversal sin restricciones | Sí | Exitoso |
| `FundoScopeTest` | Analista accede a todos los fundos | Feature | Acceso transversal de lectura | Sí | Exitoso |
| `CatalogoLotesTest` | Carga de lotes por fundo vía API | Feature | JSON con array de lotes filtrados | Sí | Exitoso |
| `CatalogoLotesTest` | Fundo no asignado retorna denegado | Feature | 403 Forbidden o array vacío | Sí | Exitoso |
| `CatalogoLotesTest` | Catálogo de cuarteles por lote | Feature | Cuarteles vinculados al lote correcto | Sí | Exitoso |
| `CatalogoLotesTest` | Formato consistente de catálogos | Feature | Claves id, name, code estructuradas | Sí | Exitoso |
| `VentaDescarteTest` | Creación exitosa de venta con cálculo automático | Feature | `valor_venta = precio * kg` exacto | Sí | Exitoso |
| `VentaDescarteTest` | Validación requerida de campos base | Feature | Falla si falta fecha, precio o kg | Sí | Exitoso |
| `VentaDescarteTest` | Motivo Cosecha Nacional exige Cuartel y Tipo | Feature | Error de validación si faltan | Sí | Exitoso |
| `VentaDescarteTest` | Motivo Campo o Packing no exige Cuartel | Feature | Se guarda correctamente | Sí | Exitoso |
| `VentaDescarteTest` | Auditoría de creación registra `created_by` | Feature | ID de usuario autenticado persistido | Sí | Exitoso |
| `VentaDescarteTest` | Auditoría de modificación registra `updated_by` | Feature | ID del editor actualizado | Sí | Exitoso |
| `VentaDescarteTest` | Historial respeta filtros por fecha y motivo | Feature | Colección filtrada con precisión | Sí | Exitoso |
| `ReporteTest` | Consolidación de KPIs (Kg, Soles, Pesajes) | Feature | Cálculos matemáticos de agregación correctos | Sí | Exitoso |
| `ReporteTest` | Distribución por motivo y fundo | Feature | Agrupaciones consistentes | Sí | Exitoso |
| `ReporteTest` | Exportación CSV con codificación UTF-8 BOM | Feature | Archivo descargable con BOM y separador `;` | Sí | Exitoso |
| `ReporteTest` | Selector dinámico de columnas exportadas | Feature | Solo incluye las columnas marcadas | Sí | Exitoso |
| `AdminTest` | Creación de nuevos fundos (solo Admin) | Feature | Fundo persistido en base de datos | Sí | Exitoso |
| `AdminTest` | Conmutación de estado activo/inactivo de fundo | Feature | Estado invertido y guardado | Sí | Exitoso |
| `AdminTest` | Usuario no administrador no puede acceder a /admin | Feature | 403 Forbidden | Sí | Exitoso |
| `AdminTest` | Creación de usuario con rol y asignación de fundos | Feature | Usuario y relaciones creadas | Sí | Exitoso |
| `AdminTest` | Conmutación de usuario activo/inactivo | Feature | Inactivo bloqueado inmediatamente | Sí | Exitoso |
| `AdminTest` | Validación de contraseña mínima y correo único | Feature | Reglas de validación aplicadas | Sí | Exitoso |

## 2. Pruebas de Interfaz y Compatibilidad Móvil (Inspección)
- **Viewport Móvil (360px - 440px):** Tipografías `clamp()` sin desbordes, botones ordenados en cuadrículas simétricas y sin solapamiento.
- **Scroll Táctil en Tablas:** Celdas legibles con `table-wrapper` en dispositivos móviles sin deformar la tarjeta principal.
- **PWA e Instalación:** Manifiesto e iconos validados para Android, iPhone y Escritorio.

