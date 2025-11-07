/**
 * Upload ID Document Script
 * 
 * Script para manejar la carga y análisis de documentos de identidad
 * usando la Cloud Vision API a través del backend
 * 
 * @author Valora.vip
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    const idPhotoInput = document.getElementById('idPhoto');
    const idPreview = document.getElementById('idPreview');
    const analyzeButton = document.getElementById('analyzeIdButton');
    const resultDiv = document.getElementById('idScanResult');
    
    // Validaciones
    const MAX_FILE_SIZE = 6 * 1024 * 1024; // 6MB
    const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    
    /**
     * Previsualizar imagen cuando se selecciona
     */
    idPhotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) {
            idPreview.classList.add('d-none');
            return;
        }
        
        // Validar tipo de archivo
        if (!ALLOWED_TYPES.includes(file.type)) {
            showError('Formato no permitido. Use JPEG, PNG o WebP.');
            idPhotoInput.value = '';
            return;
        }
        
        // Validar tamaño
        if (file.size > MAX_FILE_SIZE) {
            showError('La imagen es demasiado grande. Tamaño máximo: 6MB.');
            idPhotoInput.value = '';
            return;
        }
        
        // Mostrar previsualización
        const reader = new FileReader();
        reader.onload = function(event) {
            idPreview.src = event.target.result;
            idPreview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
        
        // Habilitar botón de análisis
        analyzeButton.disabled = false;
    });
    
    /**
     * Analizar documento al hacer clic en el botón
     */
    analyzeButton.addEventListener('click', async function() {
        const file = idPhotoInput.files[0];
        
        if (!file) {
            showError('Por favor, selecciona una imagen primero.');
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        analyzeButton.disabled = true;
        analyzeButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analizando...';
        resultDiv.innerHTML = '<div class="alert alert-info">🔍 Analizando documento con IA...</div>';
        
        try {
            // Preparar FormData
            const formData = new FormData();
            formData.append('idPhoto', file);
            
            // Enviar solicitud al backend
            const response = await fetch('/controllers/id_verification/idVerificationController.php', {
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
            analyzeButton.innerHTML = 'Analizar Documento con IA';
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
        
        // Texto completo (colapsable)
        if (data.data.fullText) {
            html += `
                <details class="mt-3">
                    <summary style="cursor: pointer; color: #882A57; font-weight: bold;">Ver texto completo extraído</summary>
                    <pre class="mt-2 p-3" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; white-space: pre-wrap;">${escapeHtml(data.data.fullText)}</pre>
                </details>
            `;
        }
        
        html += '</div></div>';
        
        // Botón para continuar
        if (data.valid) {
            html += `
                <div class="mt-4 text-center">
                    <a href="/views/login/password_reset.php" class="btn btn-primary">
                        Continuar con recuperación de contraseña
                    </a>
                </div>
            `;
        } else {
            html += `
                <div class="mt-4 text-center">
                    <button onclick="location.reload()" class="btn btn-secondary">
                        Intentar de nuevo
                    </button>
                </div>
            `;
        }
        
        resultDiv.innerHTML = html;
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
});
