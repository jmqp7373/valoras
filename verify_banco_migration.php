<?php
/**
 * Verificación completa de la migración banco_nombre → id_banco
 */

require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "<h1>🔍 VERIFICACIÓN DE MIGRACIÓN: banco_nombre → id_banco</h1>";
echo "<hr>";

// ========================================
// 1. VERIFICAR ESTRUCTURA DE usuarios_info
// ========================================
echo "<h2>1️⃣ Estructura de usuarios_info</h2>";

$stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info WHERE Field IN ('id_banco', 'banco_nombre', 'banco_tipo_cuenta', 'banco_numero_cuenta')");
$columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Key</th><th>Default</th><th>Estado</th></tr>";

$tiene_id_banco = false;
$tiene_banco_nombre = false;

foreach ($columnas as $col) {
    $estado = '✅';
    if ($col['Field'] === 'id_banco') {
        $tiene_id_banco = true;
        $estado = '✅ Nuevo campo';
    }
    if ($col['Field'] === 'banco_nombre') {
        $tiene_banco_nombre = true;
        $estado = '⚠️ Debería estar eliminado';
    }
    
    echo "<tr>";
    echo "<td><strong>{$col['Field']}</strong></td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Key']}</td>";
    echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
    echo "<td>$estado</td>";
    echo "</tr>";
}
echo "</table>";

if (!$tiene_id_banco) {
    echo "<p style='color:red'>❌ ERROR: La columna id_banco NO existe</p>";
}
if ($tiene_banco_nombre) {
    echo "<p style='color:orange'>⚠️ ADVERTENCIA: La columna banco_nombre aún existe (debería estar eliminada)</p>";
}

// ========================================
// 2. VERIFICAR FOREIGN KEY
// ========================================
echo "<h2>2️⃣ Foreign Key id_banco → usuarios_bancos</h2>";

