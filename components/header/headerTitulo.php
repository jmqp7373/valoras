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
            gap: 12px;
            padding: 8px;
            background: rgba(106, 27, 27, 0.05);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        ">
            <?php foreach ($botones as $index => $boton): ?>
                <?php 
                $btnId = $boton['id'] ?? 'btn' . $index;
                $btnLabel = $boton['label'] ?? 'Botón ' . ($index + 1);
                $isActive = $boton['active'] ?? false;
                ?>
                <button type="button" 
                        class="header-view-btn <?= $isActive ? 'active' : '' ?>" 
                        id="<?= htmlspecialchars($btnId) ?>" 
                        style="<?= $isActive 
                            ? 'background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; border: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3); transform: translateY(-2px);'
                            : 'background: white; color: #6A1B1B; border: 2px solid #E5E5E5; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);' 
                        ?>"
                        onmouseover="<?= $isActive 
                            ? 'this.style.boxShadow=\'0 6px 16px rgba(106, 27, 27, 0.4)\'; this.style.transform=\'translateY(-3px)\';' 
                            : 'this.style.borderColor=\'#6A1B1B\'; this.style.boxShadow=\'0 4px 12px rgba(106, 27, 27, 0.15)\'; this.style.transform=\'translateY(-2px)\';' 
                        ?>"
                        onmouseout="<?= $isActive 
                            ? 'this.style.boxShadow=\'0 4px 12px rgba(106, 27, 27, 0.3)\'; this.style.transform=\'translateY(-2px)\';' 
                            : 'this.style.borderColor=\'#E5E5E5\'; this.style.boxShadow=\'0 2px 4px rgba(0, 0, 0, 0.08)\'; this.style.transform=\'translateY(0)\';' 
                        ?>"
                        onclick="<?= !$isActive 
                            ? 'this.style.background=\'linear-gradient(135deg, #6A1B1B, #882A57)\'; this.style.color=\'white\'; this.style.border=\'none\'; this.style.transform=\'translateY(-2px)\';' 
                            : '' 
                        ?>">
                    <?= $btnLabel ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Estilos mejorados para los botones del header */
    .header-buttons-container {
        animation: fadeInUp 0.5s ease;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .header-view-btn {
        position: relative;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
        letter-spacing: 0.3px;
    }
    
    .header-view-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.5s, height 0.5s;
    }
    
    .header-view-btn:active::before {
        width: 300px;
        height: 300px;
    }
    
    .header-view-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(106, 27, 27, 0.2);
    }
    
    .header-view-btn:not(.active):hover {
        background: linear-gradient(135deg, rgba(106, 27, 27, 0.05), rgba(136, 42, 87, 0.05)) !important;
    }
</style>
