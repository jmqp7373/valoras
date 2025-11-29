-- Eliminar columnas duplicadas de usuarios_info
-- Estas columnas ya existen en la tabla usuarios

ALTER TABLE usuarios_info 
DROP COLUMN id_estudio,
DROP COLUMN id_referente,
DROP COLUMN codigo_pais,
DROP COLUMN celular,
DROP COLUMN cedula,
DROP COLUMN email,
DROP COLUMN direccion,
DROP COLUMN ciudad,
DROP COLUMN tipo_sangre,
DROP COLUMN alergias,
DROP COLUMN contacto_emergencia_nombre,
DROP COLUMN contacto_emergencia_parentesco,
DROP COLUMN contacto_emergencia_telefono,
DROP COLUMN dias_descanso,
DROP COLUMN nivel_orden,
DROP COLUMN notas,
DROP COLUMN progreso_perfil;
