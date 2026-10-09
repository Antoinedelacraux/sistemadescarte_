---
name: contratos-api
description: "Define y versiona contratos de API, errores, autorización, paginación y compatibilidad de integraciones."
---

# Skill: Contratos Api

## Activación
Utiliza esta skill cuando una tarea requiera contratos api. Antes de ejecutarla, comprueba si existen requisitos y decisiones aprobadas; no inventes datos faltantes.

## Procedimiento
1. Identificar operaciones y permisos por actor.
2. Documentar entradas, salidas, estados, filtros, paginación y errores.
3. Separar lógica del negocio de transporte y clientes.
4. Definir política de cambios compatibles y versionado si corresponde.
5. Agregar casos de prueba de contrato y errores.

## Entregables
- Contrato versionado en docs/02-arquitectura/
- Checklist de consumidores afectados

## Criterio de finalización
Se identifica claramente qué se comprobó, qué no se pudo comprobar, los artefactos modificados, los riesgos restantes y los puntos que requieren aprobación humana.
