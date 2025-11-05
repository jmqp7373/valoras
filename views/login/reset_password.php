<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../../controllers/login/PasswordResetController.php';

$passwordController = new PasswordResetController();
$result = null;
$tokenValid = false;
$cedula = '';

// Verificar token del enlace
if(isset($_GET['token'])) {
    $tokenResult = $passwordController->validateToken($_GET['token']);
    if($tokenResult['success']) {
        $tokenValid = true;
        $cedula = $tokenResult['cedula'];
    } else {
        $result = $tokenResult;
    }
} else {
    $result = [
        'success' => false,
        'message' => 'Enlace inválido. Solicita un nuevo enlace de recuperación.'
    ];
}

// Procesar nueva contraseña
if($_SERVER['REQUEST_METHOD'] == 'POST' && $tokenValid) {
    if($_POST['password'] !== $_POST['confirm_password']) {
        $result = [
            'success' => false,
            'message' => 'Las contraseñas no coinciden'
        ];
    } else {
        $result = $passwordController->resetPassword($_GET['token'], $_POST['password']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <img src="../../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>🔑 Nueva Contraseña</h2>
        
        <?php if($result): ?>
            <?php if($result['success']): ?>
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
                
                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" onclick="window.location.href='password_reset.php'" class="btn-submit">
                        🔄 Solicitar Nuevo Enlace
                    </button>
                </div>
            <?php endif; ?>
            
        <?php elseif($tokenValid): ?>
            
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Crea una nueva contraseña segura para tu cuenta
            </p>
            
            <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                <strong>👤 Usuario:</strong> <?php echo htmlspecialchars($cedula); ?>
            </div>
            
            <form action="" method="POST" id="resetForm">
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
            </form>
            
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 14px;">
            ¿Necesitas ayuda? <a href="mailto:soporte@valora.vip" style="color: #882A57; text-decoration: none; font-weight: 500;">Contacta soporte</a>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthMeter = document.getElementById('strengthMeter');
        const matchIndicator = document.getElementById('matchIndicator');
        const submitBtn = document.getElementById('submitBtn');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];
            
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
        
        // Prevenir envío si las contraseñas no son válidas
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
    </script>
</body>
</html>