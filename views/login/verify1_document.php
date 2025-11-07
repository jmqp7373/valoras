<?php
/**
 * PASO 1: Subir Documentos
 * 
 * Página para subir ambas caras del documento de identidad
 * 
 * @author Valora.vip
 * @version 2.0.0
 */

// Verificar sesión
require_once '../../config/database.php';
startSessionSafely();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paso 1: Verificación de Identidad - Valora</title>
    <link rel="stylesheet" href="../../components/marcaPasos.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
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
        
        #idPreviewFront,
        #idPreviewBack {
            max-height: 200px;
            max-width: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            margin-top: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .d-none {
            display: none !important;
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
            box-sizing: border-box;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #882A57 0%, #d63384 100%);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #6f2147 0%, #b82872 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(136, 42, 87, 0.4);
        }
        
        .btn-primary:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid transparent;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        .back-link {
            color: #882A57;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            margin-top: 20px;
        }
        
        .back-link:hover {
            color: #6f2147;
            text-decoration: underline;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-4 {
            margin-top: 25px;
        }
        
        .spinner-border {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php include '../../components/marcaPasos.php'; ?>
    
    <div class="verification-container">
        <!-- MARCA PASOS CON PADDING EQUIVALENTE AL CARD -->
        <div style="padding: 0 25px; margin-bottom: 20px;">
            <?php renderMarcaPasos(1, 3); ?>
        </div>
        
        <div class="card">
            <img src="../../assets/images/logos/logo_valora.png" class="logo" alt="Valora Logo" style="max-width: 150px; margin: 0 auto 20px; display: block;">
            
            <h3>📸 Paso 1: Subir Documentos</h3>
            <p>
                Sube una foto clara de ambas caras de tu documento de identidad.
            </p>
            
            <div class="alert alert-info" style="text-align: left;">
                <strong>💡 Consejos:</strong>
                <ul style="margin: 10px 0 0 20px; padding: 0;">
                    <li>Sube ambas caras del documento (frontal y posterior)</li>
                    <li>Asegúrate de que el documento esté completo y legible</li>
                    <li>Usa buena iluminación, evita sombras</li>
                    <li>Mantén el documento plano, sin reflejos</li>
                    <li>Formato: JPEG, PNG o WebP (máx. 6MB cada una)</li>
                </ul>
            </div>
            
            <!-- Cara Frontal -->
            <div style="margin-top: 20px;">
                <label style="font-weight: 600; color: #495057; margin-bottom: 8px; display: block; font-size: 14px;">
                    📄 Cara Frontal del Documento:
                </label>
                <input type="file" 
                       id="idPhotoFront" 
                       accept="image/*" 
                       capture="environment" 
                       class="form-control">
                
                <img id="idPreviewFront" 
                     class="d-none" 
                     alt="Vista previa frontal">
            </div>
            
            <!-- Cara Posterior -->
            <div style="margin-top: 20px;">
                <label style="font-weight: 600; color: #495057; margin-bottom: 8px; display: block; font-size: 14px;">
                    📄 Cara Posterior del Documento:
                </label>
                <input type="file" 
                       id="idPhotoBack" 
                       accept="image/*" 
                       capture="environment" 
                       class="form-control">
                
                <img id="idPreviewBack" 
                     class="d-none" 
                     alt="Vista previa posterior">
            </div>
            
            <button id="analyzeButton" 
                    class="btn btn-primary" 
                    disabled>
                Continuar al Paso 2: Análisis con IA
            </button>
            
            <div class="text-center mt-4">
                <a href="password_reset.php" class="back-link">
                    ← Volver a recuperación de contraseña
                </a>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/id_verification/uploadId_step1.js"></script>
</body>
</html>
