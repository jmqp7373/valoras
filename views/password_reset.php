<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../controllers/PasswordResetController.php';

$passwordController = new PasswordResetController();
$result = null;
$step = 'identify'; // 'identify', 'select_method', 'sent'

// Verificar si ya está logueado
startSessionSafely();
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../index.php');
    exit();
}

// Procesar formularios
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'identify':
                $result = $passwordController->findUser($_POST['cedula']);
                if($result['success']) {
                    $step = 'select_method';
                }
                break;
            case 'send_reset':
                $result = $passwordController->sendResetLink($_POST['cedula'], $_POST['method']);
                if($result['success']) {
                    $step = 'sent';
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Valora</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <img src="../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>🔐 Recuperar Contraseña</h2>
        
        <?php if($result && !$result['success']): ?>
            <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($result['message']); ?>
            </div>
        <?php endif; ?>

        <?php if($step === 'identify'): ?>
            <!-- Paso 1: Identificar usuario -->
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Ingresa tu número de cédula para recuperar tu contraseña
            </p>
            
            <form action="password_reset.php" method="POST">
                <input type="hidden" name="action" value="identify">
                
                <div class="form-group">
                    <label for="cedula">Número de identificación (Cédula):</label>
                    <input type="text" id="cedula" placeholder="Número de identificación" name="cedula" required>
                </div>
                
                <button type="submit" class="btn-submit">🔍 Buscar Cuenta</button>
            </form>

        <?php elseif($step === 'select_method'): ?>
            <!-- Paso 2: Seleccionar método de recuperación -->
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Selecciona cómo quieres recibir el enlace de recuperación:
            </p>
            
            <form action="password_reset.php" method="POST">
                <input type="hidden" name="action" value="send_reset">
                <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($_POST['cedula']); ?>">
                
                <!-- Alertas de validación -->
                <?php if(isset($result['email_alert']) || isset($result['phone_alert'])): ?>
                    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <h4 style="color: #856404; margin: 0 0 10px 0; font-size: 16px;">⚠️ Advertencias de Validación</h4>
                        <?php if(isset($result['email_alert'])): ?>
                            <div style="color: #856404; font-size: 14px; margin-bottom: 8px;">
                                <?php echo $result['email_alert']; ?>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($result['phone_alert'])): ?>
                            <div style="color: #856404; font-size: 14px;">
                                <?php echo $result['phone_alert']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                    // Determinar cuál es la primera opción válida para preseleccionar
                    $firstValidMethod = null;
                    if($result['masked_email']) {
                        $firstValidMethod = 'email';
                    } elseif($result['masked_phone']) {
                        $firstValidMethod = 'sms';
                    }
                ?>

                <div style="margin-bottom: 20px;">
                    <?php if($result['masked_email']): ?>
                        <div class="method-option" style="border: 2px solid #28a745; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s; background-color: #f8fff9;">
                            <input type="radio" id="method_email" name="method" value="email" required style="margin-right: 10px;" <?php echo ($firstValidMethod === 'email') ? 'checked' : ''; ?>>
                            <label for="method_email" style="cursor: pointer; font-weight: 500;">
                                📧 Correo electrónico ✅
                                <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                    Enviar a: <?php echo $result['masked_email']; ?>
                                </div>
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="method-option-disabled" style="border: 2px solid #dc3545; border-radius: 8px; padding: 15px; margin-bottom: 15px; background-color: #fff5f5; opacity: 0.7;">
                            <input type="radio" disabled style="margin-right: 10px;">
                            <label style="cursor: not-allowed; font-weight: 500; color: #dc3545;">
                                📧 Correo electrónico ❌
                                <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">
                                    Email no válido - No disponible
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($result['masked_phone']): ?>
                        <div class="method-option" style="border: 2px solid #28a745; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s; background-color: #f8fff9;">
                            <input type="radio" id="method_sms" name="method" value="sms" required style="margin-right: 10px;" <?php echo ($firstValidMethod === 'sms') ? 'checked' : ''; ?>>
                            <label for="method_sms" style="cursor: pointer; font-weight: 500;">
                                📱 Mensaje SMS ✅
                                <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                    Enviar a: <?php echo $result['masked_phone']; ?>
                                </div>
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="method-option-disabled" style="border: 2px solid #dc3545; border-radius: 8px; padding: 15px; margin-bottom: 15px; background-color: #fff5f5; opacity: 0.7;">
                            <input type="radio" disabled style="margin-right: 10px;">
                            <label style="cursor: not-allowed; font-weight: 500; color: #dc3545;">
                                📱 Mensaje SMS ❌
                                <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">
                                    Celular no válido - No disponible
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" id="sendButton" class="btn-submit">
                    <span id="buttonText">📤 Enviar Enlace</span>
                    <span id="buttonLoader" class="loading-spinner" style="display: none;">
                        <span class="spinner"></span>
                        Enviando...
                    </span>
                </button>
                <button type="button" onclick="window.location.href='password_reset.php'" class="btn-secondary" style="margin-top: 10px; background-color: #f0f0f0; color: #666;">
                    ← Volver
                </button>
            </form>

        <?php elseif($step === 'sent'): ?>
            <!-- Paso 3: Confirmación de envío -->
            <div class="alert alert-success" style="background-color: #efe; border: 1px solid #cfc; color: #3c3; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                ✅ <?php echo htmlspecialchars($result['message']); ?>
            </div>
            
            <div style="text-align: center; color: #666; margin-bottom: 30px;">
                <p>Revisa tu <?php echo $_POST['method'] === 'email' ? 'correo electrónico' : 'mensajes SMS'; ?> y sigue las instrucciones.</p>
                <p style="font-size: 14px;">Si no recibes el mensaje en unos minutos, revisa tu carpeta de spam.</p>
            </div>
            
            <div style="text-align: center;">
                <button type="button" onclick="window.location.href='login.php'" class="btn-submit">
                    ← Volver al Login
                </button>
                <button type="button" onclick="window.location.href='password_reset.php'" class="btn-secondary" style="margin-top: 10px; background-color: #f0f0f0; color: #666;">
                    🔄 Intentar de Nuevo
                </button>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 14px;">
            ¿Recordaste tu contraseña? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión</a>
        </div>
    </div>

    <style>
        .method-option:hover {
            border-color: #882A57 !important;
            background-color: #fafafa;
        }
        
        .method-option input[type="radio"]:checked + label {
            color: #882A57;
        }
        
        .method-option:has(input[type="radio"]:checked),
        .method-option.selected {
            border-color: #882A57 !important;
            background-color: #f9f5f7;
        }
        
        .btn-secondary {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0 !important;
            border-color: #bbb;
        }
        
        /* Estilos para animación del botón */
        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-submit.loading {
            background: linear-gradient(-45deg, #882A57, #9d3461, #882A57, #9d3461);
            background-size: 400% 400%;
            animation: gradientShift 2s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .btn-submit.pulse {
            animation: pulse 0.6s;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo de la animación del botón de envío
            const form = document.querySelector('form[action="password_reset.php"]');
            const sendButton = document.getElementById('sendButton');
            const buttonText = document.getElementById('buttonText');
            const buttonLoader = document.getElementById('buttonLoader');
            
            if (form && sendButton) {
                form.addEventListener('submit', function(e) {
                    // Verificar que hay un método seleccionado
                    const selectedMethod = document.querySelector('input[name="method"]:checked');
                    if (!selectedMethod) {
                        e.preventDefault();
                        alert('Por favor selecciona un método de envío');
                        return;
                    }
                    
                    // Iniciar animación
                    startLoadingAnimation();
                });
            }
            
            function startLoadingAnimation() {
                if (sendButton && buttonText && buttonLoader) {
                    // Ocultar texto normal y mostrar loader
                    buttonText.style.display = 'none';
                    buttonLoader.style.display = 'flex';
                    
                    // Deshabilitar botón y agregar clases de animación
                    sendButton.disabled = true;
                    sendButton.classList.add('loading');
                    
                    // Efecto de pulso inicial
                    sendButton.classList.add('pulse');
                    setTimeout(() => {
                        sendButton.classList.remove('pulse');
                    }, 600);
                    
                    // Cambiar texto del loader progresivamente
                    setTimeout(() => {
                        if (buttonLoader.querySelector('span:last-child')) {
                            buttonLoader.querySelector('span:last-child').textContent = 'Validando datos...';
                        }
                    }, 1000);
                    
                    setTimeout(() => {
                        if (buttonLoader.querySelector('span:last-child')) {
                            buttonLoader.querySelector('span:last-child').textContent = 'Enviando email...';
                        }
                    }, 2000);
                    
                    setTimeout(() => {
                        if (buttonLoader.querySelector('span:last-child')) {
                            buttonLoader.querySelector('span:last-child').textContent = 'Finalizando...';
                        }
                    }, 3000);
                }
            }
            
            // Mejorar la experiencia de selección de métodos
            const methodOptions = document.querySelectorAll('.method-option');
            methodOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]:not([disabled])');
                    if (radio) {
                        radio.checked = true;
                        
                        // Efecto visual de selección
                        methodOptions.forEach(opt => opt.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        // Efecto de confirmación
                        this.style.transform = 'scale(1.02)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 150);
                    }
                });
            });
            
            // Preseleccionar visualmente el método ya marcado
            const checkedRadio = document.querySelector('input[name="method"]:checked');
            if (checkedRadio) {
                const parentOption = checkedRadio.closest('.method-option');
                if (parentOption) {
                    parentOption.classList.add('selected');
                }
            }
        });
    </script>
</body>
</html>