-- Script para verificar y crear la estructura de la base de datos en producción
-- Base de datos remota: u179023609_orvlvi

-- Verificar si la tabla usuarios existe
SELECT COUNT(*) as table_exists 
FROM information_schema.tables 
WHERE table_schema = 'u179023609_orvlvi' 
AND table_name = 'usuarios';

-- Si necesitas recrear la tabla usuarios (solo ejecutar si no existe)
-- Basado en la estructura que ya tienes funcionando localmente

/*
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario int(11) NOT NULL AUTO_INCREMENT,
    numero_de_cedula varchar(20) DEFAULT NULL,
    usuario varchar(100) DEFAULT NULL,
    disponibilidad varchar(50) DEFAULT NULL,
    id_estado int(11) DEFAULT NULL,
    id_referente int(11) DEFAULT NULL,
    nombres varchar(255) DEFAULT NULL,
    apellidos varchar(255) DEFAULT NULL,
    password varchar(255) DEFAULT NULL,
    codigo_pais varchar(10) DEFAULT NULL,
    celular varchar(20) DEFAULT NULL,
    cedula varchar(20) DEFAULT NULL,
    fecha_de_nacimiento date DEFAULT NULL,
    email varchar(255) DEFAULT NULL,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY cedula (cedula),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- Crear tabla para tokens de reset de password si no existe
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    token varchar(255) NOT NULL,
    expires_at datetime NOT NULL,
    used tinyint(1) DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY token (token),
    KEY user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar cuántos usuarios hay
SELECT COUNT(*) as total_users FROM usuarios;

-- Verificar algunos usuarios de ejemplo
SELECT id_usuario, nombres, apellidos, email, cedula 
FROM usuarios 
LIMIT 5;