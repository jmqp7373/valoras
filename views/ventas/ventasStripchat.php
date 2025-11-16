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

// Obtener cuentas estudio de Stripchat (id_pagina = 3)
$stmt = $db->prepare("
    SELECT DISTINCT 
        ce.id_cuenta_estudio,
        ce.usuario_cuenta_estudio,
        c.usuario as credencial_usuario,
        c.id_credencial
    FROM cuentas_estudios ce
    INNER JOIN credenciales c ON c.id_cuenta_estudio = ce.id_cuenta_estudio
    WHERE ce.id_pagina = 3 
      AND ce.estado = 1
      AND c.eliminado = 0
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
        // Buscar ventas para esta cuenta en este día
        $stmt = $db->prepare("
            SELECT 
                SUM(total_earnings) as total,
                COUNT(*) as num_registros
            FROM ventas_strip
            WHERE id_credencial = :id_credencial
              AND period_start >= :fecha_inicio
              AND period_start <= :fecha_fin
        ");
        $stmt->execute([
            'id_credencial' => $cuenta['id_credencial'],
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = $resultado['total'] ?? 0;
        $num_registros = $resultado['num_registros'] ?? 0;
        
        $ventas_resumen[$dia]['cuentas'][] = [
            'id_cuenta_estudio' => $cuenta['id_cuenta_estudio'],
            'nombre' => $cuenta['usuario_cuenta_estudio'],
            'credencial_usuario' => $cuenta['credencial_usuario'],
            'id_credencial' => $cuenta['id_credencial'],
            'total' => floatval($total),
            'num_registros' => intval($num_registros),
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

.dia-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    overflow: hidden;
}

.dia-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dia-fecha {
    font-size: 18px;
    font-weight: 700;
    color: #333;
}

.dia-total {
    font-size: 20px;
    font-weight: 700;
    color: #6A1B1B;
}

.dia-sin-datos {
    color: #999;
    font-weight: 400;
}

.cuentas-list {
    padding: 0;
    margin: 0;
    list-style: none;
}

.cuenta-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    display: grid;
    grid-template-columns: 2fr 1fr 120px 150px;
    align-items: center;
    gap: 15px;
}

.cuenta-item:last-child {
    border-bottom: none;
}

.cuenta-nombre {
    font-weight: 600;
    color: #333;
}

.cuenta-credencial {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

.cuenta-total {
    text-align: right;
    font-weight: 700;
    font-size: 16px;
}

.cuenta-total.positivo {
    color: #28a745;
}

.cuenta-total.cero {
    color: #999;
}

.estado-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
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
    padding: 6px 12px;
    border-radius: 6px;
    border: none;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-importar-dia {
    background: #882A57;
    color: white;
}

.btn-importar-cuenta {
    background: #6A1B1B;
    color: white;
}

.btn-importar-dia:hover,
.btn-importar-cuenta:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.btn-importar-dia:disabled,
.btn-importar-cuenta:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.sin-cuentas {
    padding: 40px;
    text-align: center;
    color: #999;
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
    .cuenta-item {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .cuenta-total {
        text-align: left;
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
    
    <!-- Listado de días -->
    <?php if (empty($cuentas_stripchat)): ?>
        <div class="sin-cuentas">
            ⚠️ No hay cuentas de Stripchat configuradas en el sistema.
        </div>
    <?php else: ?>
        <?php foreach ($ventas_resumen as $dia_data): ?>
            <div class="dia-card">
                <div class="dia-header">
                    <div class="dia-fecha">
                        📅 <?= date('D, d M Y', strtotime($dia_data['fecha'])) ?>
                    </div>
                    <div class="dia-total <?= $dia_data['tiene_datos'] ? '' : 'dia-sin-datos' ?>">
                        <?php if ($dia_data['tiene_datos']): ?>
                            $<?= number_format($dia_data['total_dia'], 2) ?> USD
                        <?php else: ?>
                            Sin importaciones
                        <?php endif; ?>
                    </div>
                </div>
                
                <ul class="cuentas-list">
                    <?php foreach ($dia_data['cuentas'] as $cuenta): ?>
                        <li class="cuenta-item">
                            <div>
                                <div class="cuenta-nombre"><?= htmlspecialchars($cuenta['nombre']) ?></div>
                                <div class="cuenta-credencial"><?= htmlspecialchars($cuenta['credencial_usuario']) ?></div>
                            </div>
                            <div class="cuenta-total <?= $cuenta['total'] > 0 ? 'positivo' : 'cero' ?>">
                                <?php if ($cuenta['importado']): ?>
                                    $<?= number_format($cuenta['total'], 2) ?>
                                <?php else: ?>
                                    Sin datos
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="estado-badge <?= $cuenta['importado'] ? 'estado-importado' : 'estado-pendiente' ?>">
                                    <?= $cuenta['importado'] ? '✓ Importado' : '⏳ Pendiente' ?>
                                </span>
                            </div>
                            <div>
                                <button class="btn-importar-cuenta" 
                                        data-fecha="<?= $dia_data['fecha'] ?>"
                                        data-id-credencial="<?= $cuenta['id_credencial'] ?>"
                                        data-nombre="<?= htmlspecialchars($cuenta['nombre']) ?>">
                                    📥 Importar
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
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

// Importar ventas de una cuenta específica en un día específico
document.querySelectorAll('.btn-importar-cuenta').forEach(btn => {
    btn.addEventListener('click', async function() {
        const fecha = this.dataset.fecha;
        const idCredencial = this.dataset.idCredencial;
        const nombre = this.dataset.nombre;
        const mensajeDiv = document.getElementById('mensajeImportacion');
        
        this.disabled = true;
        this.textContent = '⏳...';
        
        mensajeDiv.innerHTML = `<div class="alert alert-info">⏳ Importando ${nombre} del ${fecha}...</div>`;
        
        try {
            const response = await fetch('../../controllers/VentasController.php?action=importarStripchatCuentaDia', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `fecha=${fecha}&id_credencial=${idCredencial}`
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
