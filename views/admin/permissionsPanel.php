<?php
/**
 * Panel de Administración de Permisos - VERSIÓN OPTIMIZADA CON AJAX
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-09
 */

// Activar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Permisos.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar permisos de administrador
try {
    $db = getDBConnection();
    $permisosModel = new Permisos($db);
    $idUsuario = $_SESSION['user_id'] ?? null;

    if (!$idUsuario || !$permisosModel->esAdmin($idUsuario)) {
        header('Location: acceso_denegado.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Error en permissionsPanel: " . $e->getMessage());
    die("Error del sistema: " . $e->getMessage() . "<br>Por favor contacte al administrador.");
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// ============================================
// OBTENER INFORMACIÓN DEL MÓDULO DESDE LA BD
// ============================================
try {
    $stmt = $db->prepare("SELECT titulo, subtitulo, icono FROM modulos WHERE ruta_completa = ? AND activo = 1 LIMIT 1");
    $stmt->execute(['views\admin\permissionsPanel.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Panel de Permisos - Valora";

// Título, subtítulo e icono desde la base de datos
$titulo_pagina = $modulo['titulo'] ?? 'Panel de Permisos del Sistema';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Administra roles y permisos del sistema';
$icono_pagina = $modulo['icono'] ?? '🔐';

// Variables para header.php
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Administración de Permisos', 'url' => null]
];

// CSS y JS adicionales
$additional_css = [
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    '../../assets/css/permissionsPanel.css'
];
$additional_js = [
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
    '../../assets/js/permissionsPanelAjax.js'
];

// OPTIMIZACIÓN: Solo obtener roles en el servidor (carga inicial rápida)
try {
    $roles = $permisosModel->obtenerRoles();
} catch (Exception $e) {
    error_log("Error obteniendo roles: " . $e->getMessage());
    $roles = [];
}

// Los botones se generan automáticamente en headerTitulo.php
// basados en la categoría del módulo actual

// ============================================
// CAPTURAR CONTENIDO DE LA PÁGINA
// ============================================
ob_start();
?>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center" style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
            <div class="spinner-border" style="width: 3rem; height: 3rem; color: #6A1B1B;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 mb-0 fw-semibold" style="color: #6A1B1B;">Cargando permisos...</p>
            <small class="text-muted">Optimizando datos...</small>
        </div>
    </div>
    
    <!-- Vista de Permisos por Rol -->
    <div class="container-fluid" style="padding: 20px 40px;">
        <div id="rolesView">
            <?php if (!empty($roles)): ?>
            
            <!-- Tabla Maestra de Permisos -->
            <div class="table-responsive rounded-3 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                
                <table class="table table-bordered table-hover table-sm mb-0" id="tablaPermisos">
                    <thead style="position: sticky; top: 0; z-index: 10; background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; border-bottom: 3px solid #7b1733;">
                        <tr>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 120px; max-width: 120px; vertical-align: middle; position: sticky; left: 0; z-index: 11; background: linear-gradient(135deg, #6A1B1B, #882A57); padding: 15px; color: white; font-size: 1.1rem; font-weight: 700;">
                                <div style="font-size: 0.9rem; font-weight: 700; letter-spacing: 0.5px;">CATEGORÍA</div>
                            </th>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 400px; max-width: 500px; vertical-align: middle; position: sticky; left: 120px; z-index: 11; background: linear-gradient(135deg, #6A1B1B, #882A57); padding: 15px; color: white; font-size: 1.1rem; font-weight: 700;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="bi bi-file-earmark-code-fill" style="font-size: 1.3rem;"></i>
                                        <strong style="letter-spacing: 0.5px;">Archivo / Ruta</strong>
                                    </div>
                                    <button type="button" 
                                            id="btnToggleExentos" 
                                            class="btn btn-sm"
                                            style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.borderColor='rgba(255,255,255,0.5)';"
                                            onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.borderColor='rgba(255,255,255,0.3)';">
                                        <i class="bi bi-eye-slash-fill" style="font-size: 0.9rem; margin-right: 6px;"></i>Mostrar Exentos
                                    </button>
                                </div>
                            </th>
                            <?php 
                            $idx = 0;
                            foreach ($roles as $rol): 
                            ?>
                                <th colspan="3" 
                                    rowspan="1"
                                    class="text-center rol-header rol-col-<?= $rol['id'] ?>" 
                                    style="min-width: 200px; border-left: 2px solid rgba(255,255,255,0.3); padding: 12px;"
                                    data-original-rowspan="1">
                                    <span>
                                        <?php
                                        $iconos = [
                                            'superadmin' => '👑',
                                            'admin' => '🔐',
                                            'promotor' => '📢',
                                            'modelo' => '⭐',
                                            'soporte' => '🛠️'
                                        ];
                                        echo ($iconos[strtolower($rol['nombre'])] ?? '🔹') . ' ' . htmlspecialchars(ucfirst($rol['nombre']));
                                        ?>
                                    </span>
                                </th>
                            <?php 
                            $idx++;
                            endforeach; 
                            ?>
                        </tr>
                        <tr>
                            <?php 
                            $idx = 0;
                            foreach ($roles as $rol): 
                            ?>
                                <th class="text-center rol-subheader rol-col-<?= $rol['id'] ?>" style="font-size: 0.85rem; padding: 10px; border-left: 2px solid rgba(255,255,255,0.3);">
                                    <i class="bi bi-eye-fill me-1"></i>Ver
                                </th>
                                <th class="text-center rol-subheader rol-col-<?= $rol['id'] ?>" style="font-size: 0.85rem; padding: 10px;">
                                    <i class="bi bi-pencil-fill me-1"></i>Editar
                                </th>
                                <th class="text-center rol-subheader rol-col-<?= $rol['id'] ?> d-none d-md-table-cell" style="font-size: 0.85rem; padding: 10px;">
                                    <i class="bi bi-trash-fill me-1"></i>Eliminar
                                </th>
                            <?php 
                            $idx++;
                            endforeach; 
                            ?>
                        </tr>
                    </thead>
                    <tbody id="tbodyPermisos">
                        <!-- Los datos se cargan vía AJAX -->
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
                <div class="alert alert-warning text-center rounded-3 shadow-sm">
                    ⚠️ No se encontraron roles en el sistema.
                </div>
            <?php endif; ?>
        </div>

        <!-- Vista de Permisos Individuales -->
        <div id="usuariosView" style="display: none;">
            <div class="card shadow-sm rounded-3" style="border: none;">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label for="selectUsuario" class="form-label fw-semibold" style="color: #333;">
                            Selecciona un usuario:
                        </label>
                        <select id="selectUsuario" class="form-select" style="max-width: 500px;">
                            <option value="">-- Selecciona un usuario --</option>
                        </select>
                    </div>

                    <div id="permisosUsuarioContainer" style="display: none;">
                        <h5 id="usuarioNombre" class="mb-3 fw-bold" style="color: #6A1B1B;"></h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 40%;">Módulo</th>
                                        <th class="text-center" style="width: 20%;">Ver</th>
                                        <th class="text-center" style="width: 20%;">Editar</th>
                                        <th class="text-center" style="width: 20%;">Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody id="permisosUsuarioBody">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container para notificaciones -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="permisosToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-semibold" id="toastMessage">
                    Mensaje aquí
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    
    <!-- Modal de Edición de Nombre Descriptivo -->
    <div class="modal fade" id="modalEditarModulo" tabindex="-1" aria-labelledby="modalEditarModuloLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; border-bottom: none; padding: 20px 24px;">
                    <h5 class="modal-title fw-bold" id="modalEditarModuloLabel">
                        <i class="bi bi-pencil-square me-2"></i>
                        Editar Nombre Descriptivo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <form id="formEditarModulo">
                        <div class="mb-3">
                            <label for="inputNombreDescriptivo" class="form-label fw-semibold" style="color: #6A1B1B;">
                                Nombre Descriptivo
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="inputNombreDescriptivo" 
                                   placeholder="Ej: Panel de Administración, Gestión de Usuarios..."
                                   style="border: 2px solid #e9ecef; border-radius: 8px; padding: 10px 14px; font-size: 0.95rem;">
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Deja vacío para mostrar solo el nombre técnico del archivo
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small">Archivo:</label>
                            <div class="p-2 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef; font-family: 'Courier New', monospace; font-size: 0.85rem; color: #6c757d;">
                                <span id="spanRutaModulo"></span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px; background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 20px;">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" form="formEditarModulo" class="btn btn-primary" id="btnGuardarNombre" style="background: linear-gradient(135deg, #6A1B1B, #882A57); border: none; border-radius: 8px; padding: 8px 20px;">
                        <i class="bi bi-check-circle me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pasar el token CSRF a JavaScript
        window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>

<?php
// Capturar el contenido generado
$content = ob_get_clean();

// ============================================
// CARGAR LAYOUT MASTER
// ============================================
include __DIR__ . '/../layouts/master.php';
?>
