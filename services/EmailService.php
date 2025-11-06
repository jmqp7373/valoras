<?php
/**
 * EmailService - Servicio de envío de emails para Valora.vip
 * Utiliza PHPMailer con configuración de Migadu SMTP
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $config;
    
    public function __construct() {
        // Cargar configuración de email
        $this->config = require_once __DIR__ . '/../config/email-config.php';
        
        // Cargar autoloader de Composer
        require_once __DIR__ . '/../vendor/autoload.php';
    }
    
    /**
     * Enviar email de recuperación de contraseña
     */
    public function sendPasswordResetEmail($recipientEmail, $recipientName, $resetLink, $cedula) {
        try {
            $mail = $this->createMailer();
            
            // Destinatario
            $toEmail = $this->config['development_mode'] ? $this->config['development_email'] : $recipientEmail;
            $mail->addAddress($toEmail, $recipientName);
            
            // Asunto
            $mail->Subject = 'Recuperación de Contraseña - Valora';
            
            // Adjuntar logo como inline attachment (método de compatibilidad)
            $logoPath = __DIR__ . '/../assets/images/logos/logo_valora.png';
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'valora_logo', 'logo_valora.png');
            }
            
            // Contenido del email
            $mail->isHTML(true);
            $mail->Body = $this->generatePasswordResetTemplate($recipientName, $resetLink, $cedula, $recipientEmail);
            $mail->AltBody = $this->generatePasswordResetTextVersion($recipientName, $resetLink, $cedula);
            
            // Enviar email
            $result = $mail->send();
            
            if($result) {
                return [
                    'success' => true,
                    'message' => 'Email de recuperación enviado exitosamente',
                    'recipient' => $toEmail
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al enviar el email: ' . $mail->ErrorInfo
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del servidor de email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar email con código de verificación de 6 dígitos
     */
    public function sendVerificationCodeEmail($recipientEmail, $recipientName, $code) {
        try {
            $mail = $this->createMailer();
            
            // Destinatario
            $toEmail = $this->config['development_mode'] ? $this->config['development_email'] : $recipientEmail;
            $mail->addAddress($toEmail, $recipientName);
            
            // Asunto
            $mail->Subject = '🔒 Código de Recuperación - Valora.vip';
            
            // Contenido del email
            $mail->isHTML(true);
            $mail->Body = $this->generateVerificationCodeTemplate($recipientName, $code);
            $mail->AltBody = $this->generateVerificationCodeTextVersion($recipientName, $code);
            
            // Enviar email
            $result = $mail->send();
            
            if($result) {
                return [
                    'success' => true,
                    'message' => 'Código de verificación enviado exitosamente',
                    'recipient' => $toEmail
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al enviar el código: ' . $mail->ErrorInfo
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del servidor de email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear instancia configurada de PHPMailer
     */
    private function createMailer() {
        $mail = new PHPMailer(true);
        
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = $this->config['smtp_host'];
        $mail->SMTPAuth = $this->config['smtp_auth'];
        $mail->Username = $this->config['smtp_username'];
        $mail->Password = $this->config['smtp_password'];
        $mail->SMTPSecure = $this->config['smtp_secure'];
        $mail->Port = $this->config['smtp_port'];
        $mail->Timeout = $this->config['timeout'];
        
        // Configuración de debug (solo en desarrollo)
        if($this->config['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer [$level]: $str");
            };
        }
        
        // Configuración del remitente
        $mail->setFrom($this->config['from_email'], $this->config['from_name']);
        $mail->addReplyTo($this->config['reply_to_email'], $this->config['reply_to_name']);
        
        // Configuración de codificación
        $mail->CharSet = $this->config['charset'];
        $mail->Encoding = 'base64';
        
        return $mail;
    }
    
    /**
     * Obtener logo en base64 para embeding en email
     */
    private function getLogoBase64() {
        $logoPath = __DIR__ . '/../assets/images/logos/logo_valora.png';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoMimeType = 'image/png';
            return "data:$logoMimeType;base64,$logoData";
        }
        
        // Fallback: crear un logo SVG simple si no existe el archivo
        $svgLogo = '<svg width="200" height="80" xmlns="http://www.w3.org/2000/svg">
            <rect width="200" height="80" fill="#882A57" rx="8"/>
            <text x="100" y="45" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="white" text-anchor="middle">VALORA</text>
        </svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svgLogo);
    }
    
    /**
     * Generar template HTML para email de recuperación
     */
    private function generatePasswordResetTemplate($name, $resetLink, $cedula, $originalEmail) {
        $currentYear = date('Y');
        $expirationTime = date('H:i', strtotime('+1 hour'));
        $logoBase64 = $this->getLogoBase64();
        
        // Determinar qué método usar para el logo
        $logoPath = __DIR__ . '/../assets/images/logos/logo_valora.png';
        $logoSrc = file_exists($logoPath) ? 'cid:valora_logo' : $logoBase64;
        
        $developmentNotice = $this->config['development_mode'] ? 
            '<div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 20px; text-align: center;">
                <strong>🚧 MODO DESARROLLO</strong><br>
                Este email estaba destinado a: ' . htmlspecialchars($originalEmail) . '
            </div>' : '';
        
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperación de Contraseña - Valora</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f8f9fa; font-family: Arial, sans-serif;'>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                <tr>
                    <td style='padding: 40px 0; text-align: center;'>
                        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' style='margin: 0 auto; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='padding: 40px 40px 20px 40px; text-align: center; border-bottom: 2px solid #f0f0f0;'>
                                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' style='margin: 0 auto;'>
                                        <tr>
                                            <td style='text-align: center;'>
                                                <img src='$logoSrc' alt='Valora Logo' width='200' height='80' style='max-width: 200px; height: auto; margin-bottom: 20px; display: block; border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic;'>
                                            </td>
                                        </tr>
                                    </table>
                                    <h1 style='color: #882A57; font-size: 28px; margin: 0; font-weight: bold; font-family: Arial, sans-serif;'>🔐 Recuperación de Contraseña</h1>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style='padding: 40px;'>
                                    $developmentNotice
                                    
                                    <p style='font-size: 18px; color: #333; margin-bottom: 20px; line-height: 1.6;'>
                                        Hola <strong>" . htmlspecialchars($name) . "</strong>,
                                    </p>
                                    
                                    <p style='font-size: 16px; color: #666; margin-bottom: 25px; line-height: 1.6;'>
                                        Recibimos una solicitud para restablecer la contraseña de tu cuenta en Valora asociada a la cédula: <strong>" . htmlspecialchars($cedula) . "</strong>
                                    </p>
                                    
                                    <div style='background-color: #f8f9ff; border-left: 4px solid #882A57; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;'>
                                        <p style='margin: 0; font-size: 16px; color: #333; font-weight: 500;'>
                                            ⏰ <strong>Este enlace expira hoy a las $expirationTime</strong><br>
                                            Por tu seguridad, úsalo lo antes posible.
                                        </p>
                                    </div>
                                    
                                    <div style='text-align: center; margin: 35px 0;'>
                                        <a href='$resetLink' style='display: inline-block; background-color: #882A57; color: #ffffff; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: bold; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s ease;'>
                                            🔓 Crear Nueva Contraseña
                                        </a>
                                    </div>
                                    
                                    <div style='background-color: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; padding: 20px; margin: 30px 0;'>
                                        <h3 style='color: #0c5460; margin: 0 0 15px 0; font-size: 16px;'>🛡️ Consejos de Seguridad:</h3>
                                        <ul style='color: #0c5460; font-size: 14px; margin: 0; padding-left: 20px; line-height: 1.5;'>
                                            <li>Usa una contraseña única que no hayas usado antes</li>
                                            <li>Combina letras mayúsculas, minúsculas, números y símbolos</li>
                                            <li>Evita información personal como fechas de nacimiento</li>
                                            <li>Nunca compartas tu contraseña con otras personas</li>
                                        </ul>
                                    </div>
                                    
                                    <p style='font-size: 14px; color: #888; margin-top: 30px; line-height: 1.5;'>
                                        Si no solicitaste este cambio de contraseña, puedes ignorar este email de forma segura. Tu contraseña actual seguirá siendo válida.
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style='padding: 30px 40px; background-color: #f8f9fa; border-top: 2px solid #f0f0f0; border-radius: 0 0 16px 16px; text-align: center;'>
                                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #666;'>
                                        <strong>Valora Team</strong> | Soporte 24/7
                                    </p>
                                    <p style='margin: 0; font-size: 12px; color: #999;'>
                                        📧 soporte@valora.vip | 🌐 www.valora.vip<br>
                                        © $currentYear Valora. Todos los derechos reservados.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }
    
    /**
     * Generar versión de texto plano del email
     */
    private function generatePasswordResetTextVersion($name, $resetLink, $cedula) {
        $expirationTime = date('H:i', strtotime('+1 hour'));
        
        return "
RECUPERACIÓN DE CONTRASEÑA - VALORA

Hola $name,

Recibimos una solicitud para restablecer la contraseña de tu cuenta en Valora asociada a la cédula: $cedula

ENLACE DE RECUPERACIÓN:
$resetLink

⏰ IMPORTANTE: Este enlace expira hoy a las $expirationTime

CONSEJOS DE SEGURIDAD:
- Usa una contraseña única que no hayas usado antes
- Combina letras mayúsculas, minúsculas, números y símbolos
- Evita información personal como fechas de nacimiento
- Nunca compartas tu contraseña con otras personas

Si no solicitaste este cambio de contraseña, puedes ignorar este email de forma segura.

--
Valora Team
Soporte: soporte@valora.vip
Web: www.valora.vip
        ";
    }
    
    /**
     * Enviar email de contacto general
     */
    public function sendContactEmail($fromName, $fromEmail, $message) {
        try {
            $mail = $this->createMailer();
            
            // Destinatario (siempre al admin)
            $mail->addAddress($this->config['from_email'], 'Administrador Valora');
            
            // Remitente
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addReplyTo($fromEmail, $fromName);
            
            // Asunto
            $mail->Subject = "Nuevo mensaje de contacto - $fromName";
            
            // Contenido
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Nuevo mensaje de contacto</h2>
                <p><strong>Nombre:</strong> " . htmlspecialchars($fromName) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($fromEmail) . "</p>
                <p><strong>Mensaje:</strong></p>
                <div style='border-left: 3px solid #882A57; padding-left: 15px; margin: 15px 0;'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <p><small>Enviado desde el formulario de contacto de valora.vip</small></p>
            ";
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Error enviando email de contacto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar template HTML para código de verificación
     */
    private function generateVerificationCodeTemplate($userName, $code) {
        return "
        <html>
        <head>
            <title>Código de Verificación - Valora.vip</title>
            <style>
                body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #ffffff; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ee6f92; padding-bottom: 20px; }
                .logo { width: 120px; height: auto; margin-bottom: 15px; }
                .code-box { 
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
                    border: 2px solid #ee6f92; 
                    border-radius: 15px; 
                    text-align: center; 
                    padding: 25px; 
                    margin: 25px 0; 
                    box-shadow: 0 4px 12px rgba(238, 111, 146, 0.1);
                }
                .code { 
                    font-size: 36px; 
                    font-weight: bold; 
                    color: #ee6f92; 
                    letter-spacing: 6px;
                    font-family: 'Courier New', monospace;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .warning { 
                    background: #fff3cd; 
                    border: 1px solid #ffeaa7; 
                    border-radius: 8px; 
                    padding: 15px; 
                    margin: 20px 0; 
                    font-size: 14px; 
                }
                .footer { 
                    text-align: center; 
                    color: #666; 
                    font-size: 12px; 
                    margin-top: 30px; 
                    border-top: 1px solid #dee2e6; 
                    padding-top: 20px; 
                }
                .security-tips { 
                    background: #e7f3ff; 
                    border-left: 4px solid #007bff; 
                    padding: 15px; 
                    margin: 20px 0; 
                    font-size: 14px; 
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://valora.vip/assets/images/logos/logo_valora.png' alt='Valora.vip' class='logo'>
                    <h1 style='color: #ee6f92; margin: 0; font-size: 24px;'>Recuperación de Contraseña</h1>
                    <p style='color: #666; margin: 10px 0 0 0; font-size: 16px;'>Hola, $userName</p>
                </div>
                
                <p style='font-size: 16px; margin-bottom: 20px;'>
                    Has solicitado recuperar tu contraseña. Tu código de verificación es:
                </p>
                
                <div class='code-box'>
                    <div class='code'>$code</div>
                    <p style='margin: 15px 0 0 0; color: #666; font-size: 14px;'>
                        Ingresa este código en la página de recuperación
                    </p>
                </div>
                
                <div class='warning'>
                    <p style='margin: 0 0 10px 0;'><strong>⏰ Este código expira en 10 minutos.</strong></p>
                    <p style='margin: 0;'>Si no solicitaste este código, ignora este mensaje.</p>
                </div>
                
                <div class='security-tips'>
                    <p style='margin: 0 0 10px 0;'><strong>💡 Consejos de seguridad:</strong></p>
                    <ul style='margin: 0; padding-left: 20px;'>
                        <li>Nunca compartas este código con nadie</li>
                        <li>Valora nunca te pedirá el código por teléfono</li>
                        <li>Usa una contraseña nueva y segura</li>
                    </ul>
                </div>
                
                <div class='footer'>
                    <p>Este es un mensaje automático de Valora.vip</p>
                    <p>© " . date('Y') . " Valora.vip - Todos los derechos reservados</p>
                    <p>¿Necesitas ayuda? <a href='mailto:soporte@valora.vip' style='color: #ee6f92;'>Contacta soporte</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Generar versión de texto plano para código de verificación
     */
    private function generateVerificationCodeTextVersion($userName, $code) {
        return "
VALORA.VIP - CÓDIGO DE VERIFICACIÓN

Hola $userName,

Has solicitado recuperar tu contraseña. Tu código de verificación es:

$code

Ingresa este código en la página de recuperación.

⏰ IMPORTANTE: Este código expira en 10 minutos.

CONSEJOS DE SEGURIDAD:
- Nunca compartas este código con nadie
- Valora nunca te pedirá el código por teléfono  
- Usa una contraseña nueva y segura

Si no solicitaste este código, puedes ignorar este email de forma segura.

--
Valora Team
Soporte: soporte@valora.vip
";
    }
}
?>