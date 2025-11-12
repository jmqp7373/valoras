<?php
/**
 * Subir script de setup de roles
 */

$ftpServer = '212.85.28.237';
$ftpUser = 'u179023609.valora.vip';
$ftpPass = 'Reylondres7373.';
$remotePath = '/public_html/';

echo "🚀 Subiendo setup_roles_complete.php...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$conn = ftp_connect($ftpServer);
if (!$conn) {
    die("❌ No se pudo conectar al servidor FTP\n");
}

$login = ftp_login($conn, $ftpUser, $ftpPass);
if (!$login) {
    die("❌ Login FTP falló\n");
}

ftp_pasv($conn, true);

$localFile = __DIR__ . '/setup_roles_complete.php';
$remoteFile = $remotePath . 'setup_roles_complete.php';

echo "📤 Subiendo archivo...\n";

if (ftp_put($conn, $remoteFile, $localFile, FTP_BINARY)) {
    echo "✅ Archivo subido correctamente\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔗 Ejecuta el setup:\n";
    echo "   👉 https://valora.vip/setup_roles_complete.php\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
} else {
    echo "❌ Error al subir archivo\n";
}

ftp_close($conn);
?>
