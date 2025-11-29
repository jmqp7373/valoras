<?php
/**
 * Modelo: Afiliados
 * 
 * Maneja las consultas para modelos, líderes y referentes
 */

class Afiliados {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todos los modelos activos (nivel_orden = 0)
     */
    public function obtenerModelos() {
        try {
            $query = "
                SELECT 
                    u.id_usuario,
                    u.usuario,
                    u.nombres,
                    u.apellidos,
                    u.celular,
                    u.email,
                    u.fecha_creacion,
                    u.id_estudio,
                    u.cedula,
                    u.codigo_pais,
                    u.fecha_nacimiento,
                    u.ciudad,
                    u.direccion,
                    u.dias_descanso,
                    u.progreso_perfil,
                    e.nombre_estudio AS estudio_nombre,
                    ec.nombre_estudio_casa AS casa_nombre,
                    ui.disponibilidad,
                    ui.tipo_sangre,
                    ui.alergias,
                    ui.contacto_emergencia_nombre,
                    ui.contacto_emergencia_parentesco,
                    ui.contacto_emergencia_telefono,
                    ui.ref1_nombre,
                    ui.ref1_parentesco,
                    ui.ref1_celular,
                    ui.banco_nombre,
                    ui.banco_tipo_cuenta,
                    ui.banco_numero_cuenta,
                    ui.url_entrevista,
                    ui.notas
                FROM usuarios u
                LEFT JOIN estudios e ON u.id_estudio = e.id_estudio
                LEFT JOIN estudios_casas ec ON e.id_estudio_casa = ec.id_estudio_casa
                LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
                WHERE u.nivel_orden = 0 
                AND u.estado = 1
                ORDER BY u.fecha_creacion DESC
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerModelos: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener todos los líderes activos (nivel_orden != 0)
     */
    public function obtenerLideres() {
        try {
            $query = "
                SELECT 
                    u.id_usuario,
                    u.usuario,
                    u.nombres,
                    u.apellidos,
                    u.celular,
                    u.email,
                    u.nivel_orden,
                    u.fecha_creacion,
                    u.id_estudio,
                    u.cedula,
                    u.codigo_pais,
                    u.fecha_nacimiento,
                    u.ciudad,
                    u.direccion,
                    u.dias_descanso,
                    u.progreso_perfil,
                    e.nombre_estudio AS estudio_nombre,
                    ec.nombre_estudio_casa AS casa_nombre,
                    ui.disponibilidad,
                    ui.tipo_sangre,
                    ui.alergias,
                    ui.contacto_emergencia_nombre,
                    ui.contacto_emergencia_parentesco,
                    ui.contacto_emergencia_telefono,
                    ui.ref1_nombre,
                    ui.ref1_parentesco,
                    ui.ref1_celular,
                    ui.banco_nombre,
                    ui.banco_tipo_cuenta,
                    ui.banco_numero_cuenta,
                    ui.url_entrevista,
                    ui.notas
                FROM usuarios u
                LEFT JOIN estudios e ON u.id_estudio = e.id_estudio
                LEFT JOIN estudios_casas ec ON e.id_estudio_casa = ec.id_estudio_casa
                LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
                WHERE u.nivel_orden != 0 
                AND u.estado = 1
                ORDER BY u.nivel_orden ASC, u.fecha_creacion DESC
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerLideres: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener todos los usuarios que son referentes de otros
     */
    public function obtenerReferentes() {
        try {
            $query = "
                SELECT 
                    u.id_usuario,
                    u.usuario,
                    u.nombres,
                    u.apellidos,
                    u.celular,
                    u.email,
                    u.nivel_orden,
                    u.estado,
                    COUNT(ref.id_usuario) as total_referidos
                FROM usuarios u
                INNER JOIN usuarios ref ON u.id_usuario = ref.id_referente
                GROUP BY u.id_usuario, u.usuario, u.nombres, u.apellidos, 
                         u.celular, u.email, u.nivel_orden, u.estado
                ORDER BY total_referidos DESC, u.nombres ASC
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerReferentes: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
