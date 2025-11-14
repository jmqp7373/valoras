/**
 * Validación del formulario de Crear Ticket
 * ticketCreate.php - Valora.vip
 */

// Validación del formulario
document.addEventListener('DOMContentLoaded', function() {
    const ticketForm = document.getElementById('ticketForm');
    
    if (ticketForm) {
        ticketForm.addEventListener('submit', function(e) {
            const subject = document.getElementById('subject').value.trim();
            const description = document.getElementById('description').value.trim();
            
            // Validar asunto
            if(subject.length < 5) {
                e.preventDefault();
                alert('El asunto debe tener al menos 5 caracteres');
                return false;
            }
            
            // Validar descripción
            if(description.length < 10) {
                e.preventDefault();
                alert('La descripción debe tener al menos 10 caracteres');
                return false;
            }
            
            // Validar archivo si se seleccionó
            const fileInput = document.getElementById('attachment');
            if(fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                
                if(!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Solo se permiten archivos JPG o PNG');
                    return false;
                }
                
                if(file.size > maxSize) {
                    e.preventDefault();
                    alert('El archivo no debe superar los 5MB');
                    return false;
                }
            }
        });
    }
    
    // Contador de caracteres para descripción
    const descriptionField = document.getElementById('description');
    if (descriptionField) {
        descriptionField.addEventListener('input', function() {
            const length = this.value.length;
            const parent = this.parentElement;
            let counter = parent.querySelector('.char-counter');
            
            if(!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter';
                counter.style.float = 'right';
                parent.querySelector('small').after(counter);
            }
            
            counter.textContent = `${length} caracteres`;
            counter.style.color = length >= 10 ? '#28a745' : '#dc3545';
        });
    }
});
