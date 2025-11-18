<?php
require_once __DIR__ . '/config/database.php';
$db = getDBConnection();

echo "<h2>Estados disponibles en tabla usuarios</h2>";
$stmt = $db->query("SELECT DISTINCT estado FROM usuarios ORDER BY estado");
$estados = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<pre>";
print_r($estados);
echo "</pre>";

echo "<h2>Conteo de usuarios por estado</h2>";
$stmt = $db->query("SELECT estado, COUNT(*) as total FROM usuarios GROUP BY estado ORDER BY total DESC");
$conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Estado</th><th>Total</th></tr>";
foreach ($conteo as $row) {
    echo "<tr><td><strong>{$row['estado']}</strong></td><td>{$row['total']}</td></tr>";
}
echo "</table>";

echo "<h2>Modelos de Strip_sat por estado</h2>";
$stmt = $db->query("
    SELECT 
        u.estado,
        COUNT(DISTINCT c.id_credencial) as total_modelos
    FROM credenciales c
    INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
    WHERE c.id_cuenta_estudio = 18
    AND c.id_pagina = 3
    AND c.eliminado = 0
    GROUP BY u.estado
    ORDER BY total_modelos DESC
");
$conteo = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Estado</th><th>Total Modelos</th></tr>";
foreach ($conteo as $row) {
    echo "<tr><td><strong>{$row['estado']}</strong></td><td>{$row['total_modelos']}</td></tr>";
}
echo "</table>";
?>
