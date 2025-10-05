<?php
header('Content-Type: text/html; charset=UTF-8');
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
        
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            ¿Aún no tienes una cuenta? <a href="register.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Regístrate aquí</a>
        </div>
    </div>
</body>
</html>