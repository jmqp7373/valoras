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
if (!isset($settings_path)) $settings_path = 'views/usuario/configuracion.php'; // Ruta a configuración

// Obtener iniciales del usuario para el avatar
$iniciales = '';
if (!empty($user_nombres)) $iniciales .= strtoupper(substr($user_nombres, 0, 1));
if (!empty($user_apellidos)) $iniciales .= strtoupper(substr($user_apellidos, 0, 1));
if (empty($iniciales)) $iniciales = 'U';

// Calcular la ruta del CSS basándose en la misma lógica que $logo_path
// Esto asegura que el CSS use la misma profundidad que las imágenes
if (strpos($logo_path, '../../') === 0) {
    $css_path = '../../assets/css/dropdown-menu.css';
    $base_controller_path = '../../controllers/';
} elseif (strpos($logo_path, '../') === 0) {
    $css_path = '../assets/css/dropdown-menu.css';
    $base_controller_path = '../controllers/';
} else {
    $css_path = 'assets/css/dropdown-menu.css';
    $base_controller_path = 'controllers/';
}

// ============================================
// OBTENER MÓDULOS DINÁMICAMENTE DESDE LA BD
// ============================================
$modulos_por_categoria = [];
$es_superadmin = false;
$rol_actual = null;
$todos_roles = [];

