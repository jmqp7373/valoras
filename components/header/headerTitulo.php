<?php
/**
 * Componente de Cabecera de Página con Título y Botones de Vista
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-11
 * 
 * Uso:
 * $pageHeader = [
 *     'titulo' => 'Título de la Página',
 *     'icono' => '⚙️',  // Opcional
 *     'descripcion' => 'Descripción de la página',  // Opcional
 *     'botones' => [  // Opcional - array de botones de vista
 *         [
 *             'id' => 'btnRoles',
 *             'label' => '🧩 Permisos por Rol',
 *             'active' => true
 *         ],
 *         [
 *             'id' => 'btnUsuarios',
 *             'label' => '👤 Permisos Individuales',
 *             'active' => false
 *         ]
 *     ]
 * ];
 * include __DIR__ . '/../../components/header/headerTitulo.php';
 */

// Verificar que existe la variable $pageHeader
if (!isset($pageHeader) || !is_array($pageHeader)) {
    return;
}

// Extraer datos con valores por defecto
$titulo = $pageHeader['titulo'] ?? 'Sin Título';
$icono = $pageHeader['icono'] ?? '';
$descripcion = $pageHeader['descripcion'] ?? '';
$botones = $pageHeader['botones'] ?? [];
?>

<div class="text-center mb-5">
    <h2 class="fw-bold text-uppercase mb-2" style="color: #6A1B1B; letter-spacing: 1px;">
        <?php if ($icono): ?>
            <?= $icono ?> 
        <?php endif; ?>
        <?= htmlspecialchars($titulo) ?>
    </h2>
    
    <?php if ($descripcion): ?>
        <p class="text-muted mb-4">
            <?= htmlspecialchars($descripcion) ?>
        </p>
    <?php endif; ?>
    
    <?php if (!empty($botones)): ?>
        <!-- Botones de Vista -->
        <div class="header-buttons-container" style="
            display: inline-flex;
            gap: 0;
            background: transparent;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        ">
            <?php foreach ($botones as $index => $boton): ?>
                <?php 
                $btnId = $boton['id'] ?? 'btn' . $index;
                $btnLabel = $boton['label'] ?? 'Botón ' . ($index + 1);
                $isActive = $boton['active'] ?? false;
                $isFirst = $index === 0;
                $isLast = $index === count($botones) - 1;
                
                // Determinar border-radius según posición
                $borderRadius = '';
                if ($isFirst && $isLast) {
                    $borderRadius = '10px';
                } elseif ($isFirst) {
                    $borderRadius = '10px 0 0 10px';
                } elseif ($isLast) {
                    $borderRadius = '0 10px 10px 0';
                } else {
                    $borderRadius = '0';
                }
                ?>
                <button type="button" 
                        class="header-view-btn <?= $isActive ? 'active' : '' ?>" 
                        id="<?= htmlspecialchars($btnId) ?>" 
                        style="<?= $isActive 
                            ? 'background: linear-gradient(135deg, #6A1B1B 0%, #8B2E57 100%); color: white; border: none; padding: 12px 28px; border-radius: ' . $borderRadius . '; font-weight: 400; font-size: 14px; cursor: pointer; transition: all 0.3s ease; box-shadow: none; font-family: system-ui, -apple-system, sans-serif; letter-spacing: 0.2px; position: relative; z-index: 2; min-width: 200px; text-align: center;'
                            : 'background: #D8D8D8; color: #5A5A5A; border: none; padding: 12px 28px; border-radius: ' . $borderRadius . '; font-weight: 400; font-size: 14px; cursor: pointer; transition: all 0.3s ease; box-shadow: none; font-family: system-ui, -apple-system, sans-serif; letter-spacing: 0.2px; position: relative; z-index: 1; min-width: 200px; text-align: center;' 
                        ?>"
                        onmouseover="<?= $isActive 
                            ? 'this.style.background=\'linear-gradient(135deg, #7A2B2B 0%, #9B3E67 100%)\';' 
                            : 'this.style.background=\'#C8C8C8\';' 
                        ?>"
                        onmouseout="<?= $isActive 
                            ? 'this.style.background=\'linear-gradient(135deg, #6A1B1B 0%, #8B2E57 100%)\';' 
                            : 'this.style.background=\'#D8D8D8\';' 
                        ?>">
                    <?= $btnLabel ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Estilos para botones estilo segmented control como la imagen */
    .header-buttons-container {
        animation: fadeInUp 0.4s ease;
        display: inline-flex;
        border-radius: 10px;
        overflow: hidden;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .header-view-btn {
        position: relative;
        overflow: hidden;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        border: none !important;
        outline: none !important;
    }
    
    .header-view-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        transform: translate(-50%, -50%);
        transition: width 0.5s, height 0.5s;
    }
    
    .header-view-btn:active::before {
        width: 250px;
        height: 250px;
    }
    
    .header-view-btn:focus {
        outline: none;
    }
</style>
