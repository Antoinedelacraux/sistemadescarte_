# Reglas permanentes del sistema web del fundo

## Principio permanente del proyecto
> "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible. Ningún agente podrá comprometer deliberadamente esos datos ni asumir que dispone de autorización para ejecutar operaciones destructivas."

---

## 1. Orden de autoridad
1. Instrucciones explícitas y actuales del propietario del proyecto, sin contravenir las políticas de seguridad.
2. Este documento maestro (`AGENTS.md`) y las reglas permanentes en `.agents/rules/`.
3. Decisiones de arquitectura aprobadas en `docs/02-arquitectura/DECISIONES.md`.
4. Requisitos y especificaciones registradas en `docs/01-requisitos/`.
5. Sugerencias de los agentes, siempre identificadas claramente como propuestas pendientes de revisión.

---

## 2. Forma de trabajo obligatoria para todos los agentes

### Antes de realizar cambios importantes, cada agente debe comunicar:
- **Qué va a modificar:** Archivos, clases, tablas o configuraciones involucradas.
- **Por qué es necesario:** Justificación técnica o funcional asociada a la solicitud.
- **Qué módulos afecta:** Mapeo de dependencias internas o externas.
- **Si modifica la base de datos:** Si requiere migración, alteración de columnas o datos de prueba.
- **Qué riesgos existen:** Impacto potencial sobre datos existentes, rendimiento o regresiones.
- **Cómo comprobará el resultado:** Plan de pruebas automatizadas o manuales verificables.

### Al finalizar la intervención, cada agente debe informar:
- **Archivos modificados:** Lista explícita de rutas afectadas.
- **Migraciones creadas:** Si se alteró el esquema y su compatibilidad retrospectiva.
- **Pruebas ejecutadas:** Comandos específicos, aserciones y evidencias de ejecución.
- **Resultados obtenidos:** Comprobación del correcto funcionamiento.
- **Riesgos pendientes:** Puntos que requieren atención o pruebas en entornos superiores.
- **Acciones que requieren autorización humana:** Despliegues, migraciones en producción o cambios irreversibles.

---

## 3. Responsabilidades por rol de agente

- **ARQUITECTO (`arquitecto-software`):**
  Evalúa el impacto sistémico, garantiza la modularidad y escalabilidad, documenta ADRs y previene la introducción de complejidad tecnológica innecesaria.
- **BACKEND (`backend`):**
  Implementa lógica de negocio, validaciones del lado servidor, persistencia eficiente y llamadas ORM seguras mediante contratos explícitos.
- **DATABASE SPECIALIST (`database-specialist`):**
  Diseña y audita esquemas de base de datos, asegura integridad referencial, índices, optimización de consultas, migraciones seguras y planes de contingencia.
- **QA (`qa`):**
  Diseña y ejecuta pruebas unitarias, de integración, de regresión y de persistencia, garantizando que las modificaciones no rompan funciones previas ni comprometan datos históricos.
- **DEVOPS (`seguridad-devops` / `devops`):**
  Administra la configuración de entornos (Desarrollo, Staging, Producción), políticas de respaldo con restauración verificada, pipelines de despliegue seguro en 10 pasos y monitoreo.
- **GIT SPECIALIST (`git-specialist`):**
  Inspecciona diffs, previene fugas de archivos `.env`, dumps o secretos, y organiza commits locales atómicos, descriptivos y trazables.

---

## 4. Separación estricta de entornos
- **DESARROLLO:** Base de datos local aislada. Datos ficticios permitidos. Reinicializaciones permitidas solo en bases de prueba locales.
- **STAGING / PRUEBAS:** Condiciones similares a producción. Datos sintéticos o anonimizados. Ensayos de migraciones y pruebas de rendimiento. Prohibido compartir credenciales con producción.
- **PRODUCCIÓN:** Datos reales del negocio exclusivamente. **Terminantemente prohibido:** ejecutar semillas ficticias, reiniciar bases de datos (`migrate:fresh`), eliminar registros históricos sin autorización o aplicar cambios sin respaldo verificado.

---

## 5. Coordinación y ejecución
- Nunca asumir que un cambio funciona solo porque compila o arranca. Toda afirmación requiere evidencia de pruebas ejecutadas.
- Toda operación destructiva o irreversible está bloqueada por defecto hasta contar con confirmación humana explícita.
- Mantener permanentemente actualizados `docs/00-gestion/ESTADO_PROYECTO.md` y `docs/00-gestion/REGISTRO_CAMBIOS.md`.
