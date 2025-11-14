<?php
/**
 * Controlador para cambio de rol temporal (solo Superadmin)
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
    error_log("=== CambiarRolController Debug ===");
    error_log("User ID: " . $user_id);
    error_log("POST data: " . print_r($_POST, true));
    
    // Verificar que el usuario actual es Superadmin (nivel_orden = 1)
    $stmt = $db->prepare("
        SELECT r.nombre, r.nivel_orden, u.nivel_orden as usuario_nivel_orden
        FROM usuarios u 
        JOIN roles r ON u.id_rol = r.id 
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("User data: " . print_r($user_data, true));
    error_log("Session rol_original_id: " . ($_SESSION['rol_original_id'] ?? 'NO SET'));
    
    // Usuario puede cambiar de rol si:
    // 1. Tiene nivel_orden = 1 (es Superadmin original)
    // 2. O tiene rol_original_id en sesión (ya está en modo prueba)
    $es_superadmin = ($user_data && ($user_data['usuario_nivel_orden'] == 1 || $user_data['nivel_orden'] == 1));
    $esta_en_modo_prueba = isset($_SESSION['rol_original_id']);
    
    if (!$es_superadmin && !$esta_en_modo_prueba) {
        error_log("ACCESO DENEGADO: No es Superadmin ni está en modo prueba");
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Solo Superadmin puede cambiar de rol',
            'debug' => [
                'usuario_nivel_orden' => $user_data['usuario_nivel_orden'] ?? null,
                'rol_nivel_orden' => $user_data['nivel_orden'] ?? null,
                'modo_prueba' => $esta_en_modo_prueba
            ]
        ]);
        exit();
    }
    
    error_log("Acceso permitido: es_superadmin=$es_superadmin, modo_prueba=$esta_en_modo_prueba");
    
    // Obtener datos del POST
    $rol_id = $_POST['rol_id'] ?? null;
    $rol_nombre = $_POST['rol_nombre'] ?? null;
    
    error_log("Rol solicitado - ID: $rol_id, Nombre: $rol_nombre");
    
    if (!$rol_id || !$rol_nombre) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }
    
    // Verificar que el rol existe
    $stmt = $db->prepare("SELECT id, nombre, nivel_orden FROM roles WHERE id = ?");
    $stmt->execute([$rol_id]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Rol encontrado: " . print_r($rol, true));
    
    if (!$rol) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
        exit();
    }
    
    // Guardar el rol original si no existe
    if (!isset($_SESSION['rol_original_id'])) {
        $stmt = $db->prepare("SELECT id_rol, nivel_orden FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$user_id]);
        $original_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['rol_original_id'] = $original_data['id_rol'];
        $_SESSION['rol_original_nivel_orden'] = $original_data['nivel_orden'];
        $_SESSION['rol_original_nombre'] = $user_data['nombre'];
        
        error_log("Rol original guardado: " . print_r($original_data, true));
    }
    
    // Cambiar el rol en la sesión (temporal)
    $_SESSION['rol_prueba_id'] = $rol_id;
    $_SESSION['rol_prueba_nombre'] = $rol_nombre;
    $_SESSION['rol_prueba_nivel_orden'] = $rol['nivel_orden'];
    
    // Actualizar temporalmente el rol del usuario en la BD
    $stmt = $db->prepare("UPDATE usuarios SET id_rol = ?, nivel_orden = ? WHERE id_usuario = ?");
    $result = $stmt->execute([$rol_id, $rol['nivel_orden'], $user_id]);
    
    error_log("UPDATE resultado: " . ($result ? 'SUCCESS' : 'FAILED'));
    error_log("Filas afectadas: " . $stmt->rowCount());
    
    echo json_encode([
        'success' => true,
        'message' => 'Rol cambiado temporalmente a ' . $rol_nombre,
        'rol_id' => $rol_id,
        'rol_nombre' => $rol_nombre,
        'debug' => [
            'nivel_orden' => $rol['nivel_orden'],
            'rows_affected' => $stmt->rowCount()
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en CambiarRolController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
