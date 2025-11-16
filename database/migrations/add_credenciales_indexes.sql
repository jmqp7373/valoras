-- ============================================
-- Migración: Índices para optimización del módulo de credenciales
-- Proyecto: Valora.vip
-- Fecha: 2025-11-15
-- Descripción: Agregar índices para mejorar el rendimiento de consultas
--              en el módulo de administración de credenciales
-- ============================================

-- Verificar si los índices ya existen antes de crearlos

-- 1. Índice sobre credenciales.id_usuario
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'credenciales' 
               AND index_name = 'idx_id_usuario');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_id_usuario ya existe'' AS Info', 
                               'CREATE INDEX idx_id_usuario ON credenciales(id_usuario)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Índice sobre credenciales.id_pagina
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'credenciales' 
               AND index_name = 'idx_id_pagina');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_id_pagina ya existe'' AS Info', 
                               'CREATE INDEX idx_id_pagina ON credenciales(id_pagina)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Índice sobre credenciales.id_cuenta_estudio
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'credenciales' 
               AND index_name = 'idx_id_cuenta_estudio');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_id_cuenta_estudio ya existe'' AS Info', 
                               'CREATE INDEX idx_id_cuenta_estudio ON credenciales(id_cuenta_estudio)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Índice sobre credenciales.eliminado
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'credenciales' 
               AND index_name = 'idx_eliminado');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_eliminado ya existe'' AS Info', 
                               'CREATE INDEX idx_eliminado ON credenciales(eliminado)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Índice sobre credenciales.usuario (para búsquedas)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'credenciales' 
               AND index_name = 'idx_usuario');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_usuario ya existe'' AS Info', 
                               'CREATE INDEX idx_usuario ON credenciales(usuario)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Índice sobre usuarios.nombres (para búsquedas)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'usuarios' 
               AND index_name = 'idx_nombres');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_nombres ya existe'' AS Info', 
                               'CREATE INDEX idx_nombres ON usuarios(nombres)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. Índice sobre usuarios.apellidos (para búsquedas)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'usuarios' 
               AND index_name = 'idx_apellidos');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_apellidos ya existe'' AS Info', 
                               'CREATE INDEX idx_apellidos ON usuarios(apellidos)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. Índice compuesto para optimizar filtros combinados
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'credenciales' 
               AND index_name = 'idx_filtros_combinados');
SET @sqlstmt := IF(@exist > 0, 'SELECT ''Índice idx_filtros_combinados ya existe'' AS Info', 
                               'CREATE INDEX idx_filtros_combinados ON credenciales(id_pagina, eliminado, id_cuenta_estudio)');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificación de índices creados
SELECT 
    '✓ Índices creados/verificados correctamente' AS Estado,
    COUNT(*) AS Total_Indices
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE table_schema = DATABASE() 
AND table_name IN ('credenciales', 'usuarios')
AND index_name LIKE 'idx_%';

SELECT 'Detalle de índices en tabla credenciales:' AS Info;
SELECT 
    index_name AS Nombre_Indice,
    column_name AS Columna,
    seq_in_index AS Secuencia
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE table_schema = DATABASE() 
AND table_name = 'credenciales'
AND index_name LIKE 'idx_%'
ORDER BY index_name, seq_in_index;

SELECT 'Detalle de índices en tabla usuarios:' AS Info;
SELECT 
    index_name AS Nombre_Indice,
    column_name AS Columna,
    seq_in_index AS Secuencia
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE table_schema = DATABASE() 
AND table_name = 'usuarios'
AND index_name LIKE 'idx_%'
ORDER BY index_name, seq_in_index;
