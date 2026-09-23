-- Ejecutar en la base de datos de la aplicación, mediante phpMyAdmin si no hay terminal.
-- Alternativa a php artisan migrate; no importa ni reemplaza la base de datos.
CREATE TABLE IF NOT EXISTS `reasignacion_docente_auditorias` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `operacion` CHAR(36) NOT NULL UNIQUE,
  `usuario_id` BIGINT UNSIGNED NULL,
  `usuario` VARCHAR(255) NOT NULL,
  `profesor_anterior_id` BIGINT UNSIGNED NOT NULL,
  `profesor_nuevo_id` BIGINT UNSIGNED NOT NULL,
  `profesor_anterior` VARCHAR(255) NOT NULL,
  `profesor_nuevo` VARCHAR(255) NOT NULL,
  `total` INT UNSIGNED NOT NULL,
  `detalle` JSON NOT NULL,
  `created_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
