<?php
/**
 * VentasController
 * Controlador para gestionar las ventas/ingresos de los modelos
 */

class VentasController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtener todas las ventas de un usuario específico
     * @param int $usuario_id ID del usuario/modelo
     * @return array Array de ventas con información de plataforma
     */
    public function getVentasByUsuario($usuario_id) {
        try {
            $sql = "SELECT 
                        v.id,
                        v.usuario_id,
                        v.credencial_id,
                        v.plataforma_id,
                        v.period_start,
                        v.period_end,
                        v.total_earnings,
                        v.created_at,
                        u.nombres,
                        u.apellidos,
                        c.usuario as credencial_usuario,
                        c.email_de_registro as credencial_email
                    FROM ventas v
                    INNER JOIN usuarios u ON v.usuario_id = u.id_usuario
                    INNER JOIN credenciales c ON v.credencial_id = c.id_credencial
                    WHERE v.usuario_id = :usuario_id
                    ORDER BY v.period_start DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getVentasByUsuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener información del usuario/modelo
     * @param int $usuario_id ID del usuario
     * @return array|null Información del usuario
     */
    public function getUsuarioInfo($usuario_id) {
        try {
            $sql = "SELECT id_usuario, nombres, apellidos, cedula, email 
                    FROM usuarios 
                    WHERE id_usuario = :usuario_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getUsuarioInfo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener lista de todos los usuarios/modelos para el select
     * @return array Array de usuarios
     */
    public function getAllUsuarios() {
        try {
            $sql = "SELECT id_usuario, nombres, apellidos, cedula 
                    FROM usuarios 
                    ORDER BY nombres, apellidos";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllUsuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener total de ventas de un usuario
     * @param int $usuario_id ID del usuario
     * @return float Total de ventas
     */
    public function getTotalVentasUsuario($usuario_id) {
        try {
            $sql = "SELECT SUM(total_earnings) as total 
                    FROM ventas 
                    WHERE usuario_id = :usuario_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0.00;
            
        } catch (PDOException $e) {
            error_log("Error en getTotalVentasUsuario: " . $e->getMessage());
            return 0.00;
        }
    }
}
?>
