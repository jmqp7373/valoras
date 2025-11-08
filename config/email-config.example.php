<?php
/**
 * Configuración de Email SMTP - PLANTILLA
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'email-config.php' (en esta misma carpeta config/)
 * 2. Reemplaza los valores de ejemplo con tus credenciales reales de SMTP
 * 3. Configura development_mode según tu entorno (true=desarrollo, false=producción)
 * 4. NO subas email-config.php al repositorio (está en .gitignore)
 */

return [
    // Configuración del servidor SMTP
    'smtp_host' => 'smtp.tu-proveedor.com',  // Ejemplo: smtp.gmail.com, smtp.migadu.com
    'smtp_port' => 465,                       // 465 para SSL, 587 para TLS
    'smtp_secure' => 'ssl',                   // 'ssl' o 'tls'
    'smtp_auth' => true,
    
    // Credenciales SMTP
    'smtp_username' => 'tu-email@dominio.com',
    'smtp_password' => 'tu-contraseña-smtp',
    
    // Configuración del remitente
    'from_email' => 'noreply@dominio.com',
    'from_name' => 'Tu Aplicación',
    
    // Configuración de réplica (opcional)
    'reply_to_email' => 'soporte@dominio.com',
    'reply_to_name' => 'Soporte',
    
    // Configuración del servidor
    'timeout' => 30,
    'charset' => 'UTF-8',
    
    // Debug (cambiar a false en producción)
    'debug' => true, // true para ver logs de debug en desarrollo
    
    // ⚠️ IMPORTANTE: Configuración de entorno
    'development_mode' => true,  // true = emails van a development_email | false = emails van a destinatarios reales
    'development_email' => 'admin@dominio.com', // Email de pruebas (solo usado si development_mode=true)
];
?>
