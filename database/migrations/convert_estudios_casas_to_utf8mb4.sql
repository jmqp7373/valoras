-- Migración: Convertir tabla estudios_casas a UTF8MB4
-- Fecha: 2025-11-15
-- Descripción: Convierte la tabla estudios_casas de latin1 a utf8mb4 para soportar tildes y caracteres especiales

-- Convertir la tabla y sus columnas a utf8mb4
ALTER TABLE estudios_casas 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Verificar el cambio
SHOW CREATE TABLE estudios_casas;

-- Mostrar algunos registros para verificar que las tildes se ven correctamente
SELECT id_estudio_casa, nombre_estudio_casa 
FROM estudios_casas 
WHERE nombre_estudio_casa LIKE '%ñ%' OR nombre_estudio_casa LIKE '%á%' 
   OR nombre_estudio_casa LIKE '%é%' OR nombre_estudio_casa LIKE '%í%' 
   OR nombre_estudio_casa LIKE '%ó%' OR nombre_estudio_casa LIKE '%ú%'
ORDER BY nombre_estudio_casa;
