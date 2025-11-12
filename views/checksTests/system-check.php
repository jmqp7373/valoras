<?php
/**
 * Página de verificación del sistema Valora.vip
 * Verifica que todos los componentes estén funcionando
 */
require_once __DIR__ . '/../../config/database.php';
startSessionSafely();

// Variables para header (si hay sesión activa)
$user_nombres = $_SESSION['user_nombres'] ?? 'Visitante';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - Valora.vip</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        body {
            background-color: #F8F9FA;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        
        .system-check-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .system-header {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .system-header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 600;
        }
        
        .system-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .check-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .check-section h3 {
            color: #882A57;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .check-item {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
        
        .status-ok {
            color: #28a745;
            font-weight: 600;
        }
        
        .status-warning {
            color: #ffc107;
            font-weight: 600;
        }
        
        .status-error {
            color: #dc3545;
            font-weight: 600;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin: 5px;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #882A57, #ee6f92);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <?php if(isLoggedIn()): ?>
        <?php include '../../components/header/header.php'; ?>
    <?php endif; ?>
    
    <div class="system-check-container">
        <div class="system-header">
            <h1>� Verificación del Sistema</h1>
            <p>Estado de todos los componentes de Valora.vip</p>
        </div>
        
        <div class="check-section">
            <h3>📊 Estado de Componentes</h3>
            
            <div class="check-item">
                <?php
                // Verificar CSS
                $cssExists = file_exists(__DIR__ . '/../../assets/css/styles.css');
                echo $cssExists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>';
                ?>
                <span>CSS: /assets/css/styles.css</span>
            </div>
            
            <div class="check-item">
                <?php
                // Verificar logo
                $logoExists = file_exists(__DIR__ . '/../../assets/images/logos/logo_valora.png');
                echo $logoExists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>';
                ?>
                <span>Logo: /assets/images/logos/logo_valora.png</span>
            </div>
            
            <div class="check-item">
                <?php
                // Verificar banderas
                $flagsDir = __DIR__ . '/../../assets/images/flags/';
                $flags = ['co.png', 'ar.png', 'cl.png', 'ec.png', 'us.png', 'mx.png', 'pe.png', 've.png'];
                $flagsOk = 0;
                foreach($flags as $flag) {
                    if(file_exists($flagsDir . $flag)) $flagsOk++;
                }
                $flagsStatus = $flagsOk == count($flags) ? 'ok' : 'warning';
                echo $flagsStatus == 'ok' ? '<span class="status-ok">✅</span>' : '<span class="status-warning">⚠️</span>';
                ?>
                <span>Banderas: <?php echo $flagsOk; ?>/<?php echo count($flags); ?> disponibles</span>
            </div>
            
            <div class="check-item">
                <?php
                // Verificar base de datos
                try {
                    require_once __DIR__ . '/../../config/database.php';
                    $db = new Database();
                    $conn = $db->getConnection();
                    if ($conn) {
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo '<span class="status-ok">✅</span>';
                        echo '<span>Base de Datos: ' . $result['total'] . ' usuarios</span>';
                    } else {
                        echo '<span class="status-error">❌</span>';
                        echo '<span>Base de Datos: No conectada</span>';
                    }
                } catch (Exception $e) {
                    echo '<span class="status-error">❌</span>';
                    echo '<span>Base de Datos: Error - ' . $e->getMessage() . '</span>';
                }
                ?>
            </div>
        </div>
        
        <div class="check-section">
            <h3>🤖 Sistema de Inteligencia Artificial</h3>
            
            <div class="check-item">
                <?php
                $configExists = file_exists(__DIR__ . '/../../config/config.php');
                echo $configExists ? '<span class="status-ok">✅</span>' : '<span class="status-warning">⚠️</span>';
                ?>
                <span>Archivo config/config.php: <?php echo $configExists ? 'Existe' : 'Falta - copiar de config/config.example.php'; ?></span>
            </div>
            
            <div class="check-item">
                <?php
                if ($configExists) {
                    require_once __DIR__ . '/../../config/config.php';
                    $apiKeyConfigured = defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'sk-ejemplo-pon-tu-api-key-aqui-1234567890';
                    echo $apiKeyConfigured ? '<span class="status-ok">✅</span>' : '<span class="status-warning">⚠️</span>';
                    echo '<span>API Key OpenAI: ' . ($apiKeyConfigured ? 'Configurada' : 'Falta configurar') . '</span>';
                } else {
                    echo '<span class="status-warning">⚠️</span>';
                    echo '<span>API Key OpenAI: No verificable (config/config.php faltante)</span>';
                }
                ?>
            </div>
            
            <div class="check-item">
                <?php
                $aiGeneratorExists = file_exists(__DIR__ . '/../../controllers/login/usernameGenerator.php');
                echo $aiGeneratorExists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>';
                ?>
                <span>Generador IA: controllers/login/usernameGenerator.php</span>
            </div>
            
            <div class="check-item">
                <?php
                $aiViewExists = file_exists(__DIR__ . '/../login/registranteUserAvailavilitySelect.php');
                echo $aiViewExists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>';
                ?>
                <span>Interfaz IA: views/login/registranteUserAvailavilitySelect.php</span>
            </div>
        </div>
        
        <div class="check-section">
            <h3>📄 Archivos del Sistema</h3>
            <?php
            $files = [
                '../../index.php' => 'Dashboard principal',
                '../login/login.php' => 'Login',
                '../login/register.php' => 'Registro con IA', 
                '../login/password_reset.php' => 'Recuperar contraseña',
                '../login/reset_password.php' => 'Reset contraseña',
                '../../controllers/login/AuthController.php' => 'Controlador Auth',
                '../../controllers/login/PasswordResetController.php' => 'Controlador Reset',
                '../../models/Usuario.php' => 'Modelo Usuario',
                '../../services/EmailService.php' => 'Servicio Email'
            ];
            
            foreach($files as $file => $desc) {
                $exists = file_exists(__DIR__ . '/' . $file);
                echo '<div class="check-item">';
                echo $exists ? '<span class="status-ok">✅</span>' : '<span class="status-error">❌</span>';
                echo '<span>' . $desc . ' (' . basename($file) . ')</span>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="check-section">
            <h3>🖥️ Información del Servidor</h3>
            <div class="check-item">
                <span>📡 Host: <?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?></span>
            </div>
            <div class="check-item">
                <span>🐘 PHP: <?php echo phpversion(); ?></span>
            </div>
            <div class="check-item">
                <span>📁 Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></span>
            </div>
            <div class="check-item">
                <span>🌐 Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
            </div>
        </div>
        
        <div class="action-buttons">
            <?php if(isLoggedIn()): ?>
                <a href="<?php echo $home_path; ?>" class="btn btn-primary">🏠 Volver al Dashboard</a>
            <?php else: ?>
                <a href="../../login/login.php" class="btn btn-primary">🔐 Ir al Login</a>
                <a href="../../login/register.php" class="btn btn-secondary">📝 Ir al Registro</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../../components/footer.php'; ?>
</body>
</html>