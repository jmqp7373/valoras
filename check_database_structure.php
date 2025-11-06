<?php
// Verificar estructura de la base de datos
require_once 'config/database.php';

echo "<h2>🔍 Verificación de Base de Datos</h2>\n";
echo "<hr>\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ <strong>Conexión a base de datos exitosa</strong><br><br>\n";
        
        // Mostrar información de conexión
        $stmt = $db->query("SELECT DATABASE() as current_db");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Base de datos actual: <strong>{$result['current_db']}</strong><br><br>\n";
        
        // Listar todas las tablas
        echo "📋 <strong>Tablas existentes:</strong><br>\n";
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "⚠️ No hay tablas en la base de datos<br><br>\n";
            
            // Crear la tabla users si no existe
            echo "🔧 <strong>Creando tabla 'users'...</strong><br>\n";
            
            $create_table_sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario VARCHAR(50) UNIQUE,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                cedula VARCHAR(20) UNIQUE NOT NULL,
                fecha_nacimiento DATE NOT NULL,
                sexo ENUM('M', 'F') NOT NULL,
                pais VARCHAR(100) NOT NULL,
                telefono VARCHAR(20),
                email VARCHAR(100),
                password VARCHAR(255) NOT NULL,
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                activo TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            if ($db->exec($create_table_sql) !== false) {
                echo "✅ Tabla 'users' creada exitosamente<br><br>\n";
            } else {
                echo "❌ Error creando tabla 'users'<br><br>\n";
            }
            
        } else {
            echo "<ul>\n";
            foreach ($tables as $table) {
                echo "<li>$table</li>\n";
                
                // Si existe la tabla users, mostrar su estructura
                if ($table === 'users') {
                    echo "<br><strong>Estructura de la tabla 'users':</strong><br>\n";
                    $desc_stmt = $db->query("DESCRIBE users");
                    $columns = $desc_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
                    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
                    foreach ($columns as $col) {
                        echo "<tr>";
                        echo "<td>{$col['Field']}</td>";
                        echo "<td>{$col['Type']}</td>";
                        echo "<td>{$col['Null']}</td>";
                        echo "<td>{$col['Key']}</td>";
                        echo "<td>{$col['Default']}</td>";
                        echo "</tr>\n";
                    }
                    echo "</table><br>\n";
                }
            }
            echo "</ul><br>\n";
        }
        
        // Verificar nuevamente las tablas después de la creación
        $stmt = $db->query("SHOW TABLES");
        $tables_after = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('users', $tables_after)) {
            echo "✅ <strong>La tabla 'users' está disponible para usar</strong><br>\n";
        } else {
            echo "❌ <strong>La tabla 'users' aún no está disponible</strong><br>\n";
        }
        
    } else {
        echo "❌ Error: No se pudo conectar a la base de datos<br>\n";
    }
    
} catch (Exception $e) {
    echo "💥 <strong>Error:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<br><hr>\n";
echo "<p><em>Verificación realizada: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>