<?php
/**
 * Script de Migración: Reorganización de tabla usuarios
 * 
 * MANTIENE en usuarios:
 * - id_usuario (PK)
 * - fecha_creacion
 * - usuario (username único)
 * - nombres
 * - apellidos
 * - password
 * - estado
 * - id_rol
 * 
 * MUEVE a usuarios_info (nueva tabla):
 * - Todo lo demás (información personal, médica, bancaria, etc.)
 */

require_once __DIR__ . '/config/database.php';

class MigracionUsuarios {
    private $conn;
    private $log = [];
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    public function ejecutar() {
        echo "<h1>🔄 Migración de Tabla Usuarios</h1>";
        echo "<style>
            body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
            h1, h2, h3 { color: #4ec9b0; }
            .success { color: #4ec9b0; }
            .error { color: #f48771; }
            .warning { color: #dcdcaa; }
            .info { color: #9cdcfe; }
            pre { background: #2d2d30; padding: 15px; border-radius: 5px; overflow-x: auto; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #3e3e42; padding: 10px; text-align: left; }
            th { background: #2d2d30; }
            .step { margin: 20px 0; padding: 15px; background: #252526; border-left: 4px solid #4ec9b0; }
        </style>";
        
        try {
            // Desactivar autocommit para transacción manual
            $this->conn->beginTransaction();
            
            // PASO 1: Verificar tabla actual
            $this->paso1_verificarTablaActual();
            
            // PASO 2: Crear tabla usuarios_info
            $this->paso2_crearTablaUsuariosInfo();
            
            // PASO 3: Migrar datos
            $this->paso3_migrarDatos();
            
            // PASO 4: Verificar migración
            $this->paso4_verificarMigracion();
            
            // PASO 5: Eliminar columnas antiguas
            $this->paso5_eliminarColumnasAntiguas();
            
            // PASO 6: Verificación final
            $this->paso6_verificacionFinal();
            
            // Confirmar transacción
            $this->conn->commit();
            
            echo "<div class='step success'>";
            echo "<h2>✅ MIGRACIÓN COMPLETADA EXITOSAMENTE</h2>";
            echo "<p>La tabla usuarios ha sido reorganizada correctamente.</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $this->conn->rollBack();
            
            echo "<div class='step error'>";
            echo "<h2>❌ ERROR EN LA MIGRACIÓN</h2>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "<p>Se han revertido todos los cambios.</p>";
            echo "</div>";
        }
        
        $this->mostrarLog();
    }
    
    private function paso1_verificarTablaActual() {
        echo "<div class='step'>";
        echo "<h2>📊 PASO 1: Verificando tabla actual</h2>";
        
        // Obtener estructura actual
        $stmt = $this->conn->query("DESCRIBE usuarios");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p class='info'>Total de columnas actuales: " . count($columnas) . "</p>";
        
        // Contar registros
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM usuarios");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "<p class='info'>Total de registros: {$total}</p>";
        
        $this->log[] = "✓ Verificación inicial completada: {$total} usuarios, " . count($columnas) . " columnas";
        echo "</div>";
    }
    
    private function paso2_crearTablaUsuariosInfo() {
        echo "<div class='step'>";
        echo "<h2>🏗️ PASO 2: Creando tabla usuarios_info</h2>";
        
        $sql = "CREATE TABLE IF NOT EXISTS `usuarios_info` (
            `id_info` INT(11) NOT NULL AUTO_INCREMENT,
            `id_usuario` INT(11) NOT NULL,
            
            -- Información básica
            `disponibilidad` VARCHAR(100) NOT NULL DEFAULT '0',
            `id_estudio` INT(11) NOT NULL DEFAULT 0,
            `id_referente` INT(11) NOT NULL DEFAULT 0,
            `codigo_pais` VARCHAR(5) NOT NULL DEFAULT '+57',
            `celular` VARCHAR(30) NOT NULL,
            `cedula` VARCHAR(20) DEFAULT NULL,
            `fecha_de_nacimiento` TIMESTAMP NULL DEFAULT NULL,
            `email` VARCHAR(50) DEFAULT NULL,
            `direccion` VARCHAR(150) DEFAULT NULL,
            `ciudad` VARCHAR(20) DEFAULT NULL,
            
            -- Referencias personales
            `ref1_nombre` VARCHAR(100) DEFAULT NULL,
            `ref1_parentesco` VARCHAR(100) DEFAULT NULL,
            `ref1_celular` VARCHAR(100) DEFAULT NULL,
            
            -- Información médica
            `info_medica` VARCHAR(1000) DEFAULT NULL,
            `tipo_sangre` VARCHAR(10) DEFAULT NULL COMMENT 'Tipo de sangre del usuario',
            `alergias` MEDIUMTEXT DEFAULT NULL COMMENT 'Alergias o condiciones médicas',
            `certificado_medico` VARCHAR(255) DEFAULT NULL COMMENT 'Ruta del certificado médico',
            
            -- Contacto de emergencia
            `contacto_emergencia_nombre` VARCHAR(255) DEFAULT NULL,
            `contacto_emergencia_parentesco` VARCHAR(100) DEFAULT NULL,
            `contacto_emergencia_telefono` VARCHAR(20) DEFAULT NULL,
            
            -- Información bancaria
            `banco_nombre` VARCHAR(100) DEFAULT NULL,
            `banco_tipo_cuenta` ENUM('Ahorros','Corriente') DEFAULT NULL,
            `banco_numero_cuenta` VARCHAR(50) DEFAULT NULL,
            
            -- Configuración y preferencias
            `dias_descanso` LONGTEXT DEFAULT NULL CHECK (json_valid(`dias_descanso`)),
            `url_entrevista` VARCHAR(300) DEFAULT NULL,
            `inmune_asistencia` INT(11) NOT NULL DEFAULT 0,
            `nivel_orden` INT(11) DEFAULT NULL,
            
            -- Documentos y fotos
            `foto_perfil` VARCHAR(255) DEFAULT NULL,
            `foto_con_cedula` VARCHAR(255) DEFAULT NULL,
            `foto_cedula_frente` VARCHAR(255) DEFAULT NULL,
            `foto_cedula_reverso` VARCHAR(255) DEFAULT NULL,
            
            -- Notas y progreso
            `notas` MEDIUMTEXT DEFAULT NULL,
            `progreso_perfil` INT(11) DEFAULT 0 COMMENT 'Porcentaje de completitud (0-100)',
            
            PRIMARY KEY (`id_info`),
            UNIQUE KEY `unique_id_usuario` (`id_usuario`),
            UNIQUE KEY `unique_celular` (`celular`),
            UNIQUE KEY `unique_cedula` (`cedula`),
            KEY `idx_ciudad` (`ciudad`),
            KEY `idx_banco` (`banco_nombre`),
            KEY `idx_progreso` (`progreso_perfil`),
            
            CONSTRAINT `fk_usuarios_info_usuario` 
                FOREIGN KEY (`id_usuario`) 
                REFERENCES `usuarios` (`id_usuario`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
        COMMENT='Información extendida de usuarios'";
        
        $this->conn->exec($sql);
        
        echo "<p class='success'>✓ Tabla usuarios_info creada exitosamente</p>";
        
        $this->log[] = "✓ Tabla usuarios_info creada";
        echo "</div>";
    }
    
    private function paso3_migrarDatos() {
        echo "<div class='step'>";
        echo "<h2>📦 PASO 3: Migrando datos a usuarios_info</h2>";
        
        $sql = "INSERT INTO usuarios_info (
            id_usuario, disponibilidad, id_estudio, id_referente,
            codigo_pais, celular, cedula, fecha_de_nacimiento, email,
            direccion, ciudad, ref1_nombre, ref1_parentesco, ref1_celular,
            info_medica, tipo_sangre, alergias, certificado_medico,
            contacto_emergencia_nombre, contacto_emergencia_parentesco,
            contacto_emergencia_telefono, banco_nombre, banco_tipo_cuenta,
            banco_numero_cuenta, dias_descanso, url_entrevista,
            inmune_asistencia, nivel_orden, foto_perfil, foto_con_cedula,
            foto_cedula_frente, foto_cedula_reverso, notas, progreso_perfil
        )
        SELECT 
            id_usuario, disponibilidad, id_estudio, id_referente,
            codigo_pais, celular, cedula, fecha_de_nacimiento, email,
            direccion, ciudad, ref1_nombre, ref1_parentesco, ref1_celular,
            info_medica, tipo_sangre, alergias, certificado_medico,
            contacto_emergencia_nombre, contacto_emergencia_parentesco,
            contacto_emergencia_telefono, banco_nombre, banco_tipo_cuenta,
            banco_numero_cuenta, dias_descanso, url_entrevista,
            inmune_asistencia, nivel_orden, foto_perfil, foto_con_cedula,
            foto_cedula_frente, foto_cedula_reverso, notas, progreso_perfil
        FROM usuarios";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $migrados = $stmt->rowCount();
        
        echo "<p class='success'>✓ {$migrados} registros migrados exitosamente</p>";
        
        $this->log[] = "✓ Migrados {$migrados} registros a usuarios_info";
        echo "</div>";
    }
    
    private function paso4_verificarMigracion() {
        echo "<div class='step'>";
        echo "<h2>🔍 PASO 4: Verificando integridad de datos</h2>";
        
        // Verificar conteo
        $stmt1 = $this->conn->query("SELECT COUNT(*) as total FROM usuarios");
        $total_usuarios = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt2 = $this->conn->query("SELECT COUNT(*) as total FROM usuarios_info");
        $total_info = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($total_usuarios == $total_info) {
            echo "<p class='success'>✓ Conteo correcto: {$total_usuarios} = {$total_info}</p>";
        } else {
            throw new Exception("ERROR: Conteo no coincide. Usuarios: {$total_usuarios}, Info: {$total_info}");
        }
        
        // Verificar registros huérfanos
        $stmt3 = $this->conn->query("
            SELECT COUNT(*) as huerfanos 
            FROM usuarios u 
            LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
            WHERE ui.id_usuario IS NULL
        ");
        $huerfanos = $stmt3->fetch(PDO::FETCH_ASSOC)['huerfanos'];
        
        if ($huerfanos > 0) {
            throw new Exception("ERROR: {$huerfanos} usuarios sin información migrada");
        }
        
        echo "<p class='success'>✓ No hay registros huérfanos</p>";
        
        // Verificar datos de muestra
        $stmt4 = $this->conn->query("
            SELECT u.id_usuario, u.nombres, ui.celular, ui.email 
            FROM usuarios u 
            INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
            LIMIT 5
        ");
        $muestra = $stmt4->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Muestra de datos migrados:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombres</th><th>Celular</th><th>Email</th></tr>";
        foreach ($muestra as $row) {
            echo "<tr>";
            echo "<td>{$row['id_usuario']}</td>";
            echo "<td>{$row['nombres']}</td>";
            echo "<td>{$row['celular']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $this->log[] = "✓ Verificación de integridad completada";
        echo "</div>";
    }
    
    private function paso5_eliminarColumnasAntiguas() {
        echo "<div class='step'>";
        echo "<h2>🗑️ PASO 5: Eliminando columnas migradas de usuarios</h2>";
        
        $columnas_eliminar = [
            'disponibilidad', 'id_estudio', 'id_referente', 'codigo_pais',
            'celular', 'cedula', 'fecha_de_nacimiento', 'email', 'direccion',
            'ciudad', 'ref1_nombre', 'ref1_parentesco', 'ref1_celular',
            'info_medica', 'tipo_sangre', 'alergias', 'certificado_medico',
            'contacto_emergencia_nombre', 'contacto_emergencia_parentesco',
            'contacto_emergencia_telefono', 'banco_nombre', 'banco_tipo_cuenta',
            'banco_numero_cuenta', 'dias_descanso', 'url_entrevista',
            'inmune_asistencia', 'nivel_orden', 'foto_perfil', 'foto_con_cedula',
            'foto_cedula_frente', 'foto_cedula_reverso', 'notas', 'progreso_perfil'
        ];
        
        echo "<p class='warning'>Eliminando " . count($columnas_eliminar) . " columnas...</p>";
        
        foreach ($columnas_eliminar as $columna) {
            try {
                // Primero eliminar índices relacionados si existen
                try {
                    $this->conn->exec("ALTER TABLE usuarios DROP INDEX idx_{$columna}");
                } catch (Exception $e) {
                    // Índice no existe, continuar
                }
                
                try {
                    $this->conn->exec("ALTER TABLE usuarios DROP INDEX {$columna}");
                } catch (Exception $e) {
                    // Índice no existe, continuar
                }
                
                // Luego eliminar la columna
                $this->conn->exec("ALTER TABLE usuarios DROP COLUMN `{$columna}`");
                echo "<p class='success'>✓ Columna '{$columna}' eliminada</p>";
            } catch (Exception $e) {
                echo "<p class='warning'>⚠ {$columna}: {$e->getMessage()}</p>";
            }
        }
        
        $this->log[] = "✓ Columnas antiguas eliminadas";
        echo "</div>";
    }
    
    private function paso6_verificacionFinal() {
        echo "<div class='step'>";
        echo "<h2>✅ PASO 6: Verificación final</h2>";
        
        // Estructura final de usuarios
        $stmt = $this->conn->query("DESCRIBE usuarios");
        $columnas_finales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Estructura final de tabla 'usuarios':</h3>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columnas_finales as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p class='success'>✓ Columnas restantes: " . count($columnas_finales) . "</p>";
        
        $esperadas = ['id_usuario', 'fecha_creacion', 'usuario', 'nombres', 'apellidos', 'password', 'estado', 'id_rol'];
        $actuales = array_column($columnas_finales, 'Field');
        
        $faltantes = array_diff($esperadas, $actuales);
        $extras = array_diff($actuales, $esperadas);
        
        if (!empty($faltantes)) {
            echo "<p class='error'>⚠ Columnas faltantes: " . implode(', ', $faltantes) . "</p>";
        }
        
        if (!empty($extras)) {
            echo "<p class='warning'>⚠ Columnas extras: " . implode(', ', $extras) . "</p>";
        }
        
        if (empty($faltantes) && count($actuales) == count($esperadas)) {
            echo "<p class='success'>✅ Estructura final correcta</p>";
        }
        
        $this->log[] = "✓ Verificación final completada";
        echo "</div>";
    }
    
    private function mostrarLog() {
        echo "<div class='step'>";
        echo "<h2>📋 Resumen de la migración</h2>";
        echo "<pre>";
        foreach ($this->log as $entrada) {
            echo $entrada . "\n";
        }
        echo "</pre>";
        echo "</div>";
    }
}

// Ejecutar migración
$migracion = new MigracionUsuarios();
$migracion->ejecutar();
?>
