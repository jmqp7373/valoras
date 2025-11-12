-- Crear tabla password_first_token para almacenar códigos de verificación inicial
-- Esta tabla maneja los códigos de 6 dígitos enviados durante el registro

CREATE TABLE IF NOT EXISTS `password_first_token` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cedula` VARCHAR(20) NOT NULL,
  `token` VARCHAR(6) NOT NULL,
  `celular` VARCHAR(30) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_at` DATETIME NULL DEFAULT NULL,
  `verified` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cedula_active` (`cedula`, `verified`),
  KEY `idx_token` (`token`),
  KEY `idx_cedula` (`cedula`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: Este sistema separa la verificación inicial del password permanente
-- El usuario primero verifica su celular con el token, luego crea su password
