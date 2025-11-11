-- ============================================================================
-- MIGRACIÓN: Renombrar columnas de la tabla ventas
-- DESCRIPCIÓN: Cambiar nombres de columnas para consistencia con otras tablas
-- PROYECTO: Valora.vip
-- FECHA: 2025-11-08
-- ============================================================================

USE valora_db;

-- Verificar que la tabla existe
SELECT 'Verificando existencia de tabla ventas...' as paso;

-- Eliminar restricciones de clave foránea antes de renombrar
ALTER TABLE ventas DROP FOREIGN KEY fk_ventas_usuario;
ALTER TABLE ventas DROP FOREIGN KEY fk_ventas_credencial;

-- Eliminar índice único antes de renombrar
ALTER TABLE ventas DROP INDEX unique_earning_period;

-- Renombrar columnas
ALTER TABLE ventas CHANGE COLUMN usuario_id id_usuario INT(11) NOT NULL COMMENT 'ID del modelo/usuario (FK a usuarios.id_usuario)';
ALTER TABLE ventas CHANGE COLUMN credencial_id id_credencial INT(11) NOT NULL COMMENT 'ID de la credencial/plataforma (FK a credenciales.id_credencial)';
ALTER TABLE ventas CHANGE COLUMN plataforma_id id_pagina INT(11) NULL COMMENT 'ID de plataforma (opcional, referencia a id_pagina)';

-- Recrear restricción única con los nuevos nombres
ALTER TABLE ventas ADD UNIQUE KEY unique_earning_period (id_usuario, id_credencial, period_start, period_end);

-- Recrear claves foráneas con los nuevos nombres
ALTER TABLE ventas 
    ADD CONSTRAINT fk_ventas_usuario 
        FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE;

ALTER TABLE ventas 
    ADD CONSTRAINT fk_ventas_credencial 
        FOREIGN KEY (id_credencial) 
        REFERENCES credenciales(id_credencial) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE;

-- Verificar cambios
SELECT 'Columnas renombradas exitosamente' as resultado;
SHOW COLUMNS FROM ventas;
