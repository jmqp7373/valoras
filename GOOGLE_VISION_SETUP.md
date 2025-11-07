# 📝 Instrucciones para Configurar Google Vision API

## ⚠️ Problema Actual

El error **"GOOGLE_VISION_API_KEY no está definida"** aparece porque el archivo `config/configGoogleVision.php` no existe en el servidor de producción o no tiene la constante definida.

## ✅ Solución Paso a Paso

### 1️⃣ **Verificar que `config/configGoogleVision.php` existe en el servidor**

Conecta por FTP o FileManager de Hostinger y verifica que existe:
```
/public_html/config/configGoogleVision.php
```

### 2️⃣ **Si NO existe, créalo basándote en `configGoogleVision.example.php`**

**Opción A: Por FTP**
1. Descarga `config/configGoogleVision.example.php` del repositorio
2. Renómbralo a `configGoogleVision.php`
3. Edita el archivo y agrega tu API Key real
4. Súbelo a `/public_html/config/configGoogleVision.php`

**Opción B: Por FileManager de Hostinger**
1. Ve a `public_html/config/`
2. Crea un nuevo archivo llamado `configGoogleVision.php`
3. Copia el contenido de abajo y pégalo

### 3️⃣ **Contenido del archivo `config/configGoogleVision.php`**

```php
<?php
/**
 * Configuración de Google Cloud Vision API para Valora.vip
 * IMPORTANTE: Este archivo NO debe subirse a GitHub
 */

// =================================
// CONFIGURACIÓN DE GOOGLE VISION API
// =================================
define('OPENAI_API_KEY', 'sk-proj-TU-API-KEY-DE-OPENAI-AQUI');

// Configuraciones de IA
define('AI_MODEL', 'gpt-4');
define('AI_MAX_TOKENS', 200);
define('AI_TEMPERATURE', 0.85);

// =================================
// CONFIGURACIÓN DE GOOGLE VISION API
// =================================
// Obtén tu API Key en: https://console.cloud.google.com/apis/credentials
define('GOOGLE_VISION_API_KEY', 'TU-GOOGLE-VISION-API-KEY-AQUI');

// Configuraciones opcionales
define('VISION_DETECT_TEXT', true);
define('VISION_DETECT_FACES', true);
define('VISION_MAX_RESULTS', 10);
?>
```

### 4️⃣ **Verificar que funciona**

Sube el script de diagnóstico a tu servidor y accede a:

1. Sube `check_vision_config.php` a `/public_html/`
2. Ve a: `https://valora.vip/check_vision_config.php`
3. El script verificará automáticamente toda la configuración
4. Si todo está OK, verás el botón para ir a verificación de documentos

## 🔐 Seguridad

**IMPORTANTE:** El archivo `config/configGoogleVision.php` contiene credenciales sensibles:
- ✅ Debe estar en `.gitignore` (ya está)
- ✅ NO debe subirse a GitHub (nunca)
- ✅ Solo debe existir en el servidor de producción
- ✅ Debe tener permisos 644 en el servidor

## 🧪 Pruebas

### Test Local (XAMPP):
```
http://localhost/valora.vip/check_vision_config.php
http://localhost/valora.vip/views/login/verify_document.php
```

### Test Producción (Hostinger):
```
https://valora.vip/check_vision_config.php
https://valora.vip/views/login/verify_document.php
```

## 🆘 Si el Error Persiste

**Verifica en FileManager de Hostinger:**

1. **Ruta correcta:**
   - ✅ `/public_html/config/configGoogleVision.php`
   - ❌ `/public_html/configGoogleVision.php` (mal ubicado)

2. **Permisos del archivo:**
   - Debe ser: `644` (lectura/escritura para owner, solo lectura para grupo y otros)

3. **Sintaxis PHP:**
   - El archivo debe empezar con `<?php`
   - Debe terminar con `?>`
   - No debe tener espacios antes de `<?php`

4. **API Key válida:**
   - Verifica en: https://console.cloud.google.com/apis/credentials
   - Debe tener Cloud Vision API habilitada

## 📊 Consumo de Google Vision API

Cada análisis de documento consume **2 unidades** de la API:
- 1 unidad por cara frontal
- 1 unidad por cara posterior

**Cuota gratuita de Google Vision:**
- 1,000 unidades/mes gratis
- Después: $1.50 por cada 1,000 unidades

**Monitorea tu consumo en:**
https://console.cloud.google.com/apis/api/vision.googleapis.com/metrics

---

**Última actualización:** 7 de noviembre de 2025
