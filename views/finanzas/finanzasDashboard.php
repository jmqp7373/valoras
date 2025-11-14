<?php
/**
 * Valora.vip - Dashboard de Finanzas
 * Módulo de gestión financiera: ingresos, gastos y análisis
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/FinanzasController.php';

startSessionSafely();

// Verificar si el usuario está logueado
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Procesar registro de movimiento
$finanzasController = new FinanzasController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_movimiento'])) {
    $finanzasController->registrarMovimiento();
    header('Location: finanzasDashboard.php');
    exit();
}

// Obtener datos
$movimientos = $finanzasController->listarMovimientos();
$totales = $finanzasController->calcularTotales();

// Obtener información del usuario
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

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
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT titulo, subtitulo, icono 
        FROM modulos 
        WHERE ruta_completa = ?
    ");
    $stmt->execute(['views\finanzas\finanzasDashboard.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $modulo = [
        'titulo' => 'Gestión de Finanzas',
        'subtitulo' => 'Control y análisis financiero',
        'icono' => '💰'
    ];
}

// Variables para master.php
$page_title = $modulo['titulo'] ?? 'Gestión de Finanzas';
$titulo_pagina = $modulo['titulo'] ?? 'Gestión de Finanzas';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Control y análisis financiero';
$icono_pagina = $modulo['icono'] ?? '💰';

// Breadcrumbs
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Gestión de Finanzas', 'url' => null]
];

// CSS adicional
$additional_css = ['../../assets/css/finanzasDashboard.css'];

// JavaScript adicional
$additional_js = [];

// ============================================
// CAPTURA DE CONTENIDO
// ============================================
ob_start();
?>

<div class="container-fluid" style="padding: 20px 40px;">
    <!-- Mensajes de éxito/error -->
    <?php if (isset($_SESSION['success_finanzas'])): ?>
        <div class="mensaje-exito">
            <?php 
            echo htmlspecialchars($_SESSION['success_finanzas']); 
            unset($_SESSION['success_finanzas']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_finanzas'])): ?>
        <div class="mensaje-error">
            <?php 
            echo htmlspecialchars($_SESSION['error_finanzas']); 
            unset($_SESSION['error_finanzas']);
            ?>
        </div>
    <?php endif; ?>            <!-- Formulario de registro de movimiento -->
            <div class="card-finanzas">
                <h2 style="color: #1B263B; font-family: 'Poppins', sans-serif; margin-bottom: 1.5rem;">📝 Registrar Nuevo Movimiento</h2>
                <form method="POST" action="" id="formMovimiento">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="fecha">Fecha *</label>
                            <input type="date" id="fecha" name="fecha" required max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo">Tipo *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Seleccione...</option>
                                <option value="Ingreso">💰 Ingreso</option>
                                <option value="Gasto">💸 Gasto</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria">Categoría *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Seleccione...</option>
                                <option value="Arriendo">🏠 Arriendo</option>
                                <option value="Nómina">👥 Nómina</option>
                                <option value="Servicios">⚡ Servicios</option>
                                <option value="Personal">👤 Personal</option>
                                <option value="Otro">📌 Otro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="monto">Monto *</label>
                            <input type="number" id="monto" name="monto" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción *</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required placeholder="Describe el movimiento..."></textarea>
                    </div>
                    
                    <button type="submit" name="registrar_movimiento" class="btn-registrar">
                        ✅ Registrar Movimiento
                    </button>
                </form>
            </div>

            <!-- Resumen de totales -->
            <div class="totales-grid">
                <div class="total-card ingreso">
                    <div class="total-icon">💰</div>
                    <div class="total-info">
                        <span class="total-label">Ingresos Totales</span>
                        <span class="total-amount">$<?php echo number_format($totales['total_ingresos'], 2, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="total-card gasto">
                    <div class="total-icon">💸</div>
                    <div class="total-info">
                        <span class="total-label">Gastos Totales</span>
                        <span class="total-amount">$<?php echo number_format($totales['total_gastos'], 2, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="total-card balance">
                    <div class="total-icon">⚖️</div>
                    <div class="total-info">
                        <span class="total-label">Balance General</span>
                        <span class="total-amount" style="color: <?php echo $totales['balance'] >= 0 ? '#28a745' : '#dc3545'; ?>">
                            $<?php echo number_format($totales['balance'], 2, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabla de movimientos -->
            <div class="card-finanzas" style="margin-top: 2rem;">
                <h2 style="color: #1B263B; font-family: 'Poppins', sans-serif; margin-bottom: 1.5rem;">📊 Historial de Movimientos</h2>
                
                <!-- Filtros -->
                <div class="filtros-container">
                    <div class="filtros-grid">
                        <div class="filtro-item">
                            <label for="fechaInicio">Fecha Inicial</label>
                            <input type="date" id="fechaInicio" class="filtro-input">
                        </div>
                        
                        <div class="filtro-item">
                            <label for="fechaFin">Fecha Final</label>
                            <input type="date" id="fechaFin" class="filtro-input">
                        </div>
                        
                        <div class="filtro-item">
                            <label for="categoriaFiltro">Categoría</label>
                            <select id="categoriaFiltro" class="filtro-input">
                                <option value="Todas">Todas</option>
                                <option value="Arriendo">🏠 Arriendo</option>
                                <option value="Nómina">👥 Nómina</option>
                                <option value="Servicios">⚡ Servicios</option>
                                <option value="Personal">👤 Personal</option>
                                <option value="Otro">📌 Otro</option>
                            </select>
                        </div>
                        
                        <div class="filtro-item filtro-botones">
                            <button type="button" id="filtrarBtn" class="btn-filtrar">
                                🔍 Filtrar
                            </button>
                            <button type="button" id="limpiarBtn" class="btn-limpiar">
                                ✖ Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de la tabla -->
                <div id="tablaMovimientos">
                <?php if (empty($movimientos)): ?>
                    <div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; padding: 1.5rem; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bee5eb; text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">�</div>
                        <h3 style="margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif;">¡Aún no hay movimientos registrados!</h3>
                        <p style="margin: 0; font-size: 1rem;">Comienza agregando el primero usando el formulario de arriba. Los datos de ejemplo se cargarán automáticamente la primera vez.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="tabla-movimientos">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Categoría</th>
                                    <th>Monto</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($mov['fecha'])); ?></td>
                                        <td>
                                            <span class="badge-tipo <?php echo strtolower($mov['tipo']); ?>">
                                                <?php echo $mov['tipo'] === 'Ingreso' ? '💰' : '💸'; ?>
                                                <?php echo htmlspecialchars($mov['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['categoria']); ?></td>
                                        <td class="monto-<?php echo strtolower($mov['tipo']); ?>">
                                            $<?php echo number_format($mov['monto'], 2, ',', '.'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['descripcion']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                </div><!-- Fin tablaMovimientos -->
            </div>
        </div>

        <script>
        // Validación del formulario
        document.getElementById('formMovimiento').addEventListener('submit', function(e) {
            const monto = document.getElementById('monto').value;
            
            if (parseFloat(monto) <= 0) {
                e.preventDefault();
                alert('El monto debe ser mayor a 0');
                return false;
            }
        });

        // Establecer fecha actual por defecto
        document.getElementById('fecha').value = new Date().toISOString().split('T')[0];

        // FUNCIONALIDAD DE FILTROS
        
        /**
         * Aplica los filtros seleccionados y actualiza la tabla
         */
        async function aplicarFiltros() {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            const categoria = document.getElementById('categoriaFiltro').value;

            // Construir parámetros de búsqueda
            const params = new URLSearchParams({
                fechaInicio: fechaInicio,
                fechaFin: fechaFin,
                categoria: categoria
            });

            try {
                // Mostrar indicador de carga
                const tablaContainer = document.getElementById('tablaMovimientos');
                tablaContainer.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6c757d;"><p style="font-size: 1.2rem;">⏳ Cargando...</p></div>';

                // Hacer petición AJAX
                const response = await fetch('../../controllers/FinanzasController.php?action=listar&' + params.toString());
                const html = await response.text();

                // Actualizar tabla
                tablaContainer.innerHTML = html;
            } catch (error) {
                console.error('Error al filtrar movimientos:', error);
                document.getElementById('tablaMovimientos').innerHTML = 
                    '<div style="background: #f8d7da; color: #721c24; padding: 1.5rem; border-radius: 8px; text-align: center;">' +
                    '<p>❌ Error al cargar los datos. Por favor, intenta de nuevo.</p>' +
                    '</div>';
            }
        }

        /**
         * Limpia los filtros y muestra todos los movimientos
         */
        async function limpiarFiltros() {
            // Restablecer valores de los filtros
            document.getElementById('fechaInicio').value = '';
            document.getElementById('fechaFin').value = '';
            document.getElementById('categoriaFiltro').value = 'Todas';

            // Recargar todos los movimientos
            await aplicarFiltros();
        }

        // Event listeners para los botones
        document.getElementById('filtrarBtn').addEventListener('click', aplicarFiltros);
        document.getElementById('limpiarBtn').addEventListener('click', limpiarFiltros);

        // Permitir filtrar con Enter en los inputs
        document.getElementById('fechaInicio').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') aplicarFiltros();
        });

        document.getElementById('fechaFin').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') aplicarFiltros();
        });

        document.getElementById('categoriaFiltro').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') aplicarFiltros();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>