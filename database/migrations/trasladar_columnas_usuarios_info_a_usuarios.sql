-- =====================================================
-- Migración: Trasladar columnas de usuarios_info a usuarios
-- Fecha: 2025-11-28
-- Descripción: Consolidar datos de usuarios_info en tabla usuarios
-- =====================================================

-- PASO 1: Agregar las columnas a la tabla usuarios (si no existen)
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS id_estudio INT(11) NULL AFTER id_rol,
ADD COLUMN IF NOT EXISTS id_referente INT(11) NULL AFTER id_estudio,
ADD COLUMN IF NOT EXISTS celular VARCHAR(20) NULL AFTER id_referente,
ADD COLUMN IF NOT EXISTS cedula VARCHAR(20) NULL AFTER celular,
ADD COLUMN IF NOT EXISTS email VARCHAR(255) NULL AFTER cedula,
ADD COLUMN IF NOT EXISTS nivel_orden INT(11) NULL DEFAULT 99 AFTER email;

-- PASO 1.1: Mover la columna 'estado' al final (después de nivel_orden)
ALTER TABLE usuarios MODIFY COLUMN estado INT(11) NULL DEFAULT 1 AFTER nivel_orden;

-- PASO 2: Copiar los datos de usuarios_info a usuarios
UPDATE usuarios u
INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
SET 
    u.id_estudio = ui.id_estudio,
    u.id_referente = ui.id_referente,
    u.celular = ui.celular,
    u.cedula = ui.cedula,
    u.email = ui.email,
    u.nivel_orden = ui.nivel_orden;

-- PASO 3: Agregar índices para mejorar rendimiento
ALTER TABLE usuarios
ADD INDEX IF NOT EXISTS idx_id_estudio (id_estudio),
ADD INDEX IF NOT EXISTS idx_id_referente (id_referente),
ADD INDEX IF NOT EXISTS idx_nivel_orden (nivel_orden),
ADD INDEX IF NOT EXISTS idx_email (email);

-- PASO 4: Agregar foreign keys si las tablas relacionadas existen
-- (Descomentar solo si las tablas relacionadas están correctas)
-- ALTER TABLE usuarios
-- ADD CONSTRAINT fk_usuarios_estudio 
--     FOREIGN KEY (id_estudio) REFERENCES estudios(id_estudio) 
--     ON DELETE SET NULL ON UPDATE CASCADE;

-- ALTER TABLE usuarios
-- ADD CONSTRAINT fk_usuarios_referente 
--     FOREIGN KEY (id_referente) REFERENCES usuarios(id_usuario) 
--     ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Verificar estructura de usuarios
DESCRIBE usuarios;

-- Verificar cantidad de registros actualizados
SELECT 
    COUNT(*) as total_usuarios,
    COUNT(id_estudio) as con_estudio,
    COUNT(id_referente) as con_referente,
    COUNT(celular) as con_celular,
    COUNT(cedula) as con_cedula,
    COUNT(email) as con_email,
    COUNT(nivel_orden) as con_nivel_orden
FROM usuarios;

-- Comparar con usuarios_info para verificar integridad
SELECT 
    'usuarios' as tabla, COUNT(*) as registros FROM usuarios
UNION ALL
SELECT 
    'usuarios_info' as tabla, COUNT(*) as registros FROM usuarios_info;

-- Ver algunos registros de ejemplo
SELECT 
    id_usuario, 
    usuario, 
    id_estudio, 
    id_referente, 
    celular, 
    cedula, 
    email, 
    nivel_orden 
FROM usuarios 
LIMIT 10;

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================
-- 1. HACER BACKUP de la base de datos ANTES de ejecutar
-- 2. Ejecutar en DESARROLLO primero
-- 3. Verificar que los datos se copiaron correctamente
-- 4. NO eliminar usuarios_info hasta estar 100% seguro
-- 5. Actualizar código PHP para usar usuarios en lugar de usuarios_info
-- 6. Las foreign keys están comentadas por seguridad
