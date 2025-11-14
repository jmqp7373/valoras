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

// Valores por defecto
$perfil = $perfil ?: [];
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$dias_seleccionados = $perfil['dias_descanso'] ?? [];
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
                           value="<?php echo htmlspecialchars($perfil['nombres'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos <span class="requerido">*</span></label>
                    <input type="text" id="apellidos" name="apellidos" 
                           value="<?php echo htmlspecialchars($perfil['apellidos'] ?? ''); ?>" required>
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
                           value="<?php echo htmlspecialchars($perfil['celular'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="fecha_de_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_de_nacimiento" name="fecha_de_nacimiento" 
                           value="<?php echo htmlspecialchars($perfil['fecha_de_nacimiento'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="tipo_sangre">Tipo de Sangre</label>
                    <select id="tipo_sangre" name="tipo_sangre">
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
                           value="<?php echo htmlspecialchars($perfil['ciudad'] ?? ''); ?>">
                </div>

                <div class="form-group full-width">
                    <label for="direccion">Dirección de Residencia</label>
                    <input type="text" id="direccion" name="direccion" 
                           value="<?php echo htmlspecialchars($perfil['direccion'] ?? ''); ?>">
                </div>

                <div class="form-group full-width">
                    <label for="email">Correo Electrónico <span class="requerido">*</span></label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($perfil['email'] ?? ''); ?>" required>
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
                                   value="<?php echo htmlspecialchars($perfil['contacto_emergencia_nombre'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contacto_emergencia_parentesco">Parentesco</label>
                            <input type="text" id="contacto_emergencia_parentesco" name="contacto_emergencia_parentesco" 
                                   value="<?php echo htmlspecialchars($perfil['contacto_emergencia_parentesco'] ?? ''); ?>" 
                                   placeholder="Ej: Madre, Hermano, Esposo/a">
                        </div>

                        <div class="form-group full-width">
                            <label for="contacto_emergencia_telefono">Teléfono o Celular <span class="requerido">*</span></label>
                            <input type="tel" id="contacto_emergencia_telefono" name="contacto_emergencia_telefono" 
                                   value="<?php echo htmlspecialchars($perfil['contacto_emergencia_telefono'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Salud y Condiciones Médicas -->
                <div class="card-seccion">
                    <h2 class="seccion-titulo">3️⃣ Salud y Condiciones Médicas</h2>
                    
                    <div class="form-group full-width">
                        <label for="alergias">Alergias o Condiciones Especiales</label>
                        <textarea id="alergias" name="alergias" rows="4" 
                                  placeholder="Describe aquí cualquier alergia, condición médica o tratamiento actual..."><?php echo htmlspecialchars($perfil['alergias'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="certificado_medico">Certificado Médico (PDF, JPG, PNG)</label>
                        <input type="file" id="certificado_medico" name="certificado_medico" 
                               accept=".pdf,.jpg,.jpeg,.png">
                        <?php if (!empty($perfil['certificado_medico'])): ?>
                        <p class="file-actual">
                            📄 Archivo actual: <a href="../../<?php echo htmlspecialchars($perfil['certificado_medico']); ?>" target="_blank">Ver certificado</a>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sección 4: Estudio al que pertenece -->
                <div class="card-seccion">
                    <h2 class="seccion-titulo">4️⃣ Estudio al que Pertenece</h2>
                    
                    <div class="form-group">
                        <label for="id_estudio">Seleccionar Estudio</label>
                        <select id="id_estudio" name="id_estudio">
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
                            <label for="banco_nombre">Nombre del Banco <span class="requerido">*</span></label>
                            <input type="text" id="banco_nombre" name="banco_nombre" 
                                   value="<?php echo htmlspecialchars($perfil['banco_nombre'] ?? ''); ?>" 
                                   placeholder="Ej: Bancolombia, Davivienda">
                        </div>

                        <div class="form-group">
                            <label for="banco_tipo_cuenta">Tipo de Cuenta</label>
                            <select id="banco_tipo_cuenta" name="banco_tipo_cuenta">
                                <option value="">Seleccionar...</option>
                                <option value="Ahorros" <?php echo ($perfil['banco_tipo_cuenta'] ?? '') === 'Ahorros' ? 'selected' : ''; ?>>Ahorros</option>
                                <option value="Corriente" <?php echo ($perfil['banco_tipo_cuenta'] ?? '') === 'Corriente' ? 'selected' : ''; ?>>Corriente</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="banco_numero_cuenta">Número de Cuenta <span class="requerido">*</span></label>
                            <input type="text" id="banco_numero_cuenta" name="banco_numero_cuenta" 
                                   value="<?php echo htmlspecialchars($perfil['banco_numero_cuenta'] ?? ''); ?>">
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
                            <div class="preview-container">
                                <img id="preview_foto_perfil" 
                                     src="<?php echo !empty($perfil['foto_perfil']) ? '../../' . $perfil['foto_perfil'] : ''; ?>" 
                                     alt="Vista previa" 
                                     style="<?php echo !empty($perfil['foto_perfil']) ? '' : 'display:none'; ?>">
                            </div>
                        </div>

                        <!-- Foto con Cédula -->
                        <div class="form-group upload-group">
                            <label for="foto_con_cedula">Foto con Cédula en Mano <span class="requerido">*</span></label>
                            <input type="file" id="foto_con_cedula" name="foto_con_cedula" 
                                   accept="image/jpeg,image/png" onchange="previsualizarImagen(this, 'preview_foto_con_cedula')">
                            <div class="preview-container">
                                <img id="preview_foto_con_cedula" 
                                     src="<?php echo !empty($perfil['foto_con_cedula']) ? '../../' . $perfil['foto_con_cedula'] : ''; ?>" 
                                     alt="Vista previa" 
                                     style="<?php echo !empty($perfil['foto_con_cedula']) ? '' : 'display:none'; ?>">
                            </div>
                        </div>

                        <!-- Cédula Frente -->
                        <div class="form-group upload-group">
                            <label for="foto_cedula_frente">Cédula - Lado Frontal <span class="requerido">*</span></label>
                            <input type="file" id="foto_cedula_frente" name="foto_cedula_frente" 
                                   accept="image/jpeg,image/png,application/pdf" onchange="previsualizarImagen(this, 'preview_foto_cedula_frente')">
                            <div class="preview-container">
                                <img id="preview_foto_cedula_frente" 
                                     src="<?php echo !empty($perfil['foto_cedula_frente']) ? '../../' . $perfil['foto_cedula_frente'] : ''; ?>" 
                                     alt="Vista previa" 
                                     style="<?php echo !empty($perfil['foto_cedula_frente']) ? '' : 'display:none'; ?>">
                            </div>
                        </div>

                        <!-- Cédula Reverso -->
                        <div class="form-group upload-group">
                            <label for="foto_cedula_reverso">Cédula - Lado Reverso <span class="requerido">*</span></label>
                            <input type="file" id="foto_cedula_reverso" name="foto_cedula_reverso" 
                                   accept="image/jpeg,image/png,application/pdf" onchange="previsualizarImagen(this, 'preview_foto_cedula_reverso')">
                        <div class="preview-container">
                            <img id="preview_foto_cedula_reverso" 
                                 src="<?php echo !empty($perfil['foto_cedula_reverso']) ? '../../' . $perfil['foto_cedula_reverso'] : ''; ?>" 
                                 alt="Vista previa" 
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
                          placeholder="Espacio libre para notas, observaciones o información adicional relevante..."><?php echo htmlspecialchars($perfil['notas'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Botón de Guardar -->
        <div class="form-actions">
            <button type="submit" class="btn-guardar">
                💾 Guardar Cambios
            </button>
        </div>
    </form>
</main>

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
        }

        .preview-container {
            margin-top: 1rem;
            text-align: center;
        }

        .preview-container img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            object-fit: cover;
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
    </style>

    <script>
        /**
         * Previsualizar imagen antes de subirla
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
            }
        }

        /**
         * Confirmación antes de enviar el formulario
         */
        document.querySelector('.form-perfil').addEventListener('submit', function(e) {
            const confirmacion = confirm('¿Estás seguro de guardar los cambios en tu perfil?');
            if (!confirmacion) {
                e.preventDefault();
            }
        });
    </script>

<?php
// Capturar el contenido generado
$content = ob_get_clean();

// Incluir el master layout
include __DIR__ . '/../layouts/master.php';
?>