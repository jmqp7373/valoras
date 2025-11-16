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
    
    // Actualizar período global
    if (!$periodo_global || ($periodo['periodo_inicio'] && $periodo['periodo_inicio'] < $periodo_global['inicio'])) {
        $periodo_global = [
            'inicio' => $periodo['periodo_inicio'],
            'fin' => $periodo['periodo_fin']
        ];
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
    
    <!-- Header con período actual -->
    <div class="header-periodo">
        <div class="periodo-info">
            <h3>📊 Período Actual de Pago</h3>
            <?php if ($periodo_global): ?>
                <p>
                    <?= date('d/m/Y H:i', strtotime($periodo_global['inicio'])) ?> 
                    → 
                    <?= date('d/m/Y H:i', strtotime($periodo_global['fin'])) ?>
                </p>
            <?php else: ?>
                <p>Sin datos de período disponibles</p>
            <?php endif; ?>
        </div>
        <button type="button" class="btn-importar-periodo" id="btnImportarPeriodo">
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
// Importar período actual
document.getElementById('btnImportarPeriodo')?.addEventListener('click', async function() {
    const btn = this;
    const mensajeDiv = document.getElementById('mensajeImportacion');
    
    btn.disabled = true;
    btn.textContent = '⏳ Importando...';
    
    mensajeDiv.innerHTML = '<div class="alert alert-info">⏳ Conectando con API de Stripchat...</div>';
    
    try {
        const response = await fetch('../../controllers/VentasController.php?action=importarPeriodoActual', {
            method: 'GET'
        });
        
        const data = await response.json();
        
        if (data.success) {
            mensajeDiv.innerHTML = `
                <div class="alert alert-success">
                    ✅ ${data.message}
                    <br><small>Recargando en 2 segundos...</small>
                </div>
            `;
            setTimeout(() => window.location.reload(), 2000);
        } else {
            mensajeDiv.innerHTML = `<div class="alert alert-error">❌ ${data.message}</div>`;
            btn.disabled = false;
            btn.textContent = '📥 Importar Período Actual';
        }
    } catch (error) {
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
