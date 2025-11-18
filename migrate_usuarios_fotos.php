<?php
/**
 * MIGRACIÓN: Crear tabla usuarios_fotos y mover columnas de fotos
 * 
 * Objetivo: Separar las fotos del usuario en una tabla independiente
 * 
 * Pasos:
 * 1. Crear tabla usuarios_fotos
 * 2. Migrar datos de usuarios_info a usuarios_fotos
 * 3. Eliminar columnas de fotos de usuarios_info
 */

require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "<h1>📸 MIGRACIÓN: usuarios_fotos</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

try {
    $pdo->beginTransaction();
    
    // ========================================
    // PASO 1: CREAR TABLA usuarios_fotos
    // ========================================
    echo "<h2>📌 PASO 1: Crear tabla usuarios_fotos</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS usuarios_fotos (
        id_foto INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        FotoDePerfil VARCHAR(255) NULL,
        FotoConCedulaEnMano VARCHAR(255) NULL,
        CedulaLadoFrontal VARCHAR(255) NULL,
        CedulaLadoReverso VARCHAR(255) NULL,
        certificado_medico VARCHAR(255) NULL,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
        UNIQUE KEY unique_usuario (id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color:green'>✅ Tabla usuarios_fotos creada exitosamente</p>";
    
    // ========================================
    // PASO 2: VERIFICAR COLUMNAS EN usuarios_info
    // ========================================
    echo "<h2>📌 PASO 2: Verificar columnas existentes</h2>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_info WHERE Field IN ('foto_perfil', 'foto_con_cedula', 'foto_cedula_frente', 'foto_cedula_reverso', 'certificado_medico')");
    $columnas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Columnas encontradas: " . implode(', ', $columnas_existentes) . "</p>";
    
    // ========================================
    // PASO 3: MIGRAR DATOS
    // ========================================
    echo "<h2>📌 PASO 3: Migrar datos a usuarios_fotos</h2>";
    
    // Obtener todos los usuarios con sus fotos
    $sql = "SELECT 
                ui.id_usuario,
                ui.foto_perfil,
                ui.foto_con_cedula,
                ui.foto_cedula_frente,
                ui.foto_cedula_reverso,
                ui.certificado_medico
            FROM usuarios_info ui
            WHERE ui.id_usuario IS NOT NULL";
    
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📊 Total usuarios a migrar: " . count($usuarios) . "</p>";
    
    $migrados = 0;
    $saltados = 0;
    
    foreach ($usuarios as $usuario) {
        // Solo insertar si al menos hay una foto
        $tiene_fotos = !empty($usuario['foto_perfil']) || 
                       !empty($usuario['foto_con_cedula']) || 
                       !empty($usuario['foto_cedula_frente']) || 
                       !empty($usuario['foto_cedula_reverso']) ||
                       !empty($usuario['certificado_medico']);
        
        if ($tiene_fotos) {
            $insertSql = "INSERT INTO usuarios_fotos 
                         (id_usuario, FotoDePerfil, FotoConCedulaEnMano, CedulaLadoFrontal, CedulaLadoReverso, certificado_medico) 
                         VALUES (:id_usuario, :foto_perfil, :foto_con_cedula, :foto_cedula_frente, :foto_cedula_reverso, :certificado_medico)
                         ON DUPLICATE KEY UPDATE
                         FotoDePerfil = VALUES(FotoDePerfil),
                         FotoConCedulaEnMano = VALUES(FotoConCedulaEnMano),
                         CedulaLadoFrontal = VALUES(CedulaLadoFrontal),
                         CedulaLadoReverso = VALUES(CedulaLadoReverso),
                         certificado_medico = VALUES(certificado_medico)";
            
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                ':id_usuario' => $usuario['id_usuario'],
                ':foto_perfil' => $usuario['foto_perfil'],
                ':foto_con_cedula' => $usuario['foto_con_cedula'],
                ':foto_cedula_frente' => $usuario['foto_cedula_frente'],
                ':foto_cedula_reverso' => $usuario['foto_cedula_reverso'],
                ':certificado_medico' => $usuario['certificado_medico']
            ]);
            
            $migrados++;
        } else {
            $saltados++;
        }
    }
    
    echo "<p style='color:green'>✅ Registros migrados: $migrados</p>";
    echo "<p style='color:blue'>ℹ️ Usuarios sin fotos (saltados): $saltados</p>";
    
    // ========================================
    // PASO 4: VERIFICAR MIGRACIÓN
    // ========================================
    echo "<h2>📌 PASO 4: Verificar migración</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios_fotos");
    $total_fotos = $stmt->fetchColumn();
    
    echo "<p>📸 Total registros en usuarios_fotos: <strong>$total_fotos</strong></p>";
    
    // Mostrar muestra de datos
    $stmt = $pdo->query("
        SELECT 
            uf.id_usuario,
            u.nombres,
            u.apellidos,
            uf.FotoDePerfil,
            uf.FotoConCedulaEnMano,
            uf.CedulaLadoFrontal,
            uf.CedulaLadoReverso
        FROM usuarios_fotos uf
        INNER JOIN usuarios u ON uf.id_usuario = u.id_usuario
        LIMIT 5
    ");
    
    echo "<h3>Muestra de datos migrados:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width:100%; font-size:12px'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Foto Perfil</th><th>Foto con Cédula</th><th>Cédula Frontal</th><th>Cédula Reverso</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['id_usuario']}</td>";
        echo "<td>{$row['nombres']} {$row['apellidos']}</td>";
        echo "<td>" . ($row['FotoDePerfil'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($row['FotoConCedulaEnMano'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($row['CedulaLadoFrontal'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($row['CedulaLadoReverso'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // ========================================
    // PASO 5: ELIMINAR COLUMNAS DE usuarios_info
    // ========================================
    echo "<h2>📌 PASO 5: Eliminar columnas de usuarios_info</h2>";
    
    $columnas_eliminar = ['foto_perfil', 'foto_con_cedula', 'foto_cedula_frente', 'foto_cedula_reverso'];
    
    foreach ($columnas_eliminar as $columna) {
        if (in_array($columna, $columnas_existentes)) {
            $sql = "ALTER TABLE usuarios_info DROP COLUMN $columna";
            $pdo->exec($sql);
            echo "<p style='color:green'>✅ Columna <strong>$columna</strong> eliminada</p>";
        } else {
            echo "<p style='color:orange'>⚠️ Columna <strong>$columna</strong> no existe (ya eliminada)</p>";
        }
    }
    
    // certificado_medico se mantiene en usuarios_info por ahora
    echo "<p style='color:blue'>ℹ️ Columna <strong>certificado_medico</strong> se mantiene en usuarios_info</p>";
    
    // Commit
    $pdo->commit();
    
    echo "<hr>";
    echo "<h2 style='color:green'>🎉 MIGRACIÓN COMPLETADA EXITOSAMENTE</h2>";
    
    // Resumen final
    echo "<h3>📋 Resumen:</h3>";
    echo "<ul>";
    echo "<li>✅ Tabla usuarios_fotos creada</li>";
    echo "<li>✅ $migrados registros migrados</li>";
    echo "<li>✅ 4 columnas eliminadas de usuarios_info</li>";
    echo "<li>✅ Foreign Key configurada</li>";
    echo "</ul>";
    
    echo "<h3>📊 Estructura de usuarios_fotos:</h3>";
    $stmt = $pdo->query("DESCRIBE usuarios_fotos");
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
