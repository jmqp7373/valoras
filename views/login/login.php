<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../../controllers/login/AuthController.php';

$authController = new AuthController();
$loginResult = null;

// Verificar si ya está logueado
startSessionSafely();
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../../index.php');
    exit();
}

// Procesar el formulario de login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loginResult = $authController->login();
    if($loginResult['success']) {
        header('Location: ' . $loginResult['redirect']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <!-- Incluir el logo de Valora ubicado en assets/images/logo_valoras.png -->
        <img src="../../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>Iniciar Sesión</h2>
        
        <?php if($loginResult && !$loginResult['success']): ?>
            <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($loginResult['message']); ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <!-- Campo de identificación con label y placeholder más descriptivos -->
                <label for="Numero_de_cedula">Número de identificación (Cédula):</label>
                <input type="text" id="Numero_de_cedula" placeholder="Número de identificación" name="Numero_de_cedula" 
                       value="<?php echo isset($_SESSION['last_registered_cedula']) ? htmlspecialchars($_SESSION['last_registered_cedula']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" placeholder="Contraseña" name="contraseña" required>
            </div>
            
            <button type="submit" class="btn-submit">Ingresar</button>
        </form>
        
        <?php if(isset($_SESSION['last_registered_cedula']) && isset($_SESSION['sms_sent_at'])): ?>
        <!-- Sección de reenvío de código solo visible para usuarios recién registrados -->
        <div id="resend-section" class="resend-section">
            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                📱 ¿No recibiste tu código de verificación?
            </p>
            <button id="resend-btn" onclick="resendCode()" class="btn-resend">
                Reenviar código
            </button>
            <span id="countdown" class="countdown-text"></span>
            <div id="resend-message" class="resend-message"></div>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="password_reset.php" style="color: #882A57; text-decoration: none; font-size: 14px; font-weight: 500;">
                🔑 ¿Olvidaste tu contraseña?
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            ¿Aún no tienes una cuenta? <a href="registranteUserAvailavilitySelect.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Regístrate aquí</a>
        </div>
    </div>

    <?php if(isset($_SESSION['last_registered_cedula']) && isset($_SESSION['sms_sent_at'])): ?>
    <script>
        // Configuración del contador regresivo
        let lastSentTime = <?php echo $_SESSION['sms_sent_at']; ?>;
        let countdown = 60;
        
        function updateCountdown() {
            const now = Math.floor(Date.now() / 1000);
            const elapsed = now - lastSentTime;
            const remaining = Math.max(0, 60 - elapsed);
            
            const countdownElement = document.getElementById('countdown');
            const resendBtn = document.getElementById('resend-btn');
            
            if (remaining > 0) {
                countdownElement.textContent = `(${remaining}s)`;
                resendBtn.disabled = true;
            } else {
                countdownElement.textContent = '';
                resendBtn.disabled = false;
            }
            
            if (remaining > 0) {
                setTimeout(updateCountdown, 1000);
            }
        }
        
        function resendCode() {
            const cedula = document.getElementById('Numero_de_cedula').value;
            const messageDiv = document.getElementById('resend-message');
            
            if (!cedula) {
                messageDiv.innerHTML = '<div class="error">⚠️ Debe ingresar su número de cédula</div>';
                return;
            }
            
            // Deshabilitar el botón durante el envío
            const resendBtn = document.getElementById('resend-btn');
            resendBtn.disabled = true;
            resendBtn.textContent = 'Enviando...';
            
            // Realizar la solicitud AJAX
            const formData = new FormData();
            formData.append('action', 'resend_code');
            formData.append('cedula', cedula);
            
            fetch('../../controllers/login/AuthController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<div class="success">✅ ' + data.message + '</div>';
                    // Actualizar el timestamp y reiniciar el countdown
                    lastSentTime = Math.floor(Date.now() / 1000);
                    updateCountdown();
                } else {
                    messageDiv.innerHTML = '<div class="error">❌ ' + data.message + '</div>';
                    // Reactivar el botón si hay error
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Reenviar código';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.innerHTML = '<div class="error">❌ Error de conexión</div>';
                resendBtn.disabled = false;
                resendBtn.textContent = 'Reenviar código';
            });
        }
        
        // Iniciar el contador al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdown();
        });
    </script>
    <?php endif; ?>
</body>
</html>