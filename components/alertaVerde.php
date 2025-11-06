<?php
/**
 * Componente de Alerta Verde Reutilizable
 * Basado en la alerta de éxito de register.php
 * Compatible con el sistema de estilos de Valora.vip
 */

function alertaVerde($mensaje, $icono = "✨", $redireccionar = false, $urlRedireccion = "login.php", $tiempoRedireccion = 5000) {
    echo '<div class="alert alert-success" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 16px; border-radius: 12px; margin-bottom: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-family: \'Poppins\', sans-serif;">';
    echo '<div style="display: flex; align-items: center; justify-content: center; gap: 8px;">';
    echo '<span style="font-size: 18px;">' . $icono . '</span>';
    echo '<strong style="font-weight: 600;">Nombre sugerido por IA aplicado exitosamente</strong>';
    echo '</div>';
    
    if (!empty($mensaje)) {
        echo '<div style="margin-top: 8px; font-size: 14px; opacity: 0.9;">' . htmlspecialchars($mensaje) . '</div>';
    }
    
    if ($redireccionar && !empty($urlRedireccion)) {
        echo '<script>';
        echo 'setTimeout(function() {';
        echo 'window.location.href = "' . htmlspecialchars($urlRedireccion) . '";';
        echo '}, ' . intval($tiempoRedireccion) . ');';
        echo '</script>';
    }
    
    echo '</div>';
}

/**
 * Función específica para mostrar éxito en aplicación de nombre de IA
 */
function alertaNombreAplicado($nombreUsuario = "") {
    $mensaje = !empty($nombreUsuario) ? "Tu nuevo nombre de usuario '$nombreUsuario' ha sido configurado correctamente." : "";
    alertaVerde($mensaje, "✨", false);
}

/**
 * Función genérica de éxito con personalización completa
 */
function alertaExito($titulo, $mensaje = "", $icono = "✅", $redireccionar = false, $urlRedireccion = "login.php", $tiempoRedireccion = 5000) {
    echo '<div class="alert alert-success" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 16px; border-radius: 12px; margin-bottom: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-family: \'Poppins\', sans-serif;">';
    echo '<div style="display: flex; align-items: center; justify-content: center; gap: 8px;">';
    echo '<span style="font-size: 18px;">' . $icono . '</span>';
    echo '<strong style="font-weight: 600;">' . htmlspecialchars($titulo) . '</strong>';
    echo '</div>';
    
    if (!empty($mensaje)) {
        echo '<div style="margin-top: 8px; font-size: 14px; opacity: 0.9;">' . htmlspecialchars($mensaje) . '</div>';
    }
    
    if ($redireccionar && !empty($urlRedireccion)) {
        echo '<script>';
        echo 'setTimeout(function() {';
        echo 'window.location.href = "' . htmlspecialchars($urlRedireccion) . '";';
        echo '}, ' . intval($tiempoRedireccion) . ');';
        echo '</script>';
    }
    
    echo '</div>';
}
?>