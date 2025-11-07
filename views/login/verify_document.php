<?php
/**
 * Verificación de Identidad mediante Documento
 * 
 * Vista para verificar la identidad del usuario mediante
 * análisis de documento con IA (Google Cloud Vision API)
 * 
 * @author Valora.vip
 * @version 1.0.0
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
    <title>Verificación de Identidad - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            padding-top: 40px;
        }
        
        .verification-container {
            max-width: 600px;
            margin: 0 auto 20px;
            padding: 15px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 25px 20px;
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
        
        /* Estilos para las previsualizaciones de imágenes */
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
        
        /* Contenedor para dispositivos móviles */
        @media (max-width: 768px) {
            body {
                padding-top: 30px;
            }
            
            .verification-container {
                margin: 0 auto 10px;
                padding: 10px;
            }
            
            .card {
                padding: 20px 15px;
            }
            
            #idPreviewFront,
            #idPreviewBack {
                max-height: 120px;
            }
            
            .card h3 {
                font-size: 20px;
                margin-bottom: 10px;
            }
            
            .card p {
                font-size: 14px;
                margin-bottom: 15px;
            }
            
            .alert-info ul {
                font-size: 13px;
                padding-left: 15px !important;
            }
            
            .alert-info ul li {
                margin-bottom: 5px;
            }
        }
        
        /* Estilos para pantallas muy pequeñas */
        @media (max-width: 480px) {
            body {
                padding-top: 25px;
            }
            
            .verification-container {
                margin: 0 5px 5px;
                padding: 5px;
            }
            
            .card {
                padding: 15px 10px;
                border-radius: 10px;
            }
            
            #idPreviewFront,
            #idPreviewBack {
                max-height: 100px;
            }
            
            .btn {
                padding: 10px 16px;
                font-size: 14px;
            }
        }
        
        .d-none {
            display: none !important;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            width: 100%;
            margin-top: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #882A57 0%, #d63384 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #6f2147 0%, #b82872 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 42, 87, 0.3);
        }
        
        .btn-primary:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-outline-secondary {
            background: transparent;
            border: 2px solid #6c757d;
            color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
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
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        .mt-4 {
            margin-top: 25px;
        }
        
        .mt-5 {
            margin-top: 40px;
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
        
        .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            vertical-align: text-bottom;
            border: 0.15em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }
        
        .spinner-border-sm {
            width: 0.875rem;
            height: 0.875rem;
            border-width: 0.15em;
        }
        
        @keyframes spinner-border {
            to {
                transform: rotate(360deg);
            }
        }
        
        .me-2 {
            margin-right: 0.5rem;
        }
        
        .mb-0 {
            margin-bottom: 0;
        }
        
        .mb-2 {
            margin-bottom: 0.5rem;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-title {
            color: #495057;
            font-size: 20px;
            font-weight: 600;
        }
        
        details summary {
            cursor: pointer;
            color: #882A57;
            font-weight: bold;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        details summary:hover {
            background: #e9ecef;
        }
        
        pre {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="card">
            <img src="../../assets/images/logos/logo_valora.png" class="logo" alt="Valora Logo" style="max-width: 150px; margin: 0 auto 20px; display: block;">
            
            <h3>📸 Verificación de Identidad</h3>
            <p>
                Sube una foto clara de tu documento de identidad o tómala con tu cámara. 
                Nuestro sistema la analizará automáticamente para validar tu identidad.
            </p>
            
            <div class="alert alert-info" style="text-align: left;">
                <strong>💡 Consejos para una buena foto:</strong>
                <ul style="margin: 10px 0 0 20px; padding: 0;">
                    <li>Sube ambas caras del documento (frontal y posterior)</li>
                    <li>Asegúrate de que el documento esté completo y legible</li>
                    <li>Usa buena iluminación, evita sombras</li>
                    <li>Mantén el documento plano, sin reflejos</li>
                    <li>Formato: JPEG, PNG o WebP (máx. 6MB cada una)</li>
                </ul>
            </div>
            
            <!-- Sección Cara Frontal -->
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
                     alt="Vista previa frontal del documento">
            </div>
            
            <!-- Sección Cara Posterior -->
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
                     alt="Vista previa posterior del documento">
            </div>
            
            <button id="analyzeIdButton" 
                    class="btn btn-primary" 
                    disabled>
                Analizar ambas imágenes con IA
            </button>
            
            <div id="idScanResult" class="mt-4"></div>
            
            <div class="text-center mt-4">
                <a href="password_reset.php" class="back-link">
                    ← Volver a recuperación de contraseña
                </a>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/id_verification/uploadId.js"></script>
</body>
</html>
