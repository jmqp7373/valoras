<?php
/**
 * Master Layout - Valora.vip
 * Layout base reutilizable para todas las vistas del sistema
 * 
 * Variables requeridas que deben definirse ANTES de incluir este archivo:
 * 
 * @var string $page_title - Título de la página para <title>
 * @var string $content - Contenido HTML de la página (capturado con ob_start/ob_get_clean)
 * 
 * Variables opcionales para header.php:
 * @var string $logo_path - Ruta al logo (default: relativa desde la vista)
 * @var string $home_path - Ruta al dashboard (default: relativa desde la vista)
 * @var string $profile_path - Ruta al perfil (default: #)
 * @var string $settings_path - Ruta a configuración (default: relativa)
 * @var string $logout_path - Ruta al logout (default: relativa)
 * @var string $user_nombres - Nombre del usuario (tomado de $_SESSION)
 * @var string $user_apellidos - Apellidos del usuario (tomado de $_SESSION)
 * 
 * Variables opcionales para breadcrumbs.php:
 * @var array $breadcrumbs - Array de breadcrumbs: [['label' => 'Dashboard', 'url' => 'index.php'], ...]
 * 
 * Variables opcionales para headerTitulo.php:
 * @var string $titulo_pagina - Título grande de la página
 * @var string $subtitulo_pagina - Subtítulo opcional
 * 
 * Variables opcionales para CSS/JS adicionales:
 * @var array $additional_css - Array de rutas de archivos CSS adicionales
 * @var array $additional_js - Array de rutas de archivos JS adicionales
 */

// Verificar que las variables obligatorias estén definidas
if (!isset($page_title)) {
    die("Error: \$page_title no está definido. Define esta variable antes de incluir master.php");
}

if (!isset($content)) {
    die("Error: \$content no está definido. Usa ob_start() y ob_get_clean() para capturar el contenido.");
}

// Asegurar que las variables de sesión estén disponibles
if (!isset($user_nombres)) $user_nombres = $_SESSION['user_nombres'] ?? '';
if (!isset($user_apellidos)) $user_apellidos = $_SESSION['user_apellidos'] ?? '';

// FALLBACK: Si los datos de sesión están vacíos pero hay user_id, obtenerlos de la BD
if ((empty($user_nombres) || empty($user_apellidos)) && isset($_SESSION['user_id'])) {
    try {
        $db_connection = getDBConnection();
        $stmt = $db_connection->prepare("
            SELECT u.nombres, u.apellidos, ui.email 
            FROM usuarios u 
            LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
            WHERE u.id_usuario = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $user_nombres = $_SESSION['user_nombres'] = $user_data['nombres'];
            $user_apellidos = $_SESSION['user_apellidos'] = $user_data['apellidos'];
            if (!isset($_SESSION['user_email']) && !empty($user_data['email'])) {
                $_SESSION['user_email'] = $user_data['email'];
            }
        }
    } catch (Exception $e) {
        error_log("Error obteniendo datos de usuario en master.php: " . $e->getMessage());
    }
}

// Variables opcionales con valores por defecto
if (!isset($additional_css)) $additional_css = [];
if (!isset($additional_js)) $additional_js = [];

// Calcular base_path automáticamente si no está definido
if (!isset($base_path)) {
    // Detectar la profundidad basándonos en logo_path
    if (isset($logo_path)) {
        $depth = substr_count($logo_path, '../');
        if ($depth >= 2) {
            $base_path = '../../';
        } elseif ($depth == 1) {
            $base_path = '../';
        } else {
            $base_path = '';
        }
    } else {
        // Default para vistas en views/*/
        $base_path = '../../';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Estilos globales del sistema -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>assets/css/styles.css">
    
    <!-- Estilos del master layout -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>assets/css/master.css">
    
    <!-- Estilos del footer -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>assets/css/footer.css?v=<?php echo filemtime(__DIR__ . '/../../assets/css/footer.css'); ?>">
    
    <!-- CSS adicionales específicos de la página -->
    <?php foreach ($additional_css as $css_file): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($css_file); ?>">
    <?php endforeach; ?>
</head>
<body>
    <!-- Header del sistema -->
    <?php include __DIR__ . '/../../components/header/header.php'; ?>
    
    <!-- Breadcrumbs (si están definidos) -->
    <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
        <div class="container-fluid" style="margin-top: 20px;">
            <?php include __DIR__ . '/../../components/header/breadcrumbs.php'; ?>
        </div>
    <?php endif; ?>
    
    <!-- Título de la página (si está definido) -->
    <?php if (isset($titulo_pagina) && !empty($titulo_pagina)): ?>
        <div class="container-fluid" style="margin-top: 20px;">
            <?php include __DIR__ . '/../../components/header/headerTitulo.php'; ?>
        </div>
    <?php endif; ?>
    
    <!-- Contenido principal de la página -->
    <?php echo $content; ?>
    
    <!-- Footer del sistema -->
    <?php include __DIR__ . '/../../components/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- Script para el menú de usuario del header -->
    <script src="<?php echo htmlspecialchars($base_path); ?>assets/js/header-dropdown.js?v=<?php echo time(); ?>"></script>
    
    <!-- Scripts adicionales específicos de la página -->
    <?php foreach ($additional_js as $js_file): ?>
        <script src="<?php echo htmlspecialchars($js_file); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
