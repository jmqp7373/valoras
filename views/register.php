<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Valora</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <!-- Incluir el logo de Valora ubicado en assets/images/logo_valoras.png -->
        <img src="../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>Crear Cuenta</h2>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nombres">Nombres:</label>
                <input type="text" id="nombres" placeholder="Nombres completos" name="nombres" required>
            </div>
            
            <div class="form-group">
                <label for="apellidos">Apellidos:</label>
                <input type="text" id="apellidos" placeholder="Apellidos completos" name="apellidos" required>
            </div>
            
            <div class="form-group">
                <label for="numero_cedula">Número de Cédula:</label>
                <input type="text" id="numero_cedula" placeholder="Cédula" name="numero_cedula" required>
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" placeholder="correo@ejemplo.com" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" placeholder="Número de teléfono" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" placeholder="Contraseña" name="contraseña" required>
            </div>
            
            <div class="form-group">
                <label for="confirmar_contraseña">Confirmar Contraseña:</label>
                <input type="password" id="confirmar_contraseña" placeholder="Confirmar contraseña" name="confirmar_contraseña" required>
            </div>
            
            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            ¿Ya tienes una cuenta? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>