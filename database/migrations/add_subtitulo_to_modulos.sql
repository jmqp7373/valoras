-- Migración: Agregar columna subtitulo a tabla modulos
-- Fecha: 2025-11-27
-- Descripción: Permitir subtítulos descriptivos para módulos

-- Agregar columna si no existe
ALTER TABLE modulos 
ADD COLUMN IF NOT EXISTS subtitulo VARCHAR(255) NULL 
AFTER titulo;

-- Verificar
DESCRIBE modulos;
