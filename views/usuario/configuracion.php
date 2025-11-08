<?php
/**
 * Valora.vip - Configuración de Usuario
 * Página para administrar preferencias y configuraciones de la cuenta
 */

require_once __DIR__ . '/../../config/database.php';

startSessionSafely();

// Verificar autenticación
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Variables para el header
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

// Mensaje de confirmación
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Valora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <?php
        $logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
        $logout_path = '../../controllers/login/logout.php';
        $profile_path = '../../views/usuario/miPerfil.php';
        $home_path = '../../index.php';
        $settings_path = '../../views/usuario/configuracion.php';
        include '../../components/header/header.php';
        ?>

        <main class="dashboard-main">
            <!-- Encabezado -->
            <div class="page-header">
                <div>
                    <h1>⚙️ Configuración</h1>
                    <p class="subtitle">Administra tus preferencias y configuraciones de cuenta</p>
                </div>
                <a href="../../index.php" class="btn-back">← Volver al Dashboard</a>
            </div>

            <!-- Mensaje de confirmación/error -->
            <?php if ($mensaje): ?>
            <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <!-- Grid de secciones de configuración -->
            <div class="settings-grid">
                
                <!-- Sección: Seguridad -->
                <div class="card-setting">
                    <div class="setting-icon">🔒</div>
                    <h2>Seguridad</h2>
                    <p>Gestiona la seguridad de tu cuenta</p>
                    <ul class="setting-options">
                        <li>
                            <a href="#cambiar-password" class="setting-link" onclick="mostrarSeccion('password')">
                                <span>Cambiar Contraseña</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="#verificacion" class="setting-link">
                                <span>Verificación en dos pasos</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sesiones" class="setting-link">
                                <span>Sesiones activas</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Sección: Notificaciones -->
                <div class="card-setting">
                    <div class="setting-icon">🔔</div>
                    <h2>Notificaciones</h2>
                    <p>Configura cómo quieres recibir notificaciones</p>
                    <ul class="setting-options">
                        <li>
                            <a href="#email-notif" class="setting-link">
                                <span>Notificaciones por email</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sms-notif" class="setting-link">
                                <span>Notificaciones por SMS</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#push-notif" class="setting-link">
                                <span>Notificaciones push</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Sección: Privacidad -->
                <div class="card-setting">
                    <div class="setting-icon">👁️</div>
                    <h2>Privacidad</h2>
                    <p>Controla quién puede ver tu información</p>
                    <ul class="setting-options">
                        <li>
                            <a href="#perfil-publico" class="setting-link">
                                <span>Perfil público</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#datos-compartidos" class="setting-link">
                                <span>Datos compartidos</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#historial" class="setting-link">
                                <span>Historial de actividad</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Sección: Cuenta -->
                <div class="card-setting">
                    <div class="setting-icon">👤</div>
                    <h2>Cuenta</h2>
                    <p>Administra la información de tu cuenta</p>
                    <ul class="setting-options">
                        <li>
                            <a href="<?php echo $profile_path; ?>" class="setting-link">
                                <span>Editar perfil</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="#descargar-datos" class="setting-link">
                                <span>Descargar mis datos</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#eliminar-cuenta" class="setting-link danger">
                                <span>Eliminar cuenta</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Sección: Apariencia -->
                <div class="card-setting">
                    <div class="setting-icon">🎨</div>
                    <h2>Apariencia</h2>
                    <p>Personaliza la interfaz a tu gusto</p>
                    <ul class="setting-options">
                        <li>
                            <a href="#tema" class="setting-link">
                                <span>Tema (Claro/Oscuro)</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#idioma" class="setting-link">
                                <span>Idioma</span>
                                <span class="badge-current">Español</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tamaño-texto" class="setting-link">
                                <span>Tamaño de texto</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Sección: Ayuda -->
                <div class="card-setting">
                    <div class="setting-icon">💬</div>
                    <h2>Ayuda y Soporte</h2>
                    <p>Obtén ayuda cuando la necesites</p>
                    <ul class="setting-options">
                        <li>
                            <a href="#faq" class="setting-link">
                                <span>Preguntas frecuentes</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#contacto" class="setting-link">
                                <span>Contactar soporte</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                        <li>
                            <a href="#tutoriales" class="setting-link">
                                <span>Tutoriales</span>
                                <span class="badge-soon">Próximamente</span>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Modal/Sección de Cambiar Contraseña -->
            <div id="modal-password" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>🔒 Cambiar Contraseña</h2>
                        <button onclick="cerrarModal()" class="btn-close">×</button>
                    </div>
                    <form method="POST" action="../../controllers/PerfilController.php" class="form-password">
                        <input type="hidden" name="action" value="cambiar_password">
                        
                        <div class="form-group">
                            <label for="password_actual">Contraseña Actual</label>
                            <input type="password" id="password_actual" name="password_actual" required>
                        </div>

                        <div class="form-group">
                            <label for="password_nueva">Nueva Contraseña</label>
                            <input type="password" id="password_nueva" name="password_nueva" required minlength="8">
                            <small>Mínimo 8 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                            <input type="password" id="password_confirmar" name="password_confirmar" required>
                        </div>

                        <div class="modal-actions">
                            <button type="button" onclick="cerrarModal()" class="btn-secondary">Cancelar</button>
                            <button type="submit" class="btn-primary">Cambiar Contraseña</button>
                        </div>
                    </form>
                </div>
            </div>

        </main>

        <?php
        $base_path = '../../';
        include '../../components/footer.php';
        ?>
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

        .dashboard-main {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Encabezado */
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

        /* Grid de configuraciones */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        /* Card de configuración */
        .card-setting {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card-setting:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .setting-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .card-setting h2 {
            font-size: 1.3rem;
            color: #6A1B1B;
            margin: 0 0 0.5rem 0;
        }

        .card-setting p {
            color: #6c757d;
            margin: 0 0 1.5rem 0;
            font-size: 0.9rem;
        }

        /* Opciones de configuración */
        .setting-options {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .setting-options li {
            border-top: 1px solid #f0f0f0;
            padding: 0;
        }

        .setting-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            color: #222;
            text-decoration: none;
            transition: color 0.3s;
        }

        .setting-link:hover {
            color: #6A1B1B;
        }

        .setting-link.danger:hover {
            color: #dc3545;
        }

        .setting-link svg {
            color: #6c757d;
        }

        /* Badges */
        .badge-soon {
            background: #ffc107;
            color: #856404;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-current {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            margin: 0;
            color: #222;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 2rem;
            color: #6c757d;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
        }

        .btn-close:hover {
            color: #dc3545;
        }

        /* Formulario */
        .form-password .form-group {
            margin-bottom: 1.5rem;
        }

        .form-password label {
            display: block;
            font-weight: 500;
            color: #222;
            margin-bottom: 0.5rem;
        }

        .form-password input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }

        .form-password input:focus {
            outline: none;
            border-color: #6A1B1B;
        }

        .form-password small {
            display: block;
            color: #6c757d;
            margin-top: 0.25rem;
            font-size: 0.85rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3);
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

            .settings-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                padding: 1.5rem;
            }
        }
    </style>

    <script>
        function mostrarSeccion(seccion) {
            if (seccion === 'password') {
                document.getElementById('modal-password').style.display = 'flex';
            }
        }

        function cerrarModal() {
            document.getElementById('modal-password').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modal-password')?.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });

        // Validar que las contraseñas coincidan
        document.querySelector('.form-password')?.addEventListener('submit', function(e) {
            const nueva = document.getElementById('password_nueva').value;
            const confirmar = document.getElementById('password_confirmar').value;
            
            if (nueva !== confirmar) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
        });
    </script>
</body>
</html>
