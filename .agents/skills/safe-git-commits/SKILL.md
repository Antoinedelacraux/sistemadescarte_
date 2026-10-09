---
name: safe-git-commits
description: "Revisa diffs, previene fuga de secretos o dumps de base de datos y genera commits Git locales atómicos, descriptivos y seguros según los estándares del proyecto."
---

# Skill: Safe Git Commits (Commits Seguros y Control de Versiones)

## Propósito
Garantizar que todo commit en el repositorio sea atómico, limpio, trazable y rigurosamente seguro, evitando la inclusión inadvertida de secretos, variables de entorno, volcados de base de datos o datos sensibles del negocio.

> **Principio Permanente:** "El código del sistema puede cambiar y evolucionar, pero la información real del cliente debe mantenerse íntegra, protegida y disponible."

---

## Cuándo activar esta skill
- Al finalizar una tarea, funcionalidad, corrección de errores, refactorización o actualización documental comprobada.
- Previo a registrar avances significativos bajo control de versiones.
- Al verificar la limpieza del staging antes de confirmar cambios.

---

## Filtro No Negociable de Secretos y Datos Sensibles
Antes de indexar o confirmar cualquier archivo, verificar rigurosamente que **NO** se incluyan:
- ❌ Archivos de entorno y configuración sensible: `.env`, `.env.local`, `.env.production`, `.env.backup`.
- ❌ Volcados y respaldos de base de datos: `*.sql`, `*.dump`, `*.sqlite`, `*.db`, `*.tar.gz`, `*.bak`.
- ❌ Archivos con datos reales de producción o información confidencial de clientes, trabajadores o precios.
- ❌ Credenciales privadas: claves SSH (`id_rsa`), certificados SSL (`*.key`, `*.pem`), tokens de acceso o llaves de API.
- ❌ Artefactos temporales o binarios de compilación pesados no justificados.

---

## Límites Operacionales de los Agentes
- **Autorización Concedida:** Los agentes tienen autorización para crear commits **locales** acotados de tareas solicitadas que hayan pasado sus verificaciones correspondientes.
- **Operaciones Estrictamente Prohibidas sin Autorización Humana:**
  - ❌ `git push` a repositorios remotos.
  - ❌ `git merge` hacia ramas protegidas.
  - ❌ `git rebase`, `git reset --hard` o cualquier alteración destructiva del historial Git.
  - ❌ `git push --force`.
  - ❌ Modificación de la configuración global de Git del usuario (`git config --global`).

---

## Procedimiento Paso a Paso para un Commit Seguro

### 1. Comprobación del Entorno y Rama
- Verificar que se está en el repositorio y la rama de trabajo adecuada:
  ```bash
  git status --short --branch
  ```

### 2. Inspección Exhaustiva del Diff
- Examinar línea por línea lo que se modificó:
  ```bash
  git diff
  ```
- Comprobar que no hay código de depuración temporal (`dd()`, `dump()`, `console.log`), rutas absolutas fijas ni credenciales hardcodeadas.

### 3. Preparación Explícita por Rutas (Staging Seguro)
- **Prohibido el uso de comodines masivos:** No utilizar `git add .`, `git add -A` ni `git commit -a` de forma ciega.
- Indexar únicamente las rutas específicas que componen la unidad lógica del hito:
  ```bash
  git add -- ruta/archivo1.php ruta/archivo2.blade.php
  ```

### 4. Doble Verificación del Índice (Cached Diff)
- Revisar exactamente lo que entrará en el commit:
  ```bash
  git diff --cached --stat
  git diff --cached
  ```
- Si se detecta algún archivo fuera de alcance o sospechoso, removerlo del índice de inmediato (`git restore --staged <archivo>`).

### 5. Creación del Commit con Mensaje Convencional
- Formato: `tipo(alcance): descripción clara en español`
  - `feat`: Nueva funcionalidad completada.
  - `fix`: Corrección de un fallo verificado.
  - `refactor`: Mejora interna de código sin cambio de comportamiento.
  - `test`: Incorporación o mejora de pruebas automatizadas.
  - `docs`: Actualización de documentación o registros de gestión.
  - `chore`: Tareas de configuración, dependencias o herramientas de desarrollo.
- Ejemplo:
  ```bash
  git commit -m "feat(fundos): agregar creacion inmediata de lotes y cuarteles en administracion"
  ```

### 6. Verificación Posterior y Reporte
- Comprobar que el commit se registró exitosamente:
  ```bash
  git log -1 --format='%h - %s (%an)'
  ```
- Informar al usuario o al orquestador el hash corto obtenido, el mensaje exacto y las rutas confirmadas.

---

## Criterios de Aceptación
- Commit atómico y coherente con la tarea solicitada.
- Ningún secreto ni archivo no deseado presente en el commit.
- Pruebas automatizadas pertinentes ejecutadas y en verde antes de confirmar.
- Hash y mensaje verificados reportados con total precisión.
