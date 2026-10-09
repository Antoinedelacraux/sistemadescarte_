# Matriz de Requisitos Funcionales y No Funcionales (V1)

**Estado:** Análisis inicial (En espera de validación de preguntas abiertas).
**Fuente:** `Requerimientos para Sistema Descart.txt` y decisión Offline-First.

## Requisitos Funcionales (RF)
| ID | Nombre | Descripción | Criterios de Aceptación |
|---|---|---|---|
| RF-01 | Login y Autenticación | El sistema debe permitir el ingreso a usuarios registrados. | 1. Credenciales válidas permiten acceso.<br>2. Credenciales inválidas muestran error. |
| RF-02 | Gestión de Catálogos | El administrador debe poder configurar fundos, lotes, usuarios y roles. | 1. Solo Administrador accede.<br>2. Se asocian lotes a fundos (los cuarteles no están predefinidos, se ingresan manualmente). |
| RF-03 | Registro Venta Descarte | Registrar nueva venta con Fecha, Lote, Motivo, Tipo, Cuartel (condicional), Precio, KG, Jabas (opcional), Peso jaba (opcional) y datos de transporte (opcionales). | 1. Precio y KG en Soles con 2 decimales.<br>2. Valor de Venta = Precio * KG.<br>3. Cuartel es obligatorio de ingreso manual si Motivo es "Cosecha Nacional".<br>4. Si Motivo es "Cosecha Nacional", los tipos son solo dos: Racimos y Granos (Campo admite Racimos, Racimos con plaga, Granos; Packing admite Racimos, Granos). |
| RF-04 | Restricción por Fundo | Los registros y consultas se limitan según el rol del usuario. | 1. Individual y General solo ven datos de su fundo asignado.<br>2. Analista ve datos de los 3 fundos. |
| RF-05 | Exportación a Excel | Permite descargar la tabla de registros en formato Excel, seleccionando las columnas a descargar. | 1. Las columnas de HORA y USUARIO (auditoría) no se incluyen en la exportación.<br>2. El archivo descarga correctamente en celular y PC. |
| RF-06 | Registro Offline-First | Permitir el registro sin red y sincronización posterior. | Ver `ADR-001-OFFLINE_FIRST.md`. |
| RF-07 | Auditoría de Creación | El sistema guardará el Usuario, Fecha y Hora del registro de forma oculta en la vista regular. | 1. Ningún usuario no administrador puede ver la hora y usuario de creación en la tabla principal. |
| RF-08 | Edición de Registros | Permite corregir una venta ya guardada, manteniendo un registro estricto de auditoría. | 1. Registra el usuario que hizo la última modificación, fecha y hora.<br>2. El registro modificado y sus datos de auditoría deben sincronizarse al backend. |

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
| General (Jefes de fundo) | Ver registros | Ve todos los registros únicamente de **su fundo** asignado. |
| Analista | Ver registros, Descargar data, Análisis | Entra al sistema y puede ver/descargar los registros de los **tres fundos**. |

## Casos de Uso Principales
1. **CU-01 Registrar Descarte:** Usuario entra a la app (online/offline), llena el formulario (validando que Cuartel es requerido para Cosecha Nacional), el valor se calcula automático, guarda y sincroniza.
2. **CU-02 Exportar Reporte:** Usuario General o Individual filtra la tabla, selecciona columnas (sin Hora/Usuario) y descarga el archivo XLSX.
3. **CU-03 Administrar Accesos:** Administrador crea cuenta a Individual, le asocia un Fundo y le activa el acceso.
