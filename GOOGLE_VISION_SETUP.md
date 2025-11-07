# 📝 Instrucciones para Configurar Google Vision API

## ⚠️ Problema Actual

El error **"GOOGLE_VISION_API_KEY no está definida en config.php"** aparece porque el archivo `config/config.php` no existe en el servidor de producción o no tiene la constante definida.

## ✅ Solución Paso a Paso

### 1️⃣ **Verificar que `config/config.php` existe en el servidor**

Conecta por FTP o FileManager de Hostinger y verifica que existe:
```
/public_html/config/config.php
```

### 2️⃣ **Si NO existe, créalo basándote en `config.example.php`**

**Opción A: Por FTP**
1. Descarga `config/config.example.php` del repositorio
2. Renómbralo a `config.php`
3. Edita el archivo y agrega tu API Key real
4. Súbelo a `/public_html/config/config.php`

**Opción B: Por FileManager de Hostinger**
1. Ve a `public_html/config/`
2. Crea un nuevo archivo llamado `config.php`
3. Copia el contenido de abajo y pégalo

### 3️⃣ **Contenido del archivo `config/config.php`**

```php
<?php
/**
 * Archivo de configuración para Valora.vip
 * IMPORTANTE: Este archivo NO debe subirse a GitHub
 */

// =================================
// CONFIGURACIÓN DE OPENAI API
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
define('GOOGLE_VISION_API_KEY', 'AIzaSyBl2PAMRxKIOZxb26P8_iRFiqwMHTxMp9Q');

// =================================
// CONFIGURACIONES DE LA APLICACIÓN
// =================================
define('APP_NAME', 'Valora.vip');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', false);
?>
```

### 4️⃣ **Verificar que funciona**

Después de crear/actualizar el archivo:

1. Ve a: `https://valora.vip/testVision.php`
2. Deberías ver: **"✅ API Key encontrada: AIzaSyBl2P..."**
3. Si funciona, ya puedes usar `verify_document.php`

## 🔐 Seguridad

**IMPORTANTE:** El archivo `config/config.php` contiene credenciales sensibles:
- ✅ Debe estar en `.gitignore` (ya está)
- ✅ NO debe subirse a GitHub (nunca)
- ✅ Solo debe existir en el servidor de producción
- ✅ Debe tener permisos 644 en el servidor

## 🧪 Pruebas

### Test Local (XAMPP):
```
http://localhost/valora.vip/testVision.php
http://localhost/valora.vip/views/login/verify_document.php
```

### Test Producción (Hostinger):
```
https://valora.vip/testVision.php
https://valora.vip/views/login/verify_document.php
```

## 🆘 Si el Error Persiste

**Verifica en FileManager de Hostinger:**

1. **Ruta correcta:**
   - ✅ `/public_html/config/config.php`
   - ❌ `/public_html/config.php` (mal ubicado)

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
