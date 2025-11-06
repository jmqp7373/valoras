<?php
/**
 * Script de diagnóstico para error 500
 * ELIMINAR DESPUÉS DE USAR
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Diagnóstico de Error 500 - Valora.vip</h2>";
echo "<hr>";

// 1. Verificar PHP
echo "<h3>✅ PHP funcionando</h3>";
echo "Versión PHP: " . phpversion() . "<br>";
echo "Host: " . $_SERVER['HTTP_HOST'] . "<br><br>";

// 2. Verificar archivos de configuración
echo "<h3>📁 Verificar archivos de configuración:</h3>";
$configFiles = [
    'config/database.php',
    'config/config.php',
    'config/email-config.php',
    'config/twilioSmsConfig.php'
];

foreach($configFiles as $file) {
    $exists = file_exists($file);
    $icon = $exists ? '✅' : '❌';
    echo "$icon $file: " . ($exists ? 'EXISTE' : 'NO EXISTE') . "<br>";
}
echo "<br>";

// 3. Verificar conexión a base de datos
echo "<h3>🗄️ Verificar conexión a base de datos:</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if($db) {
        echo "✅ Conexión a base de datos exitosa<br>";
        
        // Verificar tabla usuarios
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Tabla 'usuarios' existe - Total registros: " . $result['total'] . "<br>";
    } else {
        echo "❌ No se pudo conectar a la base de datos<br>";
    }
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 4. Verificar sesiones
echo "<h3>🔐 Verificar sesiones:</h3>";
try {
    if(function_exists('startSessionSafely')) {
        startSessionSafely();
        echo "✅ Función startSessionSafely() existe<br>";
    } else {
        echo "❌ Función startSessionSafely() NO existe<br>";
    }
    
    if(function_exists('isLoggedIn')) {
        echo "✅ Función isLoggedIn() existe<br>";
    } else {
        echo "❌ Función isLoggedIn() NO existe<br>";
    }
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 5. Verificar directorios principales
echo "<h3>📂 Verificar directorios:</h3>";
$dirs = ['views', 'controllers', 'models', 'assets', 'config'];
foreach($dirs as $dir) {
    $exists = is_dir($dir);
    $icon = $exists ? '✅' : '❌';
    echo "$icon $dir/: " . ($exists ? 'EXISTE' : 'NO EXISTE') . "<br>";
}

echo "<br>";
echo "<hr>";
echo "<p style='color: red;'><strong>⚠️ ELIMINAR ESTE ARCHIVO DESPUÉS DE DIAGNOSTICAR</strong></p>";
?>
