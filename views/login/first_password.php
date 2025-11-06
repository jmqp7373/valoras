<?php
header('Content-Type: text/html; charset=UTF-8');

try {
    require_once '../../config/database.php';
    require_once '../../controllers/login/FirstPasswordController.php';
} catch (Exception $e) {
    die('Error al cargar archivos necesarios: ' . $e->getMessage());
}

// Iniciar sesión
try {
    startSessionSafely();
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        header('Location: ../../index.php');
        exit();
    }
} catch (Exception $e) {
    error_log('Error en startSessionSafely: ' . $e->getMessage());
}

$firstPasswordController = new FirstPasswordController();
$result = null;
$step = 1; // 1 = Ingresar código, 2 = Nueva contraseña, 3 = Éxito
$cedula = '';
$userInfo = null;

// Verificar si viene del registro
if(isset($_SESSION['first_password_cedula'])) {
    $cedula = $_SESSION['first_password_cedula'];
    
    // Obtener información del usuario
    $userResult = $firstPasswordController->getUserInfo($cedula);
    if($userResult['success']) {
        $userInfo = $userResult['user_data'];
        $userInfo['masked_phone'] = $userResult['masked_phone'];
    }
} else {
    // Si no hay sesión de registro, redirigir al login
    header('Location: login.php');
    exit();
}

// Procesar verificación de código (paso 1 -> paso 2)
if(isset($_POST['cedula']) && isset($_POST['code'])) {
    $step = 2;
    $cedula = $_POST['cedula'];
    $code = $_POST['code'];
    
    // Verificar código
    $codeResult = $firstPasswordController->verifyFirstCode($cedula, $code);
    if(!$codeResult['success']) {
        $step = 1;
        $result = $codeResult;
    }
}

