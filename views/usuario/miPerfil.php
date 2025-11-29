<?php
/**
 * Valora.vip - Mi Perfil
 * Vista para gestión completa del perfil de usuario
 * Basada en la antigua Hoja de Entrevista de FLYCAM
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/PerfilController.php';

startSessionSafely();

// Verificar autenticación
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Instanciar controlador
$perfilController = new PerfilController();

// Procesar formulario si es POST
$mensaje = null;
$tipo_mensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_perfil'])) {
    $resultado = $perfilController->actualizarPerfil();
    $mensaje = $resultado['message'];
    $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
}

// Obtener datos del perfil
$perfil = $perfilController->obtenerPerfil();
$estudios = $perfilController->obtenerEstudios();
$bancos = $perfilController->obtenerBancos();

// DEBUG: Ver qué datos llegan
error_log("PERFIL DATA: " . print_r($perfil, true));
error_log("ID USUARIO SESION: " . ($_SESSION['user_id'] ?? 'NO HAY'));

// Valores por defecto
$perfil = $perfil ?: [];
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

// Decodificar dias_descanso si es un JSON string
$dias_descanso_raw = $perfil['dias_descanso'] ?? [];
if (is_string($dias_descanso_raw)) {
    $dias_seleccionados = json_decode($dias_descanso_raw, true) ?: [];
} else {
    $dias_seleccionados = is_array($dias_descanso_raw) ? $dias_descanso_raw : [];
}

$progreso = $perfil['progreso_perfil'] ?? 0;

// ============================================
// OBTENER INFORMACIÓN DEL MÓDULO DESDE LA BD
// ============================================
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT titulo, subtitulo, icono FROM modulos WHERE ruta_completa = ? AND activo = 1 LIMIT 1");
    $stmt->execute(['views\usuario\miPerfil.php']);
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error obteniendo módulo: " . $e->getMessage());
    $modulo = null;
}

// ============================================
// CONFIGURACIÓN PARA MASTER LAYOUT
// ============================================

// Meta información de la página
$page_title = "Mi Perfil - Valora";

// Título, subtítulo e icono desde la base de datos
$titulo_pagina = $modulo['titulo'] ?? 'Mi Perfil';
$subtitulo_pagina = $modulo['subtitulo'] ?? 'Completa tu información personal y profesional';
$icono_pagina = $modulo['icono'] ?? '👤';

// Variables para header.php
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';

// Variables para breadcrumbs.php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '../../index.php'],
    ['label' => 'Mi Perfil', 'url' => null]
];

// CSS adicional específico de esta página
$additional_css = [];

// JS adicional específico de esta página
$additional_js = [];

// ============================================
// CAPTURAR CONTENIDO DE LA PÁGINA
// ============================================
ob_start();
?>

<main class="dashboard-main">
    <!-- Barra de progreso del perfil -->
    <div class="card-progreso">
        <div class="progreso-header">
            <div>
                <h3>Progreso del Perfil</h3>
                <p>Completa tu información para acceder a todas las funcionalidades</p>
            </div>
            <div class="progreso-circulo">
                <svg viewBox="0 0 100 100">
                    <circle class="progreso-bg" cx="50" cy="50" r="40"></circle>
                    <circle class="progreso-fill" cx="50" cy="50" r="40" 
                            style="stroke-dashoffset: calc(251 - (251 * <?php echo $progreso; ?>) / 100)"></circle>
                </svg>
                <span class="progreso-texto"><?php echo $progreso; ?>%</span>
            </div>
        </div>
        
        <?php if ($progreso < 100): ?>
        <div class="alerta-perfil">
            ⚠️ <strong>Perfil incompleto.</strong> 
            Completa la información faltante para activar todas las funciones del sistema.
        </div>
        <?php endif; ?>
    </div>

    <!-- Mensaje de confirmación/error -->
    <?php if ($mensaje): ?>
    <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
    <?php endif; ?>

    <!-- Formulario del perfil -->
    <form method="POST" enctype="multipart/form-data" class="form-perfil">
        <input type="hidden" name="guardar_perfil" value="1">

        <!-- Sección 1: Información Personal -->
        <div class="card-seccion">
            <h2 class="seccion-titulo">1️⃣ Información Personal</h2>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombres">Nombres <span class="requerido">*</span></label>
                    <input type="text" id="nombres" name="nombres" 
                           value="<?php echo htmlspecialchars($perfil['nombres'] ?? ''); ?>" 
                           required
                           data-autosave="true"
                           class="autosave-field">
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos <span class="requerido">*</span></label>
                    <input type="text" id="apellidos" name="apellidos" 
                           value="<?php echo htmlspecialchars($perfil['apellidos'] ?? ''); ?>" 
                           required
                           data-autosave="true"
                           class="autosave-field">
                </div>

                <div class="form-group">
                    <label for="cedula">Cédula <span class="requerido">*</span></label>
                    <input type="text" id="cedula" name="cedula" 
                           value="<?php echo htmlspecialchars($perfil['cedula'] ?? ''); ?>" readonly 
                   title="La cédula no puede modificarse">
                </div>

                <div class="form-group">
                    <label for="celular">Celular <span class="requerido">*</span></label>
                    <input type="tel" id="celular" name="celular" 
                           value="<?php echo htmlspecialchars($perfil['celular'] ?? ''); ?>" 
                           required
                           data-autosave="true"
                           class="autosave-field">
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                           value="<?php 
                               $fecha = $perfil['fecha_nacimiento'] ?? '';
                               if ($fecha && $fecha !== '0000-00-00 00:00:00') {
                                   echo htmlspecialchars(date('Y-m-d', strtotime($fecha)));
                               }
                           ?>"
                           data-autosave="true"
                           class="autosave-field">
                </div>

                <div class="form-group">
                    <label for="tipo_sangre">Tipo de Sangre</label>
                    <select id="tipo_sangre" name="tipo_sangre"
                            data-autosave="true"
                            class="autosave-field">
                        <option value="">Seleccionar...</option>
                        <?php
                        $tipos_sangre = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
                        foreach ($tipos_sangre as $tipo) {
                            $selected = ($perfil['tipo_sangre'] ?? '') === $tipo ? 'selected' : '';
                            echo "<option value=\"$tipo\" $selected>$tipo</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" 
                           value="<?php echo htmlspecialchars($perfil['ciudad'] ?? ''); ?>"
                           data-autosave="true"
                           class="autosave-field">
                </div>

                <div class="form-group full-width">
                    <label for="direccion">Dirección de Residencia</label>
                    <input type="text" id="direccion" name="direccion" 
                           value="<?php echo htmlspecialchars($perfil['direccion'] ?? ''); ?>"
                           data-autosave="true"
                           class="autosave-field">
                </div>

                <div class="form-group full-width">
                    <label for="email">Correo Electrónico <span class="requerido">*</span></label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($perfil['email'] ?? ''); ?>" 
                           required
                           data-autosave="true"
                           class="autosave-field">
                </div>
            </div>
        </div>

        <!-- Sección 2: Contacto de Emergencia -->
        <div class="card-seccion">
                    <h2 class="seccion-titulo">2️⃣ Contacto de Emergencia</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="contacto_emergencia_nombre">Nombre Completo <span class="requerido">*</span></label>
                            <input type="text" id="contacto_emergencia_nombre" name="contacto_emergencia_nombre" 
                                   value="<?php echo htmlspecialchars($perfil['contacto_emergencia_nombre'] ?? ''); ?>" 
                                   required
                                   data-autosave="true"
                                   class="autosave-field">
                        </div>

                        <div class="form-group">
                            <label for="contacto_emergencia_parentesco">Parentesco</label>
                            <input type="text" id="contacto_emergencia_parentesco" name="contacto_emergencia_parentesco" 
                                   value="<?php echo htmlspecialchars($perfil['contacto_emergencia_parentesco'] ?? ''); ?>" 
                                   placeholder="Ej: Madre, Hermano, Esposo/a"
                                   data-autosave="true"
                                   class="autosave-field">
                        </div>

                        <div class="form-group full-width">
                            <label for="contacto_emergencia_telefono">Teléfono o Celular <span class="requerido">*</span></label>
                            <input type="tel" id="contacto_emergencia_telefono" name="contacto_emergencia_telefono" 
                                   value="<?php echo htmlspecialchars($perfil['contacto_emergencia_telefono'] ?? ''); ?>"
                                   data-autosave="true"
                                   class="autosave-field">
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Salud y Condiciones Médicas -->
                <div class="card-seccion">
                    <h2 class="seccion-titulo">3️⃣ Salud y Condiciones Médicas</h2>
                    
                    <div class="form-group full-width">
                        <label for="alergias">Alergias o Condiciones Especiales</label>
                        <textarea id="alergias" name="alergias" rows="4" 
                                  placeholder="Describe aquí cualquier alergia, condición médica o tratamiento actual..."
                                  data-autosave="true"
                                  class="autosave-field"><?php echo htmlspecialchars($perfil['alergias'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width upload-group">
                        <label for="certificado_medico">Certificado Médico (PDF, JPG, PNG)</label>
                        <input type="file" id="certificado_medico" name="certificado_medico" 
                               accept=".pdf,.jpg,.jpeg,.png"
                               onchange="subirArchivo(this)">
                        <?php if (!empty($perfil['certificado_medico'])): ?>
                        <div class="preview-container clickeable" onclick="verArchivoModal('../../<?php echo htmlspecialchars($perfil['certificado_medico']); ?>', 'Certificado Médico')">
                            <?php 
                            $ext = strtolower(pathinfo($perfil['certificado_medico'], PATHINFO_EXTENSION));
                            if ($ext === 'pdf'): 
                            ?>
                                <div class="pdf-preview">
                                    <div class="pdf-icon">📄</div>
                                    <p>Certificado Médico (PDF)</p>
                                    <small>Click para ver</small>
                                </div>
                            <?php else: ?>
                                <img src="../../<?php echo htmlspecialchars($perfil['certificado_medico']); ?>" 
                                     alt="Certificado Médico"
                                     title="Click para ver en tamaño completo">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sección 4: Estudio al que pertenece -->
                <div class="card-seccion">
                    <h2 class="seccion-titulo">4️⃣ Estudio al que Pertenece</h2>
                    
                    <div class="form-group">
                        <label for="id_estudio">Seleccionar Estudio</label>
                        <select id="id_estudio" name="id_estudio"
                                data-autosave="true"
                                class="autosave-field">
                            <option value="">Sin estudio asignado</option>
                            <?php foreach ($estudios as $estudio): ?>
                            <option value="<?php echo $estudio['id_estudio']; ?>" 
                                    <?php echo ($perfil['id_estudio'] ?? '') == $estudio['id_estudio'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($estudio['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Sección 5: Cuentas Bancarias -->
                <div class="card-seccion">
                    <h2 class="seccion-titulo">5️⃣ Cuentas Bancarias</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="id_banco">Banco <span class="requerido">*</span></label>
                            <select id="id_banco" name="id_banco" 
                                    required
                                    data-autosave="true"
                                    class="autosave-field">
                                <option value="">Seleccionar banco...</option>
                                <?php 
                                $tipoActual = '';
                                foreach ($bancos as $banco): 
                                    // Agregar separador visual por tipo de banco
                                    if ($tipoActual !== $banco['tipo_banco']) {
                                        if ($tipoActual !== '') echo '</optgroup>';
                                        echo '<optgroup label="' . htmlspecialchars($banco['tipo_banco']) . '">';
                                        $tipoActual = $banco['tipo_banco'];
                                    }
                                ?>
                                <option value="<?php echo $banco['id_banco']; ?>" 
                                        <?php echo ($perfil['id_banco'] ?? '') == $banco['id_banco'] ? 'selected' : ''; ?>
                                        data-color="<?php echo htmlspecialchars($banco['color_banco']); ?>"
                                        data-codigo="<?php echo htmlspecialchars($banco['codigo_abreviado']); ?>">
                                    <?php echo htmlspecialchars($banco['nombre_banco']); ?>
                                </option>
                                <?php 
                                endforeach; 
                                if ($tipoActual !== '') echo '</optgroup>';
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="banco_tipo_cuenta">Tipo de Cuenta</label>
                            <select id="banco_tipo_cuenta" name="banco_tipo_cuenta"
                                    data-autosave="true"
                                    class="autosave-field">
                                <option value="">Seleccionar...</option>
                                <option value="Ahorros" <?php echo ($perfil['banco_tipo_cuenta'] ?? '') === 'Ahorros' ? 'selected' : ''; ?>>Ahorros</option>
                                <option value="Corriente" <?php echo ($perfil['banco_tipo_cuenta'] ?? '') === 'Corriente' ? 'selected' : ''; ?>>Corriente</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="banco_numero_cuenta">Número de Cuenta <span class="requerido">*</span></label>
                            <input type="text" id="banco_numero_cuenta" name="banco_numero_cuenta" 
                                   value="<?php echo htmlspecialchars($perfil['banco_numero_cuenta'] ?? ''); ?>"
                                   data-autosave="true"
                                   class="autosave-field">
                        </div>
                    </div>
                </div>

                <!-- Sección 6: Días de Descanso -->
                <div class="card-seccion">
                    <h2 class="seccion-titulo">6️⃣ Días de Descanso</h2>
                    
                    <p class="seccion-descripcion">Selecciona los días de la semana en que prefieres descansar</p>
                    
                    <div class="dias-grid">
                        <?php foreach ($dias_semana as $dia): ?>
                        <label class="checkbox-card">
                            <input type="checkbox" name="dias_descanso[]" value="<?php echo $dia; ?>" 
                                   <?php echo in_array($dia, $dias_seleccionados) ? 'checked' : ''; ?>>
                            <span><?php echo $dia; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sección 7: Fotografías del Artista -->
                <div class="card-seccion">
                    <h2 class="seccion-titulo">7️⃣ Fotografías del Artista</h2>
                    
                    <div class="form-grid">
                        <!-- Foto de Perfil -->
                        <div class="form-group upload-group">
                            <label for="foto_perfil">Foto de Perfil <span class="requerido">*</span></label>
                            <input type="file" id="foto_perfil" name="foto_perfil" 
                                   accept="image/jpeg,image/png,image/gif" onchange="previsualizarImagen(this, 'preview_foto_perfil')">
                            <div class="preview-container clickeable" onclick="verArchivoModal('<?php echo !empty($perfil['foto_perfil']) ? '../../' . $perfil['foto_perfil'] : ''; ?>', 'Foto de Perfil')">
                                <img id="preview_foto_perfil" 
                                     src="<?php echo !empty($perfil['foto_perfil']) ? '../../' . $perfil['foto_perfil'] : ''; ?>" 
                                     alt="Vista previa" 
                                     title="Click para ver en tamaño completo"
                                     style="<?php echo !empty($perfil['foto_perfil']) ? '' : 'display:none'; ?>">
                            </div>
                        </div>

                        <!-- Foto con Cédula -->
                        <div class="form-group upload-group">
                            <label for="foto_con_cedula">Foto con Cédula en Mano <span class="requerido">*</span></label>
                            <input type="file" id="foto_con_cedula" name="foto_con_cedula" 
                                   accept="image/jpeg,image/png" onchange="previsualizarImagen(this, 'preview_foto_con_cedula')">
                            <div class="preview-container clickeable" onclick="verArchivoModal('<?php echo !empty($perfil['foto_con_cedula']) ? '../../' . $perfil['foto_con_cedula'] : ''; ?>', 'Foto con Cédula en Mano')">
                                <img id="preview_foto_con_cedula" 
                                     src="<?php echo !empty($perfil['foto_con_cedula']) ? '../../' . $perfil['foto_con_cedula'] : ''; ?>" 
                                     alt="Vista previa" 
                                     title="Click para ver en tamaño completo"
                                     style="<?php echo !empty($perfil['foto_con_cedula']) ? '' : 'display:none'; ?>">
                            </div>
                        </div>

                        <!-- Cédula Frente -->
                        <div class="form-group upload-group">
                            <label for="foto_cedula_frente">Cédula - Lado Frontal <span class="requerido">*</span></label>
                            <input type="file" id="foto_cedula_frente" name="foto_cedula_frente" 
                                   accept="image/jpeg,image/png,application/pdf" onchange="previsualizarImagen(this, 'preview_foto_cedula_frente')">
                            <div class="preview-container clickeable" onclick="verArchivoModal('<?php echo !empty($perfil['foto_cedula_frente']) ? '../../' . $perfil['foto_cedula_frente'] : ''; ?>', 'Cédula - Lado Frontal')">
                                <img id="preview_foto_cedula_frente" 
                                     src="<?php echo !empty($perfil['foto_cedula_frente']) ? '../../' . $perfil['foto_cedula_frente'] : ''; ?>" 
                                     alt="Vista previa" 
                                     title="Click para ver en tamaño completo"
                                     style="<?php echo !empty($perfil['foto_cedula_frente']) ? '' : 'display:none'; ?>">
                            </div>
                        </div>

                        <!-- Cédula Reverso -->
                        <div class="form-group upload-group">
                            <label for="foto_cedula_reverso">Cédula - Lado Reverso <span class="requerido">*</span></label>
                            <input type="file" id="foto_cedula_reverso" name="foto_cedula_reverso" 
                                   accept="image/jpeg,image/png,application/pdf" onchange="previsualizarImagen(this, 'preview_foto_cedula_reverso')">
                        <div class="preview-container clickeable" onclick="verArchivoModal('<?php echo !empty($perfil['foto_cedula_reverso']) ? '../../' . $perfil['foto_cedula_reverso'] : ''; ?>', 'Cédula - Lado Reverso')">
                            <img id="preview_foto_cedula_reverso" 
                                 src="<?php echo !empty($perfil['foto_cedula_reverso']) ? '../../' . $perfil['foto_cedula_reverso'] : ''; ?>" 
                                 alt="Vista previa" 
                                 title="Click para ver en tamaño completo"
                                 style="<?php echo !empty($perfil['foto_cedula_reverso']) ? '' : 'display:none'; ?>">
                        </div>
                    </div>
                </div>
            </div>

        <!-- Sección 8: Notas del Artista -->
        <div class="card-seccion">
            <h2 class="seccion-titulo">8️⃣ Notas del Artista</h2>
            
            <div class="form-group full-width">
                <label for="notas">Observaciones Personales</label>
                <textarea id="notas" name="notas" rows="5" 
                          placeholder="Espacio libre para notas, observaciones o información adicional relevante..."
                          data-autosave="true"
                          class="autosave-field"><?php echo htmlspecialchars($perfil['notas'] ?? ''); ?></textarea>
            </div>
        </div>
    </form>
</main>

<!-- Modal universal para mostrar archivos -->
<div id="certificadoModal" class="certificado-modal">
    <div class="certificado-modal-content">
        <button class="certificado-modal-close" onclick="cerrarCertificadoModal()">✕</button>
        <h3 id="certificadoModalTitle" style="text-align: center; margin-bottom: 15px; color: #882A57;"></h3>
        <div id="certificadoModalContent"></div>
    </div>
</div>

<style>
    /* Reset del body */
    body {
        display: block !important;
        height: auto !important;
        min-height: 100vh;
        overflow-y: auto !important;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
    }

        .dashboard-container {
            min-height: 100vh;
        }

        /* Encabezado de página */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #222;
            margin: 0 0 0.5rem 0;
        }

        .subtitle {
            color: #6c757d;
            margin: 0;
        }

        .btn-back {
            background: #6A1B1B;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-back:hover {
            background: #882A57;
        }

        /* Card de progreso */
        .card-progreso {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .progreso-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .progreso-header h3 {
            margin: 0 0 0.5rem 0;
            color: #222;
        }

        .progreso-header p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .progreso-circulo {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .progreso-circulo svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .progreso-bg {
            fill: none;
            stroke: #e9ecef;
            stroke-width: 8;
        }

        .progreso-fill {
            fill: none;
            stroke: #28a745;
            stroke-width: 8;
            stroke-dasharray: 251;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease;
        }

        .progreso-texto {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 700;
            color: #222;
        }

        .alerta-perfil {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
        }

        /* Mensajes */
        .mensaje {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .mensaje-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .mensaje-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        /* Secciones del formulario */
        .card-seccion {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .seccion-titulo {
            font-size: 1.3rem;
            color: #6A1B1B;
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .seccion-descripcion {
            color: #6c757d;
            margin-bottom: 1rem;
        }

        /* Grid de formularios */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 500;
            color: #222;
            margin-bottom: 0.5rem;
        }

        .requerido {
            color: #dc3545;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6A1B1B;
        }

        .form-group input[readonly] {
            background: #f8f9fa;
            cursor: not-allowed;
        }

        /* Días de descanso */
        .dias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .checkbox-card {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .checkbox-card:hover {
            border-color: #6A1B1B;
            background: #f8f9fa;
        }

        .checkbox-card.saving {
            background-color: #fff3cd !important;
            border-color: #ffc107 !important;
        }

        .checkbox-card.saved {
            background-color: #d4edda !important;
            border-color: #28a745 !important;
            animation: savedPulse 0.6s ease;
        }

        .checkbox-card.error {
            background-color: #f8d7da !important;
            border-color: #dc3545 !important;
        }

        .checkbox-card input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .checkbox-card input[type="checkbox"]:checked + span {
            color: #6A1B1B;
            font-weight: 600;
        }

        /* Upload de fotos */
        .upload-group {
            border: 2px dashed #ddd;
            padding: 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .upload-group.uploading {
            border-color: #ffc107;
            background-color: #fff3cd;
        }

        .upload-group.upload-success {
            border-color: #28a745;
            background-color: #d4edda;
            animation: savedPulse 0.6s ease;
        }

        .upload-group.upload-error {
            border-color: #dc3545;
            background-color: #f8d7da;
        }

        .preview-container {
            margin-top: 1rem;
            text-align: center;
            position: relative;
        }

        .preview-container img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            object-fit: cover;
        }

        .preview-container.clickeable {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .preview-container.clickeable:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .preview-container.clickeable img {
            transition: all 0.3s ease;
        }

        .preview-container.clickeable:hover img {
            opacity: 0.9;
        }

        .pdf-preview {
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pdf-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .pdf-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .pdf-preview p {
            margin: 5px 0;
            font-weight: 600;
            color: #333;
        }

        .pdf-preview small {
            color: #666;
            font-size: 12px;
        }

        .file-actual {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .file-actual a {
            color: #6A1B1B;
            text-decoration: none;
        }

        .file-actual a:hover {
            text-decoration: underline;
        }

        /* Botón de guardar */
        .form-actions {
            text-align: center;
            margin-top: 2rem;
        }

        .btn-guardar {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            padding: 1rem 3rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-guardar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(106, 27, 27, 0.3);
        }

        .nota-guardado {
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-main {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .progreso-header {
                flex-direction: column;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .dias-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Estilos para auto-guardado */
        .autosave-field {
            transition: all 0.3s ease;
        }

        .autosave-field.saving {
            background-color: #fff3cd !important;
            border-color: #ffc107 !important;
        }

        .autosave-field.saved {
            background-color: #d4edda !important;
            border-color: #28a745 !important;
            animation: savedPulse 0.6s ease;
        }

        .autosave-field.error {
            background-color: #f8d7da !important;
            border-color: #dc3545 !important;
        }

        @keyframes savedPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .autosave-indicator {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }

        .autosave-indicator.show {
            opacity: 1;
            transform: translateY(0);
        }

        .autosave-indicator.saving {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .autosave-indicator.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }

        .autosave-indicator.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        /* Estilos para select de bancos */
        #id_banco {
            font-weight: 500;
            padding-left: 12px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23666' viewBox='0 0 16 16'%3E%3Cpath d='M8 0a.5.5 0 0 1 .5.5V1h.5a.5.5 0 0 1 0 1h-.5v.5a.5.5 0 0 1-1 0V2h-.5a.5.5 0 0 1 0-1h.5V.5A.5.5 0 0 1 8 0zM4.5 4a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7zM2 5.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-.5V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7h-.5a.5.5 0 0 1-.5-.5v-1zm2.5 1.5v6.5h7V7h-7z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 8px center;
            background-size: 18px;
            padding-left: 36px;
        }

        #id_banco optgroup {
            font-weight: 600;
            font-style: normal;
            color: #666;
            background: #f8f9fa;
            padding: 8px 0;
        }

        #id_banco option {
            padding: 8px 12px;
            font-weight: 500;
        }

        /* Badge para tipo de banco */
        .banco-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
            font-size: 13px;
            color: #666;
        }

        .banco-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .banco-badge.banco {
            background: #e3f2fd;
            color: #1976d2;
        }

        .banco-badge.neobanco {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .banco-badge.cooperativa {
            background: #fff3e0;
            color: #f57c00;
        }

        /* Modal para certificado médico */
        .certificado-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.9);
            align-items: center;
            justify-content: center;
        }

        .certificado-modal-content {
            position: relative;
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 12px;
            max-width: 90%;
            max-height: 90vh;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .certificado-modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #fff;
            background: #882A57;
            border: none;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 10001;
            line-height: 1;
            padding: 0;
        }

        .certificado-modal-close:hover,
        .certificado-modal-close:focus {
            background: #6a1f44;
            transform: rotate(90deg);
        }

        #certificadoModalContent {
            text-align: center;
            padding: 10px;
        }
    </style>

    <script>
        // Indicador global de guardado
        let autosaveTimeout = null;
        let autosaveIndicator = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Crear indicador de auto-guardado
            autosaveIndicator = document.createElement('div');
            autosaveIndicator.className = 'autosave-indicator';
            document.body.appendChild(autosaveIndicator);

            // Agregar eventos a todos los campos con autosave
            const autosaveFields = document.querySelectorAll('.autosave-field');
            
            autosaveFields.forEach(field => {
                // Para inputs de texto, usar blur (cuando pierde el foco)
                if (field.type === 'text' || field.type === 'email' || field.type === 'tel') {
                    field.addEventListener('blur', function() {
                        autoSaveField(this);
                    });
                }
                
                // Para selects y dates, usar change (cuando cambia el valor)
                if (field.tagName === 'SELECT' || field.type === 'date') {
                    field.addEventListener('change', function() {
                        autoSaveField(this);
                    });
                }
                
                // Para textarea, usar blur (cuando pierde el foco)
                if (field.tagName === 'TEXTAREA') {
                    field.addEventListener('blur', function() {
                        autoSaveField(this);
                    });
                }
            });

            // Agregar eventos a checkboxes de días de descanso
            const diasCheckboxes = document.querySelectorAll('input[name="dias_descanso[]"]');
            diasCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Obtener el checkbox-card padre del checkbox que cambió
                    const checkboxCard = this.closest('.checkbox-card');
                    autoSaveDiasDescanso(checkboxCard);
                });
            });
        });

        function autoSaveField(field) {
            // Limpiar timeout anterior
            if (autosaveTimeout) {
                clearTimeout(autosaveTimeout);
            }

            // Marcar campo como guardando
            field.classList.remove('saved', 'error');
            field.classList.add('saving');
            showIndicator('Guardando...', 'saving');

            // Esperar 500ms antes de guardar (debounce)
            autosaveTimeout = setTimeout(() => {
                saveFieldData(field);
            }, 500);
        }

        function autoSaveDiasDescanso(checkboxCard) {
            // Limpiar timeout anterior
            if (autosaveTimeout) {
                clearTimeout(autosaveTimeout);
            }

            // Marcar solo el checkbox-card modificado como guardando
            checkboxCard.classList.remove('saved', 'error');
            checkboxCard.classList.add('saving');
            showIndicator('Guardando días...', 'saving');

            // Esperar 500ms antes de guardar (debounce)
            autosaveTimeout = setTimeout(() => {
                saveDiasDescanso(checkboxCard);
            }, 500);
        }

        function saveDiasDescanso(checkboxCard) {
            // Obtener todos los checkboxes seleccionados
            const checkboxes = document.querySelectorAll('input[name="dias_descanso[]"]:checked');
            const diasSeleccionados = Array.from(checkboxes).map(cb => cb.value);

            // Crear FormData
            const formData = new FormData();
            formData.append('dias_descanso', JSON.stringify(diasSeleccionados));

            // Enviar via AJAX
            fetch('../../controllers/PerfilAutosaveController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Marcar como guardado exitosamente solo el card modificado
                    checkboxCard.classList.remove('saving', 'error');
                    checkboxCard.classList.add('saved');
                    showIndicator('✓ Guardado', 'success');

                    // Remover clase después de 2 segundos
                    setTimeout(() => {
                        checkboxCard.classList.remove('saved');
                    }, 2000);

                    // Actualizar progreso si viene en la respuesta
                    if (data.progreso !== undefined) {
                        updateProgress(data.progreso);
                    }
                } else {
                    // Error al guardar
                    checkboxCard.classList.remove('saving', 'saved');
                    checkboxCard.classList.add('error');
                    showIndicator('✗ Error: ' + data.message, 'error');

                    setTimeout(() => {
                        checkboxCard.classList.remove('error');
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                checkboxCard.classList.remove('saving', 'saved');
                checkboxCard.classList.add('error');
                showIndicator('✗ Error de conexión', 'error');

                setTimeout(() => {
                    checkboxCard.classList.remove('error');
                }, 3000);
            });
        }

        function saveFieldData(field) {
            const fieldName = field.name;
            const fieldValue = field.value;

            // Crear FormData con el campo específico
            const formData = new FormData();
            formData.append(fieldName, fieldValue);

            // Enviar via AJAX al endpoint de autosave
            fetch('../../controllers/PerfilAutosaveController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Marcar como guardado exitosamente
                    field.classList.remove('saving', 'error');
                    field.classList.add('saved');
                    showIndicator('✓ Guardado', 'success');

                    // Remover clase después de 2 segundos
                    setTimeout(() => {
                        field.classList.remove('saved');
                    }, 2000);

                    // Actualizar progreso si viene en la respuesta
                    if (data.progreso !== undefined) {
                        updateProgress(data.progreso);
                    }
                } else {
                    // Error al guardar
                    field.classList.remove('saving', 'saved');
                    field.classList.add('error');
                    showIndicator('✗ Error: ' + data.message, 'error');

                    setTimeout(() => {
                        field.classList.remove('error');
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                field.classList.remove('saving', 'saved');
                field.classList.add('error');
                showIndicator('✗ Error de conexión', 'error');

                setTimeout(() => {
                    field.classList.remove('error');
                }, 3000);
            });
        }

        function showIndicator(message, type) {
            autosaveIndicator.textContent = message;
            autosaveIndicator.className = 'autosave-indicator show ' + type;

            // Ocultar después de 2 segundos (excepto si es error, entonces 3)
            const hideDelay = type === 'error' ? 3000 : 2000;
            setTimeout(() => {
                autosaveIndicator.classList.remove('show');
            }, hideDelay);
        }

        function updateProgress(progreso) {
            const progressBar = document.querySelector('.progreso-barra-relleno');
            const progressText = document.querySelector('.progreso-numero');
            
            if (progressBar && progressText) {
                progressBar.style.width = progreso + '%';
                progressText.textContent = progreso + '%';
            }
        }

        /**
         * Previsualizar imagen antes de subirla Y subirla automáticamente
         */
        function previsualizarImagen(input, previewId) {
            const preview = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
                
                // Subir archivo automáticamente
                subirArchivo(input);
            }
        }

        /**
         * Subir archivo vía AJAX
         */
        function subirArchivo(input) {
            const file = input.files[0];
            if (!file) return;

            // Obtener el contenedor del campo para mostrar feedback
            const uploadGroup = input.closest('.upload-group') || input.closest('.form-group');
            
            // Mostrar indicador de subida
            uploadGroup.classList.add('uploading');
            showIndicator('📤 Subiendo archivo...', 'saving');

            // Crear FormData con el archivo
            const formData = new FormData();
            formData.append(input.name, file);

            // Enviar vía AJAX
            fetch('../../controllers/PerfilUploadController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                uploadGroup.classList.remove('uploading');
                
                if (data.success) {
                    uploadGroup.classList.add('upload-success');
                    showIndicator('✓ Archivo guardado', 'success');

                    // Remover clase después de 2 segundos
                    setTimeout(() => {
                        uploadGroup.classList.remove('upload-success');
                    }, 2000);

                    // Actualizar progreso
                    if (data.progreso !== undefined) {
                        updateProgress(data.progreso);
                    }
                } else {
                    uploadGroup.classList.add('upload-error');
                    showIndicator('✗ Error: ' + data.message, 'error');

                    setTimeout(() => {
                        uploadGroup.classList.remove('upload-error');
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                uploadGroup.classList.remove('uploading');
                uploadGroup.classList.add('upload-error');
                showIndicator('✗ Error al subir archivo', 'error');

                setTimeout(() => {
                    uploadGroup.classList.remove('upload-error');
                }, 3000);
            });
        }

        /**
         * Confirmación antes de enviar el formulario (solo para el botón manual)
         */
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.form-perfil');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Si no es autosave, pedir confirmación
                    if (!e.submitter || !e.submitter.hasAttribute('data-autosave')) {
                        const confirmacion = confirm('¿Estás seguro de guardar los cambios en tu perfil?');
                        if (!confirmacion) {
                            e.preventDefault();
                        }
                    }
                });
            }

            // Listener para mostrar info del banco seleccionado
            const selectBanco = document.getElementById('id_banco');
            if (selectBanco) {
                selectBanco.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        const color = selectedOption.dataset.color || '#888888';
                        const codigo = selectedOption.dataset.codigo || '';
                        
                        // Cambiar color del borde del select
                        this.style.borderLeftColor = color;
                        this.style.borderLeftWidth = '4px';
                    } else {
                        this.style.borderLeftColor = '';
                        this.style.borderLeftWidth = '';
                    }
                });

                // Aplicar color inicial si hay banco seleccionado
                if (selectBanco.value) {
                    const selectedOption = selectBanco.options[selectBanco.selectedIndex];
                    const color = selectedOption.dataset.color || '#888888';
                    selectBanco.style.borderLeftColor = color;
                    selectBanco.style.borderLeftWidth = '4px';
                }
            }
        });

        /**
         * Ver archivo (imagen o PDF) en modal
         */
        function verArchivoModal(rutaArchivo, titulo) {
            // Validar que hay un archivo
            if (!rutaArchivo || rutaArchivo === '') {
                alert('No hay archivo disponible para mostrar');
                return;
            }

            const modal = document.getElementById('certificadoModal');
            const modalTitle = document.getElementById('certificadoModalTitle');
            const modalContent = document.getElementById('certificadoModalContent');
            
            // Establecer título
            modalTitle.textContent = titulo || 'Archivo';
            
            // Detectar si es PDF o imagen
            const extension = rutaArchivo.split('.').pop().toLowerCase();
            
            if (extension === 'pdf') {
                // Mostrar PDF en iframe
                modalContent.innerHTML = `<iframe src="${rutaArchivo}" style="width: 100%; height: 80vh; border: none;"></iframe>`;
            } else {
                // Mostrar imagen
                modalContent.innerHTML = `<img src="${rutaArchivo}" alt="${titulo}" style="max-width: 100%; max-height: 80vh; display: block; margin: 0 auto; border-radius: 8px;">`;
            }
            
            modal.style.display = 'flex';
        }

        /**
         * Cerrar modal del certificado
         */
        function cerrarCertificadoModal() {
            const modal = document.getElementById('certificadoModal');
            modal.style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            const modal = document.getElementById('certificadoModal');
            if (event.target === modal) {
                cerrarCertificadoModal();
            }
        }

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarCertificadoModal();
            }
        });
    </script>

<?php
// Capturar el contenido generado
$content = ob_get_clean();

// Incluir el master layout
include __DIR__ . '/../layouts/master.php';
?>