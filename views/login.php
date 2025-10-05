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
        <img src="../assets/images/logos/logo_valora.png" alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone" style="max-width: 150px;">
        <h2>Iniciar Sesión</h2>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="Número de Cédula">Número de Cédula:</label>
                <input type="text" id="Numero_de_cedula" placeholder="Cédula" name="Numero_de_cedula" required>
            </div>
            
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" placeholder="Contraseña" name="contraseña" required>
            </div>
            
            <button type="submit" class="btn-submit">Ingresar</button>
        </form>
    </div>
</body>
</html>