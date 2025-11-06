-- Script para actualizar la tabla password_reset_tokens para códigos de 6 dígitos
-- Base de datos: valora_db (local) / u179023609_orvlvi (producción)

-- 1. Verificar estructura actual
DESCRIBE password_reset_tokens;

-- 2. Agregar columna cedula si no existe (para vincular con numero_de_cedula)
ALTER TABLE password_reset_tokens 
ADD COLUMN IF NOT EXISTS cedula VARCHAR(20) AFTER user_id;

-- 3. Agregar columna method para indicar si es SMS o email
ALTER TABLE password_reset_tokens 
ADD COLUMN IF NOT EXISTS method ENUM('sms', 'email') DEFAULT 'email' AFTER cedula;

-- 4. Agregar columna used_at para timestamp de uso
ALTER TABLE password_reset_tokens 
ADD COLUMN IF NOT EXISTS used_at TIMESTAMP NULL AFTER used;

-- 5. Modificar columna token para códigos de 6 dígitos
ALTER TABLE password_reset_tokens 
MODIFY COLUMN token VARCHAR(6) NOT NULL;

-- 6. Eliminar restricción UNIQUE del token (permite múltiples códigos activos)
DROP INDEX IF EXISTS token ON password_reset_tokens;

-- 7. Verificar estructura final
DESCRIBE password_reset_tokens;

-- 8. Limpiar tokens expirados (opcional)
DELETE FROM password_reset_tokens WHERE expires_at < NOW();