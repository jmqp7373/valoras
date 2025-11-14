<?php
/**
 * Componente de Cabecera de Página con Título y Botones de Vista Dinámicos
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-12
 * 
 * NUEVA FUNCIONALIDAD: Generación automática de botones dinámicos
 * Si no se proporcionan botones, el componente automáticamente:
 * 1. Detecta el módulo actual basado en la ruta del archivo
 * 2. Obtiene la categoría del módulo
 * 3. Genera botones para todos los módulos de la misma categoría
 * 
 * Uso Opción 1 (Array):
 * $pageHeader = [
 *     'titulo' => 'Título de la Página',
 *     'icono' => '⚙️',  // Opcional
 *     'descripcion' => 'Descripción de la página',  // Opcional
 *     'botones' => [  // Opcional - Si se omite, se generan automáticamente
 *         [
 *             'id' => 'btnRoles',
 *             'label' => '🧩 Permisos por Rol',
 *             'active' => true
 *         ]
 *     ],
 *     'auto_botones' => true  // Opcional - Fuerza generación automática aunque existan botones
 * ];
 * 
 * Uso Opción 2 (Variables individuales - para master.php):
 * $titulo_pagina = 'Título de la Página';
 * $subtitulo_pagina = 'Descripción de la página';  // Opcional
 * $icono_pagina = '⚙️';  // Opcional
 * $botones_pagina = [];  // Si está vacío o no existe, se generan automáticamente
 * $auto_botones = true;  // Opcional - Fuerza generación automática
 * 
 * include __DIR__ . '/../../components/header/headerTitulo.php';
 */

// Soportar dos formatos: array $pageHeader O variables individuales
if (isset($pageHeader) && is_array($pageHeader)) {
    // Formato 1: Array (mantener compatibilidad con código existente)
    $titulo = $pageHeader['titulo'] ?? 'Sin Título';
    $icono = $pageHeader['icono'] ?? '';
    $descripcion = $pageHeader['descripcion'] ?? '';
    $botones = $pageHeader['botones'] ?? [];
    $autoBotones = $pageHeader['auto_botones'] ?? false;
} elseif (isset($titulo_pagina)) {
    // Formato 2: Variables individuales (para master.php)
    $titulo = $titulo_pagina;
    $icono = $icono_pagina ?? '';
    $descripcion = $subtitulo_pagina ?? '';
    $botones = $botones_pagina ?? [];
    $autoBotones = $auto_botones ?? false;
} else {
    // No hay datos, no mostrar nada
    return;
}

// ============================================
// GENERACIÓN AUTOMÁTICA DE BOTONES DINÁMICOS
// ============================================
// Soportar tanto $db como $pdo para diferentes archivos
$dbConnection = $db ?? $pdo ?? null;

if ((empty($botones) || $autoBotones) && $dbConnection !== null) {
    try {
        // Obtener la ruta del archivo actual
        $rutaActual = $_SERVER['PHP_SELF'] ?? '';
        $rutaActual = str_replace('/', '\\', $rutaActual);
        
        // Generar rutas alternativas para buscar
        $rutasAlternativas = [];
        if ($rutaActual) {
            // Ruta completa desde la raíz del proyecto
            $rutasAlternativas[] = ltrim($rutaActual, '\\/');
            // Ruta sin el primer directorio (views\...)
            $rutasAlternativas[] = preg_replace('#^[^/\\\\]+[/\\\\]#', '', ltrim($rutaActual, '\\/'));
            // Solo la parte views\...
            if (preg_match('#(views[/\\\\].+)#', $rutaActual, $matches)) {
                $rutasAlternativas[] = str_replace('/', '\\', $matches[1]);
            }
        }
        
        $moduloActual = null;
        $categoriaActual = null;
        $rutaModuloActual = '';
        
        // Buscar el módulo actual
        foreach ($rutasAlternativas as $ruta) {
            $stmt = $dbConnection->prepare("SELECT titulo, categoria, ruta_completa FROM modulos WHERE ruta_completa = ? AND activo = 1 LIMIT 1");
            $stmt->execute([$ruta]);
            $moduloActual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($moduloActual && !empty($moduloActual['titulo'])) {
                $categoriaActual = $moduloActual['categoria'];
                $rutaModuloActual = $moduloActual['ruta_completa'];
                break;
            }
        }
        
        // Si encontramos el módulo y su categoría, generar botones
        if ($categoriaActual) {
            $botones = [];
            $stmt = $dbConnection->prepare("
                SELECT titulo, ruta_completa 
                FROM modulos 
                WHERE categoria = ? 
                AND activo = 1
                ORDER BY ruta_completa ASC
            ");
            $stmt->execute([$categoriaActual]);
            $modulosCategoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($modulosCategoria as $moduloItem) {
                // Extraer nombre del archivo sin extensión
                $nombreArchivo = basename($moduloItem['ruta_completa'], '.php');
                
                // Determinar si es el módulo actual
                $esActivo = ($moduloItem['ruta_completa'] === $rutaModuloActual);
                
                // Calcular URL relativa desde el archivo actual
                $dirActual = dirname($rutaActual);
                $dirModulo = dirname($moduloItem['ruta_completa']);
                
                // Si están en el mismo directorio, solo nombre de archivo
                if (str_replace('/', '\\', $dirActual) === str_replace('/', '\\', $dirModulo)) {
                    $urlModulo = './' . $nombreArchivo . '.php';
                } else {
                    // Calcular ruta relativa
                    $urlModulo = '../../' . str_replace('\\', '/', $moduloItem['ruta_completa']);
                }
                
                $botones[] = [
                    'id' => 'btn' . ucfirst($nombreArchivo),
                    'label' => $moduloItem['titulo'] ?: ucfirst($nombreArchivo),
                    'active' => $esActivo,
                    'url' => $urlModulo
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error generando botones dinámicos en headerTitulo.php: " . $e->getMessage());
        // Si hay error, mantener botones vacíos o los proporcionados
    }
}
?>

<div class="header-titulo-wrapper" style="
    background: #e9ecef;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
">
    <div class="text-center">
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
                    $btnUrl = $boton['url'] ?? '#';
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
                            <?= !$isActive ? 'onclick="window.location.href=\'' . htmlspecialchars($btnUrl) . '\'"' : '' ?>
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
