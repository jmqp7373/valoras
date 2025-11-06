<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

class FirstPasswordController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Verificar código de verificación inicial
     */
    public function verifyFirstCode($cedula, $code) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM password_first_token 
                WHERE cedula = :cedula 
                AND token = :token 
                AND verified = 0 
                AND expires_at > NOW()
                LIMIT 1
            ");
            
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':token', $code);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar que el usuario existe
                $userStmt = $this->db->prepare("SELECT * FROM usuarios WHERE cedula = :cedula LIMIT 1");
                $userStmt->bindParam(':cedula', $cedula);
                $userStmt->execute();
                
                if($userStmt->rowCount() > 0) {
                    return [
                        'success' => true,
                        'message' => 'Código verificado correctamente',
                        'token_id' => $tokenData['id']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Usuario no encontrado'
                    ];
                }
            } else {
                // Verificar si el código ya fue usado
                $usedStmt = $this->db->prepare("
                    SELECT * FROM password_first_token 
                    WHERE cedula = :cedula 
                    AND token = :token 
                    AND verified = 1
                    LIMIT 1
                ");
                $usedStmt->bindParam(':cedula', $cedula);
                $usedStmt->bindParam(':token', $code);
                $usedStmt->execute();
                
                if($usedStmt->rowCount() > 0) {
                    return [
                        'success' => false,
                        'message' => 'Este código ya fue utilizado'
                    ];
                }
                
                // Verificar si el código expiró
                $expiredStmt = $this->db->prepare("
                    SELECT * FROM password_first_token 
                    WHERE cedula = :cedula 
                    AND token = :token 
                    AND expires_at <= NOW()
                    LIMIT 1
                ");
                $expiredStmt->bindParam(':cedula', $cedula);
                $expiredStmt->bindParam(':token', $code);
                $expiredStmt->execute();
                
                if($expiredStmt->rowCount() > 0) {
                    return [
                        'success' => false,
                        'message' => 'El código ha expirado. Solicita un nuevo código.'
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Código de verificación incorrecto'
                ];
            }
            
        } catch(Exception $e) {
            error_log("Error verificando código inicial: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al verificar el código'
            ];
        }
    }
    
    /**
     * Establecer la primera contraseña del usuario
     */
    public function setFirstPassword($cedula, $code, $newPassword) {
        try {
            // Primero verificar que el código sea válido
            $verifyResult = $this->verifyFirstCode($cedula, $code);
            
            if(!$verifyResult['success']) {
                return $verifyResult;
            }
            
            // Validar la contraseña
            if(strlen($newPassword) < 6) {
                return [
                    'success' => false,
                    'message' => 'La contraseña debe tener al menos 6 caracteres'
                ];
            }
            
            // Actualizar la contraseña del usuario
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $this->db->prepare("
                UPDATE usuarios 
                SET password = :password 
                WHERE cedula = :cedula
            ");
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':cedula', $cedula);
            
            if($updateStmt->execute()) {
                // Marcar el token como verificado
                $markVerifiedStmt = $this->db->prepare("
                    UPDATE password_first_token 
                    SET verified = 1, verified_at = NOW() 
                    WHERE cedula = :cedula AND token = :token
                ");
                $markVerifiedStmt->bindParam(':cedula', $cedula);
                $markVerifiedStmt->bindParam(':token', $code);
                $markVerifiedStmt->execute();
                
                // Limpiar variables de sesión de registro
                unset($_SESSION['last_registered_cedula']);
                unset($_SESSION['sms_sent_at']);
                unset($_SESSION['awaiting_first_password']);
                
                return [
                    'success' => true,
                    'message' => 'Contraseña creada exitosamente. Ya puedes iniciar sesión.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la contraseña'
                ];
            }
            
        } catch(Exception $e) {
            error_log("Error estableciendo primera contraseña: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al establecer la contraseña'
            ];
        }
    }
    
    /**
     * Obtener información del usuario por cédula
     */
    public function getUserInfo($cedula) {
        try {
            $stmt = $this->db->prepare("
                SELECT id_usuario, nombres, apellidos, cedula, celular, email 
                FROM usuarios 
                WHERE cedula = :cedula 
                LIMIT 1
            ");
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Enmascarar el celular
                $celular = $user['celular'];
                $maskedPhone = substr($celular, 0, 3) . '****' . substr($celular, -2);
                
                return [
                    'success' => true,
                    'user_data' => $user,
                    'masked_phone' => $maskedPhone
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }
            
        } catch(Exception $e) {
            error_log("Error obteniendo info de usuario: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener información del usuario'
            ];
        }
    }
}
?>
