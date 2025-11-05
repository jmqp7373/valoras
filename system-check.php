<?php
/**
 * Página de verificación del sistema Valora.vip
 * Verifica que todos los componentes estén funcionando
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - Valora.vip</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="brand">
                <img src="/assets/images/logos/logo_valora.png" class="logo" alt="Valora Logo">
                <h1>🔍 Verificación del Sistema</h1>
            </div>
            
            <div style="text-align: left; padding: 20px; background: #f8f9fa; border-radius: 12px; margin: 20px 0;">
                <h3>📊 Estado de Componentes:</h3>
                
                <?php
                // Verificar CSS
                $cssExists = file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/css/styles.css');
                echo "<p>" . ($cssExists ? "✅" : "❌") . " CSS: /assets/css/styles.css</p>";
                
                // Verificar Logo  
                $logoExists = file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/logo_valora.png');
                echo "<p>" . ($logoExists ? "✅" : "❌") . " Logo: /assets/images/logos/logo_valora.png</p>";
                
                // Verificar banderas
                $flagsDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/flags/';
                $flags = ['co.png', 'ar.png', 'cl.png', 'ec.png', 'us.png', 'mx.png', 'pe.png', 've.png'];
                $flagsOk = 0;
                foreach($flags as $flag) {
                    if(file_exists($flagsDir . $flag)) $flagsOk++;
                }
                echo "<p>" . ($flagsOk == count($flags) ? "✅" : "⚠️") . " Banderas: $flagsOk/" . count($flags) . " disponibles</p>";
                
                // Verificar base de datos
                try {
                    require_once 'config/database.php';
                    $db = new Database();
                    $conn = $db->getConnection();
                    if ($conn) {
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo "<p>✅ Base de Datos: " . $result['total'] . " usuarios</p>";
                    } else {
                        echo "<p>❌ Base de Datos: No conectada</p>";
                    }
                } catch (Exception $e) {
                    echo "<p>❌ Base de Datos: Error - " . $e->getMessage() . "</p>";
                }
                
                // Verificar configuración de IA
                echo "<h4>🤖 Sistema de Inteligencia Artificial:</h4>";
                $configExists = file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php');
                echo "<p>" . ($configExists ? "✅" : "⚠️") . " Archivo config.php: " . ($configExists ? "Existe" : "Falta - copiar de config.example.php") . "</p>";
                
                if ($configExists) {
                    require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
                    $apiKeyConfigured = defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'sk-ejemplo-pon-tu-api-key-aqui-1234567890';
                    echo "<p>" . ($apiKeyConfigured ? "✅" : "⚠️") . " API Key OpenAI: " . ($apiKeyConfigured ? "Configurada" : "Falta configurar") . "</p>";
                } else {
                    echo "<p>⚠️ API Key OpenAI: No verificable (config.php faltante)</p>";
                }
                
                $aiGeneratorExists = file_exists($_SERVER['DOCUMENT_ROOT'] . '/controllers/usernameGenerator.php');
                echo "<p>" . ($aiGeneratorExists ? "✅" : "❌") . " Generador IA: controllers/usernameGenerator.php</p>";
                
                $aiViewExists = file_exists($_SERVER['DOCUMENT_ROOT'] . '/views/login/registranteUserAvailavilitySelect.php');
                echo "<p>" . ($aiViewExists ? "✅" : "❌") . " Interfaz IA: views/login/registranteUserAvailavilitySelect.php</p>";
                
                // Verificar archivos principales
                $files = [
                    'index.php' => 'Dashboard principal',
                    'views/login.php' => 'Login',
                    'views/register.php' => 'Registro con IA', 
                    'views/password_reset.php' => 'Recuperar contraseña',
                    'views/reset_password.php' => 'Reset contraseña',
                    'controllers/AuthController.php' => 'Controlador Auth',
                    'controllers/PasswordResetController.php' => 'Controlador Reset',
                    'models/Usuario.php' => 'Modelo Usuario',
                    'services/EmailService.php' => 'Servicio Email'
                ];
                
                echo "<h4>📄 Archivos del Sistema:</h4>";
                foreach($files as $file => $desc) {
                    $exists = file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $file);
                    echo "<p>" . ($exists ? "✅" : "❌") . " $desc ($file)</p>";
                }
                
                // Info del servidor
                echo "<h4>🖥️ Información del Servidor:</h4>";
                echo "<p>📡 Host: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</p>";
                echo "<p>🐘 PHP: " . phpversion() . "</p>";
                echo "<p>📁 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
                echo "<p>🌐 Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</p>";
                ?>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="/views/login.php" style="background: #ee6f92; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block;">
                    🔐 Ir al Login
                </a>
                <a href="/views/register.php" style="background: #8b5a83; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin-left: 10px;">
                    📝 Ir al Registro
                </a>
            </div>
        </div>
    </div>
</body>
</html>