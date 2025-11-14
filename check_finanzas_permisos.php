<?php
require_once 'config/database.php';
$db = getDBConnection();

echo "=== PERMISOS PARA: views_finanzas_finanzasDashboard ===\n\n";

$stmt = $db->query("
    SELECT r.nombre, r.nivel_orden, rp.puede_ver, rp.puede_editar, rp.puede_eliminar
    FROM roles_permisos rp
    JOIN roles r ON rp.id_rol = r.id
    WHERE rp.modulo = 'views_finanzas_finanzasDashboard'
    ORDER BY r.nivel_orden
");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ver = $row['puede_ver'] ? '✅' : '❌';
    $editar = $row['puede_editar'] ? '✅' : '❌';
    $eliminar = $row['puede_eliminar'] ? '✅' : '❌';
    
    echo "{$row['nombre']} (nivel {$row['nivel_orden']}): Ver {$ver} | Editar {$editar} | Eliminar {$eliminar}\n";
}

echo "\n=== ROL: modelo ===\n";
$stmt = $db->query("
    SELECT id, nombre, nivel_orden FROM roles WHERE nombre = 'modelo'
");
$modelo = $stmt->fetch(PDO::FETCH_ASSOC);
if ($modelo) {
    echo "ID: {$modelo['id']} | Nivel: {$modelo['nivel_orden']}\n";
}
?>
