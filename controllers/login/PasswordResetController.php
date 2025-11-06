<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../services/EmailService.php';
require_once __DIR__ . '/../TwilioController.php';

class PasswordResetController {
    private $db;
    private $usuario;
    private $emailService;
    private $twilioController;
    
    public function __construct() {
        $this->db = new Database();
        $this->usuario = new Usuario($this->db->getConnection());
        $this->emailService = new EmailService();
        $this->twilioController = new TwilioController();
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
    
    public function sendResetCode($identifier, $method, $identificationMethod = 'cedula') {
        try {
            // Obtener datos del usuario
            $result = $this->findUser($identifier, $identificationMethod);
            if(!$result['success']) {
                return $result;
            }
            
            $user = $result['user_data'];
            
            // Generar código de 6 dígitos
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Expira en 10 minutos
            
            // Guardar el código en la base de datos
            $pdo = $this->db->getConnection();
            
            // Crear tabla de tokens si no existe (con nueva estructura)
            $this->createPasswordResetTable($pdo);
            
            // Limpiar códigos expirados del usuario
            $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE cedula = ? AND expires_at < NOW()");
            $stmt->execute([$user['cedula']]);
            
            // Insertar nuevo código
            $stmt = $pdo->prepare("
                INSERT INTO password_reset_tokens (cedula, token, method, expires_at, created_at, used) 
                VALUES (?, ?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$user['cedula'], $code, $method, $expires]);
            
            // Enviar según el método seleccionado
            if($method === 'email') {
                $emailResult = $this->sendEmailCode($user['email'], $code, $user['nombres'] ?? 'Usuario');
                
                if($emailResult) {
                    return [
                        'success' => true,
                        'message' => 'Se ha enviado un código de verificación a tu correo electrónico. El código expira en 10 minutos.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al enviar el código por email. Inténtalo de nuevo.'
                    ];
                }
            } else if($method === 'sms') {
                // Formatear número de teléfono
                $phone = $user['codigo_pais'] . $user['celular'];
                $phone = $this->twilioController->formatColombianNumber($phone);
                $validPhone = $this->twilioController->validatePhoneNumber($phone);
                
                if(!$validPhone) {
                    return [
                        'success' => false,
                        'message' => 'El número de teléfono registrado no es válido.'
                    ];
                }
                
                $smsResult = $this->twilioController->sendVerificationCode($validPhone, $code);
                
                if($smsResult['success']) {
                    return [
                        'success' => true,
                        'message' => 'Se ha enviado un código de verificación a tu número de celular. El código expira en 10 minutos.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al enviar el código por SMS: ' . $smsResult['error']
                    ];
                }
            }
            
        } catch(Exception $e) {
            error_log("Error en sendResetCode: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar el código. Inténtalo de nuevo.'
            ];
        }
    }
    
    private function sendEmailCode($email, $code, $userName = 'Usuario') {
        try {
            $subject = "🔒 Código de Recuperación - Valora.vip";
            $message = "
            <html>
            <head>
                <title>Código de Recuperación</title>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .logo { width: 120px; height: auto; }
                    .code-box { 
                        background: #f8f9fa; 
                        border: 2px solid #ee6f92; 
                        border-radius: 12px; 
                        text-align: center; 
                        padding: 20px; 
                        margin: 20px 0; 
                    }
                    .code { 
                        font-size: 32px; 
                        font-weight: bold; 
                        color: #ee6f92; 
                        letter-spacing: 4px;
                        font-family: 'Courier New', monospace;
                    }
                    .warning { color: #666; font-size: 14px; margin-top: 20px; }
                    .footer { text-align: center; color: #666; font-size: 12px; margin-top: 30px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <img src='https://valora.vip/assets/images/logos/logo_valora.png' alt='Valora.vip' class='logo'>
                        <h2 style='color: #ee6f92; margin: 15px 0 5px 0;'>Recuperación de Contraseña</h2>
                        <p style='color: #666; margin: 5px 0;'>Hola, $userName</p>
                    </div>
                    
                    <p>Has solicitado recuperar tu contraseña. Tu código de verificación es:</p>
                    
                    <div class='code-box'>
                        <div class='code'>$code</div>
                        <p style='margin: 10px 0 0 0; color: #666;'>Ingresa este código en la página de recuperación</p>
                    </div>
                    
                    <div class='warning'>
                        <p><strong>⏰ Este código expira en 10 minutos.</strong></p>
                        <p>🔒 Si no solicitaste este código, ignora este mensaje.</p>
                        <p>💡 Por tu seguridad, nunca compartas este código con nadie.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Este es un mensaje automático de Valora.vip</p>
                        <p>© " . date('Y') . " Valora.vip - Todos los derechos reservados</p>
                    </div>
                </div>
            </body>
            </html>";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: Valora.vip <noreply@valora.vip>\r\n";
            $headers .= "Reply-To: soporte@valora.vip\r\n";
            
            return mail($email, $subject, $message, $headers);
            
        } catch(Exception $e) {
            error_log("Error enviando email con código: " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyCode($cedula, $code) {
        try {
            $pdo = $this->db->getConnection();
            
            $stmt = $pdo->prepare("
                SELECT * FROM password_reset_tokens 
                WHERE cedula = ? AND token = ? AND used = 0 AND expires_at > NOW() 
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$cedula, $code]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($record) {
                return [
                    'success' => true,
                    'message' => 'Código verificado correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Código inválido o expirado'
                ];
            }
            
        } catch(Exception $e) {
            error_log("Error verificando código: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al verificar el código'
            ];
        }
    }
    
    public function markCodeUsed($cedula, $code) {
        try {
            $pdo = $this->db->getConnection();
            $stmt = $pdo->prepare("
                UPDATE password_reset_tokens 
                SET used = 1, used_at = NOW() 
                WHERE cedula = ? AND token = ? AND used = 0
            ");
            $stmt->execute([$cedula, $code]);
            return true;
        } catch(Exception $e) {
            error_log("Error marcando código como usado: " . $e->getMessage());
            return false;
        }
    }
    
    public function validateCode($cedula, $code) {
        try {
            $pdo = $this->db->getConnection();
            $stmt = $pdo->prepare("
                SELECT cedula, method, expires_at 
                FROM password_reset_tokens 
                WHERE cedula = ? AND token = ? AND used = 0 AND expires_at > NOW()
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$cedula, $code]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Código inválido o expirado'
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
                'message' => 'Error al validar el código'
            ];
        }
    }
    
    public function resetPassword($cedula, $code, $newPassword) {
        try {
            // Validar código
            $codeResult = $this->validateCode($cedula, $code);
            if(!$codeResult['success']) {
                return $codeResult;
            }
            
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
            
            // Marcar código como usado
            $this->markCodeUsed($cedula, $code);
            
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
            token VARCHAR(6) NOT NULL,
            method ENUM('email', 'sms') NOT NULL DEFAULT 'email',
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            used_at DATETIME NULL,
            INDEX idx_cedula (cedula),
            INDEX idx_token (token),
            INDEX idx_expires (expires_at),
            INDEX idx_used (used)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($sql);
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