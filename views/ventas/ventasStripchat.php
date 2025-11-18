<?php
/**
 * Vista: Importación y Control de Ventas Stripchat - Basado en Períodos
 * 
 * Importa períodos completos de Stripchat (currentPayment)
 * y reconstruye ventas diarias internamente usando deltas de totales acumulados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/VentasController.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// Inicializar controlador
try {
    $db = getDBConnection();
    $ventasController = new VentasController($db);
} catch (Exception $e) {
    die('Error al conectar con la base de datos: ' . $e->getMessage());
}

// Obtener cuentas estudio activas de Stripchat
$stmt = $db->prepare("
    SELECT DISTINCT 
        ce.id_cuenta_estudio,
        ce.usuario_cuenta_estudio
    FROM cuentas_estudios ce
    WHERE ce.id_pagina = 3 
    AND ce.estado = 1
    ORDER BY ce.usuario_cuenta_estudio
");
$stmt->execute();
$cuentas_stripchat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener período actual y totales por cuenta estudio
$datos_periodo = [];
$periodo_global = null;
$total_general = 0;

foreach ($cuentas_stripchat as $cuenta) {
    // Obtener período más reciente
    $stmt = $db->prepare("
        SELECT 
            MIN(vs.period_start) as periodo_inicio,
            MAX(vs.period_end) as periodo_fin,
            SUM(vs.total_earnings) as total_acumulado,
            COUNT(DISTINCT vs.id_credencial) as num_modelos
        FROM ventas_strip vs
        INNER JOIN credenciales c ON c.id_credencial = vs.id_credencial
        WHERE c.id_cuenta_estudio = :id_cuenta_estudio
        AND c.id_pagina = 3
        AND vs.period_start >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([':id_cuenta_estudio' => $cuenta['id_cuenta_estudio']]);
    $periodo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_cuenta = floatval($periodo['total_acumulado'] ?? 0);
    $total_general += $total_cuenta;
    
    $datos_periodo[$cuenta['usuario_cuenta_estudio']] = [
        'id' => $cuenta['id_cuenta_estudio'],
        'periodo_inicio' => $periodo['periodo_inicio'],
        'periodo_fin' => $periodo['periodo_fin'],
        'total_acumulado' => $total_cuenta,
        'num_modelos' => intval($periodo['num_modelos'] ?? 0)
    ];
    
    // Actualizar período global (tomar el más amplio)
    if ($periodo['periodo_inicio'] && $periodo['periodo_fin']) {
        if (!$periodo_global) {
            $periodo_global = [
                'inicio' => $periodo['periodo_inicio'],
                'fin' => $periodo['periodo_fin']
            ];
        } else {
            // Expandir el período global si es necesario
            if ($periodo['periodo_inicio'] < $periodo_global['inicio']) {
                $periodo_global['inicio'] = $periodo['periodo_inicio'];
            }
            if ($periodo['periodo_fin'] > $periodo_global['fin']) {
                $periodo_global['fin'] = $periodo['periodo_fin'];
            }
        }
    }
}

// Obtener ventas diarias reconstruidas
$ventas_diarias_total = [];

foreach ($cuentas_stripchat as $cuenta) {
    // Obtener snapshots cronológicos por modelo
    $stmt = $db->prepare("
        SELECT 
            vs.id_credencial,
            DATE(vs.updated_at) as fecha_snapshot,
            vs.total_earnings,
            vs.updated_at
        FROM ventas_strip vs
        INNER JOIN credenciales c ON c.id_credencial = vs.id_credencial
        WHERE c.id_cuenta_estudio = :id_cuenta_estudio
        AND c.id_pagina = 3
        AND vs.period_start >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY vs.id_credencial, vs.updated_at ASC
    ");
    $stmt->execute([':id_cuenta_estudio' => $cuenta['id_cuenta_estudio']]);
    $snapshots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular diferencias diarias
    $modeloAnterior = null;
    $totalAnterior = 0;
    
    foreach ($snapshots as $snapshot) {
        if ($modeloAnterior !== $snapshot['id_credencial']) {
            $totalAnterior = 0;
            $modeloAnterior = $snapshot['id_credencial'];
        }
        
        $fecha = $snapshot['fecha_snapshot'];
        $diferencia = $snapshot['total_earnings'] - $totalAnterior;
        
        if (!isset($ventas_diarias_total[$fecha])) {
            $ventas_diarias_total[$fecha] = [];
            foreach ($cuentas_stripchat as $c) {
                $ventas_diarias_total[$fecha][$c['usuario_cuenta_estudio']] = 0;
            }
        }
        
        $ventas_diarias_total[$fecha][$cuenta['usuario_cuenta_estudio']] += $diferencia;
        $totalAnterior = $snapshot['total_earnings'];
    }
}

// Ordenar fechas descendentes
krsort($ventas_diarias_total);

// Variables para header
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Consultar información del módulo
try {
    $stmt = $db->prepare("
        SELECT titulo, subtitulo, icono 
        FROM modulos 
        WHERE ruta_completa = ?
    ");
    $stmt->execute(['views\ventas\ventasStripchat.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $modulo = null;
}

// Variables para master.php
$page_title = $modulo['titulo'] ?? 'Ventas Stripchat';
$titulo_pagina = $modulo['titulo'] ?? 'Importación y Control de Ventas Stripchat';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Sistema basado en períodos de pago con reconstrucción diaria interna';
$icono_pagina = $modulo['icono'] ?? '💰';

// Breadcrumbs
$breadcrumbs = [
    ['label' => 'Home', 'url' => '../../index.php'],
    ['label' => 'Ventas Stripchat', 'url' => '']
];

$additional_css = [];
$additional_js = [];

ob_start();
?>

<style>
/* ESTILOS COMPACTOS - DASHBOARD FINANCIERO */
.ventas-stripchat-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 10px;
}

