<?php
/**
 * PASO 2: Análisis con IA
 * 
 * Muestra los resultados del análisis OCR con Google Vision API
 * 
 * @author Valora.vip
 * @version 2.0.0
 */

// Verificar sesión
require_once '../../config/database.php';
startSessionSafely();

// Verificar que venimos del Paso 1 y tenemos datos en sesión
if (!isset($_SESSION['ocr_result'])) {
    header('Location: verify1_document.php');
    exit;
}

$ocrData = $_SESSION['ocr_result'];
$valid = $ocrData['valid'] ?? false;
$userMatch = $ocrData['userMatch'] ?? false;
$errors = $ocrData['errors'] ?? [];
$warnings = $ocrData['warnings'] ?? [];
$data = $ocrData['data'] ?? [];
$userData = $ocrData['userData'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paso 2: Resultados del Análisis - Valora</title>
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
            margin-bottom: 20px;
        }
        
        .card h3 {
            color: #882A57;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
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
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
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
        }
        
        details {
            margin-top: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
        }
        
        summary {
            cursor: pointer;
            color: #882A57;
            font-weight: 600;
            padding: 5px;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include '../../components/marcaPasos.php'; ?>
    
    <div class="verification-container">
        <!-- MARCA PASOS -->
        <?php renderMarcaPasos(2, 3); ?>
        
        <div class="card">
            <img src="../../assets/images/logos/logo_valora.png" style="max-width: 150px; margin: 0 auto 20px; display: block;">
            
            <h3>📊 Paso 2: Resultados del Análisis</h3>
            
            <?php if ($valid): ?>
                <div class="alert alert-success">
                    <h5 style="margin: 0 0 10px 0;">✅ Documento validado correctamente</h5>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <h5 style="margin: 0 0 10px 0;">⚠️ Documento con inconsistencias</h5>
                </div>
            <?php endif; ?>
            
            <?php if ($userMatch): ?>
                <div class="alert alert-success">
                    <strong>✅ Usuario Verificado:</strong> Los datos del documento coinciden con un usuario registrado.
                </div>
            <?php elseif ($userMatch === false): ?>
                <div class="alert alert-danger">
                    <strong>❌ Usuario No Encontrado:</strong> No se encontró ningún usuario con este número de documento.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Errores:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($warnings)): ?>
                <div class="alert alert-warning">
                    <strong>Advertencias:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ($warnings as $warning): ?>
                            <li><?= htmlspecialchars($warning) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Información Detectada -->
            <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <h5 style="color: #882A57; margin-bottom: 15px;">📄 Información Detectada</h5>
                
                <?php if (!empty($data['documentType'])): ?>
                    <p><strong>Tipo de documento:</strong> <?= htmlspecialchars($data['documentType']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($data['cedula'])): ?>
                    <p><strong>Número de documento:</strong> <?= htmlspecialchars($data['cedula']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($data['nombres'])): ?>
                    <p><strong>Nombres:</strong> <?= htmlspecialchars($data['nombres']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($data['apellidos'])): ?>
                    <p><strong>Apellidos:</strong> <?= htmlspecialchars($data['apellidos']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($data['fechaNacimiento'])): ?>
                    <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($data['fechaNacimiento']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($data['fechaExpedicion'])): ?>
                    <p><strong>Fecha de expedición:</strong> <?= htmlspecialchars($data['fechaExpedicion']) ?></p>
                <?php endif; ?>
                
                <p><strong>Rostros detectados:</strong> <?= $data['faceCount'] ?? 0 ?></p>
                
                <?php if (!empty($data['fullText'])): ?>
                    <details>
                        <summary>Ver texto completo extraído (ambas caras)</summary>
                        <pre><?= htmlspecialchars($data['fullText']) ?></pre>
                    </details>
                <?php endif; ?>
            </div>
            
            <!-- Usuario Registrado -->
            <?php if ($userData): ?>
                <div style="margin-top: 20px; padding: 20px; background: #d4edda; border: 2px solid #28a745; border-radius: 10px;">
                    <h5 style="color: #28a745; margin-bottom: 15px;">👤 Usuario Registrado</h5>
                    <p><strong>Nombre completo:</strong> <?= htmlspecialchars($userData['nombres']) ?> <?= htmlspecialchars($userData['apellidos']) ?></p>
                    <p><strong>Cédula:</strong> <?= htmlspecialchars($userData['cedula']) ?></p>
                    <?php if (!empty($userData['celular'])): ?>
                        <p><strong>Celular:</strong> <?= htmlspecialchars($userData['celular']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($userData['email'])): ?>
                        <p><strong>Email:</strong> <?= htmlspecialchars($userData['email']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Botones de Acción -->
            <?php if ($valid && $userMatch): ?>
                <a href="verify3_Update.php" class="btn btn-primary">
                    Continuar al Paso 3: Actualizar Datos →
                </a>
                <a href="verify1_document.php" class="btn btn-secondary" style="margin-top: 10px;">
                    ← Volver al Paso 1
                </a>
            <?php else: ?>
                <a href="verify1_document.php" class="btn btn-secondary">
                    ← Volver al Paso 1 e Intentar de Nuevo
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
