<?php
/**
 * Vista: Ventas por Modelo
 * 
 * Muestra las ganancias registradas de cada modelo consultando la tabla ventas
 */

// Habilitar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/VentasController.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// Inicializar controlador
try {
    $db = getDBConnection();
    $ventasController = new VentasController($db);
} catch (Exception $e) {
    die('Error al conectar con la base de datos: ' . $e->getMessage());
}

// Si hay un modelo seleccionado, se filtra por su ID
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;
$ventas = [];
$usuarioInfo = null;
$totalVentas = 0;

if ($usuario_id) {
    $ventas = $ventasController->getVentasByUsuario($usuario_id);
    $usuarioInfo = $ventasController->getUsuarioInfo($usuario_id);
    $totalVentas = $ventasController->getTotalVentasUsuario($usuario_id);
}

// Obtener lista de todos los usuarios para el select
$usuarios = $ventasController->getAllUsuarios();

// Variables para header
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// ============================================
// CONFIGURACIÓN MASTER LAYOUT
// ============================================

// Consultar información del módulo desde la base de datos
try {
    $stmt = $db->prepare("
        SELECT titulo, subtitulo, icono 
        FROM modulos 
        WHERE ruta_completa = ?
    ");
    $stmt->execute(['views\ventas\ventasModelo.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $modulo = [
        'titulo' => 'Ventas por Modelo',
        'subtitulo' => 'Gestión de ventas y modelos',
        'icono' => '🛒'
    ];
}

// Variables para master.php
$page_title = $modulo['titulo'] ?? 'Ventas por Modelo';
$titulo_pagina = $modulo['titulo'] ?? 'Ventas por Modelo';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Gestión de ventas y modelos';
$icono_pagina = $modulo['icono'] ?? '🛒';

// Breadcrumbs
$breadcrumbs = [
    ['label' => 'Home', 'url' => '../../index.php'],
    ['label' => 'Ventas por Modelo', 'url' => '']
];

// CSS adicional (ruta relativa desde la raíz del proyecto)
$additional_css = ['../../assets/css/ventasModelo.css'];

// JavaScript adicional
$additional_js = [];

// ============================================
// CAPTURA DE CONTENIDO
// ============================================
ob_start();
?>

<div class="ventas-container">
    <!-- Filtro por modelo -->
    <div class="filter-section">
        <form method="GET">
            <label for="usuario_id">Selecciona un modelo:</label>
            <select name="usuario_id" id="usuario_id" required>
                <option value="">-- Elige un modelo --</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id_usuario'] ?>" <?= (isset($usuario_id) && $usuario_id == $u['id_usuario']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos'] . ' - ' . $u['cedula']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-consultar">🔍 Consultar</button>
            
            <?php if ($usuario_id): ?>
                <button type="button" id="importarBtn" class="btn-importar">📥 Importar desde Stripchat</button>
                <div id="resultadoImportacion"></div>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($usuario_id && $usuarioInfo): ?>
        
        <!-- Información del modelo seleccionado -->
        <div class="modelo-info">
            <h3>👤 <?= htmlspecialchars($usuarioInfo['nombres'] . ' ' . $usuarioInfo['apellidos']) ?></h3>
            <p><strong>Cédula:</strong> <?= htmlspecialchars($usuarioInfo['cedula']) ?></p>
            <?php if (!empty($usuarioInfo['email'])): ?>
                <p><strong>Email:</strong> <?= htmlspecialchars($usuarioInfo['email']) ?></p>
            <?php endif; ?>
            <div class="total-badge">
                💰 Total Acumulado: $<?= number_format($totalVentas, 2) ?> USD
            </div>
        </div>

        <?php if (!empty($ventas)): ?>
            <!-- Tabla de ventas -->
            <div class="ventas-table-container">
                <h3 style="margin-top: 0; color: #333;">Registro de Ventas</h3>
                <table class="ventas-table">
                    <thead>
                        <tr>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Plataforma</th>
                            <th>Email/Usuario</th>
                            <th>Total Ganado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($venta['period_start'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($venta['period_end'])) ?></td>
                                <td><?= htmlspecialchars($venta['credencial_usuario']) ?></td>
                                <td><?= htmlspecialchars($venta['credencial_email']) ?></td>
                                <td class="amount">$<?= number_format($venta['total_earnings'], 2) ?> USD</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <strong>Total de registros:</strong> <?= count($ventas) ?> ventas
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                ⚠️ No se encontraron registros de ventas para este modelo.
            </div>
        <?php endif; ?>
        
    <?php elseif ($usuario_id): ?>
        <div class="alert alert-warning">
            ⚠️ No se encontró información del modelo seleccionado.
        </div>
    <?php endif; ?>
</div>

<!-- Script para importar datos desde Stripchat -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const importarBtn = document.getElementById('importarBtn');
    const resultadoDiv = document.getElementById('resultadoImportacion');

    if (importarBtn) {
        importarBtn.addEventListener('click', async () => {
            // Deshabilitar botón y mostrar estado de carga
            importarBtn.disabled = true;
            importarBtn.textContent = "⏳ Importando datos...";
            resultadoDiv.innerHTML = '<div style="padding: 15px; background: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; color: #004085; margin-top: 15px;">⏳ Conectando con la API de Stripchat...</div>';

            try {
                const usuario_id = <?= $usuario_id ?? 0 ?>;
                const response = await fetch(`../../controllers/VentasController.php?action=importarDesdeAPI&usuario_id=${usuario_id}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Obtener el texto de respuesta primero para debug
                const responseText = await response.text();
                console.log('Respuesta del servidor:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    throw new Error(`Unexpected token '<', "${responseText.substring(0, 50)}..." is not valid JSON`);
                }

                if (data.success) {
                    resultadoDiv.innerHTML = `
                        <div class="alert" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <strong>✅ Importación completada exitosamente</strong><br>
                            Se agregaron/actualizaron <strong>${data.registros}</strong> registro(s) de ventas.<br>
                            <small>La página se recargará en 2 segundos...</small>
                        </div>
                    `;
                    
                    // Recargar la página después de 2 segundos
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    resultadoDiv.innerHTML = `
                        <div class="alert alert-warning" style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <strong>⚠️ No se pudieron importar los datos</strong><br>
                            ${data.message || 'Error desconocido'}
                        </div>
                    `;
                    
                    // Restaurar botón
                    importarBtn.disabled = false;
                    importarBtn.textContent = "📥 Importar desde Stripchat";
                }
            } catch (error) {
                console.error('Error en la importación:', error);
                resultadoDiv.innerHTML = `
                    <div class="alert alert-danger" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <strong>❌ Error en la solicitud</strong><br>
                        ${error.message || 'No se pudo conectar con el servidor'}
                    </div>
                `;
                
                // Restaurar botón
                importarBtn.disabled = false;
                importarBtn.textContent = "📥 Importar desde Stripchat";
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>
