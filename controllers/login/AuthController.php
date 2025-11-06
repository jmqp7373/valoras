<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';

startSessionSafely();

class AuthController {
    private $db;
    private $usuario;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->usuario = new Usuario($this->db);
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

            // Generar email temporal basado en la cédula
            $email = $cedula . '@temp.valora.vip';
            
            // Generar contraseña temporal (cédula + "123")
            $password_temp = $cedula . '123';

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
            $this->usuario->password = $password_temp;
            $this->usuario->codigo_pais = $codigo_pais;
            $this->usuario->celular = $celular;
            $this->usuario->email = $email;

            $result = $this->usuario->create();
            
            if($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente. Su contraseña temporal es: ' . $password_temp,
                    'redirect' => 'login.php'
                ];
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
        default:
            echo 'invalid_action';
            exit();
    }
}
?>