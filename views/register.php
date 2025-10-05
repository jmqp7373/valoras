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
                <label for="Numero_de_cedula">Número de Cédula:</label>
                <input type="text" id="Numero_de_cedula" placeholder="Cédula" name="Numero_de_cedula" required>
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
                    <div class="custom-select" style="position: relative; width: 220px;">
                        <div class="select-display" style="padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease;">
                            <div class="selected-option" style="display: flex; align-items: center;">
                                <img src="../assets/images/flags/colombia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                <span>+57</span>
                            </div>
                            <span style="transform: rotate(0deg); transition: transform 0.3s;">▼</span>
                        </div>
                        
                        <div class="select-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ee6f92; border-top: none; border-radius: 0 0 12px 12px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none;">
                            <!-- Colombia siempre de primero -->
                            <div class="option" data-value="+57" data-country="colombia" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/colombia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                <span>Colombia (+57)</span>
                            </div>
                            
                            <!-- Resto de países en orden alfabético -->
                            <div class="option" data-value="+54" data-country="argentina" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/argentina.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Argentina">
                                <span>Argentina (+54)</span>
                            </div>
                            <div class="option" data-value="+56" data-country="chile" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/chile.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Chile">
                                <span>Chile (+56)</span>
                            </div>
                            <div class="option" data-value="+593" data-country="ecuador" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/ecuador.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Ecuador">
                                <span>Ecuador (+593)</span>
                            </div>
                            <div class="option" data-value="+52" data-country="mexico" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/mexico.png" style="width: 20px; height: auto; margin-right: 8px;" alt="México">
                                <span>México (+52)</span>
                            </div>
                            <div class="option" data-value="+51" data-country="peru" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/peru.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Perú">
                                <span>Perú (+51)</span>
                            </div>
                            <div class="option" data-value="+58" data-country="venezuela" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/venezuela.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Venezuela">
                                <span>Venezuela (+58)</span>
                            </div>
                        </div>
                    </div>
                    <input type="tel" id="phone_number" placeholder="Número de celular" name="phone_number" inputmode="numeric" pattern="\d{6,15}" style="flex: 1; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; transition: all 0.3s ease;" required>
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
        
        .select-display.open span:last-child {
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
                    selectedOption.innerHTML += `<span>${text}</span>`;
                    
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
        });
    </script>
</body>
</html>