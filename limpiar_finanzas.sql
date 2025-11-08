-- Script para limpiar tabla finanzas y permitir recarga de datos de ejemplo
-- Ejecutar este script en phpMyAdmin o cualquier cliente MySQL

-- Eliminar todos los registros de la tabla finanzas
TRUNCATE TABLE finanzas;

-- Alternativamente, si TRUNCATE da error, usar DELETE:
-- DELETE FROM finanzas;

-- Verificar que la tabla quedó vacía
SELECT COUNT(*) as total_registros FROM finanzas;

-- Los datos de ejemplo se insertarán automáticamente al recargar:
-- - finanzasDashboard.php
-- - O cualquier página que instancie FinanzasController

-- NUEVOS DATOS DE EJEMPLO QUE SE INSERTARÁN:
-- 
-- INGRESOS (Total: $18,500,000):
-- - Ingresos Estudio Fotográfico: $8,000,000
-- - Colaboraciones Empresariales: $6,500,000
-- - Servicios Creativos: $3,000,000
-- - Bonificación Proyecto: $1,000,000
--
-- GASTOS (Total: $11,730,000):
-- - Arriendo Estudio: $4,500,000
-- - Nómina Semana 44: $5,200,000
-- - EPM y Agua: $980,000
-- - Administración Apto: $800,000
-- - Internet y Telefonía: $250,000
--
-- BALANCE GENERAL: +$6,770,000 (POSITIVO)
