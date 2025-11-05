-- Script para limpiar y eliminar la tabla users si existe
-- Ejecutar este script en phpMyAdmin o línea de comandos MySQL

-- Verificar si la tabla users existe y eliminarla
DROP TABLE IF EXISTS `users`;

-- Verificar la estructura de la tabla usuarios
DESCRIBE `usuarios`;

-- Opcional: Actualizar contraseñas existentes si no están hasheadas
-- NOTA: Ejecutar solo si las contraseñas actuales no están en formato hash
-- UPDATE usuarios SET password = MD5(CONCAT(cedula, '123')) WHERE LENGTH(password) < 20;

-- Mostrar algunos registros de ejemplo
SELECT id_usuario, cedula, nombres, apellidos, email, celular, fecha_creacion 
FROM usuarios 
LIMIT 5;