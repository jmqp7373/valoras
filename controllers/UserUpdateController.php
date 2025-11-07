<?php
/**
 * UserUpdateController
 * 
 * Controlador para actualizar datos de contacto del usuario
 * (teléfono y email) después de verificación de identidad
 * 
 * @author Valora.vip
 * @version 1.0.0
 */

// Habilitar reporte de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=UTF-8');

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Usuario.php';
} catch (Exception $e) {
    die('Error al cargar archivos necesarios: ' . $e->getMessage());
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login/verify3_Update.php?error=invalid_method');
    exit;
}

// Validar que existan los datos necesarios
if (!isset($_POST['cedula']) || !isset($_POST['telefono']) || !isset($_POST['email'])) {
    header('Location: ../views/login/verify3_Update.php?error=missing_fields');
    exit;
}

// Obtener y sanitizar datos del formulario
$cedula = trim($_POST['cedula']);
$telefono = trim($_POST['telefono']);
$email = trim($_POST['email']);

// Debug
error_log("UserUpdateController - Recibido: Cédula={$cedula}, Teléfono={$telefono}, Email={$email}");

// Validaciones básicas
if (empty($cedula) || empty($telefono) || empty($email)) {
    error_log("UserUpdateController - Campos vacíos detectados");
    header('Location: ../views/login/verify3_Update.php?error=empty_fields');
    exit;
}

// Validar formato de teléfono (10 dígitos)
if (!preg_match('/^[0-9]{10}$/', $telefono)) {
    header('Location: ../views/login/verify3_Update.php?error=invalid_phone');
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../views/login/verify3_Update.php?error=invalid_email');
    exit;
}

try {
    // Iniciar sesión de manera segura
    startSessionSafely();
    
    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    // Crear instancia del modelo Usuario
    $usuarioModel = new Usuario($db);
    
    // Actualizar datos de contacto
    $resultado = $usuarioModel->updateContactData($cedula, $telefono, $email);
    
    if ($resultado) {
        // Guardar datos en sesión para el siguiente paso
        $_SESSION['updated_phone'] = $telefono;
        $_SESSION['updated_email'] = $email;
        $_SESSION['verified_cedula'] = $cedula;
        $_SESSION['contact_updated'] = true; // Marcar que los datos fueron actualizados
        
        // NO limpiar resultado OCR todavía, lo necesitamos en verify4
        // unset($_SESSION['ocr_result']);
        
        // Redirigir a la página de aprobación (paso 4)
        header('Location: ../views/login/verify4_Approval.php');
        exit;
    } else {
        header('Location: ../views/login/verify3_Update.php?error=update_failed');
        exit;
    }
    
} catch (PDOException $e) {
    error_log('Error en UserUpdateController: ' . $e->getMessage());
    header('Location: ../views/login/verify3_Update.php?error=database_error');
    exit;
} catch (Exception $e) {
    error_log('Error general en UserUpdateController: ' . $e->getMessage());
    header('Location: ../views/login/verify3_Update.php?error=general_error');
    exit;
}
