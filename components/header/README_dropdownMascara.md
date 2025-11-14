# Dropdown Máscara de Rol - Componente

**Versión:** 1.0  
**Fecha:** 2025-11-12  
**Proyecto:** Valora.vip

## Descripción

Componente independiente que permite a usuarios con privilegios de Superadmin (nivel_orden=1) cambiar temporalmente de rol para probar diferentes niveles de permisos sin necesidad de cerrar sesión.

## Archivos del Componente

```
components/header/dropdownMascara.php  (8.1 KB)
assets/css/dropdownMascara.css         (4.3 KB)
```

## Uso

### Inclusión en Header

```php
// En components/header/header.php (línea ~125)
<div class="header-right">
    <!-- Dropdown de Máscara de Rol (componente separado) -->
    <?php include __DIR__ . '/dropdownMascara.php'; ?>
    
    <!-- Menú de usuario -->
    <div class="dropdown">
        ...
    </div>
</div>
```

### Variables Requeridas

El componente necesita que estas variables estén definidas antes de ser incluido:

```php
$es_superadmin          // boolean - Usuario tiene nivel_orden=1
$todos_roles            // array   - Lista de roles (id, nombre, nivel_orden)
$rol_actual             // array   - Rol actual del usuario (id, nombre)
$base_controller_path   // string  - Ruta a carpeta controllers ('controllers/', '../controllers/', etc)
```

### Link del CSS

```php
<!-- En header.php después del dropdown-menu.css -->
<?php if ($es_superadmin): ?>
<link rel="stylesheet" href="<?php echo str_replace('dropdown-menu.css', 'dropdownMascara.css', $css_path); ?>?v=<?php echo filemtime(__DIR__ . '/../../assets/css/dropdownMascara.css'); ?>">
<?php endif; ?>
```

## Características

### Funcionalidad Principal

1. **Detección Automática de Superadmin**
   - Solo se renderiza si `$es_superadmin === true`
   - Verifica que existan roles disponibles

2. **Cambio de Rol Temporal**
   - POST a `CambiarRolController.php`
   - Actualiza `id_rol` y `nivel_orden` en BD
   - Guarda rol original en sesión
   - Recarga página automáticamente

3. **Restauración de Rol**
   - Botón "Restaurar a Superadmin" (solo visible si hay cambio activo)
   - POST a `RestaurarRolController.php`
   - Vuelve a `nivel_orden=1` y `id_rol=26`

### Iconos por Rol

```php
'superadmin'   => '👑'
'admin'        => '🔐'
'comunicador'  => '📢'
'lider'        => '🎯'
'coordinador'  => '⚙️'
'modelo'       => '⭐'
```

### Logging de Debug

JavaScript incluye console.log extensivo:
- 🔄 Inicio de operación
- 📡 Status de respuesta HTTP
- 📄 Contenido de respuesta
- ✅ Éxito de operación
- ❌ Errores capturados

## Estilos CSS

### Clases Principales

```css
.role-switcher-dropdown     /* Contenedor principal */
.role-switcher-btn          /* Botón con gradiente */
.role-dropdown-custom       /* Menú desplegable */
.dropdown-item              /* Items del menú */
.dropdown-item.active       /* Rol actualmente activo */
.restore-btn                /* Botón de restaurar */
```

### Gradiente del Proyecto

```css
background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
```

### Responsive

- Desktop: Padding completo, iconos 18px
- Mobile (<768px): Padding reducido, iconos 16px

## Controllers Backend

### CambiarRolController.php

**Endpoint:** `POST /controllers/CambiarRolController.php`

**Parámetros:**
```
rol_id      (int)    - ID del rol destino
rol_nombre  (string) - Nombre del rol destino
```

**Respuesta JSON:**
```json
{
    "success": true,
    "message": "Rol cambiado temporalmente a admin",
    "rol_id": 2,
    "rol_nombre": "admin",
    "debug": {
        "nivel_orden": 2,
        "rows_affected": 1
    }
}
```

### RestaurarRolController.php

**Endpoint:** `POST /controllers/RestaurarRolController.php`

**Parámetros:** Ninguno

**Respuesta JSON:**
```json
{
    "success": true,
    "message": "Rol restaurado a Superadmin",
    "rol_id": 26,
    "rol_nombre": "Superadmin"
}
```

## Variables de Sesión

```php
$_SESSION['rol_original_id']         // ID del rol antes del cambio
$_SESSION['rol_original_nivel_orden'] // Nivel antes del cambio
$_SESSION['rol_original_nombre']      // Nombre antes del cambio
$_SESSION['rol_prueba_id']            // ID del rol de prueba actual
$_SESSION['rol_prueba_nombre']        // Nombre del rol de prueba
$_SESSION['rol_prueba_nivel_orden']   // Nivel del rol de prueba
```

## Seguridad

1. **Verificación de Autenticación**
   - `isLoggedIn()` en controllers
   
2. **Verificación de Privilegios**
   - Solo usuarios con `nivel_orden=1` pueden usar la función
   
3. **Validación de Datos**
   - Verificación de existencia del rol destino
   - Sanitización de inputs con `htmlspecialchars()`

4. **Cambios Temporales**
   - Solo afecta la sesión actual
   - No modifica permanentemente los roles de usuario

## Troubleshooting

### Dropdown no aparece

1. Verificar que `$es_superadmin = true`
2. Verificar que `$todos_roles` no esté vacío
3. Verificar query de roles en header.php (línea ~70-82)

### Cambio de rol no funciona

1. Abrir consola del navegador (F12)
2. Verificar URL del controller en logs
3. Verificar respuesta del servidor
4. Revisar `error_log` de PHP

### CSS no se aplica

1. Verificar que el archivo existe: `assets/css/dropdownMascara.css`
2. Verificar que el link esté en header.php
3. Limpiar caché del navegador
4. Verificar permisos del archivo

## Changelog

### v1.0 (2025-11-12)
- ✅ Componente separado de header.php
- ✅ CSS externalizado
- ✅ JavaScript con logging extensivo
- ✅ Soporte para restauración de rol
- ✅ Iconos personalizados por rol
- ✅ Responsive design
- ✅ Documentación completa
