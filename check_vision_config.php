<?php
/**
 * Diagnóstico de Configuración para Google Vision API
 * 
 * UBICACIÓN: https://valora.vip/check_vision_config.php
 * 
 * Este script verifica que todo esté configurado correctamente
 * para usar Google Cloud Vision API en verify1_document.php (flujo multi-página)
 * 
 * ⚠️ ELIMINAR DESPUÉS DE VALIDAR LA CONFIGURACIÓN
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Google Vision - Valora</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #882A57;
            border-bottom: 3px solid #ee6f92;
            padding-bottom: 10px;
        }
        .check {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 5px solid;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #882A57;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #6f2147;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Google Vision API</h1>
        <p><strong>Servidor:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
        <p><strong>Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <hr>
        
        <?php
        $allChecksPass = true;
        
        // CHECK 1: Verificar que existe config/configGoogleVision.php
        echo '<h3>1️⃣ Verificar archivo config/configGoogleVision.php</h3>';
        $configPath = __DIR__ . '/config/configGoogleVision.php';
        
        if (file_exists($configPath)) {
            echo '<div class="check success">';
            echo '<strong>✅ CORRECTO:</strong> El archivo <code>config/configGoogleVision.php</code> existe';
            echo '<br><strong>Ubicación:</strong> ' . $configPath;
            echo '</div>';
        } else {
            echo '<div class="check error">';
            echo '<strong>❌ ERROR:</strong> No se encontró <code>config/configGoogleVision.php</code>';
            echo '<br><strong>Ubicación esperada:</strong> ' . $configPath;
            echo '<br><strong>Solución:</strong> Sube el archivo por FTP o créalo en FileManager';
            echo '</div>';
            $allChecksPass = false;
        }
        
        // CHECK 2: Cargar configGoogleVision.php y verificar constante
        echo '<h3>2️⃣ Verificar constante GOOGLE_VISION_API_KEY</h3>';
        
        if (file_exists($configPath)) {
            require_once $configPath;
            
            if (defined('GOOGLE_VISION_API_KEY')) {
                $apiKey = GOOGLE_VISION_API_KEY;
                $keyLength = strlen($apiKey);
                $keyPreview = substr($apiKey, 0, 10) . '...';
                
                echo '<div class="check success">';
                echo '<strong>✅ CORRECTO:</strong> GOOGLE_VISION_API_KEY está definida';
                echo '<br><strong>Preview:</strong> <code>' . htmlspecialchars($keyPreview) . '</code>';
                echo '<br><strong>Longitud:</strong> ' . $keyLength . ' caracteres';
                echo '</div>';
            } else {
                echo '<div class="check error">';
                echo '<strong>❌ ERROR:</strong> GOOGLE_VISION_API_KEY no está definida en configGoogleVision.php';
                echo '<br><strong>Solución:</strong> Agrega esta línea en config/configGoogleVision.php:';
                echo '<pre>define(\'GOOGLE_VISION_API_KEY\', \'tu-api-key-aqui\');</pre>';
                echo '</div>';
                $allChecksPass = false;
            }
        }
        
        // CHECK 3: Verificar servicio googleVisionService.php
        echo '<h3>3️⃣ Verificar servicio Google Vision</h3>';
        $servicePath = __DIR__ . '/services/id_verification/googleVisionService.php';
        
        if (file_exists($servicePath)) {
            echo '<div class="check success">';
            echo '<strong>✅ CORRECTO:</strong> El servicio googleVisionService.php existe';
            echo '</div>';
        } else {
            echo '<div class="check error">';
            echo '<strong>❌ ERROR:</strong> No se encontró googleVisionService.php';
            echo '<br><strong>Ubicación esperada:</strong> ' . $servicePath;
            echo '</div>';
            $allChecksPass = false;
        }
        
        // CHECK 4: Verificar controlador
        echo '<h3>4️⃣ Verificar controlador</h3>';
        $controllerPath = __DIR__ . '/controllers/id_verification/idVerificationController.php';
        
        if (file_exists($controllerPath)) {
            echo '<div class="check success">';
            echo '<strong>✅ CORRECTO:</strong> El controlador idVerificationController.php existe';
            echo '</div>';
        } else {
            echo '<div class="check error">';
            echo '<strong>❌ ERROR:</strong> No se encontró idVerificationController.php';
            echo '<br><strong>Ubicación esperada:</strong> ' . $controllerPath;
            echo '</div>';
            $allChecksPass = false;
        }
        
        // CHECK 5: Verificar vista verify1_document.php (Paso 1 - Nuevo flujo multi-página)
        echo '<h3>5️⃣ Verificar vista</h3>';
        $viewPath = __DIR__ . '/views/login/verify1_document.php';
        
        if (file_exists($viewPath)) {
            echo '<div class="check success">';
            echo '<strong>✅ CORRECTO:</strong> La vista verify1_document.php existe (Paso 1)';
            echo '</div>';
        } else {
            echo '<div class="check error">';
            echo '<strong>❌ ERROR:</strong> No se encontró verify1_document.php';
            echo '<br><strong>Ubicación esperada:</strong> ' . $viewPath;
            echo '</div>';
            $allChecksPass = false;
        }
        
        // CHECK 6: Verificar extensión cURL
        echo '<h3>6️⃣ Verificar extensión cURL de PHP</h3>';
        
        if (function_exists('curl_version')) {
            $curlVersion = curl_version();
            echo '<div class="check success">';
            echo '<strong>✅ CORRECTO:</strong> cURL está habilitado';
            echo '<br><strong>Versión:</strong> ' . $curlVersion['version'];
            echo '</div>';
        } else {
            echo '<div class="check error">';
            echo '<strong>❌ ERROR:</strong> cURL no está habilitado en PHP';
            echo '<br><strong>Solución:</strong> Contacta a soporte de Hostinger para habilitar cURL';
            echo '</div>';
            $allChecksPass = false;
        }
        
        // RESUMEN FINAL
        echo '<hr>';
        echo '<h2>📊 Resumen</h2>';
        
        if ($allChecksPass) {
            echo '<div class="check success">';
            echo '<strong>✅ TODO CORRECTO:</strong> El sistema está configurado correctamente para usar Google Vision API';
            echo '<br><br><strong>Siguiente paso:</strong> Prueba la verificación de documentos en:';
            echo '<br><a href="/views/login/verify1_document.php" class="btn">Ir a Verificación de Documentos (Paso 1)</a>';
            echo '</div>';
        } else {
            echo '<div class="check error">';
            echo '<strong>❌ CONFIGURACIÓN INCOMPLETA:</strong> Corrige los errores indicados arriba';
            echo '<br><br><strong>Archivo principal a verificar:</strong> config/configGoogleVision.php';
            echo '</div>';
        }
        
        // Instrucciones adicionales
        echo '<hr>';
        echo '<div class="check info">';
        echo '<strong>📖 Documentación completa:</strong>';
        echo '<br>Lee el archivo <code>GOOGLE_VISION_SETUP.md</code> en el repositorio para instrucciones detalladas';
        echo '<br><br><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (check_vision_config.php) después de validar la configuración';
        echo '</div>';
        ?>
    </div>
</body>
</html>
