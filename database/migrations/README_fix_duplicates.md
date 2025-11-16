# Corrección de Duplicados en ventas_strip

## 📋 Problema Identificado

La tabla `ventas_strip` estaba generando registros duplicados para el mismo período de pago debido a:
- Falta de índice único compuesto
- Lógica de verificación insuficiente
- Múltiples importaciones del mismo período

## 🔧 Solución Implementada

### 1. Migración de Base de Datos

**Archivo:** `database/migrations/fix_ventas_strip_duplicates.sql`

Ejecutar en phpMyAdmin o consola MySQL:

```bash
mysql -u root -p valora_db < database/migrations/fix_ventas_strip_duplicates.sql
```

Esta migración:
- ✅ Elimina registros duplicados (conserva solo el más reciente)
- ✅ Agrega índice único: `unique_period_per_model (id_credencial, id_usuario, period_start, period_end)`
- ✅ Verifica que no queden duplicados

### 2. Código del Controlador

**Archivo:** `controllers/VentasController.php`

**Cambios aplicados:**

#### A) Nueva función UPSERT
```php
private function upsertVenta($data)
```
- Usa `INSERT ... ON DUPLICATE KEY UPDATE`
- Garantiza una sola fila por período/credencial/modelo
- Retorna: `['inserted' => bool, 'updated' => bool, 'unchanged' => bool]`

#### B) Nuevas funciones de importación
- `importarPeriodoActual()` - Importa período actual de todas las cuentas
- `importarCuentaEstudioPeriodo($id)` - Importa período de una cuenta específica
- `calcularVentasDiarias()` - Reconstruye ventas diarias usando deltas

#### C) Todas las importaciones ahora usan UPSERT
- No más duplicados automáticos
- Actualiza registros existentes en lugar de crear nuevos
- Cuenta correctamente: nuevos, actualizados, sin cambios

## 📊 Estructura del Índice Único

```sql
unique_period_per_model (
    id_credencial,
    id_usuario,
    period_start,
    period_end
)
```

Esto garantiza que **nunca** habrá dos filas con:
- Misma credencial (modelo)
- Mismo usuario
- Mismo inicio de período
- Mismo fin de período

## ✅ Verificación Post-Migración

### 1. Verificar que no hay duplicados:

```sql
SELECT 
    id_credencial,
    id_usuario,
    period_start,
    period_end,
    COUNT(*) as duplicados
FROM ventas_strip
GROUP BY id_credencial, id_usuario, period_start, period_end
HAVING duplicados > 1;
```

**Resultado esperado:** 0 filas (sin duplicados)

### 2. Verificar índice creado:

```sql
SHOW INDEX FROM ventas_strip WHERE Key_name = 'unique_period_per_model';
```

**Resultado esperado:** 4 filas (una por cada columna del índice)

### 3. Probar importación:

1. Ir a `/views/ventas/ventasStripchat.php`
2. Hacer clic en "Importar Período Actual"
3. Ejecutar dos veces seguidas
4. Verificar que la segunda vez solo actualiza, no inserta nuevos

## 🔄 Flujo de UPSERT

```
API Stripchat → Controlador
                     ↓
              upsertVenta()
                     ↓
          ¿Existe período?
         /              \
       SÍ                NO
        ↓                 ↓
    UPDATE            INSERT
  (actualiza)       (crea nuevo)
        ↓                 ↓
   return 2          return 1
  (actualizado)       (nuevo)
```

## 🎯 Beneficios

✅ **Sin duplicados:** Índice único lo garantiza  
✅ **Rendimiento:** UPSERT es más rápido que SELECT + INSERT/UPDATE  
✅ **Confiabilidad:** Totales correctos en cálculos diarios  
✅ **Trazabilidad:** `updated_at` se actualiza automáticamente  
✅ **Idempotencia:** Importar N veces = mismo resultado  

## ⚠️ IMPORTANTE

**Antes de aplicar en producción:**

1. ✅ Hacer backup de la tabla `ventas_strip`
2. ✅ Ejecutar primero en entorno de desarrollo
3. ✅ Verificar que los totales cuadran
4. ✅ Probar importación varias veces
5. ✅ Solo después aplicar en producción

## 📝 Comandos Útiles

### Backup antes de migración:
```bash
mysqldump -u root -p valora_db ventas_strip > backup_ventas_strip_$(date +%Y%m%d).sql
```

### Contar duplicados actuales:
```sql
SELECT COUNT(*) FROM ventas_strip;  -- Total antes

-- Ejecutar migración

SELECT COUNT(*) FROM ventas_strip;  -- Total después (debería ser menor)
```

### Restaurar backup si algo falla:
```bash
mysql -u root -p valora_db < backup_ventas_strip_YYYYMMDD.sql
```

## 🐛 Troubleshooting

### Error: "Duplicate entry"
**Causa:** Ya existe un índice único o hay duplicados reales  
**Solución:** 
1. Ejecutar primero el DELETE de duplicados
2. Verificar que no queden duplicados
3. Luego crear el índice

### Error: "Key too long"
**Causa:** Las columnas `period_start`/`period_end` son muy largas  
**Solución:** El índice usa `DATETIME` que es compatible, no debería ocurrir

### Error: "Can't DROP index"
**Causa:** El índice no existe  
**Solución:** Normal si es la primera vez, ignorar y continuar

---

**Autor:** Jorge Mauricio Quiñónez Pérez  
**Fecha:** 2025-11-16  
**Versión:** 1.0
