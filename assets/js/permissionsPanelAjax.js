/**
 * JavaScript para carga AJAX optimizada del panel de permisos
 * Proyecto: Valora.vip
 * Fecha: 2025-11-09
 */

// Configuración
const COLORES_ROLES = [
    '#faf5f7',
    '#f9f9f9',
    '#fdfcfa',
    '#f9f9f9',
    '#faf5f7'
];

const ICONOS_CATEGORIA = {
    'admin': 'bi-shield-lock-fill',
    'checksTests': 'bi-bug-fill',
    'login': 'bi-door-open-fill',
    'finanzas': 'bi-currency-dollar',
    'ventas': 'bi-graph-up-arrow',
    'tickets': 'bi-ticket-perforated-fill',
    'usuario': 'bi-person-fill'
};

// Variables globales
let rolesData = [];
let modulosData = [];
let permisosData = {};
let mostrarExentos = false; // Ocultar exentos por defecto

// Función principal de carga
async function cargarPermisosAjax() {
    console.log('🚀 Iniciando carga AJAX de permisos...');
    const inicio = performance.now();
    
    try {
        const response = await fetch('../../controllers/PermisosApiController.php');
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error al cargar permisos');
        }
        
        console.log('✅ Datos recibidos:', {
            roles: data.roles.length,
            modulos: data.modulos.length,
            permisos: Object.keys(data.permisos).length
        });
        
        console.log('📦 Sample de módulos:', data.modulos.slice(0, 2));
        console.log('📦 Sample de permisos:', data.permisos);
        
        // Guardar datos globales
        rolesData = data.roles;
        modulosData = data.modulos;
        permisosData = data.permisos;
        
        // Renderizar tabla
        renderizarTabla();
        
        // Inicializar event listeners
        inicializarEventListeners();
        
        // Ocultar loading
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
        
        const fin = performance.now();
        console.log(`⚡ Tabla renderizada en ${(fin - inicio).toFixed(2)}ms`);
        
        // Mostrar estadísticas
        mostrarEstadisticas(data);
        
    } catch (error) {
        console.error('❌ Error al cargar permisos:', error);
        mostrarError('Error al cargar los permisos. Por favor, recarga la página.');
        
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.innerHTML = `
                <div class="text-center" style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem; color: #dc3545;"></i>
                    <p class="mt-3 mb-3 fw-semibold" style="color: #dc3545;">Error al cargar los datos</p>
                    <button class="btn btn-primary" onclick="location.reload()" style="background: linear-gradient(135deg, #6A1B1B, #882A57); border: none;">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reintentar
                    </button>
                </div>
            `;
        }
    }
}

// Renderizar toda la tabla
function renderizarTabla() {
    const tbody = document.getElementById('tbodyPermisos');
    if (!tbody) return;
    
    let html = '';
    let contadorOcultos = 0;
    
    modulosData.forEach((modulo, index) => {
        // Normalizar exento a número
        const esExento = parseInt(modulo.exento) === 1;
        
        // Si el módulo es exento y están ocultos, saltarlo
        if (esExento && !mostrarExentos) {
            contadorOcultos++;
            return;
        }
        
        html += generarFilaModulo(modulo, index);
    });
    
    tbody.innerHTML = html;
    
    console.log(`📊 Renderizado: ${modulosData.length - contadorOcultos} módulos mostrados, ${contadorOcultos} ocultos`);
}

