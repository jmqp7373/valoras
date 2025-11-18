<?php
/**
 * MIGRACIÓN: banco_nombre → id_banco
 * 
 * Objetivo: Reemplazar el campo de texto banco_nombre por una relación FK con usuarios_bancos
 * 
 * Pasos:
 * 1. Agregar columna id_banco INT a usuarios_info (antes de banco_nombre)
 * 2. Migrar datos: correlacionar banco_nombre → usuarios_bancos.id_banco
 * 3. Eliminar columna banco_nombre
 * 4. Agregar FK constraint
 */

require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "<h1>🏦 MIGRACIÓN: banco_nombre → id_banco</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

try {
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // ========================================
    // PASO 1: AGREGAR COLUMNA id_banco
    // ========================================
    echo "<h2>📌 PASO 1: Agregar columna id_banco</h2>";
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info LIKE 'id_banco'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:orange'>⚠️ La columna id_banco ya existe. Saltando paso...</p>";
    } else {
        // Agregar columna id_banco ANTES de banco_nombre
        $sql = "ALTER TABLE usuarios_info 
                ADD COLUMN id_banco INT NULL 
                AFTER banco_numero_cuenta";
        
        $pdo->exec($sql);
        echo "<p style='color:green'>✅ Columna id_banco agregada exitosamente</p>";
    }
    
    // ========================================
    // PASO 2: OBTENER MAPEO DE BANCOS
    // ========================================
    echo "<h2>📌 PASO 2: Obtener mapeo de bancos</h2>";
    
    $stmt = $pdo->query("SELECT id_banco, nombre_banco FROM usuarios_bancos WHERE estado = 1");
    $bancos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>🏦 Bancos disponibles:</p>";
    echo "<ul>";
    foreach ($bancos as $banco) {
        echo "<li>ID: {$banco['id_banco']} - {$banco['nombre_banco']}</li>";
    }
    echo "</ul>";
    
    // Crear mapa de correlación nombre → id_banco
    $mapaBancos = [];
    foreach ($bancos as $banco) {
        // Normalizar nombre (sin tildes, minúsculas, sin espacios extras)
        $nombreNormalizado = strtolower(trim($banco['nombre_banco']));
        $mapaBancos[$nombreNormalizado] = $banco['id_banco'];
        
        // Agregar variaciones comunes
        if (strpos($nombreNormalizado, 'bancolombia') !== false) {
            $mapaBancos['bancolombia'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'davivienda') !== false) {
            $mapaBancos['davivienda'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'nequi') !== false) {
            $mapaBancos['nequi'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'bogotá') !== false || strpos($nombreNormalizado, 'bogota') !== false) {
            $mapaBancos['banco de bogota'] = $banco['id_banco'];
            $mapaBancos['bogota'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'occidente') !== false) {
            $mapaBancos['occidente'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'bbva') !== false) {
            $mapaBancos['bbva'] = $banco['id_banco'];
            $mapaBancos['bbva colombia'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'agrario') !== false) {
            $mapaBancos['agrario'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'colpatria') !== false || strpos($nombreNormalizado, 'scotiabank') !== false) {
            $mapaBancos['colpatria'] = $banco['id_banco'];
            $mapaBancos['scotiabank'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'av villas') !== false) {
            $mapaBancos['av villas'] = $banco['id_banco'];
            $mapaBancos['villas'] = $banco['id_banco'];
        }
        if (strpos($nombreNormalizado, 'caja social') !== false) {
            $mapaBancos['caja social'] = $banco['id_banco'];
        }
    }
    
    echo "<p style='color:blue'>📋 Mapeo de variaciones creado: " . count($mapaBancos) . " patrones</p>";
    
    // ========================================
    // PASO 3: MIGRAR DATOS
    // ========================================
    echo "<h2>📌 PASO 3: Migrar datos banco_nombre → id_banco</h2>";
    
    $stmt = $pdo->query("
        SELECT id_info, banco_nombre 
        FROM usuarios_info 
        WHERE banco_nombre IS NOT NULL 
        AND banco_nombre != ''
        AND (id_banco IS NULL OR id_banco = 0)
    ");
    
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>📊 Registros a migrar: " . count($registros) . "</p>";
    
    $exitosos = 0;
    $fallidos = 0;
    $noEncontrados = [];
    
    foreach ($registros as $registro) {
        $bancoNombre = strtolower(trim($registro['banco_nombre']));
        $idInfo = $registro['id_info'];
        
        // Buscar en el mapa
        $idBancoEncontrado = null;
        
        // Buscar coincidencia exacta
        if (isset($mapaBancos[$bancoNombre])) {
            $idBancoEncontrado = $mapaBancos[$bancoNombre];
        } else {
            // Buscar coincidencia parcial
            foreach ($mapaBancos as $patron => $idBanco) {
                if (strpos($bancoNombre, $patron) !== false || strpos($patron, $bancoNombre) !== false) {
                    $idBancoEncontrado = $idBanco;
                    break;
                }
            }
        }
        
        if ($idBancoEncontrado) {
            // Actualizar registro
            $updateStmt = $pdo->prepare("
                UPDATE usuarios_info 
                SET id_banco = :id_banco 
                WHERE id_info = :id_info
            ");
            $updateStmt->execute([
                ':id_banco' => $idBancoEncontrado,
                ':id_info' => $idInfo
            ]);
            
            $exitosos++;
        } else {
            $fallidos++;
            $noEncontrados[] = $registro['banco_nombre'];
        }
    }
    
    echo "<p style='color:green'>✅ Registros migrados exitosamente: $exitosos</p>";
    
    if ($fallidos > 0) {
        echo "<p style='color:orange'>⚠️ Registros no correlacionados: $fallidos</p>";
        echo "<ul>";
        foreach (array_unique($noEncontrados) as $banco) {
            echo "<li>$banco</li>";
        }
        echo "</ul>";
        echo "<p style='color:blue'>💡 Estos registros conservarán id_banco = NULL y deberán actualizarse manualmente</p>";
    }
    
    // ========================================
    // PASO 4: VERIFICACIÓN
    // ========================================
    echo "<h2>📌 PASO 4: Verificación de migración</h2>";
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN id_banco IS NOT NULL THEN 1 ELSE 0 END) as con_id_banco,
            SUM(CASE WHEN id_banco IS NULL AND banco_nombre IS NOT NULL THEN 1 ELSE 0 END) as sin_id_banco
        FROM usuarios_info
    ");
    
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse'>";
    echo "<tr><th>Total registros</th><td>{$stats['total']}</td></tr>";
    echo "<tr><th>Con id_banco</th><td style='color:green'>{$stats['con_id_banco']}</td></tr>";
    echo "<tr><th>Sin id_banco (banco_nombre != NULL)</th><td style='color:" . ($stats['sin_id_banco'] > 0 ? 'orange' : 'green') . "'>{$stats['sin_id_banco']}</td></tr>";
    echo "</table>";
    
    // ========================================
    // PASO 5: ELIMINAR COLUMNA banco_nombre
    // ========================================
    echo "<h2>📌 PASO 5: Eliminar columna banco_nombre</h2>";
    
    if ($stats['sin_id_banco'] > 0) {
        echo "<p style='color:red'>❌ NO SE ELIMINARÁ banco_nombre porque hay {$stats['sin_id_banco']} registros sin migrar</p>";
        echo "<p>👉 Corrige manualmente estos registros y vuelve a ejecutar este script</p>";
    } else {
        // Verificar si la columna existe
        $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info LIKE 'banco_nombre'");
        if ($stmt->rowCount() > 0) {
            $sql = "ALTER TABLE usuarios_info DROP COLUMN banco_nombre";
            $pdo->exec($sql);
            echo "<p style='color:green'>✅ Columna banco_nombre eliminada exitosamente</p>";
        } else {
            echo "<p style='color:orange'>⚠️ La columna banco_nombre ya no existe</p>";
        }
    }
    
    // ========================================
    // PASO 6: AGREGAR FOREIGN KEY
    // ========================================
    echo "<h2>📌 PASO 6: Agregar Foreign Key</h2>";
    
    // Verificar si ya existe la FK
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'usuarios_info' 
        AND COLUMN_NAME = 'id_banco'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:orange'>⚠️ La Foreign Key ya existe</p>";
    } else {
        $sql = "ALTER TABLE usuarios_info 
                ADD CONSTRAINT fk_usuarios_info_banco 
                FOREIGN KEY (id_banco) 
                REFERENCES usuarios_bancos(id_banco)
                ON DELETE SET NULL
                ON UPDATE CASCADE";
        
        $pdo->exec($sql);
        echo "<p style='color:green'>✅ Foreign Key agregada exitosamente</p>";
    }
    
    // Commit transacción
    $pdo->commit();
    
    echo "<hr>";
    echo "<h2 style='color:green'>🎉 MIGRACIÓN COMPLETADA EXITOSAMENTE</h2>";
    
    // Mostrar estructura final
    echo "<h3>📋 Estructura final de usuarios_info (campos banco):</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info WHERE Field LIKE 'banco%' OR Field = 'id_banco'");
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<hr>";
    echo "<h2 style='color:red'>❌ ERROR EN LA MIGRACIÓN</h2>";
    echo "<p style='color:red'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
