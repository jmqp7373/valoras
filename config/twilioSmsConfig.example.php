<?php
/**
 * Configuración de Twilio SMS - PLANTILLA
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'twilioSmsConfig.php' (en esta misma carpeta config/)
 * 2. Reemplaza los valores de ejemplo con tus credenciales reales de Twilio
 * 3. NO subas twilioSmsConfig.php al repositorio (está en .gitignore)
 * 
 * Obtén tus credenciales en: https://console.twilio.com/
 */

return [
    // Credenciales de Twilio
    'sid'   => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',  // Account SID de Twilio (empieza con AC)
    'token' => 'tu_auth_token_aqui',               // Auth Token de Twilio (32 caracteres)
    'from'  => '+1234567890'                       // Número de teléfono Twilio (formato E.164)
];
?>
