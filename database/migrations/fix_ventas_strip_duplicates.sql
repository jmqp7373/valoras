-- =====================================================
-- Corrección de duplicados en ventas_strip
-- Fecha: 2025-11-16
-- =====================================================

-- 1. Eliminar registros duplicados (conservar solo el más reciente)
DELETE t1
FROM ventas_strip t1
INNER JOIN ventas_strip t2 
  ON t1.id < t2.id
 AND t1.id_credencial = t2.id_credencial
 AND t1.id_usuario = t2.id_usuario
 AND t1.period_start = t2.period_start
 AND t1.period_end = t2.period_end;

-- 2. Agregar índice único compuesto para evitar duplicados futuros
ALTER TABLE ventas_strip 
ADD UNIQUE KEY unique_period_per_model (
    id_credencial, 
    id_usuario, 
    period_start, 
    period_end
);

-- 3. Verificar que no quedan duplicados
SELECT 
    id_credencial,
    id_usuario,
    period_start,
    period_end,
    COUNT(*) as duplicados
FROM ventas_strip
GROUP BY id_credencial, id_usuario, period_start, period_end
HAVING duplicados > 1;

-- Si la consulta anterior no retorna filas, ¡perfecto! No hay duplicados.
