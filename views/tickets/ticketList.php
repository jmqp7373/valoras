<?php
/**
 * Vista: Listado de Tickets de Soporte
 * 
 * Muestra todos los tickets creados por el usuario
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tickets de Soporte - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .tickets-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .tickets-header {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .tickets-header img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        
        .tickets-header h2 {
            color: #882A57;
            margin-bottom: 10px;
        }
        
        .tickets-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .alert-success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #3c3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #882A57, #ee6f92);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 42, 87, 0.4);
        }
        
        .btn-secondary {
            display: inline-block;
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .tickets-table th {
            background: linear-gradient(135deg, #882A57, #ee6f92);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .tickets-table th:first-child {
            border-radius: 8px 0 0 0;
        }
        
        .tickets-table th:last-child {
            border-radius: 0 8px 0 0;
        }
        
        .tickets-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tickets-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-abierto {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-en_proceso {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-resuelto {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cerrado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-view {
            padding: 6px 12px;
            background: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .btn-view:hover {
            background: #138496;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state img {
            max-width: 200px;
            opacity: 0.5;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .tickets-table {
                font-size: 14px;
            }
            
            .tickets-table th,
            .tickets-table td {
                padding: 10px;
            }
            
            .btn-primary,
            .btn-secondary {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="tickets-container">
        <div class="tickets-header">
            <img src="../../assets/images/logos/logoValoraHorizontal.png" alt="Valora Logo">
            <h2>📋 Mis Tickets de Soporte</h2>
            <p style="color: #666;">Bienvenido, <?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></p>
        </div>
        
        <div class="tickets-card">
            <?php if($success): ?>
                <div class="alert-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 20px;">
                <a href="ticketCreate.php" class="btn-primary">
                    ➕ Crear Nuevo Ticket
                </a>
                <a href="../../index.php" class="btn-secondary">
                    🏠 Volver al Inicio
                </a>
            </div>
            
            <?php if(empty($tickets)): ?>
                <div class="empty-state">
                    <div style="font-size: 80px; margin-bottom: 20px;">📭</div>
                    <h3>No tienes tickets creados</h3>
                    <p>Aún no has creado ningún ticket de soporte.</p>
                    <p>¿Necesitas ayuda? Crea tu primer ticket ahora.</p>
                    <br>
                    <a href="ticketCreate.php" class="btn-primary">
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
                                    <a href="ticketView.php?id=<?php echo $ticket['id']; ?>" class="btn-view">
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
</body>
</html>
