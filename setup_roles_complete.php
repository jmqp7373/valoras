<?php
/**
 * Crear tabla roles en producción
 * Script de migración para sistema de permisos
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = getDBConnection();
    
    echo "🚀 SETUP DE TABLA ROLES\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // PASO 1: Crear tabla roles
    echo "📋 PASO 1: Crear tabla 'roles'\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    $createRolesSQL = "
    CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) UNIQUE NOT NULL COMMENT 'Nombre del rol',
        descripcion TEXT COMMENT 'Descripción del rol',
        nivel INT DEFAULT 0 COMMENT 'Nivel de privilegios (mayor = más permisos)',
        activo TINYINT(1) DEFAULT 1 COMMENT '1=activo, 0=inactivo',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_nombre (nombre),
        INDEX idx_activo (activo),
        INDEX idx_nivel (nivel)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Roles del sistema para control de permisos'
    ";
    
    $db->exec($createRolesSQL);
    echo "✅ Tabla 'roles' creada\n\n";
    
    // PASO 2: Insertar roles básicos
    echo "📋 PASO 2: Poblar roles básicos\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    $roles = [
        ['nombre' => 'Superadmin', 'descripcion' => 'Acceso total al sistema', 'nivel' => 100],
        ['nombre' => 'Admin', 'descripcion' => 'Administrador del sistema', 'nivel' => 80],
        ['nombre' => 'Moderador', 'descripcion' => 'Moderador de contenido', 'nivel' => 60],
        ['nombre' => 'Usuario', 'descripcion' => 'Usuario estándar', 'nivel' => 20],
        ['nombre' => 'Invitado', 'descripcion' => 'Usuario invitado con acceso limitado', 'nivel' => 10],
    ];
    
    $insertSQL = "INSERT INTO roles (nombre, descripcion, nivel, activo) 
                  VALUES (:nombre, :descripcion, :nivel, 1)
                  ON DUPLICATE KEY UPDATE 
                  descripcion = VALUES(descripcion),
                  nivel = VALUES(nivel)";
    
    $stmt = $db->prepare($insertSQL);
    
    foreach ($roles as $rol) {
        $stmt->execute([
            ':nombre' => $rol['nombre'],
            ':descripcion' => $rol['descripcion'],
            ':nivel' => $rol['nivel']
        ]);
        echo "✅ Rol '{$rol['nombre']}' (nivel {$rol['nivel']})\n";
    }
    
    echo "\n";
    
    // PASO 3: Crear tabla roles_permisos
    echo "📋 PASO 3: Crear tabla 'roles_permisos'\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    $createPermisosSQL = "
    CREATE TABLE IF NOT EXISTS roles_permisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_rol INT NOT NULL COMMENT 'FK a tabla roles',
        modulo VARCHAR(255) NOT NULL COMMENT 'Clave del módulo (ej: views_admin_index_php)',
        puede_ver TINYINT(1) DEFAULT 0 COMMENT 'Permiso de lectura',
        puede_editar TINYINT(1) DEFAULT 0 COMMENT 'Permiso de edición',
        puede_eliminar TINYINT(1) DEFAULT 0 COMMENT 'Permiso de eliminación',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        UNIQUE KEY unique_rol_modulo (id_rol, modulo),
        INDEX idx_id_rol (id_rol),
        INDEX idx_modulo (modulo),
        
        FOREIGN KEY (id_rol) REFERENCES roles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Permisos asignados a cada rol por módulo'
    ";
    
    $db->exec($createPermisosSQL);
    echo "✅ Tabla 'roles_permisos' creada\n\n";
    
    // PASO 4: Asignar permisos al rol Superadmin
    echo "📋 PASO 4: Asignar permisos a Superadmin\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    // Obtener ID del rol Superadmin
    $superadminId = $db->query("SELECT id FROM roles WHERE nombre = 'Superadmin'")->fetchColumn();
    
    // Obtener todos los módulos NO exentos
    $modulosStmt = $db->query("SELECT clave FROM modulos WHERE exento = 0");
    $modulos = $modulosStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $insertPermisoSQL = "INSERT INTO roles_permisos (id_rol, modulo, puede_ver, puede_editar, puede_eliminar)
                         VALUES (:id_rol, :modulo, 1, 1, 1)
                         ON DUPLICATE KEY UPDATE 
                         puede_ver = 1, puede_editar = 1, puede_eliminar = 1";
    
    $stmt = $db->prepare($insertPermisoSQL);
    
    $count = 0;
    foreach ($modulos as $modulo) {
        $stmt->execute([
            ':id_rol' => $superadminId,
            ':modulo' => $modulo
        ]);
        $count++;
    }
    
    echo "✅ Asignados permisos totales a Superadmin: $count módulos\n\n";
    
    // PASO 5: Verificar resultados
    echo "📋 PASO 5: Verificación final\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    $rolesCount = $db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    echo "📊 Total roles: $rolesCount\n";
    
    $permisosCount = $db->query("SELECT COUNT(*) FROM roles_permisos")->fetchColumn();
    echo "📊 Total permisos asignados: $permisosCount\n";
    
    $rolesActivos = $db->query("SELECT nombre, nivel FROM roles WHERE activo = 1 ORDER BY nivel DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n📋 Roles activos:\n";
    foreach ($rolesActivos as $rol) {
        echo "   • {$rol['nombre']} (nivel {$rol['nivel']})\n";
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ SETUP DE ROLES COMPLETADO EXITOSAMENTE!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "🔗 Siguiente paso:\n";
    echo "   👉 Prueba el panel de permisos:\n";
    echo "      https://valora.vip/views/admin/permissionsPanel.php\n\n";
    
} catch (PDOException $e) {
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "❌ ERROR EN LA MIGRACIÓN\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n\n";
}
?>
