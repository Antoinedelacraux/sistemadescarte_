---
trigger: always_on
description: "Política obligatoria de commits Git seguros, prevención de fugas de secretos y trazabilidad de versiones."
---

# Control de versiones y commits seguros

## 1. Principios de versionado y trazabilidad
- **Commits atómicos y coherentes:** Cada commit debe contener una sola unidad lógica de trabajo (un fix verificado, una funcionalidad acotada, una migración y su modelo correspondiente, o una actualización documental).
- **Mensajes claros y descriptivos:** Utilizar el formato Conventional Commits en español (`feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `perf`), describiendo con precisión la causa y el impacto del cambio.
- **Separación de responsabilidades:** Cuando se introduzcan cambios de esquema de base de datos junto con nuevas pantallas, estructurar los commits separando preferiblemente la capa de migración/modelo de las vistas/controladores para facilitar auditorías y posibles reversiones.
- **Migraciones siempre versionadas:** Los archivos de migración deben commitearse rigurosamente junto con el código que los consume; nunca aplicar código sin su migración correspondiente en el repositorio.

## 2. Inspección obligatoria de seguridad pre-commit
Antes de preparar (`git add`) y confirmar (`git commit`) cualquier cambio, es obligatorio:
1. Inspeccionar el estado con `git status --short`.
2. Revisar el diff explícito con `git diff` y `git diff --cached`.
3. **Filtro estricto de secretos:** Verificar que bajo ninguna circunstancia se incluyan:
   - Archivos de entorno (`.env`, `.env.*`).
   - Respaldos de base de datos (`*.sql`, `*.dump`, `*.tar.gz`, `*.sqlite`).
   - Archivos con datos reales confidenciales de clientes, trabajadores o del fundo.
   - Credenciales, llaves privadas SSH, certificados SSL o tokens de acceso.
4. Si se detecta cualquier archivo sensible, **detener inmediatamente el commit**, corregir el `.gitignore` y purgar el archivo antes de continuar.

## 3. Autorización y límites operacionales
- Los agentes están autorizados para crear commits **locales** al completar y verificar hitos encomendados por el usuario.
- **Operaciones prohibidas sin autorización explícita:**
  - ❌ `git push` a repositorios remotos.
  - ❌ `git merge` a ramas principales o de producción.
  - ❌ `git rebase` o reescritura del historial Git (`git commit --amend`, `git reset --hard`).
  - ❌ `git push --force`.
  - ❌ Eliminación de ramas o tags en remotos.
- Nunca reportar un commit como realizado si el comando `git commit` no devolvió éxito con su hash verificado.
