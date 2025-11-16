-- Migración: Actualizar módulo de Ventas Stripchat
-- Fecha: 2025-11-15
-- Descripción: Renombrar ventasModelo.php a ventasStripchat.php y actualizar información

-- Actualizar el módulo existente con la nueva ruta y datos completos
UPDATE modulos 
SET 
    ruta_completa = 'views\\ventas\\ventasStripchat.php',
    titulo = 'Importación Stripchat',
    subtitulo = 'Resumen diario de ventas importadas desde Stripchat',
    icono = '💰',
    categoria = 'ventas'
WHERE clave = 'views_ventas_ventasModelo';

-- Eliminar registro duplicado si existe
DELETE FROM modulos 
WHERE clave = 'views_ventas_ventasStripchat' AND id != 21;

-- Actualizar la clave para reflejar el nuevo nombre
UPDATE modulos
SET clave = 'views_ventas_ventasStripchat'
WHERE id = 21;

-- Actualizar permisos si existen con la clave antigua
UPDATE roles_permisos
SET modulo = 'views_ventas_ventasStripchat'
WHERE modulo = 'views_ventas_ventasModelo';

-- Verificar resultado
SELECT id, clave, titulo, subtitulo, ruta_completa, categoria, icono
FROM modulos
WHERE clave = 'views_ventas_ventasStripchat';
