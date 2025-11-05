<?php
require_once '../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar campos disponibles
    echo "<h3>Campos disponibles en la tabla usuarios:</h3>";
    $stmt = $conn->prepare("SHOW COLUMNS FROM usuarios");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach($columns as $column) {
        echo "<li><strong>" . $column['Field'] . "</strong> (" . $column['Type'] . ")</li>";
    }
    echo "</ul>";
    
    // Verificar si hay usuarios
    echo "<h3>Primeros 3 usuarios (para ver estructura real):</h3>";
    $stmt = $conn->prepare("SELECT * FROM usuarios LIMIT 3");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if($users) {
        echo "<table border='1' style='border-collapse: collapse; font-size: 12px;'>";
        
        // Headers
        echo "<tr>";
        foreach(array_keys($users[0]) as $header) {
            echo "<th style='padding: 5px; background: #f0f0f0;'>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        // Data
        foreach($users as $user) {
            echo "<tr>";
            foreach($user as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay usuarios en la tabla.</p>";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>