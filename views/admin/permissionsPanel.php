<?php
/**
 * Panel de Administración de Permisos - VERSIÓN OPTIMIZADA CON AJAX
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-09
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Permisos.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar permisos de administrador
$db = getDBConnection();
$permisosModel = new Permisos($db);
$idUsuario = $_SESSION['user_id'] ?? null;

if (!$idUsuario || !$permisosModel->esAdmin($idUsuario)) {
    die('Acceso denegado. Solo administradores pueden acceder a esta sección.');
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// Variables para header
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// OPTIMIZACIÓN: Solo obtener roles en el servidor (carga inicial rápida)
$roles = $permisosModel->obtenerRoles();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Permisos - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/permissionsPanel.css">
    <style>
        /* Estilos para skeleton loading */
        .skeleton {
            animation: skeleton-loading 1s linear infinite alternate;
        }
        
        @keyframes skeleton-loading {
            0% { background-color: hsl(200, 20%, 80%); }
            100% { background-color: hsl(200, 20%, 95%); }
        }
        
        .skeleton-text {
            width: 100%;
            height: 20px;
            border-radius: 4px;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>
</head>
<body style="background-color: #F8F9FA;">
    <?php include '../../components/header/header.php'; ?>
    
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
    
    <div class="container" style="max-width: 1400px; padding: 40px 20px;">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb" style="font-size: 14px;">
                <li class="breadcrumb-item"><a href="../../index.php" style="color: #6A1B1B; text-decoration: none;">Dashboard</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">Administración de Permisos</li>
            </ol>
        </nav>

        <!-- Título Principal -->
        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase mb-2" style="color: #6A1B1B; letter-spacing: 1px;">
                ⚙️ Panel de Permisos del Sistema
            </h2>
            <p class="text-muted mb-4">
                Visualiza y gestiona los permisos de acceso por rol y por usuario individual
            </p>
            
            <!-- Botones de Vista -->
            <div class="btn-group" role="group" aria-label="Vista de permisos">
                <button type="button" class="btn btn-primary active" id="btnRoles" style="background: linear-gradient(135deg, #6A1B1B, #882A57); border: none; padding: 10px 30px;">
                    🧩 Permisos por Rol
                </button>
                <button type="button" class="btn btn-secondary" id="btnUsuarios" style="padding: 10px 30px;">
                    👤 Permisos Individuales
                </button>
            </div>
        </div>

        <!-- Vista de Permisos por Rol -->
        <div id="rolesView">
            <?php if (!empty($roles)): ?>
            
            <!-- Subtítulo y Botón Toggle Exentos -->
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-2" style="color: #6A1B1B;">📊 Matriz de Permisos por Rol</h4>
                <p class="text-muted mb-3">Visualiza y gestiona los permisos de acceso cruzados por rol y módulo</p>
                
                <!-- Botón Toggle Exentos -->
                <button type="button" 
                        id="btnToggleExentos" 
                        class="btn btn-outline-secondary"
                        style="padding: 10px 25px; border-radius: 8px; font-weight: 600;">
                    <i class="bi bi-eye-slash-fill me-2"></i>Ocultar Exentos
                </button>
            </div>
            
            <!-- Tabla Maestra de Permisos -->
            <div class="table-responsive rounded-3 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                
                <!-- Panel flotante de restauración -->
                <div id="panelRestauracion" style="display: none; position: sticky; top: 20px; z-index: 100; background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    <div class="text-center mb-3">
                        <h5 class="mb-2">👁️ Todas las columnas están ocultas</h5>
                        <p class="mb-0 small">Haz clic en un botón para mostrar ese rol:</p>
                    </div>
                    <div class="d-flex justify-content-center gap-3 flex-wrap" id="botonesRestauracion"></div>
                </div>
                
                <table class="table table-bordered table-hover table-sm mb-0" id="tablaPermisos">
                    <thead style="position: sticky; top: 0; z-index: 10; background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; border-bottom: 3px solid #7b1733;">
                        <tr>
                            <th rowspan="2" class="align-middle text-center" style="min-width: 400px; max-width: 500px; vertical-align: middle; position: sticky; left: 0; z-index: 11; background: linear-gradient(135deg, #6A1B1B, #882A57); padding: 15px; color: white; font-size: 1.1rem; font-weight: 700;">
                                <i class="bi bi-file-earmark-code-fill me-2" style="font-size: 1.3rem;"></i>
                                <strong style="letter-spacing: 0.5px;">Archivo / Ruta</strong>
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
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
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
                                        <button type="button" 
                                                class="btn btn-sm toggle-rol-btn active" 
                                                data-rol-id="<?= $rol['id'] ?>"
                                                style="background: rgba(255, 255, 255, 0.25); border: 2px solid rgba(255, 255, 255, 0.6); color: #ffffff; padding: 6px 10px; font-size: 1.3rem; cursor: pointer; transition: all 0.2s ease; border-radius: 8px; min-width: 40px; height: 36px; display: flex; align-items: center; justify-content: center;"
                                                title="Ocultar columnas de <?= htmlspecialchars($rol['nombre']) ?>">
                                            <i class="bi bi-eye-fill" style="font-size: 1.3rem;"></i>
                                        </button>
                                    </div>
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
            
            <!-- Leyenda con Badges -->
            <div class="mt-4 p-3 bg-white rounded-3 shadow-sm">
                <div class="d-flex justify-content-center gap-4 flex-wrap align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <strong class="text-muted">Leyenda:</strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" checked disabled class="form-check-input" style="width: 20px; height: 20px;">
                        <span class="badge bg-success">✔ Permitido</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" disabled class="form-check-input" style="width: 20px; height: 20px;">
                        <span class="badge bg-secondary">✖ Denegado</span>
                    </div>
                </div>
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

        <!-- Botón Volver -->
        <div class="text-center mt-5">
            <a href="../../index.php" class="btn btn-outline-dark rounded-pill px-5 py-3 fw-semibold shadow-sm" style="border: 2px solid #7b1733; color: #7b1733; transition: all 0.3s ease;">
                <i class="bi bi-arrow-left-circle me-2"></i>Volver al Dashboard
            </a>
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

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pasar el token CSRF a JavaScript
        window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
    <script src="../../assets/js/permissionsPanel.js"></script>
    <script src="../../assets/js/permissionsPanelAjax.js"></script>
</body>
</html>
