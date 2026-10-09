# Registro de Riesgos

| ID | Riesgo | Probabilidad | Impacto | Mitigación Implementada | Responsable | Estado |
|---|---|---|---|---|---|---|
| R-01 | Requisitos ambiguos | Baja | Alta | Requisitos aclarados en `PREGUNTAS_ABIERTAS.md` y formalizados en `REQUISITOS_APROBADOS.md`. | Analista | Mitigado |
| R-02 | Exposición de datos/credenciales | Baja | Alta | Mínimo privilegio, `.gitignore`, `.env.example` sin credenciales reales y seeders con datos ficticios. | Seguridad | Mitigado |
| R-03 | Acceso cruzado entre fundos (Cross-tenant) | Baja | Crítico | Implementado `FundoScope` (Eloquent Global Scope) con 4 pruebas automatizadas en `FundoScopeTest`. | Backend | Mitigado |
| R-04 | Trazabilidad vulnerada o datos alterados | Baja | Alta | Marcas de tiempo del servidor y usuario autenticado forzado en `created_by` y `updated_by`. | Arquitectura / Backend | Mitigado |
| R-05 | Fallos en validación de datos en campo | Baja | Alta | Validación estricta en servidor según motivo (Campo, Packing, Cosecha Nacional) probada con PHPUnit. | QA / Backend | Mitigado |
| R-06 | Deformación visual en smartphones angostos | Baja | Media | Sistema responsive con tipografía `clamp()`, `table-wrapper` con scroll táctil y botones simétricos. | Frontend / UX | Mitigado |

