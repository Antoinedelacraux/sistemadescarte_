# LÃ­mite de Responsabilidad de Despliegue (VPS)

**Directiva Estricta:**
El alcance de este proyecto, a nivel de operaciones y despliegue, **termina en la generaciÃ³n del cÃ³digo fuente precompilado y empaquetado**.

NingÃºn agente de Antigravity, ni el desarrollador local, tiene autorizaciÃ³n ni responsabilidad para:
1. Conectarse por SSH al servidor VPS (Ferozo / DonWeb).
2. Modificar configuraciones de Apache, Nginx o PHP-FPM en producciÃ³n.
3. Crear o alterar bases de datos de producciÃ³n directamente.
4. Gestionar dominios, subdominios o certificados SSL.

Toda la interacciÃ³n con el VPS queda delegada **exclusivamente al Administrador del VPS designado por el propietario**.

Nosotros nos limitamos a:
1. Escribir cÃ³digo compatible con PHP 8.4 y MySQL 8.0.
2. Compilar los assets del frontend (`npm run build`).
3. Generar un archivo comprimido listo para producciÃ³n (empaquetado).
4. Proveer el documento `PLAN_DESPLIEGUE.md` con las instrucciones exactas que el Administrador del VPS debe seguir (copiar archivos, configurar el `.env`, y ejecutar `php artisan migrate`).