// Procesar creación de contraseña (paso 2 -> paso 3)
if($_SERVER['REQUEST_METHOD'] == 'POST' && $step == 2 && isset($_POST['password'])) {
    if($_POST['password'] !== $_POST['confirm_password']) {
        $result = [
            'success' => false,
            'message' => 'Las contraseñas no coinciden'
        ];
    } else {
        $result = $firstPasswordController->setFirstPassword($_POST['cedula'], $_POST['code'], $_POST['password']);
        if($result['success']) {
            $step = 3; // Éxito
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Primera Contraseña - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .code-input {
            font-size: 24px;
            letter-spacing: 8px;
            text-align: center;
            font-family: 'Courier New', monospace;
            padding: 15px;
            border: 2px solid #ee6f92;
            border-radius: 12px;
            background: #f8f9fa;
        }
        .code-input:focus {
            outline: none;
            border-color: #d63384;
            box-shadow: 0 0 0 3px rgba(238, 111, 146, 0.1);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        .step.active {
            background: linear-gradient(135deg, #ee6f92 0%, #d63384 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(238, 111, 146, 0.3);
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .step-line {
            width: 60px;
            height: 3px;
            background: #e9ecef;
        }
        .step-line.active {
            background: linear-gradient(90deg, #ee6f92 0%, #d63384 100%);
        }
        .info-box {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #0c5460;
        }
        .info-box strong {
            color: #004085;
        }
        .password-requirements {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .password-requirements ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin: 5px 0;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }
        .btn-resend {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-resend:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
        }
        .btn-resend:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="../../assets/images/logos/logo_valora.png" class='logo' alt="Valora Logo">
        
        <?php if($step == 1): ?>
            <!-- PASO 1: Verificar código -->
            <h2>Verifica tu número</h2>
            
            <div class="step-indicator">
                <div class="step active">1</div>
                <div class="step-line"></div>
                <div class="step">2</div>
            </div>
            
            <?php if($userInfo): ?>
            <div class="info-box">
                📱 Hemos enviado un código de verificación de <strong>6 dígitos</strong> al número:<br>
                <strong><?php echo htmlspecialchars($userInfo['masked_phone']); ?></strong>
            </div>
            <?php endif; ?>
            
            <?php if($result && !$result['success']): ?>
                <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="first_password.php">
                <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>">
                
                <div class="form-group">
                    <label for="code">Código de verificación (6 dígitos):</label>
                    <input type="text" 
                           id="code" 
                           name="code" 
                           class="code-input" 
                           maxlength="6" 
                           pattern="[0-9]{6}" 
                           placeholder="000000"
                           autocomplete="off"
                           required
                           autofocus>
                </div>
                
                <button type="submit" class="btn-submit">Verificar código</button>
            </form>
            
            <!-- Sección de reenvío de código -->
            <div id="resend-section" style="margin-top: 25px; text-align: center; background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">
                    📱 ¿No recibiste tu código de verificación?
                </p>
                <button id="resend-btn" onclick="resendFirstPasswordCode()" class="btn-resend" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    Reenviar código
                </button>
                <span id="countdown" style="display: inline-block; margin-left: 10px; color: #17a2b8; font-weight: 600; font-size: 14px;"></span>
                <div id="resend-message" style="margin-top: 15px;"></div>
            </div>
            
        <?php elseif($step == 2): ?>
            <!-- PASO 2: Crear contraseña -->
            <h2>Crea tu contraseña</h2>
            
            <div class="step-indicator">
                <div class="step completed">✓</div>
                <div class="step-line active"></div>
                <div class="step active">2</div>
            </div>
            
            <div class="password-requirements">
                <strong>📋 Requisitos de la contraseña:</strong>
                <ul>
                    <li>Mínimo 6 caracteres</li>
                    <li>Se recomienda incluir letras, números y símbolos</li>
                    <li>Evita contraseñas fáciles de adivinar</li>
                </ul>
            </div>
            
            <?php if($result && !$result['success']): ?>
                <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="first_password.php">
                <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>">
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                
                <div class="form-group">
                    <label for="password">Nueva contraseña:</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Ingresa tu nueva contraseña"
                           minlength="6"
                           required
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña:</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Confirma tu nueva contraseña"
                           minlength="6"
                           required>
                </div>
                
                <button type="submit" class="btn-submit">Crear contraseña</button>
            </form>
            
        <?php elseif($step == 3): ?>
            <!-- PASO 3: Éxito -->
            <div class="success-icon">✓</div>
            <h2 style="color: #28a745; text-align: center;">¡Contraseña creada!</h2>
            
            <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <p style="margin: 0 0 10px 0; color: #155724; font-size: 16px;">
                    <strong>Tu cuenta está lista</strong>
                </p>
                <p style="margin: 0; color: #155724;">
                    Ya puedes iniciar sesión con tu número de cédula y tu nueva contraseña.
                </p>
            </div>
            
            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <p style="margin: 0; color: #856404; font-size: 14px;">
                    ⏱️ Serás redirigido automáticamente en <strong id="countdown">5</strong> segundos...
                </p>
            </div>
            
            <a href="login.php" class="btn-submit" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">
                Ir a iniciar sesión ahora
            </a>
            
            <script>
                // Contador regresivo de 5 segundos
                let timeLeft = 5;
                const countdownElement = document.getElementById('countdown');
                
                const countdownTimer = setInterval(function() {
                    timeLeft--;
                    countdownElement.textContent = timeLeft;
                    
                    if (timeLeft <= 0) {
                        clearInterval(countdownTimer);
                        window.location.href = 'login.php';
                    }
                }, 1000);
            </script>
            
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 14px;">
            ¿Ya tienes contraseña? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión aquí</a>
        </div>
    </div>
    
    <script>
        // Auto-formato del código: solo números
        const codeInput = document.getElementById('code');
        if(codeInput) {
            codeInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
            });
            
            // Submit automático cuando se completan 6 dígitos
            codeInput.addEventListener('input', function(e) {
                if(this.value.length === 6) {
                    // Opcional: auto-submit
                    // this.form.submit();
                }
            });
        }
        
        // Validación de contraseñas
        const passwordForm = document.querySelector('form');
        if(passwordForm && document.getElementById('password')) {
            passwordForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if(password !== confirmPassword) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    return false;
                }
                
                if(password.length < 6) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 6 caracteres');
                    return false;
                }
            });
        }
        
        // ============ CONTADOR REGRESIVO Y REENVÍO DE CÓDIGO ============
        <?php if($step == 1 && isset($_SESSION['first_password_sent_at'])): ?>
        // Configuración del contador regresivo
        let lastSentTime = <?php echo $_SESSION['first_password_sent_at']; ?>;
        
        function updateCountdown() {
            const now = Math.floor(Date.now() / 1000);
            const elapsed = now - lastSentTime;
            const remaining = Math.max(0, 60 - elapsed);
            
            const countdownElement = document.getElementById('countdown');
            const resendBtn = document.getElementById('resend-btn');
            
            if (remaining > 0) {
                countdownElement.textContent = `(${remaining}s)`;
                resendBtn.disabled = true;
                resendBtn.style.opacity = '0.6';
                resendBtn.style.cursor = 'not-allowed';
            } else {
                countdownElement.textContent = '';
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
                resendBtn.style.cursor = 'pointer';
            }
            
            if (remaining > 0) {
                setTimeout(updateCountdown, 1000);
            }
        }
        
        function resendFirstPasswordCode() {
            const cedula = '<?php echo htmlspecialchars($cedula); ?>';
            const messageDiv = document.getElementById('resend-message');
            const resendBtn = document.getElementById('resend-btn');
            
            if (!cedula) {
                messageDiv.innerHTML = '<div style="color: #dc3545; background: #fff5f5; padding: 10px; border-radius: 6px; margin-top: 10px;">⚠️ Error: No se encontró el número de cédula</div>';
                return;
            }
            
            // Deshabilitar el botón durante el envío
            resendBtn.disabled = true;
            resendBtn.textContent = 'Enviando...';
            resendBtn.style.opacity = '0.6';
            
            // Realizar la solicitud AJAX
            const formData = new FormData();
            formData.append('action', 'resend_first_password_code');
            formData.append('cedula', cedula);
            
            fetch('../../controllers/login/AuthController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<div style="color: #28a745; background: #d4edda; padding: 10px; border-radius: 6px; margin-top: 10px;">✅ ' + data.message + '</div>';
                    // Actualizar el timestamp y reiniciar el countdown
                    lastSentTime = Math.floor(Date.now() / 1000);
                    resendBtn.textContent = 'Reenviar código';
                    updateCountdown();
                } else {
                    messageDiv.innerHTML = '<div style="color: #dc3545; background: #fff5f5; padding: 10px; border-radius: 6px; margin-top: 10px;">❌ ' + data.message + '</div>';
                    // Reactivar el botón si hay error
                    resendBtn.disabled = false;
                    resendBtn.style.opacity = '1';
                    resendBtn.textContent = 'Reenviar código';
                }
                
                // Limpiar mensaje después de 5 segundos
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 5000);
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.innerHTML = '<div style="color: #dc3545; background: #fff5f5; padding: 10px; border-radius: 6px; margin-top: 10px;">❌ Error de conexión. Por favor, intenta de nuevo.</div>';
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
                resendBtn.textContent = 'Reenviar código';
                
                // Limpiar mensaje después de 5 segundos
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 5000);
            });
        }
        
        // Iniciar el contador al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdown();
        });
        <?php endif; ?>
    </script>
</body>
</html>
