<?php
/**
 * Deployment Completo a Hostinger
 * Proyecto: Valora.vip
 * Fecha: 2025-11-10
 */

// Configuración FTP
$ftp_server = "212.85.28.237";
$ftp_username = "u179023609.valora.vip";
$ftp_password = "Reylondres7373.";
$ftp_port = 21;
$remote_base = "/public_html/";

// Lista completa de archivos a subir
$files = [
    // Archivos raíz principales
    'index.php',
    'composer.json',
    'web.config',
    
    // Configuraciones (sin ejemplos ni credenciales locales)
    'config/database.php',
    'config/config.php',
    'config/email-config.php',
    'config/configGoogleVision.php',
    'config/configOpenAiChatgpt.php',
    'config/twilioSmsConfig.php',
    'config/configStripchat.php',
    
    // Modelos
    'models/Usuario.php',
    'models/Permisos.php',
    
    // Controladores principales
    'controllers/FinanzasController.php',
    'controllers/ModulosController.php',
    'controllers/PerfilController.php',
    'controllers/PermisosApiController.php',
    'controllers/PermissionsController.php',
    'controllers/TicketController.php',
    'controllers/TwilioController.php',
    'controllers/UserUpdateController.php',
    
    // Servicios
    'services/EmailService.php',
    
    // Componentes
    'components/alertaVerde.php',
    'components/botonContinuar.php',
    'components/footer.php',
    'components/marcaPasos.css',
    'components/marcaPasos.php',
    'components/header/header.php',
    
    // Views - Admin
    'views/admin/index.php',
    'views/admin/permissionsPanel.php',
    
    // Views - Tickets
    'views/tickets/ticketCreate.php',
    'views/tickets/ticketList.php',
    
    // Views - ChecksTests
    'views/checksTests/system-check.php',
    'views/checksTests/test_database_config.php',
    'views/checksTests/test_ftp_connection.php',
    'views/checksTests/test_ftp_interactive.php',
    
    // Assets - CSS
    'assets/css/permissionsPanel.css',
    'assets/css/styles.css',
    
    // Assets - JavaScript
    'assets/js/permissionsPanelAjax.js',
    
    // Scripts de migración importantes
    'add_column_exento.php',
];

echo "🚀 DEPLOYMENT COMPLETO A HOSTINGER\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📡 Servidor: $ftp_server\n";
echo "👤 Usuario: $ftp_username\n";
echo "📦 Archivos a subir: " . count($files) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Conectar FTP
$conn = ftp_connect($ftp_server, $ftp_port);
if (!$conn) {
    die("❌ No se pudo conectar al servidor FTP\n");
}

$login = ftp_login($conn, $ftp_username, $ftp_password);
if (!$login) {
    die("❌ Login FTP falló\n");
}

echo "✅ Conectado al servidor FTP\n\n";
ftp_pasv($conn, true);

$success = 0;
$errors = 0;
$skipped = 0;

// Función para crear directorio recursivamente
function createRemoteDir($conn, $dir, $base) {
    $parts = explode('/', $dir);
    $current = $base;
    
    foreach ($parts as $part) {
        if (empty($part)) continue;
        $current .= $part . '/';
        @ftp_mkdir($conn, $current);
    }
}

// Subir archivos
foreach ($files as $file) {
    $localFile = __DIR__ . '/' . $file;
    $remoteFile = $remote_base . $file;
    
    // Verificar si el archivo existe localmente
    if (!file_exists($localFile)) {
        echo "⚠️  SKIP: $file (no existe localmente)\n";
        $skipped++;
        continue;
    }
    
    echo "📤 Subiendo: $file... ";
    
    // Crear directorio remoto si no existe
    $remoteDir = dirname($remoteFile);
    createRemoteDir($conn, str_replace($remote_base, '', $remoteDir), $remote_base);
    
    // Subir archivo
    if (ftp_put($conn, $remoteFile, $localFile, FTP_BINARY)) {
        echo "✅\n";
        $success++;
    } else {
        echo "❌\n";
        $errors++;
    }
}

ftp_close($conn);

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 RESUMEN DEL DEPLOYMENT\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Archivos subidos correctamente: $success\n";
echo "❌ Errores: $errors\n";
echo "⚠️  Archivos omitidos: $skipped\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($success > 0) {
    echo "🎉 DEPLOYMENT COMPLETADO!\n\n";
    echo "🌐 SIGUIENTE PASO:\n";
    echo "   Ejecutar migración de base de datos:\n";
    echo "   👉 https://valora.vip/add_column_exento.php\n\n";
    echo "🔍 VERIFICAR SITIO:\n";
    echo "   👉 https://valora.vip/\n";
    echo "   👉 https://valora.vip/views/checksTests/system-check.php\n";
    echo "   👉 https://valora.vip/views/admin/permissionsPanel.php\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
?>
