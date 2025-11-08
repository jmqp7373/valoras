# 📊 Tabla `ventas` - Documentación

## 📋 Descripción General

La tabla `ventas` registra los **ingresos totales** de cada modelo (usuario) en cada plataforma (credencial) durante un periodo específico.

**Características principales:**
- ✅ Relaciona modelos con plataformas mediante IDs
- ✅ Registra rangos de fechas con periodo de inicio y fin
- ✅ Almacena solo el total de ingresos (sin desglose por tipo)
- ✅ Incluye validaciones de integridad y consistencia
- ✅ Optimizada con índices para consultas eficientes

---

## 🗃️ Estructura de la Tabla

| Campo | Tipo | Null | Descripción |
|-------|------|------|-------------|
| `id` | INT(11) | NO | ID único autoincremental (PK) |
| `usuario_id` | INT(11) | NO | ID del modelo/usuario (FK → usuarios.id_usuario) |
| `credencial_id` | INT(11) | NO | ID de la credencial/plataforma (FK → credenciales.id_credencial) |
| `plataforma_id` | INT(11) | YES | ID de plataforma (opcional, referencia id_pagina) |
| `period_start` | DATETIME | NO | Fecha y hora de inicio del periodo |
| `period_end` | DATETIME | NO | Fecha y hora de fin del periodo |
| `total_earnings` | DECIMAL(10,2) | NO | Total de ganancias en USD (default: 0.00) |
| `created_at` | TIMESTAMP | NO | Fecha de creación del registro |
| `updated_at` | TIMESTAMP | YES | Fecha de última actualización |

---

## 🔗 Relaciones (Foreign Keys)

### 1. Relación con `usuarios`
```sql
FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario)
ON DELETE CASCADE ON UPDATE CASCADE
```
- **Eliminación en cascada:** Si se elimina un usuario, se eliminan todas sus ventas
- **Actualización en cascada:** Si se actualiza id_usuario, se actualiza en ventas

### 2. Relación con `credenciales`
```sql
FOREIGN KEY (credencial_id) REFERENCES credenciales(id_credencial)
ON DELETE CASCADE ON UPDATE CASCADE
```
- **Eliminación en cascada:** Si se elimina una credencial, se eliminan sus ventas
- **Actualización en cascada:** Si se actualiza id_credencial, se actualiza en ventas

---

## 🔒 Restricciones y Validaciones

### Restricción de Unicidad
```sql
UNIQUE KEY unique_earning_period (usuario_id, credencial_id, period_start, period_end)
```
**Previene:** Registros duplicados del mismo periodo para la misma credencial del mismo usuario

### Validación de Ganancias Positivas
```sql
CHECK (total_earnings >= 0)
```
**Garantiza:** Las ganancias no pueden ser negativas

### Validación de Periodo Válido
```sql
CHECK (period_end >= period_start)
```
**Garantiza:** La fecha de fin no puede ser anterior a la fecha de inicio

---

## 🚀 Índices para Optimización

| Índice | Columnas | Propósito |
|--------|----------|-----------|
| `idx_usuario` | `usuario_id` | Búsquedas rápidas por usuario |
| `idx_credencial` | `credencial_id` | Búsquedas rápidas por credencial |
| `idx_periodo` | `period_start, period_end` | Filtros por rango de fechas |
| `idx_usuario_periodo` | `usuario_id, period_start, period_end` | Consultas combinadas usuario+periodo |

---

## 💡 Ejemplos de Uso

### Insertar un Registro de Ventas
```sql
INSERT INTO ventas (usuario_id, credencial_id, period_start, period_end, total_earnings)
VALUES (1, 5, '2025-11-01 00:00:00', '2025-11-30 23:59:59', 1500.00);
```

### Consultar Ventas de un Modelo Específico
```sql
SELECT 
    v.*,
    u.nombres,
    u.apellidos,
    c.usuario as credencial_usuario
FROM ventas v
JOIN usuarios u ON v.usuario_id = u.id_usuario
JOIN credenciales c ON v.credencial_id = c.id_credencial
WHERE v.usuario_id = 1
ORDER BY v.period_start DESC;
```