// Generar HTML de una fila de módulo
function generarFilaModulo(modulo, index) {
    const { clave, rutaMostrar, nombreDescriptivo, categoria, archivoExiste, exento, icono } = modulo;
    
    // Normalizar exento a booleano para comparaciones
    const esExento = parseInt(exento) === 1;
    
    // Clase especial para filas exentas
    const claseExento = esExento ? 'exento-row' : '';
    
    let html = `<tr data-modulo-clave="${escapeHtml(clave)}" class="${claseExento}">`;
    
    // ====== COLUMNA DE CATEGORÍA ======
    const estiloCategoria = esExento 
        ? "min-width: 120px; max-width: 120px; padding: 14px; background: linear-gradient(135deg, #4a4a4a, #6a6a6a); color: white; position: sticky; left: 0; z-index: 3; text-align: center; opacity: 0.7; border-right: 2px solid rgba(255,255,255,0.2);"
        : "min-width: 120px; max-width: 120px; padding: 14px; background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; position: sticky; left: 0; z-index: 3; text-align: center; border-right: 2px solid rgba(255,255,255,0.2);";
    
    html += `
        <td class="align-middle text-center" style="${estiloCategoria}">
            <span class="badge badge-${categoria.toLowerCase()}" style="font-size: 0.75rem; padding: 6px 12px; font-weight: 600; text-transform: uppercase;">
                ${categoria.toUpperCase()}
            </span>
        </td>
    `;
    
    // ====== COLUMNA DE ARCHIVO/RUTA (con icono integrado) ======
    const iconoMostrar = icono || '❓';
    const estiloColumna = esExento 
        ? "min-width: 400px; max-width: 500px; padding: 14px; background: linear-gradient(135deg, #4a4a4a, #6a6a6a); color: white; position: sticky; left: 120px; z-index: 2; text-align: left; opacity: 0.7;"
        : "min-width: 400px; max-width: 500px; padding: 14px; background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; position: sticky; left: 120px; z-index: 2; text-align: left;";
    
    html += `
        <td class="fw-semibold align-middle" style="${estiloColumna}">
            <div class="modulo-info">
                ${esExento ? '<span class="badge bg-secondary me-2" style="flex-shrink: 0;">EXENTO</span>' : ''}
                <div style="display: flex; align-items: center; flex: 1; gap: 8px;">
                    <div class="icono-editable" 
                         data-clave="${escapeHtml(clave)}"
                         style="font-size: 1.5rem; cursor: pointer; transition: all 0.2s ease; user-select: none; flex-shrink: 0;"
                         title="Click para cambiar icono">
                        ${iconoMostrar}
                    </div>
                    <div style="flex: 1;">
    `;
    
    // Si el archivo no existe físicamente, mostrar alerta y botón eliminar
    if (archivoExiste === false) {
        html += `
                        <div class="archivo-renombrado" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 2px; color: #ff6b6b; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                            <div style="flex: 1;">
                                <i class="bi bi-file-earmark-x-fill me-1"></i>
                                ⚠️ Archivo Renombrado o Eliminado
                            </div>
                            <button class="eliminar-modulo-btn" 
                                    data-clave="${escapeHtml(clave)}"
                                    style="background: none; border: none; padding: 4px; cursor: pointer; color: #d1d5db; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; border-radius: 6px;"
                                    title="Marcar como eliminado permanentemente"
                                    onmouseover="this.style.backgroundColor='rgba(255,107,107,0.15)'; this.style.color='#ff6b6b';"
                                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#d1d5db';">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="display: block;">
                                    <path d="M11 1.75V3h2.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75ZM4.496 6.675l.66 6.6a.25.25 0 0 0 .249.225h5.19a.25.25 0 0 0 .249-.225l.66-6.6a.75.75 0 0 1 1.492.149l-.66 6.6A1.748 1.748 0 0 1 10.595 15h-5.19a1.748 1.748 0 0 1-1.741-1.575l-.66-6.6a.75.75 0 1 1 1.492-.15ZM6.5 1.75V3h3V1.75a.25.25 0 0 0-.25-.25h-2.5a.25.25 0 0 0-.25.25Z"></path>
                                </svg>
                            </button>
                        </div>
                        <div style="font-size: 0.72rem; font-family: 'Courier New', monospace; opacity: 0.7;" title="${escapeHtml(modulo.ruta)}">
                            Último nombre conocido: ${escapeHtml(rutaMostrar)}
                        </div>
        `;
    } else if (nombreDescriptivo) {
        // Edición inline: input en lugar de div estático
        html += `
                        <input type="text" 
                               class="form-control nombre-descriptivo-input"
                               data-clave="${escapeHtml(clave)}"
                               value="${escapeHtml(nombreDescriptivo)}"
                               style="font-size: 0.95rem; font-weight: 600; margin-bottom: 2px; background: transparent; border: 1px solid transparent; color: white; padding: 4px 8px; border-radius: 4px;"
                               title="Haz clic para editar. Presiona Enter o pierde el foco para guardar.">
                        <div style="font-size: 0.72rem; font-family: 'Courier New', monospace; opacity: 0.7;" title="${escapeHtml(modulo.ruta)}">
                            ${escapeHtml(rutaMostrar)}
                        </div>
        `;
    } else {
        // Sin nombre descriptivo: mostrar texto estático en lugar de placeholder
        html += `
                        <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 2px; color: #ffc107;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Sin Nombre Descriptivo
                        </div>
                        <input type="text" 
                               class="form-control sin-nombre-input"
                               data-clave="${escapeHtml(clave)}"
                               value=""
                               placeholder="Escribe un nombre descriptivo..."
                               style="font-size: 0.85rem; font-weight: 600; margin-top: 4px; background: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107; color: white; padding: 4px 8px; border-radius: 4px;"
                               title="Haz clic para agregar un nombre descriptivo. Presiona Enter o pierde el foco para guardar.">
                        <div style="font-size: 0.72rem; font-family: 'Courier New', monospace; opacity: 0.7; margin-top: 2px;" title="${escapeHtml(modulo.ruta)}">
                            ${escapeHtml(rutaMostrar)}
                        </div>
        `;
    }
    
    html += `
                    </div>
                    <!-- Botón para marcar/desmarcar como exento -->
                    <button class="toggle-exento-btn" 
                            data-clave="${escapeHtml(clave)}"
                            data-exento="${esExento ? '1' : '0'}"
                            style="background: none; border: none; padding: 4px; cursor: pointer; color: ${esExento ? '#ffc107' : '#d1d5db'}; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; border-radius: 6px; margin-top: 8px;"
                            title="${esExento ? 'Marcar como NO exento' : 'Marcar como exento'}"
                            onmouseover="this.style.backgroundColor='rgba(255,193,7,0.15)'; this.style.color='#ffc107';"
                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='${esExento ? '#ffc107' : '#d1d5db'}';">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="display: block;">
                            <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM2.04 4.326c.325 1.329 2.532 2.54 3.717 3.19.48.263.793.434.743.484-.08.08-.162.158-.242.234-.416.396-.787.749-.758 1.266.035.634.618.824 1.214 1.017.577.188 1.168.38 1.286.983.082.417-.075.988-.22 1.52-.215.782-.406 1.48.22 1.48 1.5-.5 3.798-3.186 4-5 .138-1.243-2-2-3.5-2.5-.478-.16-.755.081-.99.284-.172.15-.322.279-.51.216-.445-.148-2.5-2-1.5-2.5.78-.39.952-.171 1.227.182.078.099.163.208.273.318.609.304.662-.132.723-.633.039-.322.081-.671.277-.867.434-.434 1.265-.791 2.028-1.12.712-.306 1.365-.587 1.579-.88A7 7 0 1 0 2.04 4.327z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </td>
    `;
    
    // Columnas de permisos por rol
    rolesData.forEach((rol, idx) => {
        const bgColor = COLORES_ROLES[idx % 5];
        const permisoModulo = (permisosData[rol.id] && permisosData[rol.id][clave]) || {
            puede_ver: 0,
            puede_editar: 0,
            puede_eliminar: 0
        };
        
        // Si es exento, deshabilitar checkboxes
        const deshabilitado = esExento ? 'disabled' : '';
        const estiloDeshabilitado = esExento ? 'opacity: 0.4; cursor: not-allowed;' : 'cursor: pointer;';
        
        // Ver
        html += `
            <td class="text-center align-middle rol-data-cell rol-col-${rol.id}" style="padding: 10px; border-left: 2px solid #dee2e6; background-color: ${bgColor};">
                <input type="checkbox" 
                       class="form-check-input permiso-checkbox" 
                       data-rol-id="${rol.id}"
                       data-modulo="${escapeHtml(clave)}"
                       data-permiso="ver"
                       title="${permisoModulo.puede_ver ? '✔ Permitido' : '✖ Denegado'}"
                       ${permisoModulo.puede_ver ? 'checked' : ''}
                       ${deshabilitado}
                       style="width: 20px; height: 20px; ${estiloDeshabilitado}">
            </td>
        `;
        
        // Editar
        html += `
            <td class="text-center align-middle rol-data-cell rol-col-${rol.id}" style="padding: 10px; background-color: ${bgColor};">
                <input type="checkbox" 
                       class="form-check-input permiso-checkbox"
                       data-rol-id="${rol.id}"
                       data-modulo="${escapeHtml(clave)}"
                       data-permiso="editar"
                       title="${permisoModulo.puede_editar ? '✔ Permitido' : '✖ Denegado'}"
                       ${permisoModulo.puede_editar ? 'checked' : ''}
                       ${deshabilitado}
                       style="width: 20px; height: 20px; ${estiloDeshabilitado}">
            </td>
        `;
        
        // Eliminar
        html += `
            <td class="text-center align-middle rol-data-cell rol-col-${rol.id} d-none d-md-table-cell" style="padding: 10px; background-color: ${bgColor};">
                <input type="checkbox" 
                       class="form-check-input permiso-checkbox"
                       data-rol-id="${rol.id}"
                       data-modulo="${escapeHtml(clave)}"
                       data-permiso="eliminar"
                       title="${permisoModulo.puede_eliminar ? '✔ Permitido' : '✖ Denegado'}"
                       ${permisoModulo.puede_eliminar ? 'checked' : ''}
                       ${deshabilitado}
                       style="width: 20px; height: 20px; ${estiloDeshabilitado}">
            </td>
        `;
    });
    
    html += '</tr>';
    return html;
}

