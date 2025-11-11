<?php
/**
 * Setup completo de tabla modulos en producción
 * 1. Crea la tabla modulos
 * 2. Agrega columna exento
 * 3. Pobla con módulos del sistema
 * 4. Marca módulos de login como exentos
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = getDBConnection();
    
    echo "🚀 SETUP COMPLETO DE TABLA MODULOS\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // PASO 1: Crear tabla modulos
    echo "📋 PASO 1: Crear tabla 'modulos'\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS modulos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(255) UNIQUE NOT NULL COMMENT 'Identificador único generado de la ruta',
        ruta_completa VARCHAR(500) NOT NULL COMMENT 'Ruta completa del archivo',
        nombre_descriptivo VARCHAR(255) NULL COMMENT 'Nombre amigable para mostrar',
        categoria VARCHAR(100) NOT NULL COMMENT 'Categoría del módulo',
        activo TINYINT(1) DEFAULT 1 COMMENT '1=visible, 0=archivado',
        exento TINYINT(1) DEFAULT 0 COMMENT '1=exento de permisos, 0=requiere permisos',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_categoria (categoria),
        INDEX idx_activo (activo),
        INDEX idx_clave (clave),
        INDEX idx_exento (exento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Registro de módulos/vistas del sistema con nombres descriptivos editables'
    ";
    
    $db->exec($createTableSQL);
    echo "✅ Tabla 'modulos' creada o ya existente\n\n";
    
    // PASO 2: Verificar/agregar columna exento
    echo "📋 PASO 2: Verificar columna 'exento'\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    $checkColumnSQL = "SHOW COLUMNS FROM modulos LIKE 'exento'";
    $stmt = $db->query($checkColumnSQL);
    $existe = $stmt->fetch();
    
    if (!$existe) {
        $addColumnSQL = "ALTER TABLE modulos ADD COLUMN exento TINYINT(1) DEFAULT 0 COMMENT '1=exento de permisos' AFTER activo";
        $db->exec($addColumnSQL);
        echo "✅ Columna 'exento' agregada exitosamente\n\n";
    } else {
        echo "✅ Columna 'exento' ya existe\n\n";
    }
    
    // PASO 3: Poblar tabla con módulos principales
    echo "📋 PASO 3: Poblar tabla con módulos del sistema\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    // Definir módulos principales del sistema
    $modulos = [
        // Login (exentos)
        ['clave' => 'views_login_login_php', 'ruta' => 'views/login/login.php', 'nombre' => 'Inicio de Sesión', 'categoria' => 'login', 'exento' => 1],
        ['clave' => 'views_login_register_php', 'ruta' => 'views/login/register.php', 'nombre' => 'Registro de Usuario', 'categoria' => 'login', 'exento' => 1],
        ['clave' => 'views_login_password_reset_php', 'ruta' => 'views/login/password_reset.php', 'nombre' => 'Recuperar Contraseña', 'categoria' => 'login', 'exento' => 1],
        ['clave' => 'views_login_reset_password_php', 'ruta' => 'views/login/reset_password.php', 'nombre' => 'Restablecer Contraseña', 'categoria' => 'login', 'exento' => 1],
        ['clave' => 'controllers_login_AuthController_php', 'ruta' => 'controllers/login/AuthController.php', 'nombre' => 'Controlador de Autenticación', 'categoria' => 'login', 'exento' => 1],
        
        // Admin
        ['clave' => 'views_admin_index_php', 'ruta' => 'views/admin/index.php', 'nombre' => 'Panel de Administración', 'categoria' => 'admin', 'exento' => 0],
        ['clave' => 'views_admin_permissionsPanel_php', 'ruta' => 'views/admin/permissionsPanel.php', 'nombre' => 'Gestión de Permisos', 'categoria' => 'admin', 'exento' => 0],
        
        // Tickets
        ['clave' => 'views_tickets_ticketCreate_php', 'ruta' => 'views/tickets/ticketCreate.php', 'nombre' => 'Crear Ticket de Soporte', 'categoria' => 'tickets', 'exento' => 0],
        ['clave' => 'views_tickets_ticketList_php', 'ruta' => 'views/tickets/ticketList.php', 'nombre' => 'Lista de Tickets', 'categoria' => 'tickets', 'exento' => 0],
        
        // Finanzas
        ['clave' => 'views_finanzas_index_php', 'ruta' => 'views/finanzas/index.php', 'nombre' => 'Dashboard Financiero', 'categoria' => 'finanzas', 'exento' => 0],
        
        // Perfil
        ['clave' => 'views_perfil_index_php', 'ruta' => 'views/perfil/index.php', 'nombre' => 'Mi Perfil', 'categoria' => 'perfil', 'exento' => 0],
        
        // Dashboard
        ['clave' => 'index_php', 'ruta' => 'index.php', 'nombre' => 'Dashboard Principal', 'categoria' => 'dashboard', 'exento' => 0],
    ];
    
    $insertSQL = "INSERT INTO modulos (clave, ruta_completa, nombre_descriptivo, categoria, exento, activo) 
                  VALUES (:clave, :ruta, :nombre, :categoria, :exento, 1)
                  ON DUPLICATE KEY UPDATE 
                  nombre_descriptivo = IF(nombre_descriptivo IS NULL, VALUES(nombre_descriptivo), nombre_descriptivo),
                  categoria = VALUES(categoria),
                  exento = VALUES(exento)";
    
    $stmt = $db->prepare($insertSQL);
    
    $insertados = 0;
    $actualizados = 0;
    
    foreach ($modulos as $modulo) {
        $stmt->execute([
            ':clave' => $modulo['clave'],
            ':ruta' => $modulo['ruta'],
            ':nombre' => $modulo['nombre'],
            ':categoria' => $modulo['categoria'],
            ':exento' => $modulo['exento']
        ]);
        
        if ($stmt->rowCount() > 0) {
            $insertados++;
        }
    }
    
    echo "✅ Módulos procesados: " . count($modulos) . "\n";
    echo "   • Insertados/Actualizados: $insertados\n\n";
    
    // PASO 4: Verificar resultados
    echo "📋 PASO 4: Resumen de módulos\n";
    echo "─────────────────────────────────────────────────────────\n";
    
    // Total de módulos
    $totalSQL = "SELECT COUNT(*) as total FROM modulos";
    $total = $db->query($totalSQL)->fetch()['total'];
    echo "📊 Total de módulos: $total\n\n";
    
    // Por categoría
    $catSQL = "SELECT categoria, COUNT(*) as total FROM modulos GROUP BY categoria ORDER BY categoria";
    $categorias = $db->query($catSQL)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📂 Módulos por categoría:\n";
    foreach ($categorias as $cat) {
        echo "   • {$cat['categoria']}: {$cat['total']}\n";
    }
    echo "\n";
    
    // Módulos exentos
    $exentosSQL = "SELECT categoria, COUNT(*) as total FROM modulos WHERE exento = 1 GROUP BY categoria";
    $exentos = $db->query($exentosSQL)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "🔓 Módulos exentos de permisos:\n";
    if (count($exentos) > 0) {
        foreach ($exentos as $ex) {
            echo "   • {$ex['categoria']}: {$ex['total']}\n";
        }
    } else {
        echo "   • Ninguno\n";
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ SETUP COMPLETADO EXITOSAMENTE!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "🔗 Siguiente paso:\n";
    echo "   👉 Verifica el panel de permisos:\n";
    echo "      https://valora.vip/views/admin/permissionsPanel.php\n\n";
    
} catch (PDOException $e) {
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "❌ ERROR EN LA MIGRACIÓN\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "\n";
}
?>
