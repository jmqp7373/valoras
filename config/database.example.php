<?php
/**
 * Configuración de Base de Datos - PLANTILLA
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'database.php' (en esta misma carpeta config/)
 * 2. Reemplaza los valores de ejemplo con tus credenciales reales
 * 3. NO subas database.php al repositorio (está en .gitignore)
 */

// Configuración de la base de datos
$host = 'localhost';           // Host del servidor MySQL
$db_name = 'nombre_base_datos'; // Nombre de tu base de datos
$username = 'tu_usuario';       // Usuario de MySQL
$password = 'tu_contraseña';    // Contraseña de MySQL

// Crear conexión PDO
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error al conectar con la base de datos. Por favor, contacta al administrador.");
}
?>
