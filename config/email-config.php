<?php
/**
 * Configuración de Email para Valora.vip
 * Sistema de envío usando Migadu SMTP
 */
return [
    // Configuración SMTP de Migadu
    'smtp_host' => 'smtp.migadu.com',
    'smtp_port' => 465, // SSL
    'smtp_secure' => 'ssl', // 'ssl' para puerto 465, 'tls' para puerto 587
    'smtp_auth' => true,
    
    // Credenciales SMTP - CAMBIAR POR LAS REALES
    'smtp_username' => 'noreply@valora.vip', // Email de Migadu para valora.vip
    'smtp_password' => 'Reylondres7373.', // Password de Migadu
    
    // Configuración de remitente
    'from_email' => 'noreply@valora.vip',
    'from_name' => 'Valora - Sistema de Recuperación',
    
    // Configuración de réplica (opcional)
    'reply_to_email' => 'soporte@valora.vip',
    'reply_to_name' => 'Soporte Valora',
    
    // Configuración del servidor
    'timeout' => 30,
    'charset' => 'UTF-8',
    
    // Debug (cambiar a false en producción)
    'debug' => true, // 0=off, 1=client messages, 2=client and server messages
    
    // Configuración para desarrollo
    'development_mode' => true, // Si está en true, todos los emails van a development_email
    'development_email' => 'admin@valora.vip', // Email para pruebas en desarrollo
];
?>