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
 * - $profile_path: (Opcional) Ruta al perfil del usuario
 * - $home_path: (Opcional) Ruta al dashboard principal
 */

// Verificar que las variables necesarias estén definidas
if (!isset($user_nombres)) $user_nombres = '';
if (!isset($user_apellidos)) $user_apellidos = '';
if (!isset($logo_path)) $logo_path = 'assets/images/logos/logoValoraHorizontal.png';
if (!isset($logout_path)) $logout_path = 'controllers/login/logout.php';
if (!isset($profile_path)) $profile_path = '#'; // Ruta por defecto
if (!isset($home_path)) $home_path = 'index.php'; // Ruta por defecto al home

// Obtener iniciales del usuario para el avatar
$iniciales = '';
if (!empty($user_nombres)) $iniciales .= strtoupper(substr($user_nombres, 0, 1));
if (!empty($user_apellidos)) $iniciales .= strtoupper(substr($user_apellidos, 0, 1));
if (empty($iniciales)) $iniciales = 'U';
?>
<header class="dashboard-header">
    <div class="header-left">
        <a href="<?php echo htmlspecialchars($home_path); ?>" class="home-link" title="Ir al Dashboard">
            <svg class="home-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
        </a>
        <a href="<?php echo htmlspecialchars($home_path); ?>" title="Ir al Dashboard">
            <img src="<?php echo htmlspecialchars($logo_path); ?>" class="logo" alt="Valora Logo">
        </a>
    </div>
    
    <div class="user-menu-container">
        <button class="user-menu-btn" id="userMenuBtn" aria-label="Menú de usuario">
            <div class="user-avatar"><?php echo htmlspecialchars($iniciales); ?></div>
            <span class="user-name"><?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></span>
            <svg class="dropdown-icon" width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                <path d="M6 9L1 4h10L6 9z"/>
            </svg>
        </button>
        
        <div class="user-dropdown" id="userDropdown">
            <div class="dropdown-header">
                <div class="user-avatar-large"><?php echo htmlspecialchars($iniciales); ?></div>
                <div class="user-details">
                    <p class="user-full-name"><?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></p>
                    <p class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'usuario@valora.vip'); ?></p>
                </div>
            </div>
            
            <div class="dropdown-divider"></div>
            
            <ul class="dropdown-menu">
                <li>
                    <a href="<?php echo htmlspecialchars($profile_path); ?>" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M8 8a3 3 0 100-6 3 3 0 000 6zm0 1.5c-2.67 0-8 1.34-8 4v1.5h16v-1.5c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span>Mi Perfil</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($logout_path); ?>" class="dropdown-item logout">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M10 3.5V2H2v12h8v-1.5H3.5v-9H10zM13.5 8l-3-3v2H6v2h4.5v2l3-3z"/>
                        </svg>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>
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
    
    /* Header Left - Logo y Home */
    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .home-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
        border-radius: 10px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(106, 27, 27, 0.2);
    }
    
    .home-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3);
        background: linear-gradient(135deg, #882A57 0%, #6A1B1B 100%);
    }
    
    .home-link:active {
        transform: translateY(0);
    }
    
    .home-icon {
        width: 24px;
        height: 24px;
    }
    
    .dashboard-header .logo {
        height: 40px;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }
    
    .dashboard-header .logo:hover {
        opacity: 0.8;
    }
    
    /* User Menu Container */
    .user-menu-container {
        position: relative;
    }
    
    /* User Menu Button */
    .user-menu-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: transparent;
        border: 1px solid #E5E5E5;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }
    
    .user-menu-btn:hover {
        background-color: #f8f9fa;
        border-color: #6A1B1B;
    }
    
    /* User Avatar */
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6A1B1B, #882A57);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .user-name {
        color: #222222;
        font-size: 0.95rem;
        font-weight: 500;
    }
    
    .dropdown-icon {
        transition: transform 0.3s;
        color: #666;
    }
    
    .user-menu-btn:hover .dropdown-icon {
        color: #6A1B1B;
    }
    
    /* Dropdown Menu */
    .user-dropdown {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        min-width: 280px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1001;
    }
    
    .user-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    /* Dropdown Header */
    .dropdown-header {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .user-avatar-large {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6A1B1B, #882A57);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
    }
    
    .user-details {
        flex: 1;
    }
    
    .user-full-name {
        margin: 0;
        font-weight: 600;
        color: #222222;
        font-size: 1rem;
        font-family: 'Poppins', sans-serif;
    }
    
    .user-email {
        margin: 0.25rem 0 0 0;
        font-size: 0.85rem;
        color: #666;
        font-family: 'Poppins', sans-serif;
    }
    
    /* Dropdown Divider */
    .dropdown-divider {
        height: 1px;
        background-color: #E5E5E5;
        margin: 0;
    }
    
    /* Dropdown Menu Items */
    .dropdown-menu {
        list-style: none;
        margin: 0;
        padding: 0.5rem 0;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        color: #222222;
        text-decoration: none;
        transition: background-color 0.2s;
        font-family: 'Poppins', sans-serif;
        font-size: 0.95rem;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .dropdown-item.logout {
        color: #dc3545;
    }
    
    .dropdown-item.logout:hover {
        background-color: #fff5f5;
    }
    
    .dropdown-item svg {
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1rem;
        }
        
        .user-name {
            display: none;
        }
        
        .user-menu-btn {
            padding: 0.5rem;
        }
        
        .user-dropdown {
            right: -1rem;
            min-width: 260px;
        }
    }
</style>

<script>
    // Dropdown functionality
    (function() {
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userMenuBtn && userDropdown) {
            // Toggle dropdown
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
                
                // Rotate icon
                const icon = this.querySelector('.dropdown-icon');
                if (icon) {
                    icon.style.transform = userDropdown.classList.contains('show') 
                        ? 'rotate(180deg)' 
                        : 'rotate(0deg)';
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('show');
                    const icon = userMenuBtn.querySelector('.dropdown-icon');
                    if (icon) {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            });
            
            // Close dropdown when pressing Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && userDropdown.classList.contains('show')) {
                    userDropdown.classList.remove('show');
                    const icon = userMenuBtn.querySelector('.dropdown-icon');
                    if (icon) {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            });
        }
    })();
</script>
