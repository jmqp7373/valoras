<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../services/EmailService.php';

class PasswordResetController {
    private $db;
    private $usuario;
    private $emailService;
    
    public function __construct() {
        $this->db = new Database();
        $this->usuario = new Usuario($this->db->getConnection());
        $this->emailService = new EmailService();
    }
    
    public function findUser($identifier, $method = 'cedula') {
        if(empty($identifier)) {
            $methodName = $this->getMethodName($method);
            return [
                'success' => false,
                'message' => "Debe ingresar $methodName"
            ];
        }
        
        try {
            $pdo = $this->db->getConnection();
            
            // Determinar el campo de búsqueda según el método
            switch($method) {
                case 'cedula':
                    $field = 'cedula'; // Confirmado que existe
                    break;
                case 'username':
                    $field = 'usuario'; // Verificar si existe este campo
                    break;
                case 'celular':
                    $field = 'celular'; // Verificar si existe este campo
                    // Para celular, limpiar el número (remover +, espacios, etc.)
                    $identifier = preg_replace('/[^\d]/', '', $identifier);
                    break;
                default:
                    $field = 'cedula';
            }
            
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE $field = ? LIMIT 1");
            $stmt->execute([$identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$user) {
                $methodName = $this->getMethodName($method);
                return [
                    'success' => false,
                    'message' => "No se encontró una cuenta asociada a $methodName"
                ];
            }
            
            // Validar email con criterios más estrictos
            $hasEmail = $this->isValidEmail($user['email']);
            $emailAlert = null;
            if(!empty($user['email']) && !$hasEmail) {
                $emailAlert = "⚠️ El email registrado ({$user['email']}) no es válido";
            }
            
            // Validar celular colombiano (10 dígitos)
            $hasPhone = $this->isValidColombianPhone($user['celular']);
            $phoneAlert = null;
            if(!empty($user['celular']) && !$hasPhone) {
                $phoneAlert = "⚠️ El celular registrado ({$user['codigo_pais']}{$user['celular']}) no es un número colombiano válido";
            }
            
            // Verificar si no hay métodos válidos
            if(!$hasEmail && !$hasPhone) {
                $alerts = [];
                if($emailAlert) $alerts[] = $emailAlert;
                if($phoneAlert) $alerts[] = $phoneAlert;
                
                if(empty($alerts)) {
                    $message = 'Esta cuenta no tiene métodos de contacto registrados. Contacta soporte técnico.';
                } else {
                    $message = 'Esta cuenta tiene problemas con sus datos de contacto:<br>' . implode('<br>', $alerts) . '<br><br>Contacta soporte técnico para corregir estos datos.';
                }
                
                return [
                    'success' => false,
                    'message' => $message
                ];
            }
            
            return [
                'success' => true,
                'masked_email' => $hasEmail ? $this->maskEmail($user['email']) : null,
                'masked_phone' => $hasPhone ? $this->maskPhone($user['codigo_pais'] . $user['celular']) : null,
                'email_alert' => $emailAlert,
                'phone_alert' => $phoneAlert,
                'user_data' => $user
            ];
            
        } catch(Exception $e) {
            // Temporalmente mostrar error detallado para debug
            return [
                'success' => false,
                'message' => 'ERROR: ' . $e->getMessage()
            ];
        }
    }
    
    public function sendResetLink($identifier, $method, $identificationMethod = 'cedula') {
        try {
            // Obtener datos del usuario nuevamente
            $result = $this->findUser($identifier, $identificationMethod);
            if(!$result['success']) {
                return $result;
            }
            
            $user = $result['user_data'];
            
            // Generar token único y seguro
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira en 1 hora
            
            // Guardar el token en la base de datos
            $pdo = $this->db->getConnection();
            
            // Crear tabla de tokens si no existe
            $this->createPasswordResetTable($pdo);
            
            // Insertar token
            $stmt = $pdo->prepare("
                INSERT INTO password_reset_tokens (cedula, token, method, expires_at, created_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                token = VALUES(token), 
                method = VALUES(method), 
                expires_at = VALUES(expires_at), 
                created_at = NOW()
            ");
            $stmt->execute([$cedula, $token, $method, $expires]);
            
            // Crear enlace de reseteo
            $resetLink = "http://localhost/valora.vip/views/login/reset_password.php?token=" . $token;
            
            // Enviar según el método seleccionado
            if($method === 'email') {
                // Envío real de email usando EmailService
                // Construir nombre completo de forma segura
                $nombres = isset($user['nombres']) && !empty($user['nombres']) ? $user['nombres'] : 'Usuario';
                $apellidos = isset($user['apellidos']) && !empty($user['apellidos']) ? $user['apellidos'] : '';
                $nombreCompleto = trim($nombres . ' ' . $apellidos);
                
                $emailResult = $this->emailService->sendPasswordResetEmail(
                    $user['email'],
                    $nombreCompleto,
                    $resetLink,
                    $user['cedula']
                );
                
                if($emailResult['success']) {
                    return [
                        'success' => true,
                        'message' => 'Se ha enviado un enlace de recuperación a tu correo electrónico.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al enviar el email: ' . $emailResult['message']
                    ];
                }
            } else {
                // SMS - mantener simulación por ahora (requiere integración con Twilio/etc.)
                $this->simulateSMSSend($user['codigo_pais'] . $user['celular'], $resetLink);
                return [
                    'success' => true,
                    'message' => 'Se ha enviado un enlace de recuperación a tu número de celular.'
                ];
            }
            
        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al enviar el enlace. Inténtalo de nuevo.'
            ];
        }
    }
    
    public function validateToken($token) {
        try {
            $pdo = $this->db->getConnection();
            $stmt = $pdo->prepare("
                SELECT cedula, method, expires_at 
                FROM password_reset_tokens 
                WHERE token = ? AND expires_at > NOW() AND used_at IS NULL
                LIMIT 1
            ");
            $stmt->execute([$token]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ];
            }
            
            return [
                'success' => true,
                'cedula' => $tokenData['cedula'],
                'method' => $tokenData['method']
            ];
            
        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al validar el token'
            ];
        }
    }
    
    public function resetPassword($token, $newPassword) {
        try {
            // Validar token
            $tokenResult = $this->validateToken($token);
            if(!$tokenResult['success']) {
                return $tokenResult;
            }
            
            $cedula = $tokenResult['cedula'];
            
            // Validar nueva contraseña
            if(strlen($newPassword) < 6) {
                return [
                    'success' => false,
                    'message' => 'La contraseña debe tener al menos 6 caracteres'
                ];
            }
            
            // Actualizar contraseña
            $pdo = $this->db->getConnection();
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE cedula = ?");
            $stmt->execute([$hashedPassword, $cedula]);
            
            // Marcar token como usado
            $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE token = ?");
            $stmt->execute([$token]);
            
            return [
                'success' => true,
                'message' => 'Tu contraseña ha sido actualizada exitosamente'
            ];
            
        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña. Inténtalo de nuevo.'
            ];
        }
    }
    
