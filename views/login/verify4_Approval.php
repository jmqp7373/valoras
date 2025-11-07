<?php
session_start();

// Verificar que viene del paso anterior
if(!isset($_SESSION['ocr_result']) || !isset($_SESSION['contact_updated'])) {
    header('Location: verify1_document.php');
    exit;
}

$userData = $_SESSION['ocr_result']['userData'];
$cedula = $userData['cedula'] ?? '';
$nombres = $userData['nombres'] ?? '';
$apellidos = $userData['apellidos'] ?? '';

// Establecer la fecha límite de aprobación (24 horas desde ahora)
if(!isset($_SESSION['approval_deadline'])) {
    $_SESSION['approval_deadline'] = time() + (24 * 60 * 60); // 24 horas en segundos
}

$deadline = $_SESSION['approval_deadline'];
$timeRemaining = $deadline - time();

// Si ya expiró, redirigir o mostrar mensaje
$expired = $timeRemaining <= 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paso 4: Pendiente de Aprobación - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../components/marcaPasos.css">
    <style>
        * { box-sizing: border-box; }
        
        .verification-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px 25px;
        }
        
        .card h3 {
            color: #882A57;
            text-align: center;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .card p {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        /* Reloj de cuenta regresiva */
        .countdown-container {
            background: linear-gradient(135deg, #e91e63, #ff4081);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 8px 25px rgba(233, 30, 99, 0.3);
        }
        
        .countdown-title {
            text-align: center;
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }
        
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .time-unit {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            min-width: 100px;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .time-unit:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .time-value {
            font-size: 42px;
            font-weight: 700;
            color: white;
            display: block;
            line-height: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-family: 'Courier New', monospace;
        }
        
        .time-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 8px;
            display: block;
            font-weight: 500;
        }
        
        /* Animación de pulso para cuando quedan pocas horas */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .countdown-timer.warning .time-unit {
            animation: pulse 2s infinite;
            background: rgba(255, 193, 7, 0.2);
            border-color: rgba(255, 193, 7, 0.5);
        }
        
        .countdown-timer.danger .time-unit {
            animation: pulse 1s infinite;
            background: rgba(244, 67, 54, 0.2);
            border-color: rgba(244, 67, 54, 0.5);
        }
        
        /* Alerta de información */
        .alert-info {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .alert-info h4 {
            color: #004085;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        
        .alert-info ul {
            margin: 0;
            padding-left: 20px;
            color: #004085;
        }
        
        .alert-info li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        /* Estado de aprobación */
        .approval-status {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .status-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .status-text {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .status-subtext {
            font-size: 14px;
            color: #6c757d;
        }
        
        /* Botón de actualizar */
        .btn-refresh {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #882A57, #9d3461);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin: 10px 5px;
        }
        
        .btn-refresh:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 42, 87, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
        }
        
        /* Mensaje de expiración */
        .expired-message {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
        }
        
        .expired-message .icon {
            font-size: 64px;
            margin-bottom: 15px;
        }
        
        .expired-message h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .expired-message p {
            color: #856404;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .countdown-timer {
                gap: 10px;
            }
            
            .time-unit {
                min-width: 80px;
                padding: 15px 10px;
            }
            
            .time-value {
                font-size: 32px;
            }
            
            .time-label {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../components/marcaPasos.php'; ?>
    
    <div class="verification-container">
        <!-- MARCA PASOS -->
        <?php renderMarcaPasos(4, 4); ?>
        
        <div class="card">
            <img src="../../assets/images/logos/logo_valora.png" style="max-width: 150px; margin: 0 auto 20px; display: block;">
            
            <h3>⏳ Paso 4: Pendiente de Aprobación</h3>
            
            <?php if(!$expired): ?>
                <p>
                    <strong>Hola <?php echo htmlspecialchars($nombres . ' ' . $apellidos); ?>,</strong><br>
                    Tu solicitud de verificación de identidad ha sido recibida y está siendo revisada por nuestro equipo de soporte.
                </p>
                
                <!-- Estado de aprobación -->
                <div class="approval-status">
                    <div class="status-icon">🔍</div>
                    <div class="status-text">En Revisión</div>
                    <div class="status-subtext">Nuestro equipo está verificando tu información</div>
                </div>
                
                <!-- Reloj de cuenta regresiva -->
                <div class="countdown-container">
                    <div class="countdown-title">⏰ Tiempo de Respuesta Garantizada</div>
                    <div class="countdown-timer" id="countdown">
                        <div class="time-unit">
                            <span class="time-value" id="hours">--</span>
                            <span class="time-label">Horas</span>
                        </div>
                        <div class="time-unit">
                            <span class="time-value" id="minutes">--</span>
                            <span class="time-label">Minutos</span>
                        </div>
                        <div class="time-unit">
                            <span class="time-value" id="seconds">--</span>
                            <span class="time-label">Segundos</span>
                        </div>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="alert-info">
                    <h4>💡 ¿Qué sucederá ahora?</h4>
                    <ul>
                        <li><strong>Revisión Manual:</strong> Un especialista verificará tus documentos y datos</li>
                        <li><strong>Tiempo de Respuesta:</strong> Recibirás una respuesta dentro de las próximas 24 horas</li>
                        <li><strong>Notificación:</strong> Te contactaremos por <?php echo htmlspecialchars($userData['email'] ?? 'email'); ?></li>
                        <li><strong>Acceso:</strong> Una vez aprobado, podrás crear tu contraseña y acceder a tu cuenta</li>
                    </ul>
                </div>
                
                <!-- Información del usuario -->
                <div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin: 20px 0; font-size: 14px; color: #495057;">
                    <strong>📋 Datos Enviados:</strong><br>
                    <span style="color: #6c757d;">
                        Cédula: <?php echo htmlspecialchars($cedula); ?><br>
                        Nombre: <?php echo htmlspecialchars($nombres . ' ' . $apellidos); ?><br>
                        Email: <?php echo htmlspecialchars($userData['email'] ?? 'N/A'); ?><br>
                        Celular: <?php echo htmlspecialchars($userData['celular'] ?? 'N/A'); ?>
                    </span>
                </div>
                
                <!-- Botones de acción -->
                <div style="text-align: center; margin-top: 30px;">
                    <button type="button" class="btn-refresh" onclick="location.reload()">
                        🔄 Actualizar Estado
                    </button>
                    <a href="../../index.php" class="btn-refresh btn-secondary">
                        🏠 Volver al Inicio
                    </a>
                </div>
                
            <?php else: ?>
                <!-- Mensaje de tiempo expirado -->
                <div class="expired-message">
                    <div class="icon">⏰</div>
                    <h3>Tiempo de Respuesta Expirado</h3>
                    <p>
                        Han transcurrido más de 24 horas desde tu solicitud.<br>
                        Por favor, contacta con nuestro equipo de soporte para conocer el estado de tu solicitud.
                    </p>
                    <div style="margin-top: 20px;">
                        <a href="mailto:soporte@valora.vip" class="btn-refresh">
                            📧 Contactar Soporte
                        </a>
                        <a href="verify1_document.php" class="btn-refresh btn-secondary">
                            🔄 Iniciar Nueva Solicitud
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        <?php if(!$expired): ?>
        // Tiempo de deadline desde PHP (en milisegundos)
        const deadline = <?php echo $deadline * 1000; ?>;
        
        function updateCountdown() {
            const now = new Date().getTime();
            const timeLeft = deadline - now;
            
            if (timeLeft <= 0) {
                // Tiempo expirado, recargar página
                location.reload();
                return;
            }
            
            // Calcular horas, minutos y segundos
            const hours = Math.floor(timeLeft / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
            
            // Actualizar display
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
            
            // Cambiar estilo según tiempo restante
            const countdownTimer = document.getElementById('countdown');
            
            if (hours < 1) {
                // Menos de 1 hora: modo peligro (rojo)
                countdownTimer.classList.remove('warning');
                countdownTimer.classList.add('danger');
            } else if (hours < 6) {
                // Menos de 6 horas: modo advertencia (amarillo)
                countdownTimer.classList.remove('danger');
                countdownTimer.classList.add('warning');
            } else {
                // Más de 6 horas: modo normal
                countdownTimer.classList.remove('warning', 'danger');
            }
        }
        
        // Actualizar cada segundo
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Auto-refrescar la página cada 5 minutos para verificar el estado
        setInterval(() => {
            console.log('Auto-refresh: Verificando estado de aprobación...');
            // Aquí podrías hacer una llamada AJAX para verificar si ya fue aprobado
            // Por ahora solo refrescamos la página
            location.reload();
        }, 5 * 60 * 1000); // 5 minutos
        
        <?php endif; ?>
    </script>
</body>
</html>
