-- =====================================================
-- Migración: Mover columna 'estado' al final de tabla usuarios
-- Fecha: 2025-11-28
-- Descripción: Reorganizar columnas para que 'estado' sea la última
-- =====================================================

-- Mover la columna 'estado' al final (después de nivel_orden)
ALTER TABLE usuarios MODIFY COLUMN estado INT(11) NULL DEFAULT 1 AFTER nivel_orden;

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Verificar el orden de las columnas
DESCRIBE usuarios;

-- =====================================================
-- RESULTADO ESPERADO
-- =====================================================
-- Orden final de columnas:
-- 1. id_usuario
-- 2. fecha_creacion
-- 3. usuario
-- 4. nombre
-- 5. apellido
-- 6. password
-- 7. id_rol
-- 8. id_estudio
-- 9. id_referente
-- 10. celular
-- 11. cedula
-- 12. email
-- 13. nivel_orden
-- 14. estado (ÚLTIMA COLUMNA)
