-- Migración: Actualizar estados NULL a 1 en categorías y clases
-- Fecha: 2025-11-26
-- Descripción: Los registros con estado NULL no se mostraban en la interfaz

-- Actualizar categorías con estado NULL a activo (1)
UPDATE estudios_categorias 
SET estado = 1 
WHERE estado IS NULL;

-- Actualizar clases con estado NULL a activo (1)
UPDATE estudios_clases 
SET estado = 1 
WHERE estado IS NULL;

-- Verificar resultados
SELECT 'Categorías actualizadas' as tabla, COUNT(*) as total 
FROM estudios_categorias 
WHERE estado = 1
UNION ALL
SELECT 'Clases actualizadas' as tabla, COUNT(*) as total 
FROM estudios_clases 
WHERE estado = 1;
