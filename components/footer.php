<?php
/**
 * Footer Component - Valora.vip
 * Componente reutilizable para el pie de página del dashboard
 * 
 * Variables requeridas:
 * - $pdo o $db: Conexión a base de datos
 * - $base_path: Ruta base relativa (opcional, por defecto '')
 * - $company_name: Nombre de la empresa (opcional, por defecto 'Valora.vip')
 * - $company_tagline: Tagline de la empresa (opcional)
 * - $css_path: Ruta a assets/css/ (opcional, se calcula automáticamente)
 */

// Valores por defecto
if (!isset($company_name)) {
    $company_name = 'Valora.vip';
}
if (!isset($company_tagline)) {
    $company_tagline = 'Plataforma de gestión empresarial';
}
if (!isset($base_path)) {
    $base_path = '';
}

// Calcular ruta CSS si no está definida (similar a header.php)
if (!isset($css_path)) {
    $depth = substr_count($base_path, '../');
    if ($depth >= 2) {
        $css_path = '../../assets/css/';
    } elseif ($depth == 1) {
        $css_path = '../assets/css/';
    } else {
        $css_path = 'assets/css/';
    }
}

// Cargar módulos activos desde base de datos CON PERMISOS
$modulos_footer = [];
try {
    $db_conn = isset($pdo) ? $pdo : (isset($db) ? $db : null);
    if ($db_conn) {
        // Obtener el rol del usuario actual
        $user_id = $_SESSION['user_id'] ?? null;
        $id_rol = null;
        
        if ($user_id) {
            $stmt = $db_conn->prepare("SELECT id_rol FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_rol = $result['id_rol'] ?? null;
        }
        
        if ($id_rol) {
            // Obtener solo módulos con permiso de ver para el rol actual
            $stmt = $db_conn->prepare("
                SELECT m.clave, m.ruta_completa, m.titulo, m.categoria, m.icono
                FROM modulos m
                LEFT JOIN roles_permisos rp ON m.clave = rp.modulo AND rp.id_rol = ?
                WHERE m.activo = 1 AND m.exento = 0 AND m.categoria != 'login' AND rp.puede_ver = 1
                ORDER BY m.categoria, m.titulo
            ");
            $stmt->execute([$id_rol]);
            $modulos_footer = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    error_log("Error cargando módulos para footer: " . $e->getMessage());
}

// Agrupar por categoría
$footer_categorias = [];
foreach ($modulos_footer as $modulo) {
    $cat = $modulo['categoria'];
    if (!isset($footer_categorias[$cat])) {
        $footer_categorias[$cat] = [];
    }
    $footer_categorias[$cat][] = $modulo;
}
?>

<footer class="dashboard-footer">
    <div class="footer-content">
        <?php 
        // Mostrar TODAS las categorías dinámicamente
        foreach ($footer_categorias as $categoria => $modulos): 
        ?>
        <div class="footer-section">
            <h4><?php echo ucfirst(htmlspecialchars($categoria)); ?></h4>
            <ul>
                <?php foreach ($modulos as $modulo): ?>
                <li>
                    <a href="<?php echo $base_path . htmlspecialchars($modulo['ruta_completa']); ?>">
                        <?php if (!empty($modulo['icono'])): ?>
                            <span class="footer-icon"><?php echo $modulo['icono']; ?></span>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($modulo['titulo']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Sección Copyright con info de empresa integrada -->
    <div class="footer-copyright-section">
        <p class="footer-copyright">
            <strong><?php echo htmlspecialchars($company_name); ?></strong> - <?php echo htmlspecialchars($company_tagline); ?>
            <br>
            &copy; <?php echo date('Y'); ?> Todos los derechos reservados
        </p>
    </div>
</footer>
