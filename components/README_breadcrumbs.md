# 🍞 Componente de Migas de Pan (Breadcrumbs)

## 📋 Descripción
Componente reutilizable para mostrar la navegación breadcrumb en todas las vistas del sistema.

## 🎨 Características
- ✅ Diseño elegante con wrapper degradado
- ✅ Borde izquierdo distintivo color vino
- ✅ Sombra sutil para profundidad
- ✅ Separador personalizado (›)
- ✅ Efectos hover en enlaces
- ✅ Último elemento destacado como activo
- ✅ Responsive y adaptable

## 🔧 Uso

### Ejemplo Básico
```php
<?php
// Definir las migas de pan
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Nombre de la Sección', 'url' => '../section/index.php'],
    ['label' => 'Página Actual', 'url' => null] // null indica página actual (sin enlace)
];

// Incluir el componente
include __DIR__ . '/../../components/breadcrumbs.php';
?>
```

### Estructura de Datos
Cada elemento del array `$breadcrumbs` debe tener:
- **label** (string): Texto que se mostrará en la miga
- **url** (string|null): Ruta del enlace. Si es `null`, se muestra como elemento activo sin enlace

### Ejemplos de Implementación

#### Ejemplo 1: Dashboard → Administración
```php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Administración de Permisos', 'url' => null]
];
include __DIR__ . '/../../components/breadcrumbs.php';
```

#### Ejemplo 2: Dashboard → Ventas → Reporte
```php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Ventas', 'url' => '../ventas/index.php'],
    ['label' => 'Reporte Mensual', 'url' => null]
];
include __DIR__ . '/../../components/breadcrumbs.php';
```

#### Ejemplo 3: Dashboard → Usuario → Mi Perfil
```php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Mi Perfil', 'url' => null]
];
include __DIR__ . '/../../components/breadcrumbs.php';
```

#### Ejemplo 4: Navegación Profunda
```php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Configuración', 'url' => '../config/index.php'],
    ['label' => 'Usuarios', 'url' => '../config/users.php'],
    ['label' => 'Editar Usuario', 'url' => null]
];
include __DIR__ . '/../../components/breadcrumbs.php';
```

## 🎨 Personalización de Estilos

El componente utiliza:
- **Color principal**: `#6A1B1B` (vino)
- **Color secundario**: `#882A57` (rosa oscuro)
- **Separador**: `›` (chevron)
- **Fondo**: Degradado suave de los colores principales
- **Borde**: 4px sólido a la izquierda

## 📁 Ubicación
```
components/
  └── breadcrumbs.php
```

## ✅ Validaciones
- Si no existe `$breadcrumbs` o está vacío, no se renderiza nada
- Si un elemento no tiene `label`, se muestra vacío
- Si el último elemento tiene `url`, se ignora y se muestra como activo

## 🔄 Mantenimiento
Archivo creado: 2025-11-11  
Última actualización: 2025-11-11  
Autor: Jorge Mauricio Quiñónez Pérez
