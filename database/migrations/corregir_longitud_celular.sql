-- Corrección: Ampliar campo celular en tabla usuarios
-- Fecha: 2025-11-28
-- Razón: usuarios_info.celular es VARCHAR(30), usuarios.celular es VARCHAR(20)
-- =====================================================

-- Ampliar el campo celular para aceptar hasta 50 caracteres
ALTER TABLE usuarios MODIFY COLUMN celular VARCHAR(50) NULL;

-- Reintentar la sincronización del registro con discrepancia
UPDATE usuarios u
INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
SET u.celular = ui.celular
WHERE NOT (u.celular <=> ui.celular);

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Verificar estructura
DESCRIBE usuarios;

-- Verificar que no haya discrepancias
SELECT 
    u.id_usuario,
    u.usuario,
    u.celular as usuarios_celular,
    ui.celular as info_celular,
    CASE WHEN u.celular <=> ui.celular THEN 'OK ✅' ELSE 'ERROR ❌' END as estado
FROM usuarios u
LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
WHERE NOT (u.celular <=> ui.celular);
