<?php
/**
 * API para obtener datos de permisos optimizada
 * Retorna todos los datos en una sola consulta SQL
 * Proyecto: Valora.vip
 * Fecha: 2025-11-09
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Permisos.php';

header('Content-Type: application/json');
startSessionSafely();

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

// Verificar permisos de administrador
$db = getDBConnection();
$permisosModel = new Permisos($db);
$idUsuario = $_SESSION['user_id'] ?? null;

if (!$idUsuario || !$permisosModel->esAdmin($idUsuario)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos de administrador']);
    exit();
}

try {
    // Obtener roles
    $roles = $permisosModel->obtenerRoles();
    
    // Obtener módulos con nombres
    $modulos = $permisosModel->obtenerModulosConNombres();
    
    // OPTIMIZACIÓN: Obtener TODOS los permisos en una sola consulta SQL
    $sql = "SELECT 
                rp.id_rol,
                rp.modulo,
                rp.puede_ver,
                rp.puede_editar,
                rp.puede_eliminar
            FROM roles_permisos rp
            ORDER BY rp.id_rol, rp.modulo";
    
    $stmt = $db->query($sql);
    $todosPermisos = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rolId = $row['id_rol'];
        $moduloClave = $row['modulo'];
        
        if (!isset($todosPermisos[$rolId])) {
            $todosPermisos[$rolId] = [];
        }
        
        $todosPermisos[$rolId][$moduloClave] = [
            'puede_ver' => (int)$row['puede_ver'],
            'puede_editar' => (int)$row['puede_editar'],
            'puede_eliminar' => (int)$row['puede_eliminar']
        ];
    }
    
    // Construir respuesta estructurada
    $response = [
        'success' => true,
        'roles' => $roles,
        'modulos' => [],
        'permisos' => $todosPermisos
    ];
    
    // Formatear módulos con categorías
    foreach ($modulos as $clave => $data) {
        $rutaCompleta = $data['ruta'];
        
        // Extraer categoría
        $categoria = 'sistema';
        if (preg_match('/views\\\\([^\\\\]+)/', $rutaCompleta, $matches)) {
            $categoria = $matches[1];
        }
        
        // Ruta simplificada
        $rutaMostrar = preg_replace('/^views\\\\[^\\\\]+/', '', $rutaCompleta);
        
        $response['modulos'][] = [
            'clave' => $clave,
            'ruta' => $rutaCompleta,
            'rutaMostrar' => $rutaMostrar,
            'nombreDescriptivo' => $data['titulo'] ?? '',
            'subtitulo' => $data['subtitulo'] ?? '',
            'categoria' => $categoria,
            'archivoExiste' => $data['archivo_existe'] ?? true,
            'exento' => $data['exento'] ?? 0,
            'icono' => $data['icono'] ?? null
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos: ' . $e->getMessage()
    ]);
}
