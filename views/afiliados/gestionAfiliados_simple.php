<?php
/**
 * Vista: Gestión de Afiliados - Versión Simple SIN DataTables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Afiliados.php';

startSessionSafely();

if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$afiliados = new Afiliados($db);

// Variables para master.php
$page_title = 'Gestión de Afiliados - Valora';
$titulo_pagina = 'Gestión de Afiliados';
$subtitulo_pagina = 'Versión de prueba sin DataTables';
$icono_pagina = 'fa-users';

ob_start();
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs" id="afiliadosTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="modelos-tab" data-bs-toggle="tab" data-bs-target="#modelos" type="button">
                        <i class="fas fa-star me-1"></i> Modelos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="lideres-tab" data-bs-toggle="tab" data-bs-target="#lideres" type="button">
                        <i class="fas fa-user-tie me-1"></i> Líderes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="referentes-tab" data-bs-toggle="tab" data-bs-target="#referentes" type="button">
                        <i class="fas fa-user-friends me-1"></i> Referentes
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="afiliadosTabContent">
                <!-- TAB 1: MODELOS -->
                <div class="tab-pane fade show active" id="modelos" role="tabpanel">
                    <h5 class="mt-3 mb-3">Modelos Activos (Nivel Orden = 0)</h5>
                    <?php
                    try {
                        $modelos = $afiliados->obtenerModelos();
                        echo "<p><strong>Total: " . count($modelos) . " modelos</strong></p>";
                        
                        if (count($modelos) > 0) {
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped table-hover'>";
                            echo "<thead class='table-dark'>";
                            echo "<tr><th>ID</th><th>Usuario</th><th>Nombres</th><th>Apellidos</th><th>Celular</th><th>Email</th><th>Estudio</th></tr>";
                            echo "</thead><tbody>";
                            
                            // Mostrar solo los primeros 50
                            $contador = 0;
                            foreach ($modelos as $modelo) {
                                if ($contador >= 50) break;
                                echo "<tr>";
                                echo "<td>{$modelo['id_usuario']}</td>";
                                echo "<td><strong>{$modelo['usuario']}</strong></td>";
                                echo "<td>{$modelo['nombres']}</td>";
                                echo "<td>{$modelo['apellidos']}</td>";
                                echo "<td>" . ($modelo['celular'] ?: '-') . "</td>";
                                echo "<td>" . ($modelo['email'] ?: '-') . "</td>";
                                echo "<td>{$modelo['id_estudio']}</td>";
                                echo "</tr>";
                                $contador++;
                            }
                            
                            echo "</tbody></table></div>";
                            if (count($modelos) > 50) {
                                echo "<p class='text-muted'>Mostrando 50 de " . count($modelos) . " registros</p>";
                            }
                        } else {
                            echo "<div class='alert alert-warning'>No hay modelos activos</div>";
                        }
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                    }
                    ?>
                </div>

                <!-- TAB 2: LÍDERES -->
                <div class="tab-pane fade" id="lideres" role="tabpanel">
                    <h5 class="mt-3 mb-3">Líderes Activos (Nivel Orden ≠ 0)</h5>
                    <?php
                    try {
                        $lideres = $afiliados->obtenerLideres();
                        echo "<p><strong>Total: " . count($lideres) . " líderes</strong></p>";
                        
                        if (count($lideres) > 0) {
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped table-hover'>";
                            echo "<thead class='table-dark'>";
                            echo "<tr><th>ID</th><th>Usuario</th><th>Nombres</th><th>Apellidos</th><th>Nivel</th><th>Celular</th><th>Email</th></tr>";
                            echo "</thead><tbody>";
                            
                            foreach ($lideres as $lider) {
                                echo "<tr>";
                                echo "<td>{$lider['id_usuario']}</td>";
                                echo "<td><strong>{$lider['usuario']}</strong></td>";
                                echo "<td>{$lider['nombres']}</td>";
                                echo "<td>{$lider['apellidos']}</td>";
                                echo "<td><span class='badge bg-primary'>{$lider['nivel_orden']}</span></td>";
                                echo "<td>" . ($lider['celular'] ?: '-') . "</td>";
                                echo "<td>" . ($lider['email'] ?: '-') . "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                        } else {
                            echo "<div class='alert alert-warning'>No hay líderes activos</div>";
                        }
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                    }
                    ?>
                </div>

                <!-- TAB 3: REFERENTES -->
                <div class="tab-pane fade" id="referentes" role="tabpanel">
                    <h5 class="mt-3 mb-3">Usuarios Referentes</h5>
                    <?php
                    try {
                        $referentes = $afiliados->obtenerReferentes();
                        echo "<p><strong>Total: " . count($referentes) . " referentes</strong></p>";
                        
                        if (count($referentes) > 0) {
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped table-hover'>";
                            echo "<thead class='table-dark'>";
                            echo "<tr><th>ID</th><th>Usuario</th><th>Nombres</th><th>Apellidos</th><th>Nivel</th><th># Referidos</th><th>Estado</th></tr>";
                            echo "</thead><tbody>";
                            
                            foreach ($referentes as $ref) {
                                $estado = $ref['estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                                echo "<tr>";
                                echo "<td>{$ref['id_usuario']}</td>";
                                echo "<td><strong>{$ref['usuario']}</strong></td>";
                                echo "<td>{$ref['nombres']}</td>";
                                echo "<td>{$ref['apellidos']}</td>";
                                echo "<td>{$ref['nivel_orden']}</td>";
                                echo "<td><span class='badge bg-info'>{$ref['total_referidos']}</span></td>";
                                echo "<td>{$estado}</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                        } else {
                            echo "<div class='alert alert-warning'>No hay usuarios referentes</div>";
                        }
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Gestión de Afiliados', 'url' => null]
];

$additional_css = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

$additional_js = [];

require_once __DIR__ . '/../layouts/master.php';
?>