// Inicializar event listeners
function inicializarEventListeners() {
    // Event listener para inputs de nombre descriptivo (inline editing)
    document.querySelectorAll('.nombre-descriptivo-input, .sin-nombre-input').forEach(input => {
        // Guardar al perder foco
        input.addEventListener('blur', function() {
            guardarNombreDescriptivo(this);
        });
        
        // Guardar al presionar Enter
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur(); // Trigger blur event
            }
        });
        
        // Efecto visual al hacer focus
        input.addEventListener('focus', function() {
            this.style.background = 'rgba(255, 255, 255, 0.15)';
            this.style.borderColor = '#fff';
        });
        
        input.addEventListener('blur', function() {
            this.style.background = 'transparent';
            this.style.borderColor = 'transparent';
        });
    });
    
    // Event listener para botones de eliminar módulo
    document.querySelectorAll('.eliminar-modulo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const clave = this.getAttribute('data-clave');
            marcarModuloEliminado(clave);
        });
    });
    
    // Event listener para botones de toggle exento
    document.querySelectorAll('.toggle-exento-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const clave = this.getAttribute('data-clave');
            const exentoActual = this.getAttribute('data-exento') === '1';
            toggleExentoModulo(clave, exentoActual);
        });
    });
    
    // Event listener para checkboxes de permisos
    document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const rolId = this.getAttribute('data-rol-id');
            const modulo = this.getAttribute('data-modulo');
            const permiso = this.getAttribute('data-permiso');
            const valor = this.checked ? 1 : 0;
            
            actualizarPermiso(rolId, modulo, permiso, valor, this);
        });
    });
    
    // Event listener para edición de iconos
    document.querySelectorAll('.icono-editable').forEach(div => {
        div.addEventListener('click', function() {
            const clave = this.getAttribute('data-clave');
            mostrarSelectorIcono(clave, this);
        });
    });
    
    console.log('✅ Event listeners inicializados');
    console.log('   - Inputs nombre:', document.querySelectorAll('.nombre-descriptivo-input, .sin-nombre-input').length);
    console.log('   - Checkboxes permisos:', document.querySelectorAll('.permiso-checkbox').length);
    console.log('   - Iconos editables:', document.querySelectorAll('.icono-editable').length);
}

