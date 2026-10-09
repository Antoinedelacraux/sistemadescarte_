---
trigger: always_on
description: "Principio permanente de integridad de datos del cliente, prevención de operaciones destructivas y separación estricta de entornos."
---

# Principio permanente del proyecto

> **PRINCIPIO NO NEGOCIABLE:**
> "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible. Ningún agente podrá comprometer deliberadamente esos datos ni asumir que dispone de autorización para ejecutar operaciones destructivas."

---

## 1. Reglas generales obligatorias para todos los agentes

1. **Prioridad absoluta a la integridad de los datos:** Ninguna conveniencia técnica, velocidad de entrega o simplificación temporal justifica poner en riesgo información real del negocio agrícola.
2. **Arquitectura mantenible y modular:** Todo diseño debe ser modular, desacoplado y preparado para evolucionar sin requerir reescrituras masivas.
3. **Separación tripartita estricta:** Mantener siempre desacoplados:
   - **Código de aplicación:** Lógica, controladores, vistas y contratos versionados.
   - **Configuraciones:** Variables de entorno (`.env`), secretos y parámetros fuera del control de versiones.
   - **Datos persistentes:** Bases de datos, archivos cargados y respaldos almacenados independientemente.
4. **Cero cambios destructivos innecesarios:** Prohibido eliminar tablas, columnas, registros o archivos de datos persistentes sin análisis formal de dependencias y autorización explícita.
5. **Compatibilidad retrospectiva (Backwards Compatibility):** Toda modificación de esquema o API debe ser compatible con los datos y registros históricos existentes.
6. **Documentación obligatoria de cambios:** Registrar todo cambio relevante en el registro de cambios, arquitectura y estado del proyecto.
7. **Simplicidad y sostenibilidad:** Priorizar soluciones limpias, directas y comprobadas; evitar introducir herramientas, librerías o complejidades tecnológicas innecesarias.
8. **Validación real y evidencia comprobable:** No asumir que un sistema funciona solo porque compila o responde un código HTTP. Toda afirmación de éxito requiere pruebas ejecutadas con evidencia.
9. **Detención inmediata ante operaciones de riesgo:** Frenar la ejecución y solicitar autorización humana explícita ante cualquier operación con potencial destructivo, irreversible o que involucre el entorno de producción.

---

## 2. Política obligatoria de separación de entornos

El sistema opera bajo tres entornos estrictamente aislados:

### A. DESARROLLO (Local / Sandbox)
- **Propósito:** Construcción de funcionalidades, refactorización y pruebas unitarias rápidas.
- **Datos:** Exclusivamente datos ficticios o simulados.
- **Base de datos:** Aislada localmente (`database.sqlite` de prueba o base MySQL dev local).
- **Operaciones:** Se permiten reinicializaciones controladas (`migrate:fresh`) **únicamente** en bases de datos de desarrollo identificadas, nunca sobre datos reales.

### B. STAGING / PRUEBAS (Pre-producción)
- **Propósito:** Homologación en condiciones similares a producción antes de cada release.
- **Datos:** Datos sintéticos o debidamente anonimizados mediante proceso formal autorizado.
- **Validaciones:** Ejecución y ensayo de migraciones reversibles, pruebas de integración, seguridad, concurrencia y rendimiento.
- **Aislamiento:** Prohibido compartir credenciales, cadenas de conexión o bases de datos con el entorno de producción.

### C. PRODUCCIÓN (Operación Real del Fundo)
- **Propósito:** Operación diaria de pesadores, registradores, administradores y analistas del fundo.
- **Datos:** Almacena única y exclusivamente información real, autorizada y trazable del negocio.
- **Prohibiciones absolutas:**
  - ❌ Prohibido ejecutar semillas de prueba o desarrollo (`DatabaseSeeder` con mocks).
  - ❌ Prohibido ejecutar reinicios, truncados o reseteos de base de datos (`migrate:fresh`, `migrate:reset`, `db:wipe`, `TRUNCATE`).
  - ❌ Prohibido eliminar o modificar registros históricos sin autorización explícita y procedimiento de auditoría.
  - ❌ Prohibido realizar pruebas experimentales o diagnósticos intrusivos en caliente.
- **Protección de secretos:** Todas las credenciales, llaves de cifrado y secretos deben gestionarse por variables de entorno seguras fuera de Git, con permisos mínimos de lectura en el servidor.
