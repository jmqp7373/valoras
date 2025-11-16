<?php
/**
 * Vista: Importación y Control de Ventas Stripchat
 * 
 * Muestra resumen diario de ventas importadas desde Stripchat,
 * agrupadas por día y cuenta estudio, con importación automática desde API.
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

// Determinar rango de fechas
$rango = $_GET['rango'] ?? '15';
$fecha_desde = null;
$fecha_hasta = null;

if ($rango === 'personalizado') {
    $fecha_desde = $_GET['fecha_desde'] ?? null;
    $fecha_hasta = $_GET['fecha_hasta'] ?? null;
    if (!$fecha_desde || !$fecha_hasta) {
        $rango = '15'; // Fallback
    }
}

// Calcular fechas según rango
switch ($rango) {
    case '7':
        $dias = 7;
        break;
    case '30':
        $dias = 30;
        break;
    case 'personalizado':
        $dias = null;
        break;
    default:
        $dias = 15;
        $rango = '15';
}

if ($dias) {
    $fecha_hasta = date('Y-m-d');
    $fecha_desde = date('Y-m-d', strtotime("-{$dias} days"));
}

// Obtener SOLO cuentas estudio únicas de Stripchat (sin duplicados por credencial)
$stmt = $db->prepare("
    SELECT DISTINCT 
        ce.id_cuenta_estudio,
        ce.usuario_cuenta_estudio
    FROM cuentas_estudios ce
    WHERE ce.id_pagina = 3 
      AND ce.estado = 1
      AND EXISTS (
          SELECT 1 FROM credenciales c 
          WHERE c.id_cuenta_estudio = ce.id_cuenta_estudio 
          AND c.eliminado = 0
      )
    ORDER BY ce.usuario_cuenta_estudio
");
$stmt->execute();
$cuentas_stripchat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generar array de días en el rango
$dias_array = [];
$current_date = new DateTime($fecha_desde);
$end_date = new DateTime($fecha_hasta);

while ($current_date <= $end_date) {
    $dias_array[] = $current_date->format('Y-m-d');
    $current_date->modify('+1 day');
}
$dias_array = array_reverse($dias_array); // Mostrar más reciente primero

// Obtener resumen de ventas por día y cuenta estudio
$ventas_resumen = [];
$totales_generales = [
    'dias_con_datos' => 0,
    'total_usd' => 0,
    'total_registros' => 0
];

foreach ($dias_array as $dia) {
    $fecha_inicio = $dia . ' 00:00:00';
    $fecha_fin = $dia . ' 23:59:59';
    
    $ventas_resumen[$dia] = [
        'fecha' => $dia,
        'cuentas' => [],
        'total_dia' => 0,
        'tiene_datos' => false
    ];
    
    foreach ($cuentas_stripchat as $cuenta) {
        // Buscar ventas para TODAS las credenciales de esta cuenta estudio en este día
        // Esto suma automáticamente todos los modelos asociados a esta cuenta
        $stmt = $db->prepare("
            SELECT 
                SUM(vs.total_earnings) as total,
                COUNT(*) as num_registros,
                COUNT(DISTINCT vs.id_credencial) as num_modelos
            FROM ventas_strip vs
            INNER JOIN credenciales c ON c.id_credencial = vs.id_credencial
            WHERE c.id_cuenta_estudio = :id_cuenta_estudio
              AND c.eliminado = 0
              AND vs.period_start >= :fecha_inicio
              AND vs.period_start <= :fecha_fin
        ");
        $stmt->execute([
            'id_cuenta_estudio' => $cuenta['id_cuenta_estudio'],
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = $resultado['total'] ?? 0;
        $num_registros = $resultado['num_registros'] ?? 0;
        $num_modelos = $resultado['num_modelos'] ?? 0;
        
        $ventas_resumen[$dia]['cuentas'][] = [
            'id_cuenta_estudio' => $cuenta['id_cuenta_estudio'],
            'nombre' => $cuenta['usuario_cuenta_estudio'],
            'total' => floatval($total),
            'num_registros' => intval($num_registros),
            'num_modelos' => intval($num_modelos),
            'importado' => $num_registros > 0
        ];
        
        $ventas_resumen[$dia]['total_dia'] += floatval($total);
        if ($num_registros > 0) {
            $ventas_resumen[$dia]['tiene_datos'] = true;
        }
    }
    
    if ($ventas_resumen[$dia]['tiene_datos']) {
        $totales_generales['dias_con_datos']++;
        $totales_generales['total_usd'] += $ventas_resumen[$dia]['total_dia'];
    }
}

// Calcular total de registros
$stmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM ventas_strip
    WHERE period_start >= :fecha_desde
      AND period_start <= :fecha_hasta
");
$stmt->execute([
    'fecha_desde' => $fecha_desde . ' 00:00:00',
    'fecha_hasta' => $fecha_hasta . ' 23:59:59'
]);
$totales_generales['total_registros'] = $stmt->fetchColumn();

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
$page_title = $modulo['titulo'] ?? 'Importación Stripchat';
$titulo_pagina = $modulo['titulo'] ?? 'Importación y Control de Ventas Stripchat';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Resumen diario de ventas importadas desde Stripchat';
$icono_pagina = $modulo['icono'] ?? '💰';

// Breadcrumbs
$breadcrumbs = [
    ['label' => 'Home', 'url' => '../../index.php'],
    ['label' => 'Ventas Stripchat', 'url' => '']
];

// CSS adicional
$additional_css = [];

// JavaScript adicional
$additional_js = [];

ob_start();
?>

<style>
.ventas-stripchat-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.controles-superiores {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.selector-rango {
    display: flex;
    align-items: center;
    gap: 10px;
}

.selector-rango label {
    font-weight: 600;
    color: #333;
}

.selector-rango select,
.selector-rango input[type="date"] {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.btn-importar-global {
    background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-importar-global:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3);
}

.btn-importar-global:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.resumen-general {
    background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.resumen-item {
    text-align: center;
}

.resumen-item .valor {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 5px;
}

.resumen-item .label {
    font-size: 14px;
    opacity: 0.9;
}

/* Separador de día */
.dia-separator {
    background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
    color: white;
    padding: 12px 20px;
    margin: 30px 0 15px 0;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 6px rgba(106, 27, 27, 0.3);
}

