<?php
/**
 * Componente: Marca Pasos (Step Indicator)
 * 
 * Sistema de indicador visual de pasos para formularios multi-etapa
 * 
 * @author Valora.vip
 * @version 1.0.0
 * 
 * USO:
 * 1. Incluir este archivo: <?php include '../../components/marcaPasos.php'; ?>
 * 2. Incluir el CSS: <link rel="stylesheet" href="../../components/marcaPasos.css">
 * 3. Llamar la función: <?php renderMarcaPasos(2, 3); ?> // (paso actual, total de pasos)
 */

/**
 * Renderiza el indicador de pasos (marca pasos)
 * 
 * @param int $currentStep Paso actual (1, 2, 3, etc.)
 * @param int $totalSteps Total de pasos en el proceso
 * @param array $stepLabels (Opcional) Etiquetas personalizadas para cada paso
 * 
 * @example
 * // Ejemplo básico con números
 * renderMarcaPasos(2, 3);
 * 
 * // Ejemplo con etiquetas personalizadas
 * renderMarcaPasos(2, 3, ['Upload', 'Verify', 'Update']);
 */
function renderMarcaPasos($currentStep, $totalSteps, $stepLabels = []) {
    // Validar parámetros
    if ($currentStep < 1) $currentStep = 1;
    if ($currentStep > $totalSteps) $currentStep = $totalSteps;
    if ($totalSteps < 1) $totalSteps = 1;
    
    echo '<div class="steps-container">';
    
    for ($i = 1; $i <= $totalSteps; $i++) {
        // Determinar estado del paso
        $stepClass = 'step';
        if ($i < $currentStep) {
            $stepClass .= ' completed';
        } elseif ($i == $currentStep) {
            $stepClass .= ' active';
        }
        
        // Determinar etiqueta (número o texto personalizado)
        $label = isset($stepLabels[$i - 1]) ? $stepLabels[$i - 1] : $i;
        
        // Renderizar paso
        echo '<div class="' . $stepClass . '">' . htmlspecialchars($label) . '</div>';
        
        // Renderizar línea conectora (excepto después del último paso)
        if ($i < $totalSteps) {
            $lineClass = 'step-line';
            if ($i < $currentStep) {
                $lineClass .= ' completed';
            } elseif ($i == $currentStep - 1) {
                $lineClass .= ' active';
            }
            echo '<div class="' . $lineClass . '"></div>';
        }
    }
    
    echo '</div>';
}

/**
 * Renderiza marca pasos con configuración avanzada
 * 
 * @param array $config Configuración del marca pasos
 *   - currentStep: int (requerido) Paso actual
 *   - totalSteps: int (requerido) Total de pasos
 *   - labels: array (opcional) Etiquetas personalizadas
 *   - showTitle: bool (opcional) Mostrar título del paso actual
 *   - titles: array (opcional) Títulos para cada paso
 * 
 * @example
 * renderMarcaPasosAdvanced([
 *     'currentStep' => 2,
 *     'totalSteps' => 3,
 *     'labels' => ['📸', '🔍', '✏️'],
 *     'showTitle' => true,
 *     'titles' => ['Subir Documento', 'Análisis OCR', 'Actualizar Datos']
 * ]);
 */
function renderMarcaPasosAdvanced($config) {
    $currentStep = $config['currentStep'] ?? 1;
    $totalSteps = $config['totalSteps'] ?? 1;
    $labels = $config['labels'] ?? [];
    $showTitle = $config['showTitle'] ?? false;
    $titles = $config['titles'] ?? [];
    
    // Renderizar título si está habilitado
    if ($showTitle && isset($titles[$currentStep - 1])) {
        echo '<div style="text-align: center; margin-bottom: 15px;">';
        echo '<h2 style="color: #882A57; font-size: 22px; margin: 0;">';
        echo htmlspecialchars($titles[$currentStep - 1]);
        echo '</h2>';
        echo '</div>';
    }
    
    // Renderizar marca pasos
    renderMarcaPasos($currentStep, $totalSteps, $labels);
}
?>
