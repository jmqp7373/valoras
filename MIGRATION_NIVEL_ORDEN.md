# Migración: Renombrado de columnas nivel y orden
**Fecha:** 2025-11-12  
**Estado:** ✅ COMPLETADO

## Resumen de Cambios

### 1. Renombrado de Columnas
- ✅ **tabla `usuarios`**: columna `nivel` → `nivel_orden`
- ✅ **tabla `roles`**: columna `orden` → `nivel_orden`

### 2. Actualización de Datos
- ✅ Usuarios con `nivel_orden = 3` → actualizado a `nivel_orden = 1` (Superadmin)
- ✅ Total usuarios actualizados: **6 usuarios**
- ✅ Usuarios con nivel_orden=1 asignados al rol Superadmin (id=26)

### 3. Archivos Modificados

#### Controllers
- ✅ `controllers/CambiarRolController.php`
  - Actualizado para verificar `nivel_orden = 1` en lugar de nombre 'superadmin'
  - Guarda y restaura `nivel_orden` en sesión
  - Actualiza `nivel_orden` en BD al cambiar rol

- ✅ `controllers/RestaurarRolController.php`
  - Restaura `nivel_orden` original al volver a Superadmin
  - Limpia variables de sesión correctamente

#### Models
- ✅ `models/Permisos.php`
  - Consulta SQL actualizada: `ORDER BY nivel_orden ASC`

#### Components
- ✅ `components/header/header.php`
  - Detección Superadmin: `nivel_orden = 1` en usuarios O roles
  - Query incluye `nivel_orden` de ambas tablas
  - Dropdown carga roles ordenados por `nivel_orden`

### 4. Estructura Final

#### Tabla: roles
```sql
ID  | Nombre       | nivel_orden
----|--------------|------------
26  | superadmin   | 1
2   | admin        | 2
27  | comunicador  | 3
28  | lider        | 4
29  | coordinador  | 5
4   | modelo       | 6
```

#### Usuarios Superadmin (nivel_orden=1)
- User ID: 1, 1054, 2824, 7163, 7407, 8520
- Todos con `id_rol = 26` (superadmin)
- Todos con `nivel_orden = 1`

### 5. Lógica del Dropdown de Rol Máscara

El dropdown ahora funciona correctamente:

1. **Detección de Superadmin:**
   ```php
   if ($rol_actual && ($rol_actual['usuario_nivel_orden'] == 1 || $rol_actual['nivel_orden'] == 1))
   ```

2. **Carga de Roles:**
   ```sql
   SELECT id, nombre, nivel_orden FROM roles ORDER BY nivel_orden ASC
   ```

3. **Cambio de Rol:**
   - Guarda `nivel_orden` original en sesión
   - Actualiza `id_rol` Y `nivel_orden` en BD temporalmente
   - Al restaurar, vuelve a `nivel_orden = 1` (Superadmin)

4. **Ventaja:**
   - Ya no depende del nombre del rol ('superadmin')
   - Usa relación numérica clara: `nivel_orden = 1` = privilegios máximos
   - Permite volver a Superadmin desde cualquier rol de prueba

### 6. Próximos Pasos para Probar

1. Iniciar sesión con usuario ID=1
2. Verificar que aparece dropdown de roles en header
3. Cambiar a rol "Admin" (nivel_orden=2)
4. Verificar permisos restringidos en PermissionsPanel
5. Usar botón "Restaurar a Superadmin"
6. Confirmar que vuelve a nivel_orden=1 con todos los permisos

## Archivos de Migración
- `database/migrations/rename_nivel_orden_columns.sql`

## Notas Técnicas
- La columna `nivel_orden` en usuarios permite anular el nivel del rol
- Si `usuarios.nivel_orden = 1`, el usuario es Superadmin sin importar su `id_rol`
- Esta flexibilidad permite roles de prueba sin perder privilegios originales
