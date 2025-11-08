-- Script para agregar campos extendidos al perfil de usuario en Valora.vip
-- Basado en el antiguo formulario de entrevistas de FLYCAM
-- Ejecutar este script para actualizar la tabla usuarios con todos los campos necesarios

USE valora_db;

-- Agregar columnas de información personal extendida
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS tipo_sangre VARCHAR(10) NULL COMMENT 'Tipo de sangre del usuario (O+, A-, etc)',
ADD COLUMN IF NOT EXISTS direccion TEXT NULL COMMENT 'Dirección de residencia completa',
ADD COLUMN IF NOT EXISTS ciudad VARCHAR(100) NULL COMMENT 'Ciudad de residencia';

-- Agregar columnas de contacto de emergencia
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS contacto_emergencia_nombre VARCHAR(255) NULL COMMENT 'Nombre completo del contacto de emergencia',
ADD COLUMN IF NOT EXISTS contacto_emergencia_parentesco VARCHAR(100) NULL COMMENT 'Relación familiar con el contacto',
ADD COLUMN IF NOT EXISTS contacto_emergencia_telefono VARCHAR(20) NULL COMMENT 'Teléfono del contacto de emergencia';

-- Agregar columnas de salud
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS alergias TEXT NULL COMMENT 'Alergias o condiciones médicas especiales',
ADD COLUMN IF NOT EXISTS certificado_medico VARCHAR(255) NULL COMMENT 'Ruta del archivo del certificado médico';

-- Agregar columnas de información bancaria
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS banco_nombre VARCHAR(100) NULL COMMENT 'Nombre del banco',
ADD COLUMN IF NOT EXISTS banco_tipo_cuenta ENUM('Ahorros', 'Corriente') NULL COMMENT 'Tipo de cuenta bancaria',
ADD COLUMN IF NOT EXISTS banco_numero_cuenta VARCHAR(50) NULL COMMENT 'Número de cuenta bancaria';

-- Agregar columnas para días de descanso (JSON array)
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS dias_descanso JSON NULL COMMENT 'Array JSON con días de descanso: ["Lunes", "Martes", etc]';

-- Agregar columnas para fotografías
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS foto_perfil VARCHAR(255) NULL COMMENT 'Ruta de la foto de perfil principal',
ADD COLUMN IF NOT EXISTS foto_con_cedula VARCHAR(255) NULL COMMENT 'Ruta de foto con cédula en mano',
ADD COLUMN IF NOT EXISTS foto_cedula_frente VARCHAR(255) NULL COMMENT 'Ruta de foto de cédula por el frente',
ADD COLUMN IF NOT EXISTS foto_cedula_reverso VARCHAR(255) NULL COMMENT 'Ruta de foto de cédula por el reverso';

-- Agregar columna de notas
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS notas TEXT NULL COMMENT 'Observaciones internas o personales del artista';

-- Agregar columna de progreso del perfil
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS progreso_perfil INT DEFAULT 0 COMMENT 'Porcentaje de completitud del perfil (0-100)';

-- Verificar que la columna id_estudio existe (referencia a tabla estudios)
-- Si no existe, agregarla
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS id_estudio INT NULL COMMENT 'ID del estudio al que pertenece el artista';

-- Crear índices para mejorar el rendimiento en búsquedas
CREATE INDEX IF NOT EXISTS idx_ciudad ON usuarios(ciudad);
CREATE INDEX IF NOT EXISTS idx_banco ON usuarios(banco_nombre);
CREATE INDEX IF NOT EXISTS idx_progreso ON usuarios(progreso_perfil);

-- Verificar las columnas agregadas
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'valora_db' 
AND TABLE_NAME = 'usuarios'
AND COLUMN_NAME IN (
    'tipo_sangre', 'direccion', 'ciudad',
    'contacto_emergencia_nombre', 'contacto_emergencia_parentesco', 'contacto_emergencia_telefono',
    'alergias', 'certificado_medico',
    'banco_nombre', 'banco_tipo_cuenta', 'banco_numero_cuenta',
    'dias_descanso',
    'foto_perfil', 'foto_con_cedula', 'foto_cedula_frente', 'foto_cedula_reverso',
    'notas', 'progreso_perfil', 'id_estudio'
);
