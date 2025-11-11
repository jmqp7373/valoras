<?php
/**
 * Controlador de Permisos
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-08
 * 
 * Gestiona las operaciones CRUD de permisos por rol y por usuario
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Permisos.php';

class PermissionsController {
    private $conn;
    private $permisosModel;

    public function __construct() {
        try {
            $this->conn = getDBConnection();
            $this->permisosModel = new Permisos($this->conn);
            
            // Verificar autenticación
            startSessionSafely();
            if (!isLoggedIn()) {
                $this->jsonResponse(['success' => false, 'message' => 'No autenticado'], 401);
                exit();
            }

            // Verificar que el usuario sea admin o superadmin
            $idUsuario = $_SESSION['user_id'] ?? null;
            if (!$idUsuario || !$this->permisosModel->esAdmin($idUsuario)) {
                $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
                exit();
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()], 500);
            exit();
        }
    }

    /**
     * Enrutador principal
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? null;

        switch ($action) {
            case 'getRoles':
                $this->getRoles();
                break;
            case 'getPermisosPorRol':
                $this->getPermisosPorRol();
                break;
            case 'getUsuarios':
                $this->getUsuarios();
                break;
            case 'getPermisosPorUsuario':
                $this->getPermisosPorUsuario();
                break;
            case 'guardarPermisoRol':
                $this->guardarPermisoRol();
                break;
            case 'guardarPermisoUsuario':
                $this->guardarPermisoUsuario();
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
        }
    }

    /**
     * Obtener lista de roles
     */
    private function getRoles() {
        try {
            $roles = $this->permisosModel->obtenerRoles();
            $this->jsonResponse(['success' => true, 'roles' => $roles]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener permisos de un rol específico
     */
    private function getPermisosPorRol() {
        $idRol = $_GET['id_rol'] ?? null;
        
        if (!$idRol) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de rol requerido'], 400);
            return;
        }

        try {
            $permisos = $this->permisosModel->obtenerPermisosPorRol($idRol);
            $this->jsonResponse(['success' => true, 'permisos' => $permisos]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener lista de usuarios
     */
    private function getUsuarios() {
        try {
            $usuarios = $this->permisosModel->obtenerUsuariosConRoles();
            $this->jsonResponse(['success' => true, 'usuarios' => $usuarios]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener permisos de un usuario específico
     */
    private function getPermisosPorUsuario() {
        $idUsuario = $_GET['id_usuario'] ?? null;
        
        if (!$idUsuario) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de usuario requerido'], 400);
            return;
        }

        try {
            $permisos = $this->permisosModel->obtenerPermisosPorUsuario($idUsuario);
            $this->jsonResponse(['success' => true, 'permisos' => $permisos]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Guardar permiso de un rol
     */
    private function guardarPermisoRol() {
        // Validar CSRF token
        if (!$this->validarCSRF()) {
            $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            return;
        }

        $idRol = $_POST['id_rol'] ?? null;
        $modulo = $_POST['modulo'] ?? null;
        
        // Nuevo formato: permiso individual (ver, editar, eliminar)
        if (isset($_POST['permiso']) && isset($_POST['valor'])) {
            $permiso = $_POST['permiso'];
            $valor = (int)$_POST['valor'];
            
            if (!$idRol || !$modulo) {
                $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos'], 400);
                return;
            }
            
            try {
                // Obtener permisos actuales
                $permisosActuales = $this->permisosModel->obtenerPermisosPorRol($idRol);
                $permisoActual = $permisosActuales[$modulo] ?? [
                    'puede_ver' => 0,
                    'puede_editar' => 0,
                    'puede_eliminar' => 0
                ];
                
                // Actualizar solo el permiso específico
                $puedeVer = $permisoActual['puede_ver'];
                $puedeEditar = $permisoActual['puede_editar'];
                $puedeEliminar = $permisoActual['puede_eliminar'];
                
                if ($permiso === 'ver') {
                    $puedeVer = $valor;
                } elseif ($permiso === 'editar') {
                    $puedeEditar = $valor;
                } elseif ($permiso === 'eliminar') {
                    $puedeEliminar = $valor;
                }
                
                $resultado = $this->permisosModel->actualizarPermisoRol(
                    $idRol,
                    $modulo,
                    $puedeVer,
                    $puedeEditar,
                    $puedeEliminar
                );
                
                if ($resultado) {
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Permiso actualizado correctamente'
                    ]);
                } else {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'No se pudo actualizar el permiso'
                    ], 500);
                }
            } catch (Exception $e) {
                $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return;
        }
        
        // Formato antiguo: todos los permisos a la vez
        $puedeVer = isset($_POST['puede_ver']) ? (int)$_POST['puede_ver'] : 0;
        $puedeEditar = isset($_POST['puede_editar']) ? (int)$_POST['puede_editar'] : 0;
        $puedeEliminar = isset($_POST['puede_eliminar']) ? (int)$_POST['puede_eliminar'] : 0;

        if (!$idRol || !$modulo) {
            $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos'], 400);
            return;
        }

        try {
            $resultado = $this->permisosModel->actualizarPermisoRol(
                $idRol,
                $modulo,
                $puedeVer,
                $puedeEditar,
                $puedeEliminar
            );

            if ($resultado) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Permiso actualizado correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo actualizar el permiso'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Guardar permiso de un usuario
     */
    private function guardarPermisoUsuario() {
        // Validar CSRF token
        if (!$this->validarCSRF()) {
            $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            return;
        }

        $idUsuario = $_POST['id_usuario'] ?? null;
        $modulo = $_POST['modulo'] ?? null;
        $puedeVer = isset($_POST['puede_ver']) ? (int)$_POST['puede_ver'] : 0;
        $puedeEditar = isset($_POST['puede_editar']) ? (int)$_POST['puede_editar'] : 0;
        $puedeEliminar = isset($_POST['puede_eliminar']) ? (int)$_POST['puede_eliminar'] : 0;

        if (!$idUsuario || !$modulo) {
            $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos'], 400);
            return;
        }

        try {
            $resultado = $this->permisosModel->actualizarPermisoUsuario(
                $idUsuario,
                $modulo,
                $puedeVer,
                $puedeEditar,
                $puedeEliminar
            );

            if ($resultado) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Permiso actualizado correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo actualizar el permiso'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Validar token CSRF
     */
    private function validarCSRF() {
        $token = $_POST['csrf_token'] ?? '';
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Enviar respuesta JSON
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}

// Si se llama directamente al archivo, ejecutar el controlador
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new PermissionsController();
    $controller->handleRequest();
}
