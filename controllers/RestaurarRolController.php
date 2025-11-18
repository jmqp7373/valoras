<?php
/**
 * Controlador para restaurar el rol original del Superadmin
 * Proyecto: Valora.vip
 */

require_once __DIR__ . '/../config/database.php';
startSessionSafely();

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

try {
    $db = getDBConnection();
    $user_id = $_SESSION['user_id'] ?? null;
    
    // LOG: Debug información inicial
    error_log("=== RestaurarRolController Debug ===");
    error_log("User ID: " . $user_id);
    error_log("Session data: " . print_r($_SESSION, true));
    
    // Verificar que existe un rol original guardado
    if (!isset($_SESSION['rol_original_id'])) {
        error_log("ERROR: No hay rol_original_id en sesión");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No hay rol original para restaurar']);
        exit();
    }
    
    $rol_original_id = $_SESSION['rol_original_id'];
    $rol_original_nombre = $_SESSION['rol_original_nombre'] ?? 'Superadmin';
    
    error_log("Restaurando a: id=$rol_original_id, nombre=$rol_original_nombre");
    
    // Restaurar el rol en la BD
    $stmt = $db->prepare("UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?");
    $result = $stmt->execute([$rol_original_id, $user_id]);
    
    error_log("UPDATE resultado: " . ($result ? 'SUCCESS' : 'FAILED'));
    error_log("Filas afectadas: " . $stmt->rowCount());
    
    // Limpiar las variables de sesión temporales
    unset($_SESSION['rol_prueba_id']);
    unset($_SESSION['rol_prueba_nombre']);
    unset($_SESSION['rol_original_id']);
    unset($_SESSION['rol_original_nombre']);
    
    error_log("Variables de sesión limpiadas");
    
    echo json_encode([
        'success' => true,
        'message' => 'Rol restaurado a ' . $rol_original_nombre,
        'rol_id' => $rol_original_id,
        'rol_nombre' => $rol_original_nombre,
        'debug' => [
            'rows_affected' => $stmt->rowCount()
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en RestaurarRolController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
