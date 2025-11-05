# 🤖 Configuración de IA para Valora.vip

## 📋 Funcionalidad Implementada

Valora.vip ahora incluye **generación inteligente de nombres de usuario** usando OpenAI GPT-4. Los usuarios pueden:

- Describir su personalidad o estilo
- Recibir 5 sugerencias únicas y elegantes  
- Integrar automáticamente con el registro
- Experiencia fluida y profesional

## 🚀 Configuración Rápida

### 1. Crear archivo de configuración

```bash
# Copiar plantilla
cp config.example.php config.php

# Editar con tu API Key
nano config.php  # o usar tu editor favorito
```

### 2. Configurar API Key de OpenAI

En `config.php`, cambiar:
```php
define('OPENAI_API_KEY', 'sk-ejemplo-pon-tu-api-key-aqui-1234567890');
```

Por tu API Key real:
```php
define('OPENAI_API_KEY', 'sk-tu-api-key-real-aqui');
```

### 3. Obtener API Key de OpenAI

1. Ir a: https://platform.openai.com/api-keys
2. Crear cuenta o iniciar sesión
3. Generar nueva API Key
4. Copiar y pegar en `config.php`

## 📁 Archivos del Sistema IA

```
controllers/
├── usernameGenerator.php      # Backend OpenAI GPT-4
└── AuthController.php         # Actualizado para campo username

views/
├── register.php               # Formulario con botón IA
└── login/
    └── registranteUserAvailavilitySelect.php  # Interfaz IA

config.example.php             # Plantilla de configuración
setup-ai.sh                   # Script de instalación (Linux/Mac)
AI_SETUP_INSTRUCTIONS.md      # Este archivo
```

## 🔧 Solución de Problemas

### Error: "Configuración no encontrada"
```bash
# Verificar que config.php existe
ls -la config.php

# Si no existe, crearlo desde plantilla
cp config.example.php config.php
```

### Error: "API Key de OpenAI no configurada"
```bash
# Editar config.php
nano config.php

# Verificar que la línea sea así:
# define('OPENAI_API_KEY', 'sk-tu-api-key-real');
```

### Error: "Error de conexión"
- Verificar conexión a internet
- Comprobar que la API Key sea válida
- Verificar límites de uso en OpenAI

## 🌐 URLs de Prueba

Una vez configurado:

- **Registro completo**: `/views/register.php`
- **Generador IA directo**: `/views/login/registranteUserAvailavilitySelect.php` 
- **Verificación del sistema**: `/system-check.php`

## 🎯 Flujo de Usuario

1. Usuario va a **registro** (`/views/register.php`)
2. Completa datos básicos (nombre, apellido, etc.)
3. En campo "Usuario", hace clic en botón **"✨ IA"**
4. Se abre nueva pestaña con generador inteligente
5. Describe su estilo: "elegante", "internacional", "creativo", etc.
6. Recibe 5 sugerencias únicas
7. Hace clic en una sugerencia
8. Automáticamente regresa al registro con el nombre aplicado
9. Completa registro con nombre sugerido por IA

## 🔒 Seguridad

- ✅ `config.php` **NO** se sube al repositorio (`.gitignore`)
- ✅ API Keys mantenidas seguras en servidor
- ✅ Validación de entrada y manejo de errores
- ✅ Timeouts para prevenir colgadas
- ✅ Sanitización de respuestas de IA

## 💡 Personalización

### Modificar el prompt del sistema
En `controllers/usernameGenerator.php`, línea ~35:
```php
["role" => "system", "content" => "Personaliza este mensaje según tu marca..."]
```

### Cambiar modelo de IA
En `config.php`:
```php
define('AI_MODEL', 'gpt-3.5-turbo');  // Más económico
// o
define('AI_MODEL', 'gpt-4');          // Más inteligente
```

### Ajustar creatividad
En `config.php`:
```php
define('AI_TEMPERATURE', 0.7);   // Más conservador
define('AI_TEMPERATURE', 1.0);   // Más creativo
```

## 📊 Monitoreo de Uso

OpenAI cobra por tokens usados. Monitorear en:
- https://platform.openai.com/usage

Cada sugerencia usa aproximadamente:
- **Tokens de entrada**: ~50-100
- **Tokens de salida**: ~50-150  
- **Costo estimado**: $0.002-0.005 por sugerencia

---

**¿Necesitas ayuda?** 
- Revisa `/system-check.php` para diagnósticos
- Verifica logs del servidor web
- Contacta soporte técnico