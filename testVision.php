<?php
/**
 * Test de Google Cloud Vision API
 * 
 * Este script valida el funcionamiento de la Cloud Vision API
 * usando la API Key configurada en config.php.
 * 
 * Funcionalidades probadas:
 * - OCR (Detección de texto en documentos)
 * - Detección de rostros
 * 
 * INSTRUCCIONES:
 * 1. Coloca una imagen llamada 'documento.jpg' en el mismo directorio
 * 2. Asegúrate de que config/config.php contenga GOOGLE_VISION_API_KEY
 * 3. Ejecuta: https://localhost/valora.vip/testVision.php (local)
 *           o https://valora.vip/testVision.php (producción)
 * 
 * ⚠️ ELIMINAR DESPUÉS DE VALIDAR LA INTEGRACIÓN
 */

// Habilitar reporte de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Importar configuración para obtener la API Key
require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Google Vision API - Valora</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
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
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
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
        .section {
            margin: 25px 0;
        }
        .section h3 {
            color: #495057;
            margin-bottom: 10px;
        }
        .metric {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test de Google Cloud Vision API</h1>
        
        <?php
        // 1. Verificar que existe la API Key
        if (!defined('GOOGLE_VISION_API_KEY')) {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong> La constante GOOGLE_VISION_API_KEY no está definida en config/config.php';
            echo '</div>';
            exit;
        }
        
        echo '<div class="success">';
        echo '<strong>✅ API Key encontrada:</strong> ' . substr(GOOGLE_VISION_API_KEY, 0, 10) . '...';
        echo '</div>';
        
        // 2. Verificar que existe la imagen de prueba
        $imagePath = __DIR__ . '/documento.jpg';
        
        if (!file_exists($imagePath)) {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong> No se encontró el archivo documento.jpg en el directorio raíz.<br>';
            echo '<strong>Ubicación esperada:</strong> ' . htmlspecialchars($imagePath) . '<br><br>';
            echo '<strong>Instrucciones:</strong><br>';
            echo '1. Coloca una imagen de un documento (cédula, pasaporte, etc.) en el directorio raíz<br>';
            echo '2. Renómbrala como "documento.jpg"<br>';
            echo '3. Recarga esta página';
            echo '</div>';
            exit;
        }
        
        echo '<div class="success">';
        echo '<strong>✅ Imagen encontrada:</strong> documento.jpg (' . number_format(filesize($imagePath) / 1024, 2) . ' KB)';
        echo '</div>';
        
        // 3. Convertir imagen a base64
        echo '<div class="info">';
        echo '<strong>📤 Preparando imagen...</strong> Codificando a base64';
        echo '</div>';
        
        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);
        
        // 4. Preparar solicitud a Google Vision API
        $apiUrl = 'https://vision.googleapis.com/v1/images:annotate?key=' . GOOGLE_VISION_API_KEY;
        
        // Estructura de la solicitud JSON
        $requestData = [
            'requests' => [
                [
                    'image' => [
                        'content' => $base64Image
                    ],
                    'features' => [
                        [
                            'type' => 'DOCUMENT_TEXT_DETECTION',
                            'maxResults' => 1
                        ],
                        [
                            'type' => 'FACE_DETECTION',
                            'maxResults' => 10
                        ]
                    ]
                ]
            ]
        ];
        
        $jsonRequest = json_encode($requestData);
        
        // 5. Enviar solicitud usando cURL
        echo '<div class="info">';
        echo '<strong>🌐 Enviando solicitud a Google Vision API...</strong>';
        echo '</div>';
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonRequest)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // 6. Verificar respuesta
        if ($curlError) {
            echo '<div class="error">';
            echo '<strong>❌ Error de conexión:</strong> ' . htmlspecialchars($curlError);
            echo '</div>';
            exit;
        }
        
        if ($httpCode !== 200) {
            echo '<div class="error">';
            echo '<strong>❌ Error HTTP ' . $httpCode . ':</strong><br>';
            echo '<strong>Respuesta de la API:</strong>';
            echo '<pre>' . htmlspecialchars($response) . '</pre>';
            echo '</div>';
            
            if ($httpCode === 403) {
                echo '<div class="warning">';
                echo '<strong>⚠️ Posible causa:</strong> API Key inválida o Google Vision API no habilitada.<br>';
                echo 'Verifica en: <a href="https://console.cloud.google.com/apis/library/vision.googleapis.com" target="_blank">Google Cloud Console</a>';
                echo '</div>';
            }
            exit;
        }
        
        // 7. Procesar respuesta exitosa
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '<div class="error">';
            echo '<strong>❌ Error al decodificar JSON:</strong> ' . json_last_error_msg();
            echo '</div>';
            exit;
        }
        
        echo '<div class="success">';
        echo '<strong>✅ Conexión exitosa con Google Vision API</strong><br>';
        echo 'Código HTTP: ' . $httpCode;
        echo '</div>';
        
        // 8. Mostrar resultados: Texto detectado (OCR)
        echo '<div class="section">';
        echo '<h3>📄 Detección de Texto (OCR)</h3>';
        
        if (isset($responseData['responses'][0]['fullTextAnnotation']['text'])) {
            $detectedText = $responseData['responses'][0]['fullTextAnnotation']['text'];
            echo '<div class="success">';
            echo '<strong>✅ Texto detectado:</strong>';
            echo '</div>';
            echo '<pre>' . htmlspecialchars($detectedText) . '</pre>';
        } else {
            echo '<div class="warning">';
            echo '<strong>⚠️ No se detectó texto en la imagen</strong>';
            echo '</div>';
        }
        echo '</div>';
        
        // 9. Mostrar resultados: Rostros detectados
        echo '<div class="section">';
        echo '<h3>😊 Detección de Rostros</h3>';
        
        if (isset($responseData['responses'][0]['faceAnnotations'])) {
            $faces = $responseData['responses'][0]['faceAnnotations'];
            $faceCount = count($faces);
            
            echo '<div class="success">';
            echo '<strong>✅ Rostros detectados:</strong> ' . $faceCount;
            echo '</div>';
            
            if ($faceCount > 0) {
                echo '<div class="metric">';
                foreach ($faces as $index => $face) {
                    $confidence = isset($face['detectionConfidence']) 
                        ? round($face['detectionConfidence'] * 100, 2) 
                        : 'N/A';
                    echo '<strong>Rostro ' . ($index + 1) . ':</strong> Confianza ' . $confidence . '%<br>';
                }
                echo '</div>';
            }
        } else {
            echo '<div class="warning">';
            echo '<strong>⚠️ No se detectaron rostros en la imagen</strong>';
            echo '</div>';
        }
        echo '</div>';
        
        // 10. Información adicional
        echo '<div class="section">';
        echo '<h3>📊 Información Adicional</h3>';
        echo '<div class="info">';
        echo '<strong>💡 Nota:</strong> Revisa el consumo de la API en:<br>';
        echo '<a href="https://console.cloud.google.com/apis/api/vision.googleapis.com/metrics" target="_blank">';
        echo 'Google Cloud Console → Vision API → Metrics';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        
        // 11. Respuesta completa (para debugging)
        echo '<div class="section">';
        echo '<h3>🔧 Respuesta Completa (Debug)</h3>';
        echo '<details>';
        echo '<summary style="cursor: pointer; color: #882A57; font-weight: bold;">Ver JSON completo</summary>';
        echo '<pre>' . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
        echo '</details>';
        echo '</div>';
        ?>
        
        <div class="error" style="margin-top: 30px;">
            <strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (testVision.php) después de validar la integración.
        </div>
    </div>
</body>
</html>
