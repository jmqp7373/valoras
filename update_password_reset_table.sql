-- Script para actualizar la tabla password_reset_tokens para códigos de 6 dígitos
-- Base de datos: valora_db (local) / u179023609_orvlvi (producción)

-- 1. Agregar columna method para indicar si es SMS o email
ALTER TABLE password_reset_tokens 
ADD COLUMN method ENUM('sms', 'email') DEFAULT 'email' AFTER token;

-- 2. Modificar columna token para códigos de 6 dígitos
ALTER TABLE password_reset_tokens 
MODIFY COLUMN token VARCHAR(6) NOT NULL;

-- 3. Verificar estructura final
DESCRIBE password_reset_tokens;

-- 4. Limpiar tokens expirados (opcional)
DELETE FROM password_reset_tokens WHERE expires_at < NOW();