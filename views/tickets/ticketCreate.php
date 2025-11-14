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

// ============================================
// OBTENER INFORMACIÓN DEL MÓDULO DESDE LA BD
// ============================================
try {
    $stmt = $pdo->prepare("SELECT titulo, subtitulo, icono FROM modulos WHERE ruta_completa = ? AND activo = 1 LIMIT 1");
    $stmt->execute(['views\tickets\ticketCreate.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Crear Ticket de Soporte - Valora";

// Título, subtítulo e icono desde la base de datos
$titulo_pagina = $modulo['titulo'] ?? 'Crear Nuevo Ticket de Soporte';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Envíanos tu consulta o reporte';
$icono_pagina = $modulo['icono'] ?? '🎫';

// Variables para header.php
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Crear Ticket', 'url' => null]
];

// CSS adicional específico de esta página
$additional_css = ['../../assets/css/ticketCreate.css'];

// JS adicional específico de esta página
$additional_js = ['../../assets/js/ticketCreateValidation.js'];

// Obtener mensajes de sesión
$error = $_SESSION['ticket_error'] ?? '';
$success = $_SESSION['ticket_success'] ?? '';
unset($_SESSION['ticket_error'], $_SESSION['ticket_success']);

// ============================================
// CAPTURAR CONTENIDO DE LA PÁGINA
// ============================================
ob_start();
?>

<div class="ticket-container">
    <div class="ticket-card">
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
            
            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #6A1B1B, #882A57); border: none; color: white; padding: 12px 30px; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3); width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(106, 27, 27, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(106, 27, 27, 0.3)';">
                📨 Enviar Ticket
            </button>
        </form>
    </div>
</div>

<?php
// Capturar el contenido generado
$content = ob_get_clean();

// ============================================
// CARGAR LAYOUT MASTER
// ============================================
include __DIR__ . '/../layouts/master.php';
?>
