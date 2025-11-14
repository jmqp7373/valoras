<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../../controllers/login/AuthController.php';

$authController = new AuthController();
$registerResult = null;

// Variables para mantener los valores del formulario en caso de error
$cedula = '';
$nombres = '';
$apellidos = '';
$username = '';
$country_code = '+57';
$phone_number = '';

// Verificar si ya está logueado
startSessionSafely();
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../../index.php');
    exit();
}

// Procesar el formulario de registro
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capturar los valores para mantenerlos en caso de error
    $cedula = $_POST['Numero_de_cedula'] ?? '';
    $nombres = $_POST['first_name'] ?? '';
    $apellidos = $_POST['last_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $country_code = $_POST['country_code'] ?? '+57';
    $phone_number = $_POST['phone_number'] ?? '';
    
    $registerResult = $authController->register();
    if($registerResult['success']) {
        // Limpiar variables en caso de éxito para evitar mostrar datos
        $cedula = $nombres = $apellidos = $username = $phone_number = '';
        $country_code = '+57';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body class="auth-layout">
    <div class="login-container">
        <!-- Incluir el logo de Valora ubicado en assets/images/logo_valoras.png -->
        <img src="../../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>Crear Cuenta</h2>
        
        <?php if($registerResult): ?>
            <?php if($registerResult['success']): ?>
                <div class="alert alert-success" style="background-color: #efe; border: 1px solid #cfc; color: #3c3; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($registerResult['message']); ?>
                    <script>
                        setTimeout(function() {
                            window.location.href = 'first_password.php';
                        }, 5000);
                    </script>
                </div>
            <?php else: ?>
                <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($registerResult['message']); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="form-group">
                <!-- Campo de identificación con label y placeholder más descriptivos -->
                <label for="Numero_de_cedula">Número de identificación (Cédula):</label>
                <input type="text" id="Numero_de_cedula" placeholder="Número de identificación" name="Numero_de_cedula" value="<?php echo htmlspecialchars($cedula); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">Nombre:</label>
                <input type="text" id="first_name" placeholder="Nombre" name="first_name" value="<?php echo htmlspecialchars($nombres); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Apellidos:</label>
                <input type="text" id="last_name" placeholder="Apellidos" name="last_name" value="<?php echo htmlspecialchars($apellidos); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Nombre de Usuario:</label>
                <div class="username-container" style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="username" placeholder="Elige tu nombre de usuario único" name="username" value="<?php echo htmlspecialchars($username); ?>" style="flex: 1; background-color: #f5f5f5;" readonly required>
                    <a href="registranteUserAvailavilitySelect.php" 
                       class="ai-button"
                       style="padding: 12px 16px; background: linear-gradient(135deg, #ee6f92, #882A57); color: white; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500; white-space: nowrap; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(238, 111, 146, 0.3)'"
                       onmouseout="this.style.transform='none'; this.style.boxShadow='none'"
                       title="Ir al asistente IA para cambiar tu nombre de usuario">
                        <span style="font-size: 16px;">🤖</span>
                        <span>Cambiar Nombre con IA</span>
                    </a>
                </div>
                <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                    💡 Usa "Cambiar Nombre con IA" para comenzar el proceso guiado de 3 pasos y encontrar tu nombre perfecto
                </small>
            </div>
            
            <div class="form-group">
                <label for="phone_number">Número de Celular:</label>
                <div style="display: flex; gap: 8px;">
                    <!-- Select oculto para envío del formulario -->
                    <input type="hidden" id="country_code" name="country_code" value="<?php echo htmlspecialchars($country_code); ?>" required>
                    
                    <!-- Selector personalizado -->
                    <div class="custom-select" style="position: relative; width: 110px;">
                        <div class="select-display" style="padding: 14px 12px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease;">
                            <div class="selected-option" style="display: flex; align-items: center;">
                                <img src="../../assets/images/flags/co.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                <span><?php echo htmlspecialchars($country_code); ?></span>
                            </div>
                            <span class="dropdown-arrow" style="transform: rotate(0deg); transition: transform 0.3s;">▼</span>
                        </div>
                        
                        <div class="select-dropdown" style="position: absolute; top: 100%; left: 0; width: 200px; background: white; border: 1px solid #ee6f92; border-top: none; border-radius: 0 0 12px 12px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none;">
                            <!-- Colombia siempre de primero -->
                            <div class="option" data-value="+57" data-country="co" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/co.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                <span>Colombia (+57)</span>
                            </div>
                            
                            <!-- Resto de países en orden alfabético -->
                            <div class="option" data-value="+54" data-country="ar" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/ar.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Argentina">
                                <span>Argentina (+54)</span>
                            </div>
                            <div class="option" data-value="+56" data-country="cl" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/cl.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Chile">
                                <span>Chile (+56)</span>
                            </div>
                            <div class="option" data-value="+593" data-country="ec" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/ec.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Ecuador">
                                <span>Ecuador (+593)</span>
                            </div>
                            <div class="option" data-value="+1" data-country="us" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/us.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Estados Unidos">
                                <span>Estados Unidos (+1)</span>
                            </div>
                            <div class="option" data-value="+52" data-country="mx" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/mx.png" style="width: 20px; height: auto; margin-right: 8px;" alt="México">
                                <span>México (+52)</span>
                            </div>
                            <div class="option" data-value="+51" data-country="pe" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/pe.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Perú">
                                <span>Perú (+51)</span>
                            </div>
                            <div class="option" data-value="+58" data-country="ve" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../../assets/images/flags/ve.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Venezuela">
                                <span>Venezuela (+58)</span>
                            </div>
                        </div>
                    </div>
                    <input type="tel" id="phone_number" placeholder="Número de celular" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" inputmode="numeric" pattern="\d{10}" maxlength="10" style="flex: 1; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; transition: all 0.3s ease;" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
        
        <div style="text-align: center; margin-top: 25px; color: #666; font-size: 14px;">
            <div style="margin-bottom: 12px;">
                ¿Ya tienes una cuenta? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión</a>
            </div>
            <div>
                <a href="password_reset.php" style="color: #882A57; text-decoration: none; font-weight: 500;">¿Olvidaste tu contraseña?</a>
            </div>
        </div>
        
    </div>

    <style>
        .custom-select .option:hover {
            background-color: #f8f9fa;
        }
        
        .custom-select .option.selected {
            background-color: #e9ecef;
        }
        
        .select-display:hover {
            border-color: #d63384;
        }
        
        .select-display.open {
            border-radius: 12px 12px 0 0;
            border-bottom-color: #ee6f92;
        }
        
        .select-display.open .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .select-dropdown {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .custom-select .option img {
            flex-shrink: 0;
        }
        
        /* Estilos responsivos para móvil */
        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
                margin: 10px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                font-size: 14px;
                font-weight: 600;
                margin-bottom: 8px;
                display: block;
            }
            
            /* Mejorar el campo nombre de usuario en móvil */
            .username-container {
                display: flex !important;
                flex-direction: column !important;
                gap: 12px !important;
                align-items: stretch !important;
            }
            
            #username {
                width: 100% !important;
                padding: 14px 16px !important;
                font-size: 16px !important;
                border-radius: 12px !important;
                border: 2px solid #ee6f92 !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }
            
            /* Botón "Cambiar Nombre con IA" mejorado para móvil */
            .ai-button {
                padding: 14px 20px !important;
                font-size: 14px !important;
                text-align: center !important;
                border-radius: 12px !important;
                width: 100% !important;
                box-sizing: border-box !important;
                justify-content: center !important;
                margin: 0 !important;
            }
            
            .ai-button span:last-child {
                font-size: 14px !important;
            }
            
            /* Selector de país mejorado para móvil */
            .form-group div[style*="display: flex; gap: 8px;"] {
                flex-direction: column !important;
                gap: 12px !important;
            }
            
            .custom-select {
                width: 100% !important;
            }
            
            .select-display {
                padding: 14px 16px !important;
                font-size: 16px !important;
            }
            
            .select-dropdown {
                width: 100% !important;
            }
            
            #phone_number {
                padding: 14px 16px !important;
                font-size: 16px !important;
            }
            
            /* Mejorar todos los inputs del formulario */
            input[type="text"], input[type="email"], input[type="password"] {
                padding: 14px 16px !important;
                font-size: 16px !important;
                border-radius: 12px !important;
                border: 2px solid #ee6f92 !important;
            }
            
            /* Botón de registro mejorado */
            button[type="submit"] {
                padding: 16px 24px !important;
                font-size: 16px !important;
                margin-top: 25px !important;
            }
            
            /* Mejorar el texto de ayuda */
            .form-group small {
                font-size: 12px !important;
                line-height: 1.4 !important;
                margin-top: 8px !important;
            }
        }
        
        /* Estilos para móviles muy pequeños */
        @media (max-width: 480px) {
            .login-container {
                padding: 10px;
                margin: 5px;
            }
            
            .logo {
                width: 120px !important;
                margin-bottom: 15px !important;
            }
            
            h2 {
                font-size: 22px !important;
                margin-bottom: 20px !important;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            input[type="text"], input[type="email"], input[type="password"] {
                padding: 12px 14px !important;
                font-size: 15px !important;
            }
            
            .ai-button {
                padding: 12px 16px !important;
                font-size: 13px !important;
            }
            
            .select-display {
                padding: 12px 14px !important;
                font-size: 15px !important;
            }
            
            #phone_number {
                padding: 12px 14px !important;
                font-size: 15px !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customSelect = document.querySelector('.custom-select');
            const selectDisplay = customSelect.querySelector('.select-display');
            const dropdown = customSelect.querySelector('.select-dropdown');
            const hiddenInput = document.getElementById('country_code');
            const selectedOption = selectDisplay.querySelector('.selected-option');
            const options = dropdown.querySelectorAll('.option');
            
            // Inicializar selector con el valor previo del formulario (si existe)
            const currentCountryCode = hiddenInput.value || '+57';
            
            // Phone number validation and length management  
            const phoneInput = document.getElementById('phone_number');
            
            // Mapeo de longitud de teléfonos por país
            const phoneLengthMap = {
                '+57': 10,  // Colombia
                '+52': 10,  // México
                '+1': 10,   // Estados Unidos
                '+56': 9,   // Chile
                '+51': 9,   // Perú
                '+58': 11,  // Venezuela
                '+54': 10,  // Argentina
                '+593': 9   // Ecuador
            };
            
            // Mapeo de nombres de países para placeholders amigables
            const countryNamesMap = {
                '+57': 'Colombia',
                '+52': 'México', 
                '+1': 'Estados Unidos',
                '+56': 'Chile',
                '+51': 'Perú',
                '+58': 'Venezuela',
                '+54': 'Argentina',
                '+593': 'Ecuador'
            };
            
            // Función para actualizar maxlength y placeholder según el país seleccionado
            function updatePhoneMaxLength(countryCode) {
                const maxLength = phoneLengthMap[countryCode] || 10;
                const countryName = countryNamesMap[countryCode] || 'País';
                
                // Actualizar atributos del campo de teléfono
                phoneInput.setAttribute('maxlength', maxLength);
                phoneInput.setAttribute('pattern', `\\d{${maxLength}}`);
                
                // Actualizar placeholder dinámicamente con información del país
                phoneInput.setAttribute('placeholder', `${maxLength} dígitos (${countryName})`);
                
                // Truncar valor actual si excede la nueva longitud máxima
                if (phoneInput.value.length > maxLength) {
                    phoneInput.value = phoneInput.value.substring(0, maxLength);
                }
            }
            
            initializeCountrySelector(currentCountryCode);
            
            function initializeCountrySelector(countryCode) {
                const option = dropdown.querySelector(`[data-value="${countryCode}"]`);
                if (option) {
                    const img = option.querySelector('img').cloneNode(true);
                    const text = countryCode;
                    
                    // Actualizar display
                    selectedOption.innerHTML = '';
                    selectedOption.appendChild(img);
                    selectedOption.appendChild(document.createTextNode(text));
                    
                    // Actualizar input hidden
                    hiddenInput.value = countryCode;
                    
                    // Actualizar configuración del campo de teléfono
                    updatePhoneMaxLength(countryCode);
                }
            }
            
            // Toggle dropdown
            selectDisplay.addEventListener('click', function() {
                const isOpen = dropdown.style.display === 'block';
                
                if (isOpen) {
                    dropdown.style.display = 'none';
                    selectDisplay.classList.remove('open');
                } else {
                    dropdown.style.display = 'block';
                    selectDisplay.classList.add('open');
                }
            });
            
            // Handle option selection
            options.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const country = this.getAttribute('data-country');
                    const img = this.querySelector('img').cloneNode(true);
                    const text = value;
                    
                    // Update hidden input
                    hiddenInput.value = value;
                    
                    // Update display
                    selectedOption.innerHTML = '';
                    selectedOption.appendChild(img);
                    const spanElement = document.createElement('span');
                    spanElement.textContent = text;
                    selectedOption.appendChild(spanElement);
                    
                    // Close dropdown
                    dropdown.style.display = 'none';
                    selectDisplay.classList.remove('open');
                    
                    // Remove selected class from all options
                    options.forEach(opt => opt.classList.remove('selected'));
                    // Add selected class to current option
                    this.classList.add('selected');
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!customSelect.contains(e.target)) {
                    dropdown.style.display = 'none';
                    selectDisplay.classList.remove('open');
                }
            });
            
            // Set initial selected state
            const initialOption = dropdown.querySelector('.option[data-value="+57"]');
            if (initialOption) {
                initialOption.classList.add('selected');
            }
            
            // Actualizar maxlength y placeholder cuando cambia la selección de país
            options.forEach(option => {
                option.addEventListener('click', function() {
                    const countryCode = this.getAttribute('data-value');
                    // Llamar a la función que actualiza tanto maxlength como placeholder
                    updatePhoneMaxLength(countryCode);
                });
            });
            
            // Validación numérica en tiempo real - solo permitir números
            phoneInput.addEventListener('input', function(e) {
                // Eliminar cualquier carácter que no sea numérico
                let value = e.target.value.replace(/[^0-9]/g, '');
                
                // Actualizar el valor del input con solo números limpios
                e.target.value = value;
                
                // Validar contra la longitud máxima actual
                const currentMaxLength = parseInt(e.target.getAttribute('maxlength'));
                if (value.length > currentMaxLength) {
                    e.target.value = value.substring(0, currentMaxLength);
                }
            });
            
            // Prevenir pegado de contenido no numérico
            phoneInput.addEventListener('paste', function(e) {
                e.preventDefault(); // Interceptar el evento de pegado
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const numericOnly = paste.replace(/[^0-9]/g, ''); // Filtrar solo números
                const currentMaxLength = parseInt(e.target.getAttribute('maxlength'));
                // Aplicar solo los números filtrados respetando la longitud máxima
                e.target.value = numericOnly.substring(0, currentMaxLength);
            });
            
            // Prevenir escritura de caracteres no numéricos
            phoneInput.addEventListener('keypress', function(e) {
                // Permitir: backspace, delete, tab, escape, enter
                if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                    // Permitir: Ctrl+A (seleccionar todo), Ctrl+C (copiar), Ctrl+V (pegar), Ctrl+X (cortar)
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true)) {
                    return; // Permitir estas teclas especiales
                }
                // Asegurar que solo sean números y bloquear otras teclas
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault(); // Bloquear teclas no numéricas
                }
            });
        });
        
        // Manejar sugerencias de IA desde URL
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const suggestedUsername = urlParams.get('suggested_username');
            
            if (suggestedUsername) {
                const usernameField = document.getElementById('username');
                if (usernameField) {
                    usernameField.value = suggestedUsername;
                    usernameField.style.background = '#e8f5e8';
                    usernameField.style.borderColor = '#4CAF50';
                    
                    // Mostrar notificación temporal
                    const notification = document.createElement('div');
                    notification.innerHTML = '✨ Nombre sugerido por IA aplicado exitosamente';
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: #4CAF50;
                        color: white;
                        padding: 12px 20px;
                        border-radius: 8px;
                        font-weight: 500;
                        z-index: 9999;
                        animation: slideIn 0.3s ease;
                    `;
                    document.body.appendChild(notification);
                    
                    // Remover notificación después de 3 segundos
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                    
                    // Limpiar URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        });
    </script>
    
    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    
</body>
</html>
