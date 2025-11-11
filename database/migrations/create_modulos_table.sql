-- Migración: Crear tabla de módulos para gestión de nombres descriptivos
-- Proyecto: Valora.vip
-- Fecha: 2025-11-09

CREATE TABLE IF NOT EXISTS modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(255) UNIQUE NOT NULL COMMENT 'Identificador único generado de la ruta (ej: views_admin_index_php)',
    ruta_completa VARCHAR(500) NOT NULL COMMENT 'Ruta completa del archivo (ej: views\admin\index.php)',
    nombre_descriptivo VARCHAR(255) NULL COMMENT 'Nombre amigable para mostrar en la interfaz',
    categoria VARCHAR(100) NOT NULL COMMENT 'Categoría del módulo (admin, login, finanzas, etc.)',
    activo TINYINT(1) DEFAULT 1 COMMENT '1=visible en panel, 0=archivado',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo),
    INDEX idx_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de módulos/vistas del sistema con nombres descriptivos editables';

-- Sincronizar módulos existentes con los archivos actuales del sistema
-- Este procedimiento se ejecutará automáticamente al cargar el panel de permisos
