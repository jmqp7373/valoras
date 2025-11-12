<?php
/**
 * Controlador para actualizar nombres descriptivos de módulos
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-09
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Permisos.php';

header('Content-Type: application/json');
startSessionSafely();

// Verificar autenticación y permisos de administrador
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$db = getDBConnection();
$permisosModel = new Permisos($db);
$idUsuario = $_SESSION['user_id'] ?? null;

if (!$idUsuario || !$permisosModel->esAdmin($idUsuario)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos de administrador']);
    exit();
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Validar token CSRF
$token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit();
}

// Obtener datos
$accion = $_POST['accion'] ?? 'actualizar_nombre';
$clave = $_POST['clave'] ?? '';

if (empty($clave)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Clave del módulo requerida']);
    exit();
}

// Procesar según la acción
if ($accion === 'marcar_eliminado') {
    // Marcar módulo como eliminado
    $resultado = $permisosModel->marcarComoEliminado($clave);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Módulo marcado como eliminado correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al marcar módulo como eliminado']);
    }
} elseif ($accion === 'toggle_exento') {
    // Toggle estado exento
    $exento = isset($_POST['exento']) ? (int)$_POST['exento'] : 0;
    $resultado = $permisosModel->toggleExento($clave, $exento);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Estado exento actualizado correctamente',
            'exento' => $exento
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar estado exento']);
    }
} else {
    // Acción por defecto: actualizar nombre descriptivo
    $nombreDescriptivo = trim($_POST['nombre_descriptivo'] ?? '');
    $resultado = $permisosModel->actualizarNombreDescriptivo($clave, $nombreDescriptivo);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Nombre descriptivo actualizado correctamente',
            'nombre' => $nombreDescriptivo
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el nombre descriptivo']);
    }
}

