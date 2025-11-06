<?php
require_once 'config/database.php';

echo "=== ESTRUCTURA DE password_reset_tokens ===\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->query("DESCRIBE password_reset_tokens");
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
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>