    // Validar que el email sea real y bien formado
    private function isValidEmail($email) {
        // Verificar que no esté vacío
        if(empty($email)) return false;
        
        // Validación básica de PHP
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        
        // Validaciones adicionales más estrictas
        // Verificar que tenga exactamente un @
        if(substr_count($email, '@') !== 1) return false;
        
        $parts = explode('@', $email);
        $localPart = $parts[0];
        $domain = $parts[1];
        
        // Validar parte local (antes del @)
        if(strlen($localPart) < 1 || strlen($localPart) > 64) return false;
        if(preg_match('/^\.|\.$|\.\./', $localPart)) return false; // No puede empezar/terminar con punto o tener puntos consecutivos
        
        // Validar dominio
        if(strlen($domain) < 4 || strlen($domain) > 255) return false;
        if(!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) return false;
        
        // Verificar que el dominio no sea solo números
        if(preg_match('/^\d+\.\d+\.\d+\.\d+$/', $domain)) return false;
        
        return true;
    }
    
    // Validar que el celular sea colombiano válido (10 dígitos)
    private function isValidColombianPhone($phone) {
        // Verificar que no esté vacío
        if(empty($phone)) return false;
        
        // Limpiar el número (solo números)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Debe tener exactamente 10 dígitos
        if(strlen($cleanPhone) !== 10) return false;
        
        // Debe empezar con 3 (celulares colombianos)
        if(!preg_match('/^3[0-9]{9}$/', $cleanPhone)) return false;
        
        // Validar rangos de operadores colombianos
        $validPrefixes = [
            // Claro
            '300', '301', '302', '303', '304', '305',
            // Movistar  
            '310', '311', '312', '313', '314', '315', '316', '317', '318', '319',
            // Tigo
            '320', '321', '322', '323', '324', '325',
            // Avantel
            '350', '351',
            // WOM
            '321', '322', '323', '324', '325',
            // Otros operadores
            '333', '334', '335', '336', '337', '338', '339'
        ];
        
        $prefix = substr($cleanPhone, 0, 3);
        return in_array($prefix, $validPrefixes);
    }
    
