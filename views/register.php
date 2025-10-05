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
                            <!-- Países principales -->
                            <div style="padding: 8px 12px; background: #f5f5f5; font-weight: bold; color: #666; font-size: 12px;">Países principales</div>
                            <div class="option" data-value="+57" data-country="colombia" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/colombia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Colombia">
                                <span>Colombia (+57)</span>
                            </div>
                            <div class="option" data-value="+58" data-country="venezuela" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/venezuela.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Venezuela">
                                <span>Venezuela (+58)</span>
                            </div>
                            <div class="option" data-value="+52" data-country="mexico" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/mexico.png" style="width: 20px; height: auto; margin-right: 8px;" alt="México">
                                <span>México (+52)</span>
                            </div>
                            <div class="option" data-value="+54" data-country="argentina" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/argentina.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Argentina">
                                <span>Argentina (+54)</span>
                            </div>
                            
                            <!-- Todos los países -->
                            <div style="padding: 8px 12px; background: #f5f5f5; font-weight: bold; color: #666; font-size: 12px;">Todos los países</div>
                            <div class="option" data-value="+93" data-country="afganistan" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/afganistan.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Afganistán">
                                <span>Afganistán (+93)</span>
                            </div>
                            <div class="option" data-value="+355" data-country="albania" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/albania.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Albania">
                                <span>Albania (+355)</span>
                            </div>
                            <div class="option" data-value="+213" data-country="argelia" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/argelia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Argelia">
                                <span>Argelia (+213)</span>
                            </div>
                            <div class="option" data-value="+376" data-country="andorra" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/andorra.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Andorra">
                                <span>Andorra (+376)</span>
                            </div>
                            <div class="option" data-value="+244" data-country="angola" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/angola.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Angola">
                                <span>Angola (+244)</span>
                            </div>
                            <div class="option" data-value="+1264" data-country="anguila" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/anguila.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Anguila">
                                <span>Anguila (+1264)</span>
                            </div>
                            <div class="option" data-value="+1268" data-country="antiguaybarbuda" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/antiguaybarbuda.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Antigua y Barbuda">
                                <span>Antigua y Barbuda (+1268)</span>
                            </div>
                            <div class="option" data-value="+966" data-country="arabiasaudita" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/arabiasaudita.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Arabia Saudita">
                                <span>Arabia Saudita (+966)</span>
                            </div>
                            <div class="option" data-value="+374" data-country="armenia" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/armenia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Armenia">
                                <span>Armenia (+374)</span>
                            </div>
                            <div class="option" data-value="+297" data-country="aruba" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/aruba.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Aruba">
                                <span>Aruba (+297)</span>
                            </div>
                            <div class="option" data-value="+61" data-country="australia" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/australia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Australia">
                                <span>Australia (+61)</span>
                            </div>
                            <div class="option" data-value="+43" data-country="austria" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/austria.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Austria">
                                <span>Austria (+43)</span>
                            </div>
                            
                            <!-- Países más comunes -->
                            <div class="option" data-value="+55" data-country="brasil" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/brasil.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Brasil">
                                <span>Brasil (+55)</span>
                            </div>
                            <div class="option" data-value="+1" data-country="canada" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/canada.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Canadá">
                                <span>Canadá (+1)</span>
                            </div>
                            <div class="option" data-value="+56" data-country="chile" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/chile.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Chile">
                                <span>Chile (+56)</span>
                            </div>
                            <div class="option" data-value="+506" data-country="costarica" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/costarica.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Costa Rica">
                                <span>Costa Rica (+506)</span>
                            </div>
                            <div class="option" data-value="+593" data-country="ecuador" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/ecuador.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Ecuador">
                                <span>Ecuador (+593)</span>
                            </div>
                            <div class="option" data-value="+503" data-country="elsalvador" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/elsalvador.png" style="width: 20px; height: auto; margin-right: 8px;" alt="El Salvador">
                                <span>El Salvador (+503)</span>
                            </div>
                            <div class="option" data-value="+34" data-country="espana" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/espana.png" style="width: 20px; height: auto; margin-right: 8px;" alt="España">
                                <span>España (+34)</span>
                            </div>
                            <div class="option" data-value="+1" data-country="estadosunidos" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/estadosunidos.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Estados Unidos">
                                <span>Estados Unidos (+1)</span>
                            </div>
                            <div class="option" data-value="+33" data-country="francia" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/francia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Francia">
                                <span>Francia (+33)</span>
                            </div>
                            <div class="option" data-value="+502" data-country="guatemala" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/guatemala.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Guatemala">
                                <span>Guatemala (+502)</span>
                            </div>
                            <div class="option" data-value="+504" data-country="honduras" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/honduras.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Honduras">
                                <span>Honduras (+504)</span>
                            </div>
                            <div class="option" data-value="+39" data-country="italia" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/italia.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Italia">
                                <span>Italia (+39)</span>
                            </div>
                            <div class="option" data-value="+505" data-country="nicaragua" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/nicaragua.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Nicaragua">
                                <span>Nicaragua (+505)</span>
                            </div>
                            <div class="option" data-value="+507" data-country="panama" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/panama.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Panamá">
                                <span>Panamá (+507)</span>
                            </div>
                            <div class="option" data-value="+595" data-country="paraguay" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/paraguay.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Paraguay">
                                <span>Paraguay (+595)</span>
                            </div>
                            <div class="option" data-value="+51" data-country="peru" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/peru.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Perú">
                                <span>Perú (+51)</span>
                            </div>
                            <div class="option" data-value="+1" data-country="puertorico" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/puertorico.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Puerto Rico">
                                <span>Puerto Rico (+1)</span>
                            </div>
                            <div class="option" data-value="+1" data-country="republicadominicana" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/republicadominicana.png" style="width: 20px; height: auto; margin-right: 8px;" alt="República Dominicana">
                                <span>República Dominicana (+1)</span>
                            </div>
                            <div class="option" data-value="+598" data-country="uruguay" style="padding: 10px 16px; cursor: pointer; display: flex; align-items: center; transition: background-color 0.2s; border-bottom: 1px solid #f0f0f0;">
                                <img src="../assets/images/flags/uruguay.png" style="width: 20px; height: auto; margin-right: 8px;" alt="Uruguay">
                                <span>Uruguay (+598)</span>
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