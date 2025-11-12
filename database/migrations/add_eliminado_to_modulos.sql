-- Agregar columna 'eliminado' a la tabla modulos
-- Fecha: 2025-11-11
-- Descripción: Permite marcar módulos como eliminados cuando sus archivos ya no existen

ALTER TABLE `modulos` 
ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indica si el módulo fue marcado como eliminado' AFTER `activo`,
ADD INDEX `idx_eliminado` (`eliminado`);

-- Actualizar fecha de modificación
UPDATE `modulos` SET `fecha_actualizacion` = CURRENT_TIMESTAMP WHERE 1=1;