.header-periodo {
    background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 60px;
}

.periodo-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
}

.periodo-info p {
    margin: 0;
    font-size: 12px;
    opacity: 0.9;
}

.btn-importar-periodo {
    background: white;
    color: #6A1B1B;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-importar-periodo:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}

.btn-importar-periodo:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.totales-cuentas {
    background: white;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.totales-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
}

.cuenta-total-card {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 6px;
    border-left: 3px solid #882A57;
}

.cuenta-total-card .nombre {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}

.cuenta-total-card .total {
    font-size: 20px;
    font-weight: 700;
    color: #333;
}

.cuenta-total-card .modelos {
    font-size: 10px;
    color: #999;
    margin-top: 2px;
}

.ventas-diarias-section {
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.ventas-diarias-section h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 700;
    color: #333;
}

.tabla-ventas {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.tabla-ventas thead {
    background: #f8f9fa;
    border-bottom: 2px solid #882A57;
}

.tabla-ventas thead th {
    padding: 8px 10px;
    text-align: left;
    font-weight: 700;
    color: #333;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tabla-ventas thead th:first-child {
    border-radius: 6px 0 0 0;
}

.tabla-ventas thead th:last-child {
    border-radius: 0 6px 0 0;
    text-align: right;
}

.tabla-ventas thead th.text-right {
    text-align: right;
}

.tabla-ventas tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background 0.15s ease;
}

.tabla-ventas tbody tr:hover {
    background: #f8f9fa;
}

.tabla-ventas tbody tr:last-child {
    border-bottom: none;
}

.tabla-ventas tbody td {
    padding: 10px;
    color: #333;
}

.tabla-ventas tbody td:first-child {
    font-weight: 600;
    color: #666;
}

.tabla-ventas tbody td.text-right {
    text-align: right;
}

.tabla-ventas tbody td.positivo {
    color: #28a745;
    font-weight: 700;
}

.tabla-ventas tbody td.cero {
    color: #ccc;
}

.tabla-ventas tfoot {
    background: #f8f9fa;
    border-top: 2px solid #882A57;
}

.tabla-ventas tfoot td {
    padding: 10px;
    font-weight: 700;
    color: #333;
    font-size: 13px;
}

.tabla-ventas tfoot td:last-child {
    text-align: right;
    color: #6A1B1B;
}

.sin-datos {
    padding: 40px;
    text-align: center;
    color: #999;
    font-size: 13px;
}

#mensajeImportacion {
    margin-top: 10px;
}

