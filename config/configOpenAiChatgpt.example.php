<?php
/**
 * Configuración de OpenAI ChatGPT API para Valora.vip (EJEMPLO)
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'configOpenAiChatgpt.php' (en esta misma carpeta config/)
 * 2. Reemplaza 'TU-OPENAI-API-KEY-AQUI' con tu API Key real
 * 3. NO subas configOpenAiChatgpt.php al repositorio (está en .gitignore)
 * 
 * Obtén tu API Key en: https://platform.openai.com/api-keys
 * 
 * @package Valora
 * @subpackage Config
 */

// =================================
// CONFIGURACIÓN DE OPENAI API
// =================================
define('OPENAI_API_KEY', 'TU-OPENAI-API-KEY-AQUI');

// =================================
// CONFIGURACIONES DE IA
// =================================
define('AI_MODEL', 'gpt-4');                 // Modelo a usar: gpt-4, gpt-4-turbo, gpt-3.5-turbo
define('AI_MAX_TOKENS', 200);                // Máximo de tokens en la respuesta
define('AI_TEMPERATURE', 0.85);              // Creatividad (0.0-2.0, recomendado: 0.7-1.0)

// Configuraciones adicionales de OpenAI
define('AI_TOP_P', 1.0);                     // Nucleus sampling (0.0-1.0)
define('AI_FREQUENCY_PENALTY', 0.0);         // Penalización por frecuencia (-2.0 a 2.0)
define('AI_PRESENCE_PENALTY', 0.0);          // Penalización por presencia (-2.0 a 2.0)

// Timeouts y reintentos
define('AI_REQUEST_TIMEOUT', 30);            // Timeout en segundos
define('AI_MAX_RETRIES', 3);                 // Número de reintentos en caso de error

?>
