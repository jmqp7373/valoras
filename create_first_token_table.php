<?php
require_once 'config/database.php';

echo "=== CREANDO TABLA password_first_token ===\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `password_first_token` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `cedula` VARCHAR(20) NOT NULL,
      `token` VARCHAR(6) NOT NULL,
      `celular` VARCHAR(30) NOT NULL,
      `expires_at` DATETIME NOT NULL,
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `verified_at` DATETIME NULL DEFAULT NULL,
      `verified` TINYINT(1) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      KEY `idx_token` (`token`),
      KEY `idx_cedula` (`cedula`),
      KEY `idx_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    echo "✅ Tabla 'password_first_token' creada exitosamente\n\n";
    
    // Verificar la estructura
    $stmt = $db->query("DESCRIBE password_first_token");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estructura de la tabla:\n";
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
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>