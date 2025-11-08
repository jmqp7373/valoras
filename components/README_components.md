# Componentes Reutilizables - Valora.vip

## 📋 Descripción

Esta carpeta contiene componentes PHP reutilizables para mantener consistencia en el diseño y funcionalidad del proyecto.

---

## 🎯 Componentes Disponibles

### 1. `header.php` - Encabezado del Dashboard

**Descripción:**  
Barra superior con logo y menú de usuario (nombre + botón de cerrar sesión).

**Variables requeridas:**
```php
$user_nombres = 'Juan';              // Nombre del usuario
$user_apellidos = 'Pérez';           // Apellidos del usuario
$logo_path = 'assets/images/logos/logoValoraHorizontal.png';  // Ruta al logo
$logout_path = 'controllers/login/logout.php';                // Ruta logout
```

**Uso básico (desde raíz del proyecto):**
```php
<?php
// Definir variables
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$logo_path = 'assets/images/logos/logoValoraHorizontal.png';
$logout_path = 'controllers/login/logout.php';

// Incluir header
include 'components/header.php';
?>
```

**Uso desde subcarpetas (ej: views/finanzas/):**
```php
<?php
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$logout_path = '../../controllers/login/logout.php';

include '../../components/header.php';
?>
```

**Características:**
- ✅ Header sticky (se queda fijo al hacer scroll)
- ✅ Responsive (se adapta a móvil)
- ✅ z-index: 1000 para estar siempre visible
- ✅ Estilos incluidos automáticamente

---

### 2. `footer.php` - Pie de Página

**Descripción:**  
Footer con enlaces, información de copyright y navegación secundaria.

**Variables opcionales:**
```php
$base_path = '';  // Ruta base para enlaces (vacío desde raíz, '../' desde subcarpetas)
```

**Uso básico (desde raíz del proyecto):**
```php
<?php
$base_path = '';
include 'components/footer.php';
?>
```

**Uso desde subcarpetas (ej: views/finanzas/):**
```php
<?php
$base_path = '../../';
include '../../components/footer.php';
?>
```

**Características:**
- ✅ 4 secciones: Información, Enlaces, Soporte, Copyright
- ✅ Grid responsive (4 columnas en desktop, 1 en móvil)
- ✅ Enlaces a todas las secciones principales
- ✅ Copyright dinámico con año actual
- ✅ Colores institucionales Valora.vip

---

## 📂 Estructura de Ejemplo Completa

### Desde raíz del proyecto (index.php):

```php
<?php
require_once 'config/database.php';
startSessionSafely();

if(!isLoggedIn()) {
    header('Location: views/login/login.php');
    exit();
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Valora</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <?php
        $logo_path = 'assets/images/logos/logoValoraHorizontal.png';
        $logout_path = 'controllers/login/logout.php';
        include 'components/header.php';
        ?>
        
        <main class="dashboard-main">
            <!-- Tu contenido aquí -->
            <h1>¡Bienvenido!</h1>
        </main>

        <?php
        $base_path = '';
        include 'components/footer.php';
        ?>
    </div>
</body>
</html>
```

### Desde subcarpeta (views/finanzas/finanzasDashboard.php):

```php
<?php
require_once __DIR__ . '/../../config/database.php';
startSessionSafely();

if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas - Valora</title>
</head>
<body>
    <div class="dashboard-container">
        <?php
        $logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
        $logout_path = '../../controllers/login/logout.php';
        include '../../components/header.php';
        ?>
        
        <main class="dashboard-main">
            <!-- Tu contenido aquí -->
            <h1>Gestión de Finanzas</h1>
        </main>

        <?php
        $base_path = '../../';
        include '../../components/footer.php';
        ?>
    </div>
</body>
</html>
```

---

## 🎨 Estilos Incluidos

Ambos componentes incluyen sus propios estilos CSS inline mediante etiquetas `<style>`. No necesitas agregar CSS adicional.

### Colores institucionales utilizados:
- **Vinotinto:** #6A1B1B, #882A57
- **Rosa:** #ee6f92
- **Azul oscuro:** #1B263B
- **Gris neutro:** #E5E5E5
- **Blanco:** #FFFFFF
- **Negro suave:** #222222

---

## 📱 Responsive Design

### Header:
- **Desktop:** Logo izquierda, usuario y logout derecha (flex-row)
- **Móvil:** Elementos apilados verticalmente (flex-column)

### Footer:
- **Desktop:** 4 columnas en grid
- **Móvil:** 1 columna, centrado

---

## ✅ Ventajas de Usar Estos Componentes

1. **Consistencia:** Mismo diseño en todas las páginas
2. **Mantenibilidad:** Un solo lugar para actualizar header/footer
3. **DRY (Don't Repeat Yourself):** No duplicar código
4. **Fácil actualización:** Cambios se reflejan automáticamente
5. **Reutilización:** Usar en cualquier página del proyecto

---

## 🔧 Personalización

### Cambiar el logo:
```php
$logo_path = 'ruta/a/otro/logo.png';
```

### Cambiar color del botón logout:
Edita el CSS en `header.php` línea con `.logout-btn`

### Agregar enlaces al footer:
Edita `footer.php` y agrega elementos `<li><a>` en las secciones

---

## 📌 Notas Importantes

- Los componentes **NO incluyen** las etiquetas `<body>` o `<html>`
- Asegúrate de que las rutas sean relativas correctas
- Los estilos CSS están incluidos en cada componente
- Variables no definidas usan valores por defecto

---

## 🚀 Próximos Pasos

Para implementar estos componentes en tus páginas existentes:

1. Definir las variables necesarias
2. Reemplazar tu header actual con `include 'components/header.php'`
3. Reemplazar tu footer (o agregar si no existe) con `include 'components/footer.php'`
4. Eliminar CSS duplicado de header/footer en tus archivos

---

**Última actualización:** 08/11/2025  
**Versión:** 1.0.0
