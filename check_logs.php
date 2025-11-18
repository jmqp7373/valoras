<?php
/**
 * Script de diagnóstico para verificar configuración de logs
 * y conteo de modelos activos por cuenta estudio
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Logs y Modelos Stripchat</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #6A1B1B;
            margin-top: 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #6A1B1B;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .highlight {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico del Sistema de Importación Stripchat</h1>

    <?php
    // 1. CONFIGURACIÓN DE PHP
    ?>
    <div class="card">
        <h2>⚙️ Configuración de PHP</h2>
        <div class="info-row">
            <span class="label">Versión de PHP:</span>
            <span class="value"><?php echo PHP_VERSION; ?></span>
        </div>
        <div class="info-row">
            <span class="label">Error Reporting:</span>
            <span class="value"><?php echo ini_get('error_reporting'); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Display Errors:</span>
            <span class="value <?php echo ini_get('display_errors') ? 'success' : 'warning'; ?>">
                <?php echo ini_get('display_errors') ? 'ON' : 'OFF'; ?>
            </span>
        </div>
        <div class="info-row">
            <span class="label">Log Errors:</span>
            <span class="value <?php echo ini_get('log_errors') ? 'success' : 'error'; ?>">
                <?php echo ini_get('log_errors') ? 'ON' : 'OFF'; ?>
            </span>
        </div>
        <div class="info-row">
            <span class="label">Error Log:</span>
            <span class="value"><?php echo ini_get('error_log') ?: 'No configurado'; ?></span>
        </div>
        <div class="info-row">
            <span class="label">Max Execution Time:</span>
            <span class="value"><?php echo ini_get('max_execution_time'); ?> segundos</span>
        </div>
        <div class="info-row">
            <span class="label">Memory Limit:</span>
            <span class="value"><?php echo ini_get('memory_limit'); ?></span>
        </div>
    </div>

    <?php
    // 2. ARCHIVOS DE LOG POSIBLES
    $posiblesLogs = [
        'C:\\xampp\\php\\logs\\php_error.log',
        'C:\\xampp\\apache\\logs\\error.log',
        'C:\\xampp\\apache\\logs\\php_error.log',
        __DIR__ . '/logs/php_error.log',
        __DIR__ . '/error.log'
    ];
    ?>
    <div class="card">
        <h2>📄 Ubicaciones de Logs</h2>
        <table>
            <thead>
                <tr>
                    <th>Ruta</th>
                    <th>Estado</th>
                    <th>Tamaño</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posiblesLogs as $logPath): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($logPath); ?></code></td>
                        <td>
                            <?php if (file_exists($logPath)): ?>
                                <span class="badge badge-success">✓ Existe</span>
                            <?php else: ?>
                                <span class="badge badge-danger">✗ No existe</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if (file_exists($logPath)) {
                                $size = filesize($logPath);
                                echo number_format($size / 1024, 2) . ' KB';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // 3. CONFIGURACIÓN DE STRIPCHAT
    $config = require __DIR__ . '/config/configStripchat.php';
    ?>
    <div class="card">
        <h2>🔐 Cuentas Estudio Configuradas</h2>
        <table>
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Studio Username</th>
                    <th>API Key</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($config['cuentas'] as $key => $cuenta): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cuenta['nombre']); ?></strong></td>
                        <td><code><?php echo htmlspecialchars($cuenta['studio_username']); ?></code></td>
                        <td>
                            <?php 
                            if (strpos($cuenta['api_key'], 'COLOCAR') !== false) {
                                echo '<span class="badge badge-danger">No configurada</span>';
                            } else {
                                echo substr($cuenta['api_key'], 0, 10) . '...';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($cuenta['activo']): ?>
                                <span class="badge badge-success">✓ Activa</span>
                            <?php else: ?>
                                <span class="badge badge-warning">⏸ Inactiva</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // 4. CONTEO DE MODELOS POR CUENTA ESTUDIO
    try {
        $db = getDBConnection();

        $stmt = $db->prepare("
            SELECT 
                ce.id_cuenta_estudio,
                ce.usuario_cuenta_estudio,
                COUNT(DISTINCT c.id_credencial) as total_credenciales,
                COUNT(DISTINCT CASE 
                    WHEN u.eliminado = 0 
                    AND c.eliminado = 0 
                    AND ue.nombre_estado = 'Activo' 
                    THEN c.id_credencial 
                END) as credenciales_activas,
                COUNT(DISTINCT CASE 
                    WHEN vs.updated_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) 
                    THEN c.id_credencial 
                END) as con_actividad_60dias
            FROM cuentas_estudios ce
            LEFT JOIN credenciales c ON c.id_cuenta_estudio = ce.id_cuenta_estudio AND c.id_pagina = 3
            LEFT JOIN usuarios u ON u.id_usuario = c.id_usuario
            LEFT JOIN usuarios_estados ue ON ue.id_usuario_estado = u.id_usuario_estado
            LEFT JOIN ventas_strip vs ON vs.id_credencial = c.id_credencial
            WHERE ce.id_pagina = 3
            AND ce.estado = 1
            GROUP BY ce.id_cuenta_estudio, ce.usuario_cuenta_estudio
            ORDER BY ce.usuario_cuenta_estudio
        ");
        $stmt->execute();
        $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="card">
        <h2>📊 Estadísticas de Modelos por Cuenta Estudio</h2>
        
        <div class="highlight">
            <strong>ℹ️ Nota:</strong> Con la nueva configuración, se importarán <strong>TODOS los modelos activos</strong> (sin límite de 100).
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Cuenta Estudio</th>
                    <th>Total Credenciales</th>
                    <th>Usuarios Activos</th>
                    <th>Con Actividad (60 días)</th>
                    <th>Tiempo Estimado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalGeneral = 0;
                foreach ($cuentas as $cuenta): 
                    $activos = intval($cuenta['credenciales_activas']);
                    $totalGeneral += $activos;
                    $tiempoEstimado = ceil($activos * 3 / 60); // 3 seg por modelo, convertir a minutos
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cuenta['usuario_cuenta_estudio']); ?></strong></td>
                        <td><?php echo number_format($cuenta['total_credenciales']); ?></td>
                        <td>
                            <span class="badge badge-success">
                                <?php echo number_format($activos); ?> modelos
                            </span>
                        </td>
                        <td><?php echo number_format($cuenta['con_actividad_60dias']); ?></td>
                        <td>
                            <?php if ($tiempoEstimado > 60): ?>
                                ~<?php echo number_format($tiempoEstimado / 60, 1); ?> horas
                            <?php else: ?>
                                ~<?php echo $tiempoEstimado; ?> minutos
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL</td>
                    <td>-</td>
                    <td>
                        <span class="badge badge-success">
                            <?php echo number_format($totalGeneral); ?> modelos
                        </span>
                    </td>
                    <td>-</td>
                    <td>
                        <?php 
                        $tiempoTotalMin = ceil($totalGeneral * 3 / 60);
                        if ($tiempoTotalMin > 60) {
                            echo '~' . number_format($tiempoTotalMin / 60, 1) . ' horas';
                        } else {
                            echo '~' . $tiempoTotalMin . ' minutos';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="highlight" style="margin-top: 20px; border-left-color: #6A1B1B; background: #f8d7da;">
            <strong>⚠️ IMPORTANTE:</strong> 
            <ul style="margin: 10px 0;">
                <li>Se importarán <strong><?php echo number_format($totalGeneral); ?> modelos activos</strong> en total</li>
                <li>Tiempo estimado: <strong><?php echo $tiempoTotalMin > 60 ? number_format($tiempoTotalMin / 60, 1) . ' horas' : $tiempoTotalMin . ' minutos'; ?></strong></li>
                <li>El proceso NO se puede interrumpir una vez iniciado</li>
                <li>Asegúrate de tener buena conexión a internet</li>
            </ul>
        </div>
    </div>

    <?php
    } catch (Exception $e) {
        echo '<div class="card"><div class="error">Error al consultar base de datos: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
    }
    ?>

    <div class="card">
        <h2>🚀 Siguientes Pasos</h2>
        <ol>
            <li><strong>Ejecutar migración SQL</strong> (si aún no lo has hecho):
                <ul>
                    <li>Ir a phpMyAdmin</li>
                    <li>Seleccionar base de datos <code>valora_db</code></li>
                    <li>Ir a pestaña "SQL"</li>
                    <li>Ejecutar el archivo: <code>database/migrations/fix_ventas_strip_duplicates.sql</code></li>
                </ul>
            </li>
            <li><strong>Ir al módulo de importación:</strong>
                <ul>
                    <li><a href="views/ventas/ventasStripchat.php" target="_blank">Abrir módulo de Ventas Stripchat</a></li>
                </ul>
            </li>
            <li><strong>Hacer clic en "Importar Período Actual"</strong> y esperar pacientemente</li>
        </ol>
    </div>

</body>
</html>
