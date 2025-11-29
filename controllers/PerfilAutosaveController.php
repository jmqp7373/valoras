<?php
/**
 * API endpoint para auto-guardado de perfil
 * Maneja actualizaciones AJAX de campos individuales
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $usuarioModel = new Usuario($db);
    
    $id_usuario = $_SESSION['user_id'];
    $datos = [];
    
    // Campos permitidos para auto-guardado
    $campos_permitidos = [
        'nombres', 'apellidos', 'celular', 'email', 'fecha_nacimiento',
        'tipo_sangre', 'direccion', 'ciudad', 'codigo_pais', 'cedula',
        'contacto_emergencia_nombre', 'contacto_emergencia_parentesco', 'contacto_emergencia_telefono',
        'alergias', 'id_banco', 'banco_tipo_cuenta', 'banco_numero_cuenta',
        'notas', 'id_estudio', 'ref1_nombre', 'ref1_parentesco', 'ref1_celular', 'id_referente'
    ];
    
    // Recoger solo los campos enviados
    foreach ($campos_permitidos as $campo) {
        if (isset($_POST[$campo])) {
            $datos[$campo] = trim($_POST[$campo]);
        }
    }
    
    // Manejar días de descanso (viene como JSON)
    if (isset($_POST['dias_descanso'])) {
        $datos['dias_descanso'] = $_POST['dias_descanso']; // Ya viene como JSON desde el frontend
    }
    
    // Si no hay datos, retornar error
    if (empty($datos)) {
        echo json_encode(['success' => false, 'message' => 'No hay datos para actualizar']);
        exit;
    }
    
    // Actualizar perfil
    $resultado = $usuarioModel->actualizarPerfil($id_usuario, $datos);
    
    if ($resultado['success']) {
        // Actualizar variables de sesión si se modificaron
        if (isset($datos['nombres'])) {
            $_SESSION['user_nombres'] = $datos['nombres'];
        }
        if (isset($datos['apellidos'])) {
            $_SESSION['user_apellidos'] = $datos['apellidos'];
        }
        if (isset($datos['email'])) {
            $_SESSION['user_email'] = $datos['email'];
        }
        
        // Retornar respuesta exitosa con progreso
        echo json_encode([
            'success' => true,
            'message' => 'Campo actualizado correctamente',
            'progreso' => $resultado['progreso'] ?? 0
        ]);
    } else {
        echo json_encode($resultado);
    }
    
} catch (Exception $e) {
    error_log("Error en autosave: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar: ' . $e->getMessage()
    ]);
}
?>
