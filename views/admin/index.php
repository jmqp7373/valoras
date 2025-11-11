<?php
/**
 * Panel de Administración - Valora.vip
 * Acceso a herramientas administrativas y tests del sistema
 */
require_once '../../config/database.php';
startSessionSafely();

// Verificar si el usuario está logueado
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Valora.vip</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="brand">
                <img src="../../assets/images/logos/logo_valora.png" class="logo" alt="Valora Logo">
                <h1>🔧 Panel de Administración</h1>
            </div>
            
            <div style="text-align: left; padding: 20px;">
                <h3>🛠️ Herramientas Disponibles:</h3>
                
                <div style="margin: 20px 0;">
                    <h4>📋 Verificación y Tests:</h4>
                    <div style="margin-left: 20px;">
                        <p><a href="../checksTests/system-check.php" style="color: #882A57; text-decoration: none; font-weight: 500;">
                            🔍 Verificación Completa del Sistema</a> - Estado general de todos los componentes
                        </p>
                        <p><a href="../checksTests/test_database_config.php" style="color: #882A57; text-decoration: none; font-weight: 500;">
                            🗄️ Test de Base de Datos</a> - Verificar conectividad y configuración
                        </p>
                        <p><a href="../checksTests/test_ftp_connection.php" style="color: #882A57; text-decoration: none; font-weight: 500;">
                            📡 Test de Conexión FTP</a> - Probar credenciales de deploy
                        </p>
                        <p><a href="../checksTests/test_ftp_interactive.php" style="color: #882A57; text-decoration: none; font-weight: 500;">
                            🔄 Test FTP Interactivo</a> - Test avanzado con logs detallados
                        </p>
                    </div>
                </div>
                
                <div style="margin: 20px 0;">
                    <h4>⚙️ Configuración y Seguridad:</h4>
                    <div style="margin-left: 20px;">
                        <p><a href="permissionsPanel.php" style="color: #882A57; text-decoration: none; font-weight: 500;">
                            🔐 Administración de Permisos</a> - Gestionar permisos por rol y usuario
                        </p>
                    </div>
                </div>
                
                <div style="margin: 20px 0;">
                    <h4>📊 Información del Sistema:</h4>
                    <div style="margin-left: 20px;">
                        <p>🌐 <strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
                        <p>🐘 <strong>PHP:</strong> <?php echo phpversion(); ?></p>
                        <p>📁 <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
                        <p>🏠 <strong>Host:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?></p>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="../../index.php" style="background: #ee6f92; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block;">
                    🏠 Volver al Dashboard
                </a>
                <a href="../login/login.php" style="background: #8b5a83; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin-left: 10px;">
                    👤 Cambiar Usuario
                </a>
            </div>
        </div>
    </div>
</body>
</html>