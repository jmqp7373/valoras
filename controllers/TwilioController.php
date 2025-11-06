<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Twilio\Rest\Client;

class TwilioController {
    private $sid;
    private $token;
    private $from;

    public function __construct() {
        $config = require __DIR__ . '/../config/twilioSmsConfig.php';
        $this->sid   = $config['sid'];
        $this->token = $config['token'];
        $this->from  = $config['from'];
    }

    public function sendVerificationCode($phone, $code) {
        try {
            $client = new Client($this->sid, $this->token);
            $message = "Tu código de verificación Valora.vip: {$code}. Válido por 10 minutos.";
            
            $result = $client->messages->create($phone, [
                'from' => $this->from,
                'body' => $message
            ]);
            
            // Log para debugging
            error_log("SMS enviado exitosamente a {$phone}. SID: " . $result->sid);
            return ['success' => true, 'sid' => $result->sid];
            
        } catch (Exception $e) {
            error_log("Error enviando SMS a {$phone}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Validar formato de número de teléfono
     */
    public function validatePhoneNumber($phone) {
        // Remover espacios y caracteres especiales
        $phone = preg_replace('/[^+\d]/', '', $phone);
        
        // Verificar que comience con + y tenga entre 10-15 dígitos
        if (preg_match('/^\+\d{10,15}$/', $phone)) {
            return $phone;
        }
        
        return false;
    }

    /**
     * Formatear número colombiano si no tiene código de país
     */
    public function formatColombianNumber($phone) {
        // Si es número colombiano sin +57, agregarlo
        if (preg_match('/^3\d{9}$/', $phone)) {
            return '+57' . $phone;
        }
        
        return $phone;
    }
}
?>