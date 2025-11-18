<?php
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "=== ESTRUCTURA DE usuarios_bancos ===\n\n";

$stmt = $pdo->query('DESCRIBE usuarios_bancos');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-25s | %-20s | %-5s | %s\n", 
        $row['Field'], 
        $row['Type'], 
        $row['Key'], 
        $row['Default'] ?? 'NULL'
    );
}

echo "\n=== DATOS EN usuarios_bancos ===\n\n";

$stmt = $pdo->query('SELECT * FROM usuarios_bancos LIMIT 10');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n=== ESTRUCTURA ACTUAL DE usuarios_info (campos banco) ===\n\n";

$stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info LIKE 'banco%'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-25s | %-20s | %-5s | %s\n", 
        $row['Field'], 
        $row['Type'], 
        $row['Key'], 
        $row['Default'] ?? 'NULL'
    );
}
