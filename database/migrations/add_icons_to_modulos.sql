-- Agregar iconos a módulos que no tienen
-- Migración: add_icons_to_modulos.sql

-- Módulos de USUARIO
UPDATE modulos SET icono = '👤' WHERE clave = 'mi_perfil' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '⚙️' WHERE clave = 'configuracion_cuenta' AND (icono IS NULL OR icono = '');

-- Módulos de LOGIN (no se mostrarán en el menú, pero por completitud)
UPDATE modulos SET icono = '🔑' WHERE clave = 'establecer_password_inicial' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '🔓' WHERE clave = 'login' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '📧' WHERE clave = 'reset_password_email' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '📝' WHERE clave = 'registro_usuario' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '👥' WHERE clave = 'seleccion_tipo_usuario' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '🔒' WHERE clave = 'cambiar_password_olvidada' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '🎂' WHERE clave = 'restriccion_edad' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '🪪' WHERE clave = 'verificacion_documento' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '📸' WHERE clave = 'verificacion_ocr' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '✏️' WHERE clave = 'verificacion_datos' AND (icono IS NULL OR icono = '');
UPDATE modulos SET icono = '✅' WHERE clave = 'verificacion_aprobacion' AND (icono IS NULL OR icono = '');
