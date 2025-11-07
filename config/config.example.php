<?php
/**
 * Archivo de configuración de ejemplo para Valora.vip
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'config.php' (en esta misma carpeta config/)
 * 2. Reemplaza los valores de ejemplo con tus credenciales reales
 * 3. NO subas config.php al repositorio (está en .gitignore)
 */

// =================================
// CONFIGURACIÓN DE OPENAI API
// =================================
// Obtén tu API Key en: https://platform.openai.com/api-keys
define('OPENAI_API_KEY', 'sk-ejemplo-pon-tu-api-key-aqui-1234567890');

// Configuraciones de IA
define('AI_MODEL', 'gpt-4');
define('AI_MAX_TOKENS', 200);
define('AI_TEMPERATURE', 0.85);

// =================================
// CONFIGURACIÓN DE GOOGLE VISION API
// =================================
// Obtén tu API Key en: https://console.cloud.google.com/apis/credentials
define('GOOGLE_VISION_API_KEY', 'AIzaSy-ejemplo-pon-tu-google-vision-api-key-aqui');

// =================================
// CONFIGURACIONES DE LA APLICACIÓN
// =================================
define('APP_NAME', 'Valora.vip');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', false); // Cambiar a false en producción

// =================================
// CONFIGURACIONES ADICIONALES
// =================================
// Aquí puedes agregar más configuraciones según necesites
// define('SMTP_HOST', 'tu-servidor-smtp.com');
// define('SMTP_USER', 'usuario@dominio.com');
// define('SMTP_PASS', 'tu-password');

?>