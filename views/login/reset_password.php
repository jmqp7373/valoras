<?php
header('Content-Type: text/html; charset=UTF-8');

try {
    require_once '../../config/database.php';
    require_once '../../controllers/login/PasswordResetController.php';
} catch (Exception $e) {
    die('Error al cargar archivos necesarios: ' . $e->getMessage());
}

// Verificar si ya está logueado
try {
    startSessionSafely();
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        header('Location: ../../index.php');
        exit();
    }
} catch (Exception $e) {
    // Si hay error con la sesión, continuar sin verificar login
    error_log('Error en startSessionSafely: ' . $e->getMessage());
}

$passwordController = new PasswordResetController();
$result = null;
$step = 1; // 1 = Ingresar código, 2 = Nueva contraseña
$cedula = '';
$userInfo = null;

// Verificar si viene de password_reset.php
if(isset($_SESSION['reset_code_sent']) && $_SESSION['reset_code_sent'] === true) {
    // Usar información de la sesión
    $cedula = $_SESSION['reset_user_identifier'] ?? '';
    $contactMethod = $_SESSION['reset_contact_method'] ?? 'email';
    
    // Obtener información del usuario para mostrar
    if($cedula) {
        $userResult = $passwordController->findUser($cedula, $_SESSION['reset_identification_method'] ?? 'cedula');
        if($userResult['success']) {
            $userInfo = $userResult['user_data'];
            $userInfo['contact_method'] = $contactMethod;
            $userInfo['masked_email'] = $userResult['masked_email'] ?? null;
            $userInfo['masked_phone'] = $userResult['masked_phone'] ?? null;
        }
    }
}

// Determinar paso actual
if(isset($_POST['cedula']) && isset($_POST['code'])) {
    $step = 2;
    $cedula = $_POST['cedula'];
    $code = $_POST['code'];
    
    // Verificar código
    $codeResult = $passwordController->verifyCode($cedula, $code);
    if(!$codeResult['success']) {
        $step = 1;
        $result = $codeResult;
    }
}

