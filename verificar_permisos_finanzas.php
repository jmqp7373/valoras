<?php
require_once 'config/database.php';

$db = getDBConnection();

echo "=== VERIFICACIÓN DE PERMISOS: finanzasDashboard ===\n\n";

$stmt = $db->query("
    SELECT rp.id_rol, r.nombre as rol, r.nivel_orden, rp.modulo, rp.puede_ver, rp.puede_editar, rp.puede_eliminar
    FROM roles_permisos rp 
    JOIN roles r ON rp.id_rol = r.id 
    WHERE rp.modulo = 'finanzasDashboard'
    ORDER BY r.nivel_orden
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "❌ NO SE ENCONTRARON PERMISOS PARA finanzasDashboard\n";
    
    // Buscar qué clave tiene el módulo
    echo "\n--- Buscando el módulo en la tabla 'modulos' ---\n";
    $stmt2 = $db->query("SELECT clave, titulo, ruta_completa FROM modulos WHERE ruta_completa LIKE '%finanzas%'");
    $modulos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($modulos as $mod) {
        echo "Clave: {$mod['clave']} | Título: {$mod['titulo']} | Ruta: {$mod['ruta_completa']}\n";
    }
} else {
    foreach ($rows as $row) {
        $ver = $row['puede_ver'] ? '✅ SÍ' : '❌ NO';
        $editar = $row['puede_editar'] ? '✅ SÍ' : '❌ NO';
        $eliminar = $row['puede_eliminar'] ? '✅ SÍ' : '❌ NO';
        
        echo "Rol: {$row['rol']} (nivel {$row['nivel_orden']})\n";
        echo "  Ver: {$ver} | Editar: {$editar} | Eliminar: {$eliminar}\n\n";
    }
}

// Verificar rol del usuario actual
echo "\n=== VERIFICACIÓN DE USUARIO ACTUAL ===\n";
session_start();
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $stmt = $db->prepare("
        SELECT u.id_usuario, u.nombre_usuario, r.nombre as rol, r.id as id_rol, r.nivel_orden
        FROM usuarios u
        JOIN roles r ON u.id_rol = r.id
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Usuario: {$user['nombre_usuario']}\n";
        echo "Rol: {$user['rol']} (ID: {$user['id_rol']}, Nivel: {$user['nivel_orden']})\n";
        
        // Verificar si tiene rol_prueba activo
        if (isset($_SESSION['rol_prueba_id'])) {
            echo "\n🎭 MODO PRUEBA ACTIVO:\n";
            echo "Rol de prueba ID: {$_SESSION['rol_prueba_id']}\n";
            echo "Rol original ID: " . ($_SESSION['rol_original_id'] ?? 'N/A') . "\n";
        }
    }
}
?>
