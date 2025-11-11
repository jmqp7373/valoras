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
$clave = $_POST['clave'] ?? '';
$nombreDescriptivo = trim($_POST['nombre_descriptivo'] ?? '');

if (empty($clave)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Clave del módulo requerida']);
    exit();
}

// Actualizar nombre descriptivo (puede estar vacío para resetear)
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
