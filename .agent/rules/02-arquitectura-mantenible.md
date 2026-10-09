---
trigger: always_on
description: "Arquitectura modular, escalable, sostenible y separación de código, configuración y datos."
---

# Arquitectura para mantenibilidad y evolución

## 1. Modularidad y cohesión
- Diseñar la aplicación mediante módulos funcionales independientes y altamente cohesionados (Autenticación, Ventas/Descartes, Catálogos de Fundos/Lotes/Cuarteles, Reportes y Administración).
- Favorecer interfaces y contratos explícitos entre capas (Controladores, Servicios, Repositorios/Modelos, Vistas).
- Ningún módulo debe depender de detalles internos o estados ocultos de otro sin un contrato formal establecido.
- Debe ser posible mantener, actualizar y probar cada módulo de manera aislada sin provocar efectos colaterales en el resto del sistema.

## 2. Separación de código, configuraciones y datos
- **Código:** Versionado en Git, libre de rutas absolutas locales fijas o credenciales hardcodeadas.
- **Configuración:** Cargada dinámicamente mediante variables de entorno del sistema (`config()`, `.env`), separando estrictamente los parámetros de cada entorno.
- **Datos persistentes:** Almacenados en el motor de base de datos y sistemas de archivos dedicados, desacoplados del ciclo de vida del despliegue del código.

## 3. Simplicidad tecnológica y proporcionalidad
- Priorizar soluciones directas, comprensibles y fáciles de auditar por el equipo técnico.
- Evitar microservicios innecesarios, capas de abstracción excesivas o dependencias externas complejas cuando un monolito modular bien estructurado resuelve la necesidad con menor riesgo operacional.
- Toda nueva librería o dependencia de terceros debe justificarse por una necesidad real del negocio que no pueda resolverse limpiamente con las herramientas base del stack aprobado.

## 4. Gestión de cambios arquitectónicos
- Cualquier cambio de arquitectura, alteración de contratos públicos, cambio de motor de base de datos o sustitución de dependencias clave requiere:
  1. Registro de Decisión Arquitectónica (ADR) documentado.
  2. Análisis formal de impacto sobre módulos existentes.
  3. Plan de reversión (rollback) detallado.
  4. Aprobación expresa del arquitecto y del propietario del proyecto.