// Función para toggle de exentos (separada para evitar duplicación de listeners)
function toggleExentos() {
    mostrarExentos = !mostrarExentos;
    
    const btnToggleExentos = document.getElementById('btnToggleExentos');
    if (btnToggleExentos) {
        // Actualizar texto del botón
        const icono = mostrarExentos ? 'bi-eye-fill' : 'bi-eye-slash-fill';
        const texto = mostrarExentos ? 'Ocultar Exentos' : 'Mostrar Exentos';
        btnToggleExentos.innerHTML = `<i class="bi ${icono}" style="font-size: 0.9rem; margin-right: 6px;"></i>${texto}`;
    }
    
    console.log('🔄 Toggle exentos:', mostrarExentos ? 'MOSTRANDO' : 'OCULTANDO');
    
    // Re-renderizar tabla
    renderizarTabla();
    inicializarEventListeners();
}

// Guardar nombre descriptivo inline
async function guardarNombreDescriptivo(inputElement) {
    const clave = inputElement.getAttribute('data-clave');
    const nuevoNombre = inputElement.value.trim();
    
    // Buscar el nombre original en modulosData
    const moduloOriginal = modulosData.find(m => m.clave === clave);
    const nombreOriginal = moduloOriginal ? moduloOriginal.nombreDescriptivo : '';
    
    // Si no cambió, no hacer nada
    if (nuevoNombre === nombreOriginal) {
        return;
    }
    
    console.log('💾 Guardando nombre descriptivo:', { clave, nuevoNombre });
    
    // Deshabilitar input temporalmente
    inputElement.disabled = true;
    const valorAnterior = inputElement.value;
    
    try {
        const formData = new FormData();
        formData.append('clave', clave);
        formData.append('titulo', nuevoNombre);
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../../controllers/ModulosController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Nombre actualizado correctamente');
            
            // Actualizar datos globales
            if (moduloOriginal) {
                moduloOriginal.nombreDescriptivo = nuevoNombre;
            }
            
            // Cambiar clase del input si ahora tiene nombre
            if (nuevoNombre && inputElement.classList.contains('sin-nombre-input')) {
                inputElement.classList.remove('sin-nombre-input');
                inputElement.classList.add('nombre-descriptivo-input');
                inputElement.style.background = 'transparent';
                inputElement.style.border = '1px solid transparent';
                inputElement.style.color = 'white';
                inputElement.removeAttribute('placeholder');
            }
            
            mostrarMensajeExito('✓ Nombre actualizado');
            
        } else {
            throw new Error(data.message || 'Error al actualizar');
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        
        // Revertir valor
        inputElement.value = valorAnterior;
        
        mostrarError('Error al actualizar el nombre: ' + error.message);
        
    } finally {
        // Rehabilitar input
        inputElement.disabled = false;
    }
}

