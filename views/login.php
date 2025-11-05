<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../controllers/AuthController.php';

$authController = new AuthController();
$loginResult = null;

// Verificar si ya está logueado
startSessionSafely();
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../index.php');
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
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <!-- Incluir el logo de Valora ubicado en assets/images/logo_valoras.png -->
        <img src="../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
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
                <input type="text" id="Numero_de_cedula" placeholder="Número de identificación" name="Numero_de_cedula" required>
            </div>
            
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" placeholder="Contraseña" name="contraseña" required>
            </div>
            
            <button type="submit" class="btn-submit">Ingresar</button>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="password_reset.php" style="color: #882A57; text-decoration: none; font-size: 14px; font-weight: 500;">
                🔑 ¿Olvidaste tu contraseña?
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            ¿Aún no tienes una cuenta? <a href="register.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Regístrate aquí</a>
        </div>
    </div>
</body>
</html>