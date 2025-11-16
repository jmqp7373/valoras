<?php
/**
 * Valora.vip - Dashboard Principal
 * Deploy Test: Nov 5, 2025 - FTP Credentials Updated
 */
require_once 'config/database.php';
require_once 'controllers/FinanzasController.php';
startSessionSafely();

// Obtener datos financieros para el resumen
$finanzasController = new FinanzasController();
$totalesFinanzas = $finanzasController->calcularTotales();

// Verificar si el usuario está logueado
if(!isLoggedIn()) {
    header('Location: views/login/login.php');
    exit();
}

// Obtener información del usuario
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$user_cedula = $_SESSION['user_cedula'] ?? '';

// ============================================
// OBTENER INFORMACIÓN DEL MÓDULO DESDE LA BD
// ============================================
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT titulo, subtitulo, icono FROM modulos WHERE ruta_completa = ? AND activo = 1 LIMIT 1");
    $stmt->execute(['index.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Dashboard - Valora";

// Título, subtítulo e icono desde la base de datos
$titulo_pagina = $modulo['titulo'] ?? 'Dashboard Principal';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Bienvenido a tu panel de control';
$icono_pagina = $modulo['icono'] ?? '🏠';

// Variables para header.php
$logo_path = 'assets/images/logos/logoValoraHorizontal.png';
$home_path = 'index.php';
$profile_path = 'views/usuario/miPerfil.php';
$settings_path = 'views/usuario/configuracion.php';
$logout_path = 'controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => null]
];

// CSS adicional específico de esta página
$additional_css = [];

// JS adicional específico de esta página
$additional_js = ['https://cdn.jsdelivr.net/npm/chart.js'];

// ============================================
// CAPTURAR CONTENIDO DE LA PÁGINA
// ============================================
ob_start();
?>

            <div class="welcome-section">
                <div style="margin-top: 2rem; text-align: center;">
                    <a href="views/tickets/ticketCreate.php" 
                       style="background: linear-gradient(135deg, #882A57, #ee6f92); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px;">
                        🎫 Crear Ticket de Soporte
                    </a>
                    <a href="views/tickets/ticketList.php" 
                       style="background: #17a2b8; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px;">
                        📋 Ver Mis Tickets
                    </a>
                    <a href="views/ventas/ventasStripchat.php" 
                       style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px; font-weight: 600;">
                        💰 Importación Stripchat
                    </a>
                    <a href="views/admin/permissionsPanel.php" 
                       style="background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px; font-weight: 600;">
                        ⚙️ Administración de Permisos
                    </a>
                    <a href="views/finanzas/finanzasDashboard.php" 
                       style="background: #222222; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px; font-weight: 600;">
                        💰 Gestión de Finanzas Completa
                    </a>
                </div>
            </div>

    <style>
        /* Reset de estilos del body para permitir scroll */
        body {
            display: block !important;
            height: auto !important;
            min-height: 100vh;
            overflow-y: auto !important;
            padding: 0 !important;
        }

        .dashboard-container {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        .welcome-section {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .user-details {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        /* Estilos para resumen financiero */
        .finanzas-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .finanza-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s;
        }

        .finanza-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .finanza-card.ingreso {
            border-left: 5px solid #28a745;
        }

        .finanza-card.gasto {
            border-left: 5px solid #dc3545;
        }

        .finanza-card.balance {
            border-left: 5px solid #222222;
        }

        .finanza-icon {
            font-size: 3rem;
        }

        .finanza-info {
            display: flex;
            flex-direction: column;
        }

        .finanza-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        .finanza-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #222222;
        }

        @media (max-width: 768px) {
            .finanzas-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>

<?php
// Capturar el contenido generado
$content = ob_get_clean();

// Incluir el master layout
include __DIR__ . '/views/layouts/master.php';
?>