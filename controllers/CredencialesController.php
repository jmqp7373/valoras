<?php
/**
 * Controlador de Credenciales
 * Proyecto: Valora.vip
 * Autor: Sistema Valora
 * Fecha: 2025-11-15
 * 
 * Gestiona la administración de credenciales de modelos en plataformas
 */

require_once __DIR__ . '/../config/database.php';

class CredencialesController {
    private $conn;

    public function __construct() {
        try {
            $this->conn = getDBConnection();
            
            // Verificar autenticación
            startSessionSafely();
            if (!isLoggedIn()) {
                header('Location: views/login/login.php');
                exit();
            }
        } catch (Exception $e) {
            error_log("Error en CredencialesController: " . $e->getMessage());
            die("Error de conexión a la base de datos");
        }
    }

    /**
     * Método principal para mostrar la vista index
     */
    public function index() {
        try {
            // Cargar listas para los filtros
            $paginas = $this->obtenerPaginas();
            $estudios = $this->obtenerEstudios();
            $casas = $this->obtenerEstudioCasas();
            $cuentasEstudios = $this->obtenerCuentasEstudios();

            // Incluir la vista
            require __DIR__ . '/../views/credenciales/credenciales_index.php';
        } catch (Exception $e) {
            error_log("Error en index de credenciales: " . $e->getMessage());
            die("Error al cargar el módulo de credenciales");
        }
    }

    /**
     * Endpoint AJAX para listar credenciales con filtros y paginación
     */
    public function listarAjax() {
        header('Content-Type: application/json');
        
        try {
            // Leer parámetros
            $modeloBusqueda = $_GET['modelo'] ?? '';
            $plataformaId = $_GET['plataforma'] ?? '';
            $estudioId = $_GET['estudio'] ?? '';
            $casaId = $_GET['casa'] ?? '';
            $cuentaEstudioId = $_GET['cuenta_estudio'] ?? '';
            $estado = $_GET['estado'] ?? 'todas';
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));
            $porPagina = 50;
            $offset = ($pagina - 1) * $porPagina;

            // Construir SQL base
            $sqlBase = "
                FROM credenciales c
                INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
                INNER JOIN paginas p ON p.id_pagina = c.id_pagina
                INNER JOIN cuentas_estudios ce ON ce.id_cuenta_estudio = c.id_cuenta_estudio
                INNER JOIN estudios e ON e.id_estudio = ce.id_estudio
                INNER JOIN estudios_casas ec ON ec.id_estudio_casa = e.id_estudio_casa
                WHERE 1=1
            ";

            $params = [];

            // Aplicar filtros
            if (!empty($modeloBusqueda)) {
                $sqlBase .= " AND (u.nombres LIKE ? OR u.apellidos LIKE ? OR c.usuario LIKE ?)";
                $searchTerm = "%{$modeloBusqueda}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($plataformaId)) {
                $sqlBase .= " AND c.id_pagina = ?";
                $params[] = $plataformaId;
            }

            if (!empty($estudioId)) {
                $sqlBase .= " AND e.id_estudio = ?";
                $params[] = $estudioId;
            }

            if (!empty($casaId)) {
                $sqlBase .= " AND ec.id_estudio_casa = ?";
                $params[] = $casaId;
            }

            if (!empty($cuentaEstudioId)) {
                $sqlBase .= " AND c.id_cuenta_estudio = ?";
                $params[] = $cuentaEstudioId;
            }

            if ($estado === 'activas') {
                $sqlBase .= " AND c.eliminado = 0";
            } elseif ($estado === 'eliminadas') {
                $sqlBase .= " AND c.eliminado = 1";
            }

            // Obtener total de registros
            $sqlCount = "SELECT COUNT(*) as total " . $sqlBase;
            $stmtCount = $this->conn->prepare($sqlCount);
            $stmtCount->execute($params);
            $totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPaginas = ceil($totalRegistros / $porPagina);

            // Obtener datos paginados
            $sqlData = "
                SELECT 
                    c.id_credencial,
                    c.usuario AS usuario_credencial,
                    c.password,
                    c.email_de_registro,
                    c.fecha_creacion,
                    c.eliminado,
                    u.nombres AS nombre,
                    u.apellidos,
                    p.nombre_pagina,
                    p.color_pagina,
                    ce.usuario_cuenta_estudio,
                    e.nombre_estudio,
                    ec.nombre_estudio_casa
                " . $sqlBase . "
                ORDER BY c.id_credencial DESC
                LIMIT ? OFFSET ?
            ";

            $params[] = $porPagina;
            $params[] = $offset;

            $stmtData = $this->conn->prepare($sqlData);
            $stmtData->execute($params);
            $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            // Respuesta JSON
            echo json_encode([
                'success' => true,
                'data' => $data,
                'pagina_actual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_registros' => $totalRegistros
            ]);

        } catch (Exception $e) {
            error_log("Error en listarAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar credenciales: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener todas las páginas/plataformas
     */
    private function obtenerPaginas() {
        $stmt = $this->conn->query("SELECT id_pagina, nombre_pagina, color_pagina FROM paginas ORDER BY nombre_pagina");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todos los estudios
     */
    private function obtenerEstudios() {
        $stmt = $this->conn->query("SELECT id_estudio, nombre_estudio FROM estudios ORDER BY nombre_estudio");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las casas de estudio
     */
    private function obtenerEstudioCasas() {
        $stmt = $this->conn->query("SELECT id_estudio_casa, nombre_estudio_casa FROM estudios_casas ORDER BY nombre_estudio_casa");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las cuentas de estudio activas
     */
    private function obtenerCuentasEstudios() {
        $stmt = $this->conn->query("
            SELECT DISTINCT ce.id_cuenta_estudio, ce.usuario_cuenta_estudio, p.nombre_pagina
            FROM cuentas_estudios ce
            INNER JOIN paginas p ON p.id_pagina = ce.id_pagina
            WHERE ce.estado = 1
            ORDER BY p.nombre_pagina, ce.usuario_cuenta_estudio
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Manejo de solicitudes
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CredencialesController();
    $action = $_GET['action'] ?? 'index';

    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
}
