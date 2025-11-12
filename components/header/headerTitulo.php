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
        <div class="btn-group" role="group" aria-label="Botones de vista">
            <?php foreach ($botones as $index => $boton): ?>
                <?php 
                $btnId = $boton['id'] ?? 'btn' . $index;
                $btnLabel = $boton['label'] ?? 'Botón ' . ($index + 1);
                $isActive = $boton['active'] ?? false;
                $btnClass = $isActive ? 'btn btn-primary active' : 'btn btn-secondary';
                $btnStyle = $isActive 
                    ? 'background: linear-gradient(135deg, #6A1B1B, #882A57); border: none; padding: 10px 30px;'
                    : 'padding: 10px 30px;';
                ?>
                <button type="button" 
                        class="<?= $btnClass ?>" 
                        id="<?= htmlspecialchars($btnId) ?>" 
                        style="<?= $btnStyle ?>">
                    <?= $btnLabel ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
