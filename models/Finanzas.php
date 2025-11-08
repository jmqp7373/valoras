<?php
require_once __DIR__ . '/../config/database.php';

class Finanzas {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
        $this->crearTablaFinanzas();
    }
    
    /**
     * Crea la tabla finanzas si no existe
     */
    private function crearTablaFinanzas() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS finanzas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fecha DATE NOT NULL,
                tipo ENUM('Ingreso','Gasto') NOT NULL,
                categoria VARCHAR(100),
                monto DECIMAL(12,2) NOT NULL,
                descripcion TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_fecha (fecha),
                INDEX idx_tipo (tipo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log("Error al crear tabla finanzas: " . $e->getMessage());
        }
    }
    
    /**
     * Guarda un nuevo movimiento financiero
     * 
     * @param array $data Array con fecha, tipo, categoria, monto, descripcion
     * @return bool true si se guardó correctamente
     */
    public function guardarMovimiento($data) {
        try {
            $sql = "INSERT INTO finanzas (fecha, tipo, categoria, monto, descripcion) 
                    VALUES (:fecha, :tipo, :categoria, :monto, :descripcion)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':fecha' => $data['fecha'],
                ':tipo' => $data['tipo'],
                ':categoria' => $data['categoria'],
                ':monto' => $data['monto'],
                ':descripcion' => $data['descripcion']
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error al guardar movimiento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los movimientos ordenados por fecha descendente
     * Soporta filtros opcionales por fecha y categoría
     * 
     * @param array $filtros Array con fechaInicio, fechaFin, categoria
     * @return array Lista de movimientos
     */
    public function obtenerMovimientos($filtros = []) {
        try {
            $sql = "SELECT id, fecha, tipo, categoria, monto, descripcion, created_at 
                    FROM finanzas";
            
            $where = [];
            $params = [];
            
            // Filtro por fecha inicial
            if (!empty($filtros['fechaInicio'])) {
                $where[] = "fecha >= :fechaInicio";
                $params[':fechaInicio'] = $filtros['fechaInicio'];
            }
            
            // Filtro por fecha final
            if (!empty($filtros['fechaFin'])) {
                $where[] = "fecha <= :fechaFin";
                $params[':fechaFin'] = $filtros['fechaFin'];
            }
            
            // Filtro por categoría
            if (!empty($filtros['categoria']) && $filtros['categoria'] !== 'Todas') {
                $where[] = "categoria = :categoria";
                $params[':categoria'] = $filtros['categoria'];
            }
            
            // Agregar condiciones WHERE si existen filtros
            if (count($where) > 0) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            
            $sql .= " ORDER BY fecha DESC, created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener movimientos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcula totales, balance y última actualización
     * 
     * @return array Array con total_ingresos, total_gastos, balance, ultima_actualizacion
     */
    public function obtenerTotales() {
        try {
            // Obtener totales
            $sql = "SELECT 
                        SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) as total_ingresos,
                        SUM(CASE WHEN tipo = 'Gasto' THEN monto ELSE 0 END) as total_gastos,
                        MAX(fecha) as ultima_fecha
                    FROM finanzas";
            
            $stmt = $this->conn->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total_ingresos = floatval($result['total_ingresos'] ?? 0);
            $total_gastos = floatval($result['total_gastos'] ?? 0);
            $balance = $total_ingresos - $total_gastos;
            
            // Formatear fecha de última actualización
            $ultima_actualizacion = 'Sin movimientos';
            if ($result['ultima_fecha']) {
                $fecha = new DateTime($result['ultima_fecha']);
                $ultima_actualizacion = $fecha->format('d/m/Y');
            }
            
            return [
                'total_ingresos' => $total_ingresos,
                'total_gastos' => $total_gastos,
                'balance' => $balance,
                'ultima_actualizacion' => $ultima_actualizacion
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener totales: " . $e->getMessage());
            return [
                'total_ingresos' => 0,
                'total_gastos' => 0,
                'balance' => 0,
                'ultima_actualizacion' => 'Sin movimientos'
            ];
        }
    }
}
