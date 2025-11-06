<?php
/**
 * Configuración de Email SMTP - PLANTILLA
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'email-config.php' (en esta misma carpeta config/)
 * 2. Reemplaza los valores de ejemplo con tus credenciales reales de SMTP
 * 3. NO subas email-config.php al repositorio (está en .gitignore)
 */

return [
    // Configuración del servidor SMTP
    'smtp_host' => 'smtp.tu-proveedor.com',  // Ejemplo: smtp.gmail.com, smtp.migadu.com
    'smtp_port' => 465,                       // 465 para SSL, 587 para TLS
    'smtp_secure' => 'ssl',                   // 'ssl' o 'tls'
    
    // Credenciales SMTP
    'smtp_username' => 'tu-email@dominio.com',
    'smtp_password' => 'tu-contraseña-smtp',
    
    // Configuración del remitente
    'from_email' => 'noreply@dominio.com',
    'from_name' => 'Tu Aplicación',
    
    // Configuración adicional
    'smtp_auth' => true,
    'charset' => 'UTF-8'
];
?>
