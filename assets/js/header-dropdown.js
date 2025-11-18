/**
 * Header Dropdown Menu Handler
 * Maneja el comportamiento del menú de usuario con submenús en cascada
 * Requiere: Bootstrap 5.3+
 */

(function() {
    'use strict';
    
    // Función de inicialización
    function initDropdownMenu() {
        console.log('🔧 Inicializando menú de usuario...');
        
        // Verificar que Bootstrap esté disponible
        if (typeof bootstrap === 'undefined') {
            console.error('❌ Bootstrap no está cargado. El menú de usuario no funcionará correctamente.');
            return;
        }
        
        console.log('✓ Bootstrap detectado correctamente');
        
        // Obtener el botón del menú principal
        const userMenuButton = document.getElementById('userMenuDropdown');
        if (!userMenuButton) {
            console.warn('⚠️ No se encontró el botón del menú de usuario (#userMenuDropdown)');
            return;
        }
        
        console.log('✓ Botón del menú encontrado');
        
        // Verificar que Bootstrap Dropdown esté disponible
        if (typeof bootstrap.Dropdown === 'undefined') {
            console.error('❌ Bootstrap Dropdown no está disponible');
            return;
        }
        
        // Obtener o crear instancia de Bootstrap Dropdown
        let dropdownInstance = bootstrap.Dropdown.getInstance(userMenuButton);
        if (!dropdownInstance) {
            try {
                dropdownInstance = new bootstrap.Dropdown(userMenuButton, {
                    autoClose: true,
                    boundary: 'viewport'
                });
                console.log('✓ Nueva instancia de Dropdown creada');
            } catch (error) {
                console.error('❌ Error al crear instancia de dropdown:', error);
                return;
            }
        } else {
            console.log('✓ Instancia de Dropdown ya existe');
        }
        
        // Manejar los submenús en cascada
        const dropdownSubmenus = document.querySelectorAll('.dropdown-submenu');
        console.log(`📋 Submenús encontrados: ${dropdownSubmenus.length}`);
        
        dropdownSubmenus.forEach(function(submenu, index) {
            const toggle = submenu.querySelector('.dropdown-toggle');
            const submenuDropdown = submenu.querySelector('.dropdown-menu');
            
            if (toggle && submenuDropdown) {
                console.log(`✓ Configurando submenú ${index + 1}`);
                
                // Desktop: mostrar en hover
                submenu.addEventListener('mouseenter', function() {
                    if (window.innerWidth > 768) {
                        submenuDropdown.classList.add('show');
                    }
                });
                
                submenu.addEventListener('mouseleave', function() {
                    if (window.innerWidth > 768) {
                        submenuDropdown.classList.remove('show');
                    }
                });
                
                // Mobile: toggle en click
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        e.stopPropagation();
                        submenuDropdown.classList.toggle('show');
                    }
                });
            }
        });
        
        // Cerrar submenús cuando se cierra el menú principal
        userMenuButton.addEventListener('hidden.bs.dropdown', function() {
            console.log('🔒 Menú principal cerrado, cerrando submenús');
            document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(submenu) {
                submenu.classList.remove('show');
            });
        });
        
        // Log cuando el menú se abre
        userMenuButton.addEventListener('shown.bs.dropdown', function() {
            console.log('✓ Menú principal abierto');
        });
        
        // Log cuando el menú se oculta
        userMenuButton.addEventListener('hide.bs.dropdown', function() {
            console.log('🔒 Menú principal cerrándose');
        });
        
        console.log('✅ Menú de usuario inicializado correctamente');
    }
    
    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdownMenu);
    } else {
        // El DOM ya está listo, ejecutar inmediatamente
        initDropdownMenu();
    }
})();
