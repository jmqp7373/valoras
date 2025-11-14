<?php
/**
 * Componente de Migas de Pan (Breadcrumbs)
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-11
 * 
 * Uso:
 * $breadcrumbs = [
 *     ['label' => 'Dashboard', 'url' => '../../index.php'],
 *     ['label' => 'Nombre de la página actual', 'url' => null] // null indica página actual
 * ];
 * include __DIR__ . '/../../components/header/breadcrumbs.php';
 */

// Verificar que existe la variable $breadcrumbs
if (!isset($breadcrumbs) || !is_array($breadcrumbs) || empty($breadcrumbs)) {
    return;
}
?>

<nav aria-label="breadcrumb" class="mb-4">
    <div class="breadcrumb-wrapper" style="
        background: #e9ecef;
        border-radius: 8px;
        padding: 12px 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    ">
        <ol class="breadcrumb m-0" style="background: none; padding: 0; font-size: 14px;">
            <?php foreach ($breadcrumbs as $index => $item): ?>
                <?php 
                $isLast = ($index === count($breadcrumbs) - 1);
                $label = htmlspecialchars($item['label'] ?? '');
                $url = $item['url'] ?? null;
                ?>
                
                <?php if ($isLast || $url === null): ?>
                    <!-- Elemento activo (último o sin URL) -->
                    <li class="breadcrumb-item active" aria-current="page" style="
                        color: #666;
                        font-weight: 600;
                    ">
                        <?= $label ?>
                    </li>
                <?php else: ?>
                    <!-- Elemento con enlace -->
                    <li class="breadcrumb-item">
                        <a href="<?= htmlspecialchars($url) ?>" style="
                            color: #6A1B1B;
                            text-decoration: none;
                            font-weight: 500;
                            transition: all 0.2s ease;
                        " 
                        onmouseover="this.style.color='#882A57'; this.style.textDecoration='underline';"
                        onmouseout="this.style.color='#6A1B1B'; this.style.textDecoration='none';">
                            <?= $label ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<style>
    /* Separador personalizado para las migas de pan */
    .breadcrumb-wrapper .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-weight: 600;
        color: #6A1B1B;
        padding: 0 8px;
    }
    
    /* Eliminar el separador por defecto de Bootstrap si está presente */
    .breadcrumb-wrapper .breadcrumb-item + .breadcrumb-item {
        padding-left: 0;
    }
</style>
