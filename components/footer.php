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

// Cargar módulos activos desde base de datos
$modulos_footer = [];
try {
    $db_conn = isset($pdo) ? $pdo : (isset($db) ? $db : null);
    if ($db_conn) {
        $stmt = $db_conn->prepare("
            SELECT clave, ruta_completa, titulo, categoria, icono 
            FROM modulos 
            WHERE activo = 1 AND categoria != 'login'
            ORDER BY categoria, titulo
        ");
        $stmt->execute();
        $modulos_footer = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <!-- Sección Empresa -->
        <div class="footer-section">
            <h4><?php echo htmlspecialchars($company_name); ?></h4>
            <p><?php echo htmlspecialchars($company_tagline); ?></p>
        </div>
        
        <?php 
        // Mostrar categorías dinámicamente (máximo 3 columnas)
        $count = 0;
        $max_cols = 3;
        foreach ($footer_categorias as $categoria => $modulos): 
            if ($count >= $max_cols) break;
            $count++;
        ?>
        <div class="footer-section">
            <h4><?php echo ucfirst(htmlspecialchars($categoria)); ?></h4>
            <ul>
                <?php foreach ($modulos as $modulo): ?>
                <li>
                    <a href="<?php echo $base_path . htmlspecialchars($modulo['ruta_completa']); ?>">
                        <?php echo htmlspecialchars($modulo['icono'] ?? ''); ?> 
                        <?php echo htmlspecialchars($modulo['titulo']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Sección Copyright - Separada y centrada -->
    <div class="footer-copyright-section">
        <p class="footer-copyright">
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company_name); ?> - Todos los derechos reservados
        </p>
    </div>
</footer>
