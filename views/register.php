<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Valora</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <!-- Incluir el logo de Valora ubicado en assets/images/logo_valoras.png -->
        <img src="../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>Crear Cuenta</h2>
        <form action="register.php" method="POST">
            <div class="form-group">
                <!-- Campo de identificación con label y placeholder más descriptivos -->
                <label for="Numero_de_cedula">Número de identificación (Cédula):</label>
                <input type="text" id="Numero_de_cedula" placeholder="Número de identificación" name="Numero_de_cedula" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">Nombre:</label>
                <input type="text" id="first_name" placeholder="Nombre" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Apellidos:</label>
                <input type="text" id="last_name" placeholder="Apellidos" name="last_name" required>
            </div>
            
            <div class="form-group">
                <label for="phone_number">Número de Celular:</label>
                <div style="display: flex; gap: 8px;">
                    <!-- Select oculto para envío del formulario -->
                    <input type="hidden" id="country_code" name="country_code" value="+57" required>
                    
                    <!-- Selector personalizado -->
                    <div class="custom-select" style="position: relative; width: 110px;">
                        <div class="select-display" style="padding: 14px 12px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease;">
                            <div class="selected-option" style="display: flex; align-items: center;">
                                <img src="../assets/images/flags/co.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                <span>+57</span>
                            </div>
                            <span class="dropdown-arrow" style="transform: rotate(0deg); transition: transform 0.3s;">▼</span>
                        </div>
                        
                        <div class="select-dropdown" style="position: absolute; top: 100%; left: 0; width: 200px; background: white; border: 1px solid #ee6f92; border-top: none; border-radius: 0 0 12px 12px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none;">
                            <!-- Colombia siempre de primero -->
                            <div class="option" data-value="+57" data-country="co" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/co.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                <span>Colombia (+57)</span>
                            </div>
                            
                            <!-- Resto de países en orden alfabético -->
                            <div class="option" data-value="+54" data-country="ar" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/ar.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Argentina">
                                <span>Argentina (+54)</span>
                            </div>
                            <div class="option" data-value="+56" data-country="cl" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/cl.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Chile">
                                <span>Chile (+56)</span>
                            </div>
                            <div class="option" data-value="+593" data-country="ec" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/ec.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Ecuador">
                                <span>Ecuador (+593)</span>
                            </div>
                            <div class="option" data-value="+1" data-country="us" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/us.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Estados Unidos">
                                <span>Estados Unidos (+1)</span>
                            </div>
                            <div class="option" data-value="+52" data-country="mx" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/mx.png" style="width: 20px; height: auto; margin-right: 8px;" alt="México">
                                <span>México (+52)</span>
                            </div>
                            <div class="option" data-value="+51" data-country="pe" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/pe.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Perú">
                                <span>Perú (+51)</span>
                            </div>
                            <div class="option" data-value="+58" data-country="ve" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/ve.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Venezuela">
                                <span>Venezuela (+58)</span>
                            </div>
                        </div>
                    </div>
                    <input type="tel" id="phone_number" placeholder="Número de celular" name="phone_number" inputmode="numeric" pattern="\d{10}" maxlength="10" style="flex: 1; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; transition: all 0.3s ease;" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            ¿Ya tienes una cuenta? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión aquí</a>
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customSelect = document.querySelector('.custom-select');
            const selectDisplay = customSelect.querySelector('.select-display');
            const dropdown = customSelect.querySelector('.select-dropdown');
            const hiddenInput = document.getElementById('country_code');
            const selectedOption = selectDisplay.querySelector('.selected-option');
            const options = dropdown.querySelectorAll('.option');
            
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
            
            // Establecer configuración inicial para Colombia (país por defecto)
            updatePhoneMaxLength('+57');
            
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
    </script>
</body>
</html>