// Marcar módulo como eliminado
async function marcarModuloEliminado(clave) {
    // Confirmar acción
    if (!confirm('¿Estás seguro de marcar este módulo como eliminado? Esta acción lo ocultará permanentemente del panel.')) {
        return;
    }
    
    console.log('🗑️ Marcando módulo como eliminado:', clave);
    
    try {
        const formData = new FormData();
        formData.append('accion', 'marcar_eliminado');
        formData.append('clave', clave);
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../../controllers/ModulosController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Módulo marcado como eliminado correctamente');
            
            // Eliminar la fila de la tabla con animación
            const fila = document.querySelector(`tr[data-modulo-clave="${clave}"]`);
            if (fila) {
                fila.style.transition = 'opacity 0.3s, transform 0.3s';
                fila.style.opacity = '0';
                fila.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    fila.remove();
                    
                    // Actualizar datos globales
                    modulosData = modulosData.filter(m => m.clave !== clave);
                    
                    mostrarMensajeExito('✓ Módulo eliminado');
                }, 300);
            }
            
        } else {
            throw new Error(data.message || 'Error al eliminar módulo');
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        mostrarError('Error al eliminar el módulo: ' + error.message);
    }
}

// Toggle estado exento de un módulo
async function toggleExentoModulo(clave, exentoActual) {
    const nuevoEstado = exentoActual ? 0 : 1;
    const accion = nuevoEstado ? 'exento' : 'no exento';
    
    console.log(`🔄 Cambiando módulo ${clave} a ${accion}`);
    
    try {
        const formData = new FormData();
        formData.append('accion', 'toggle_exento');
        formData.append('clave', clave);
        formData.append('exento', nuevoEstado);
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../../controllers/ModulosController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log(`✅ Módulo marcado como ${accion} correctamente`);
            
            // Actualizar el módulo en los datos globales
            const modulo = modulosData.find(m => m.clave === clave);
            if (modulo) {
                modulo.exento = nuevoEstado;
            }
            
            // Recargar la tabla para reflejar los cambios
            renderizarTabla();
            inicializarEventListeners();
            
            mostrarMensajeExito(`✓ Módulo marcado como ${accion}`);
            
        } else {
            throw new Error(data.message || 'Error al cambiar estado exento');
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        mostrarError('Error al cambiar estado exento: ' + error.message);
    }
}

