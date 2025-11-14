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
                        v.id_usuario,
                        v.id_credencial,
                        v.id_pagina,
                        v.period_start,
                        v.period_end,
                        v.total_earnings,
                        v.created_at,
                        u.nombres,
                        u.apellidos,
                        c.usuario as credencial_usuario,
                        c.email_de_registro as credencial_email
                    FROM ventas_strip v
                    INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                    INNER JOIN credenciales c ON v.id_credencial = c.id_credencial
                    WHERE v.id_usuario = :usuario_id
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
                    FROM ventas_strip 
                    WHERE id_usuario = :usuario_id";
            
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
    
    /**
     * Importar datos de ventas desde la API de Stripchat
     * @param int $usuario_id ID del usuario/modelo
     * @return array Resultado de la importación con success, message y registros
     */
    public function importarDesdeAPI($usuario_id) {
        try {
            // 1️⃣ Cargar configuración de Stripchat
            $config = require __DIR__ . '/../config/configStripchat.php';
            $apiKey = $config['api_key'];
            $studioUsername = $config['studio_username'];
            $baseUrl = rtrim($config['base_url'], '/');
            
            // Verificar que el usuario existe
            $usuarioInfo = $this->getUsuarioInfo($usuario_id);
            if (!$usuarioInfo) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'registros' => 0
                ];
            }
            
            // 2️⃣ Obtener credencial del modelo (username en Stripchat)
            // id_pagina = 3 corresponde a Stripchat
            $stmt = $this->db->prepare("SELECT id_credencial, usuario AS model_username FROM credenciales WHERE id_usuario = ? AND id_pagina = 3 LIMIT 1");
            $stmt->execute([$usuario_id]);
            $credencial = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$credencial) {
                return [
                    'success' => false,
                    'message' => 'El modelo no tiene credenciales de Stripchat registradas.',
                    'registros' => 0
                ];
            }
            
            $modelUsername = $credencial['model_username'];
            $credencial_id = $credencial['id_credencial'];
            
            // 3️⃣ Construir URL completa de la API
            $url = "{$baseUrl}{$studioUsername}/models/username/{$modelUsername}?periodType=currentPayment";
            
            // 4️⃣ Inicializar solicitud cURL
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $config['timeout'],
                CURLOPT_HTTPHEADER => [
                    "API-Key: {$apiKey}",
                    "Accept: application/json"
                ],
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Verificar respuesta HTTP
            if ($httpCode !== 200 || !$response) {
                $errorMsg = $curlError ? $curlError : "HTTP {$httpCode}";
                return [
                    'success' => false,
                    'message' => "Error al comunicarse con la API de Stripchat: {$errorMsg}",
                    'registros' => 0
                ];
            }
            
            // 5️⃣ Decodificar respuesta JSON
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['totalEarnings'])) {
                return [
                    'success' => false,
                    'message' => 'Respuesta inválida o sin campo totalEarnings',
                    'registros' => 0
                ];
            }
            
            // 6️⃣ Insertar registro en la tabla ventas
            $totalEarnings = floatval($data['totalEarnings']);
            
            // Obtener fechas del periodo (si están disponibles en la respuesta)
            $periodStart = isset($data['periodStart']) ? $data['periodStart'] : date('Y-m-d H:i:s');
            $periodEnd = isset($data['periodEnd']) ? $data['periodEnd'] : date('Y-m-d H:i:s');
            
            $inserted = $this->insertarVenta(
                $usuario_id,
                $credencial_id,
                $periodStart,
                $periodEnd,
                $totalEarnings
            );
            
            if ($inserted) {
                return [
                    'success' => true,
                    'registros' => 1,
                    'message' => "Datos importados correctamente para {$modelUsername}. Total: $" . number_format($totalEarnings, 2)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo insertar el registro en la base de datos',
                    'registros' => 0
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en importarDesdeAPI: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Excepción: ' . $e->getMessage(),
                'registros' => 0
            ];
        }
    }
    
    /**
     * Obtener credencial de Stripchat del usuario
     * @param int $usuario_id ID del usuario
     * @return array|null Credencial de Stripchat
     */
    private function getCredencialStripchat($usuario_id) {
        try {
            // Buscar credencial de Stripchat (ajustar id_pagina según tu BD)
            $sql = "SELECT id_credencial, usuario, email_de_registro 
                    FROM credenciales 
                    WHERE id_usuario = :usuario_id 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getCredencialStripchat: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Insertar una venta en la base de datos
     * @param int $usuario_id ID del usuario
     * @param int $credencial_id ID de la credencial
     * @param string $period_start Fecha de inicio
     * @param string $period_end Fecha de fin
     * @param float $total_earnings Total ganado
     * @return bool True si se insertó correctamente
     */
    private function insertarVenta($usuario_id, $credencial_id, $period_start, $period_end, $total_earnings) {
        try {
            // Verificar si ya existe el registro para evitar duplicados
            $sqlCheck = "SELECT COUNT(*) as count FROM ventas_strip 
                         WHERE id_usuario = :usuario_id 
                         AND id_credencial = :credencial_id 
                         AND period_start = :period_start 
                         AND period_end = :period_end";
            
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([
                ':usuario_id' => $usuario_id,
                ':credencial_id' => $credencial_id,
                ':period_start' => $period_start,
                ':period_end' => $period_end
            ]);
            
            $exists = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if ($exists['count'] > 0) {
                // Ya existe, actualizar en lugar de insertar
                $sqlUpdate = "UPDATE ventas_strip 
                             SET total_earnings = :total_earnings,
                                 updated_at = CURRENT_TIMESTAMP
                             WHERE id_usuario = :usuario_id 
                             AND id_credencial = :credencial_id 
                             AND period_start = :period_start 
                             AND period_end = :period_end";
                
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                return $stmtUpdate->execute([
                    ':total_earnings' => $total_earnings,
                    ':usuario_id' => $usuario_id,
                    ':credencial_id' => $credencial_id,
                    ':period_start' => $period_start,
                    ':period_end' => $period_end
                ]);
            }
            
            // Insertar nuevo registro
            $sql = "INSERT INTO ventas_strip (id_usuario, id_credencial, period_start, period_end, total_earnings) 
                    VALUES (:usuario_id, :credencial_id, :period_start, :period_end, :total_earnings)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':credencial_id' => $credencial_id,
                ':period_start' => $period_start,
                ':period_end' => $period_end,
                ':total_earnings' => $total_earnings
            ]);
            
        } catch (PDOException $e) {
            error_log("Error en insertarVenta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Simular datos de la API de Stripchat (reemplazar con llamada real)
     * @param int $usuario_id ID del usuario
     * @param int $credencial_id ID de la credencial
     * @return array Datos simulados de ventas
     */
    private function simularDatosStripchat($usuario_id, $credencial_id) {
        // TODO: Reemplazar con llamada real a la API de Stripchat
        // Por ahora retornamos datos de prueba
        
        $mesActual = date('Y-m');
        $mesAnterior = date('Y-m', strtotime('-1 month'));
        
        return [
            [
                'period_start' => $mesAnterior . '-01 00:00:00',
                'period_end' => $mesAnterior . '-' . date('t', strtotime($mesAnterior . '-01')) . ' 23:59:59',
                'total_earnings' => rand(1000, 3000) + (rand(0, 99) / 100)
            ],
            [
                'period_start' => $mesActual . '-01 00:00:00',
                'period_end' => $mesActual . '-' . date('t', strtotime($mesActual . '-01')) . ' 23:59:59',
                'total_earnings' => rand(1500, 4000) + (rand(0, 99) / 100)
            ]
        ];
    }
}

// Handler para peticiones AJAX
if (isset($_GET['action']) && $_GET['action'] === 'importarDesdeAPI') {
    header('Content-Type: application/json');
    
    try {
        require_once __DIR__ . '/../config/database.php';
        
        $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
        
        if ($usuario_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario inválido',
                'registros' => 0
            ]);
            exit;
        }
        
        $db = getDBConnection();
        $controller = new VentasController($db);
        $resultado = $controller->importarDesdeAPI($usuario_id);
        
        echo json_encode($resultado);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error del servidor: ' . $e->getMessage(),
            'registros' => 0
        ]);
    }
    exit;
}
?>
