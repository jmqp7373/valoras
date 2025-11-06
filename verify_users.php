<?php
require_once 'config/database.php';

echo "=== VERIFICACIÓN DE USUARIOS REGISTRADOS ===\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Últimos 5 usuarios registrados:\n";
    $stmt = $db->query("SELECT id_usuario, usuario, nombres, apellidos, cedula, email FROM usuarios ORDER BY id_usuario DESC LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "No hay usuarios registrados en la base de datos\n";
    } else {
        foreach ($users as $user) {
            echo sprintf("ID: %s, Usuario: \"%s\", Nombre: %s %s, Cédula: %s, Email: \"%s\"\n", 
                $user['id_usuario'], $user['usuario'], $user['nombres'], $user['apellidos'], $user['cedula'], $user['email']);
        }
    }
    
    echo "\nVerificando usuario específico (cédula: 12345678):\n";
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE cedula = :cedula");
    $stmt->bindParam(':cedula', $cedula);
    $cedula = '12345678';
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Usuario encontrado:\n";
        foreach ($user as $key => $value) {
            echo "  $key: '$value'\n";
        }
    } else {
        echo "❌ Usuario con cédula 12345678 no encontrado\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>