<?php
/**
 * ID Verification Controller
 * 
 * Controlador para manejar la verificación de documentos de identidad
 * usando Google Cloud Vision API
 * 
 * @author Valora.vip
 * @version 2.0.0 - Multi-page flow with SESSION
 */

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}

// Iniciar sesión
require_once __DIR__ . '/../../config/database.php';
startSessionSafely();

// Importar configuración y servicios
$configPath = __DIR__ . '/../../config/configGoogleVision.php';
if (!file_exists($configPath)) {
    $_SESSION['ocr_result'] = [
        'success' => false,
        'error' => 'Configuración no encontrada. Contacte al administrador.'
    ];
    header('Location: ../../views/login/verify2_OCR.php');
    exit;
}

require_once $configPath;

// Verificar que la API Key esté definida
if (!defined('GOOGLE_VISION_API_KEY')) {
    $_SESSION['ocr_result'] = [
        'success' => false,
        'error' => 'GOOGLE_VISION_API_KEY no está definida'
    ];
    header('Location: ../../views/login/verify2_OCR.php');
    exit;
}

require_once __DIR__ . '/../../services/id_verification/googleVisionService.php';

try {
    // Validar que se recibieron ambas imágenes
    if (!isset($_FILES['idPhotoFront']) || $_FILES['idPhotoFront']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió la imagen frontal o hubo un error en la carga.');
    }
    
    if (!isset($_FILES['idPhotoBack']) || $_FILES['idPhotoBack']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió la imagen posterior o hubo un error en la carga.');
    }
    
    $fileFront = $_FILES['idPhotoFront'];
    $fileBack = $_FILES['idPhotoBack'];
    
    // Validar tamaño de los archivos (máximo 6MB cada uno)
    $maxSize = 6 * 1024 * 1024; // 6MB en bytes
    
    if ($fileFront['size'] > $maxSize) {
        throw new Exception('La imagen frontal es demasiado grande. Tamaño máximo: 6MB.');
    }
    
    if ($fileBack['size'] > $maxSize) {
        throw new Exception('La imagen posterior es demasiado grande. Tamaño máximo: 6MB.');
    }
    
    // Validar tipos MIME
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeTypeFront = finfo_file($finfo, $fileFront['tmp_name']);
    $mimeTypeBack = finfo_file($finfo, $fileBack['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeTypeFront, $allowedTypes)) {
        throw new Exception('Formato de imagen frontal no permitido. Use JPEG, PNG o WebP.');
    }
    
    if (!in_array($mimeTypeBack, $allowedTypes)) {
        throw new Exception('Formato de imagen posterior no permitido. Use JPEG, PNG o WebP.');
    }
    
    // Guardar temporalmente las imágenes para procesamiento
    $tempPathFront = sys_get_temp_dir() . '/' . uniqid('id_front_') . '.jpg';
    $tempPathBack = sys_get_temp_dir() . '/' . uniqid('id_back_') . '.jpg';
    
    if (!move_uploaded_file($fileFront['tmp_name'], $tempPathFront)) {
        throw new Exception('Error al guardar imagen frontal temporalmente.');
    }
    
    if (!move_uploaded_file($fileBack['tmp_name'], $tempPathBack)) {
        @unlink($tempPathFront);
        throw new Exception('Error al guardar imagen posterior temporalmente.');
    }
    
    // Inicializar servicio de Google Vision
    $visionService = new GoogleVisionService();
    
    // Analizar ambas imágenes
    $analysisResult = $visionService->analyzeMultipleImages([$tempPathFront, $tempPathBack]);
    
    // Limpiar archivos temporales inmediatamente
    @unlink($tempPathFront);
    @unlink($tempPathBack);
    
    if (!$analysisResult['success']) {
        throw new Exception($analysisResult['error']);
    }
    
    // Validar documento
    $validation = $visionService->validateDocument($analysisResult);
    
    // Conectar a base de datos para cotejo
    $database = new Database();
    $db = $database->getConnection();
    
    $userMatch = false;
    $userData = null;
    
    // Si se extrajo un número de cédula, buscar en la base de datos
    if (!empty($analysisResult['documentInfo']['cedula'])) {
        $cedula = $analysisResult['documentInfo']['cedula'];
        
        // Buscar usuario por cédula
        $stmt = $db->prepare("SELECT cedula, nombres, apellidos, celular, email FROM usuarios WHERE cedula = :cedula LIMIT 1");
        $stmt->execute(['cedula' => $cedula]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $userMatch = true;
            $userData = [
                'cedula' => $usuario['cedula'],
                'nombres' => $usuario['nombres'],
                'apellidos' => $usuario['apellidos'],
                'celular' => $usuario['celular'] ?? '',
                'email' => $usuario['email'] ?? ''
            ];
        }
    }
    
    // Preparar respuesta para SESSION
    $response = [
        'success' => true,
        'valid' => $validation['valid'],
        'userMatch' => $userMatch,
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
    
    // Agregar datos del usuario si se encontró
    if ($userData) {
        $response['userData'] = $userData;
    }
    
    // Guardar en SESSION
    $_SESSION['ocr_result'] = $response;
    
    // Redirigir a Paso 2
    header('Location: ../../views/login/verify2_OCR.php');
    exit;
    
} catch (Exception $e) {
    // Limpiar archivos temporales en caso de error
    if (isset($tempPathFront) && file_exists($tempPathFront)) {
        @unlink($tempPathFront);
    }
    if (isset($tempPathBack) && file_exists($tempPathBack)) {
        @unlink($tempPathBack);
    }
    
    // Guardar error en SESSION
    $_SESSION['ocr_result'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    // Redirigir a Paso 2 con error
    header('Location: ../../views/login/verify2_OCR.php');
    exit;
}
?>
