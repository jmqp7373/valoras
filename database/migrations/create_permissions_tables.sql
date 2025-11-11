-- ============================================
-- Creación de tablas para sistema de permisos
-- Proyecto: Valora.vip
-- Fecha: 2025-11-08
-- ============================================

-- Tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles por defecto
INSERT INTO roles (nombre, descripcion) VALUES
('superadmin', 'Acceso total al sistema'),
('admin', 'Administrador del estudio'),
('promotor', 'Promotor de modelos'),
('modelo', 'Modelo webcam'),
('soporte', 'Equipo de soporte técnico')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);

-- Agregar campo id_rol a tabla usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS id_rol INT DEFAULT 4,
ADD CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id);

-- Tabla de permisos por rol
CREATE TABLE IF NOT EXISTS roles_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    puede_ver TINYINT(1) DEFAULT 0,
    puede_editar TINYINT(1) DEFAULT 0,
    puede_eliminar TINYINT(1) DEFAULT 0,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rol_modulo (id_rol, modulo),
    INDEX idx_rol (id_rol),
    INDEX idx_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de permisos individuales por usuario
CREATE TABLE IF NOT EXISTS usuarios_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    puede_ver TINYINT(1) DEFAULT 0,
    puede_editar TINYINT(1) DEFAULT 0,
    puede_eliminar TINYINT(1) DEFAULT 0,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_modulo (id_usuario, modulo),
    INDEX idx_usuario (id_usuario),
    INDEX idx_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar permisos por defecto para superadmin (acceso total)
INSERT INTO roles_permisos (id_rol, modulo, puede_ver, puede_editar, puede_eliminar) VALUES
(1, 'usuarios', 1, 1, 1),
(1, 'finanzas', 1, 1, 1),
(1, 'ventas', 1, 1, 1),
(1, 'tickets', 1, 1, 1),
(1, 'credenciales', 1, 1, 1),
(1, 'permisos', 1, 1, 1),
(1, 'reportes', 1, 1, 1)
ON DUPLICATE KEY UPDATE 
    puede_ver = VALUES(puede_ver),
    puede_editar = VALUES(puede_editar),
    puede_eliminar = VALUES(puede_eliminar);

-- Insertar permisos por defecto para admin
INSERT INTO roles_permisos (id_rol, modulo, puede_ver, puede_editar, puede_eliminar) VALUES
(2, 'usuarios', 1, 1, 0),
(2, 'finanzas', 1, 1, 0),
(2, 'ventas', 1, 1, 0),
(2, 'tickets', 1, 1, 0),
(2, 'credenciales', 1, 1, 0),
(2, 'permisos', 1, 0, 0),
(2, 'reportes', 1, 0, 0)
ON DUPLICATE KEY UPDATE 
    puede_ver = VALUES(puede_ver),
    puede_editar = VALUES(puede_editar),
    puede_eliminar = VALUES(puede_eliminar);

-- Insertar permisos por defecto para modelo
INSERT INTO roles_permisos (id_rol, modulo, puede_ver, puede_editar, puede_eliminar) VALUES
(4, 'usuarios', 0, 0, 0),
(4, 'finanzas', 1, 0, 0),
(4, 'ventas', 1, 0, 0),
(4, 'tickets', 1, 1, 0),
(4, 'credenciales', 0, 0, 0),
(4, 'permisos', 0, 0, 0),
(4, 'reportes', 0, 0, 0)
ON DUPLICATE KEY UPDATE 
    puede_ver = VALUES(puede_ver),
    puede_editar = VALUES(puede_editar),
    puede_eliminar = VALUES(puede_eliminar);
