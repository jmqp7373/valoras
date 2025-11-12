<?php
// Simplified test for password reset controller
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing PasswordResetController</h2>";

try {
    require_once __DIR__ . '/../../config/database.php';
    echo "<p>✅ Database config loaded</p>";
    
    require_once __DIR__ . '/../../controllers/login/PasswordResetController.php';
    echo "<p>✅ PasswordResetController class loaded</p>";
    
    $controller = new PasswordResetController();
    echo "<p>✅ PasswordResetController instantiated</p>";
    
    // Test findUser method
    $result = $controller->findUser('1125998052', 'cedula');
    
    echo "<h3>Result:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if($result['success']) {
        echo "<p style='color: green;'>✅ User found successfully!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ " . htmlspecialchars($result['message']) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h4>Stack trace:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
