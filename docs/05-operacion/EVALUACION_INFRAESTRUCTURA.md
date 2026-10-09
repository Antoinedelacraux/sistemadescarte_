# Evaluación y Selección de Infraestructura

**Estado: DECIDIDO Y PLANIFICADO (VPS DonWeb Cloud Server).**

## 1. Infraestructura Seleccionada
- **Proveedor:** DonWeb (Cloud Server VPS).
- **Sistema Operativo:** Ubuntu 24.04 LTS x86_64.
- **Servidor Web:** Nginx optimizado con reverse proxy y compresión gzip.
- **Runtime:** PHP 8.4-FPM con extensiones `pdo_mysql`, `mbstring`, `bcmath`, `xml`, `curl`, `zip`.
- **Base de Datos:** MySQL 8.0 con motor InnoDB y charset `utf8mb4`.
- **Seguridad:** Firewall UFW (puertos 22, 80, 443), SSL gratuito con Let's Encrypt (Certbot) y Fail2ban.

## 2. Comparativa Técnica

| Opción | Base de Datos | Costo / Rendimiento | Mantenimiento | Decisión |
|---|---|---|---|---|
| Hosting Compartido cPanel | MySQL compartida | Bajo costo / Recursos limitados | Alto riesgo de incompatibilidad con PHP 8.4 y PWA | Descartado |
| **VPS DonWeb Dedicado** | **MySQL 8.0 aislada** | **Excelente relación costo/rendimiento** | **Control total del stack y compatibilidad 100%** | **SELECCIONADO** |
| Cloud Server AWS / GCP | RDS / Aurora | Alto costo recurrente en dólares | Complejidad innecesaria para el volumen inicial | Descartado |

## 3. Delimitación de Responsabilidad
- **Equipo de Desarrollo:** Entrega paquete precompilado (.zip) listo para producción, libre de código de desarrollo y con documentación en `PLAN_DESPLIEGUE.md`.
- **Administrador del VPS:** Aprovisiona el servidor, configura DNS, SSL, cortafuegos y ejecuta el script de instalación en producción.

