<?php
// Habilitar reporte de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=UTF-8');

try {
    require_once '../../config/database.php';
    require_once '../../controllers/login/PasswordResetController.php';
} catch (Exception $e) {
    die('Error al cargar archivos necesarios: ' . $e->getMessage());
}

$passwordController = new PasswordResetController();
$result = null;
$step = 'identify'; // 'identify', 'select_method', 'sent'

// Verificar si ya está logueado
try {
    startSessionSafely();
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        header('Location: ../index.php');
        exit();
    }
} catch (Exception $e) {
    // Si hay error con la sesión, continuar sin verificar login
    error_log('Error en startSessionSafely: ' . $e->getMessage());
}

// Procesar formularios
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'identify':
                $identificationMethod = $_POST['identification_method'];
                
                // Para celular, combinar código de país con número
                if($identificationMethod === 'celular') {
                    $countryCode = $_POST['country_code'] ?? '+57';
                    $phoneNumber = $_POST['identifier'] ?? '';
                    // Limpiar el número y agregar código de país si no lo tiene
                    $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);
                    $identifier = $phoneNumber; // Solo el número sin código para buscar en BD
                } else {
                    $identifier = $_POST['identifier'];
                }
                
                $result = $passwordController->findUser($identifier, $identificationMethod);
                if($result['success']) {
                    $step = 'select_method';
                    // Guardar datos en sesión para el siguiente paso
                    $_SESSION['reset_identifier'] = $identifier;
                    $_SESSION['reset_method'] = $identificationMethod;
                }
                break;
            case 'send_reset':
                $identifier = $_SESSION['reset_identifier'] ?? $_POST['cedula']; // Fallback para compatibilidad
                $method = $_SESSION['reset_method'] ?? 'cedula';
                $result = $passwordController->sendResetCode($identifier, $_POST['method'], $method);
                if($result['success']) {
                    // Guardar datos necesarios en sesión para reset_password.php
                    $_SESSION['reset_code_sent'] = true;
                    $_SESSION['reset_user_identifier'] = $identifier;
                    $_SESSION['reset_identification_method'] = $method;
                    $_SESSION['reset_contact_method'] = $_POST['method'];
                    
                    // Redirigir inmediatamente a la página de ingreso de código
                    header('Location: reset_password.php');
                    exit();
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        /* Transiciones suaves para cambios dinámicos */
        #identifier_label {
            transition: all 0.3s ease;
            color: #882A57;
            font-weight: 500;
        }
        
        #identifier {
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.15s ease;
        }
        
        #identifier:focus {
            outline: none;
            border-color: #ee6f92;
            box-shadow: 0 0 0 3px rgba(238, 111, 146, 0.1);
        }
        
        #identification_method {
            transition: border-color 0.3s ease;
        }
        
        #identification_method:focus {
            outline: none;
            border-color: #ee6f92;
            box-shadow: 0 0 0 3px rgba(238, 111, 146, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        /* Animación sutil para el cambio de label */
        @keyframes labelChange {
            0% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .label-changing {
            animation: labelChange 0.3s ease;
        }
        
        /* Estilos para el indicador de pasos */
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid #dee2e6;
        }
        
        .step.active {
            background: linear-gradient(135deg, #ee6f92 0%, #d63384 100%);
            border-color: #ee6f92;
            color: white;
            box-shadow: 0 4px 12px rgba(238, 111, 146, 0.3);
        }
        
        .step.completed {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .step-line {
            width: 60px;
            height: 3px;
            background: #e9ecef;
        }
        
        .step-line.active {
            background: linear-gradient(90deg, #28a745 0%, #28a745 100%);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="../../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>🔐 Recuperar Contraseña</h2>
        
        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <?php 
            $currentStep = 1;
            if($step === 'select_method') $currentStep = 2;
            ?>
            <div class="step <?php echo $currentStep >= 1 ? ($currentStep > 1 ? 'completed' : 'active') : ''; ?>">1</div>
            <div class="step-line <?php echo $currentStep > 1 ? 'active' : ''; ?>"></div>
            <div class="step <?php echo $currentStep >= 2 ? 'active' : ''; ?>">2</div>
        </div>
        
        <?php if($result && !$result['success']): ?>
            <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($result['message']); ?>
            </div>
        <?php endif; ?>

        <?php if($step === 'identify'): ?>
            <!-- Paso 1: Identificar usuario -->
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Ingresa tu información para recuperar tu contraseña
            </p>
            
            <form action="password_reset.php" method="POST">
                <input type="hidden" name="action" value="identify">
                
                <!-- Selector de método de identificación -->
                <div class="form-group">
                    <label for="identification_method">¿Cómo quieres identificarte?</label>
                    <select id="identification_method" name="identification_method" 
                            onchange="updatePlaceholder()" 
                            class="form-input"
                            style="width: 100%; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; margin-bottom: 15px; cursor: pointer;">
                        <option value="cedula">Número de Cédula</option>
                        <option value="username">Nombre de Usuario</option>
                        <option value="celular">Número de Celular</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="identifier" id="identifier_label">Número de identificación (Cédula):</label>
                    <div id="identifier_container">
                        <!-- Campo normal para cédula y username -->
                        <input type="text" id="identifier" name="identifier" required
                               placeholder="Ingresa tu número de cédula"
                               class="form-input"
                               style="width: 100%; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa;">
                        
                        <!-- Campo especial para celular (inicialmente oculto) -->
                        <div id="phone_container" style="display: none; gap: 8px;">
                            <!-- Select oculto para envío del formulario -->
                            <input type="hidden" id="country_code_reset" name="country_code" value="+57">
                            
                            <!-- Selector personalizado -->
                            <div class="custom-select" style="position: relative; width: 110px;">
                                <div class="select-display" style="padding: 14px 12px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease;">
                                    <div class="selected-option" style="display: flex; align-items: center;">
                                        <img src="../../assets/images/flags/co.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                        <span>+57</span>
                                    </div>
                                    <span class="dropdown-arrow" style="transform: rotate(0deg); transition: transform 0.3s;">▼</span>
                                </div>
                                
                                <!-- Dropdown options -->
                                <div class="options-list" style="position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #ee6f92; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 100; display: none; max-height: 200px; overflow-y: auto;">
                                    <div class="option" data-value="+57" data-flag="co" style="padding: 12px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s;">
                                        <img src="../../assets/images/flags/co.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                        <span style="margin-right: 8px;">Colombia</span>
                                        <span style="color: #666; font-size: 14px;">(+57)</span>
                                    </div>
                                    <div class="option" data-value="+54" data-flag="ar" style="padding: 12px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s;">
                                        <img src="../../assets/images/flags/ar.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Argentina">
                                        <span style="margin-right: 8px;">Argentina</span>
                                        <span style="color: #666; font-size: 14px;">(+54)</span>
                                    </div>
                                    <div class="option" data-value="+56" data-flag="cl" style="padding: 12px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s;">
                                        <img src="../../assets/images/flags/cl.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Chile">
                                        <span style="margin-right: 8px;">Chile</span>
                                        <span style="color: #666; font-size: 14px;">(+56)</span>
                                    </div>
                                    <div class="option" data-value="+593" data-flag="ec" style="padding: 12px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s;">
                                        <img src="../../assets/images/flags/ec.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Ecuador">
                                        <span style="margin-right: 8px;">Ecuador</span>
                                        <span style="color: #666; font-size: 14px;">(+593)</span>
                                    </div>
                                    <div class="option" data-value="+1" data-flag="us" style="padding: 12px; cursor: pointer; display: flex; align-items: center; transition: background 0.2s;">
                                        <img src="../../assets/images/flags/us.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Estados Unidos">
                                        <span style="margin-right: 8px;">Estados Unidos</span>
                                        <span style="color: #666; font-size: 14px;">(+1)</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Input del teléfono -->
                            <input type="tel" id="phone_input" name="phone_number" 
                                   placeholder="10 dígitos (Colombia)"
                                   maxlength="10"
                                   style="flex: 1; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa;">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Enviar Código</button>
                
                <!-- Opción de verificación con documento -->
                <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <p style="color: #6c757d; margin-bottom: 10px; font-size: 14px;">¿No puedes recibir el código?</p>
                    <a href="verify1_document.php" 
                       style="display: inline-block; padding: 10px 20px; background: transparent; border: 2px solid #6c757d; color: #6c757d; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px; transition: all 0.3s ease;">
                        📸 Verifica tu identidad con tu documento
                    </a>
                </div>
            </form>

            <script>
                function updatePlaceholder() {
                    const method = document.getElementById('identification_method').value;
                    const label = document.getElementById('identifier_label');
                    const normalInput = document.getElementById('identifier');
                    const phoneContainer = document.getElementById('phone_container');
                    const phoneInput = document.getElementById('phone_input');
                    
                    // Añadir animación al label
                    label.classList.add('label-changing');
                    setTimeout(() => label.classList.remove('label-changing'), 300);
                    
                    switch(method) {
                        case 'cedula':
                            label.innerHTML = '<span style="color: #ee6f92;">📋</span> Número de identificación (Cédula):';
                            normalInput.style.display = 'block';
                            phoneContainer.style.display = 'none';
                            normalInput.placeholder = 'Ejemplo: 1125998052';
                            normalInput.type = 'text';
                            normalInput.maxLength = 15;
                            normalInput.name = 'identifier';
                            normalInput.required = true;
                            phoneInput.required = false;
                            break;
                        case 'username':
                            label.innerHTML = '<span style="color: #ee6f92;">👤</span> Nombre de Usuario:';
                            normalInput.style.display = 'block';
                            phoneContainer.style.display = 'none';
                            normalInput.placeholder = 'Ejemplo: juan_valora2024';
                            normalInput.type = 'text';
                            normalInput.maxLength = 50;
                            normalInput.name = 'identifier';
                            normalInput.required = true;
                            phoneInput.required = false;
                            break;
                        case 'celular':
                            label.innerHTML = '<span style="color: #ee6f92;">📱</span> Número de Celular:';
                            normalInput.style.display = 'none';
                            phoneContainer.style.display = 'flex';
                            phoneInput.placeholder = '10 dígitos (Colombia)';
                            phoneInput.maxLength = 10;
                            phoneInput.name = 'identifier';
                            phoneInput.required = true;
                            normalInput.required = false;
                            break;
                        default:
                            normalInput.style.display = 'block';
                            phoneContainer.style.display = 'none';
                            label.innerHTML = 'Información de identificación:';
                            normalInput.placeholder = 'Ingresa tu información';
                            normalInput.type = 'text';
                    }
                    
                    // Limpiar los campos cuando cambie el método
                    normalInput.value = '';
                    phoneInput.value = '';
                    
                    // Enfocar el campo correcto
                    setTimeout(() => {
                        if (method === 'celular') {
                            phoneInput.focus();
                        } else {
                            normalInput.focus();
                        }
                    }, 150);
                }
                
                // Funcionalidad del selector de país
                function initPhoneSelector() {
                    const selectDisplay = document.querySelector('.select-display');
                    const optionsList = document.querySelector('.options-list');
                    const options = document.querySelectorAll('.option');
                    const countryCodeInput = document.getElementById('country_code_reset');
                    const phoneInput = document.getElementById('phone_input');
                    
                    // Toggle dropdown
                    selectDisplay.addEventListener('click', () => {
                        const isOpen = optionsList.style.display === 'block';
                        optionsList.style.display = isOpen ? 'none' : 'block';
                        const arrow = selectDisplay.querySelector('.dropdown-arrow');
                        arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                    });
                    
                    // Handle option selection
                    options.forEach(option => {
                        option.addEventListener('click', () => {
                            const value = option.getAttribute('data-value');
                            const flag = option.getAttribute('data-flag');
                            
                            // Update display
                            const selectedOption = selectDisplay.querySelector('.selected-option');
                            selectedOption.innerHTML = `
                                <img src="../../assets/images/flags/${flag}.png" style="width: 20px; height: auto; margin-right: 8px;" alt="${flag}">
                                <span>${value}</span>
                            `;
                            
                            // Update hidden input
                            countryCodeInput.value = value;
                            
                            // Close dropdown
                            optionsList.style.display = 'none';
                            selectDisplay.querySelector('.dropdown-arrow').style.transform = 'rotate(0deg)';
                            
                            // Update phone placeholder based on country
                            const countryName = option.querySelector('span').textContent;
                            let digits, maxLength;
                            
                            switch(value) {
                                case '+57': // Colombia
                                    digits = '10 dígitos';
                                    maxLength = 10;
                                    break;
                                case '+54': // Argentina
                                    digits = '10 dígitos';
                                    maxLength = 10;
                                    break;
                                case '+56': // Chile
                                    digits = '9 dígitos';
                                    maxLength = 9;
                                    break;
                                case '+593': // Ecuador
                                    digits = '9 dígitos';
                                    maxLength = 9;
                                    break;
                                case '+1': // Estados Unidos
                                    digits = '10 dígitos';
                                    maxLength = 10;
                                    break;
                                default:
                                    digits = '10 dígitos';
                                    maxLength = 10;
                            }
                            
                            phoneInput.placeholder = `${digits} (${countryName})`;
                            phoneInput.maxLength = maxLength;
                            
                            phoneInput.focus();
                        });
                        
                        // Hover effect
                        option.addEventListener('mouseenter', () => {
                            option.style.backgroundColor = '#f8f9fa';
                        });
                        option.addEventListener('mouseleave', () => {
                            option.style.backgroundColor = 'white';
                        });
                    });
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!selectDisplay.contains(e.target) && !optionsList.contains(e.target)) {
                            optionsList.style.display = 'none';
                            selectDisplay.querySelector('.dropdown-arrow').style.transform = 'rotate(0deg)';
                        }
                    });
                    
                    // Add input validation for phone number
                    phoneInput.addEventListener('input', function(e) {
                        // Solo permitir números
                        let value = e.target.value.replace(/\D/g, '');
                        
                        // Aplicar longitud máxima
                        const maxLength = parseInt(e.target.maxLength);
                        if (value.length > maxLength) {
                            value = value.substring(0, maxLength);
                        }
                        
                        e.target.value = value;
                        
                        // Cambiar color del borde según la validez
                        if (value.length === maxLength) {
                            e.target.style.borderColor = '#28a745'; // Verde si está completo
                        } else if (value.length > 0) {
                            e.target.style.borderColor = '#ffc107'; // Amarillo si está parcial
                        } else {
                            e.target.style.borderColor = '#ee6f92'; // Rosa por defecto
                        }
                    });
                }
                
                // Ejecutar al cargar la página
                document.addEventListener('DOMContentLoaded', function() {
                    updatePlaceholder();
                    initPhoneSelector();
                    
                    // Agregar evento adicional por si acaso
                    const select = document.getElementById('identification_method');
                    if (select) {
                        select.addEventListener('change', updatePlaceholder);
                    }
                });
            </script>

        <?php elseif($step === 'select_method'): ?>
            <!-- Paso 2: Seleccionar método de recuperación -->
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Selecciona cómo quieres recibir el enlace de recuperación:
            </p>
            
            <form action="password_reset.php" method="POST">
                <input type="hidden" name="action" value="send_reset">
                
                <!-- Alertas de validación -->
                <?php if(isset($result['email_alert']) || isset($result['phone_alert'])): ?>
                    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <h4 style="color: #856404; margin: 0 0 10px 0; font-size: 16px;">⚠️ Advertencias de Validación</h4>
                        <?php if(isset($result['email_alert'])): ?>
                            <div style="color: #856404; font-size: 14px; margin-bottom: 8px;">
                                <?php echo $result['email_alert']; ?>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($result['phone_alert'])): ?>
                            <div style="color: #856404; font-size: 14px;">
                                <?php echo $result['phone_alert']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                    // Determinar cuál es la primera opción válida para preseleccionar
                    $firstValidMethod = null;
                    if($result['masked_email']) {
                        $firstValidMethod = 'email';
                    } elseif($result['masked_phone']) {
                        $firstValidMethod = 'sms';
                    }
                ?>

                <div style="margin-bottom: 20px;">
                    <?php if($result['masked_email']): ?>
                        <div class="method-option" style="border: 2px solid #28a745; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s; background-color: #f8fff9;">
                            <input type="radio" id="method_email" name="method" value="email" required style="margin-right: 10px;" <?php echo ($firstValidMethod === 'email') ? 'checked' : ''; ?>>
                            <label for="method_email" style="cursor: pointer; font-weight: 500;">
                                📧 Correo electrónico ✅
                                <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                    Enviar a: <?php echo $result['masked_email']; ?>
                                </div>
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="method-option-disabled" style="border: 2px solid #dc3545; border-radius: 8px; padding: 15px; margin-bottom: 15px; background-color: #fff5f5; opacity: 0.7;">
                            <input type="radio" disabled style="margin-right: 10px;">
                            <label style="cursor: not-allowed; font-weight: 500; color: #dc3545;">
                                📧 Correo electrónico ❌
                                <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">
                                    Email no válido - No disponible
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($result['masked_phone']): ?>
                        <div class="method-option" style="border: 2px solid #28a745; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s; background-color: #f8fff9;">
                            <input type="radio" id="method_sms" name="method" value="sms" required style="margin-right: 10px;" <?php echo ($firstValidMethod === 'sms') ? 'checked' : ''; ?>>
                            <label for="method_sms" style="cursor: pointer; font-weight: 500;">
                                📱 Mensaje SMS ✅
                                <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                    Enviar a: <?php echo $result['masked_phone']; ?>
                                </div>
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="method-option-disabled" style="border: 2px solid #dc3545; border-radius: 8px; padding: 15px; margin-bottom: 15px; background-color: #fff5f5; opacity: 0.7;">
                            <input type="radio" disabled style="margin-right: 10px;">
                            <label style="cursor: not-allowed; font-weight: 500; color: #dc3545;">
                                📱 Mensaje SMS ❌
                                <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">
                                    Celular no válido - No disponible
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" id="sendButton" class="btn-submit">
                    <span id="buttonText">Enviar Código</span>
                    <span id="buttonLoader" class="loading-spinner" style="display: none;">
                        <span class="spinner"></span>
                        Enviando...
                    </span>
                </button>
                
                <!-- Opción de verificación con documento -->
                <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <p style="color: #6c757d; margin-bottom: 10px; font-size: 14px;">¿No puedes recibir el código?</p>
                    <a href="verify1_document.php" 
                       style="display: inline-block; padding: 10px 20px; background: transparent; border: 2px solid #6c757d; color: #6c757d; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 14px; transition: all 0.3s ease;">
                        📸 Verifica tu identidad con tu documento
                    </a>
                </div>
                
                <button type="button" onclick="window.location.href='password_reset.php'" class="btn-secondary" style="margin-top: 10px; background-color: #f0f0f0; color: #666;">
                    ← Volver
                </button>
            </form>

        <?php elseif($step === 'sent'): ?>
            <!-- Paso 3: Confirmación de envío -->
            <div class="alert alert-success" style="background-color: #efe; border: 1px solid #cfc; color: #3c3; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                ✅ <?php echo htmlspecialchars($result['message']); ?>
            </div>
            
            <div style="text-align: center; color: #666; margin-bottom: 30px;">
                <p>Revisa tu <?php echo $_POST['method'] === 'email' ? 'correo electrónico' : 'mensajes SMS'; ?> y sigue las instrucciones.</p>
                <p style="font-size: 14px;">Si no recibes el mensaje en unos minutos, revisa tu carpeta de spam.</p>
            </div>
            
            <div style="text-align: center;">
                <button type="button" onclick="window.location.href='login.php'" class="btn-submit">
                    ← Volver al Login
                </button>
                <button type="button" onclick="window.location.href='password_reset.php'" class="btn-secondary" style="margin-top: 10px; background-color: #f0f0f0; color: #666;">
                    🔄 Intentar de Nuevo
                </button>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 14px;">
            <div style="margin-bottom: 12px;">
                ¿Recordaste tu contraseña? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión</a>
            </div>
            <div>
                ¿Aún no tienes una cuenta? <a href="registranteUserAvailavilitySelect.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Regístrate aquí</a>
            </div>
        </div>
    </div>

    <style>
        .method-option:hover {
            border-color: #882A57 !important;
            background-color: #fafafa;
        }
        
        .method-option input[type="radio"]:checked + label {
            color: #882A57;
        }
        
        .method-option:has(input[type="radio"]:checked),
        .method-option.selected {
            border-color: #882A57 !important;
            background-color: #f9f5f7;
        }
        
        .btn-secondary {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0 !important;
            border-color: #bbb;
        }
        
        /* Estilos para animación del botón */
        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-submit.loading {
            background: linear-gradient(-45deg, #882A57, #9d3461, #882A57, #9d3461);
            background-size: 400% 400%;
            animation: gradientShift 2s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .btn-submit.pulse {
            animation: pulse 0.6s;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Estilo para el enlace de verificación documental */
        a[href="verify1_document.php"]:hover {
            background: #6c757d !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo de la animación del botón de envío
            const form = document.querySelector('form[action="password_reset.php"]');
            const sendButton = document.getElementById('sendButton');
            const buttonText = document.getElementById('buttonText');
            const buttonLoader = document.getElementById('buttonLoader');
            
            if (form && sendButton) {
                form.addEventListener('submit', function(e) {
                    // Verificar que hay un método seleccionado
                    const selectedMethod = document.querySelector('input[name="method"]:checked');
                    if (!selectedMethod) {
                        e.preventDefault();
                        alert('Por favor selecciona un método de envío');
                        return;
                    }
                    
                    // Iniciar animación
                    startLoadingAnimation();
                });
            }
            
            function startLoadingAnimation() {
                if (sendButton && buttonText && buttonLoader) {
                    // Ocultar texto normal y mostrar loader
                    buttonText.style.display = 'none';
                    buttonLoader.style.display = 'flex';
                    
                    // Deshabilitar botón y agregar clases de animación
                    sendButton.disabled = true;
                    sendButton.classList.add('loading');
                    
                    // Efecto de pulso inicial
                    sendButton.classList.add('pulse');
                    setTimeout(() => {
                        sendButton.classList.remove('pulse');
                    }, 600);
                    
                    // Cambiar texto del loader progresivamente
                    setTimeout(() => {
                        if (buttonLoader.querySelector('span:last-child')) {
                            buttonLoader.querySelector('span:last-child').textContent = 'Validando datos...';
                        }
                    }, 1000);
                    
                    setTimeout(() => {
                        if (buttonLoader.querySelector('span:last-child')) {
                            buttonLoader.querySelector('span:last-child').textContent = 'Enviando email...';
                        }
                    }, 2000);
                    
                    setTimeout(() => {
                        if (buttonLoader.querySelector('span:last-child')) {
                            buttonLoader.querySelector('span:last-child').textContent = 'Finalizando...';
                        }
                    }, 3000);
                }
            }
            
            // Mejorar la experiencia de selección de métodos
            const methodOptions = document.querySelectorAll('.method-option');
            methodOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]:not([disabled])');
                    if (radio) {
                        radio.checked = true;
                        
                        // Efecto visual de selección
                        methodOptions.forEach(opt => opt.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        // Efecto de confirmación
                        this.style.transform = 'scale(1.02)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 150);
                    }
                });
            });
            
            // Preseleccionar visualmente el método ya marcado
            const checkedRadio = document.querySelector('input[name="method"]:checked');
            if (checkedRadio) {
                const parentOption = checkedRadio.closest('.method-option');
                if (parentOption) {
                    parentOption.classList.add('selected');
                }
            }
        });
    </script>
</body>
</html>