-- ============================================================================
-- SCRIPT DE DEPLOY PARA PRODUCCIÓN - TABLA VENTAS
-- BASE DE DATOS: u179023609_orvlvi (Hostinger)
-- PROYECTO: Valora.vip
-- FECHA: 2025-11-08
-- ============================================================================

-- INSTRUCCIONES:
-- 1. Conectarse a la base de datos de producción en Hostinger
-- 2. Ejecutar este script completo
-- 3. Verificar que la tabla se creó correctamente con DESCRIBE earnings;
-- 4. Verificar las claves foráneas con las consultas al final del script

USE u179023609_orvlvi;

-- Verificar que las tablas relacionadas existen
SELECT 'Verificando existencia de tabla usuarios...' as paso;
SELECT COUNT(*) as usuarios_existe 
FROM information_schema.tables 
WHERE table_schema = 'u179023609_orvlvi' 
AND table_name = 'usuarios';

SELECT 'Verificando existencia de tabla credenciales...' as paso;
SELECT COUNT(*) as credenciales_existe 
FROM information_schema.tables 
WHERE table_schema = 'u179023609_orvlvi' 
AND table_name = 'credenciales';

-- Crear tabla ventas
SELECT 'Creando tabla ventas...' as paso;

CREATE TABLE IF NOT EXISTS ventas (
    -- ID principal
    id INT(11) NOT NULL AUTO_INCREMENT,
    
    -- Relación con el modelo (usuario)
    usuario_id INT(11) NOT NULL COMMENT 'ID del modelo/usuario (FK a usuarios.id_usuario)',
    
    -- Relación con la credencial de plataforma
    credencial_id INT(11) NOT NULL COMMENT 'ID de la credencial/plataforma (FK a credenciales.id_credencial)',
    
    -- ID de plataforma (opcional, para casos especiales)
    plataforma_id INT(11) NULL COMMENT 'ID de plataforma (opcional, referencia a id_pagina)',
    
    -- Rango de fechas del periodo de ingresos
    period_start DATETIME NOT NULL COMMENT 'Inicio del periodo de ganancias',
    period_end DATETIME NOT NULL COMMENT 'Fin del periodo de ganancias',
    
    -- Total de ingresos en el periodo
    total_earnings DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total de ganancias en el periodo (USD)',
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro del earning',
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
    
    -- Clave primaria
    PRIMARY KEY (id),
    
    -- Índices para optimización de consultas
    INDEX idx_usuario (usuario_id),
    INDEX idx_credencial (credencial_id),
    INDEX idx_periodo (period_start, period_end),
    INDEX idx_usuario_periodo (usuario_id, period_start, period_end),
    
    -- Restricción única: Un modelo no puede tener dos registros idénticos del mismo periodo en la misma credencial
    UNIQUE KEY unique_earning_period (usuario_id, credencial_id, period_start, period_end),
    
    -- Claves foráneas con eliminación en cascada
    CONSTRAINT fk_ventas_usuario 
        FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id_usuario) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_ventas_credencial 
        FOREIGN KEY (credencial_id) 
        REFERENCES credenciales(id_credencial) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    -- Restricciones de validación
    CONSTRAINT chk_positive_earnings 
        CHECK (total_earnings >= 0),
    
    CONSTRAINT chk_valid_period 
        CHECK (period_end >= period_start)
        
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Registro de ingresos totales de modelos por plataforma y periodo';

-- Mensaje de confirmación
SELECT '✅ Tabla ventas creada exitosamente en producción' AS resultado;

-- ============================================================================
-- CONSULTAS DE VERIFICACIÓN (ejecutar después del deploy)
-- ============================================================================

-- 1. Ver estructura de la tabla
SELECT 'Estructura de la tabla ventas:' as paso;
DESCRIBE ventas;

-- 2. Verificar claves foráneas
SELECT 'Verificando claves foráneas:' as paso;
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'u179023609_orvlvi'
AND TABLE_NAME = 'ventas'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 3. Ver definición completa de la tabla
SELECT 'Definición completa de la tabla:' as paso;
SHOW CREATE TABLE ventas;

-- 4. Verificar que la tabla está vacía (debe devolver 0)
SELECT 'Conteo de registros en ventas:' as paso;
SELECT COUNT(*) as total_registros FROM ventas;

SELECT '✅ Deploy completado - Tabla ventas lista para usar' AS estado_final;
