<?php
/**
 * Script para limpiar tabla finanzas y recargar datos de ejemplo
 * Ejecutar una sola vez visitando: http://localhost/valora.vip/limpiar_finanzas.php
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Limpiar Finanzas - Valora.vip</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #6A1B1B;
            margin-bottom: 1rem;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 1px solid #c3e6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 1px solid #bee5eb;
        }
        .btn {
            background: linear-gradient(135deg, #6A1B1B, #882A57);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .stats {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .stats h3 {
            margin-top: 0;
            color: #1B263B;
        }
        .amount {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🗑️ Limpiar Tabla de Finanzas</h1>";

try {
    $conn = getDBConnection();
    
    // Obtener totales antes de limpiar
    $sql = "SELECT 
                SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) as total_ingresos,
                SUM(CASE WHEN tipo = 'Gasto' THEN monto ELSE 0 END) as total_gastos,
                COUNT(*) as total_registros
            FROM finanzas";
    $stmt = $conn->query($sql);
    $anterior = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $ingresos_ant = floatval($anterior['total_ingresos'] ?? 0);
    $gastos_ant = floatval($anterior['total_gastos'] ?? 0);
    $balance_ant = $ingresos_ant - $gastos_ant;
    $registros_ant = intval($anterior['total_registros'] ?? 0);
    
    echo "<div class='stats'>
            <h3>📊 Datos Anteriores</h3>
            <p><strong>Registros:</strong> {$registros_ant}</p>
            <p><strong>Ingresos:</strong> \$" . number_format($ingresos_ant, 2, ',', '.') . "</p>
            <p><strong>Gastos:</strong> \$" . number_format($gastos_ant, 2, ',', '.') . "</p>
            <p><strong>Balance:</strong> <span class='amount " . ($balance_ant >= 0 ? 'positive' : 'negative') . "'>\$" . number_format($balance_ant, 2, ',', '.') . "</span></p>
          </div>";
    
    // Limpiar tabla
    $conn->exec("TRUNCATE TABLE finanzas");
    
    echo "<div class='success'>
            ✅ Tabla limpiada exitosamente<br>
            Se eliminaron {$registros_ant} registros
          </div>";
    
    echo "<div class='info'>
            <h3>📌 Próximos Pasos</h3>
            <p>Los nuevos datos de ejemplo se insertarán automáticamente cuando visites:</p>
            <ul>
                <li><a href='views/finanzas/finanzasDashboard.php'>Dashboard de Finanzas</a></li>
                <li><a href='index.php'>Dashboard Principal</a></li>
            </ul>
          </div>";
    
    echo "<div class='stats'>
            <h3>💰 Nuevos Datos que se Insertarán</h3>
            <p><strong>Ingresos Totales:</strong> <span class='amount positive'>\$18,500,000.00</span></p>
            <ul>
                <li>Ingresos Estudio Fotográfico: \$8,000,000</li>
                <li>Colaboraciones Empresariales: \$6,500,000</li>
                <li>Servicios Creativos: \$3,000,000</li>
                <li>Bonificación Proyecto: \$1,000,000</li>
            </ul>
            
            <p><strong>Gastos Totales:</strong> <span class='amount negative'>\$11,730,000.00</span></p>
            <ul>
                <li>Arriendo Estudio Diamante: \$4,500,000</li>
                <li>Pago Modelos Semana 44: \$5,200,000</li>
                <li>Servicio EPM y Agua: \$980,000</li>
                <li>Administración Apto: \$800,000</li>
                <li>Internet y Telefonía: \$250,000</li>
            </ul>
            
            <p><strong>Balance General:</strong> <span class='amount positive'>\$6,770,000.00</span></p>
            <p style='color: #28a745; font-weight: bold;'>✅ BALANCE POSITIVO</p>
          </div>";
    
    echo "<a href='views/finanzas/finanzasDashboard.php' class='btn'>📊 Ir a Dashboard de Finanzas</a>
          <a href='index.php' class='btn' style='background: linear-gradient(135deg, #1B263B, #17a2b8); margin-left: 10px;'>🏠 Ir al Dashboard Principal</a>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin: 1rem 0; border: 1px solid #f5c6cb;'>
            ❌ Error al limpiar la tabla: " . htmlspecialchars($e->getMessage()) . "
          </div>";
}

echo "    </div>
</body>
</html>";
?>
