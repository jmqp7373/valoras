<?php
require_once 'config/database.php';
$db = getDBConnection();

echo "=== MÓDULOS EN roles_permisos ===\n";
$stmt = $db->query('SELECT DISTINCT modulo FROM roles_permisos ORDER BY modulo');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - " . $r['modulo'] . "\n";
}

echo "\n=== MÓDULOS EN tabla modulos ===\n";
$stmt = $db->query('SELECT clave, titulo FROM modulos ORDER BY clave');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$r['clave']} ({$r['titulo']})\n";
}
?>
