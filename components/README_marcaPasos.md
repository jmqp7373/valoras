# 📊 Componente: Marca Pasos (Step Indicator)

Sistema de indicador visual de pasos para formularios multi-etapa.

## 📁 Archivos del Componente

```
components/
├── marcaPasos.php      # Función de renderizado
├── marcaPasos.css      # Estilos del componente
└── README_marcaPasos.md # Esta documentación
```

## 🚀 Uso Básico

### 1. Incluir archivos necesarios

En tu archivo PHP:

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Formulario</title>
    
    <!-- Incluir CSS del marca pasos -->
    <link rel="stylesheet" href="../../components/marcaPasos.css">
</head>
<body>
    <?php
    // Incluir función de renderizado
    include '../../components/marcaPasos.php';
    
    // Renderizar marca pasos: Paso 2 de 3
    renderMarcaPasos(2, 3);
    ?>
    
    <!-- Tu contenido aquí -->
</body>
</html>
```

## 📖 Ejemplos

### Ejemplo 1: Marca Pasos Simple (con números)

```php
<?php renderMarcaPasos(2, 3); ?>
```

**Resultado:** `● -- ● -- ○` (Paso 2 de 3, con pasos 1 completado, 2 activo, 3 pendiente)

---

### Ejemplo 2: Marca Pasos con Etiquetas Personalizadas

```php
<?php 
renderMarcaPasos(2, 3, ['📸', '🔍', '✏️']); 
?>
```

**Resultado:** `📸 -- 🔍 -- ✏️` (Con emojis en lugar de números)

---

### Ejemplo 3: Marca Pasos Avanzado con Título

```php
<?php 
renderMarcaPasosAdvanced([
    'currentStep' => 2,
    'totalSteps' => 3,
    'labels' => ['Upload', 'Verify', 'Update'],
    'showTitle' => true,
    'titles' => [
        'Paso 1: Subir Documento',
        'Paso 2: Análisis OCR',
        'Paso 3: Actualizar Datos'
    ]
]);
?>
```

---

## 🎨 Estados Visuales

### Estado: Completado ✅
- **Color:** Verde (`#4caf50`)
- **Representa:** Paso ya finalizado
- **CSS Class:** `.step.completed`

### Estado: Activo 🔴
- **Color:** Rosa/Magenta (`#e91e63`)
- **Representa:** Paso actual
- **CSS Class:** `.step.active`
- **Efecto:** Escala 1.15x + Sombra

### Estado: Pendiente ⚪
- **Color:** Gris (`#e0e0e0`)
- **Representa:** Paso no alcanzado
- **CSS Class:** `.step`

---

## 🔧 Personalización CSS

Si necesitas personalizar los colores o tamaños:

```css
/* Sobrescribir color del paso activo */
.step.active {
    background: linear-gradient(135deg, #882A57, #d63384) !important;
}

/* Cambiar tamaño de los círculos */
.step {
    width: 50px !important;
    height: 50px !important;
    font-size: 22px !important;
}
```

---

## 📱 Responsive

El componente incluye breakpoints automáticos:

- **Desktop (>768px):** Círculos de 45px
- **Tablet (≤768px):** Círculos de 38px
- **Móvil (≤480px):** Círculos de 32px

---

## 🎯 Casos de Uso en Valora.vip

### Flujo de Verificación de Identidad (3 pasos)

**Paso 1: verify1_document.php**
```php
<?php include '../../components/marcaPasos.php'; ?>
<?php renderMarcaPasos(1, 3); ?>
```

**Paso 2: verify2_OCR.php**
```php
<?php include '../../components/marcaPasos.php'; ?>
<?php renderMarcaPasos(2, 3); ?>
```

**Paso 3: verify3_Update.php**
```php
<?php include '../../components/marcaPasos.php'; ?>
<?php renderMarcaPasos(3, 3); ?>
```

---

## 🔍 Estructura HTML Generada

```html
<div class="steps-container">
    <div class="step completed">1</div>
    <div class="step-line active"></div>
    <div class="step active">2</div>
    <div class="step-line"></div>
    <div class="step">3</div>
</div>
```

---

## ⚡ Performance

- **CSS:** ~3KB (sin comprimir)
- **PHP:** Mínimo overhead (~0.1ms)
- **No requiere JavaScript**
- **Compatible con todos los navegadores modernos**

---

## 📝 Changelog

### v1.0.0 (2025-11-07)
- ✅ Versión inicial del componente
- ✅ Soporte para pasos numéricos y personalizados
- ✅ Diseño responsive
- ✅ Estados: completado, activo, pendiente
- ✅ Función avanzada con títulos

---

## 🆘 Soporte

Si encuentras problemas o tienes sugerencias, contacta al equipo de desarrollo de Valora.vip.