try {
    // Detectar la conexión disponible
    $dbConnection = null;
    if (isset($pdo) && $pdo instanceof PDO) {
        $dbConnection = $pdo;
    } elseif (isset($db) && $db instanceof PDO) {
        $dbConnection = $db;
    } else {
        // Intentar crear conexión si no existe
        if (file_exists(__DIR__ . '/../../config/database.php')) {
            require_once __DIR__ . '/../../config/database.php';
            $dbConnection = getDBConnection();
        }
    }
    
    if ($dbConnection) {
        // Verificar si el usuario es Superadmin (nivel_orden = 1)
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id) {
            $stmt = $dbConnection->prepare("
                SELECT r.id, r.nombre, r.nivel_orden, u.nivel_orden as usuario_nivel_orden
                FROM usuarios u 
                JOIN roles r ON u.id_rol = r.id 
                WHERE u.id_usuario = ?
            ");
            $stmt->execute([$user_id]);
            $rol_actual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Usuario es Superadmin si:
            // 1. Tiene nivel_orden = 1 en usuarios O en roles
            // 2. O tiene un rol_original_id guardado en sesión (está en modo prueba)
            $es_superadmin_original = ($rol_actual && ($rol_actual['usuario_nivel_orden'] == 1 || $rol_actual['nivel_orden'] == 1));
            $esta_en_modo_prueba = isset($_SESSION['rol_original_id']);
            
            if ($es_superadmin_original || $esta_en_modo_prueba) {
                $es_superadmin = true;
                
                // Obtener todos los roles para el dropdown
                $stmt = $dbConnection->query("SELECT id, nombre, nivel_orden FROM roles ORDER BY nivel_orden ASC");
                $todos_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
        // ============================================
        // VERIFICACIÓN DE PERMISOS DE ACCESO AL MÓDULO
        // ============================================
        if ($user_id && $rol_actual) {
            // Obtener la ruta actual del archivo que está siendo ejecutado
            $archivo_actual = $_SERVER['PHP_SELF'] ?? '';
            
            error_log("[HEADER PERMISOS] Archivo actual: " . $archivo_actual);
            
            // Convertir la ruta a formato relativo desde views/
            // Ejemplo: /valora.vip/views/finanzas/finanzasDashboard.php -> views\finanzas\finanzasDashboard.php
            if (preg_match('/views\/(.+\.php)/', $archivo_actual, $matches)) {
                $ruta_relativa = 'views\\' . str_replace('/', '\\', $matches[1]);
                
                error_log("[HEADER PERMISOS] Ruta relativa detectada: " . $ruta_relativa);
                
                // Buscar el módulo en la BD
                $stmt = $dbConnection->prepare("SELECT id, clave, exento FROM modulos WHERE ruta_completa = ? LIMIT 1");
                $stmt->execute([$ruta_relativa]);
                $modulo_actual = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($modulo_actual) {
                    error_log("[HEADER PERMISOS] Módulo encontrado: " . $modulo_actual['clave'] . " | Exento: " . ($modulo_actual['exento'] ? 'SÍ' : 'NO'));
                    
                    // Si el módulo NO es exento, verificar permisos
                    if (!$modulo_actual['exento']) {
                        // Usar rol de prueba si existe, sino el rol real
                        $id_rol_a_verificar = $_SESSION['rol_prueba_id'] ?? $rol_actual['id'];
                        
                        error_log("[HEADER PERMISOS] Verificando permisos para rol ID: " . $id_rol_a_verificar);
                        
                        // Verificar si tiene permiso de VER este módulo
                        $stmt = $dbConnection->prepare("
                            SELECT puede_ver 
                            FROM roles_permisos 
                            WHERE id_rol = ? AND modulo = ?
                        ");
                        $stmt->execute([$id_rol_a_verificar, $modulo_actual['clave']]);
                        $permiso = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        error_log("[HEADER PERMISOS] Permiso encontrado: " . ($permiso ? 'SÍ' : 'NO') . " | Puede ver: " . ($permiso && $permiso['puede_ver'] ? 'SÍ' : 'NO'));
                        
                        // Si NO tiene permiso o el permiso está en 0, redirigir
                        if (!$permiso || !$permiso['puede_ver']) {
                            error_log("[HEADER PERMISOS] ❌ ACCESO DENEGADO - Redirigiendo a acceso_denegado.php");
                            
                            // Calcular ruta a acceso_denegado.php según profundidad actual
                            $profundidad = substr_count($archivo_actual, '/') - 2; // Restar /valora.vip/
                            $ruta_denegado = str_repeat('../', $profundidad) . 'views/admin/acceso_denegado.php';
                            
                            header('Location: ' . $ruta_denegado);
                            exit();
                        } else {
                            error_log("[HEADER PERMISOS] ✅ ACCESO PERMITIDO");
                        }
                    }
                } else {
                    error_log("[HEADER PERMISOS] ⚠️ Módulo NO encontrado en BD para ruta: " . $ruta_relativa);
                }
            } else {
                error_log("[HEADER PERMISOS] ⚠️ Ruta no coincide con patrón views/");
            }
        }
        
        // Obtener todos los módulos activos ordenados por categoría y orden
        $stmt = $dbConnection->prepare("
            SELECT clave, titulo, categoria, ruta_completa, icono 
            FROM modulos 
            WHERE activo = 1 AND exento = 0
            ORDER BY categoria, titulo
        ");
        $stmt->execute();
        $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar módulos por categoría
        foreach ($modulos as $modulo) {
            $categoria = $modulo['categoria'] ?? 'General';
            if (!isset($modulos_por_categoria[$categoria])) {
                $modulos_por_categoria[$categoria] = [];
            }
            $modulos_por_categoria[$categoria][] = $modulo;
        }
    }
} catch (Exception $e) {
    // Si hay error, continuar sin módulos dinámicos
    error_log("Error al cargar módulos en header: " . $e->getMessage());
}
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
    
    <div class="header-right">
        <!-- Dropdown de Máscara de Rol (componente separado) -->
        <?php include __DIR__ . '/dropdownMascara.php'; ?>
        
        <!-- Menú de usuario -->
        <div class="dropdown">
            <button class="user-menu-btn dropdown-toggle" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar"><?php echo htmlspecialchars($iniciales); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></span>
            </button>
        
        <ul class="dropdown-menu dropdown-menu-end user-dropdown-custom" aria-labelledby="userMenuDropdown">
            <li class="dropdown-header-custom">
                <div class="user-avatar-large"><?php echo htmlspecialchars($iniciales); ?></div>
                <div class="user-details">
                    <p class="user-full-name"><?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></p>
                    <p class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'usuario@valora.vip'); ?></p>
                </div>
            </li>
            
            <li><hr class="dropdown-divider"></li>
            
            <!-- Módulos dinámicos agrupados por categoría -->
            <?php if (!empty($modulos_por_categoria)): ?>
                <?php foreach ($modulos_por_categoria as $categoria => $modulos): ?>
                    <?php if ($categoria !== 'login'): // Ocultar módulos de login del menú ?>
                    <li class="dropdown-submenu">
                        <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <span class="category-name"><?php echo htmlspecialchars(ucfirst($categoria)); ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($modulos as $modulo): ?>
                                <?php
                                // Calcular ruta relativa desde el archivo actual
                                $ruta_modulo = $modulo['ruta_completa'];
                                // Si la ruta ya empieza con /, es absoluta desde el dominio
                                if (strpos($ruta_modulo, '/') !== 0 && strpos($ruta_modulo, 'http') !== 0) {
                                    // Convertir formato Windows a formato web
                                    $ruta_modulo = str_replace('\\', '/', $ruta_modulo);
                                    
                                    // Calcular profundidad basándose en $logo_path
                                    if (strpos($logo_path, '../../') === 0) {
                                        // Estamos a 2 niveles de profundidad (ej: views/admin/file.php)
                                        $ruta_modulo = '../../' . $ruta_modulo;
                                    } elseif (strpos($logo_path, '../') === 0) {
                                        // Estamos a 1 nivel de profundidad (ej: views/file.php)
                                        $ruta_modulo = '../' . $ruta_modulo;
                                    }
                                    // Si $logo_path no tiene ../, estamos en la raíz, no agregar nada
                                }
                                ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo htmlspecialchars($ruta_modulo); ?>">
                                        <?php if (!empty($modulo['icono'])): ?>
                                            <span style="margin-right: 8px;"><?php echo $modulo['icono']; ?></span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($modulo['titulo']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <li><hr class="dropdown-divider"></li>
            <?php endif; ?>
            
            <!-- Opción de Cerrar Sesión -->
            <li>
                <a class="dropdown-item text-danger" href="<?php echo htmlspecialchars($logout_path); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="margin-right: 8px;">
                        <path d="M10 3.5V2H2v12h8v-1.5H3.5v-9H10zM13.5 8l-3-3v2H6v2h4.5v2l3-3z"/>
                    </svg>
                    Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
    </div> <!-- Cierre de header-right -->
</header>

<!-- Dropdown Menu Styles -->
<link rel="stylesheet" href="<?php echo $css_path; ?>?v=<?php echo filemtime(__DIR__ . '/../../assets/css/dropdown-menu.css'); ?>">

<!-- Dropdown Máscara de Rol Styles -->
<?php if ($es_superadmin): ?>
<link rel="stylesheet" href="<?php echo str_replace('dropdown-menu.css', 'dropdownMascara.css', $css_path); ?>?v=<?php echo filemtime(__DIR__ . '/../../assets/css/dropdownMascara.css'); ?>">
<?php endif; ?>

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
        z-index: 100;
        overflow: visible;
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
        max-width: 180px;
        height: auto;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }
    
    .dashboard-header .logo:hover {
        opacity: 0.8;
    }
    
    /* Header Right - Contenedor de dropdowns */
    .header-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* User Menu Container */
    .dropdown {
        position: relative;
    }
    
    /* User Menu Button */
    .user-menu-btn {
        display: flex !important;
        align-items: center;
        gap: 0.75rem;
        background: transparent !important;
        border: 1px solid #E5E5E5 !important;
        padding: 0.5rem 1rem !important;
        border-radius: 50px !important;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }
    
    .user-menu-btn:hover {
        background-color: #f8f9fa !important;
        border-color: #6A1B1B !important;
    }
    
    .user-menu-btn::after {
        display: none !important;
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
    
    /* Dropdown Header Custom */
    .dropdown-header-custom {
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 2px solid #E5E5E5;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px 12px 0 0 !important;
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
        box-shadow: 0 4px 12px rgba(106, 27, 27, 0.2);
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
    
    /* Nota: Los estilos del dropdown están en assets/css/dropdown-menu.css */

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
    }
</style>

<script>
// ============================================
// MANEJO DE SUBMENÚS EN CASCADA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Manejar los submenús en cascada
    const dropdownSubmenus = document.querySelectorAll('.dropdown-submenu');
    
    dropdownSubmenus.forEach(function(submenu) {
        const toggle = submenu.querySelector('.dropdown-toggle');
        const submenuDropdown = submenu.querySelector('.dropdown-menu');
        
        if (toggle && submenuDropdown) {
            // Desktop: mostrar en hover
            submenu.addEventListener('mouseenter', function() {
                if (window.innerWidth > 768) {
                    submenuDropdown.classList.add('show');
                }
            });
            
            submenu.addEventListener('mouseleave', function() {
                if (window.innerWidth > 768) {
                    submenuDropdown.classList.remove('show');
                }
            });
            
            // Mobile: toggle en click
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    submenuDropdown.classList.toggle('show');
                }
            });
        }
    });
    
    // Cerrar submenús cuando se cierra el menú principal
    const mainDropdown = document.getElementById('userMenuDropdown');
    if (mainDropdown) {
        mainDropdown.addEventListener('hidden.bs.dropdown', function() {
            document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(submenu) {
                submenu.classList.remove('show');
            });
        });
    }
});
</script>

</header>
