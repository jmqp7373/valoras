<?php
// Función para iniciar sesión de forma segura
function startSessionSafely() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Configuración de la base de datos
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Detectar si estamos en producción o desarrollo
        $isProduction = isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'valora.vip';
        
        if ($isProduction) {
            // Configuración para producción (hosting)
            $this->host = 'localhost'; // En hosting compartido suele ser localhost
            $this->db_name = 'u179023609_orvlvi';
            $this->username = 'u179023609_orvlvi';
            $this->password = 'Reylondres7373.';
        } else {
            // Configuración para desarrollo local (XAMPP)
            $this->host = 'localhost';
            $this->db_name = 'valora_db';
            $this->username = 'root';
            $this->password = '';
        }
    }

    // Método para conectar a la base de datos
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Función auxiliar para obtener conexión a la base de datos
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para redirigir si no está logueado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: views/login.php');
        exit();
    }
}

// Función para hashear contraseñas de forma segura
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Función para verificar contraseñas
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>