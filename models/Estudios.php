<?php
require_once __DIR__ . '/../config/database.php';

class Estudios {
    private $conn;
    private $table_estudios = 'estudios';
    private $table_casas = 'estudios_casas';
    private $table_categorias = 'estudios_categorias';
    private $table_clases = 'estudios_clases';
    private $table_auditoria = 'estudios_auditoria';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ==================== ESTUDIOS ====================
    
    /**
     * Obtener todos los estudios
     */
    public function obtenerEstudios($id_usuario = null, $es_admin = false) {
        if ($es_admin) {
            $query = "SELECT * FROM " . $this->table_estudios . " ORDER BY nombre_estudio ASC";
            $stmt = $this->conn->prepare($query);
        } else {
            $query = "SELECT DISTINCT e.* FROM " . $this->table_estudios . " e
                     INNER JOIN usuarios_estudios ue ON e.id_estudio = ue.id_estudio
                     WHERE ue.id_usuario = ?
                     ORDER BY e.nombre_estudio ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_usuario);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un estudio por ID
     */
    public function obtenerEstudioPorId($id_estudio) {
        $query = "SELECT * FROM " . $this->table_estudios . " WHERE id_estudio = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_estudio);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nuevo estudio
     */
    public function crearEstudio($datos) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_estudios . " 
                     (nombre_estudio, id_estudio_casa, id_lider, porcent_lider, id_seguridad_aplicacion_rol, estado) 
                     VALUES (:nombre_estudio, :id_estudio_casa, :id_lider, :porcent_lider, :id_seguridad_aplicacion_rol, :estado)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio' => $datos['nombre_estudio'],
                ':id_estudio_casa' => $datos['id_estudio_casa'],
                ':id_lider' => $datos['id_lider'] ?? 0,
                ':porcent_lider' => $datos['porcent_lider'] ?? 2.50,
                ':id_seguridad_aplicacion_rol' => $datos['id_seguridad_aplicacion_rol'] ?? 0,
                ':estado' => $datos['estado'] ?? 1
            ]);

            $id_estudio = $this->conn->lastInsertId();

            // Registrar auditoría
            $this->registrarAuditoria('estudios', $id_estudio, 'INSERT', null, $datos, 
                'Creación de nuevo estudio: ' . $datos['nombre_estudio']);