.dia-separator:first-of-type {
    margin-top: 0;
}

.dia-total-separator {
    font-weight: 400;
    font-size: 14px;
    opacity: 0.95;
}

/* Tarjeta individual por cuenta estudio */
.cuenta-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 15px;
    overflow: hidden;
    border-left: 4px solid #882A57;
    transition: all 0.2s ease;
}

.cuenta-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.cuenta-card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cuenta-info {
    flex: 1;
}

.cuenta-nombre-titulo {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin: 0 0 5px 0;
}

.cuenta-credencial {
    font-size: 13px;
    color: #666;
}

.cuenta-estado-container {
    margin-left: 15px;
}

.cuenta-card-body {
    padding: 20px;
}

.cuenta-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.stat-value.positivo {
    color: #28a745;
}

.stat-value.cero {
    color: #999;
}

.cuenta-actions {
    display: flex;
    justify-content: center;
}

.estado-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}

.estado-importado {
    background: #d4edda;
    color: #155724;
}

.estado-pendiente {
    background: #fff3cd;
    color: #856404;
}


.btn-importar-dia,
.btn-importar-cuenta {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-importar-dia {
    background: #882A57;
    color: white;
}

.btn-importar-cuenta {
    background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
    color: white;
    padding: 12px 30px;
    font-size: 15px;
}

.btn-importar-dia:hover,
.btn-importar-cuenta:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3);
}

.btn-importar-dia:disabled,
.btn-importar-cuenta:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.sin-cuentas {
    padding: 60px;
    text-align: center;
    color: #999;
    font-size: 18px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}


.fecha-personalizada {
    display: none;
    gap: 10px;
    margin-top: 10px;
}

.fecha-personalizada.active {
    display: flex;
}

