<?php
/**
 * Dropdown Máscara de Rol - Component
 * Proyecto: Valora.vip
 * 
 * Permite a usuarios Superadmin (nivel_orden=1) cambiar temporalmente de rol
 * para probar diferentes niveles de permisos sin cerrar sesión.
 * 
 * Variables requeridas:
 * - $es_superadmin: boolean - Si el usuario tiene nivel_orden=1
 * - $todos_roles: array - Lista de roles disponibles (id, nombre, nivel_orden)
 * - $rol_actual: array - Rol actual del usuario (id, nombre)
 * - $base_controller_path: string - Ruta base a la carpeta controllers
 */

// Verificar que las variables requeridas existan
if (!isset($es_superadmin)) $es_superadmin = false;
if (!isset($todos_roles)) $todos_roles = [];
if (!isset($rol_actual)) $rol_actual = null;
if (!isset($base_controller_path)) {
    // Calcular ruta por defecto si no está definida
    $base_controller_path = 'controllers/';
}

// Solo mostrar si es Superadmin y hay roles disponibles
if (!$es_superadmin || empty($todos_roles)) {
    return; // No renderizar nada
}

// Iconos para cada rol
$iconos_roles = [
    'superadmin' => '👑',
    'admin' => '🔐',
    'comunicador' => '📢',
    'lider' => '🎯',
    'coordinador' => '⚙️',
    'modelo' => '⭐'
];
?>

<!-- Dropdown de Máscara de Rol (Solo Superadmin) -->
<div class="dropdown me-3 role-switcher-dropdown">
    <?php 
    // Solo aplicar clase en-modo-prueba si hay rol de prueba Y NO es Superadmin
    $en_modo_prueba = isset($_SESSION['rol_prueba_id']) && strtolower($rol_actual['nombre'] ?? '') !== 'superadmin';
    ?>
    <button class="role-switcher-btn dropdown-toggle <?php echo $en_modo_prueba ? 'en-modo-prueba' : ''; ?>" 
            type="button" 
            id="roleSwitcherDropdown" 
            data-bs-toggle="dropdown" 
            aria-expanded="false" 
            title="<?php echo $en_modo_prueba ? 'Modo de prueba activo' : 'Cambiar rol de prueba'; ?>">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" class="role-icon">
            <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
            <path d="M2 1a2 2 0 0 0-2 2v9.5A1.5 1.5 0 0 0 1.5 14h.653a5.373 5.373 0 0 1 1.066-2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v9h-2.219c.554.654.89 1.373 1.066 2h.653a1.5 1.5 0 0 0 1.5-1.5V3a2 2 0 0 0-2-2H2z"/>
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
        </svg>
        <span class="role-name">
            <?php 
            if ($en_modo_prueba) {
                echo '🎭 ' . htmlspecialchars(ucfirst($rol_actual['nombre'] ?? 'Rol'));
            } else {
                echo htmlspecialchars(ucfirst($rol_actual['nombre'] ?? 'Rol'));
            }
            ?>
        </span>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end role-dropdown-custom" aria-labelledby="roleSwitcherDropdown">
        <!-- Lista de roles disponibles -->
        <?php foreach ($todos_roles as $rol): ?>
        <li>
            <a class="dropdown-item <?php echo ($rol_actual && $rol['id'] == $rol_actual['id']) ? 'active' : ''; ?>" 
               href="#" 
               onclick="cambiarRolPrueba(<?php echo $rol['id']; ?>, '<?php echo htmlspecialchars($rol['nombre']); ?>'); return false;">
                <!-- Check mark para rol activo -->
                <?php if ($rol_actual && $rol['id'] == $rol_actual['id']): ?>
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="check-icon">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                    </svg>
                <?php else: ?>
                    <span class="check-spacer"></span>
                <?php endif; ?>
                
                <!-- Icono y nombre del rol -->
                <?php 
                $icono = $iconos_roles[strtolower($rol['nombre'])] ?? '🔹';
                echo $icono . ' ' . htmlspecialchars(ucfirst($rol['nombre']));
                ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- JavaScript para cambio de rol -->
<script>
function cambiarRolPrueba(rolId, rolNombre) {
    const url = '<?php echo $base_controller_path; ?>CambiarRolController.php';
    console.log('🔄 Cambiando rol a:', rolNombre);
    console.log('URL:', url);
    console.log('Rol destino:', { id: rolId, nombre: rolNombre });
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'rol_id=' + rolId + '&rol_nombre=' + encodeURIComponent(rolNombre)
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        console.log('📄 Response text:', text);
        try {
            const data = JSON.parse(text);
            console.log('✅ Response data:', data);
            
            if (data.success) {
                console.log('✨ Rol cambiado exitosamente a:', rolNombre);
                // Recargar sin alert
                location.reload();
            } else {
                console.error('❌ Error:', data.message);
                alert('❌ Error al cambiar de rol: ' + data.message);
            }
        } catch (e) {
            console.error('❌ Error parsing JSON:', e);
            console.error('Response recibido:', text);
            alert('❌ Error: Respuesta inválida del servidor');
        }
    })
    .catch(error => {
        console.error('❌ Error completo:', error);
        alert('❌ Error de conexión: ' + error.message);
    });
}

function restaurarRolOriginal() {
    const url = '<?php echo $base_controller_path; ?>RestaurarRolController.php';
    console.log('🔄 Restaurando rol original...');
    console.log('URL:', url);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        console.log('📄 Response text:', text);
        try {
            const data = JSON.parse(text);
            console.log('✅ Response data:', data);
            
            if (data.success) {
                console.log('✨ Rol restaurado exitosamente');
                // Recargar sin alert
                location.reload();
            } else {
                console.error('❌ Error:', data.message);
                alert('❌ Error al restaurar rol: ' + data.message);
            }
        } catch (e) {
            console.error('❌ Error parsing JSON:', e);
            console.error('Response recibido:', text);
            alert('❌ Error: Respuesta inválida del servidor');
        }
    })
    .catch(error => {
        console.error('❌ Error completo:', error);
        alert('❌ Error de conexión: ' + error.message);
    });
}
</script>
