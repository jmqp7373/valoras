<?php
require_once __DIR__ . '/../config/database.php';

class Estudios {
    private $conn;
    private $table_estudios = 'estudios';
    private $table_casas = 'estudios_casas';
    private $table_categorias = 'estudios_categorias';
    private $table_clases = 'estudios_clases';
    private $table_ediciones = 'estudios_auditoria'; // Cambiar a 'estudios_ediciones' después de migración

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
    /**
     * Método legacy - redirige al nuevo sistema de ediciones
     * @deprecated Usar registrarEdicion() en su lugar
     */
    private function registrarAuditoria($tabla, $id_registro, $accion, $datos_anteriores, $datos_nuevos, $descripcion) {
        $id_usuario = $_SESSION['id_usuario'] ?? null;
        
        // No convertir - la tabla usa INSERT/UPDATE/DELETE directamente
        return $this->registrarEdicion($tabla, $id_registro, $accion, $datos_anteriores, $datos_nuevos, $id_usuario);
    }

    /**
     * Registrar edición en estudios_ediciones (compatible con estudios_auditoria)
     */
    public function registrarEdicion($tabla, $id_registro, $accion, $datos_anteriores = null, $datos_nuevos = null, $id_usuario = null) {
        try {
            // Usar nombres de columnas actuales de estudios_auditoria
            $query = "INSERT INTO " . $this->table_ediciones . " 
                     (tabla_afectada, id_registro, accion, datos_anteriores, datos_nuevos, id_usuario, ip_usuario, descripcion) 
                     VALUES (:tabla, :id_registro, :accion, :datos_anteriores, :datos_nuevos, :id_usuario, :ip, :descripcion)";
            
            // Generar descripción automática
            $descripcion = $this->generarDescripcionEdicion($tabla, $accion, $datos_anteriores, $datos_nuevos);
            
            // Convertir datos a JSON
            $datos_anteriores_json = $datos_anteriores ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null;
            $datos_nuevos_json = $datos_nuevos ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null;
            
            // Obtener IP
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':tabla' => $tabla,
                ':id_registro' => $id_registro,
                ':accion' => $accion,
                ':datos_anteriores' => $datos_anteriores_json,
                ':datos_nuevos' => $datos_nuevos_json,
                ':id_usuario' => $id_usuario,
                ':ip' => $ip,
                ':descripcion' => $descripcion
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error al registrar edición: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Generar descripción legible de la edición
     */
    private function generarDescripcionEdicion($tabla, $accion, $datos_anteriores, $datos_nuevos) {
        $nombres = [
            'estudios' => 'estudio',
            'estudios_casas' => 'casa',
            'estudios_categorias' => 'categoría',
            'estudios_clases' => 'clase'
        ];
        
        $tipo = $nombres[$tabla] ?? 'registro';
        
        switch($accion) {
            case 'INSERT':
            case 'CREATE':
                return "Creó {$tipo}";
            case 'UPDATE':
                if ($datos_anteriores && $datos_nuevos) {
                    $cambios = [];
                    foreach ($datos_nuevos as $campo => $nuevo) {
                        if (isset($datos_anteriores[$campo]) && $datos_anteriores[$campo] != $nuevo) {
                            $cambios[] = $campo;
                        }
                    }
                    if (!empty($cambios)) {
                        return "Modificó " . implode(', ', $cambios) . " de {$tipo}";
                    }
                }
                return "Modificó {$tipo}";
            case 'DELETE':
                return "Eliminó {$tipo}";
            default:
                return "Acción en {$tipo}";
        }
    }

    /**
     * Obtener historial de ediciones (compatible con estudios_auditoria)
     */
    public function obtenerHistorial($filtros = []) {
        try {
            $query = "SELECT e.id_auditoria, e.tabla_afectada, e.id_registro, e.accion, 
                            e.datos_anteriores, e.datos_nuevos, e.id_usuario, 
                            e.fecha_modificacion, e.ip_usuario, e.descripcion,
                            u.nombres, u.apellidos, u.usuario 
                     FROM " . $this->table_ediciones . " e
                     LEFT JOIN usuarios u ON e.id_usuario = u.id_usuario
                     WHERE 1=1";
            
            $params = [];
            
            // Filtrar por tabla
            if (!empty($filtros['tabla'])) {
                $query .= " AND e.tabla_afectada = :tabla";
                $params[':tabla'] = $filtros['tabla'];
            }
            
            // Filtrar por acción
            if (!empty($filtros['accion'])) {
                $query .= " AND e.accion = :accion";
                $params[':accion'] = $filtros['accion'];
            }
            
            // Ordenar por más reciente
            $query .= " ORDER BY e.fecha_modificacion DESC LIMIT 100";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }
}

