<?php
require_once 'config/database.php';
startSessionSafely();

// Verificar si el usuario está logueado
if(!isLoggedIn()) {
    header('Location: views/login.php');
    exit();
}

// Obtener información del usuario
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$user_cedula = $_SESSION['user_cedula'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Valora</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <img src="assets/images/logos/logo_valora.png" class="logo" alt="Valora Logo">
            <div class="user-info">
                <span>Bienvenido, <?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></span>
                <a href="controllers/logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </header>
        
        <main class="dashboard-main">
            <div class="welcome-section">
                <h1>¡Bienvenido a Valora!</h1>
                <p>Has iniciado sesión exitosamente.</p>
                <div class="user-details">
                    <h3>Información del Usuario:</h3>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></p>
                    <p><strong>Cédula:</strong> <?php echo htmlspecialchars($user_cedula); ?></p>
                </div>
            </div>
        </main>
    </div>
    
    <style>
        .dashboard-container {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        .dashboard-header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-header .logo {
            height: 40px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logout-btn {
            background-color: #ee6f92;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #d63384;
        }
        
        .dashboard-main {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-section {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .user-details {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</body>
</html>