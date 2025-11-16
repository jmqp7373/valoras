-- Migración: Ampliar campo icono en tabla modulos
-- Fecha: 2025-01-XX
-- Descripción: Aumenta el tamaño del campo icono de VARCHAR(10) a VARCHAR(50)
--              para permitir almacenar código HTML de iconos de Bootstrap

-- Ampliar el campo icono
ALTER TABLE modulos MODIFY icono VARCHAR(50);

-- Actualizar el icono del módulo de credenciales con Bootstrap Icons
UPDATE modulos SET icono = '<i class="bi bi-key"></i>' WHERE clave = 'credenciales_admin';

-- Verificación
SELECT 
    clave, 
    titulo, 
    LENGTH(icono) as longitud_icono, 
    icono 
FROM modulos 
WHERE clave = 'credenciales_admin';
