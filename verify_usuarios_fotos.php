<?php
/**
 * Verificación de migración usuarios_fotos
 */

require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "<h1>🔍 VERIFICACIÓN: usuarios_fotos</h1>";
echo "<hr>";

// 1. Verificar tabla existe
echo "<h2>1️⃣ Estructura de usuarios_fotos</h2>";
$stmt = $pdo->query("SHOW TABLES LIKE 'usuarios_fotos'");
if ($stmt->rowCount() > 0) {
    echo "<p style='color:green'>✅ Tabla usuarios_fotos existe</p>";
    
    $stmt = $pdo->query("DESCRIBE usuarios_fotos");
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Key</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td><strong>{$row['Field']}</strong></td><td>{$row['Type']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>❌ Tabla usuarios_fotos NO existe</p>";
}

// 2. Verificar datos
echo "<h2>2️⃣ Estadísticas</h2>";
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN FotoDePerfil IS NOT NULL THEN 1 ELSE 0 END) as con_foto_perfil,
        SUM(CASE WHEN FotoConCedulaEnMano IS NOT NULL THEN 1 ELSE 0 END) as con_foto_cedula,
        SUM(CASE WHEN CedulaLadoFrontal IS NOT NULL THEN 1 ELSE 0 END) as con_cedula_frente,
        SUM(CASE WHEN CedulaLadoReverso IS NOT NULL THEN 1 ELSE 0 END) as con_cedula_reverso
    FROM usuarios_fotos
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse'>";
echo "<tr><th>Métrica</th><th>Cantidad</th></tr>";
echo "<tr><td>Total registros</td><td><strong>{$stats['total']}</strong></td></tr>";
echo "<tr><td>Con FotoDePerfil</td><td>{$stats['con_foto_perfil']}</td></tr>";
echo "<tr><td>Con FotoConCedulaEnMano</td><td>{$stats['con_foto_cedula']}</td></tr>";
echo "<tr><td>Con CedulaLadoFrontal</td><td>{$stats['con_cedula_frente']}</td></tr>";
echo "<tr><td>Con CedulaLadoReverso</td><td>{$stats['con_cedula_reverso']}</td></tr>";
echo "</table>";

// 3. Verificar que columnas fueron eliminadas de usuarios_info
echo "<h2>3️⃣ Verificar limpieza de usuarios_info</h2>";
$stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info WHERE Field IN ('foto_perfil', 'foto_con_cedula', 'foto_cedula_frente', 'foto_cedula_reverso')");
if ($stmt->rowCount() > 0) {
    echo "<p style='color:orange'>⚠️ Aún existen columnas de fotos en usuarios_info:</p>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>{$row['Field']}</li>";
    }
} else {
    echo "<p style='color:green'>✅ Todas las columnas de fotos fueron eliminadas de usuarios_info</p>";
}

// 4. Probar query del modelo
echo "<h2>4️⃣ Probar query de obtenerPerfil</h2>";
$stmt = $pdo->query("
    SELECT u.id_usuario, u.nombres, u.apellidos,
           uf.FotoDePerfil as foto_perfil,
           uf.FotoConCedulaEnMano as foto_con_cedula,
           uf.CedulaLadoFrontal as foto_cedula_frente,
           uf.CedulaLadoReverso as foto_cedula_reverso
    FROM usuarios u
    LEFT JOIN usuarios_fotos uf ON u.id_usuario = uf.id_usuario
    WHERE uf.FotoDePerfil IS NOT NULL
    LIMIT 5
");

if ($stmt->rowCount() > 0) {
    echo "<p style='color:green'>✅ Query funciona correctamente</p>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width:100%'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Foto Perfil</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $foto = basename($row['foto_perfil']);
        echo "<tr><td>{$row['id_usuario']}</td><td>{$row['nombres']} {$row['apellidos']}</td><td>$foto</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠️ No hay usuarios con fotos para mostrar</p>";
}

echo "<hr>";
echo "<h2 style='color:green'>✅ VERIFICACIÓN COMPLETADA</h2>";
