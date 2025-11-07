<?php
/**
 * PASO 3: Actualizar Datos de Contacto
 * 
 * Formulario para actualizar teléfono y email del usuario verificado
 * 
 * @author Valora.vip
 * @version 2.0.0
 */

// Verificar sesión
require_once '../../config/database.php';
startSessionSafely();

// Verificar que venimos del Paso 2 y el usuario fue encontrado
if (!isset($_SESSION['ocr_result']) || !isset($_SESSION['ocr_result']['userData'])) {
    header('Location: verify1_document.php');
    exit;
}

$userData = $_SESSION['ocr_result']['userData'];
$cedula = $userData['cedula'] ?? '';

// Debug: Log para verificar datos
error_log("verify3_Update - Cédula: {$cedula}, Teléfono actual: " . ($userData['celular'] ?? 'N/A') . ", Email actual: " . ($userData['email'] ?? 'N/A'));

// Mensajes de error/éxito
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paso 3: Actualizar Datos - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        * { box-sizing: border-box; }
        
        body {
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
            overflow-x: hidden;
        }
        
        .verification-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
        }
        
        /* STEPS INDICATOR */
        .steps-container {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-bottom: 25px !important;
            gap: 8px !important;
            background: white !important;
            padding: 20px !important;
            border-radius: 15px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
        }
        
        .step {
            width: 45px !important;
            height: 45px !important;
            border-radius: 50% !important;
            background: #e0e0e0 !important;
            color: #757575 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 700 !important;
            font-size: 20px !important;
            line-height: 1 !important;
        }
        
        .step-line {
            width: 70px !important;
            height: 4px !important;
            background: #e0e0e0 !important;
        }
        
        .step.active {
            background: linear-gradient(135deg, #e91e63, #ff4081) !important;
            color: white !important;
            box-shadow: 0 6px 15px rgba(233, 30, 99, 0.5) !important;
            transform: scale(1.15) !important;
        }
        
        .step.completed {
            background: linear-gradient(135deg, #4caf50, #66bb6a) !important;
            color: white !important;
        }
        
        .step-line.active {
            background: linear-gradient(90deg, #e91e63, #ff4081) !important;
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
            color: #6c757d;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ee6f92;
            box-shadow: 0 0 0 3px rgba(238, 111, 146, 0.1);
        }
        
        small {
            display: block;
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            width: 100%;
            max-width: 100%;
            margin: 20px auto 0;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #882A57 0%, #d63384 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #6f2147 0%, #b82872 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(136, 42, 87, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <!-- STEP INDICATOR -->
        <div class="steps-container">
            <div class="step completed">1</div>
            <div class="step-line active"></div>
            <div class="step completed">2</div>
            <div class="step-line active"></div>
            <div class="step active">3</div>
        </div>
        
        <div class="card">
            <img src="../../assets/images/logos/logo_valora.png" style="max-width: 150px; margin: 0 auto 20px; display: block;">
            
            <h3>✏️ Paso 3: Actualiza tus Datos</h3>
            <p>
                Para continuar con la recuperación de tu contraseña, actualiza tus datos de contacto.
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>❌ Error:</strong> 
                    <?php 
                    switch($error) {
                        case 'invalid_phone':
                            echo 'Número de teléfono inválido. Debe tener 10 dígitos.';
                            break;
                        case 'invalid_email':
                            echo 'Correo electrónico inválido.';
                            break;
                        case 'update_failed':
                            echo 'No se pudo actualizar los datos. Intenta de nuevo.';
                            break;
                        default:
                            echo 'Ocurrió un error. Por favor intenta de nuevo.';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <strong>Usuario verificado:</strong> <?= htmlspecialchars($userData['nombres']) ?> <?= htmlspecialchars($userData['apellidos']) ?><br>
                <strong>Cédula:</strong> <?= htmlspecialchars($cedula) ?>
            </div>
            
            <form action="../../controllers/UserUpdateController.php" method="POST">
                <input type="hidden" name="cedula" value="<?= htmlspecialchars($cedula) ?>">
                
                <div class="form-group">
                    <label for="telefono">📱 Número de Celular:</label>
                    <input type="tel" 
                           id="telefono" 
                           name="telefono" 
                           class="form-control" 
                           placeholder="Ejemplo: 3103951529" 
                           pattern="[0-9]{10}" 
                           title="Ingresa un número de 10 dígitos"
                           value="<?= htmlspecialchars($userData['celular'] ?? '') ?>"
                           required>
                    <small>Ingresa 10 dígitos sin espacios ni guiones</small>
                </div>
                
                <div class="form-group">
                    <label for="email">📧 Correo Electrónico:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="Ejemplo: usuario@email.com"
                           value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                           required>
                    <small>Ingresa un correo electrónico válido</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    💾 Guardar Cambios y Continuar
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="verify2_OCR.php" class="btn btn-secondary">
                    ← Volver al Paso 2
                </a>
            </div>
        </div>
    </div>
</body>
</html>
