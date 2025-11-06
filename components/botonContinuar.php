<?php
/**
 * Componente de Botón Primario Reutilizable
 * Mantiene el diseño y comportamiento del botón verde "Continuar con el Registro"
 * Compatible con el sistema de estilos existente de Valora.vip
 */

function primaryButton($text, $icon = "✅", $link = "#", $id = "", $onclick = "", $type = "button", $style = "") {
    // Generar ID único si no se proporciona
    $buttonId = !empty($id) ? $id : 'btn-' . uniqid();
    
    // Determinar si es un enlace o botón
    if ($type === "link" && $link !== "#") {
        // Renderizar como enlace
        echo "<a href='{$link}' class='btn-primary' id='{$buttonId}' style='{$style}'>
            {$icon} {$text}
        </a>";
    } else {
        // Renderizar como botón
        $onclickAttr = !empty($onclick) ? "onclick='{$onclick}'" : "";
        
        echo "<button type='{$type}' class='btn-primary' id='{$buttonId}' {$onclickAttr} style='{$style}'>
            {$icon} {$text}
        </button>";
    }
}

/**
 * Función específica para el botón de continuar registro
 * Mantiene exactamente la funcionalidad del paso 3
 */
function continueRegistrationButton($username = "", $display = "none") {
    $onclick = "if (selectedUsername) { window.location.href = `register.php?suggested_username=\${encodeURIComponent(selectedUsername)}`; }";
    
    primaryButton(
        "Continuar con el Registro",
        "✅",
        "#",
        "continueBtn",
        $onclick,
        "button",
        "display: {$display};"
    );
}
?>