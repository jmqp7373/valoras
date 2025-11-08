<?php
/**
 * Controlador de Tickets de Soporte
 * 
 * Maneja la creación, listado y gestión de tickets de soporte
 * 
 * @author Valora.vip
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/database.php';

class TicketController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
        $this->createTicketsTableIfNotExists();
        startSessionSafely();
        
        // Verificar que el usuario esté autenticado
        if(!isLoggedIn()) {
            header('Location: /views/login/login.php');
            exit();
        }
    }
    
    /**
     * Crear tabla de tickets si no existe
     */
    private function createTicketsTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_cedula VARCHAR(20) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            attachment_path VARCHAR(500) DEFAULT NULL,
            status ENUM('abierto', 'en_proceso', 'resuelto', 'cerrado') DEFAULT 'abierto',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_cedula),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creando tabla tickets: " . $e->getMessage());
        }
    }
    
    /**
     * Renderizar vista de creación de ticket
     */
    public function create() {
        require_once __DIR__ . '/../views/tickets/ticketCreate.php';
    }
    
    /**
     * Procesar y guardar nuevo ticket
     */
    public function store() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /views/tickets/ticketCreate.php');
            exit();
        }
        
        // Validar campos obligatorios
        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $userCedula = $_SESSION['user_cedula'] ?? '';
        
        if(empty($subject) || empty($description)) {
            $_SESSION['ticket_error'] = 'El asunto y la descripción son obligatorios';
            header('Location: /views/tickets/ticketCreate.php');
            exit();
        }
        
        if(strlen($subject) < 5) {
            $_SESSION['ticket_error'] = 'El asunto debe tener al menos 5 caracteres';
            header('Location: /views/tickets/ticketCreate.php');
            exit();
        }
        
        if(strlen($description) < 10) {
            $_SESSION['ticket_error'] = 'La descripción debe tener al menos 10 caracteres';
            header('Location: /views/tickets/ticketCreate.php');
            exit();
        }
        
        // Procesar archivo adjunto si existe
        $attachmentPath = null;
        if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $attachmentPath = $this->handleFileUpload($_FILES['attachment']);
            
            if($attachmentPath === false) {
                $_SESSION['ticket_error'] = 'Error al subir el archivo. Verifica que sea una imagen válida (JPG, PNG) y no supere 5MB';
                header('Location: /views/tickets/ticketCreate.php');
                exit();
            }
        }
        
        // Guardar ticket en base de datos
        try {
            $sql = "INSERT INTO tickets (user_cedula, subject, description, attachment_path, status) 
                    VALUES (:cedula, :subject, :description, :attachment, 'abierto')";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':cedula' => $userCedula,
                ':subject' => $subject,
                ':description' => $description,
                ':attachment' => $attachmentPath
            ]);
            
            $_SESSION['ticket_success'] = 'Ticket creado exitosamente. ID: ' . $this->pdo->lastInsertId();
            header('Location: /views/tickets/ticketList.php');
            exit();
            
        } catch (PDOException $e) {
            error_log("Error guardando ticket: " . $e->getMessage());
            $_SESSION['ticket_error'] = 'Error al crear el ticket. Intenta de nuevo.';
            header('Location: /views/tickets/ticketCreate.php');
            exit();
        }
    }
    
    /**
     * Mostrar listado de tickets del usuario
     */
    public function index() {
        $userCedula = $_SESSION['user_cedula'] ?? '';
        
        try {
            $sql = "SELECT id, subject, status, created_at, updated_at 
                    FROM tickets 
                    WHERE user_cedula = :cedula 
                    ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cedula' => $userCedula]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            require_once __DIR__ . '/../views/tickets/ticketList.php';
            
        } catch (PDOException $e) {
            error_log("Error obteniendo tickets: " . $e->getMessage());
            $tickets = [];
            require_once __DIR__ . '/../views/tickets/ticketList.php';
        }
    }
    
    /**
     * Manejar subida de archivo
     */
    private function handleFileUpload($file) {
        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = $file['type'];
        
        if(!in_array($fileType, $allowedTypes)) {
            return false;
        }
        
        // Validar tamaño (máximo 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB en bytes
        if($file['size'] > $maxSize) {
            return false;
        }
        
        // Crear directorio de uploads si no existe
        $uploadDir = __DIR__ . '/../uploads/tickets/';
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'ticket_' . uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;
        
        // Mover archivo
        if(move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return 'uploads/tickets/' . $fileName;
        }
        
        return false;
    }
}

// Enrutamiento simple para tickets
if(basename($_SERVER['PHP_SELF']) === 'TicketController.php') {
    $controller = new TicketController();
    $action = $_GET['action'] ?? 'index';
    
    switch($action) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'index':
        default:
            $controller->index();
            break;
    }
}
