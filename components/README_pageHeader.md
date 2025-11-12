# 📄 Componente de Cabecera de Página (Page Header)

## 📋 Descripción
Componente reutilizable para mostrar el título principal de una página con icono, descripción y botones de vista opcionales.

## 🎨 Características
- ✅ Título grande y destacado en color vino
- ✅ Icono opcional antes del título
- ✅ Descripción opcional debajo del título
- ✅ Botones de vista agrupados opcionales
- ✅ Estilo consistente en todo el sistema
- ✅ Botón activo con degradado vino/rosa
- ✅ Responsive y centrado

## 🔧 Uso

### Ejemplo Básico (Solo Título)
```php
<?php
$pageHeader = [
    'titulo' => 'Mi Página'
];
include __DIR__ . '/../../components/pageHeader.php';
?>
```

### Ejemplo con Icono y Descripción
```php
<?php
$pageHeader = [
    'titulo' => 'Panel de Control',
    'icono' => '🎛️',
    'descripcion' => 'Gestiona la configuración general del sistema'
];
include __DIR__ . '/../../components/pageHeader.php';
?>
```

### Ejemplo Completo con Botones
```php
<?php
$pageHeader = [
    'titulo' => 'Panel de Permisos',
    'icono' => '⚙️',
    'descripcion' => 'Visualiza y gestiona los permisos de acceso',
    'botones' => [
        [
            'id' => 'btnRoles',
            'label' => '🧩 Permisos por Rol',
            'active' => true
        ],
        [
            'id' => 'btnUsuarios',
            'label' => '👤 Permisos Individuales',
            'active' => false
        ]
    ]
];
include __DIR__ . '/../../components/pageHeader.php';
?>
```

### Ejemplo con Múltiples Botones
```php
<?php
$pageHeader = [
    'titulo' => 'Gestión de Ventas',
    'icono' => '💰',
    'descripcion' => 'Administra las ventas del sistema',
    'botones' => [
        [
            'id' => 'btnActivas',
            'label' => '✅ Ventas Activas',
            'active' => true
        ],
        [
            'id' => 'btnPendientes',
            'label' => '⏳ Pendientes',
            'active' => false
        ],
        [
            'id' => 'btnCompletadas',
            'label' => '✔️ Completadas',
            'active' => false
        ]
    ]
];
include __DIR__ . '/../../components/pageHeader.php';
?>
```

## 📊 Estructura de Datos

### Parámetros de $pageHeader

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `titulo` | string | ✅ Sí | Texto del título principal |
| `icono` | string | ❌ No | Emoji o icono que aparece antes del título |
| `descripcion` | string | ❌ No | Texto descriptivo debajo del título |
| `botones` | array | ❌ No | Array de botones de vista |

### Estructura de cada botón

| Propiedad | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | string | ❌ No | ID del botón (auto-generado si no se provee) |
| `label` | string | ✅ Sí | Texto que aparece en el botón |
| `active` | boolean | ❌ No | Si el botón está activo (default: false) |

## 🎨 Estilos Aplicados

### Título
- **Color**: `#6A1B1B` (vino)
- **Tamaño**: h2
- **Estilo**: Bold, mayúsculas, espaciado de letras

### Descripción
- **Color**: Gris (text-muted)
- **Margen**: 16px abajo

### Botones Activos
- **Fondo**: Degradado de `#6A1B1B` a `#882A57`
- **Padding**: 10px 30px
- **Clase**: `btn btn-primary active`

### Botones Inactivos
- **Estilo**: Bootstrap secondary
- **Padding**: 10px 30px
- **Clase**: `btn btn-secondary`

## 💡 Ejemplos de Uso por Sección

### Dashboard
```php
$pageHeader = [
    'titulo' => 'Dashboard Principal',
    'icono' => '📊',
    'descripcion' => 'Vista general del sistema'
];
```

### Configuración
```php
$pageHeader = [
    'titulo' => 'Configuración del Sistema',
    'icono' => '⚙️',
    'descripcion' => 'Ajusta los parámetros de configuración'
];
```

### Reportes
```php
$pageHeader = [
    'titulo' => 'Generador de Reportes',
    'icono' => '📈',
    'descripcion' => 'Crea y exporta reportes personalizados',
    'botones' => [
        ['id' => 'btnDiario', 'label' => '📅 Diario', 'active' => true],
        ['id' => 'btnMensual', 'label' => '📆 Mensual', 'active' => false],
        ['id' => 'btnAnual', 'label' => '🗓️ Anual', 'active' => false]
    ]
];
```

## 📁 Ubicación
```
components/
  └── pageHeader.php
```

## ✅ Validaciones
- Si no existe `$pageHeader`, no se renderiza nada
- Si `titulo` está vacío, muestra "Sin Título"
- Si no hay `botones`, no se muestra el grupo de botones
- Si un botón no tiene `id`, se genera automáticamente
- Los botones sin `label` muestran "Botón N"

## 🔄 Mantenimiento
Archivo creado: 2025-11-11  
Última actualización: 2025-11-11  
Autor: Jorge Mauricio Quiñónez Pérez
