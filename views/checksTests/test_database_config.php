<?php
/**
 * Script para probar la configuración de base de datos
 */
require_once __DIR__ . '/../../config/database.php';

echo "🔍 Probando configuración de base de datos...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Detectar entorno
$isProduction = isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'valora.vip';
echo "🌐 Entorno detectado: " . ($isProduction ? "PRODUCCIÓN" : "DESARROLLO") . "\n";

if (!$isProduction) {
    echo "🏠 Host: localhost (XAMPP)\n";
    echo "📄 Base de datos: valora_db\n";
    echo "👤 Usuario: root\n";
    echo "🔐 Password: (vacío)\n";
} else {
    echo "🌐 Host: localhost (Hosting)\n";
    echo "📄 Base de datos: u179023609_orvlvi\n";
    echo "👤 Usuario: u179023609_orvlvi\n";
    echo "🔐 Password: [CONFIGURADO]\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Intentar conexión
try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ ¡CONEXIÓN EXITOSA!\n";
        
        // Probar una consulta simple
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 Total usuarios en la base de datos: " . $result['total'] . "\n";
        echo "✅ Consulta de prueba exitosa\n";
        
    } else {
        echo "❌ No se pudo obtener la conexión\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR DE CONEXIÓN:\n";
    echo "🔍 " . $e->getMessage() . "\n";
    echo "\n💡 Posibles soluciones:\n";
    if (!$isProduction) {
        echo "   1. Verificar que XAMPP esté ejecutándose\n";
        echo "   2. Verificar que MySQL esté activo\n";
        echo "   3. Verificar que la base de datos 'valora_db' exista\n";
    } else {
        echo "   1. Verificar credenciales de la base de datos remota\n";
        echo "   2. Verificar que la base de datos esté creada en el hosting\n";
        echo "   3. Importar la estructura y datos si es necesario\n";
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
?>