-- Migración: Renombrar columnas nivel y orden
-- Fecha: 2025-11-12
-- Descripción: Unificar nomenclatura nivel/orden en usuarios y roles

-- 1. Renombrar columna 'nivel' a 'nivel_orden' en tabla usuarios
ALTER TABLE `usuarios` 
CHANGE COLUMN `nivel` `nivel_orden` INT(11) NULL DEFAULT NULL;

-- 2. Renombrar columna 'orden' a 'nivel_orden' en tabla roles
ALTER TABLE `roles` 
CHANGE COLUMN `orden` `nivel_orden` INT(11) NULL DEFAULT NULL;

-- 3. Actualizar usuarios con nivel_orden = 3 a nivel_orden = 1 (Superadmin)
-- Esto sincroniza con el nivel_orden del rol superadmin en la tabla roles
UPDATE `usuarios` 
SET `nivel_orden` = 1 
WHERE `nivel_orden` = 3;

-- 4. Verificación de los cambios
SELECT 'Usuarios actualizados:' as mensaje, COUNT(*) as total 
FROM usuarios 
WHERE nivel_orden = 1;

SELECT 'Roles con nivel_orden:' as mensaje;
SELECT id, nombre, nivel_orden 
FROM roles 
ORDER BY nivel_orden ASC;
