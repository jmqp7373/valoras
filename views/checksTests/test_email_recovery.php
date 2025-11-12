<?php
/**
 * Script de prueba para verificar envío de email de recuperación
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/login/PasswordResetController.php';

echo "<h2>🧪 Test de Envío de Email de Recuperación</h2>";

// Prueba 1: Verificar configuración
echo "<h3>1. Verificación de Configuración</h3>";
$emailConfig = require __DIR__ . '/../../config/email-config.php';
echo "<pre>";
echo "SMTP Host: " . $emailConfig['smtp_host'] . "\n";
echo "SMTP Port: " . $emailConfig['smtp_port'] . "\n";
echo "From Email: " . $emailConfig['from_email'] . "\n";
echo "Development Mode: " . ($emailConfig['development_mode'] ? 'TRUE ⚠️' : 'FALSE ✅') . "\n";
if($emailConfig['development_mode']) {
    echo "⚠️ TODOS LOS EMAILS SE REDIRIGEN A: " . $emailConfig['development_email'] . "\n";
} else {
    echo "✅ Los emails se envían a los destinatarios reales\n";
}
echo "</pre>";

// Prueba 2: Buscar un usuario para prueba
echo "<h3>2. Búsqueda de Usuario de Prueba</h3>";
try {
    $controller = new PasswordResetController();
    
    // Ingresa aquí una cédula de prueba
    $cedulaPrueba = '1125998052'; // CAMBIAR POR UNA CÉDULA REAL
    
    echo "<p>Buscando usuario con cédula: <strong>$cedulaPrueba</strong></p>";
    
    $userResult = $controller->findUser($cedulaPrueba, 'cedula');
    
    if($userResult['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
        echo "<strong>✅ Usuario encontrado:</strong><br>";
        echo "Email registrado: <strong>" . $userResult['user_data']['email'] . "</strong><br>";
        echo "Email enmascarado: " . ($userResult['masked_email'] ?? 'N/A') . "<br>";
        echo "Celular: " . ($userResult['user_data']['codigo_pais'] ?? '') . ($userResult['user_data']['celular'] ?? 'N/A') . "<br>";
        echo "</div>";
        
        // Prueba 3: Enviar código
        echo "<h3>3. Envío de Código de Verificación</h3>";
        echo "<p>Enviando código al email: <strong>" . $userResult['user_data']['email'] . "</strong></p>";
        
        $sendResult = $controller->sendResetCode($cedulaPrueba, 'email', 'cedula');
        
        if($sendResult['success']) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
            echo "<strong>✅ " . $sendResult['message'] . "</strong><br>";
            echo "Revisa la bandeja de entrada de: <strong>" . $userResult['user_data']['email'] . "</strong>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
            echo "<strong>❌ Error:</strong> " . $sendResult['message'];
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
        echo "<strong>❌ Usuario no encontrado:</strong> " . $userResult['message'];
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<p style='color: #666; font-size: 14px;'>";
echo "💡 <strong>Nota:</strong> Si development_mode está en TRUE, el email se enviará a admin@valora.vip<br>";
echo "Para enviar al email real del usuario, configura development_mode = false en config/email-config.php";
echo "</p>";
?>