#mensajeImportacion {
    margin-top: 15px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin: 10px 0;
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
    .dia-separator {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
    
    .cuenta-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .cuenta-estado-container {
        margin-left: 0;
    }
    
    .cuenta-stats {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .stat-value {
        font-size: 20px;
    }
    
    .resumen-general {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="ventas-stripchat-container">
    
    <!-- Controles superiores -->
    <div class="controles-superiores">
        <form method="GET" class="selector-rango" id="formRango">
            <label for="rango">📅 Rango:</label>
            <select name="rango" id="rango" onchange="toggleFechaPersonalizada()">
                <option value="7" <?= $rango === '7' ? 'selected' : '' ?>>Últimos 7 días</option>
                <option value="15" <?= $rango === '15' ? 'selected' : '' ?>>Últimos 15 días</option>
                <option value="30" <?= $rango === '30' ? 'selected' : '' ?>>Últimos 30 días</option>
                <option value="personalizado" <?= $rango === 'personalizado' ? 'selected' : '' ?>>Rango personalizado</option>
            </select>
            
            <div class="fecha-personalizada <?= $rango === 'personalizado' ? 'active' : '' ?>" id="fechaPersonalizada">
                <input type="date" name="fecha_desde" value="<?= $fecha_desde ?>" max="<?= date('Y-m-d') ?>">
                <input type="date" name="fecha_hasta" value="<?= $fecha_hasta ?>" max="<?= date('Y-m-d') ?>">
            </div>
            
            <button type="submit" class="btn-importar-global">🔍 Aplicar</button>
        </form>
        
        <button type="button" class="btn-importar-global" id="btnImportarPendientes">
            📥 Importar ventas pendientes de los últimos <?= $dias ?? 15 ?> días
        </button>
    </div>
    
    <!-- Resumen general -->
    <div class="resumen-general">
        <div class="resumen-item">
            <div class="valor"><?= count($dias_array) ?></div>
            <div class="label">Días consultados</div>
        </div>
        <div class="resumen-item">
            <div class="valor"><?= $totales_generales['dias_con_datos'] ?></div>
            <div class="label">Días con datos</div>
        </div>
        <div class="resumen-item">
            <div class="valor">$<?= number_format($totales_generales['total_usd'], 2) ?></div>
            <div class="label">Total USD</div>
        </div>
        <div class="resumen-item">
            <div class="valor"><?= $totales_generales['total_registros'] ?></div>
            <div class="label">Registros importados</div>
        </div>
    </div>
    
    <div id="mensajeImportacion"></div>
    
    <!-- Listado de días y cuentas -->
    <?php if (empty($cuentas_stripchat)): ?>
        <div class="sin-cuentas">
            ⚠️ No hay cuentas de Stripchat configuradas en el sistema.
        </div>
    <?php else: ?>
        <?php foreach ($ventas_resumen as $dia_data): ?>
            <!-- Separador de día -->
            <div class="dia-separator">
                📅 <?= date('l, d \d\e F \d\e Y', strtotime($dia_data['fecha'])) ?>
                <span class="dia-total-separator">
                    <?php if ($dia_data['tiene_datos']): ?>
                        Total del día: <strong>$<?= number_format($dia_data['total_dia'], 2) ?> USD</strong>
                    <?php else: ?>
                        Sin importaciones este día
                    <?php endif; ?>
                </span>
            </div>
            
            <!-- Una tarjeta por cada cuenta estudio -->
            <?php foreach ($dia_data['cuentas'] as $cuenta): ?>
                <div class="cuenta-card">
                    <div class="cuenta-card-header">
                        <div class="cuenta-info">
                            <h4 class="cuenta-nombre-titulo">🏢 <?= htmlspecialchars($cuenta['nombre']) ?></h4>
                            <div class="cuenta-credencial">
                                <?php if ($cuenta['num_modelos'] > 0): ?>
                                    <?= $cuenta['num_modelos'] ?> modelo<?= $cuenta['num_modelos'] > 1 ? 's' : '' ?> activo<?= $cuenta['num_modelos'] > 1 ? 's' : '' ?>
                                <?php else: ?>
                                    Sin modelos con datos
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="cuenta-estado-container">
                            <span class="estado-badge <?= $cuenta['importado'] ? 'estado-importado' : 'estado-pendiente' ?>">
                                <?= $cuenta['importado'] ? '✓ Importado' : '⏳ Pendiente' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="cuenta-card-body">
                        <div class="cuenta-stats">
                            <div class="stat-item">
                                <div class="stat-label">Total USD</div>
                                <div class="stat-value <?= $cuenta['total'] > 0 ? 'positivo' : 'cero' ?>">
                                    <?php if ($cuenta['importado']): ?>
                                        $<?= number_format($cuenta['total'], 2) ?>
                                    <?php else: ?>
                                        $0.00
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Registros</div>
                                <div class="stat-value">
                                    <?= $cuenta['num_registros'] ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="cuenta-actions">
                            <button class="btn-importar-cuenta" 
                                    data-fecha="<?= $dia_data['fecha'] ?>"
                                    data-id-cuenta="<?= $cuenta['id_cuenta_estudio'] ?>"
                                    data-nombre="<?= htmlspecialchars($cuenta['nombre']) ?>">
                                <?= $cuenta['importado'] ? '🔄 Re-importar' : '📥 Importar ventas' ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
</div>

<script>
function toggleFechaPersonalizada() {
    const select = document.getElementById('rango');
    const divPersonalizado = document.getElementById('fechaPersonalizada');
    
    if (select.value === 'personalizado') {
        divPersonalizado.classList.add('active');
    } else {
        divPersonalizado.classList.remove('active');
    }
}

// Importar ventas pendientes globalmente
document.getElementById('btnImportarPendientes')?.addEventListener('click', async function() {
    const btn = this;
    const mensajeDiv = document.getElementById('mensajeImportacion');
    
    btn.disabled = true;
    btn.textContent = '⏳ Importando...';
    
    mensajeDiv.innerHTML = '<div class="alert alert-info">⏳ Conectando con API de Stripchat...</div>';
    
    try {
        const response = await fetch('../../controllers/VentasController.php?action=importarStripchatRango', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `fecha_desde=<?= $fecha_desde ?>&fecha_hasta=<?= $fecha_hasta ?>`
        });
        
        const data = await response.json();
        
        if (data.success) {
            mensajeDiv.innerHTML = `
                <div class="alert alert-success">
                    ✅ Importación completada: ${data.total_importados} registro(s) importado(s).
                    <br><small>Recargando en 2 segundos...</small>
                </div>
            `;
            setTimeout(() => window.location.reload(), 2000);
        } else {
            mensajeDiv.innerHTML = `<div class="alert alert-error">❌ ${data.message}</div>`;
            btn.disabled = false;
            btn.textContent = '📥 Importar ventas pendientes de los últimos <?= $dias ?? 15 ?> días';
        }
    } catch (error) {
        mensajeDiv.innerHTML = `<div class="alert alert-error">❌ Error: ${error.message}</div>`;
        btn.disabled = false;
        btn.textContent = '📥 Importar ventas pendientes de los últimos <?= $dias ?? 15 ?> días';
    }
});

// Importar ventas de una cuenta estudio específica en un día específico
document.querySelectorAll('.btn-importar-cuenta').forEach(btn => {
    btn.addEventListener('click', async function() {
        const fecha = this.dataset.fecha;
        const idCuenta = this.dataset.idCuenta;
        const nombre = this.dataset.nombre;
        const mensajeDiv = document.getElementById('mensajeImportacion');
        
        this.disabled = true;
        this.textContent = '⏳...';
        
        mensajeDiv.innerHTML = `<div class="alert alert-info">⏳ Importando ${nombre} del ${fecha}...</div>`;
        
        try {
            const response = await fetch('../../controllers/VentasController.php?action=importarStripchatCuentaDia', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `fecha=${fecha}&id_cuenta_estudio=${idCuenta}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                mensajeDiv.innerHTML = `
                    <div class="alert alert-success">
                        ✅ ${data.message || 'Importación exitosa'}
                        <br><small>Recargando...</small>
                    </div>
                `;
                setTimeout(() => window.location.reload(), 1500);
            } else {
                mensajeDiv.innerHTML = `<div class="alert alert-error">❌ ${data.message}</div>`;
                this.disabled = false;
                this.textContent = '📥 Importar';
            }
        } catch (error) {
            mensajeDiv.innerHTML = `<div class="alert alert-error">❌ Error: ${error.message}</div>`;
            this.disabled = false;
            this.textContent = '📥 Importar';
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>
