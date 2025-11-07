<?php
/**
 * ID Verification Controller
 * 
 * Controlador para manejar la verificación de documentos de identidad
 * usando Google Cloud Vision API
 * 
 * @author Valora.vip
 * @version 1.0.0
 */

// Habilitar CORS si es necesario
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

// Importar configuración y servicios
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../services/id_verification/googleVisionService.php';

try {
    // Validar que se recibió una imagen
    if (!isset($_FILES['idPhoto']) || $_FILES['idPhoto']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió ninguna imagen o hubo un error en la carga.');
    }
    
    $file = $_FILES['idPhoto'];
    
    // Validar tamaño del archivo (máximo 6MB)
    $maxSize = 6 * 1024 * 1024; // 6MB en bytes
    if ($file['size'] > $maxSize) {
        throw new Exception('La imagen es demasiado grande. Tamaño máximo: 6MB.');
    }
    
    // Validar tipo MIME
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Formato de imagen no permitido. Use JPEG, PNG o WebP.');
    }
    
    // Leer y codificar imagen en base64
    $imageData = file_get_contents($file['tmp_name']);
    $base64Image = base64_encode($imageData);
    
    // Inicializar servicio de Google Vision
    $visionService = new GoogleVisionService();
    
    // Analizar documento
    $analysisResult = $visionService->analyzeDocument($base64Image);
    
    if (!$analysisResult['success']) {
        throw new Exception($analysisResult['error']);
    }
    
    // Validar documento
    $validation = $visionService->validateDocument($analysisResult);
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'valid' => $validation['valid'],
        'message' => $validation['message'],
        'data' => [
            'documentType' => $analysisResult['documentInfo']['tipoDocumento'],
            'cedula' => $analysisResult['documentInfo']['cedula'],
            'nombres' => $analysisResult['documentInfo']['nombres'],
            'apellidos' => $analysisResult['documentInfo']['apellidos'],
            'fechaNacimiento' => $analysisResult['documentInfo']['fechaNacimiento'],
            'fechaExpedicion' => $analysisResult['documentInfo']['fechaExpedicion'],
            'faceCount' => $analysisResult['faceCount'],
            'fullText' => $analysisResult['text']
        ],
        'errors' => $validation['errors'],
        'warnings' => $validation['warnings']
    ];
    
    // Limpiar archivo temporal
    @unlink($file['tmp_name']);
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
