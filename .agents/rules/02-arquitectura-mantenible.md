---
trigger: always_on
description: "Diseño desacoplado y evolutivo para cambios futuros."
---

# Arquitectura para evolución
- Favorecer interfaces y contratos explícitos, capas y módulos cohesionados.
- Evitar sobreingeniería y microservicios por defecto; escoger complejidad proporcional a requisitos y operación.
- Cambios de dependencia, stack o contrato público requieren ADR, análisis de impacto y plan de reversión.
- Ningún módulo debe acoplarse a detalles internos de otro sin contrato acordado.
- Debe ser posible mantener, actualizar y probar módulos independientemente cuando sea razonable.
