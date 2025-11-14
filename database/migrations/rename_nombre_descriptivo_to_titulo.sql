-- Renombrar columna nombre_descriptivo a titulo en tabla modulos
-- Fecha: 2025-11-12
-- Descripción: Cambio de nombre de columna para simplificar nomenclatura

ALTER TABLE `modulos` 
CHANGE COLUMN `nombre_descriptivo` `titulo` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Título del módulo';
