/**
 * Upload ID Document Script
 * 
 * Script para manejar la carga y análisis de documentos de identidad (frontal y posterior)
 * usando la Cloud Vision API a través del backend
 * 
 * @author Valora.vip
 * @version 2.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    const idPhotoFrontInput = document.getElementById('idPhotoFront');
    const idPhotoBackInput = document.getElementById('idPhotoBack');
    const idPreviewFront = document.getElementById('idPreviewFront');
    const idPreviewBack = document.getElementById('idPreviewBack');
    const analyzeButton = document.getElementById('analyzeIdButton');
    const resultDiv = document.getElementById('idScanResult');
    
    // Validaciones
    const MAX_FILE_SIZE = 6 * 1024 * 1024; // 6MB
    const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    
    let frontImageReady = false;
    let backImageReady = false;
    
    /**
     * Previsualizar imagen frontal cuando se selecciona
     */
    idPhotoFrontInput.addEventListener('change', function(e) {
        handleImageSelection(e, idPreviewFront, 'frontal', (isValid) => {
            frontImageReady = isValid;
            updateAnalyzeButton();
        });
    });
    
    /**
     * Previsualizar imagen posterior cuando se selecciona
     */
    idPhotoBackInput.addEventListener('change', function(e) {
        handleImageSelection(e, idPreviewBack, 'posterior', (isValid) => {
            backImageReady = isValid;
            updateAnalyzeButton();
        });
    });
    
    /**
     * Manejar selección de imagen
     */
    function handleImageSelection(event, previewElement, side, callback) {
        const file = event.target.files[0];
        
        if (!file) {
            previewElement.classList.add('d-none');
            callback(false);
            return;
        }
        
        // Validar tipo de archivo
        if (!ALLOWED_TYPES.includes(file.type)) {
            showError(`Formato no permitido en imagen ${side}. Use JPEG, PNG o WebP.`);
            event.target.value = '';
            callback(false);
            return;
        }
        
        // Validar tamaño
        if (file.size > MAX_FILE_SIZE) {
            showError(`La imagen ${side} es demasiado grande. Tamaño máximo: 6MB.`);
            event.target.value = '';
            callback(false);
            return;
        }
        
        // Mostrar previsualización
        const reader = new FileReader();
        reader.onload = function(event) {
            previewElement.src = event.target.result;
            previewElement.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
        
        callback(true);
    }
    
    /**
     * Actualizar estado del botón de análisis
     */
    function updateAnalyzeButton() {
        analyzeButton.disabled = !(frontImageReady && backImageReady);
    }
    
    /**
     * Analizar documento al hacer clic en el botón
     */
    analyzeButton.addEventListener('click', async function() {
        const fileFront = idPhotoFrontInput.files[0];
        const fileBack = idPhotoBackInput.files[0];
        
        if (!fileFront || !fileBack) {
            showError('Por favor, selecciona ambas imágenes (frontal y posterior).');
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        analyzeButton.disabled = true;
        analyzeButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analizando ambas imágenes...';
        resultDiv.innerHTML = '<div class="alert alert-info">🔍 Analizando documento con IA (frontal y posterior)...</div>';
        
        try {
            // Preparar FormData con ambas imágenes
            const formData = new FormData();
            formData.append('id_photo_front', fileFront);
            formData.append('id_photo_back', fileBack);
            
            // Enviar solicitud al backend (ruta relativa desde views/login/)
            const response = await fetch('../../controllers/id_verification/idVerificationController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Error al analizar el documento');
            }
            
            // Mostrar resultados
            displayResults(data);
            
        } catch (error) {
            showError('Error: ' + error.message);
        } finally {
            // Restaurar botón
            analyzeButton.disabled = false;
            analyzeButton.innerHTML = 'Analizar ambas imágenes con IA';
        }
    });
    
    /**
     * Mostrar resultados del análisis
     */
    function displayResults(data) {
        let html = '';
        
        // Estado general
        if (data.valid) {
            html += `
                <div class="alert alert-success">
                    <h5 class="mb-2">✅ ${data.message}</h5>
                </div>
            `;
        } else {
            html += `
                <div class="alert alert-warning">
                    <h5 class="mb-2">⚠️ ${data.message}</h5>
                </div>
            `;
        }
        
        // Coincidencia con usuario en BD
        if (data.userMatch) {
            html += `
                <div class="alert alert-success">
                    <strong>✅ Usuario Verificado:</strong> Los datos del documento coinciden con un usuario registrado en el sistema.
                </div>
            `;
        } else if (data.userMatch === false) {
            html += `
                <div class="alert alert-danger">
                    <strong>❌ Usuario No Encontrado:</strong> No se encontró ningún usuario registrado con este número de documento.
                </div>
            `;
        }
        
        // Errores
        if (data.errors && data.errors.length > 0) {
            html += '<div class="alert alert-danger"><strong>Errores:</strong><ul class="mb-0">';
            data.errors.forEach(error => {
                html += `<li>${error}</li>`;
            });
            html += '</ul></div>';
        }
        
        // Advertencias
        if (data.warnings && data.warnings.length > 0) {
            html += '<div class="alert alert-warning"><strong>Advertencias:</strong><ul class="mb-0">';
            data.warnings.forEach(warning => {
                html += `<li>${warning}</li>`;
            });
            html += '</ul></div>';
        }
        
        // Información detectada
        html += '<div class="card mt-3"><div class="card-body">';
        html += '<h5 class="card-title mb-3">📄 Información Detectada</h5>';
        
        if (data.data.documentType) {
            html += `<p><strong>Tipo de documento:</strong> ${data.data.documentType}</p>`;
        }
        
        if (data.data.cedula) {
            html += `<p><strong>Número de documento:</strong> ${data.data.cedula}</p>`;
        }
        
        if (data.data.nombres) {
            html += `<p><strong>Nombres:</strong> ${data.data.nombres}</p>`;
        }
        
        if (data.data.apellidos) {
            html += `<p><strong>Apellidos:</strong> ${data.data.apellidos}</p>`;
        }
        
        if (data.data.fechaNacimiento) {
            html += `<p><strong>Fecha de nacimiento:</strong> ${data.data.fechaNacimiento}</p>`;
        }
        
        if (data.data.fechaExpedicion) {
            html += `<p><strong>Fecha de expedición:</strong> ${data.data.fechaExpedicion}</p>`;
        }
        
        html += `<p><strong>Rostros detectados:</strong> ${data.data.faceCount}</p>`;
        
        // Texto completo combinado (colapsable)
        if (data.data.fullText) {
            html += `
                <details class="mt-3 ocr-text-toggle">
                    <summary style="cursor: pointer; color: #882A57; font-weight: bold;">Ver texto completo extraído (ambas caras)</summary>
                    <pre class="mt-2 p-3 ocr-text-content">${escapeHtml(data.data.fullText)}</pre>
                </details>
            `;
        }
        
        html += '</div></div>';
        
        // Información del usuario encontrado (si aplica)
        if (data.userData) {
            html += '<div class="card mt-3" style="border: 2px solid #28a745;"><div class="card-body">';
            html += '<h5 class="card-title mb-3" style="color: #28a745;">👤 Usuario Registrado</h5>';
            html += `<p><strong>Nombre completo:</strong> ${escapeHtml(data.userData.nombres)} ${escapeHtml(data.userData.apellidos)}</p>`;
            html += `<p><strong>Cédula:</strong> ${escapeHtml(data.userData.cedula)}</p>`;
            if (data.userData.celular) {
                html += `<p><strong>Celular:</strong> ${escapeHtml(data.userData.celular)}</p>`;
            }
            if (data.userData.email) {
                html += `<p><strong>Email:</strong> ${escapeHtml(data.userData.email)}</p>`;
            }
            html += '</div></div>';
        }
        
        // Botón para continuar
        if (data.valid && data.userMatch) {
            html += `
                <div class="mt-4 text-center">
                    <a href="password_reset.php" class="btn btn-primary analyze-button">
                        Continuar con recuperación
                    </a>
                </div>
            `;
        } else {
            html += `
                <div class="mt-4 text-center">
                    <button onclick="location.reload()" class="btn btn-secondary analyze-button">
                        Intentar de nuevo
                    </button>
                </div>
            `;
        }
        
        resultDiv.innerHTML = html;
        
        // Agregar listener para controlar el scroll al expandir el texto OCR
        const detailsElement = resultDiv.querySelector('details.ocr-text-toggle');
        if (detailsElement) {
            detailsElement.addEventListener('toggle', function() {
                console.log('OCR toggle listener activo - Estado:', this.open ? 'abierto' : 'cerrado');
                if (this.open) {
                    // Esperar a que el navegador aplique el cambio de estado
                    setTimeout(() => {
                        this.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start'
                        });
                    }, 100);
                }
            });
        }
        
        // ACTIVAR PASOS SEGÚN EL RESULTADO DE LA VERIFICACIÓN
        // Paso 2: Activar cuando el documento es validado correctamente
        if (data.valid) {
            setActiveStep(2);
            console.log('Paso 2 activado: Documento validado');
        }
        
        // Paso 3: Activar y mostrar formulario si hay coincidencia con usuario en BD
        if (data.valid && data.userMatch) {
            setTimeout(() => {
                setActiveStep(3);
                showUpdateForm(data.data.cedula);
                console.log('Paso 3 activado: Usuario encontrado, formulario visible');
            }, 500); // Pequeño delay para mejor UX
        }
    }
    
    /**
     * Mostrar mensaje de error
     */
    function showError(message) {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>❌ Error:</strong> ${message}
            </div>
        `;
    }
    
    /**
     * Escapar HTML para prevenir XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * GESTIÓN DE PASOS (STEP VIEW)
     */
    
    /**
     * Activar un paso específico en el indicador de progreso
     * @param {number} stepNumber - Número del paso a activar (1, 2 o 3)
     */
    function setActiveStep(stepNumber) {
        console.log(`[setActiveStep] Activando paso ${stepNumber}`);
        
        const steps = document.querySelectorAll('.steps-container .step');
        const stepLines = document.querySelectorAll('.steps-container .step-line');
        
        console.log(`[setActiveStep] Pasos encontrados: ${steps.length}, Líneas: ${stepLines.length}`);
        
        steps.forEach((step, index) => {
            const currentStepNum = index + 1;
            
            // Remover clases activas
            step.classList.remove('active', 'completed');
            
            // Activar el paso actual
            if (currentStepNum === stepNumber) {
                step.classList.add('active');
                console.log(`[setActiveStep] Paso ${currentStepNum} marcado como ACTIVO`);
            }
            
            // Marcar como completados los pasos anteriores
            if (currentStepNum < stepNumber) {
                step.classList.add('completed');
                console.log(`[setActiveStep] Paso ${currentStepNum} marcado como COMPLETADO`);
            }
        });
        
        // Actualizar líneas de conexión
        stepLines.forEach((line, index) => {
            line.classList.remove('active');
            if (index < stepNumber - 1) {
                line.classList.add('active');
            }
        });
        
        console.log(`Step View: Paso ${stepNumber} activado`);
    }
    
    /**
     * Mostrar el formulario de actualización de datos (Paso 3)
     * @param {string} cedula - Número de cédula del usuario verificado
     */
    function showUpdateForm(cedula) {
        const updateForm = document.getElementById('updateUserData');
        const hiddenCedulaInput = document.getElementById('hiddenCedula');
        
        if (updateForm && hiddenCedulaInput) {
            // Establecer la cédula en el campo oculto
            hiddenCedulaInput.value = cedula;
            
            // Mostrar el formulario con animación
            updateForm.classList.add('visible');
            
            // Scroll suave hacia el formulario
            setTimeout(() => {
                updateForm.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }, 200);
            
            console.log('Formulario de actualización visible con cédula:', cedula);
        }
    }
    
    // =========================================
    // INICIALIZACIÓN DEL STEP VIEW AL CARGAR LA PÁGINA
    // =========================================
    
    // Asegurar que solo el Paso 1 esté activo al inicio
    setActiveStep(1);
    console.log('Step View inicializado correctamente - Paso 1 activo por defecto');
});

