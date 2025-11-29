-- =====================================================
-- Migración: estudios_auditoria → estudios_ediciones
-- Fecha: 2025-01-26
-- Descripción: Renombrar tabla y estandarizar columnas
-- =====================================================

-- PASO 1: Renombrar la tabla
RENAME TABLE estudios_auditoria TO estudios_ediciones;

-- PASO 2: Verificar y actualizar estructura de columnas
-- (ejecutar solo las que apliquen según la estructura actual)

-- Renombrar columnas si tienen nombres diferentes
ALTER TABLE estudios_ediciones 
    CHANGE COLUMN tabla tabla_afectada VARCHAR(50) NOT NULL;

ALTER TABLE estudios_ediciones 
    CHANGE COLUMN datos_anteriores datos_anteriores_json JSON;

ALTER TABLE estudios_ediciones 
    CHANGE COLUMN datos_nuevos datos_nuevos_json JSON;

-- Agregar columnas faltantes si no existen
-- (comentar las que ya existan)

-- ALTER TABLE estudios_ediciones 
--     ADD COLUMN ip_usuario VARCHAR(45);

-- ALTER TABLE estudios_ediciones 
--     ADD COLUMN fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Modificar descripcion para soportar textos más largos
ALTER TABLE estudios_ediciones 
    MODIFY COLUMN descripcion TEXT;

-- =====================================================
-- VERIFICACIÓN
-- =====================================================
SHOW TABLES LIKE 'estudios_ediciones';
DESCRIBE estudios_ediciones;


