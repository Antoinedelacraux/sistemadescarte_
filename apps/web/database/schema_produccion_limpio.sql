-- ==============================================================================
-- SISTEMA WEB DE GESTIÓN AGRÍCOLA — VENTA DE FRUTA DE DESCARTE (TALSA)
-- Esquema Oficial y Catálogos Iniciales para Producción en DonWeb Ferozo (MySQL 8.0+)
-- Cero registros de venta (inicia desde cero con registros del personal)
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. TABLA: migrations (Trazabilidad de migraciones versionadas)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2026_10_08_000001_create_roles_table', 1),
('2026_10_08_000002_create_fundos_table', 1),
('2026_10_08_000003_add_role_and_status_to_users_table', 1),
('2026_10_08_000004_create_fundo_user_table', 1),
('2026_10_08_000005_create_ventas_descarte_table', 1),
('2026_10_08_000006_create_lotes_and_cuarteles_tables', 1),
('2026_10_08_000007_add_details_to_ventas_descarte_table', 1),
('2026_10_09_000008_add_cliente_to_ventas_descarte_table', 1),
('2026_10_09_000009_add_estado_to_ventas_descarte_table', 1),
('2026_10_09_000010_setup_real_fundos_and_lotes', 1),
('2026_10_09_000011_add_nombre_completo_and_clean_fundo_names', 1),
('2026_10_12_000012_add_performance_indexes_to_ventas_descarte_table', 1),
('2026_10_12_000013_create_clientes_table', 1),
('2026_10_12_000014_update_roles_and_add_module_overrides_to_users_table', 1);

-- ------------------------------------------------------------------------------
-- 2. TABLA: roles (Los 5 roles oficiales del sistema)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrador', 'Acceso total y configuración de todos los módulos del sistema', NOW(), NOW()),
(2, 'jefe_fundo', 'Jefe de fundo', 'Visualización de información de sus fundos asignados (Panel e Historial)', NOW(), NOW()),
(3, 'registrador', 'Registrador', 'Registro de ventas e historial en sus fundos asignados', NOW(), NOW()),
(4, 'analista', 'Analista', 'Visualización de información de todos los fundos (Historial)', NOW(), NOW()),
(5, 'ventas', 'Ventas', 'Visualización de información de todos los fundos (Historial)', NOW(), NOW());

