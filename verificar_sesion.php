<?php
/**
 * Script de prueba para verificar datos de sesión
 */

require_once __DIR__ . '/config/database.php';
startSessionSafely();

echo "<h2>Verificación de Sesión Actual</h2>";
echo "<pre>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NO DEFINIDO') . "\n";
echo "user_nombres: " . ($_SESSION['user_nombres'] ?? 'NO DEFINIDO') . "\n";
echo "user_apellidos: " . ($_SESSION['user_apellidos'] ?? 'NO DEFINIDO') . "\n";
echo "user_email: " . ($_SESSION['user_email'] ?? 'NO DEFINIDO') . "\n";
echo "user_cedula: " . ($_SESSION['user_cedula'] ?? 'NO DEFINIDO') . "\n";
echo "logged_in: " . ($_SESSION['logged_in'] ?? 'NO DEFINIDO') . "\n";
echo "</pre>";

// Verificar datos en la base de datos
if (isset($_SESSION['user_id'])) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("
            SELECT 
                u.id_usuario,
                u.usuario,
                u.nombres,
                u.apellidos,
                ui.cedula,
                ui.celular,
                ui.email
            FROM usuarios u
            LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
            WHERE u.id_usuario = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h2>Datos en Base de Datos</h2>";
        echo "<pre>";
        print_r($user_data);
        echo "</pre>";
        
        // Comparar
        echo "<h2>Comparación</h2>";
        if ($user_data) {
            echo "<pre>";
            echo "¿Nombres coinciden? " . (($_SESSION['user_nombres'] ?? '') === ($user_data['nombres'] ?? '') ? 'SÍ' : 'NO') . "\n";
            echo "¿Apellidos coinciden? " . (($_SESSION['user_apellidos'] ?? '') === ($user_data['apellidos'] ?? '') ? 'SÍ' : 'NO') . "\n";
            echo "¿Email coincide? " . (($_SESSION['user_email'] ?? '') === ($user_data['email'] ?? '') ? 'SÍ' : 'NO') . "\n";
            echo "</pre>";
            
            // Sugerencia
            if (($_SESSION['user_nombres'] ?? '') !== ($user_data['nombres'] ?? '') || 
                ($_SESSION['user_apellidos'] ?? '') !== ($user_data['apellidos'] ?? '')) {
                echo "<h3 style='color: red;'>⚠️ PROBLEMA DETECTADO</h3>";
                echo "<p>Los datos de la sesión NO coinciden con la base de datos.</p>";
                echo "<p><strong>Solución:</strong> Cerrar sesión y volver a iniciar sesión para actualizar los datos.</p>";
                echo "<p><a href='controllers/login/logout.php' style='padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;'>Cerrar Sesión</a></p>";
            } else {
                echo "<h3 style='color: green;'>✓ Datos correctos</h3>";
                echo "<p>Los datos de la sesión coinciden con la base de datos.</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>No hay sesión activa. Por favor inicie sesión.</p>";
}
?>
