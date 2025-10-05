<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Valoras</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
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