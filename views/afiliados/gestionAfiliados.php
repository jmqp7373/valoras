<?php
/**
 * Vista: Gestión de Afiliados
 * 
 * Administración de modelos, líderes y referentes
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
        $query = "SELECT nivel_orden FROM usuarios WHERE id_usuario = ?";
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
    $stmt->execute(['views_afiliados_gestionAfiliados']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Gestión de Afiliados - Valora";

// Título, subtítulo e icono desde la base de datos
$titulo_pagina = $modulo['titulo'] ?? 'Gestión de Afiliados';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Administrar modelos, líderes y referentes';
$icono_pagina = $modulo['icono'] ?? 'fa-users';

// Variables para header.php
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Gestión de Afiliados', 'url' => null]
];

// CSS adicional específico de esta página
$additional_css = [
    'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    '../../assets/css/afiliadosGestion.css'
];

// JS adicional específico de esta página
$additional_js = [
    'https://code.jquery.com/jquery-3.7.0.min.js',
    'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js',
    '../../assets/js/afiliados-gestion.js'
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
            <ul class="nav nav-tabs" id="afiliadosTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="modelos-tab" data-bs-toggle="tab" data-bs-target="#modelos" type="button" role="tab">
                        <i class="fas fa-star me-1"></i> Artistas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="lideres-tab" data-bs-toggle="tab" data-bs-target="#lideres" type="button" role="tab">
                        <i class="fas fa-user-tie me-1"></i> Líderes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="referentes-tab" data-bs-toggle="tab" data-bs-target="#referentes" type="button" role="tab">
                        <i class="fas fa-user-friends me-1"></i> Referentes
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="afiliadosTabContent">
                <!-- TAB 1: MODELOS -->
                <div class="tab-pane fade show active" id="modelos" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <h5 class="mb-0">
                            <i class="fas fa-star text-warning me-2"></i>
                            Artistas Activos (Nivel Orden = 0)
                        </h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaModelos">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Celular</th>
                                    <th>Email</th>
                                    <th>Casa</th>
                                    <th>Estudio</th>
                                    <th>Fecha Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: LÍDERES -->
                <div class="tab-pane fade" id="lideres" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            Líderes Activos (Nivel Orden ≠ 0)
                        </h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaLideres">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Celular</th>
                                    <th>Email</th>
                                    <th>Nivel Orden</th>
                                    <th>Casa</th>
                                    <th>Estudio</th>
                                    <th>Fecha Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: REFERENTES -->
                <div class="tab-pane fade" id="referentes" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <h5 class="mb-0">
                            <i class="fas fa-user-friends text-success me-2"></i>
                            Usuarios Referentes
                        </h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaReferentes">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Celular</th>
                                    <th>Email</th>
                                    <th>Nivel Orden</th>
                                    <th># Referidos</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llenará con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/master.php';
?>