### Total de Ventas por Modelo
```sql
SELECT 
    u.id_usuario,
    u.nombres,
    u.apellidos,
    COUNT(v.id) as total_registros,
    SUM(v.total_earnings) as total_ganado
FROM usuarios u
LEFT JOIN ventas v ON u.id_usuario = v.usuario_id
GROUP BY u.id_usuario
ORDER BY total_ganado DESC;
```

### Ventas por Plataforma en un Periodo
```sql
SELECT 
    c.usuario as plataforma,
    COUNT(v.id) as total_registros,
    SUM(v.total_earnings) as total_ingresos,
    AVG(v.total_earnings) as promedio_por_periodo
FROM ventas v
JOIN credenciales c ON v.credencial_id = c.id_credencial
WHERE v.period_start >= '2025-11-01'
AND v.period_end <= '2025-11-30'
GROUP BY c.id_credencial
ORDER BY total_ingresos DESC;
```

### Actualizar Total de Ventas
```sql
UPDATE ventas 
SET total_earnings = 2750.00,
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1;
```

---

## 📂 Archivos de Migración

### Local (Desarrollo)
**Archivo:** `database/migrations/create_ventas_table.sql`
```bash
# Ejecutar en local
mysql -u root valora_db < database/migrations/create_ventas_table.sql
```

### Producción (Hostinger)
**Archivo:** `database/migrations/deploy_ventas_production.sql`
```bash
# Conectar a producción y ejecutar
mysql -u u179023609_orvlvi -p u179023609_orvlvi < database/migrations/deploy_ventas_production.sql
```

---

## ✅ Verificación Post-Deploy

### 1. Verificar Estructura
```sql
DESCRIBE ventas;
```

### 2. Verificar Claves Foráneas
```sql
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'ventas'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### 3. Ver Definición Completa
```sql
SHOW CREATE TABLE ventas;
```

---

## 🎯 Casos de Uso

### Caso 1: Registro Mensual de Ventas
Un modelo trabaja en OnlyFans (credencial_id = 5) durante noviembre 2025 y genera $1,500 USD:
```sql
INSERT INTO ventas (usuario_id, credencial_id, period_start, period_end, total_earnings)
VALUES (1, 5, '2025-11-01 00:00:00', '2025-11-30 23:59:59', 1500.00);
```

### Caso 2: Consultar Histórico de un Modelo
Ver todas las ventas históricas del modelo ID 1:
```sql
SELECT 
    DATE_FORMAT(period_start, '%Y-%m') as mes,
    c.usuario as plataforma,
    total_earnings
FROM ventas v
JOIN credenciales c ON v.credencial_id = c.id_credencial
WHERE v.usuario_id = 1
ORDER BY v.period_start DESC;
```

### Caso 3: Reporte de Ganancias por Plataforma
Total ganado en cada plataforma en el último trimestre:
```sql
SELECT 
    c.usuario as plataforma,
    SUM(v.total_earnings) as total,
    COUNT(v.id) as periodos
FROM ventas v
JOIN credenciales c ON v.credencial_id = c.id_credencial
WHERE v.period_start >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
GROUP BY c.id_credencial
ORDER BY total DESC;
```

---

## 🛡️ Mejores Prácticas

1. **Periodos Consistentes:** Usar siempre el mismo formato de periodos (ej: mensual, quincenal)
2. **No Duplicar:** La restricción `unique_earning_period` previene duplicados
3. **Validar Fechas:** Asegurar que `period_end >= period_start` antes de insertar
4. **Usar Transacciones:** Para operaciones batch que modifiquen múltiples ventas
5. **Auditoría:** Aprovechar `created_at` y `updated_at` para tracking de cambios

---

## 📅 Historial de Cambios

| Fecha | Versión | Descripción |
|-------|---------|-------------|
| 2025-11-08 | 1.0.0 | Creación inicial de la tabla ventas (renombrada de earnings) |

---

## 👥 Contacto y Soporte

Para dudas o modificaciones sobre esta tabla, contactar al equipo de desarrollo de Valora.vip.
