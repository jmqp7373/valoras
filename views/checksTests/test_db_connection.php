<?php
// Test database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

try {
    require_once __DIR__ . '/../../config/database.php';
    
    echo "<p>✅ Database class loaded successfully</p>";
    
    $db = new Database();
    echo "<p>✅ Database object created successfully</p>";
    
    $conn = $db->getConnection();
    echo "<p>✅ Connection obtained successfully</p>";
    
    // Test a simple query
    $stmt = $conn->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Connected to database: <strong>" . $result['db_name'] . "</strong></p>";
    
    // Test usuarios table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM usuarios");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Usuarios table accessible. Total users: <strong>" . $result['count'] . "</strong></p>";
    
    echo "<h3 style='color: green;'>✅ All tests passed!</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