// Mostrar modal de edición (ELIMINADO - YA NO SE USA)

// Actualizar permiso vía AJAX
async function actualizarPermiso(rolId, modulo, permiso, valor, checkbox) {
    console.log('🔄 Actualizando permiso:', { rolId, modulo, permiso, valor });
    
    // Deshabilitar checkbox temporalmente
    checkbox.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('action', 'guardarPermisoRol');
        formData.append('id_rol', rolId);
        formData.append('modulo', modulo);
        formData.append('permiso', permiso);
        formData.append('valor', valor);
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../../controllers/PermissionsController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Permiso actualizado correctamente');
            
            // Actualizar tooltip
            const nuevoTitulo = valor ? '✔ Permitido' : '✖ Denegado';
            checkbox.setAttribute('title', nuevoTitulo);
            
            // Actualizar datos globales
            if (!permisosData[rolId]) {
                permisosData[rolId] = {};
            }
            if (!permisosData[rolId][modulo]) {
                permisosData[rolId][modulo] = {};
            }
            
            const campoPermiso = 'puede_' + permiso;
            permisosData[rolId][modulo][campoPermiso] = valor;
            
            // Mostrar mensaje de éxito sutil
            mostrarMensajeExito('✓ Permiso actualizado');
            
        } else {
            throw new Error(data.message || 'Error al actualizar');
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        
        // Revertir checkbox
        checkbox.checked = !checkbox.checked;
        
        mostrarError('Error al actualizar el permiso: ' + error.message);
        
    } finally {
        // Rehabilitar checkbox
        checkbox.disabled = false;
    }
}

// Mostrar mensaje de éxito
function mostrarMensajeExito(mensaje) {
    const alerta = document.createElement('div');
    alerta.className = 'alert alert-success alert-dismissible fade show';
    alerta.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 250px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);';
    alerta.innerHTML = `
        <i class="bi bi-check-circle-fill me-2"></i>
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alerta);
    
    setTimeout(() => alerta.remove(), 2000);
}

// Escape HTML para prevenir XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? String(text).replace(/[&<>"']/g, m => map[m]) : '';
}

// Mostrar error
function mostrarError(mensaje) {
    const alerta = document.createElement('div');
    alerta.className = 'alert alert-danger alert-dismissible fade show';
    alerta.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);';
    alerta.innerHTML = `
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alerta);
    
    setTimeout(() => alerta.remove(), 5000);
}

