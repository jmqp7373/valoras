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
            border-radius: 0;
            box-shadow: none;
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
                    $borderRadius = '8px';
                } elseif ($isFirst) {
                    $borderRadius = '8px 0 0 8px';
                } elseif ($isLast) {
                    $borderRadius = '0 8px 8px 0';
                } else {
                    $borderRadius = '0';
                }
                ?>
                <button type="button" 
                        class="header-view-btn <?= $isActive ? 'active' : '' ?>" 
                        id="<?= htmlspecialchars($btnId) ?>" 
                        style="<?= $isActive 
                            ? 'background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%); color: white; border: none; padding: 14px 32px; border-radius: ' . $borderRadius . '; font-weight: 500; font-size: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(106, 27, 27, 0.25); font-family: \'Poppins\', sans-serif; letter-spacing: 0.3px; position: relative; z-index: ' . ($isActive ? '2' : '1') . ';'
                            : 'background: linear-gradient(135deg, #8B8B8B 0%, #A8A8A8 100%); color: white; border: none; padding: 14px 32px; border-radius: ' . $borderRadius . '; font-weight: 500; font-size: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); font-family: \'Poppins\', sans-serif; letter-spacing: 0.3px; opacity: 0.85; position: relative; z-index: 1;' 
                        ?>"
                        onmouseover="<?= $isActive 
                            ? 'this.style.boxShadow=\'0 4px 12px rgba(106, 27, 27, 0.35)\'; this.style.transform=\'translateY(-1px)\';' 
                            : 'this.style.opacity=\'1\'; this.style.boxShadow=\'0 4px 12px rgba(0, 0, 0, 0.25)\'; this.style.transform=\'translateY(-1px)\';' 
                        ?>"
                        onmouseout="<?= $isActive 
                            ? 'this.style.boxShadow=\'0 2px 8px rgba(106, 27, 27, 0.25)\'; this.style.transform=\'translateY(0)\';' 
                            : 'this.style.opacity=\'0.85\'; this.style.boxShadow=\'0 2px 8px rgba(0, 0, 0, 0.15)\'; this.style.transform=\'translateY(0)\';' 
                        ?>">
                    <?= $btnLabel ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Estilos mejorados para los botones del header - estilo unificado */
    .header-buttons-container {
        animation: fadeInUp 0.5s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
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
        border: none !important;
    }
    
    .header-view-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .header-view-btn:active::before {
        width: 300px;
        height: 300px;
    }
    
    .header-view-btn:focus {
        outline: none;
    }
    
    .header-view-btn.active {
        box-shadow: 0 2px 8px rgba(106, 27, 27, 0.25) !important;
    }
</style>
