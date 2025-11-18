-- Migración: Convertir tablas de estudios a UTF8MB4 y corregir caracteres especiales
-- Fecha: 2025-11-16
-- Descripción: Convierte todas las tablas de estudios a utf8mb4 y corrige datos con caracteres especiales corruptos

-- 1. Convertir tablas a UTF8MB4
ALTER TABLE estudios 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE estudios_casas 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE estudios_categorias 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE estudios_clases 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE estudios_auditoria 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Corregir datos de estudios_clases
UPDATE estudios_clases SET nombre_estudio_clase = 'Satélites' WHERE id_estudio_clase = 2;
UPDATE estudios_clases SET nombre_estudio_clase = 'SatélitesEstudio' WHERE id_estudio_clase = 4;

-- 3. Corregir datos de estudios
UPDATE estudios SET nombre_estudio = 'España' WHERE nombre_estudio LIKE '%Espa%a%' OR nombre_estudio LIKE 'Espa╟±a';
UPDATE estudios SET nombre_estudio = 'Estadio Día' WHERE nombre_estudio LIKE '%Estadio D%a%' OR nombre_estudio LIKE 'Estadio D╟≡a';
UPDATE estudios SET nombre_estudio = 'Japón' WHERE nombre_estudio LIKE '%Jap%n%' OR nombre_estudio LIKE 'Jap╟ⁿn';
UPDATE estudios SET nombre_estudio = 'Justo y bueno Día' WHERE nombre_estudio LIKE 'Justo y bueno D╟≡a';
UPDATE estudios SET nombre_estudio = 'La 33 día' WHERE nombre_estudio LIKE 'La 33 d╟≡a';
UPDATE estudios SET nombre_estudio = 'La 80 día' WHERE nombre_estudio LIKE 'La 80 d╟≡a';
UPDATE estudios SET nombre_estudio = 'Merkepaisaa Día' WHERE nombre_estudio LIKE 'Merkepaisaa D╟≡a';
UPDATE estudios SET nombre_estudio = 'Santa Gema Día' WHERE nombre_estudio LIKE 'Santa Gema D╟≡a';
UPDATE estudios SET nombre_estudio = 'Satélites De Estudio' WHERE nombre_estudio LIKE 'Sat%lites De Estudio' AND id_estudio = 9;
UPDATE estudios SET nombre_estudio = 'Satélites De Estudio 2' WHERE nombre_estudio LIKE 'Sat%lites De Estudio 2' AND id_estudio = 30;

-- 4. Verificar cambios
SELECT 'Estudios corregidos:' as Resultado;
SELECT id_estudio, nombre_estudio 
FROM estudios 
WHERE nombre_estudio IN (
    'España', 'Estadio Día', 'Japón', 'Justo y bueno Día', 
    'La 33 día', 'La 80 día', 'Merkepaisaa Día', 'Santa Gema Día',
    'Satélites De Estudio', 'Satélites De Estudio 2'
)
ORDER BY nombre_estudio;

SELECT 'Clases corregidas:' as Resultado;
SELECT id_estudio_clase, nombre_estudio_clase 
FROM estudios_clases 
WHERE nombre_estudio_clase LIKE '%Satélites%'
ORDER BY nombre_estudio_clase;

-- 5. Verificar charset de las tablas
SHOW CREATE TABLE estudios;
SHOW CREATE TABLE estudios_casas;
SHOW CREATE TABLE estudios_categorias;
SHOW CREATE TABLE estudios_clases;
SHOW CREATE TABLE estudios_auditoria;
