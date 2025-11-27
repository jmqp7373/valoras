<?php
/**
 * Vista: Gestión de Estudios
 * 
 * Administración de estudios, casas/plataformas, categorías y clases
 * con sistema de auditoría centralizado
 */

// Habilitar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar permisos (solo admin puede acceder)
$es_admin = false;
if (isset($_SESSION['rol_actual'])) {
    $rol = strtolower($_SESSION['rol_actual']);
    $es_admin = ($rol === 'superadmin' || $rol === 'admin');
}

// Si no es admin, verificar nivel_orden
if (!$es_admin) {
    try {
        $pdo = getDBConnection();
        $query = "SELECT nivel_orden FROM usuarios_info WHERE id_usuario = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $es_admin = $result && $result['nivel_orden'] <= 2;
    } catch (PDOException $e) {
        error_log("Error verificando permisos: " . $e->getMessage());
    }
}

// Si no tiene permisos, redirigir
if (!$es_admin) {
    $_SESSION['error_message'] = 'No tienes permisos para acceder a esta sección';
    header('Location: ../../index.php');
    exit();
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// ============================================
// OBTENER INFORMACIÓN DEL MÓDULO DESDE LA BD
// ============================================
try {
    $stmt = $pdo->prepare("SELECT titulo, subtitulo, icono FROM modulos WHERE clave = ? AND activo = 1 LIMIT 1");
    $stmt->execute(['views_admin_estudiosGestion']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Gestión de Estudios - Valora";

// Título, subtítulo e icono desde la base de datos
$titulo_pagina = $modulo['titulo'] ?? 'Gestión de Estudios';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Administrar estudios, casas y categorías';
$icono_pagina = $modulo['icono'] ?? 'fa-building';

// Variables para header.php
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Gestión de Estudios', 'url' => null]
];

// CSS adicional específico de esta página
$additional_css = [
    'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    '../../assets/css/estudiosGestion.css'
];

// JS adicional específico de esta página
$additional_js = [
    'https://code.jquery.com/jquery-3.7.0.min.js',
    'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11'
];

// ============================================
// CAPTURAR CONTENIDO DE LA PÁGINA
// ============================================
ob_start();
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="estudiosTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="casas-tab" data-bs-toggle="tab" data-bs-target="#casas" type="button" role="tab">
                        <i class="fas fa-home me-1"></i> Casa Estudios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="estudios-tab" data-bs-toggle="tab" data-bs-target="#estudios" type="button" role="tab">
                        <i class="fas fa-building me-1"></i> Estudios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="categorias-tab" data-bs-toggle="tab" data-bs-target="#categorias" type="button" role="tab">
                        <i class="fas fa-tags me-1"></i> Categorías
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="clases-tab" data-bs-toggle="tab" data-bs-target="#clases" type="button" role="tab">
                        <i class="fas fa-layer-group me-1"></i> Clases
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">
                        <i class="fas fa-history me-1"></i> Historial de Cambios
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="estudiosTabContent">
                <!-- TAB 1: CASA ESTUDIOS -->
                <div class="tab-pane fade show active" id="casas" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <?php if ($es_admin): ?>
                                <button class="btn btn-primary" id="btnNuevoEstudio">
                                    <i class="fas fa-plus me-1"></i> Nueva Casa
                                </button>
                            <?php endif; ?>
                        </div>
                        <button type="button" 
                                id="btnToggleInactivosEstudios"
                                class="btn btn-sm"
                                style="background: rgba(106, 27, 27, 0.15); color: #6A1B1B; border: 1px solid rgba(106, 27, 27, 0.3); padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease;"
                                onmouseover="this.style.background='rgba(106, 27, 27, 0.25)'; this.style.borderColor='rgba(106, 27, 27, 0.5)';"
                                onmouseout="this.style.background='rgba(106, 27, 27, 0.15)'; this.style.borderColor='rgba(106, 27, 27, 0.3)';">
                            <i class="fas fa-eye-slash" style="font-size: 0.9rem; margin-right: 6px;"></i>Mostrar Inactivos
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style='padding: 1rem;' id="tablaEstudios">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>CREACIÓN</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: ESTUDIOS -->
                <div class="tab-pane fade" id="estudios" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-3 align-items-center">
                            <?php if ($es_admin): ?>
                                <button class="btn btn-primary" id="btnNuevoEstudio">
                                    <i class="fas fa-plus me-1"></i> Nuevo Estudio
                                </button>
                            <?php endif; ?>
                            <div class="d-flex align-items-center gap-2">
                                <label for="filtroCasaEstudio" class="form-label mb-0 text-nowrap" style="font-weight: 500; color: #6A1B1B;">
                                    <i class="fas fa-filter me-1"></i>Filtrar Estudio por Casa:
                                </label>
                                <select class="form-select" id="filtroCasaEstudio" style="min-width: 250px; max-width: 300px;">
                                    <option value="">Todas las casas</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" 
                                id="btnToggleInactivosCasas"
                                class="btn btn-sm"
                                style="background: rgba(106, 27, 27, 0.15); color: #6A1B1B; border: 1px solid rgba(106, 27, 27, 0.3); padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease;"
                                onmouseover="this.style.background='rgba(106, 27, 27, 0.25)'; this.style.borderColor='rgba(106, 27, 27, 0.5)';"
                                onmouseout="this.style.background='rgba(106, 27, 27, 0.15)'; this.style.borderColor='rgba(106, 27, 27, 0.3)';">
                            <i class="fas fa-eye-slash" style="font-size: 0.9rem; margin-right: 6px;"></i>Mostrar Inactivos
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaCasas">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>CREACIÓN</th>
                                    <th>Estudio</th>
                                    <th>Nombre Casa</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: CATEGORÍAS -->
                <div class="tab-pane fade" id="categorias" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <?php if ($es_admin): ?>
                                <button class="btn btn-primary" id="btnNuevaCategoria">
                                    <i class="fas fa-plus me-1"></i> Nueva Categoría
                                </button>
                            <?php endif; ?>
                        </div>
                        <button type="button" 
                                id="btnToggleInactivosCategorias"
                                class="btn btn-sm"
                                style="background: rgba(106, 27, 27, 0.15); color: #6A1B1B; border: 1px solid rgba(106, 27, 27, 0.3); padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease;"
                                onmouseover="this.style.background='rgba(106, 27, 27, 0.25)'; this.style.borderColor='rgba(106, 27, 27, 0.5)';"
                                onmouseout="this.style.background='rgba(106, 27, 27, 0.15)'; this.style.borderColor='rgba(106, 27, 27, 0.3)';">
                            <i class="fas fa-eye-slash" style="font-size: 0.9rem; margin-right: 6px;"></i>Mostrar Inactivos
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaCategorias">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>CREACIÓN</th>
                                    <th>Nombre</th>
                                    <?php if ($es_admin): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 4: CLASES -->
                <div class="tab-pane fade" id="clases" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <?php if ($es_admin): ?>
                                <button class="btn btn-primary" id="btnNuevaClase">
                                    <i class="fas fa-plus me-1"></i> Nueva Clase
                                </button>
                            <?php endif; ?>
                        </div>
                        <button type="button" 
                                id="btnToggleInactivosClases"
                                class="btn btn-sm"
                                style="background: rgba(106, 27, 27, 0.15); color: #6A1B1B; border: 1px solid rgba(106, 27, 27, 0.3); padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease;"
                                onmouseover="this.style.background='rgba(106, 27, 27, 0.25)'; this.style.borderColor='rgba(106, 27, 27, 0.5)';"
                                onmouseout="this.style.background='rgba(106, 27, 27, 0.15)'; this.style.borderColor='rgba(106, 27, 27, 0.3)';">
                            <i class="fas fa-eye-slash" style="font-size: 0.9rem; margin-right: 6px;"></i>Mostrar Inactivos
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaClases">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>CREACIÓN</th>
                                    <th>Nombre</th>
                                    <?php if ($es_admin): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 5: HISTORIAL -->
                <div class="tab-pane fade" id="historial" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label for="filtroHistorialTabla" class="form-label">Filtrar por Tabla:</label>
                            <select class="form-select" id="filtroHistorialTabla">
                                <option value="">Todas las tablas</option>
                                <option value="estudios">Estudios</option>
                                <option value="estudios_casas">Casas</option>
                                <option value="estudios_categorias">Categorías</option>
                                <option value="estudios_clases">Clases</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="filtroHistorialAccion" class="form-label">Filtrar por Acción:</label>
                            <select class="form-select" id="filtroHistorialAccion">
                                <option value="">Todas las acciones</option>
                                <option value="INSERT">Creación</option>
                                <option value="UPDATE">Actualización</option>
                                <option value="DELETE">Eliminación</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button class="btn btn-secondary me-2" id="btnFiltrarHistorial">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <button class="btn btn-outline-secondary ms-2" id="btnLimpiarFiltros">
                                <i class="fas fa-eraser me-1"></i> Limpiar
                            </button>
                        </div>
                    </div>
                    
                    <div id="contenedorHistorial">
                        <!-- Se llenará con AJAX -->
                    </div>
                </div>
            </div>
    </div>

    <!-- Modal Estudio -->
    <div class="modal fade" id="modalEstudio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstudioTitulo">Nuevo Estudio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEstudio">
                        <input type="hidden" id="estudio_id" name="id_estudio">
                        <div class="mb-3">
                            <label for="estudio_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="estudio_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="estudio_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="estudio_descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEstudio">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Casa -->
    <div class="modal fade" id="modalCasa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCasaTitulo">Nueva Casa/Plataforma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCasa">
                        <input type="hidden" id="casa_id" name="id_casa">
                        <div class="mb-3">
                            <label for="casa_estudio" class="form-label">Estudio <span class="text-danger">*</span></label>
                            <select class="form-select" id="casa_estudio" name="id_estudio" required>
                                <option value="">Seleccione un estudio</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="casa_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="casa_nombre" name="nombre_casa" required>
                        </div>
                        <div class="mb-3">
                            <label for="casa_url" class="form-label">URL</label>
                            <input type="url" class="form-control" id="casa_url" name="url_casa" placeholder="https://...">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarCasa">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Categoría -->
    <div class="modal fade" id="modalCategoria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCategoriaTitulo">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCategoria">
                        <input type="hidden" id="categoria_id" name="id_categoria">
                        <div class="mb-3">
                            <label for="categoria_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoria_nombre" name="nombre_categoria" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoria_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="categoria_descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarCategoria">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Clase -->
    <div class="modal fade" id="modalClase" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClaseTitulo">Nueva Clase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formClase">
                        <input type="hidden" id="clase_id" name="id_clase">
                        <div class="mb-3">
                            <label for="clase_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="clase_nombre" name="nombre_clase" required>
                        </div>
                        <div class="mb-3">
                            <label for="clase_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="clase_descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarClase">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

<!-- Script de configuración - DEBE estar ANTES de estudios-gestion.js -->
<script>
    window.ESTUDIOS_CONFIG = {
        esAdmin: <?php echo $es_admin ? 'true' : 'false'; ?>
    };
    console.log('🔐 Configuración cargada:', window.ESTUDIOS_CONFIG);
</script>

<?php
// Capturar el contenido generado
$content = ob_get_clean();

// Agregar el JS específico de esta página al final
$additional_js[] = '../../assets/js/estudios-gestion.js';

// ============================================
// CARGAR LAYOUT MASTER
// ============================================
include __DIR__ . '/../layouts/master.php';
?>
