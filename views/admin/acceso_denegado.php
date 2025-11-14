<?php
/**
 * Página de Acceso Denegado con Contador Regresivo
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-12
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - Valora.vip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            padding: 20px;
            overflow: hidden;
        }

        .container {
            text-align: center;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.98);
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-container {
            margin-bottom: 30px;
            position: relative;
        }

        .logo-valora {
            max-width: 200px;
            height: auto;
            margin: 0 auto;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(106, 27, 27, 0.3));
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .shield-icon {
            width: 120px;
            height: 120px;
            margin: 20px auto 0;
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(106, 27, 27, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(106, 27, 27, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(136, 42, 87, 0.5);
            }
        }

        .shield-icon svg {
            width: 60px;
            height: 60px;
            fill: white;
        }

        h1 {
            color: #6A1B1B;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #555;
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .reason {
            background: rgba(106, 27, 27, 0.1);
            color: #6A1B1B;
            padding: 15px 25px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            margin: 25px 0;
            border-left: 4px solid #882A57;
        }

        .countdown-container {
            margin: 40px 0 30px;
            text-align: center;
        }

        .countdown-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .countdown {
            display: inline-flex;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(106, 27, 27, 0.3);
            position: relative;
            animation: countdownPulse 1s ease-in-out infinite;
        }

        @keyframes countdownPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.08);
            }
        }

        #segundos {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .progress-ring {
            position: absolute;
            top: -5px;
            left: -5px;
            transform: rotate(-90deg);
        }

        .progress-ring-circle {
            fill: none;
            stroke: rgba(255, 255, 255, 0.3);
            stroke-width: 4;
        }

        .progress-ring-circle-active {
            fill: none;
            stroke: white;
            stroke-width: 4;
            stroke-dasharray: 345;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 1s linear;
            stroke-linecap: round;
        }

        .footer-text {
            color: #888;
            font-size: 0.85rem;
            margin-top: 30px;
            font-weight: 400;
        }

        .footer-text strong {
            color: #6A1B1B;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                padding: 40px 25px;
            }

            h1 {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .shield-icon {
                width: 100px;
                height: 100px;
            }

            .shield-icon svg {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <img src="../../assets/images/logos/logo_valora.png" alt="Valora.vip" class="logo-valora">
            <div class="shield-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                    <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                    <path d="M7.001 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.553.553 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
            </div>
        </div>

        <h1>Acceso Denegado</h1>
        
        <p class="subtitle">
            No tienes los permisos necesarios para acceder a esta sección.
        </p>

        <div class="reason">
            Solo administradores pueden acceder a esta zona del sistema.
        </div>

        <div class="countdown-container">
            <p class="countdown-label">Redirigiendo en:</p>
            <div class="countdown">
                <svg class="progress-ring" width="110" height="110">
                    <circle class="progress-ring-circle" cx="55" cy="55" r="52"></circle>
                    <circle class="progress-ring-circle-active" id="progressCircle" cx="55" cy="55" r="52"></circle>
                </svg>
                <span id="segundos">10</span>
            </div>
        </div>

        <p class="footer-text">
            Serás redirigido a <strong>Inicio</strong> automáticamente
        </p>
    </div>

    <script>
        let segundosRestantes = 10;
        const elementoSegundos = document.getElementById('segundos');
        const progressCircle = document.getElementById('progressCircle');
        const circumference = 2 * Math.PI * 52; // 2πr
        
        // Configurar el círculo de progreso
        progressCircle.style.strokeDasharray = circumference;
        progressCircle.style.strokeDashoffset = 0;

        function actualizarContador() {
            segundosRestantes--;
            elementoSegundos.textContent = segundosRestantes;
            
            // Actualizar el círculo de progreso
            const offset = circumference - (segundosRestantes / 10) * circumference;
            progressCircle.style.strokeDashoffset = offset;
            
            if (segundosRestantes <= 0) {
                window.location.href = '../../index.php';
            }
        }

        // Iniciar contador cada segundo
        setInterval(actualizarContador, 1000);
    </script>
</body>
</html>
