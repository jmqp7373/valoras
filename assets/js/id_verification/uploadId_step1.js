/**
 * JavaScript para Paso 1: Carga de Documentos
 * Maneja la previsualización y envío de fotos de cédula
 * 
 * @author Valora.vip
 * @version 2.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    const inputFront = document.getElementById('idPhotoFront');
    const inputBack = document.getElementById('idPhotoBack');
    const previewFront = document.getElementById('idPreviewFront');
    const previewBack = document.getElementById('idPreviewBack');
    const btnContinue = document.getElementById('analyzeButton');
    
    // Validar que ambos archivos estén seleccionados
    function validateFiles() {
        if (inputFront.files.length > 0 && inputBack.files.length > 0) {
            btnContinue.disabled = false;
        } else {
            btnContinue.disabled = true;
        }
    }
    
    // Previsualizar imagen frontal
    inputFront.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                previewFront.src = event.target.result;
                previewFront.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
        validateFiles();
    });
    
    // Previsualizar imagen trasera
    inputBack.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                previewBack.src = event.target.result;
                previewBack.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
        validateFiles();
    });
    
    // Manejar envío del formulario
    btnContinue.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validar que ambas imágenes estén cargadas
        if (inputFront.files.length === 0 || inputBack.files.length === 0) {
            alert('⚠️ Por favor selecciona ambas fotos de tu cédula');
            return;
        }
        
        // Deshabilitar botón para evitar doble clic
        btnContinue.disabled = true;
        btnContinue.textContent = '⏳ Procesando...';
        
        // Crear FormData con las imágenes
        const formData = new FormData();
        formData.append('idPhotoFront', inputFront.files[0]);
        formData.append('idPhotoBack', inputBack.files[0]);
        
        // Enviar al controlador
        fetch('../../controllers/id_verification/idVerificationController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // El controlador ahora redirige, pero podría devolver JSON en caso de error AJAX
            // Por seguridad, esperamos que redirija automáticamente
            // Si llegamos aquí, verificar si es redirect HTML o JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Es una redirección, seguirla
                window.location.href = '../../views/login/verify2_OCR.php';
                return null;
            }
        })
        .then(data => {
            if (data === null) return; // Ya redirigió
            
            console.log('Respuesta del servidor:', data);
            
            if (data.success) {
                // Redirigir a Paso 2
                window.location.href = 'verify2_OCR.php';
            } else {
                // Mostrar error
                alert('❌ Error: ' + (data.message || data.error || 'No se pudo procesar el documento'));
                btnContinue.disabled = false;
                btnContinue.textContent = 'Continuar al Paso 2: Análisis con IA';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al procesar el documento. Por favor intenta de nuevo.');
            btnContinue.disabled = false;
            btnContinue.textContent = 'Continuar al Paso 2: Análisis con IA';
        });
    });
});
