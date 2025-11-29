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
    // Eliminar módulo permanentemente
    $resultado = $permisosModel->marcarComoEliminado($clave);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Módulo eliminado correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar módulo']);
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
} elseif ($accion === 'actualizar_icono') {
    // Actualizar icono del módulo
    $icono = $_POST['icono'] ?? '';
    
    try {
        $stmt = $db->prepare("UPDATE modulos SET icono = ? WHERE clave = ?");
        $resultado = $stmt->execute([$icono, $clave]);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Icono actualizado correctamente',
                'icono' => $icono
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el icono']);
        }
    } catch (PDOException $e) {
        error_log("Error actualizando icono: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} else {
    // Verificar si se está actualizando el subtítulo
    if (isset($_POST['subtitulo'])) {
        $subtitulo = trim($_POST['subtitulo']);
        
        try {
            $stmt = $db->prepare("UPDATE modulos SET subtitulo = ? WHERE clave = ?");
            $resultado = $stmt->execute([$subtitulo, $clave]);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Subtítulo actualizado correctamente',
                    'subtitulo' => $subtitulo
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el subtítulo']);
            }
        } catch (PDOException $e) {
            error_log("Error actualizando subtítulo: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    } else {
        // Acción por defecto: actualizar título
        $titulo = trim($_POST['titulo'] ?? $_POST['nombre_descriptivo'] ?? '');
        $resultado = $permisosModel->actualizarNombreDescriptivo($clave, $titulo);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Título actualizado correctamente',
                'nombre' => $titulo
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el título']);
        }
    }
}