-- ------------------------------------------------------------------------------
-- 3. TABLA: fundos (Los 3 fundos oficiales del negocio)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `fundos`;
CREATE TABLE `fundos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_completo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fundos_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `fundos` (`id`, `name`, `nombre_completo`, `code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'AGRITAC', 'AGRICOLA TAMBO COLORADO', 'AGRITAC', 'Fundo Agrícola Tambo Colorado', 1, NOW(), NOW()),
(2, 'PROCOM', 'AGRICOLA PROCOM', 'PROCOM', 'Fundo Agrícola Procom', 1, NOW(), NOW()),
(3, 'EL NEGRO', 'TALSA GRAPE FARMS', 'ELNEGRO', 'Fundo Talsa Grape Farms - El Negro', 1, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- 4. TABLA: users (Cuentas de usuario del sistema)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `modulos_personalizados` json DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Administrador inicial (clave por defecto: password123 | cambiar de inmediato al ingresar)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrador General', 'admin@fundo.test', '$2y$12$D7k3uXbV3d4E6xXmPcqDxe3jKzDfqe.lV5n9K7E5hG2mB3cQ8e8h.', 1, 1, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- 5. TABLA: fundo_user (Asignación de fundos a usuarios)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `fundo_user`;
CREATE TABLE `fundo_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `fundo_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fundo_user_user_id_fundo_id_unique` (`user_id`,`fundo_id`),
  KEY `fundo_user_fundo_id_foreign` (`fundo_id`),
  CONSTRAINT `fundo_user_fundo_id_foreign` FOREIGN KEY (`fundo_id`) REFERENCES `fundos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fundo_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asignar los 3 fundos al administrador
INSERT INTO `fundo_user` (`user_id`, `fundo_id`, `created_at`, `updated_at`) VALUES
(1, 1, NOW(), NOW()),
(1, 2, NOW(), NOW()),
(1, 3, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- 6. TABLA: lotes (Los 34 lotes oficiales distribuidos por fundo)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `lotes`;
CREATE TABLE `lotes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fundo_id` bigint unsigned NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variedad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hectareas` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lotes_fundo_id_foreign` (`fundo_id`),
  CONSTRAINT `lotes_fundo_id_foreign` FOREIGN KEY (`fundo_id`) REFERENCES `fundos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AGRITAC (18 Lotes)
INSERT INTO `lotes` (`fundo_id`, `nombre`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'A03', 1, NOW(), NOW()), (1, 'A05', 1, NOW(), NOW()), (1, 'A06', 1, NOW(), NOW()),
(1, 'L03', 1, NOW(), NOW()), (1, 'L06', 1, NOW(), NOW()), (1, 'L07', 1, NOW(), NOW()),
(1, 'M03', 1, NOW(), NOW()), (1, 'M05', 1, NOW(), NOW()), (1, 'M04', 1, NOW(), NOW()),
(1, 'M01', 1, NOW(), NOW()), (1, 'M02', 1, NOW(), NOW()), (1, 'A04', 1, NOW(), NOW()),
(1, 'A02', 1, NOW(), NOW()), (1, 'A01', 1, NOW(), NOW()), (1, 'L01', 1, NOW(), NOW()),
(1, 'L02', 1, NOW(), NOW()), (1, 'L04', 1, NOW(), NOW()), (1, 'L05', 1, NOW(), NOW());

-- PROCOM (9 Lotes)
INSERT INTO `lotes` (`fundo_id`, `nombre`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'H01', 1, NOW(), NOW()), (2, 'H02', 1, NOW(), NOW()), (2, 'H03', 1, NOW(), NOW()),
(2, 'H04', 1, NOW(), NOW()), (2, 'H05', 1, NOW(), NOW()), (2, 'H06', 1, NOW(), NOW()),
(2, 'H07', 1, NOW(), NOW()), (2, 'H08', 1, NOW(), NOW()), (2, 'H09', 1, NOW(), NOW());

-- EL NEGRO (7 Lotes)
INSERT INTO `lotes` (`fundo_id`, `nombre`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'N01', 1, NOW(), NOW()), (3, 'N03', 1, NOW(), NOW()), (3, 'N04', 1, NOW(), NOW()),
(3, 'N05', 1, NOW(), NOW()), (3, 'N06', 1, NOW(), NOW()), (3, 'N07', 1, NOW(), NOW()),
(3, 'N08', 1, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- 7. TABLA: clientes (Catálogo de clientes compradores de fruta de descarte)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 8. TABLA: ventas_descarte (Registro operativo de ventas de descarte)
-- INICIA EN CERO REGISTROS PARA PRODUCCIÓN
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `ventas_descarte`;
CREATE TABLE `ventas_descarte` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fundo_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `fecha_produccion` date NOT NULL,
  `fecha_pesaje` date DEFAULT NULL,
  `motivo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_descarte` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lote` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variedad` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kilogramos` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,4) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `placa_vehiculo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cliente` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activo','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `motivo_anulacion` text COLLATE utf8mb4_unicode_ci,
  `anulado_por` bigint unsigned DEFAULT NULL,
  `anulado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ventas_descarte_uuid_unique` (`uuid`),
  KEY `ventas_descarte_fundo_id_foreign` (`fundo_id`),
  KEY `ventas_descarte_user_id_foreign` (`user_id`),
  KEY `ventas_descarte_anulado_por_foreign` (`anulado_por`),
  KEY `idx_ventas_estado_fecha` (`estado`,`fecha_produccion`),
  KEY `idx_ventas_fundo_fecha` (`fundo_id`,`fecha_produccion`),
  KEY `idx_ventas_analytics` (`estado`,`fundo_id`,`fecha_produccion`),
  CONSTRAINT `ventas_descarte_anulado_por_foreign` FOREIGN KEY (`anulado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_descarte_fundo_id_foreign` FOREIGN KEY (`fundo_id`) REFERENCES `fundos` (`id`),
  CONSTRAINT `ventas_descarte_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 9. TABLAS DE SOPORTE LARAVEL: sessions, cache, jobs
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
