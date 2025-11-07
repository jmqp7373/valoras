<?php
/**
 * Configuración de Google Cloud Vision API para Valora.vip (EJEMPLO)
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'configGoogleVision.php' (en esta misma carpeta config/)
 * 2. Reemplaza 'TU-GOOGLE-VISION-API-KEY-AQUI' con tu API Key real
 * 3. NO subas configGoogleVision.php al repositorio (está en .gitignore)
 * 
 * Obtén tu API Key en: https://console.cloud.google.com/apis/credentials
 * 
 * @package Valora
 * @subpackage Config
 */

// =================================
// CONFIGURACIÓN DE GOOGLE VISION API
// =================================
define('GOOGLE_VISION_API_KEY', 'TU-GOOGLE-VISION-API-KEY-AQUI');

// =================================
// CONFIGURACIONES DE GOOGLE VISION
// =================================
// Tipos de detección habilitados
define('VISION_DETECT_TEXT', true);          // OCR - Detección de texto
define('VISION_DETECT_FACES', true);         // Detección de rostros
define('VISION_DETECT_LABELS', false);       // Etiquetas de imagen (deshabilitado)
define('VISION_DETECT_LANDMARKS', false);    // Puntos de referencia (deshabilitado)

// Límites y configuraciones
define('VISION_MAX_RESULTS', 10);            // Máximo de resultados por detección
define('VISION_CONFIDENCE_THRESHOLD', 0.7);  // Umbral de confianza (0-1)

// Configuración de imágenes
define('VISION_MAX_IMAGE_SIZE', 4194304);    // 4MB máximo por imagen
define('VISION_ALLOWED_FORMATS', 'jpg,jpeg,png,webp'); // Formatos permitidos

?>
