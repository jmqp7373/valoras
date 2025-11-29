<?php
/**
 * Controlador: Afiliados
 * 
 * Maneja las operaciones CRUD de modelos, líderes y referentes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Afiliados.php';

startSessionSafely();

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

// Inicializar conexión y modelo
$database = new Database();
$db = $database->getConnection();
$afiliados = new Afiliados($db);

// Obtener acción
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'obtenerModelos':
            $modelos = $afiliados->obtenerModelos();
            echo json_encode(['success' => true, 'data' => $modelos]);
            break;
            
        case 'obtenerLideres':
            $lideres = $afiliados->obtenerLideres();
            echo json_encode(['success' => true, 'data' => $lideres]);
            break;
            
        case 'obtenerReferentes':
            $referentes = $afiliados->obtenerReferentes();
            echo json_encode(['success' => true, 'data' => $referentes]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en AfiliadosController: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
