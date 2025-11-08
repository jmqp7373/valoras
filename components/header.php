<?php
/**
 * Header Component - Valora.vip
 * Componente reutilizable para el encabezado del dashboard
 * 
 * Variables requeridas:
 * - $user_nombres: Nombre del usuario
 * - $user_apellidos: Apellidos del usuario
 * - $logo_path: Ruta relativa al logo (ej: 'assets/images/logos/logoValoraHorizontal.png')
 * - $logout_path: Ruta relativa al script de logout (ej: 'controllers/login/logout.php')
 */

// Verificar que las variables necesarias estén definidas
if (!isset($user_nombres)) $user_nombres = '';
if (!isset($user_apellidos)) $user_apellidos = '';
if (!isset($logo_path)) $logo_path = 'assets/images/logos/logoValoraHorizontal.png';
if (!isset($logout_path)) $logout_path = 'controllers/login/logout.php';
?>
<header class="dashboard-header">
    <img src="<?php echo htmlspecialchars($logo_path); ?>" class="logo" alt="Valora Logo">
    <div class="user-info">
        <span>Bienvenido, <?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></span>
        <a href="<?php echo htmlspecialchars($logout_path); ?>" class="logout-btn">Cerrar Sesión</a>
    </div>
</header>

<style>
    .dashboard-header {
        background-color: white;
        padding: 1rem 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .dashboard-header .logo {
        height: 40px;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-family: 'Poppins', sans-serif;
    }
    
    .logout-btn {
        background-color: #ee6f92;
        color: white;
        padding: 0.5rem 1rem;
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.3s;
        font-family: 'Poppins', sans-serif;
    }
    
    .logout-btn:hover {
        background-color: #d63384;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }

        .user-info {
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }

        .logout-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>