.alert {
    padding: 10px 14px;
    border-radius: 6px;
    margin: 8px 0;
    font-size: 12px;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

@media (max-width: 768px) {
    .header-periodo {
        flex-direction: column;
        gap: 10px;
        min-height: auto;
    }
    
    .totales-grid {
        grid-template-columns: 1fr;
    }
    
    .tabla-ventas {
        font-size: 11px;
    }
    
    .tabla-ventas thead th,
    .tabla-ventas tbody td {
        padding: 6px 8px;
    }
}
</style>

<div class="ventas-stripchat-container">
    
    <!-- Panel de Debugging en Tiempo Real -->
    <div id="debugPanel" style="display: block; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; margin-bottom: 15px; font-family: 'Consolas', monospace; font-size: 12px; max-height: 500px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 8px;">
            <div>
                <h4 style="margin: 0; color: #4EC9B0; font-size: 14px;">🔍 Debug Console - Import Engine Monitor</h4>
                <small id="debugStatus" style="color: #858585; font-size: 10px;">Esperando inicio de proceso...</small>
            </div>
            <div style="display: flex; gap: 8px;">
                <button id="btnCopyDebug" style="background: #2196F3; color: #fff; border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;">📋 Copiar</button>
                <button id="btnClearDebug" style="background: #555; color: #fff; border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;">🗑️ Limpiar</button>
            </div>
        </div>
        <div id="debugStats" style="display: flex; gap: 15px; margin-bottom: 10px; padding: 8px; background: #252525; border-radius: 4px; font-size: 11px;">
            <div><span style="color: #858585;">⏱️ Tiempo total:</span> <strong id="totalTime" style="color: #4EC9B0;">0s</strong></div>
            <div><span style="color: #858585;">📊 Eventos:</span> <strong id="eventCount" style="color: #4EC9B0;">0</strong></div>
            <div><span style="color: #858585;">✅ Éxitos:</span> <strong id="successCount" style="color: #4CAF50;">0</strong></div>
            <div><span style="color: #858585;">❌ Errores:</span> <strong id="errorCount" style="color: #F44336;">0</strong></div>
        </div>
        <div id="debugContent" style="line-height: 1.8;">
            <div id="welcomeMessage" style="text-align: center; padding: 40px 20px; animation: fadeInOut 3s ease-in-out infinite;">
                <div style="font-size: 48px; margin-bottom: 15px;">👇</div>
                <div style="color: #4EC9B0; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    ¡Bienvenido al Monitor de Importación!
                </div>
                <div style="color: #858585; font-size: 12px; line-height: 1.6;">
                    Haz clic en el botón <strong style="color: #4EC9B0;">"📥 Importar Período Actual"</strong> de abajo<br>
                    para comenzar a importar las ventas desde Stripchat.<br><br>
                    <span style="color: #2196F3;">Aquí podrás ver en tiempo real todo el proceso de importación.</span>
                </div>
            </div>
        </div>
    </div>

<style>
@keyframes fadeInOut {
    0%, 100% {
        opacity: 0.4;
        transform: scale(0.98);
    }
    50% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
    
    <!-- Header con período actual -->
    <div class="header-periodo">
        <div class="periodo-info">
            <h3>📊 Período Actual de Pago</h3>
            <?php if ($periodo_global && $periodo_global['inicio'] && $periodo_global['fin']): ?>
                <p>
                    Desde: <?= date('d/m/Y H:i', strtotime($periodo_global['inicio'])) ?> 
                    hasta: <?= date('d/m/Y H:i', strtotime($periodo_global['fin'])) ?>
                </p>
            <?php elseif (!empty($cuentas_stripchat)): ?>
                <p>⚠️ No hay importaciones recientes. Haz clic en "Importar" para comenzar.</p>
            <?php else: ?>
                <p>⚠️ No hay cuentas de Stripchat configuradas en el sistema.</p>
            <?php endif; ?>
        </div>
        <button type="button" class="btn-importar-periodo" id="btnImportarPeriodo" 
                <?= empty($cuentas_stripchat) ? 'disabled' : '' ?>>
            📥 Importar Período Actual
        </button>
    </div>
    
    <div id="mensajeImportacion"></div>
    
    <!-- Totales por cuenta estudio -->
    <?php if (!empty($datos_periodo)): ?>
    <div class="totales-cuentas">
        <div class="totales-grid">
            <?php foreach ($datos_periodo as $nombre_cuenta => $datos): ?>
            <div class="cuenta-total-card">
                <div class="nombre">🏢 <?= htmlspecialchars($nombre_cuenta) ?></div>
                <div class="total">$<?= number_format($datos['total_acumulado'], 2) ?></div>
                <div class="modelos"><?= $datos['num_modelos'] ?> modelo<?= $datos['num_modelos'] != 1 ? 's' : '' ?></div>
            </div>
            <?php endforeach; ?>
            
            <div class="cuenta-total-card" style="border-left-color: #6A1B1B;">
                <div class="nombre">💰 TOTAL GENERAL</div>
                <div class="total" style="color: #6A1B1B;">$<?= number_format($total_general, 2) ?></div>
                <div class="modelos"><?= count($cuentas_stripchat) ?> cuenta<?= count($cuentas_stripchat) != 1 ? 's' : '' ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Tabla de ventas diarias reconstruidas -->
    <?php if (!empty($ventas_diarias_total)): ?>
    <div class="ventas-diarias-section">
        <h4>📅 Ventas Diarias del Período (Reconstruidas Internamente)</h4>
        <table class="tabla-ventas">
            <thead>
                <tr>
                    <th>Día</th>
                    <?php foreach ($cuentas_stripchat as $cuenta): ?>
                    <th class="text-right"><?= htmlspecialchars($cuenta['usuario_cuenta_estudio']) ?></th>
                    <?php endforeach; ?>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_diarias_total as $fecha => $ventas_dia): ?>
                <?php 
                    $total_dia = array_sum($ventas_dia);
                    if ($total_dia == 0) continue; // Saltar días sin ventas
                ?>
                <tr>
                    <td><?= date('D, d/m/Y', strtotime($fecha)) ?></td>
                    <?php foreach ($cuentas_stripchat as $cuenta): ?>
                    <?php $valor = $ventas_dia[$cuenta['usuario_cuenta_estudio']] ?? 0; ?>
                    <td class="text-right <?= $valor > 0 ? 'positivo' : 'cero' ?>">
                        $<?= number_format($valor, 2) ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="text-right" style="font-weight: 700; color: #6A1B1B;">
                        $<?= number_format($total_dia, 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL ACUMULADO</td>
                    <?php foreach ($cuentas_stripchat as $cuenta): ?>
                    <td class="text-right">
                        $<?= number_format($datos_periodo[$cuenta['usuario_cuenta_estudio']]['total_acumulado'] ?? 0, 2) ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="text-right">
                        $<?= number_format($total_general, 2) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="ventas-diarias-section">
        <div class="sin-datos">
            ⚠️ No hay datos de ventas disponibles.<br>
            Haz clic en "Importar Período Actual" para comenzar.
        </div>
    </div>
    <?php endif; ?>
    
</div>

<script>
// Variables globales para tracking
let debugStartTime = null;
let eventCounter = 0;
let successCounter = 0;
let errorCounter = 0;
let lastEventTime = null;

// Botones de control del debug panel
document.getElementById('btnCopyDebug')?.addEventListener('click', function() {
    const debugContent = document.getElementById('debugContent');
    if (!debugContent) return;
    
    // Extraer solo el texto sin HTML
    const textContent = debugContent.innerText || debugContent.textContent;
    
    // Copiar al portapapeles
    navigator.clipboard.writeText(textContent).then(function() {
        // Feedback visual
        const btn = document.getElementById('btnCopyDebug');
        const originalText = btn.textContent;
        btn.textContent = '✅ Copiado!';
        btn.style.background = '#4CAF50';
        
        setTimeout(function() {
            btn.textContent = originalText;
            btn.style.background = '#2196F3';
        }, 2000);
    }).catch(function(err) {
        alert('Error al copiar: ' + err);
    });
});

document.getElementById('btnClearDebug')?.addEventListener('click', function() {
    document.getElementById('debugContent').innerHTML = '';
    eventCounter = 0;
    successCounter = 0;
    errorCounter = 0;
    updateDebugStats();
});

// Actualizar estadísticas
function updateDebugStats() {
    document.getElementById('eventCount').textContent = eventCounter;
    document.getElementById('successCount').textContent = successCounter;
    document.getElementById('errorCount').textContent = errorCounter;
    
    if (debugStartTime) {
        const elapsed = ((Date.now() - debugStartTime) / 1000).toFixed(2);
        document.getElementById('totalTime').textContent = elapsed + 's';
    }
}

// Actualizar status
function updateDebugStatus(message) {
    const statusEl = document.getElementById('debugStatus');
    if (statusEl) {
        statusEl.textContent = message;
    }
}

// Función mejorada para agregar log al debug panel
function addDebugLog(message, type = 'info', details = null) {
    const debugContent = document.getElementById('debugContent');
    const debugPanel = document.getElementById('debugPanel');
    
    if (!debugContent) return;
    
    const now = Date.now();
    const currentTime = new Date();
    
    // Calcular tiempo desde último evento
    let deltaTime = '';
    if (lastEventTime) {
        const delta = ((now - lastEventTime) / 1000).toFixed(3);
        deltaTime = ` <span style="color: #858585;">(+${delta}s)</span>`;
    }
    lastEventTime = now;
    
    // Incrementar contadores
    eventCounter++;
    if (type === 'success') successCounter++;
    if (type === 'error') errorCounter++;
    
    const colors = {
        'info': '#4EC9B0',
        'success': '#4CAF50',
        'error': '#F44336',
        'warning': '#FF9800',
        'api': '#2196F3',
        'data': '#9C27B0',
        'time': '#FFC107'
    };
    
    const icons = {
        'info': 'ℹ️',
        'success': '✅',
        'error': '❌',
        'warning': '⚠️',
        'api': '🌐',
        'data': '📊',
        'time': '⏱️'
    };
    
    const timestamp = currentTime.toLocaleTimeString('es-ES', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit', fractionalSecondDigits: 3 });
    const color = colors[type] || colors.info;
    const icon = icons[type] || icons.info;
    
    let logHtml = `<div style="margin-bottom: 5px; border-left: 3px solid ${color}; padding-left: 8px; padding-top: 3px; padding-bottom: 3px;">
        <span style="color: #858585;">[${timestamp}]</span>${deltaTime} 
        <span style="color: ${color};">${icon} ${message}</span>`;
    
    // Agregar detalles expandibles si existen
    if (details) {
        const detailId = 'detail_' + eventCounter;
        logHtml += `
        <div style="margin-top: 4px;">
            <button onclick="document.getElementById('${detailId}').style.display = document.getElementById('${detailId}').style.display === 'none' ? 'block' : 'none'" 
                    style="background: #333; color: #999; border: none; padding: 2px 8px; border-radius: 3px; cursor: pointer; font-size: 10px;">
                📋 Ver detalles
            </button>
            <pre id="${detailId}" style="display: none; background: #2d2d2d; padding: 8px; border-radius: 4px; margin-top: 4px; font-size: 10px; overflow-x: auto; max-height: 300px; border-left: 2px solid ${color};">${JSON.stringify(details, null, 2)}</pre>
        </div>`;
    }
    
    logHtml += `</div>`;
    debugContent.innerHTML += logHtml;
    
    // Actualizar stats
    updateDebugStats();
    
    // Mostrar panel y scroll automático
    debugPanel.style.display = 'block';
    debugPanel.scrollTop = debugPanel.scrollHeight;
}

// Importar período actual con debugging mejorado
document.getElementById('btnImportarPeriodo')?.addEventListener('click', async function() {
    const btn = this;
    const mensajeDiv = document.getElementById('mensajeImportacion');
    const debugContent = document.getElementById('debugContent');
    const welcomeMessage = document.getElementById('welcomeMessage');
    
    // Ocultar mensaje de bienvenida
    if (welcomeMessage) {
        welcomeMessage.style.display = 'none';
    }
    
    // Resetear variables de tracking
    debugStartTime = Date.now();
    lastEventTime = debugStartTime;
    eventCounter = 0;
    successCounter = 0;
    errorCounter = 0;
    
    // Limpiar debug panel
    if (debugContent) {
        debugContent.innerHTML = '';
    }
    
    btn.disabled = true;
    btn.textContent = '⏳ Importando...';
    
    updateDebugStatus('🚀 Proceso en ejecución...');
    addDebugLog('🚀 Iniciando proceso de importación', 'info');
    addDebugLog('Solicitando análisis previo de cuentas y modelos...', 'info');
    
    mensajeDiv.innerHTML = '<div class="alert alert-info">⏳ Analizando configuración...</div>';
    
    try {
        // Paso 1: Debug info
        const step1Start = Date.now();
        addDebugLog('Consultando estado del sistema...', 'api');
        
        const debugResponse = await fetch('debug_import_stripchat.php');
        const debugData = await debugResponse.json();
        
        const step1Duration = ((Date.now() - step1Start) / 1000).toFixed(2);
        addDebugLog(`Análisis completado en ${step1Duration}s`, 'time');
        
        if (debugData.error) {
            addDebugLog('Error en análisis: ' + debugData.mensaje, 'error', debugData);
        } else {
            addDebugLog(`Cuentas en BD: ${debugData.cuentas_en_bd}`, 'data');
            addDebugLog(`Cuentas activas validadas: ${debugData.cuentas_activas_validadas}`, 'data');
            
            if (debugData.cuentas_activas_detalle) {
                debugData.cuentas_activas_detalle.forEach(cuenta => {
                    addDebugLog(`  → ${cuenta.nombre_bd} (${cuenta.studio_username})`, 'data');
                });
            }
            
            // Mostrar modelos por cuenta
            if (debugData.modelos_por_cuenta) {
                let totalModelos = 0;
                for (const [cuenta, info] of Object.entries(debugData.modelos_por_cuenta)) {
                    if (info.error) {
                        addDebugLog(`❌ Error en ${cuenta}: ${info.error}`, 'error');
                    } else {
                        totalModelos += info.total;
                        const batches = Math.ceil(info.total / 50);
                        addDebugLog(`${cuenta}: ${info.total} modelos (${batches} lotes)`, 'data', info.muestra);
                    }
                }
                const totalBatches = Math.ceil(totalModelos / 50);
                addDebugLog(`📦 Total: ${totalModelos} modelos en ${totalBatches} lotes de 50`, 'info');
            }
            
            if (debugData.url_prueba) {
                addDebugLog(`URL de API generada`, 'api', { url: debugData.url_prueba, api_key: debugData.api_key_preview });
            }
        }
        
        // Paso 2: Importación real
        const step2Start = Date.now();
        addDebugLog('Iniciando importación desde API de Stripchat...', 'api');
        updateDebugStatus('🌐 Conectando con API de Stripchat...');
        mensajeDiv.innerHTML = '<div class="alert alert-info">⏳ Conectando con API de Stripchat...</div>';
        
        const response = await fetch('../../controllers/VentasController.php?action=importarPeriodoActual', {
            method: 'GET'
        });
        
        const data = await response.json();
        
        const step2Duration = ((Date.now() - step2Start) / 1000).toFixed(2);
        addDebugLog(`Importación completada en ${step2Duration}s`, 'time');
        addDebugLog('Respuesta recibida del servidor', 'api');
        
        if (data.success) {
            addDebugLog('✅ Importación exitosa', 'success');
            if (data.totales) {
                addDebugLog(`Resultados: Nuevos: ${data.totales.nuevos}, Actualizados: ${data.totales.actualizados}, Sin cambios: ${data.totales.sin_cambios || 0}`, 'success');
                addDebugLog(`Cuentas procesadas: ${data.totales.cuentas_procesadas}, Modelos procesados: ${data.totales.modelos_procesados}`, 'data', {
                    cuentas_procesadas: data.totales.cuentas_procesadas,
                    modelos_procesados: data.totales.modelos_procesados,
                    nuevos: data.totales.nuevos,
                    actualizados: data.totales.actualizados,
                    sin_cambios: data.totales.sin_cambios || 0,
                    total_errores: data.totales.errores ? data.totales.errores.length : 0
                });
                
                if (data.totales.errores && data.totales.errores.length > 0) {
                    addDebugLog(`⚠️ ${data.totales.errores.length} errores encontrados`, 'warning', data.totales.errores);
                }
                
                if (data.resultados_por_cuenta) {
                    // Mostrar progreso de lotes para cada cuenta
                    for (const [cuenta, resultado] of Object.entries(data.resultados_por_cuenta)) {
                        if (resultado.success && resultado.batch_progress) {
                            addDebugLog(`📊 Progreso de ${cuenta}:`, 'info');
                            resultado.batch_progress.forEach(batch => {
                                addDebugLog(`  Lote ${batch.batch}/${batch.total_batches} (${batch.progress_percent}%): ${batch.nuevos} nuevos, ${batch.actualizados} actualizados, ${batch.sin_cambios} sin cambios`, 'data');
                            });
                        }
                    }
                    
                    addDebugLog('Desglose por cuenta:', 'info', data.resultados_por_cuenta);
                }
            }
            
            const totalDuration = ((Date.now() - debugStartTime) / 1000).toFixed(2);
            addDebugLog(`⏱️ Proceso total completado en ${totalDuration}s`, 'time');
            updateDebugStatus(`✅ Completado en ${totalDuration}s - Panel permanece abierto para inspección`);
            
            mensajeDiv.innerHTML = `
                <div class="alert alert-success">
                    ✅ ${data.message}
                    <br><small><a href="javascript:window.location.reload()" style="color: #6A1B1B; text-decoration: underline;">Haz clic aquí para recargar</a> o revisa el debug primero</small>
                </div>
            `;
            
            // Habilitar botón para permitir nueva importación
            btn.disabled = false;
            btn.textContent = '📥 Importar Período Actual';
        } else {
            addDebugLog('❌ Error en importación: ' + data.message, 'error', data);
            updateDebugStatus('❌ Error en importación - Ver detalles abajo');
            mensajeDiv.innerHTML = `<div class="alert alert-error">❌ ${data.message}</div>`;
            btn.disabled = false;
            btn.textContent = '📥 Importar Período Actual';
        }
    } catch (error) {
        addDebugLog('❌ Excepción: ' + error.message, 'error', { message: error.message, stack: error.stack });
        updateDebugStatus('❌ Excepción capturada');
        mensajeDiv.innerHTML = `<div class="alert alert-error">❌ Error: ${error.message}</div>`;
        btn.disabled = false;
        btn.textContent = '📥 Importar Período Actual';
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>