            $this->conn->commit();
            return ['success' => true, 'id_estudio' => $id_estudio, 'message' => 'Estudio creado exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al crear estudio: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar estudio
     */
    public function actualizarEstudio($id_estudio, $datos) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerEstudioPorId($id_estudio);

            $query = "UPDATE " . $this->table_estudios . " 
                     SET nombre_estudio = :nombre_estudio, 
                         id_estudio_casa = :id_estudio_casa,
                         id_lider = :id_lider,
                         porcent_lider = :porcent_lider,
                         id_seguridad_aplicacion_rol = :id_seguridad_aplicacion_rol,
                         estado = :estado
                     WHERE id_estudio = :id_estudio";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio' => $datos['nombre_estudio'],
                ':id_estudio_casa' => $datos['id_estudio_casa'],
                ':id_lider' => $datos['id_lider'] ?? 0,
                ':porcent_lider' => $datos['porcent_lider'] ?? 2.50,
                ':id_seguridad_aplicacion_rol' => $datos['id_seguridad_aplicacion_rol'] ?? 0,
                ':estado' => $datos['estado'] ?? 1,
                ':id_estudio' => $id_estudio
            ]);

            // Registrar auditoría
            $this->registrarAuditoria('estudios', $id_estudio, 'UPDATE', $datos_anteriores, $datos, 
                'Actualización de estudio: ' . $datos['nombre_estudio']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Estudio actualizado exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar estudio: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar estudio
     */
    public function eliminarEstudio($id_estudio) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerEstudioPorId($id_estudio);

            $query = "DELETE FROM " . $this->table_estudios . " WHERE id_estudio = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_estudio);
            $stmt->execute();

            // Registrar auditoría
            $this->registrarAuditoria('estudios', $id_estudio, 'DELETE', $datos_anteriores, null, 
                'Eliminación de estudio: ' . $datos_anteriores['nombre_estudio']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Estudio eliminado exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al eliminar estudio: ' . $e->getMessage()];
        }
    }

    // ==================== CASAS ====================

    /**
     * Obtener casas por estudio
     */
    public function obtenerCasasPorEstudio($id_estudio) {
        $query = "SELECT * FROM " . $this->table_casas . " 
                 WHERE id_estudio_casa = ? 
                 ORDER BY nombre_estudio_casa ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_estudio);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las casas con información del estudio
     */
    public function obtenerTodasCasas() {
        $query = "SELECT c.*, e.id_estudio, e.nombre_estudio as estudio_nombre 
                 FROM " . $this->table_casas . " c
                 LEFT JOIN " . $this->table_estudios . " e ON c.id_estudio_casa = e.id_estudio
                 ORDER BY e.nombre_estudio, c.nombre_estudio_casa ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener una casa por ID
     */
    public function obtenerCasaPorId($id_casa) {
        $query = "SELECT * FROM " . $this->table_casas . " WHERE id_estudio_casa = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_casa);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nueva casa
     */
    public function crearCasa($datos) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_casas . " 
                     (nombre_estudio_casa, id_estudio_metrica, meta_quincenal_estudio, unidad_de_meta, 
                      id_lider, id_estudio_categoria, id_estudio_clase, estado) 
                     VALUES (:nombre_estudio_casa, :id_estudio_metrica, :meta_quincenal_estudio, :unidad_de_meta,
                             :id_lider, :id_estudio_categoria, :id_estudio_clase, :estado)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio_casa' => $datos['nombre_estudio_casa'],
                ':id_estudio_metrica' => $datos['id_estudio_metrica'] ?? '',
                ':meta_quincenal_estudio' => $datos['meta_quincenal_estudio'] ?? 0,
                ':unidad_de_meta' => $datos['unidad_de_meta'] ?? '',
                ':id_lider' => $datos['id_lider'] ?? 0,
                ':id_estudio_categoria' => $datos['id_estudio_categoria'],
                ':id_estudio_clase' => $datos['id_estudio_clase'],
                ':estado' => $datos['estado'] ?? 1
            ]);

            $id_casa = $this->conn->lastInsertId();

            // Registrar auditoría
            $this->registrarAuditoria('estudios_casas', $id_casa, 'INSERT', null, $datos, 
                'Creación de nueva casa: ' . $datos['nombre_estudio_casa']);

            $this->conn->commit();
            return ['success' => true, 'id_casa' => $id_casa, 'message' => 'Casa creada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al crear casa: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar casa
     */
    public function actualizarCasa($id_casa, $datos) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerCasaPorId($id_casa);

            $query = "UPDATE " . $this->table_casas . " 
                     SET nombre_estudio_casa = :nombre_estudio_casa,
                         id_estudio_metrica = :id_estudio_metrica,
                         meta_quincenal_estudio = :meta_quincenal_estudio,
                         unidad_de_meta = :unidad_de_meta,
                         id_lider = :id_lider,
                         id_estudio_categoria = :id_estudio_categoria,
                         id_estudio_clase = :id_estudio_clase,
                         estado = :estado
                     WHERE id_estudio_casa = :id_casa";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio_casa' => $datos['nombre_estudio_casa'],
                ':id_estudio_metrica' => $datos['id_estudio_metrica'] ?? '',
                ':meta_quincenal_estudio' => $datos['meta_quincenal_estudio'] ?? 0,
                ':unidad_de_meta' => $datos['unidad_de_meta'] ?? '',
                ':id_lider' => $datos['id_lider'] ?? 0,
                ':id_estudio_categoria' => $datos['id_estudio_categoria'],
                ':id_estudio_clase' => $datos['id_estudio_clase'],
                ':estado' => $datos['estado'] ?? 1,
                ':id_casa' => $id_casa
            ]);

            // Registrar auditoría
            $this->registrarAuditoria('estudios_casas', $id_casa, 'UPDATE', $datos_anteriores, $datos, 
                'Actualización de casa: ' . $datos['nombre_estudio_casa']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Casa actualizada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar casa: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar casa
     */
    public function eliminarCasa($id_casa) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerCasaPorId($id_casa);

            $query = "DELETE FROM " . $this->table_casas . " WHERE id_estudio_casa = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_casa);
            $stmt->execute();

            // Registrar auditoría
            $this->registrarAuditoria('estudios_casas', $id_casa, 'DELETE', $datos_anteriores, null, 
                'Eliminación de casa: ' . $datos_anteriores['nombre_estudio_casa']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Casa eliminada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al eliminar casa: ' . $e->getMessage()];
        }
    }

    // ==================== CATEGORÍAS ====================

    /**
     * Obtener todas las categorías
     */
    public function obtenerCategorias() {
        $query = "SELECT * FROM " . $this->table_categorias . " ORDER BY nombre_estudio_categoria ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener una categoría por ID
     */
    public function obtenerCategoriaPorId($id_categoria) {
        $query = "SELECT * FROM " . $this->table_categorias . " WHERE id_estudio_categoria = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_categoria);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nueva categoría
     */
    public function crearCategoria($datos) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_categorias . " 
                     (nombre_estudio_categoria, id_seguridad_aplicacion_rol_director, id_seguridad_aplicacion_rol_colaborador) 
                     VALUES (:nombre_estudio_categoria, :id_seguridad_aplicacion_rol_director, :id_seguridad_aplicacion_rol_colaborador)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio_categoria' => $datos['nombre_estudio_categoria'],
                ':id_seguridad_aplicacion_rol_director' => $datos['id_seguridad_aplicacion_rol_director'] ?? 0,
                ':id_seguridad_aplicacion_rol_colaborador' => $datos['id_seguridad_aplicacion_rol_colaborador'] ?? 0
            ]);

            $id_categoria = $this->conn->lastInsertId();

            // Registrar auditoría
            $this->registrarAuditoria('estudios_categorias', $id_categoria, 'INSERT', null, $datos, 
                'Creación de nueva categoría: ' . $datos['nombre_estudio_categoria']);

            $this->conn->commit();
            return ['success' => true, 'id_categoria' => $id_categoria, 'message' => 'Categoría creada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al crear categoría: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar categoría
     */
    public function actualizarCategoria($id_categoria, $datos) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerCategoriaPorId($id_categoria);

            $query = "UPDATE " . $this->table_categorias . " 
                     SET nombre_estudio_categoria = :nombre_estudio_categoria,
                         id_seguridad_aplicacion_rol_director = :id_seguridad_aplicacion_rol_director,
                         id_seguridad_aplicacion_rol_colaborador = :id_seguridad_aplicacion_rol_colaborador
                     WHERE id_estudio_categoria = :id_categoria";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio_categoria' => $datos['nombre_estudio_categoria'],
                ':id_seguridad_aplicacion_rol_director' => $datos['id_seguridad_aplicacion_rol_director'] ?? 0,
                ':id_seguridad_aplicacion_rol_colaborador' => $datos['id_seguridad_aplicacion_rol_colaborador'] ?? 0,
                ':id_categoria' => $id_categoria
            ]);

            // Registrar auditoría
            $this->registrarAuditoria('estudios_categorias', $id_categoria, 'UPDATE', $datos_anteriores, $datos, 
                'Actualización de categoría: ' . $datos['nombre_estudio_categoria']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Categoría actualizada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar categoría: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar categoría
     */
    public function eliminarCategoria($id_categoria) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerCategoriaPorId($id_categoria);

            $query = "DELETE FROM " . $this->table_categorias . " WHERE id_estudio_categoria = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_categoria);
            $stmt->execute();

            // Registrar auditoría
            $this->registrarAuditoria('estudios_categorias', $id_categoria, 'DELETE', $datos_anteriores, null, 
                'Eliminación de categoría: ' . $datos_anteriores['nombre_estudio_categoria']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Categoría eliminada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al eliminar categoría: ' . $e->getMessage()];
        }
    }

    // ==================== CLASES ====================

    /**
     * Obtener todas las clases
     */
    public function obtenerClases() {
        $query = "SELECT * FROM " . $this->table_clases . " ORDER BY nombre_estudio_clase ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener una clase por ID
     */
    public function obtenerClasePorId($id_clase) {
        $query = "SELECT * FROM " . $this->table_clases . " WHERE id_estudio_clase = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_clase);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nueva clase
     */
    public function crearClase($datos) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_clases . " 
                     (nombre_estudio_clase) 
                     VALUES (:nombre_estudio_clase)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio_clase' => $datos['nombre_estudio_clase']
            ]);

            $id_clase = $this->conn->lastInsertId();

            // Registrar auditoría
            $this->registrarAuditoria('estudios_clases', $id_clase, 'INSERT', null, $datos, 
                'Creación de nueva clase: ' . $datos['nombre_estudio_clase']);

            $this->conn->commit();
            return ['success' => true, 'id_clase' => $id_clase, 'message' => 'Clase creada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al crear clase: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar clase
     */
    public function actualizarClase($id_clase, $datos) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerClasePorId($id_clase);

            $query = "UPDATE " . $this->table_clases . " 
                     SET nombre_estudio_clase = :nombre_estudio_clase
                     WHERE id_estudio_clase = :id_clase";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':nombre_estudio_clase' => $datos['nombre_estudio_clase'],
                ':id_clase' => $id_clase
            ]);

            // Registrar auditoría
            $this->registrarAuditoria('estudios_clases', $id_clase, 'UPDATE', $datos_anteriores, $datos, 
                'Actualización de clase: ' . $datos['nombre_estudio_clase']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Clase actualizada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar clase: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar clase
     */
    public function eliminarClase($id_clase) {
        try {
            $this->conn->beginTransaction();

            // Obtener datos anteriores para auditoría
            $datos_anteriores = $this->obtenerClasePorId($id_clase);

            $query = "DELETE FROM " . $this->table_clases . " WHERE id_estudio_clase = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_clase);
            $stmt->execute();

            // Registrar auditoría
            $this->registrarAuditoria('estudios_clases', $id_clase, 'DELETE', $datos_anteriores, null, 
                'Eliminación de clase: ' . $datos_anteriores['nombre_estudio_clase']);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Clase eliminada exitosamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al eliminar clase: ' . $e->getMessage()];
        }
    }

    // ==================== AUDITORÍA ====================

    /**
     * Obtener historial de auditoría
     */
    public function obtenerHistorialAuditoria($tabla = null, $id_registro = null, $limit = 100) {
        $query = "SELECT a.*, u.nombre, u.apellido 
                 FROM " . $this->table_auditoria . " a
                 LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
                 WHERE 1=1";
        
        $params = [];
        
        if ($tabla !== null) {
            $query .= " AND a.tabla = ?";
            $params[] = ['value' => $tabla, 'type' => PDO::PARAM_STR];
        }
        
        if ($id_registro !== null) {
            $query .= " AND a.id_registro = ?";
            $params[] = ['value' => $id_registro, 'type' => PDO::PARAM_INT];
        }
        
        $query .= " ORDER BY a.fecha_auditoria DESC LIMIT ?";
        $params[] = ['value' => (int)$limit, 'type' => PDO::PARAM_INT];
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param['value'], $param['type']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar auditoría
     */
    private function registrarAuditoria($tabla, $id_registro, $accion, $datos_anteriores, $datos_nuevos, $descripcion) {
        try {
            $id_usuario = $_SESSION['id_usuario'] ?? null;
            
            $query = "INSERT INTO " . $this->table_auditoria . "
                     (tabla, id_registro, accion, datos_anteriores, datos_nuevos, id_usuario, descripcion)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $tabla,
                $id_registro,
                $accion,
                json_encode($datos_anteriores),
                json_encode($datos_nuevos),
                $id_usuario,
                $descripcion
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error al registrar auditoría: " . $e->getMessage());
            return false;
        }
    }
}