$stmt = $pdo->query("
    SELECT 
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'usuarios_info' 
    AND COLUMN_NAME = 'id_banco'
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if ($stmt->rowCount() > 0) {
    $fk = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p style='color:green'>✅ Foreign Key configurada correctamente:</p>";
    echo "<ul>";
    echo "<li>Constraint: <strong>{$fk['CONSTRAINT_NAME']}</strong></li>";
    echo "<li>Referencia: <strong>{$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</strong></li>";
    echo "</ul>";
} else {
    echo "<p style='color:red'>❌ ERROR: No se encontró Foreign Key en id_banco</p>";
}

// ========================================
// 3. ESTADÍSTICAS DE DATOS
// ========================================
echo "<h2>3️⃣ Estadísticas de Datos</h2>";

$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_usuarios,
        SUM(CASE WHEN id_banco IS NOT NULL THEN 1 ELSE 0 END) as con_banco,
        SUM(CASE WHEN id_banco IS NULL THEN 1 ELSE 0 END) as sin_banco,
        SUM(CASE WHEN banco_numero_cuenta IS NOT NULL AND banco_numero_cuenta != '' THEN 1 ELSE 0 END) as con_numero_cuenta
    FROM usuarios_info
");

$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse'>";
echo "<tr><th>Métrica</th><th>Valor</th><th>%</th></tr>";
echo "<tr><td>Total usuarios</td><td>{$stats['total_usuarios']}</td><td>100%</td></tr>";

$pct_con_banco = $stats['total_usuarios'] > 0 ? round(($stats['con_banco'] / $stats['total_usuarios']) * 100, 1) : 0;
$color = $pct_con_banco > 80 ? 'green' : ($pct_con_banco > 50 ? 'orange' : 'red');
echo "<tr><td>Con banco asignado (id_banco)</td><td style='color:$color'>{$stats['con_banco']}</td><td>{$pct_con_banco}%</td></tr>";

echo "<tr><td>Sin banco asignado</td><td>{$stats['sin_banco']}</td><td>" . round(($stats['sin_banco'] / $stats['total_usuarios']) * 100, 1) . "%</td></tr>";
echo "<tr><td>Con número de cuenta</td><td>{$stats['con_numero_cuenta']}</td><td>" . round(($stats['con_numero_cuenta'] / $stats['total_usuarios']) * 100, 1) . "%</td></tr>";
echo "</table>";

// ========================================
// 4. DISTRIBUCIÓN POR BANCO
// ========================================
echo "<h2>4️⃣ Distribución de Usuarios por Banco</h2>";

$stmt = $pdo->query("
    SELECT 
        ub.id_banco,
        ub.nombre_banco,
        ub.tipo_banco,
        ub.codigo_abreviado,
        ub.color_banco,
        COUNT(ui.id_info) as total_usuarios
    FROM usuarios_bancos ub
    LEFT JOIN usuarios_info ui ON ub.id_banco = ui.id_banco
    WHERE ub.estado = 1
    GROUP BY ub.id_banco
    ORDER BY total_usuarios DESC, ub.nombre_banco ASC
");

echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%'>";
echo "<tr><th>Banco</th><th>Tipo</th><th>Código</th><th>Color</th><th>Usuarios</th></tr>";

$total_asignados = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total_asignados += $row['total_usuarios'];
    $colorIndicador = "<span style='display:inline-block; width:20px; height:20px; background:{$row['color_banco']}; border-radius:4px; vertical-align:middle'></span>";
    
    echo "<tr>";
    echo "<td><strong>{$row['nombre_banco']}</strong></td>";
    echo "<td>{$row['tipo_banco']}</td>";
    echo "<td>{$row['codigo_abreviado']}</td>";
    echo "<td>$colorIndicador {$row['color_banco']}</td>";
    echo "<td style='text-align:center'><strong>{$row['total_usuarios']}</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Total usuarios con banco asignado: $total_asignados</strong></p>";

// ========================================
// 5. MUESTRA DE DATOS
// ========================================
echo "<h2>5️⃣ Muestra de Datos (Primeros 5 usuarios con banco)</h2>";

$stmt = $pdo->query("
    SELECT 
        u.id_usuario,
        u.nombres,
        u.apellidos,
        ui.id_banco,
        ub.nombre_banco,
        ub.tipo_banco,
        ui.banco_tipo_cuenta,
        ui.banco_numero_cuenta
    FROM usuarios u
    INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
    LEFT JOIN usuarios_bancos ub ON ui.id_banco = ub.id_banco
    WHERE ui.id_banco IS NOT NULL
    LIMIT 5
");

if ($stmt->rowCount() > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Banco</th><th>Tipo Banco</th><th>Tipo Cuenta</th><th>Número Cuenta</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['id_usuario']}</td>";
        echo "<td>{$row['nombres']} {$row['apellidos']}</td>";
        echo "<td><strong>{$row['nombre_banco']}</strong></td>";
        echo "<td>{$row['tipo_banco']}</td>";
        echo "<td>{$row['banco_tipo_cuenta']}</td>";
        echo "<td>{$row['banco_numero_cuenta']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠️ No hay usuarios con banco asignado</p>";
}

// ========================================
// 6. VERIFICAR CONTROLLERS
// ========================================
echo "<h2>6️⃣ Archivos Actualizados</h2>";

$archivos = [
    'controllers/PerfilController.php' => ['obtenerBancos', 'id_banco'],
    'controllers/PerfilAutosaveController.php' => ['id_banco'],
    'models/Usuario.php' => ['id_banco', 'usuarios_bancos'],
    'views/usuario/miPerfil.php' => ['id_banco', 'obtenerBancos']
];

echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%'>";
echo "<tr><th>Archivo</th><th>Verificación</th><th>Estado</th></tr>";

foreach ($archivos as $archivo => $buscar) {
    $ruta = __DIR__ . '/' . $archivo;
    if (file_exists($ruta)) {
        $contenido = file_get_contents($ruta);
        $encontrado = true;
        $faltantes = [];
        
        foreach ($buscar as $patron) {
            if (strpos($contenido, $patron) === false) {
                $encontrado = false;
                $faltantes[] = $patron;
            }
        }
        
        if ($encontrado) {
            echo "<tr><td>$archivo</td><td>✅ Contiene: " . implode(', ', $buscar) . "</td><td style='color:green'>OK</td></tr>";
        } else {
            echo "<tr><td>$archivo</td><td>⚠️ Falta: " . implode(', ', $faltantes) . "</td><td style='color:orange'>REVISAR</td></tr>";
        }
    } else {
        echo "<tr><td>$archivo</td><td>❌ No existe</td><td style='color:red'>ERROR</td></tr>";
    }
}
echo "</table>";

// ========================================
// RESUMEN FINAL
// ========================================
echo "<hr>";
echo "<h2>📊 RESUMEN FINAL</h2>";

$errores = 0;
$advertencias = 0;

if (!$tiene_id_banco) {
    echo "<p style='color:red'>❌ Columna id_banco no existe</p>";
    $errores++;
}
if ($tiene_banco_nombre) {
    echo "<p style='color:orange'>⚠️ Columna banco_nombre aún existe</p>";
    $advertencias++;
}

if ($stats['sin_banco'] > 0 && $stats['con_numero_cuenta'] > $stats['con_banco']) {
    echo "<p style='color:orange'>⚠️ Hay usuarios con número de cuenta pero sin banco asignado</p>";
    $advertencias++;
}

if ($errores === 0 && $advertencias === 0) {
    echo "<h3 style='color:green'>✅ MIGRACIÓN COMPLETADA EXITOSAMENTE</h3>";
    echo "<p>Todos los componentes están correctamente configurados.</p>";
} else {
    echo "<h3 style='color:orange'>⚠️ MIGRACIÓN COMPLETADA CON ADVERTENCIAS</h3>";
    echo "<p>Errores: $errores | Advertencias: $advertencias</p>";
}

echo "<hr>";
echo "<p><em>Fecha de verificación: " . date('Y-m-d H:i:s') . "</em></p>";