// Procesar nueva contraseña (paso 2)
if($_SERVER['REQUEST_METHOD'] == 'POST' && $step == 2 && isset($_POST['password'])) {
    if($_POST['password'] !== $_POST['confirm_password']) {
        $result = [
            'success' => false,
            'message' => 'Las contraseñas no coinciden'
        ];
    } else {
        $result = $passwordController->resetPassword($_POST['cedula'], $_POST['code'], $_POST['password']);
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
    <title>Recuperar Contraseña - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../components/marcaPasos.css">
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
        
        .btn-back:hover {
            background-color: #882A57 !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 42, 87, 0.3);
        }
    </style>
</head>
<body>
    <?php include '../../components/marcaPasos.php'; ?>
    
    <!-- CONTENEDOR PRINCIPAL SIN PADDING -->
    <div style="width: 100%; max-width: 380px; margin: 20px auto;">
        <!-- MARCA PASOS -->
        <div style="margin-bottom: 20px;">
            <?php renderMarcaPasos(3, 3); // Paso 3 de 3: identify → select_method → reset_password ?>
        </div>
    
    <div class="login-container">
        <img src="../../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo">
        <h2>🔑 Crear Nueva Contraseña</h2>
        
        <?php if($result): ?>
            <?php if($result['success'] && $step == 3): ?>
                <div class="alert alert-success" style="background-color: #efe; border: 1px solid #cfc; color: #3c3; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    ✅ <?php echo htmlspecialchars($result['message']); ?>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" onclick="window.location.href='login.php'" class="btn-submit">
                        🚀 Iniciar Sesión
                    </button>
                </div>
                
            <?php else: ?>
                <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    ❌ <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if($step == 1): ?>
            <?php if($userInfo): ?>
                <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px; text-align: center;">
                    <strong>📋 Información del Usuario:</strong><br>
                    <span style="color: #666; font-size: 14px;">
                        <?php echo htmlspecialchars(($userInfo['nombres'] ?? '') . ' ' . ($userInfo['apellidos'] ?? '')); ?> <br>
                        Cédula: <?php echo htmlspecialchars($userInfo['cedula'] ?? ''); ?><br>
                        Código enviado a: 
                        <?php if($contactMethod === 'sms'): ?>
                            📱 <?php echo htmlspecialchars($userInfo['celular'] ?? ''); ?>
                        <?php else: ?>
                            📧 <?php echo htmlspecialchars($userInfo['email'] ?? ''); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Ingresa el código de 6 dígitos que recibiste
                </p>
            <?php else: ?>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Ingresa el código de 6 dígitos que recibiste y tu número de cédula
                </p>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="cedula">Número de Cédula:</label>
                    <input type="text" id="cedula" name="cedula" placeholder="Tu número de cédula" required 
                           value="<?php echo htmlspecialchars($userInfo['cedula'] ?? $_POST['cedula'] ?? ''); ?>"
                           <?php echo $userInfo ? 'readonly style="background-color: #f8f9fa;"' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="code">Código de Verificación:</label>
                    <input type="text" id="code" name="code" class="code-input" 
                           placeholder="123456" maxlength="6" required 
                           pattern="[0-9]{6}" title="Debe ser un código de 6 dígitos">
                </div>
                
                <div style="background-color: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; padding: 15px; margin: 20px 0; font-size: 14px;">
                    <strong>💡 ¿No recibiste el código?</strong>
                    <ul style="margin: 10px 0 0 20px; color: #666;">
                        <li>Revisa tu carpeta de spam o correo no deseado</li>
                        <li>Verifica que tu número de celular esté correcto</li>
                        <li>El código expira en 10 minutos</li>
                        <li><a href="password_reset.php" style="color: #ee6f92;">Solicitar nuevo código</a></li>
                    </ul>
                </div>
                
                <button type="submit" class="btn-submit">
                    🔍 Verificar Código
                </button>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="password_reset.php" class="btn-back" style="display: inline-block; padding: 12px 24px; color: #882A57; text-decoration: none; border: 2px solid #882A57; border-radius: 12px; font-weight: 500; transition: all 0.3s ease;">
                        ← Volver al paso anterior
                    </a>
                </div>
            </form>
            
        <?php elseif($step == 2): ?>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Código verificado ✅ Ahora crea una nueva contraseña segura
            </p>
            
            <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                <strong>👤 Usuario:</strong> <?php echo htmlspecialchars($cedula); ?>
            </div>
            
            <form method="POST" action="" id="resetForm">
                <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>">
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                
                <div class="form-group">
                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                    <div class="password-strength" id="strengthMeter" style="margin-top: 5px; font-size: 12px;"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la contraseña" required minlength="6">
                    <div id="matchIndicator" style="margin-top: 5px; font-size: 12px;"></div>
                </div>
                
                <div style="background-color: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; padding: 15px; margin: 20px 0; font-size: 14px;">
                    <strong>💡 Consejos para una contraseña segura:</strong>
                    <ul style="margin: 10px 0 0 20px; color: #666;">
                        <li>Usa al menos 8 caracteres</li>
                        <li>Combina letras mayúsculas y minúsculas</li>
                        <li>Incluye números y símbolos</li>
                        <li>Evita información personal obvia</li>
                    </ul>
                </div>
                
                <button type="submit" id="submitBtn" class="btn-submit" disabled>
                    🔐 Actualizar Contraseña
                </button>
                
                <div style="text-align: center; margin-top: 15px;">
                    <button type="button" onclick="history.back()" class="btn-back" style="padding: 12px 24px; color: #882A57; background: white; border: 2px solid #882A57; border-radius: 12px; font-weight: 500; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 16px; transition: all 0.3s ease;">
                        ← Volver al paso anterior
                    </button>
                </div>
            </form>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 14px;">
            <div style="margin-bottom: 12px;">
                <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">← Volver al inicio de sesión</a>
            </div>
            <div>
                ¿Necesitas ayuda? <a href="mailto:soporte@valora.vip" style="color: #882A57; text-decoration: none; font-weight: 500;">Contacta soporte</a>
            </div>
        </div>
    </div>

    <script>
        // Solo aplicar scripts si estamos en el paso 2
        <?php if($step == 2): ?>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthMeter = document.getElementById('strengthMeter');
        const matchIndicator = document.getElementById('matchIndicator');
        const submitBtn = document.getElementById('submitBtn');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            const levels = [
                { min: 0, max: 2, text: '🔴 Muy débil', color: '#dc3545' },
                { min: 3, max: 3, text: '🟡 Débil', color: '#ffc107' },
                { min: 4, max: 4, text: '🟠 Regular', color: '#fd7e14' },
                { min: 5, max: 5, text: '🟢 Fuerte', color: '#28a745' },
                { min: 6, max: 6, text: '💚 Muy fuerte', color: '#20c997' }
            ];
            
            const level = levels.find(l => strength >= l.min && strength <= l.max);
            return { strength, ...level };
        }
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword === '') {
                matchIndicator.innerHTML = '';
                return false;
            }
            
            if (password === confirmPassword) {
                matchIndicator.innerHTML = '<span style="color: #28a745;">✅ Las contraseñas coinciden</span>';
                return true;
            } else {
                matchIndicator.innerHTML = '<span style="color: #dc3545;">❌ Las contraseñas no coinciden</span>';
                return false;
            }
        }
        
        function updateSubmitButton() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const strengthResult = checkPasswordStrength(password);
            const passwordsMatch = checkPasswordMatch();
            
            const isValid = password.length >= 6 && 
                          confirmPassword.length >= 6 && 
                          passwordsMatch && 
                          strengthResult.strength >= 3;
            
            submitBtn.disabled = !isValid;
            submitBtn.style.opacity = isValid ? '1' : '0.6';
        }
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            if (password === '') {
                strengthMeter.innerHTML = '';
            } else {
                const result = checkPasswordStrength(password);
                strengthMeter.innerHTML = `<span style="color: ${result.color};">${result.text}</span>`;
            }
            updateSubmitButton();
        });
        
        confirmPasswordInput.addEventListener('input', updateSubmitButton);
        
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
        <?php endif; ?>
        
        // Formatear input de código para que solo acepte números
        <?php if($step == 1): ?>
        document.getElementById('code').addEventListener('input', function(e) {
            // Solo permitir números
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limitar a 6 dígitos
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
        
        // Auto-enviar cuando se ingresen 6 dígitos
        document.getElementById('code').addEventListener('input', function(e) {
            if (this.value.length === 6 && document.getElementById('cedula').value.length > 0) {
                // Opcional: auto-enviar el formulario
                // document.querySelector('form').submit();
            }
        });
        <?php endif; ?>
    </script>
</div> <!-- /CONTENEDOR PRINCIPAL -->
</body>
</html>