    private function maskEmail($email) {
        $parts = explode('@', $email);
        if(count($parts) !== 2) return $email;
        
        $username = $parts[0];
        $domain = $parts[1];
        $length = strlen($username);
        
        // Si el username es muy corto, mostrar todo con asteriscos
        if($length <= 7) {
            $maskedUsername = str_repeat('*', $length);
        } else {
            // Mostrar 1 al inicio, 5 asteriscos en el medio, y el resto al final
            $start = substr($username, 0, 1);
            $end = substr($username, -($length - 6)); // Los últimos caracteres después de quitar 1 del inicio y 5 del medio
            $maskedUsername = $start . '*****' . $end;
        }
        
        return $maskedUsername . '@' . $domain;
    }
    
    private function maskPhone($phone) {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        $length = strlen($cleanPhone);
        
        // Si el número es muy corto, mostrar todo con asteriscos
        if($length <= 7) {
            return str_repeat('*', $length);
        } else {
            // Mostrar 3 al inicio, 5 asteriscos en el medio, y 2 al final
            $start = substr($cleanPhone, 0, 3);
            $end = substr($cleanPhone, -2);
            return $start . '*****' . $end;
        }
    }
    
    private function createPasswordResetTable($pdo) {
        $sql = "
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cedula VARCHAR(20) NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            method ENUM('email', 'sms') NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            INDEX idx_cedula (cedula),
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($sql);
    }
    
    // Simulaciones para desarrollo - en producción reemplazar con APIs reales
    private function simulateEmailSend($email, $resetLink) {
        // Log para desarrollo
        error_log("EMAIL enviado a: $email");
        error_log("Enlace: $resetLink");
        
        // En producción integrar con:
        // - SendGrid: https://sendgrid.com/
        // - Mailgun: https://www.mailgun.com/
        // - Amazon SES: https://aws.amazon.com/ses/
        
        return true;
    }
    
    private function simulateSMSSend($phone, $resetLink) {
        // Log para desarrollo
        error_log("SMS enviado a: $phone");
        error_log("Enlace: $resetLink");
        
        // En producción integrar con:
        // - Twilio: https://www.twilio.com/
        // - Amazon SNS: https://aws.amazon.com/sns/
        // - Nexmo: https://www.vonage.com/
        
        return true;
    }
    
    private function getMethodName($method) {
        switch($method) {
            case 'cedula':
                return 'esta cédula';
            case 'username':
                return 'este nombre de usuario';
            case 'celular':
                return 'este número de celular';
            default:
                return 'esta información';
        }
    }
}
?>