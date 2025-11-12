# 🧪 Tests y Verificaciones del Sistema - Valora.vip

## 📋 Descripción

Este directorio contiene scripts de verificación y testing para validar el correcto funcionamiento de diferentes componentes del sistema.

---

## 📁 Archivos de Test

### 🌐 Verificación General del Sistema

#### `system-check.php`
**Propósito:** Verificación completa del estado del sistema  
**Ejecutar en:** Desarrollo y Producción  
**URL:** `https://valora.vip/views/checksTests/system-check.php`

**Verifica:**
- ✅ Archivos CSS y assets
- ✅ Conexión a base de datos
- ✅ Configuración de IA (OpenAI)
- ✅ Banderas y recursos
- ✅ Información del servidor
- ✅ Archivos del sistema

**Cuándo usar:**
- Después de un deployment
- Para diagnosticar problemas generales
- Verificación periódica del sistema

---

### 💾 Tests de Base de Datos

#### `test_database_config.php`
**Propósito:** Verificar configuración de base de datos  
**Ejecutar en:** Desarrollo y Producción  
**URL:** `https://valora.vip/views/checksTests/test_database_config.php`

**Verifica:**
- ✅ Detección automática de entorno (desarrollo/producción)
- ✅ Parámetros de conexión correctos
- ✅ Conexión exitosa a BD
- ✅ Permisos de usuario BD

**Cuándo usar:**
- Problemas de conexión a base de datos
- Después de cambiar credenciales
- Migración entre servidores

#### `test_db_connection.php`
**Propósito:** Test básico de conexión a BD  
**Ejecutar en:** Desarrollo  
**URL:** `https://valora.vip/views/checksTests/test_db_connection.php`

**Verifica:**
- ✅ Carga de clase Database
- ✅ Creación de objeto de conexión
- ✅ Ejecución de query simple
- ✅ Nombre de base de datos

**Cuándo usar:**
- Debug rápido de conexión BD
- Verificar nombre de base de datos activa

---

### 📧 Tests de Email

#### `test_email_recovery.php`
**Propósito:** Verificar sistema de recuperación de contraseña por email  
**Ejecutar en:** Desarrollo (principalmente)  
**URL:** `https://valora.vip/views/checksTests/test_email_recovery.php`

**Verifica:**
- ✅ Configuración SMTP
- ✅ Modo desarrollo/producción
- ✅ EmailService funcional
- ✅ Envío de emails de recuperación
- ✅ Generación de tokens

**Cuándo usar:**
- Problemas con emails de recuperación
- Configurar nuevo servidor SMTP
- Verificar templates de email

#### `test_password_reset.php`
**Propósito:** Test del controlador de reset de password  
**Ejecutar en:** Desarrollo  
**URL:** `https://valora.vip/views/checksTests/test_password_reset.php`

**Verifica:**
- ✅ Carga de PasswordResetController
- ✅ Método findUser() funcional
- ✅ Búsqueda por cédula
- ✅ Validación de usuarios

**Cuándo usar:**
- Debug de proceso de reset
- Verificar búsqueda de usuarios

---

### 🤖 Tests de Servicios IA

#### `check_vision_config.php`
**Propósito:** Diagnóstico completo de Google Cloud Vision API  
**Ejecutar en:** Desarrollo y Producción  
**URL:** `https://valora.vip/views/checksTests/check_vision_config.php`

**Verifica:**
- ✅ Archivo configGoogleVision.php existe
- ✅ Constante GOOGLE_VISION_API_KEY definida
- ✅ Servicio googleVisionService.php disponible
- ✅ Composer autoload de Google Cloud
- ✅ Cliente Vision correctamente configurado
- ✅ Credenciales JSON válidas

**Cuándo usar:**
- Configurar Google Vision por primera vez
- Problemas con verificación de documentos
- Errores de autenticación con Google Cloud

---

## 🚀 Guía de Uso

### Ejecución Local (XAMPP)

```bash
# Verificación general
http://localhost/valora.vip/views/checksTests/system-check.php

# Test de base de datos
http://localhost/valora.vip/views/checksTests/test_database_config.php

# Test de Google Vision
http://localhost/valora.vip/views/checksTests/check_vision_config.php
```

### Ejecución en Producción

```bash
# Verificación general
https://valora.vip/views/checksTests/system-check.php

# Test de base de datos
https://valora.vip/views/checksTests/test_database_config.php

# Test de Google Vision (solo si hay problemas)
https://valora.vip/views/checksTests/check_vision_config.php
```

---

## ⚠️ Notas de Seguridad

### 🔐 Producción
- ❌ NO dejar accesibles en producción indefinidamente
- ✅ Usar solo para diagnóstico temporal
- ✅ Proteger con autenticación si es necesario
- ✅ Eliminar después de resolver problemas

### 🏠 Desarrollo
- ✅ Usar libremente para debugging
- ✅ Ideal para configuración inicial
- ✅ Verificar antes de commits importantes

---

## 📝 Checklist de Deployment

Ejecutar estos tests en orden después de un deployment:

1. ✅ `system-check.php` - Verificación general
2. ✅ `test_database_config.php` - Configuración BD
3. ✅ `check_vision_config.php` - Solo si hay verificación de documentos

Si todo pasa ✅, el sistema está listo.

---

## 🔧 Troubleshooting

### Error: "No se puede conectar a BD"
→ Ejecutar `test_database_config.php` para ver detalles

### Error: "Email no se envía"
→ Ejecutar `test_email_recovery.php` y verificar configuración SMTP

### Error: "Google Vision falla"
→ Ejecutar `check_vision_config.php` y seguir las soluciones sugeridas

### Error 500 después de deployment
→ Ejecutar `system-check.php` para identificar archivos faltantes

---

## 📊 Historial de Cambios

- **2025-11-11:** Organización inicial de tests
- **2025-11-11:** Eliminados tests temporales y con credenciales hardcodeadas
- **2025-11-11:** Actualizadas rutas relativas para nueva ubicación

---

## 👨‍💻 Mantenimiento

**Agregar nuevos tests:**
1. Crear archivo en esta carpeta
2. Documentarlo en este README
3. Usar rutas relativas: `__DIR__ . '/../../config/...'`
4. Incluir descripción clara del propósito

**Eliminar tests obsoletos:**
1. Verificar que no se usen en producción
2. Documentar la eliminación
3. Actualizar este README

---

## 📞 Soporte

Para dudas sobre tests:
- Autor: Jorge Mauricio Quiñónez Pérez
- Email: jmqp7373@gmail.com
- Proyecto: Valora.vip
