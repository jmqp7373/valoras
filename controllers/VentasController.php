<?php
/**
 * VentasController
 * Controlador para gestionar las ventas/ingresos de los modelos
 */

// Suprimir warnings en respuestas AJAX
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

class VentasController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Insertar o actualizar venta usando UPSERT (ON DUPLICATE KEY UPDATE)
     * Evita duplicados garantizando una sola fila por período/credencial/modelo
     * 
     * @param array $data Datos de la venta
     * @return array ['inserted' => bool, 'updated' => bool]
     */
    private function upsertVenta($data) {
        try {
            // Usar INSERT ... ON DUPLICATE KEY UPDATE para UPSERT automático
            $sql = "
                INSERT INTO ventas_strip (
                    id_credencial,
                    id_usuario,
                    id_pagina,
                    period_start,
                    period_end,
                    total_earnings,
                    tips,
                    public_chat_tips,
                    private_chat_tips,
                    exclusive_privates,
                    tokens_earned,
                    created_at,
                    updated_at
                ) VALUES (
                    :id_credencial,
                    :id_usuario,
                    :id_pagina,
                    :period_start,
                    :period_end,
                    :total_earnings,
                    :tips,
                    :public_chat_tips,
                    :private_chat_tips,
                    :exclusive_privates,
                    :tokens_earned,
                    NOW(),
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    total_earnings = VALUES(total_earnings),
                    tips = VALUES(tips),
                    public_chat_tips = VALUES(public_chat_tips),
                    private_chat_tips = VALUES(private_chat_tips),
                    exclusive_privates = VALUES(exclusive_privates),
                    tokens_earned = VALUES(tokens_earned),
                    period_end = VALUES(period_end),
                    updated_at = NOW()
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_credencial' => $data['id_credencial'],
                ':id_usuario' => $data['id_usuario'],
                ':id_pagina' => $data['id_pagina'] ?? 3,
                ':period_start' => $data['period_start'],
                ':period_end' => $data['period_end'],
                ':total_earnings' => $data['total_earnings'],
                ':tips' => $data['tips'] ?? 0,
                ':public_chat_tips' => $data['public_chat_tips'] ?? 0,
                ':private_chat_tips' => $data['private_chat_tips'] ?? 0,
                ':exclusive_privates' => $data['exclusive_privates'] ?? 0,
                ':tokens_earned' => $data['tokens_earned'] ?? 0
            ]);
            
            // rowCount() retorna:
            // 1 = INSERT
            // 2 = UPDATE
            // 0 = Sin cambios
            $rowCount = $stmt->rowCount();
            
            return [
                'inserted' => $rowCount === 1,
                'updated' => $rowCount === 2,
                'unchanged' => $rowCount === 0
            ];
            
        } catch (PDOException $e) {
            error_log("Error en upsertVenta: " . $e->getMessage());
            return [
                'inserted' => false,
                'updated' => false,
                'unchanged' => false,
                'error' => $e->getMessage()
            ];
        }
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
    
    /**
     * Importar período actual (currentPayment) de Stripchat para todas las cuentas estudio activas
     * Usa UPSERT para evitar duplicados
     */
    public function importarPeriodoActual() {
        try {
            set_time_limit(300);
            
            $config = require __DIR__ . '/../config/configStripchat.php';
            
            // Obtener todas las cuentas estudio de Stripchat activas
            $stmtCuentas = $this->db->prepare("
                SELECT DISTINCT 
                    ce.id_cuenta_estudio,
                    ce.usuario_cuenta_estudio
                FROM cuentas_estudios ce
                WHERE ce.id_pagina = 3
                AND ce.estado = 1
                ORDER BY ce.usuario_cuenta_estudio
            ");
            $stmtCuentas->execute();
            $cuentas = $stmtCuentas->fetchAll(PDO::FETCH_ASSOC);
            
            $resultados = [];
            $totales = [
                'cuentas_procesadas' => 0,
                'modelos_procesados' => 0,
                'nuevos' => 0,
                'actualizados' => 0,
                'sin_cambios' => 0,
                'errores' => []
            ];
            
            foreach ($cuentas as $cuenta) {
                $resultado = $this->importarCuentaEstudioPeriodo($cuenta['id_cuenta_estudio']);
                $resultados[$cuenta['usuario_cuenta_estudio']] = $resultado;
                
                if ($resultado['success']) {
                    $totales['cuentas_procesadas']++;
                    $totales['modelos_procesados'] += $resultado['modelos_procesados'];
                    $totales['nuevos'] += $resultado['nuevos'];
                    $totales['actualizados'] += $resultado['actualizados'];
                    $totales['sin_cambios'] += $resultado['sin_cambios'] ?? 0;
                    $totales['errores'] = array_merge($totales['errores'], $resultado['errores']);
                }
            }
            
            return [
                'success' => true,
                'message' => "Período importado: {$totales['nuevos']} nuevos, {$totales['actualizados']} actualizados de {$totales['modelos_procesados']} modelos",
                'totales' => $totales,
                'resultados_por_cuenta' => $resultados
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Importar período actual de una cuenta estudio específica (método privado auxiliar)
     * Usa UPSERT para prevenir duplicados
     */
    private function importarCuentaEstudioPeriodo($id_cuenta_estudio) {
        try {
            $config = require __DIR__ . '/../config/configStripchat.php';
            
            // Obtener datos de la cuenta estudio
            $stmtCuenta = $this->db->prepare("
                SELECT 
                    ce.id_cuenta_estudio,
                    ce.usuario_cuenta_estudio
                FROM cuentas_estudios ce
                WHERE ce.id_cuenta_estudio = :id_cuenta_estudio
                AND ce.id_pagina = 3
                AND ce.estado = 1
            ");
            $stmtCuenta->execute([':id_cuenta_estudio' => $id_cuenta_estudio]);
            $cuentaEstudio = $stmtCuenta->fetch(PDO::FETCH_ASSOC);
            
            if (!$cuentaEstudio) {
                return [
                    'success' => false,
                    'message' => 'Cuenta estudio no encontrada',
                    'modelos_procesados' => 0,
                    'nuevos' => 0,
                    'actualizados' => 0,
                    'sin_cambios' => 0,
                    'errores' => []
                ];
            }
            
            // Buscar configuración de API
            $cuentaNormalizada = strtolower(str_replace(['_', ' ', '-'], '', $cuentaEstudio['usuario_cuenta_estudio']));
            $apiConfig = null;
            
            foreach ($config['cuentas'] as $key => $cuenta) {
                $keyNormalizado = strtolower(str_replace(['_', ' ', '-'], '', $key));
                if ($keyNormalizado === $cuentaNormalizada && $cuenta['activo']) {
                    $apiConfig = $cuenta;
                    break;
                }
            }
            
            if (!$apiConfig || empty($apiConfig['api_key'])) {
                return [
                    'success' => false,
                    'message' => 'API key no configurada',
                    'modelos_procesados' => 0,
                    'nuevos' => 0,
                    'actualizados' => 0,
                    'sin_cambios' => 0,
                    'errores' => []
                ];
            }
            
            // Obtener modelos activos (máximo 100)
            $stmtModelos = $this->db->prepare("
                SELECT DISTINCT
                    c.id_credencial,
                    c.usuario as model_username,
                    c.id_usuario
                FROM credenciales c
                LEFT JOIN (
                    SELECT id_credencial, MAX(updated_at) as ultima_actualizacion
                    FROM ventas_strip
                    GROUP BY id_credencial
                ) vs ON vs.id_credencial = c.id_credencial
                WHERE c.id_cuenta_estudio = :id_cuenta_estudio
                AND c.id_pagina = 3
                AND c.eliminado = 0
                AND (
                    vs.ultima_actualizacion >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                    OR vs.ultima_actualizacion IS NULL
                )
                ORDER BY c.id_credencial
                LIMIT 100
            ");
            $stmtModelos->execute([':id_cuenta_estudio' => $id_cuenta_estudio]);
            $modelos = $stmtModelos->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($modelos)) {
                return [
                    'success' => false,
                    'message' => 'No hay modelos activos',
                    'modelos_procesados' => 0,
                    'nuevos' => 0,
                    'actualizados' => 0,
                    'sin_cambios' => 0,
                    'errores' => []
                ];
            }
            
            $nuevos = 0;
            $actualizados = 0;
            $sinCambios = 0;
            $errores = [];
            
            $baseUrl = rtrim($config['base_url'], '/');
            $studioUsername = $apiConfig['studio_username'];
            $apiKey = $apiConfig['api_key'];
            
            foreach ($modelos as $modelo) {
                try {
                    // Llamar a la API con currentPayment
                    $url = "{$baseUrl}/username/{$studioUsername}/models/username/{$modelo['model_username']}?periodType=currentPayment";
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "API-Key: {$apiKey}",
                        'Accept: application/json'
                    ]);
                    
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode !== 200) {
                        $errores[] = "{$modelo['model_username']}: HTTP {$httpCode}";
                        continue;
                    }
                    
                    $data = json_decode($response, true);
                    
                    if (!$data || !isset($data['totalEarnings'])) {
                        continue;
                    }
                    
                    // Extraer datos del período
                    $periodStart = isset($data['periodStart']) ? date('Y-m-d H:i:s', strtotime($data['periodStart'])) : date('Y-m-d H:i:s');
                    $periodEnd = isset($data['periodEnd']) ? date('Y-m-d H:i:s', strtotime($data['periodEnd'])) : date('Y-m-d H:i:s');
                    
                    // Usar UPSERT para insertar o actualizar
                    $result = $this->upsertVenta([
                        'id_credencial' => $modelo['id_credencial'],
                        'id_usuario' => $modelo['id_usuario'],
                        'id_pagina' => 3,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'total_earnings' => $data['totalEarnings'] ?? 0,
                        'tips' => $data['tips'] ?? 0,
                        'public_chat_tips' => $data['publicChatTips'] ?? 0,
                        'private_chat_tips' => $data['privateChatTips'] ?? 0,
                        'exclusive_privates' => $data['exclusivePrivates'] ?? 0,
                        'tokens_earned' => $data['tips'] ?? 0
                    ]);
                    
                    if ($result['inserted']) {
                        $nuevos++;
                    } elseif ($result['updated']) {
                        $actualizados++;
                    } elseif ($result['unchanged']) {
                        $sinCambios++;
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "{$modelo['model_username']}: {$e->getMessage()}";
                }
            }
            
            return [
                'success' => true,
                'modelos_procesados' => count($modelos),
                'nuevos' => $nuevos,
                'actualizados' => $actualizados,
                'sin_cambios' => $sinCambios,
                'errores' => array_slice($errores, 0, 5)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'modelos_procesados' => 0,
                'nuevos' => 0,
                'actualizados' => 0,
                'sin_cambios' => 0,
                'errores' => []
            ];
        }
    }
    
    /**
     * Calcular ventas diarias reconstruidas a partir de totales acumulados
     * Retorna ventas diarias por cuenta estudio usando diferencias de totales
     */
    public function calcularVentasDiarias() {
        try {
            // Obtener todas las cuentas estudio de Stripchat
            $stmtCuentas = $this->db->prepare("
                SELECT DISTINCT 
                    ce.id_cuenta_estudio,
                    ce.usuario_cuenta_estudio
                FROM cuentas_estudios ce
                WHERE ce.id_pagina = 3
                AND ce.estado = 1
                ORDER BY ce.usuario_cuenta_estudio
            ");
            $stmtCuentas->execute();
            $cuentas = $stmtCuentas->fetchAll(PDO::FETCH_ASSOC);
            
            $resultado = [];
            $periodo_global = null;
            
            foreach ($cuentas as $cuenta) {
                // Obtener el período más reciente
                $stmtPeriodo = $this->db->prepare("
                    SELECT 
                        MIN(vs.period_start) as periodo_inicio,
                        MAX(vs.period_end) as periodo_fin,
                        SUM(vs.total_earnings) as total_acumulado
                    FROM ventas_strip vs
                    INNER JOIN credenciales c ON c.id_credencial = vs.id_credencial
                    WHERE c.id_cuenta_estudio = :id_cuenta_estudio
                    AND c.id_pagina = 3
                    AND vs.period_start >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ");
                $stmtPeriodo->execute([':id_cuenta_estudio' => $cuenta['id_cuenta_estudio']]);
                $periodo = $stmtPeriodo->fetch(PDO::FETCH_ASSOC);
                
                if (!$periodo_global || ($periodo['periodo_inicio'] && $periodo['periodo_inicio'] < $periodo_global['inicio'])) {
                    $periodo_global = [
                        'inicio' => $periodo['periodo_inicio'],
                        'fin' => $periodo['periodo_fin']
                    ];
                }
                
                // Obtener snapshots por modelo ordenados cronológicamente
                $stmtVentas = $this->db->prepare("
                    SELECT 
                        vs.id_credencial,
                        DATE(vs.updated_at) as fecha_snapshot,
                        vs.total_earnings,
                        vs.updated_at
                    FROM ventas_strip vs
                    INNER JOIN credenciales c ON c.id_credencial = vs.id_credencial
                    WHERE c.id_cuenta_estudio = :id_cuenta_estudio
                    AND c.id_pagina = 3
                    AND vs.period_start >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ORDER BY vs.id_credencial, vs.updated_at ASC
                ");
                $stmtVentas->execute([':id_cuenta_estudio' => $cuenta['id_cuenta_estudio']]);
                $snapshots = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);
                
                // Calcular diferencias diarias
                $ventasDiarias = [];
                $modeloAnterior = null;
                $totalAnterior = 0;
                
                foreach ($snapshots as $snapshot) {
                    if ($modeloAnterior !== $snapshot['id_credencial']) {
                        // Nuevo modelo - resetear
                        $totalAnterior = 0;
                        $modeloAnterior = $snapshot['id_credencial'];
                    }
                    
                    $fecha = $snapshot['fecha_snapshot'];
                    $diferencia = $snapshot['total_earnings'] - $totalAnterior;
                    
                    if (!isset($ventasDiarias[$fecha])) {
                        $ventasDiarias[$fecha] = 0;
                    }
                    
                    $ventasDiarias[$fecha] += $diferencia;
                    $totalAnterior = $snapshot['total_earnings'];
                }
                
                $resultado[$cuenta['usuario_cuenta_estudio']] = [
                    'periodo' => [
                        'inicio' => $periodo['periodo_inicio'],
                        'fin' => $periodo['periodo_fin']
                    ],
                    'total_acumulado' => floatval($periodo['total_acumulado'] ?? 0),
                    'ventas_diarias' => $ventasDiarias
                ];
            }
            
            return [
                'success' => true,
                'periodo_global' => $periodo_global,
                'data' => $resultado
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Importar ventas de Stripchat para un rango de fechas completo
     * Recorre todas las cuentas estudio y todos los días del rango
     */
    public function importarStripchatRango($fecha_desde, $fecha_hasta) {
        try {
            $total_importados = 0;
            $errores = [];
            
            // Obtener todas las cuentas estudio de Stripchat activas
            $stmt = $this->db->prepare("
                SELECT DISTINCT 
                    c.id_credencial,
                    c.usuario as model_username,
                    c.id_usuario,
                    ce.id_cuenta_estudio
                FROM credenciales c
                INNER JOIN cuentas_estudios ce ON ce.id_cuenta_estudio = c.id_cuenta_estudio
                WHERE ce.id_pagina = 3 
                  AND ce.estado = 1
                  AND c.eliminado = 0
            ");
            $stmt->execute();
            $credenciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($credenciales)) {
                return [
                    'success' => false,
                    'message' => 'No hay cuentas de Stripchat configuradas',
                    'total_importados' => 0
                ];
            }
            
            // Generar array de días
            $current = new DateTime($fecha_desde);
            $end = new DateTime($fecha_hasta);
            
            while ($current <= $end) {
                $fecha = $current->format('Y-m-d');
                
                foreach ($credenciales as $cred) {
                    $resultado = $this->importarStripchatCuentaDia($fecha, $cred['id_credencial']);
                    if ($resultado['success'] && $resultado['registros_insertados'] > 0) {
                        $total_importados += $resultado['registros_insertados'];
                    } elseif (!$resultado['success']) {
                        $errores[] = $resultado['message'];
                    }
                }
                
                $current->modify('+1 day');
            }
            
            return [
                'success' => true,
                'total_importados' => $total_importados,
                'errores' => count($errores),
                'mensaje_errores' => implode('; ', array_slice($errores, 0, 5))
            ];
            
        } catch (Exception $e) {
            error_log("Error en importarStripchatRango: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'total_importados' => 0
            ];
        }
    }
    
    /**
     * Importar ventas de una cuenta estudio específica en un día específico
     */
    /**
     * Importar ventas de Stripchat para una cuenta estudio específica
     * Recorre TODAS las credenciales (modelos) de esa cuenta estudio
     * Importa el período de pago actual (currentPayment) de Stripchat
     * @param string $fecha Fecha (ignorada - la API solo soporta períodos, no fechas específicas)
     * @param int $id_cuenta_estudio ID de la cuenta estudio
     * @return array Resultado de la importación
     */
    public function importarStripchatCuentaDia($fecha, $id_cuenta_estudio) {
        try {
            // Obtener información de la cuenta estudio
            $stmt = $this->db->prepare("
                SELECT 
                    ce.id_cuenta_estudio,
                    ce.usuario_cuenta_estudio
                FROM cuentas_estudios ce
                WHERE ce.id_cuenta_estudio = :id_cuenta_estudio
                  AND ce.id_pagina = 3
                  AND ce.estado = 1
            ");
            $stmt->execute(['id_cuenta_estudio' => $id_cuenta_estudio]);
            $cuentaEstudio = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cuentaEstudio) {
                return [
                    'success' => false,
                    'message' => 'Cuenta estudio no encontrada o inactiva',
                    'registros_insertados' => 0
                ];
            }
            
            // Cargar configuración de Stripchat
            $configPath = __DIR__ . '/../config/configStripchat.php';
            if (!file_exists($configPath)) {
                return [
                    'success' => false,
                    'message' => 'Archivo de configuración de Stripchat no encontrado',
                    'registros_insertados' => 0
                ];
            }
            
            $config = require $configPath;
            
            // Buscar la configuración de esta cuenta estudio
            $apiKey = null;
            $studioUsername = null;
            
            // Mapeo manual de cuentas estudio a configuraciones
            // Esto es temporal hasta que se implementen los campos en la BD
            $nombreCuenta = strtolower(trim($cuentaEstudio['usuario_cuenta_estudio']));
            
            // Si el config tiene estructura antigua (compatibilidad)
            if (isset($config['api_key']) && isset($config['studio_username'])) {
                $apiKey = $config['api_key'];
                $studioUsername = $config['studio_username'];
            } 
            // Si tiene estructura nueva con múltiples cuentas
            elseif (isset($config['cuentas'])) {
                foreach ($config['cuentas'] as $key => $cuenta) {
                    if (strtolower($key) === $nombreCuenta || 
                        strtolower($cuenta['studio_username']) === $nombreCuenta) {
                        if (!empty($cuenta['activo'])) {
                            $apiKey = $cuenta['api_key'];
                            $studioUsername = $cuenta['studio_username'];
                            break;
                        }
                    }
                }
            }
            
            if (!$apiKey || !$studioUsername) {
                return [
                    'success' => false,
                    'message' => "No se encontró configuración de API para la cuenta '{$cuentaEstudio['usuario_cuenta_estudio']}'. Verifica configStripchat.php",
                    'registros_insertados' => 0
                ];
            }
            
            $baseUrl = rtrim($config['base_url'], '/');
            
            // Aumentar tiempo de ejecución para cuentas con muchos modelos
            set_time_limit(300); // 5 minutos máximo
            
            // Obtener credenciales activas de esta cuenta estudio
            // OPTIMIZACIÓN: Solo traer modelos que tienen ventas recientes
            $stmt = $this->db->prepare("
                SELECT DISTINCT
                    c.id_credencial,
                    c.usuario as model_username,
                    c.id_usuario,
                    ce.id_cuenta_estudio,
                    ce.usuario_cuenta_estudio,
                    MAX(vs.period_start) as ultima_venta
                FROM credenciales c
                INNER JOIN cuentas_estudios ce ON ce.id_cuenta_estudio = c.id_cuenta_estudio
                LEFT JOIN ventas_strip vs ON vs.id_credencial = c.id_credencial
                WHERE c.id_cuenta_estudio = :id_cuenta_estudio
                  AND c.eliminado = 0
                  AND ce.estado = 1
                GROUP BY c.id_credencial, c.usuario, c.id_usuario, ce.id_cuenta_estudio, ce.usuario_cuenta_estudio
                HAVING ultima_venta IS NULL OR ultima_venta >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                ORDER BY ultima_venta DESC
                LIMIT 100
            ");
            $stmt->execute(['id_cuenta_estudio' => $id_cuenta_estudio]);
            $credenciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($credenciales)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron credenciales activas para esta cuenta estudio',
                    'registros_insertados' => 0
                ];
            }
            
            $total_insertados = 0;
            $total_actualizados = 0;
            $errores = [];
            
            // Log de inicio
            error_log("Iniciando importación para {$cuentaEstudio['usuario_cuenta_estudio']}: " . count($credenciales) . " modelos");
            
            // Recorrer cada credencial (modelo) de la cuenta
            foreach ($credenciales as $index => $credencial) {
                $modelUsername = $credencial['model_username'];
                
                // Log cada 10 modelos
                if ($index % 10 === 0) {
                    error_log("Procesando modelo {$index}/" . count($credenciales));
                }
                
                // Construir URL de la API con filtro de fecha según documentación oficial
                // Formato: /api/stats/v2/studios/username/{studioUsername}/models/username/{modelUsername}
                $url = "{$baseUrl}/username/{$studioUsername}/models/username/{$modelUsername}";
                $url .= "?periodType=currentPayment";
                
                // Llamar a la API
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => $config['timeout'] ?? 30,
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
                
                // Validar respuesta
                if ($httpCode !== 200 || !$response) {
                    $errores[] = "Modelo {$modelUsername}: " . ($curlError ?: "HTTP {$httpCode}");
                    continue; // Continuar con el siguiente modelo
                }
                
                $data = json_decode($response, true);
                
                if (!$data || !isset($data['totalEarnings'])) {
                    $errores[] = "Modelo {$modelUsername}: Respuesta inválida";
                    continue;
                }
                
                $totalEarnings = floatval($data['totalEarnings']);
                
                // Si no hay earnings, continuar con el siguiente
                if ($totalEarnings <= 0) {
                    continue;
                }
                
                // La API de Stripchat devuelve periodStart y periodEnd
                $periodStart = isset($data['periodStart']) ? date('Y-m-d H:i:s', strtotime($data['periodStart'])) : date('Y-m-d H:i:s');
                $periodEnd = isset($data['periodEnd']) ? date('Y-m-d H:i:s', strtotime($data['periodEnd'])) : date('Y-m-d H:i:s');
                
                // Verificar si ya existe este registro para este período
                $stmtCheck = $this->db->prepare("
                    SELECT id FROM ventas_strip
                    WHERE id_credencial = :id_credencial
                      AND period_start = :period_start
                      AND period_end = :period_end
                ");
                $stmtCheck->execute([
                    'id_credencial' => $credencial['id_credencial'],
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd
                ]);
                $existe = $stmtCheck->fetch();
                
                if ($existe) {
                    // Actualizar registro existente
                    $stmtUpdate = $this->db->prepare("
                        UPDATE ventas_strip
                        SET total_earnings = :total_earnings,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        'total_earnings' => $totalEarnings,
                        'id' => $existe['id']
                    ]);
                    $total_actualizados++;
                } else {
                    // Insertar nuevo registro
                    $stmtInsert = $this->db->prepare("
                        INSERT INTO ventas_strip 
                        (id_usuario, id_credencial, id_pagina, period_start, period_end, total_earnings)
                        VALUES (:id_usuario, :id_credencial, 3, :period_start, :period_end, :total_earnings)
                    ");
                    $stmtInsert->execute([
                        'id_usuario' => $credencial['id_usuario'],
                        'id_credencial' => $credencial['id_credencial'],
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'total_earnings' => $totalEarnings
                    ]);
                    $total_insertados++;
                }
            } // Fin del foreach de credenciales
            
            // Preparar mensaje final
            $mensaje = ($total_insertados + $total_actualizados) > 0 
                ? "Importados: {$total_insertados} nuevos, {$total_actualizados} actualizados (de " . count($credenciales) . " modelos procesados) - Período actual de pago"
                : "No se encontraron ventas para esta cuenta en el período actual";
            
            if (!empty($errores) && count($errores) <= 5) {
                $mensaje .= ". Errores: " . implode(', ', array_slice($errores, 0, 5));
            } elseif (count($errores) > 5) {
                $mensaje .= ". " . count($errores) . " errores encontrados";
            }
            
            return [
                'success' => ($total_insertados + $total_actualizados) > 0,
                'message' => $mensaje,
                'registros_insertados' => $total_insertados + $total_actualizados
            ];
            
        } catch (Exception $e) {
            error_log("Error en importarStripchatCuentaDia: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'registros_insertados' => 0
            ];
        }
    }
}

// Handler para peticiones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        require_once __DIR__ . '/../config/database.php';
        $db = getDBConnection();
        $controller = new VentasController($db);
        
        switch ($_GET['action']) {
            case 'importarPeriodoActual':
                $resultado = $controller->importarPeriodoActual();
                echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'calcularVentasDiarias':
                $resultado = $controller->calcularVentasDiarias();
                echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'importarDesdeAPI':
                $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
                
                if ($usuario_id <= 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'ID de usuario inválido',
                        'registros' => 0
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $resultado = $controller->importarDesdeAPI($usuario_id);
                echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'importarStripchatRango':
                $fecha_desde = $_POST['fecha_desde'] ?? null;
                $fecha_hasta = $_POST['fecha_hasta'] ?? null;
                
                if (!$fecha_desde || !$fecha_hasta) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Fechas requeridas',
                        'total_importados' => 0
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $resultado = $controller->importarStripchatRango($fecha_desde, $fecha_hasta);
                echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
                break;
                
            case 'importarStripchatCuentaDia':
                $fecha = $_POST['fecha'] ?? null;
                $id_cuenta_estudio = $_POST['id_cuenta_estudio'] ?? null;
                
                if (!$fecha || !$id_cuenta_estudio) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Fecha y cuenta estudio requeridas',
                        'registros_insertados' => 0
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $resultado = $controller->importarStripchatCuentaDia($fecha, $id_cuenta_estudio);
                echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Acción no reconocida'
                ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error del servidor: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}
?>
