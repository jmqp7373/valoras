<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../TwilioController.php';

startSessionSafely();

class AuthController {
    private $db;
    private $usuario;
    private $twilioController;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->usuario = new Usuario($this->db);
        $this->twilioController = new TwilioController();
    }

    // Método para manejar el login
    public function login() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $numero_cedula = trim($_POST['Numero_de_cedula']);
            $password = trim($_POST['contraseña']);

            // Validar que los campos no estén vacíos
            if(empty($numero_cedula) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Por favor complete todos los campos'
                ];
            }

            // Intentar autenticar
            $user_data = $this->usuario->login($numero_cedula, $password);

            if($user_data) {
                // Login exitoso
                $_SESSION['user_id'] = $user_data['id_usuario'];
                $_SESSION['user_cedula'] = $user_data['cedula'];
                $_SESSION['user_nombres'] = $user_data['nombres'];
                $_SESSION['user_apellidos'] = $user_data['apellidos'];
                $_SESSION['user_email'] = $user_data['email'];
                $_SESSION['logged_in'] = true;

                return [
                    'success' => true,
                    'message' => 'Login exitoso',
                    'redirect' => '../../index.php'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Número de cédula o contraseña incorrectos'
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Método no permitido'
        ];
    }

    // Método para manejar el registro
    public function register() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cedula = trim($_POST['Numero_de_cedula']);
            $nombres = trim($_POST['first_name']);
            $apellidos = trim($_POST['last_name']);
            $username = trim($_POST['username']);
            $codigo_pais = trim($_POST['country_code']);
            $celular = trim($_POST['phone_number']);

            // NO generar email automático - dejarlo vacío por ahora
            $email = '';
            
            // Generar código temporal de 6 dígitos para inicio de sesión
            $codigo_temporal = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Validar que los campos requeridos no estén vacíos
            if(empty($cedula) || empty($nombres) || empty($apellidos) || empty($username) || empty($celular)) {
                return [
                    'success' => false,
                    'message' => 'Por favor complete todos los campos obligatorios incluyendo el nombre de usuario'
                ];
            }

            // Verificar si el usuario ya existe
            if($this->usuario->existsByCedula($cedula)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un usuario con este número de cédula'
                ];
            }

            // Crear nuevo usuario
            $this->usuario->cedula = $cedula;
            $this->usuario->nombres = $nombres;
            $this->usuario->apellidos = $apellidos;
            $this->usuario->usuario = $username; // ¡CORREGIDO! Asignar el nombre de usuario
            $this->usuario->password = $codigo_temporal; // Usar código de 6 dígitos como contraseña temporal
            $this->usuario->codigo_pais = $codigo_pais;
            $this->usuario->celular = $celular;
            $this->usuario->email = $email; // Campo vacío por ahora

            $result = $this->usuario->create();
            
            if($result['success']) {
                // Enviar código de verificación por SMS
                $smsResult = $this->sendVerificationCodeSMS($celular, $codigo_temporal, $cedula);
                
                if($smsResult['success']) {
                    // Guardar la cédula en la sesión para pre-llenar el login
                    $_SESSION['last_registered_cedula'] = $cedula;
                    $_SESSION['sms_sent_at'] = time(); // Timestamp para el contador regresivo
                    
                    return [
                        'success' => true,
                        'message' => 'Usuario registrado exitosamente. Se ha enviado un código de verificación de 6 dígitos a tu número de celular. Usa este código para iniciar sesión.',
                        'redirect' => 'login.php'
                    ];
                } else {
                    // Si falla el SMS, mostrar el código en pantalla como respaldo
                    return [
                        'success' => true,
                        'message' => 'Usuario registrado exitosamente. Tu código temporal de acceso es: ' . $codigo_temporal . '. Use este código para iniciar sesión. (SMS no disponible)',
                        'redirect' => 'login.php'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Método no permitido'
        ];
    }

    // Método para verificar disponibilidad de username
    public function checkUsername() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            
            if(empty($username)) {
                echo 'invalid';
                return;
            }
            
            try {
                // Verificar si el username ya existe en la columna 'usuario'
                $query = "SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':usuario', $username);
                $stmt->execute();
                
                $count = $stmt->fetchColumn();
                
                echo $count == 0 ? 'available' : 'unavailable';
                return;
                
            } catch (Exception $e) {
                // En caso de error de base de datos
                echo 'unavailable';
                return;
            }
        }
        
        echo 'invalid';
        return;
    }

    // Método para enviar código de verificación por SMS
    private function sendVerificationCodeSMS($celular, $codigo, $cedula) {
        try {
            // Formatear el número de celular
            $phone = $this->twilioController->formatColombianNumber($celular);
            $validPhone = $this->twilioController->validatePhoneNumber($phone);
            
            if(!$validPhone) {
                return [
                    'success' => false,
                    'error' => 'El número de celular no es válido.'
                ];
            }
            
            // Enviar SMS usando TwilioController
            $smsResult = $this->twilioController->sendVerificationCode($validPhone, $codigo);
            
            if($smsResult['success']) {
                // Guardar el código en la tabla de tokens para verificación posterior
                $this->saveVerificationToken($cedula, $codigo);
                
                return [
                    'success' => true,
                    'message' => 'Código de verificación enviado por SMS.'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $smsResult['error']
                ];
            }
            
        } catch(Exception $e) {
            error_log("Error enviando SMS de registro: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al enviar el código por SMS.'
            ];
        }
    }
    
    // Método para guardar el token de verificación en la base de datos
    private function saveVerificationToken($cedula, $codigo) {
        try {
            // Usar la misma tabla que usa el sistema de reset de contraseña
            $stmt = $this->db->prepare("
                INSERT INTO password_reset_tokens (cedula, token, expires_at, method, created_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 'sms', NOW())
                ON DUPLICATE KEY UPDATE 
                token = VALUES(token), 
                expires_at = VALUES(expires_at), 
                created_at = VALUES(created_at)
            ");
            
            // La tabla usa token como VARCHAR(6), no necesita hash
            $stmt->execute([$cedula, $codigo]);
            
            return true;
        } catch(Exception $e) {
            error_log("Error guardando token de verificación: " . $e->getMessage());
            return false;
        }
    }

    // Método para reenviar código de verificación
    public function resendVerificationCode() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cedula = trim($_POST['cedula']);
            
            if(empty($cedula)) {
                return [
                    'success' => false,
                    'message' => 'Número de cédula requerido'
                ];
            }
            
            // Verificar que no haya pasado menos de 60 segundos del último envío
            if(isset($_SESSION['sms_sent_at'])) {
                $time_diff = time() - $_SESSION['sms_sent_at'];
                if($time_diff < 60) {
                    $remaining = 60 - $time_diff;
                    return [
                        'success' => false,
                        'message' => "Debe esperar {$remaining} segundos antes de reenviar el código",
                        'remaining_time' => $remaining
                    ];
                }
            }
            
            try {
                // Buscar el usuario por cédula
                $stmt = $this->db->prepare("SELECT cedula, celular FROM usuarios WHERE cedula = :cedula LIMIT 1");
                $stmt->bindParam(':cedula', $cedula);
                $stmt->execute();
                
                if($stmt->rowCount() == 0) {
                    return [
                        'success' => false,
                        'message' => 'Usuario no encontrado'
                    ];
                }
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Generar nuevo código de 6 dígitos
                $nuevo_codigo = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
                
                // Actualizar la contraseña del usuario
                $update_stmt = $this->db->prepare("UPDATE usuarios SET password = :password WHERE cedula = :cedula");
                $hashed_password = password_hash($nuevo_codigo, PASSWORD_DEFAULT);
                $update_stmt->bindParam(':password', $hashed_password);
                $update_stmt->bindParam(':cedula', $cedula);
                $update_stmt->execute();
                
                // Enviar nuevo código por SMS
                $smsResult = $this->sendVerificationCodeSMS($user['celular'], $nuevo_codigo, $cedula);
                
                if($smsResult['success']) {
                    // Actualizar timestamp del último envío
                    $_SESSION['sms_sent_at'] = time();
                    
                    return [
                        'success' => true,
                        'message' => 'Nuevo código de verificación enviado a tu celular'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al enviar el código: ' . $smsResult['error']
                    ];
                }
                
            } catch(Exception $e) {
                error_log("Error en reenvío de código: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error al procesar la solicitud'
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Método no permitido'
        ];
    }

    // Método para cerrar sesión
    public function logout() {
        session_start();
        session_destroy();
        header('Location: ../views/login/login.php');
        exit();
    }
}

// Manejar solicitudes AJAX
if(isset($_POST['action'])) {
    $auth = new AuthController();
    
    switch($_POST['action']) {
        case 'check_username':
            echo $auth->checkUsername();
            exit();
        case 'resend_code':
            $result = $auth->resendVerificationCode();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        default:
            echo 'invalid_action';
            exit();
    }
}
?>