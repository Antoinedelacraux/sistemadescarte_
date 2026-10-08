# Análisis inicial de requisitos — Sistema de Descarte (v1)

**Estado:** Borrador para validación; no representa aprobación de decisiones abiertas.
**Fuente primaria:** `Requerimientos para Sistema Descart.txt`, provisto por el solicitante.
**Infraestructura conocida:** Ferozo / DonWeb; capturas con Linux, Apache 2.4.68, PHP 8.4 FPM, MySQL 8.0.44 y aplicaciones existentes. Capacidad/SSH/document root no comprobados.

## 1. Objetivo confirmado
Aplicación web para personal de fundo que registra información de descarte y venta de descarte. Debe ser modificable, escalable, adaptable a celular y permitir exportar datos a Excel.

## 2. Roles confirmados
- **Administrador:** crea cuentas, asigna roles y fundos, y consulta los cambios del sistema.
- **Individual:** registra, consulta y exporta información de fundos asignados; el texto usa singular en algunos pasajes, debe aclararse si podrá tener varios.
- **General:** puede registrar y consultar información de su fundo y otros fundos. No se especifica con precisión el alcance para **editar, borrar o registrar en fundos ajenos**.

### Matriz provisional de permisos (PROPUESTA, no decisión)
| Operación | Administrador | Individual | General |
|---|---|---|---|
| Gestionar usuarios/roles/asignaciones | Sí | No | No |
| Gestionar catálogos | Sí | No | No |
| Crear venta | Pendiente | Solo fundo autorizado | Fundo(s) autorizado(s): definir |
| Consultar ventas | Sí (supervisión): confirmar | Fundo asignado | Todos los fundos: según texto |
| Editar/anular venta | Pendiente | Pendiente | Pendiente |
| Exportar ventas | Pendiente | Fundo asignado | Mismo alcance que consulta: confirmar |
| Ver auditoría | Sí | No | Pendiente |

**Regla de seguridad:** permisos y filtros de fundo se comprueban en backend en cada lectura/escritura/exportación. La UI no es barrera de seguridad.

## 3. Formulario confirmado
| Campo | Condición |
|---|---|
| Fecha de producción | Obligatorio, fecha del sistema por defecto, editable |
| Lote | Obligatorio, del fundo correspondiente |
| Motivo | Obligatorio: Campo, Packing, Cosecha Nacional |
| Tipo de descarte | Obligatorio; depende del motivo |
| Cuartel | Obligatorio para Cosecha Nacional, opcional para los demás |
| Precio | Obligatorio |
| Kilogramos totales | Obligatorio |
| Cantidad de jabas | Opcional |
| Peso de jaba | Opcional |
| Valor de la venta | Automático = Precio × kilogramos totales |
| Brevete, RUC, placa, nombre conductor, viaje, observación | Opcionales |
| Usuario, fecha y hora de operación | Autoregistrados para trazabilidad |

**Opciones por motivo según fuente literal:**
- Campo: `grano`, `racimos`, `racimos con plaga`.
- Packing: `Granos`, `Racimos`.
- Cosecha Nacional: `Racimos y granos` (requiere aclarar si es UNA opción compuesta o DOS opciones).

**Propuestas técnicas:** Validaciones en frontend y backend; `DECIMAL` para kg/precio; fecha de producción diferenciada de created_at; no confiar en totales enviados por el navegador. Se deben aclarar moneda, unidad de precio, precisión y redondeo.

## 4. Requisitos UI/UX confirmados
- Responsive con uso prioritario en Android; funcionamiento en iPhone y escritorio.
- Acceso directo instalable como PWA.
- Permitir ocultar barra lateral/navegación.
- Tablas sin desborde visual de contenido por celda.
- Exportación a Excel con selección de columnas.
- Ocultar USUARIO y HORA tanto en tabla regular como en Excel (no omitirlos del registro de auditoría).
- Conectividad irregular en zonas del fundo; el documento NO confirma explícitamente registro sin Internet ni política de sincronización. Decisión pendiente.

## 5. Módulos recomendados (PROPUESTA)
1. Autenticación y sesiones.
2. Usuarios, roles y permisos.
3. Fundos y asignación de usuarios a fundos.
4. Lotes y cuarteles.
5. Motivos y tipos de descarte configurables.
6. Ventas de descarte.
7. Consultas, filtros y exportación Excel.
8. Auditoría de acciones.
9. PWA y experiencia responsive.

Se pospone crear un módulo separado para choferes/vehículos hasta confirmar si se reutilizan como catálogos; por ahora pueden ser campos opcionales de la operación.

## 6. Decisiones pendientes ordenadas por impacto
- **P1 Offline:** ¿Registrar sin Internet y sincronizar después, o exigir conexión al guardar? La PWA instalable por sí misma no resuelve el registro offline.
- **P1 Permisos:** ¿General puede crear/modificar/exportar en todos los fundos o solo verlos? ¿Quién puede editar y anular registros? ¿Administrador también puede registrar?
- **P1 Descarte tipo:** ¿`Racimos y granos` de Cosecha Nacional es una opción única o dos opciones?
- **P1 Dinero:** ¿Precio en soles por kilogramo? ¿Se permiten decimales y cómo se redondea?
- **P1 VPS:** ¿Ferozo permite apuntar subdominio a `public/` de Laravel y usar sus extensiones PHP? ¿Accesos/dependencias? No tocar servidor de producción.
- **P2 Relación territorial:** ¿Cuarteles pertenecen directamente a fundos o a lotes? ¿Puede un lote tener varios cuarteles?
- **P2 Registro:** ¿Se puede registrar una fecha de producción futura o pasada? ¿Quién puede cambiar registros antiguos?
- **P2 Datos opcionales:** ¿Viaje es texto, consecutivo o catálogo? ¿RUC es del comprador/transportista? ¿Precio varía por kilo y moneda?
- **P2 Reportes:** Filtros y orden de columnas; auditoría distinta de tabla/Excel funcional.

## 7. Casos de prueba de aceptación iniciales
1. Cada motivo muestra exclusivamente sus tipos permitidos.
2. Cosecha Nacional no guarda sin cuartel; Campo/Packing sí lo permiten.
3. Se impide elegir lote/cuartel ajeno al fundo asociado.
4. Valor de venta se calcula en el servidor con precisión decimal.
5. Usuario Individual no puede consultar/exportar registros de otro fundo manipulando URL o petición.
6. General solamente realiza operaciones expresamente permitidas.
7. Se registran quién creó/modificó una venta y cuándo.
8. El Excel contiene columnas seleccionadas, exceptuando usuario/hora en la exportación operativa.
9. En celular no hay controles inaccesibles ni celdas que desborden.
10. Una pérdida de conexión nunca se presenta falsamente como un registro guardado.

## 8. Alcance de la primera entrega
La primera entrega funcional debe cubrir login, permisos, fundos/lotes, motivos/tipos y el registro de venta de descarte; después filtros, Excel, auditoría y PWA. No inventar flujos de negocio faltantes; documentar supuestos y esperar validación de decisiones P1.
