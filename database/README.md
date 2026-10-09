# Base de Datos — Sistema Fundo

La persistencia y el esquema relacional del proyecto se gestionan mediante migraciones y seeders de Laravel ubicados en **`apps/web/database/`**.

- **Migraciones:** `apps/web/database/migrations/` (roles, fundos, users, fundo_user, ventas_descarte)
- **Seeders y Datos de Prueba:** `apps/web/database/seeders/` (`DatabaseSeeder.php`)
- **Base de Datos Local:** SQLite (`apps/web/database/database.sqlite`)
- **Base de Datos Producción:** MySQL 8.0 en VPS DonWeb
- **Modelo Entidad-Relación:** [docs/02-arquitectura/MODELO_DATOS_ER.md](file:///docs/02-arquitectura/MODELO_DATOS_ER.md)

