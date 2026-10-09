---
name: production-readiness
description: "Evalúa rigurosamente si el sistema cumple la checklist de entrega al cliente para despliegue y puesta en marcha en entorno de producción."
---

# Skill: Production Readiness (Evaluación para Entrega al Cliente)

## Propósito
Realizar una auditoría técnica exhaustiva y verificar el cumplimiento estricto de la checklist de salida antes de autorizar la entrega del sistema web del fundo al cliente o su puesta en operación real con usuarios del negocio.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Al finalizar una fase de desarrollo y preparar el hito de entrega formal.
- Previo a la migración inicial o puesta en marcha en el servidor de producción.
- Para emitir el dictamen formal de aprobación técnica (*Go / No-Go*).

---

## Checklist Integral de Entrega al Cliente

### 1. Base de Datos y Calidad de Datos
- [ ] **Esquema Oficial Limpio:** Base de datos inicializada exclusivamente con migraciones oficiales versionadas.
- [ ] **Cero Datos Ficticios:** Ausencia absoluta de registros de prueba, nombres simulados, pesajes aleatorios o semillas de desarrollo en la base de datos de producción.
- [ ] **Catálogos Oficiales Validados:** Fundos reales (`AGRICOLA TAMBO COLORADO (AGRITAC)`, `AGRICOLA PROCOM (PROCOM)`, `TALSA GRAPE FARMS (EL NEGRO)`) y sus 34 lotes exactos correctamente cargados.
- [ ] **Integridad Referencial Activa:** Claves foráneas configuradas sin registros huérfanos.

### 2. Seguridad y Control de Acceso
- [ ] **Roles y Permisos:** Los 4 roles base (`Admin`, `Analista`, `Individual`, `Visualizador`) configurados y probados con sus respectivos scopes de aislamiento (`FundoScope`).
- [ ] **Administrador Inicial Seguro:** Administrador creado mediante procedimiento seguro interactivo, sin contraseñas predeterminadas ni credenciales registradas en archivos de Git.
- [ ] **Configuración `.env` Protegida:**
  - `APP_ENV=production`
  - `APP_DEBUG=false` (sin fugas de trazas ni variables en pantallas de error).
  - `APP_KEY` generada de 32 bytes aleatorios.
  - Permisos de archivo `.env` configurados en `chmod 600` o `640`.
- [ ] **Comunicaciones Cifradas (HTTPS):** Certificado SSL activo con redirección obligatoria de HTTP a HTTPS y encabezados seguros (`HSTS`, `X-Frame-Options`, `X-Content-Type-Options`).

### 3. Operaciones, Respaldos y Monitoreo
- [ ] **Rutina de Respaldos Diarios:** Tarea programada activa con volcado transaccional y transferencia a almacenamiento aislado off-site.
- [ ] **Ensayo de Restauración Exitoso:** Se ejecutó una prueba real de restauración de la copia de seguridad sobre una base aislada con validación de datos.
- [ ] **Monitoreo y Alertas:** Registro de errores configurado en `storage/logs/` con rotación diaria y sin almacenamiento de contraseñas ni datos sensibles.

### 4. Paquete Documental Obligatorio
- [ ] **Documentación de Instalación y Despliegue:** Guía paso a paso para desplegar en el VPS o hosting aprobado.
- [ ] **Manual de Usuario:** Guía operativa para pesadores y administradores (registro de ventas de descarte, anulación en dos pasos, visualización en modal y exportación a Excel .xlsx).
- [ ] **Manual Técnico:** Arquitectura, diccionario de datos, roles y contratos de API.
- [ ] **Procedimiento de Actualización y Rollback:** Flujo de 10 pasos documentado para mantenimiento sin pérdida de datos.
- [ ] **Procedimiento de Recuperación ante Desastres:** Runbook con instrucciones claras ante contingencias.
- [ ] **Definición de Responsables:** Contactos formalmente establecidos para infraestructura, soporte técnico y ventanas de atención.

---

## Criterio de Decisión: Go / No-Go
- **GO (Aprobado):** El 100% de los puntos de la checklist se encuentran verificados y con evidencia documentada.
- **NO-GO (Bloqueado):** Si existe al menos un elemento pendiente o en riesgo (ej. `APP_DEBUG=true`, datos ficticios en producción, respaldos sin probar), el sistema queda **bloqueado para entrega** hasta la subsanación formal.

---

## Entregable de la Skill
- Informe de Auditoría de Entrega (*Production Readiness Report*) firmado por el Arquitecto, DevOps y QA con el estado verificado de cada punto.