// Mostrar selector de iconos
function mostrarSelectorIcono(clave, divIcono) {
    // Lista extendida de 250+ iconos organizados por categorías
    const iconosDisponibles = [
        // Seguridad y Acceso (20)
        '🔐', '🔑', '🔒', '🔓', '🔏', '🗝️', '🛡️', '🚪', '🚫', '⛔',
        '🔞', '🆔', '🎫', '🏷️', '🔖', '📛', '⚠️', '🚨', '🚦', '🔱',
        
        // Personas y Roles (20)
        '👤', '👥', '👨‍💼', '👩‍💼', '👑', '👮', '🧑‍💻', '👨‍🔧', '👩‍🔬', '🧑‍🎨',
        '👨‍🏫', '👩‍⚕️', '🧑‍🍳', '👨‍🌾', '👩‍✈️', '🧑‍🚀', '🦸', '🧙', '🤵', '👰',
        
        // Comunicación (20)
        '📧', '✉️', '📨', '📩', '📮', '📪', '📫', '📬', '📭', '📯',
        '📞', '☎️', '📱', '📲', '📟', '📠', '💬', '💭', '🗨️', '🗯️',
        
        // Documentos y Archivos (25)
        '📄', '📃', '📑', '📜', '📋', '📊', '📈', '📉', '📝', '📁',
        '📂', '🗂️', '🗃️', '🗄️', '📕', '📗', '📘', '📙', '📚', '📓',
        '📔', '📒', '📰', '🗞️', '📑',
        
        // Finanzas y Dinero (20)
        '💰', '💵', '💴', '💶', '💷', '🪙', '💸', '💳', '🏧', '💹',
        '📊', '📈', '📉', '💼', '🏦', '🤑', '💲', '💱', '🧾', '🪪',
        
        // Tecnología (25)
        '💻', '🖥️', '⌨️', '🖱️', '🖨️', '💾', '💿', '📀', '🎮', '🕹️',
        '📡', '🔌', '🔋', '🪫', '📶', '📳', '📴', '🔆', '🔅', '💡',
        '🔦', '🕯️', '🪔', '📻', '📺',
        
        // Herramientas y Configuración (20)
        '⚙️', '🛠️', '🔧', '🔨', '⚒️', '🛁', '⛏️', '🪛', '🔩', '🗜️',
        '⚡', '🔥', '💧', '🌊', '🧰', '🪓', '⚔️', '🗡️', '🏹', '🪃',
        
        // Organización y Gestión (20)
        '📅', '📆', '🗓️', '📇', '🗒️', '📌', '📍', '📎', '🖇️', '📏',
        '📐', '✂️', '🖊️', '🖍️', '✏️', '📝', '✍️', '🖌️', '🖍️', '📏',
        
        // Navegación y Ubicación (15)
        '🧭', '🗺️', '🌐', '🌍', '🌎', '🌏', '🏠', '🏢', '🏛️', '🏗️',
        '🏭', '🏪', '🏬', '🏦', '🏛️',
        
        // Símbolos y Estados (20)
        '✅', '❌', '✔️', '✖️', '❗', '❓', '⁉️', '‼️', '⭐', '🌟',
        '✨', '💫', '🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '⚫', '⚪',
        
        // Multimedia y Arte (20)
        '🎨', '🖼️', '🎭', '🎪', '🎬', '🎤', '🎧', '🎼', '🎵', '🎶',
        '🎹', '🥁', '🎷', '🎺', '🎸', '🪕', '🎻', '📷', '📸', '📹',
        
        // Educación y Ciencia (20)
        '📚', '📖', '📕', '📗', '📘', '📙', '🎓', '🔬', '🔭', '⚗️',
        '🧪', '🧬', '🔢', '➕', '➖', '✖️', '➗', '🔣', '💯', '📐',
        
        // Premios y Logros (15)
        '🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '🎗️', '🎁', '🎀', '🎊',
        '🎉', '🎈', '🎂', '🍾', '🥂',
        
        // Tiempo y Clima (10)
        '⏰', '⏱️', '⏲️', '⏳', '⌛', '🕐', '🕑', '🕒', '🕓', '🕔',
        
        // Transporte (10)
        '🚀', '✈️', '🚁', '🚂', '🚗', '🚕', '🚙', '🚌', '🚎', '🏎️',
        
        // Comida y Bebida (10)
        '🍕', '🍔', '🍟', '🌭', '🥪', '🌮', '🌯', '🥙', '🍿', '🧈'
    ];
    
    // Crear modal simple con SweetAlert2 si está disponible
    if (typeof Swal !== 'undefined') {
        const html = `
            <div style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 6px; max-width: 600px; max-height: 400px; overflow-y: auto; margin: 0 auto; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                ${iconosDisponibles.map(icono => `
                    <button type="button" 
                            class="btn-icono-selector" 
                            data-icono="${icono}"
                            style="font-size: 1.3rem; padding: 6px; border: 2px solid #dee2e6; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;"
                            onmouseover="this.style.borderColor='#6A1B1B'; this.style.backgroundColor='#f8f9fa'; this.style.transform='scale(1.15)';"
                            onmouseout="this.style.borderColor='#dee2e6'; this.style.backgroundColor='white'; this.style.transform='scale(1)';">
                        ${icono}
                    </button>
                `).join('')}
            </div>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #dee2e6;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #6A1B1B;">
                    O escribe un emoji personalizado:
                </label>
                <input type="text" 
                       id="inputIconoPersonalizado" 
                       maxlength="2"
                       placeholder="Pega aquí un emoji..."
                       style="width: 100%; padding: 10px; border: 2px solid #dee2e6; border-radius: 8px; font-size: 1.2rem; text-align: center;">
            </div>
        `;
        
        Swal.fire({
            title: 'Selecciona un Icono',
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Usar Personalizado',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary',
                actions: 'swal2-actions-custom'
            },
            buttonsStyling: false,
            width: 700,
            didOpen: () => {
                // Event listeners para los botones de iconos
                document.querySelectorAll('.btn-icono-selector').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const icono = this.getAttribute('data-icono');
                        Swal.close();
                        guardarIcono(clave, icono, divIcono);
                    });
                });
            },
            preConfirm: () => {
                const input = document.getElementById('inputIconoPersonalizado');
                const icono = input.value.trim();
                if (icono) {
                    return icono;
                } else {
                    Swal.showValidationMessage('Por favor escribe un emoji');
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                guardarIcono(clave, result.value, divIcono);
            }
        });
    } else {
        // Fallback sin SweetAlert2: prompt simple
        const nuevoIcono = prompt('Escribe un emoji para usar como icono:', divIcono.textContent.trim());
        if (nuevoIcono && nuevoIcono.trim()) {
            guardarIcono(clave, nuevoIcono.trim(), divIcono);
        }
    }
}

