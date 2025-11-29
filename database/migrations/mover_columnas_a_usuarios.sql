-- ========================================
-- MIGRACIÓN: Mover columnas de usuarios_info a usuarios
-- Fecha: 2025-11-28
-- ========================================

-- PASO 1: Agregar columnas a usuarios si no existen
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS id_estudio INT(11) NULL AFTER id_rol,
ADD COLUMN IF NOT EXISTS id_referente INT(11) NULL AFTER id_estudio,
ADD COLUMN IF NOT EXISTS codigo_pais VARCHAR(5) DEFAULT '+57' AFTER id_referente,
ADD COLUMN IF NOT EXISTS celular VARCHAR(50) NULL AFTER codigo_pais,
ADD COLUMN IF NOT EXISTS cedula VARCHAR(20) NULL AFTER celular,
ADD COLUMN IF NOT EXISTS fecha_nacimiento TIMESTAMP NULL AFTER cedula,
ADD COLUMN IF NOT EXISTS email VARCHAR(255) NULL AFTER fecha_nacimiento,
ADD COLUMN IF NOT EXISTS direccion VARCHAR(255) NULL AFTER email,
ADD COLUMN IF NOT EXISTS ciudad VARCHAR(100) NULL AFTER direccion,
ADD COLUMN IF NOT EXISTS dias_descanso JSON NULL AFTER ciudad,
ADD COLUMN IF NOT EXISTS progreso_perfil INT(11) DEFAULT 0 AFTER dias_descanso;

-- PASO 2: Copiar datos de usuarios_info a usuarios
UPDATE usuarios u
INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
SET 
    u.id_estudio = COALESCE(ui.id_estudio, u.id_estudio),
    u.id_referente = COALESCE(ui.id_referente, u.id_referente),
    u.codigo_pais = COALESCE(ui.codigo_pais, u.codigo_pais),
    u.celular = COALESCE(ui.celular, u.celular),
    u.cedula = COALESCE(ui.cedula, u.cedula),
    u.fecha_nacimiento = COALESCE(ui.fecha_de_nacimiento, u.fecha_nacimiento),
    u.email = COALESCE(ui.email, u.email),
    u.direccion = COALESCE(ui.direccion, u.direccion),
    u.ciudad = COALESCE(ui.ciudad, u.ciudad),
    u.dias_descanso = COALESCE(ui.dias_descanso, u.dias_descanso),
    u.progreso_perfil = COALESCE(ui.progreso_perfil, u.progreso_perfil);

-- PASO 3: Eliminar columnas de usuarios_info
ALTER TABLE usuarios_info
DROP COLUMN IF EXISTS id_estudio,
DROP COLUMN IF EXISTS id_referente,
DROP COLUMN IF EXISTS codigo_pais,
DROP COLUMN IF EXISTS celular,
DROP COLUMN IF EXISTS cedula,
DROP COLUMN IF EXISTS fecha_de_nacimiento,
DROP COLUMN IF EXISTS email,
DROP COLUMN IF EXISTS direccion,
DROP COLUMN IF EXISTS ciudad,
DROP COLUMN IF EXISTS dias_descanso,
DROP COLUMN IF EXISTS progreso_perfil;
