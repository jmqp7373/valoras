-- ============================================================================
-- TABLA: earnings
-- DESCRIPCIÓN: Registro de ingresos totales de modelos por plataforma y periodo
-- PROYECTO: Valora.vip
-- FECHA: 2025-11-08
-- ============================================================================

-- Verificar que las tablas relacionadas existen
-- (Comentado para referencia, no ejecutar)
/*
SELECT 'Verificando tabla usuarios...' as mensaje;
SELECT COUNT(*) as usuarios_existe 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'usuarios';

SELECT 'Verificando tabla credenciales...' as mensaje;
SELECT COUNT(*) as credenciales_existe 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'credenciales';
*/

-- Crear tabla earnings
CREATE TABLE IF NOT EXISTS earnings (
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
    CONSTRAINT fk_earnings_usuario 
        FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id_usuario) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_earnings_credencial 
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
SELECT 'Tabla earnings creada exitosamente' AS mensaje;

-- ============================================================================
-- CONSULTAS ÚTILES PARA VERIFICACIÓN
-- ============================================================================

-- Ver estructura de la tabla
-- DESCRIBE earnings;

-- Ver información detallada de la tabla
-- SHOW CREATE TABLE earnings;

-- Verificar claves foráneas
-- SELECT 
--     CONSTRAINT_NAME,
--     TABLE_NAME,
--     COLUMN_NAME,
--     REFERENCED_TABLE_NAME,
--     REFERENCED_COLUMN_NAME
-- FROM information_schema.KEY_COLUMN_USAGE
-- WHERE TABLE_SCHEMA = DATABASE()
-- AND TABLE_NAME = 'earnings'
-- AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ============================================================================
-- EJEMPLO DE USO
-- ============================================================================
/*
-- Insertar un registro de earnings
INSERT INTO earnings (usuario_id, credencial_id, period_start, period_end, total_earnings)
VALUES (1, 5, '2025-11-01 00:00:00', '2025-11-30 23:59:59', 1500.00);

-- Consultar earnings de un modelo específico
SELECT 
    e.*,
    u.nombres,
    u.apellidos,
    c.usuario as credencial_usuario
FROM earnings e
JOIN usuarios u ON e.usuario_id = u.id_usuario
JOIN credenciales c ON e.credencial_id = c.id_credencial
WHERE e.usuario_id = 1
ORDER BY e.period_start DESC;

-- Consultar total de earnings por modelo
SELECT 
    u.id_usuario,
    u.nombres,
    u.apellidos,
    COUNT(e.id) as total_registros,
    SUM(e.total_earnings) as total_ganado
FROM usuarios u
LEFT JOIN earnings e ON u.id_usuario = e.usuario_id
GROUP BY u.id_usuario
ORDER BY total_ganado DESC;
*/
