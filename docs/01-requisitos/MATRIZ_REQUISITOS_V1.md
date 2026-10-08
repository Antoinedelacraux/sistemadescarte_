# Matriz de Requisitos Funcionales y No Funcionales (V1)

**Estado:** Análisis inicial (En espera de validación de preguntas abiertas).
**Fuente:** `Requerimientos para Sistema Descart.txt` y decisión Offline-First.

## Requisitos Funcionales (RF)
| ID | Nombre | Descripción | Criterios de Aceptación |
|---|---|---|---|
| RF-01 | Login y Autenticación | El sistema debe permitir el ingreso a usuarios registrados. | 1. Credenciales válidas permiten acceso.<br>2. Credenciales inválidas muestran error. |
| RF-02 | Gestión de Catálogos | El administrador debe poder configurar fundos, lotes, cuarteles, usuarios y roles. | 1. Solo Administrador accede.<br>2. Se asocian lotes y cuarteles a fundos. |
| RF-03 | Registro Venta Descarte | Registrar nueva venta con Fecha, Lote, Motivo, Tipo, Cuartel (condicional), Precio, KG, Jabas (opcional), Peso jaba (opcional) y datos de transporte (opcionales). | 1. Se calcula automáticamente: Precio * KG.<br>2. Cuartel es obligatorio si Motivo es "Cosecha Nacional".<br>3. Solo se muestran los tipos de descarte correspondientes al motivo seleccionado. |
| RF-04 | Restricción por Fundo | Los registros (creación y consulta) se limitan al fundo asignado del usuario. | 1. Usuario Individual solo ve lotes y registros de su fundo.<br>2. Usuario General ve registros de todos los fundos (pendiente confirmar si puede registrar en todos). |
| RF-05 | Exportación a Excel | Permite descargar la tabla de registros en formato Excel, seleccionando las columnas a descargar. | 1. Las columnas de HORA y USUARIO (auditoría) no se incluyen en la exportación.<br>2. El archivo descarga correctamente en celular y PC. |
| RF-06 | Registro Offline-First | Permitir el registro sin red y sincronización posterior. | Ver `ADR-001-OFFLINE_FIRST.md`. |
| RF-07 | Auditoría Transparente | El sistema guardará el Usuario, Fecha y Hora del registro de forma oculta en la vista regular. | 1. Ningún usuario no administrador puede ver la hora y usuario en la tabla principal. |

## Requisitos No Funcionales (RNF)
| ID | Nombre | Descripción |
|---|---|---|
| RNF-01 | Diseño Responsive | Debe adaptarse a celulares (Android e iPhone) para uso en campo, y a escritorio. |
| RNF-02 | PWA Instalable | Se debe poder descargar un acceso directo a la web (PWA) en celulares y escritorio. |
| RNF-03 | Tablas Contenidas | Las tablas de registros no deben desbordar el texto de sus celdas ("que los registros que se guarden no se salgan de su celda"). |
| RNF-04 | Barra Colapsable | La navegación (sidebar/menú) debe poder ocultarse para priorizar la visualización de datos. |
| RNF-05 | Escalabilidad | Código estructurado para futuros módulos y mantenibilidad ("enfocado en cambios a futuro"). |

## Matriz de Roles y Permisos (Confirmados y Propuestos)
| Rol | Operación | Alcance Confirmado en TXT |
|---|---|---|
| Administrador | Cuentas, Roles, Fundos, Ver cambios | Todo el sistema. |
| Individual | Registrar, Guardar, Descargar Excel | Limitado estrictamente al fundo asignado. |
| General | Registrar, Ver registros | Ver registros de su fundo y otros fundos (Falta aclarar si puede registrar en otros fundos o editar). |

## Casos de Uso Principales
1. **CU-01 Registrar Descarte:** Usuario entra a la app (online/offline), llena el formulario (validando que Cuartel es requerido para Cosecha Nacional), el valor se calcula automático, guarda y sincroniza.
2. **CU-02 Exportar Reporte:** Usuario General o Individual filtra la tabla, selecciona columnas (sin Hora/Usuario) y descarga el archivo XLSX.
3. **CU-03 Administrar Accesos:** Administrador crea cuenta a Individual, le asocia un Fundo y le activa el acceso.
