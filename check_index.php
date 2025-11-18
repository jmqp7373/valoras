<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h3>Verificando índice único en ventas_strip</h3>";

$sql = "SHOW INDEXES FROM ventas_strip WHERE Key_name = 'unique_period_per_model'";
$stmt = $conn->query($sql);
$indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($indexes) > 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ ÍNDICE ÚNICO EXISTE</p>";
    echo "<pre>";
    print_r($indexes);
    echo "</pre>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ ÍNDICE ÚNICO NO EXISTE</p>";
    echo "<p>Debes ejecutar la migración SQL en: <code>database/migrations/fix_ventas_strip_duplicates.sql</code></p>";
    echo "<p><strong>IMPORTANTE:</strong> Sin este índice, el sistema NO puede evitar duplicados y NO funcionará correctamente.</p>";
}

echo "<hr>";
echo "<h3>Verificando configuración de Stripchat</h3>";

$configFile = 'config/configStripchat.php';
if (file_exists($configFile)) {
    $stripchatConfig = require $configFile;
    
    if (isset($stripchatConfig['cuentas']) && is_array($stripchatConfig['cuentas'])) {
        foreach ($stripchatConfig['cuentas'] as $cuenta => $config) {
            $activo = $config['activo'] ? 'SÍ ✓' : '<span style="color:red;">NO ✗</span>';
            $apiConfigured = (strlen($config['api_key']) > 20 && !str_contains($config['api_key'], 'COLOCAR')) 
                ? '✓ Configurada' 
                : '<span style="color:red;">✗ NO configurada</span>';
            
            echo "<h4>" . $config['nombre'] . " ($cuenta)</h4>";
            echo "Activo: $activo<br>";
            echo "API Key: $apiConfigured<br>";
            echo "Studio Username: " . $config['studio_username'] . "<br><br>";
        }
    } else {
        echo "<p style='color: red;'>Configuración de cuentas no encontrada</p>";
    }
} else {
    echo "<p style='color: red;'>Archivo de configuración no encontrado</p>";
}

echo "<hr>";
echo "<h3>Verificando logs recientes en ventas_strip</h3>";

$sql = "SELECT COUNT(*) as total FROM ventas_strip";
$stmt = $conn->query($sql);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total registros en ventas_strip: <strong>" . $row['total'] . "</strong><br><br>";

$sql = "SELECT * FROM ventas_strip ORDER BY id DESC LIMIT 5";
$stmt = $conn->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h4>Últimos 5 registros:</h4>";
if (count($rows) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: white;'><th>ID</th><th>ID Credencial</th><th>ID Usuario</th><th>Period Start</th><th>Period End</th><th>Total Earnings</th></tr>";
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['id_credencial'] . "</td>";
        echo "<td>" . $row['id_usuario'] . "</td>";
        echo "<td>" . $row['period_start'] . "</td>";
        echo "<td>" . $row['period_end'] . "</td>";
        echo "<td>$" . number_format($row['total_earnings'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange; font-weight: bold;'>⚠ No hay registros en la tabla</p>";
    echo "<p>Esto es NORMAL si acabas de eliminar todos los registros para probar.</p>";
}
