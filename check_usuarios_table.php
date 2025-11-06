<?php
require_once 'config/database.php';

echo "=== ESTRUCTURA DE LA TABLA USUARIOS ===\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    printf("%-20s %-20s %-10s %-10s %s\n", 'CAMPO', 'TIPO', 'NULL', 'KEY', 'DEFAULT');
    echo str_repeat('-', 80) . "\n";
    
    foreach ($columns as $col) {
        printf("%-20s %-20s %-10s %-10s %s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key'], 
            $col['Default'] ?? 'NULL'
        );
    }
    
    echo "\n=== DATOS ACTUALES EN LA TABLA ===\n";
    $stmt = $db->query("SELECT id, usuario, nombre, apellido, cedula, email FROM usuarios LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "No hay usuarios registrados\n";
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['id']}, Usuario: '{$user['usuario']}', Nombre: {$user['nombre']} {$user['apellido']}, Cédula: {$user['cedula']}, Email: '{$user['email']}'\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>