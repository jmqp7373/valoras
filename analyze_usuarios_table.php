<?php
require_once __DIR__ . '/config/database.php';

$db = getDBConnection();

echo "<h2>🔍 Análisis Detallado de Tabla usuarios</h2>";

// Obtener estructura completa
$stmt = $db->query("SHOW COLUMNS FROM usuarios");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Todas las columnas de la tabla 'usuarios':</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; font-family: monospace;'>";
echo "<tr style='background: #333; color: white;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

foreach ($columns as $col) {
    echo "<tr style='background: #f9f9f9;'>";
    echo "<td><strong style='color: #d63384;'>{$col['Field']}</strong></td>";
    echo "<td style='color: #0d6efd;'>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td><strong>{$col['Key']}</strong></td>";
    echo "<td>" . ($col['Default'] ?? '<i>NULL</i>') . "</td>";
    echo "<td>" . ($col['Extra'] ?? '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Obtener una muestra de datos
echo "<h3>Muestra de 5 usuarios:</h3>";
$stmt = $db->query("SELECT * FROM usuarios LIMIT 5");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($usuarios)) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; font-family: monospace; font-size: 12px;'>";
    echo "<tr style='background: #333; color: white;'>";
    foreach (array_keys($usuarios[0]) as $key) {
        echo "<th>{$key}</th>";
    }
    echo "</tr>";
    
    foreach ($usuarios as $row) {
        echo "<tr style='background: #f9f9f9;'>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Buscar relación con credenciales
echo "<h3>🔗 Query de prueba: credenciales + usuarios</h3>";
try {
    $stmt = $db->query("
        SELECT 
            c.id_credencial,
            c.usuario as model_username,
            c.id_usuario,
            u.*
        FROM credenciales c
        INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
        WHERE c.id_pagina = 3
        LIMIT 3
    ");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p style='color: green;'>✅ Query ejecutado. Resultados: " . count($result) . "</p>";
    
    if (!empty($result)) {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; font-family: monospace; font-size: 11px;'>";
        echo "<tr style='background: #333; color: white;'>";
        foreach (array_keys($result[0]) as $key) {
            $highlight = (stripos($key, 'estado') !== false) ? "style='background: yellow; color: black;'" : "";
            echo "<th $highlight>{$key}</th>";
        }
        echo "</tr>";
        
        foreach ($result as $row) {
            echo "<tr style='background: #f9f9f9;'>";
            foreach ($row as $key => $value) {
                $highlight = (stripos($key, 'estado') !== false) ? "style='background: #ffffcc;'" : "";
                echo "<td $highlight>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: {$e->getMessage()}</p>";
}
?>
