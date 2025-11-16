<?php
/**
 * Vista Principal de Administración de Credenciales
 * Proyecto: Valora.vip
 * Autor: Sistema Valora
 * Fecha: 2025-11-15
 */

// Activar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// ============================================
// OBTENER INFORMACIÓN DEL MÓDULO DESDE LA BD
// ============================================
try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT titulo, subtitulo, icono FROM modulos WHERE ruta_completa = ? AND activo = 1 LIMIT 1");
    $stmt->execute(['views\credenciales\credenciales_index.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CARGAR DATOS PARA FILTROS SI NO VIENEN DEL CONTROLADOR
// ============================================
if (!isset($paginas)) {
    try {
        $stmt = $db->query("SELECT id_pagina, nombre_pagina, color_pagina FROM paginas ORDER BY nombre_pagina");
        $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error cargando paginas: " . $e->getMessage());
        $paginas = [];
    }
}

if (!isset($estudios)) {
    try {
        $stmt = $db->query("SELECT id_estudio, nombre_estudio FROM estudios ORDER BY nombre_estudio");
        $estudios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error cargando estudios: " . $e->getMessage());
        $estudios = [];
    }
}

if (!isset($casas)) {
    try {
        $stmt = $db->query("SELECT id_estudio_casa, nombre_estudio_casa FROM estudios_casas ORDER BY nombre_estudio_casa");
        $casas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error cargando casas: " . $e->getMessage());
        $casas = [];
    }
}

if (!isset($cuentasEstudios)) {
    try {
        $stmt = $db->query("
            SELECT DISTINCT ce.id_cuenta_estudio, ce.usuario_cuenta_estudio, p.nombre_pagina
            FROM cuentas_estudios ce
            INNER JOIN paginas p ON p.id_pagina = ce.id_pagina
            WHERE ce.estado = 1
            ORDER BY p.nombre_pagina, ce.usuario_cuenta_estudio
        ");
        $cuentasEstudios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error cargando cuentas estudios: " . $e->getMessage());
        $cuentasEstudios = [];
    }
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Administración de Credenciales - Valora";

// Título, subtítulo e icono desde la base de datos o por defecto
$titulo_pagina = $modulo['titulo'] ?? 'Administración de Credenciales';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Gestiona las credenciales de modelos en todas las plataformas';
$icono_pagina = $modulo['icono'] ?? '🔑';

// Variables para header.php
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Administración de Credenciales', 'url' => null]
];

// CSS y JS adicionales
$additional_css = [];
$additional_js = [
    '../../assets/js/credenciales.js'
];

// Incluir el layout master
ob_start();
?>

<!-- Contenedor principal -->
<div class="container-fluid px-4 py-4">
    <!-- Zona de Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Filtro: Buscar modelo -->
                <div class="col-md-3">
                    <label for="filtro_modelo" class="form-label">Buscar Modelo</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="filtro_modelo" 
                        placeholder="Nombre, apellidos o usuario..."
                    >
                </div>

                <!-- Filtro: Plataforma -->
                <div class="col-md-2">
                    <label for="filtro_plataforma" class="form-label">Plataforma</label>
                    <select class="form-select" id="filtro_plataforma">
                        <option value="">Todas</option>
                        <?php foreach ($paginas as $pagina): ?>
                            <option value="<?php echo htmlspecialchars($pagina['id_pagina']); ?>">
                                <?php echo htmlspecialchars($pagina['nombre_pagina']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro: Casa de Estudio -->
                <div class="col-md-2">
                    <label for="filtro_casa" class="form-label">Casa de Estudio</label>
                    <select class="form-select" id="filtro_casa">
                        <option value="">Todas</option>
                        <?php foreach ($casas as $casa): ?>
                            <option value="<?php echo htmlspecialchars($casa['id_estudio_casa']); ?>">
                                <?php echo htmlspecialchars($casa['nombre_estudio_casa']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro: Estudio -->
                <div class="col-md-2">
                    <label for="filtro_estudio" class="form-label">Estudio</label>
                    <select class="form-select" id="filtro_estudio">
                        <option value="">Todos</option>
                        <?php foreach ($estudios as $estudio): ?>
                            <option value="<?php echo htmlspecialchars($estudio['id_estudio']); ?>">
                                <?php echo htmlspecialchars($estudio['nombre_estudio']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro: Cuenta de Estudio -->
                <div class="col-md-2">
                    <label for="filtro_cuenta_estudio" class="form-label">Cuenta de Estudio</label>
                    <select class="form-select" id="filtro_cuenta_estudio">
                        <option value="">Todas</option>
                        <?php foreach ($cuentasEstudios as $cuenta): ?>
                            <option value="<?php echo htmlspecialchars($cuenta['id_cuenta_estudio']); ?>">
                                <?php echo htmlspecialchars($cuenta['usuario_cuenta_estudio']); ?> 
                                (<?php echo htmlspecialchars($cuenta['nombre_pagina']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro: Estado -->
                <div class="col-md-1">
                    <label for="filtro_estado" class="form-label">Estado</label>
                    <select class="form-select" id="filtro_estado">
                        <option value="todas">Todas</option>
                        <option value="activas" selected>Activas</option>
                        <option value="eliminadas">Eliminadas</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Credenciales -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-key"></i> Lista de Credenciales
            </h5>
            <div>
                <span class="badge bg-info" id="badge-total-registros">Cargando...</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="tabla-credenciales">
                    <thead class="table-light">
                        <tr>
                            <th>Modelo</th>
                            <th>Plataforma</th>
                            <th>Usuario</th>
                            <th>Password</th>
                            <th>Email</th>
                            <th>Cuenta Estudio</th>
                            <th>Estudio</th>
                            <th>Casa</th>
                            <th>Creada</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas cargadas dinámicamente por AJAX -->
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-3 text-muted">Cargando credenciales...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <!-- Paginación -->
            <nav aria-label="Paginación de credenciales">
                <ul class="pagination justify-content-center mb-0" id="paginacion-credenciales">
                    <!-- Botones de paginación generados dinámicamente -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal de Detalle de Credencial -->
<div class="modal fade" id="modalDetalleCredencial" tabindex="-1" aria-labelledby="modalDetalleCredencialLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleCredencialLabel">
                    <i class="bi bi-info-circle"></i> Detalle de Credencial
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modal-body-detalle">
                <!-- Contenido dinámico -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>
