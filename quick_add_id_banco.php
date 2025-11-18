<?php
require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

$stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info LIKE 'id_banco'");
echo "Columna id_banco existe: " . ($stmt->rowCount() > 0 ? 'SI' : 'NO') . PHP_EOL;

if ($stmt->rowCount() == 0) {
    echo "\n⚠️ La columna id_banco NO existe. Ejecutando migración...\n\n";
    
    // Ejecutar migración
    try {
        $pdo->beginTransaction();
        
        // Agregar columna id_banco
        $sql = "ALTER TABLE usuarios_info 
                ADD COLUMN id_banco INT NULL 
                AFTER banco_numero_cuenta";
        $pdo->exec($sql);
        echo "✅ Columna id_banco agregada\n";
        
        // Agregar FK
        $sql = "ALTER TABLE usuarios_info 
                ADD CONSTRAINT fk_usuarios_info_banco 
                FOREIGN KEY (id_banco) 
                REFERENCES usuarios_bancos(id_banco)
                ON DELETE SET NULL
                ON UPDATE CASCADE";
        $pdo->exec($sql);
        echo "✅ Foreign Key agregada\n";
        
        $pdo->commit();
        echo "\n✅ Migración completada exitosamente\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La columna id_banco ya existe\n";
}
