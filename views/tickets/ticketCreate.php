<?php
/**
 * Vista: Crear Nuevo Ticket de Soporte
 * 
 * Formulario para que el usuario cree un ticket de soporte
 */

// Habilitar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Crear tabla si no existe
try {
    $pdo = getDBConnection();
    $createTable = "CREATE TABLE IF NOT EXISTS tickets (
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
    $pdo->exec($createTable);
} catch (PDOException $e) {
    error_log("Error creando tabla tickets: " . $e->getMessage());
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// Variables para header
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Obtener mensajes de sesión
$error = $_SESSION['ticket_error'] ?? '';
$success = $_SESSION['ticket_success'] ?? '';
unset($_SESSION['ticket_error'], $_SESSION['ticket_success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ticket de Soporte - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .ticket-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .ticket-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .ticket-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .ticket-header img {
            max-width: 150px;
            margin-bottom: 20px;
        }
        
        .ticket-header h2 {
            color: #882A57;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #882A57;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ee6f92;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }
        
        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #d63384;
            box-shadow: 0 0 0 3px rgba(238, 111, 146, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ee6f92;
            border-radius: 8px;
            background: #fef7f9;
            cursor: pointer;
        }
        
        .form-group small {
            display: block;
            color: #6c757d;
            margin-top: 5px;
            font-size: 14px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #882A57, #ee6f92);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 42, 87, 0.4);
        }
        
        .btn-secondary {
            width: 100%;
            padding: 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }
        
        .alert-success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }
        
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body style="background-color: #F8F9FA;">
    <?php include '../../components/header/header.php'; ?>
    
    <div class="ticket-container">
        <div class="ticket-card">
            <div class="ticket-header">
                <img src="../../assets/images/logos/logoValoraHorizontal.png" alt="Valora Logo">
                <h2>🎫 Crear Ticket de Soporte</h2>
                <p style="color: #666;">Cuéntanos en qué podemos ayudarte</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form action="../../controllers/TicketController.php?action=store" method="POST" enctype="multipart/form-data" id="ticketForm">
                <div class="form-group">
                    <label for="subject">
                        📋 Asunto del Ticket <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           placeholder="Ejemplo: Problema con mi cuenta"
                           maxlength="255"
                           required>
                    <small>Describe brevemente tu problema (mínimo 5 caracteres)</small>
                </div>
                
                <div class="form-group">
                    <label for="description">
                        📝 Descripción Detallada <span class="required">*</span>
                    </label>
                    <textarea id="description" 
                              name="description" 
                              placeholder="Describe tu problema con el mayor detalle posible..."
                              required></textarea>
                    <small>Explica tu situación con detalle (mínimo 10 caracteres)</small>
                </div>
                
                <div class="form-group">
                    <label for="attachment">
                        📎 Adjuntar Archivo (Opcional)
                    </label>
                    <input type="file" 
                           id="attachment" 
                           name="attachment" 
                           accept="image/jpeg,image/png,image/jpg">
                    <small>Formatos permitidos: JPG, PNG. Tamaño máximo: 5MB</small>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>👤 Información del Usuario:</strong><br>
                    <span style="color: #666; font-size: 14px;">
                        <?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?><br>
                        Cédula: <?php echo htmlspecialchars($_SESSION['user_cedula'] ?? ''); ?>
                    </span>
                </div>
                
                <button type="submit" class="btn-submit">
                    📨 Enviar Ticket
                </button>
                
                <a href="ticketList.php" class="btn-secondary">
                    📋 Ver Mis Tickets
                </a>
                
                <a href="../../index.php" class="btn-secondary" style="background: #17a2b8;">
                    🏠 Volver al Inicio
                </a>
            </form>
        </div>
    </div>
    
    <script>
        // Validación del formulario
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            const subject = document.getElementById('subject').value.trim();
            const description = document.getElementById('description').value.trim();
            
            if(subject.length < 5) {
                e.preventDefault();
                alert('El asunto debe tener al menos 5 caracteres');
                return false;
            }
            
            if(description.length < 10) {
                e.preventDefault();
                alert('La descripción debe tener al menos 10 caracteres');
                return false;
            }
            
            // Validar archivo si se seleccionó
            const fileInput = document.getElementById('attachment');
            if(fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                
                if(!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Solo se permiten archivos JPG o PNG');
                    return false;
                }
                
                if(file.size > maxSize) {
                    e.preventDefault();
                    alert('El archivo no debe superar los 5MB');
                    return false;
                }
            }
        });
        
        // Contador de caracteres para descripción
        document.getElementById('description').addEventListener('input', function() {
            const length = this.value.length;
            const parent = this.parentElement;
            let counter = parent.querySelector('.char-counter');
            
            if(!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter';
                counter.style.float = 'right';
                parent.querySelector('small').after(counter);
            }
            
            counter.textContent = `${length} caracteres`;
            counter.style.color = length >= 10 ? '#28a745' : '#dc3545';
        });
    </script>
    
    <?php include '../../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
