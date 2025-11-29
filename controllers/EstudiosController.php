<?php
/**
 * Controlador de Estudios
 * Maneja todas las peticiones AJAX para el módulo de gestión de estudios
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Estudios.php';

// Iniciar sesión de forma segura
startSessionSafely();

class EstudiosController {
    private $db;
    private $estudios;
    private $es_admin;
    private $id_usuario;
    private $id_estudio_usuario;

    public function __construct() {
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'No autenticado'], 401);
            exit;
        }

        // Inicializar conexión y modelo
        $database = new Database();
        $this->db = $database->getConnection();
        $this->estudios = new Estudios($this->db);

        // Obtener datos del usuario
        $this->id_usuario = $_SESSION['user_id'];
        $this->es_admin = $this->esAdministrador();
        $this->id_estudio_usuario = $this->obtenerEstudioUsuario();
    }

    /**
     * Verificar si el usuario es administrador
     */
    private function esAdministrador() {
        // Verificar si es superadmin o admin por rol
        if (isset($_SESSION['rol_actual'])) {
            $rol = strtolower($_SESSION['rol_actual']);
            if ($rol === 'superadmin' || $rol === 'admin') {
                return true;
            }
        }

        // Verificar por nivel_orden
        try {
            $query = "SELECT nivel_orden FROM usuarios WHERE id_usuario = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->id_usuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && $result['nivel_orden'] <= 2;
        } catch (Exception $e) {
            error_log("Error al verificar admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el estudio del usuario
     */
    private function obtenerEstudioUsuario() {
        try {
            $query = "SELECT id_estudio FROM usuarios WHERE id_usuario = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->id_usuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['id_estudio'] : null;
        } catch (Exception $e) {
            error_log("Error al obtener estudio: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si el usuario puede acceder a un estudio específico
     */
    private function puedeAccederEstudio($id_estudio) {
        if ($this->es_admin) {
            return true; // Admin puede acceder a todos
        }
        
        return $this->id_estudio_usuario == $id_estudio;
    }

    /**
     * Procesar solicitudes AJAX
     */
    public function procesarAjax() {
        $accion = $_POST['accion'] ?? $_GET['accion'] ?? null;

        if (!$accion) {
            $this->jsonResponse(['success' => false, 'message' => 'Acción no especificada'], 400);
            return;
        }

        switch ($accion) {
            // === ESTUDIOS ===
            case 'listar_estudios':
                $this->listarEstudios();
                break;
            case 'obtener_estudio':
                $this->obtenerEstudio();
                break;
            case 'crear_estudio':
                $this->crearEstudio();
                break;
            case 'actualizar_estudio':
                $this->actualizarEstudio();
                break;
            case 'eliminar_estudio':
                $this->eliminarEstudio();
                break;
            case 'actualizar_estudio_inline':
                $this->actualizarInline('estudio');
                break;

            // === CASAS ===
            case 'listar_casas':
                $this->listarCasas();
                break;
            case 'obtener_casa':
                $this->obtenerCasa();
                break;
            case 'crear_casa':
                $this->crearCasa();
                break;
            case 'actualizar_casa':
                $this->actualizarCasa();
                break;
            case 'eliminar_casa':
                $this->eliminarCasa();
                break;
            case 'actualizar_casa_inline':
                $this->actualizarInline('casa');
                break;

            // === CATEGORÍAS ===
            case 'listar_categorias':
                $this->listarCategorias();
                break;
            case 'obtener_categoria':
                $this->obtenerCategoria();
                break;
            case 'crear_categoria':
                $this->crearCategoria();
                break;
            case 'actualizar_categoria':
                $this->actualizarCategoria();
                break;
            case 'eliminar_categoria':
                $this->eliminarCategoria();
                break;
            case 'actualizar_categoria_inline':
                $this->actualizarInline('categoria');
                break;

            // === CLASES ===
            case 'listar_clases':
                $this->listarClases();
                break;
            case 'obtener_clase':
                $this->obtenerClase();
                break;
            case 'crear_clase':
                $this->crearClase();
                break;
            case 'actualizar_clase':
                $this->actualizarClase();
                break;
            case 'eliminar_clase':
                $this->eliminarClase();
                break;
            case 'actualizar_clase_inline':
                $this->actualizarInline('clase');
                break;

            // === AUDITORÍA ===
            case 'historial':
                $this->obtenerHistorial();
                break;

            default:
                $this->jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
        }
    }

    // ============================================
    // MÉTODOS DE ESTUDIOS
    // ============================================

    private function listarEstudios() {
        try {
            $estudios = $this->estudios->obtenerEstudios($this->id_usuario, $this->es_admin);
            $this->jsonResponse(['success' => true, 'data' => $estudios]);
        } catch (Exception $e) {
            error_log("Error en listarEstudios: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Error al cargar estudios: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerEstudio() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $estudio = $this->estudios->obtenerEstudioPorId($id);
        
        if (!$estudio) {
            $this->jsonResponse(['success' => false, 'message' => 'Estudio no encontrado'], 404);
            return;
        }

        if (!$this->puedeAccederEstudio($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $this->jsonResponse(['success' => true, 'data' => $estudio]);
    }

    private function crearEstudio() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? null
        ];

        if (empty($datos['nombre'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->crearEstudio($datos);
        $this->jsonResponse($resultado);
    }

    private function actualizarEstudio() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $id = $_POST['id_estudio'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? null
        ];

        if (empty($datos['nombre'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->actualizarEstudio($id, $datos);
        $this->jsonResponse($resultado);
    }

    private function eliminarEstudio() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $id = $_POST['id_estudio'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $resultado = $this->estudios->eliminarEstudio($id);
        $this->jsonResponse($resultado);
    }

    // ============================================
    // MÉTODOS DE CASAS
    // ============================================

    private function listarCasas() {
        try {
            $id_estudio = $_GET['id_estudio'] ?? null;

            if ($id_estudio) {
                // Verificar permisos
                if (!$this->puedeAccederEstudio($id_estudio)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
                    return;
                }
                $casas = $this->estudios->obtenerCasasPorEstudio($id_estudio);
            } else {
                $casas = $this->estudios->obtenerTodasCasas();
            }

            $this->jsonResponse(['success' => true, 'data' => $casas]);
        } catch (Exception $e) {
            error_log("Error en listarCasas: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Error al cargar casas: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerCasa() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $casa = $this->estudios->obtenerCasaPorId($id);
        
        if (!$casa) {
            $this->jsonResponse(['success' => false, 'message' => 'Casa no encontrada'], 404);
            return;
        }

        if (!$this->puedeAccederEstudio($casa['id_estudio'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $this->jsonResponse(['success' => true, 'data' => $casa]);
    }

    private function crearCasa() {
        $datos = [
            'id_estudio' => $_POST['id_estudio'] ?? null,
            'nombre_casa' => $_POST['nombre_casa'] ?? '',
            'url_casa' => $_POST['url_casa'] ?? null
        ];

        if (!$datos['id_estudio']) {
            $this->jsonResponse(['success' => false, 'message' => 'Estudio es requerido'], 400);
            return;
        }

        if (!$this->puedeAccederEstudio($datos['id_estudio'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        if (empty($datos['nombre_casa'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->crearCasa($datos);
        $this->jsonResponse($resultado);
    }

    private function actualizarCasa() {
        $id = $_POST['id_casa'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $casa_actual = $this->estudios->obtenerCasaPorId($id);
        if (!$casa_actual || !$this->puedeAccederEstudio($casa_actual['id_estudio'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $datos = [
            'id_estudio' => $_POST['id_estudio'] ?? $casa_actual['id_estudio'],
            'nombre_casa' => $_POST['nombre_casa'] ?? '',
            'url_casa' => $_POST['url_casa'] ?? null
        ];

        if (!$this->puedeAccederEstudio($datos['id_estudio'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        if (empty($datos['nombre_casa'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->actualizarCasa($id, $datos);
        $this->jsonResponse($resultado);
    }

    private function eliminarCasa() {
        $id = $_POST['id_casa'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $casa = $this->estudios->obtenerCasaPorId($id);
        if (!$casa || !$this->puedeAccederEstudio($casa['id_estudio'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $resultado = $this->estudios->eliminarCasa($id);
        $this->jsonResponse($resultado);
    }

    // ============================================
    // MÉTODOS DE CATEGORÍAS
    // ============================================

    private function listarCategorias() {
        try {
            $categorias = $this->estudios->obtenerCategorias();
            $this->jsonResponse(['success' => true, 'data' => $categorias]);
        } catch (Exception $e) {
            error_log("Error en listarCategorias: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Error al cargar categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerCategoria() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $categoria = $this->estudios->obtenerCategoriaPorId($id);
        
        if (!$categoria) {
            $this->jsonResponse(['success' => false, 'message' => 'Categoría no encontrada'], 404);
            return;
        }

        $this->jsonResponse(['success' => true, 'data' => $categoria]);
    }

    private function crearCategoria() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $datos = [
            'nombre_categoria' => $_POST['nombre_categoria'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? null
        ];

        if (empty($datos['nombre_categoria'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->crearCategoria($datos);
        $this->jsonResponse($resultado);
    }

    private function actualizarCategoria() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $id = $_POST['id_categoria'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $datos = [
            'nombre_categoria' => $_POST['nombre_categoria'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? null
        ];

        if (empty($datos['nombre_categoria'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->actualizarCategoria($id, $datos);
        $this->jsonResponse($resultado);
    }

    private function eliminarCategoria() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $id = $_POST['id_categoria'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $resultado = $this->estudios->eliminarCategoria($id);
        $this->jsonResponse($resultado);
    }

    // ============================================
    // MÉTODOS DE CLASES
    // ============================================

    private function listarClases() {
        try {
            $clases = $this->estudios->obtenerClases();
            $this->jsonResponse(['success' => true, 'data' => $clases]);
        } catch (Exception $e) {
            error_log("Error en listarClases: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Error al cargar clases: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerClase() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $clase = $this->estudios->obtenerClasePorId($id);
        
        if (!$clase) {
            $this->jsonResponse(['success' => false, 'message' => 'Clase no encontrada'], 404);
            return;
        }

        $this->jsonResponse(['success' => true, 'data' => $clase]);
    }

    private function crearClase() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $datos = [
            'nombre_clase' => $_POST['nombre_clase'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? null
        ];

        if (empty($datos['nombre_clase'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->crearClase($datos);
        $this->jsonResponse($resultado);
    }

    private function actualizarClase() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $id = $_POST['id_clase'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $datos = [
            'nombre_clase' => $_POST['nombre_clase'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? null
        ];

        if (empty($datos['nombre_clase'])) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 400);
            return;
        }

        $resultado = $this->estudios->actualizarClase($id, $datos);
        $this->jsonResponse($resultado);
    }

    private function eliminarClase() {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'Acceso denegado'], 403);
            return;
        }

        $id = $_POST['id_clase'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID no proporcionado'], 400);
            return;
        }

        $resultado = $this->estudios->eliminarClase($id);
        $this->jsonResponse($resultado);
    }

    // ============================================
    // AUDITORÍA
    // ============================================

    private function obtenerHistorial() {
        // NO usar $_GET['accion'] porque ya se usa para enrutar
        $filtros = [
            'tabla' => $_GET['tabla'] ?? null,
            'accion' => $_GET['tipo_accion'] ?? null  // Solo usar tipo_accion para filtrar
        ];

        $historial = $this->estudios->obtenerHistorial($filtros);
        $this->jsonResponse(['success' => true, 'data' => $historial]);
    }

    // ============================================
    // EDICIÓN INLINE
    // ============================================

    private function actualizarInline($tipo) {
        if (!$this->es_admin) {
            $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para actualizar'], 403);
        }

        $id = $_POST['id'] ?? null;
        $campo = $_POST['campo'] ?? null;
        $valor = $_POST['valor'] ?? null;

        if (!$id || !$campo) {
            $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
        }

        try {
            $tabla = '';
            $nombreCampo = '';
            $idCampo = '';

            switch($tipo) {
                case 'estudio':
                    $tabla = 'estudios';
                    $idCampo = 'id_estudio';
                    $nombreCampo = $campo === 'nombre' ? 'nombre_estudio' : $campo;
                    break;
                case 'casa':
                    $tabla = 'estudios_casas';
                    $idCampo = 'id_estudio_casa';
                    $nombreCampo = $campo === 'nombre' ? 'nombre_estudio_casa' : $campo;
                    break;
                case 'categoria':
                    $tabla = 'estudios_categorias';
                    $idCampo = 'id_estudio_categoria';
                    $nombreCampo = $campo === 'nombre' ? 'nombre_estudio_categoria' : $campo;
                    break;
                case 'clase':
                    $tabla = 'estudios_clases';
                    $idCampo = 'id_estudio_clase';
                    $nombreCampo = $campo === 'nombre' ? 'nombre_estudio_clase' : $campo;
                    break;
            }

            // Obtener valor anterior
            $queryAnterior = "SELECT $nombreCampo FROM $tabla WHERE $idCampo = ?";
            $stmtAnterior = $this->db->prepare($queryAnterior);
            $stmtAnterior->execute([$id]);
            $valorAnterior = $stmtAnterior->fetchColumn();

            // Actualizar registro
            $query = "UPDATE $tabla SET $nombreCampo = ? WHERE $idCampo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$valor, $id]);

            // Registrar edición
            $this->estudios->registrarEdicion(
                $tabla,
                $id,
                'UPDATE',
                [$nombreCampo => $valorAnterior],
                [$nombreCampo => $valor],
                $this->id_usuario
            );

            $this->jsonResponse(['success' => true, 'message' => 'Actualizado correctamente']);
        } catch (Exception $e) {
            error_log("Error en actualización inline: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    // ============================================
    // UTILIDADES
    // ============================================

    /**
     * Enviar respuesta JSON
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Procesar la petición si se llama directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new EstudiosController();
    $controller->procesarAjax();
}
