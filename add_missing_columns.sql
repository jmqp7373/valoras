-- Script para agregar la columna id_estado si fuera necesaria en el futuro
-- Ejecutar solo si se requiere compatibilidad con la estructura remota

-- Verificar si la columna existe antes de agregarla
SET @s = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = 'usuarios'
        AND table_schema = DATABASE()
        AND column_name = 'id_estado'
    ) > 0,
    'SELECT 1',
    'ALTER TABLE usuarios ADD COLUMN id_estado int(11) DEFAULT 1 AFTER disponibilidad'
));

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Establecer valor por defecto para registros existentes
UPDATE usuarios SET id_estado = 1 WHERE id_estado IS NULL;