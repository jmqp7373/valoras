<?php
/**
 * Vista: Listado de Tickets de Soporte
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

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$userCedula = $_SESSION['user_cedula'] ?? '';

// Obtener mensajes de sesión
$success = $_SESSION['ticket_success'] ?? '';
unset($_SESSION['ticket_success']);

// Obtener tickets del usuario directamente
$tickets = [];
try {
    $pdo = getDBConnection();
    
    // Crear tabla si no existe
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
    
    // Obtener tickets
    $sql = "SELECT id, subject, status, created_at, updated_at 
            FROM tickets 
            WHERE user_cedula = :cedula 
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cedula' => $userCedula]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo tickets: " . $e->getMessage());
    echo "<!-- Error DB: " . $e->getMessage() . " -->";
    $tickets = [];
}

// ============================================
// OBTENER INFORMACIÓN DEL MÓDULO DESDE LA BD
// ============================================
try {
    $stmt = $pdo->prepare("SELECT titulo, subtitulo, icono FROM modulos WHERE ruta_completa = ? AND activo = 1 LIMIT 1");
    $stmt->execute(['views\tickets\ticketList.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Mis Tickets de Soporte - Valora";

// Título, subtítulo e icono desde la base de datos
$titulo_pagina = $modulo['titulo'] ?? 'Lista de Tickets de Soporte';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Gestiona tus tickets de soporte';
$icono_pagina = $modulo['icono'] ?? '📋';

// Variables para header.php
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Mis Tickets', 'url' => null]
];

// CSS adicional específico de esta página
$additional_css = ['../../assets/css/ticketList.css'];

// ============================================
// CAPTURAR CONTENIDO DE LA PÁGINA
// ============================================
ob_start();
?>

<div class="tickets-container">
    <div class="tickets-card">
        <?php if($success): ?>
            <div class="alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ✅ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-bottom: 20px;">
            <a href="ticketCreate.php" class="btn-primary" style="background: linear-gradient(135deg, #6A1B1B, #882A57); border: none; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(106, 27, 27, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(106, 27, 27, 0.3)';">
                ➕ Crear Nuevo Ticket
            </a>
        </div>
        
        <?php if(empty($tickets)): ?>
            <div class="empty-state">
                <div style="font-size: 80px; margin-bottom: 20px;">📭</div>
                <h3>No tienes tickets creados</h3>
                <p>Aún no has creado ningún ticket de soporte.</p>
                <p>¿Necesitas ayuda? Crea tu primer ticket ahora.</p>
                <br>
                <a href="ticketCreate.php" class="btn-primary" style="background: linear-gradient(135deg, #6A1B1B, #882A57); border: none; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(106, 27, 27, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(106, 27, 27, 0.3)';">
                    ➕ Crear Mi Primer Ticket
                </a>
            </div>
        <?php else: ?>
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Fecha de Creación</th>
                        <th>Última Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tickets as $ticket): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($ticket['id']); ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($ticket['subject']); ?></strong>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'status-' . $ticket['status'];
                                $statusText = [
                                    'abierto' => '🟢 Abierto',
                                    'en_proceso' => '🟡 En Proceso',
                                    'resuelto' => '✅ Resuelto',
                                    'cerrado' => '🔴 Cerrado'
                                ];
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText[$ticket['status']] ?? $ticket['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $date = new DateTime($ticket['created_at']);
                                echo $date->format('d/m/Y H:i');
                                ?>
                            </td>
                            <td>
                                <?php 
                                $date = new DateTime($ticket['updated_at']);
                                echo $date->format('d/m/Y H:i');
                                ?>
                            </td>
                            <td>
                                <a href="ticketView.php?id=<?php echo $ticket['id']; ?>" class="btn-view" style="background: linear-gradient(135deg, #6A1B1B, #882A57); border: none; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(106, 27, 27, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(106, 27, 27, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(106, 27, 27, 0.3)';">
                                    👁️ Ver Detalle
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px; text-align: center; color: #6c757d;">
                <p>Total de tickets: <strong><?php echo count($tickets); ?></strong></p>
            </div>
        <?php endif; ?>
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