// Guardar icono seleccionado
async function guardarIcono(clave, icono, divIcono) {
    console.log('💾 Guardando icono:', { clave, icono });
    
    const iconoAnterior = divIcono.textContent.trim();
    
    // Actualizar UI optimísticamente
    divIcono.textContent = icono;
    divIcono.style.opacity = '0.5';
    
    try {
        const formData = new FormData();
        formData.append('accion', 'actualizar_icono');
        formData.append('clave', clave);
        formData.append('icono', icono);
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../../controllers/ModulosController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Icono actualizado correctamente');
            
            // Actualizar datos globales
            const modulo = modulosData.find(m => m.clave === clave);
            if (modulo) {
                modulo.icono = icono;
            }
            
            divIcono.style.opacity = '1';
            mostrarMensajeExito('✓ Icono actualizado');
            
        } else {
            throw new Error(data.message || 'Error al actualizar');
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        
        // Revertir icono
        divIcono.textContent = iconoAnterior;
        divIcono.style.opacity = '1';
        
        mostrarError('Error al actualizar el icono: ' + error.message);
    }
}

// Mostrar estadísticas en consola
function mostrarEstadisticas(data) {
    const conNombre = modulosData.filter(m => m.nombreDescriptivo).length;
    const sinNombre = modulosData.length - conNombre;
    
    console.log('📊 Estadísticas:');
    console.log('   - Total módulos:', modulosData.length);
    console.log('   - Con nombre descriptivo:', conNombre);
    console.log('   - Sin nombre descriptivo:', sinNombre);
    console.log('   - Total roles:', rolesData.length);
}

// Cargar al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    console.log('📄 DOM cargado, iniciando carga AJAX...');
    
    // Event listener para botón toggle (solo una vez)
    const btnToggleExentos = document.getElementById('btnToggleExentos');
    if (btnToggleExentos) {
        btnToggleExentos.addEventListener('click', toggleExentos);
        console.log('✅ Event listener de toggle exentos registrado');
    }
    
    cargarPermisosAjax();
});
