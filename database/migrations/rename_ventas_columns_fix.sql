-- ============================================================================
-- Script de migración: Renombrar columnas de la tabla ventas
-- Proyecto: Valora.vip
-- Fecha: 2025-11-08
-- Descripción: Estandariza los nombres de columnas para usar id_ como prefijo
-- ============================================================================

USE valora_db;

-- Verificar que la tabla existe
SELECT 'Verificando tabla ventas...' as mensaje;

-- Renombrar columnas de la tabla ventas
ALTER TABLE ventas 
    CHANGE COLUMN usuario_id id_usuario INT(11) NOT NULL COMMENT 'ID del usuario/modelo (FK a usuarios.id_usuario)',
    CHANGE COLUMN credencial_id id_credencial INT(11) NOT NULL COMMENT 'ID de la credencial/plataforma (FK a credenciales.id_credencial)',
    CHANGE COLUMN plataforma_id id_pagina INT(11) NULL COMMENT 'ID de la página/plataforma (FK a paginas.id_pagina)';

SELECT 'Columnas renombradas exitosamente' as resultado;

-- Verificar la nueva estructura
DESCRIBE ventas;
