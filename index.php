<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hola Mundo - PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        h1 {
            font-size: 3em;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .php-info {
            margin-top: 20px;
            font-size: 1.2em;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
            $mensaje = "¡Hola Mundo!";
            echo "<h1>$mensaje</h1>";
            
            $fecha = date('d/m/Y H:i:s');
            echo "<div class='php-info'>";
            echo "Ejecutándose con PHP " . phpversion() . "<br>";
            echo "Fecha y hora: " . $fecha . "<br>";
            echo "🚀 Deployment automático con GitHub Actions";
            echo "</div>";
        ?>
    </div>
</body>
</html>