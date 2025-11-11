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
let mostrarExentos = true; // Control de visibilidad de módulos exentos

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
    const { clave, rutaMostrar, nombreDescriptivo, categoria, archivoExiste, exento } = modulo;
    
    // Normalizar exento a booleano para comparaciones
    const esExento = parseInt(exento) === 1;
    
    // Clase especial para filas exentas
    const claseExento = esExento ? 'exento-row' : '';
    
    let html = `<tr data-modulo-clave="${escapeHtml(clave)}" class="${claseExento}">`;
    
    // Columna de archivo/ruta
    const estiloColumna = esExento 
        ? "min-width: 400px; max-width: 500px; padding: 14px; background: linear-gradient(135deg, #4a4a4a, #6a6a6a); color: white; position: sticky; left: 0; z-index: 2; text-align: left; opacity: 0.7;"
        : "min-width: 400px; max-width: 500px; padding: 14px; background: linear-gradient(135deg, #6A1B1B, #882A57); color: white; position: sticky; left: 0; z-index: 2; text-align: left;";
    
    html += `
        <td class="fw-semibold align-middle" style="${estiloColumna}">
            <div class="modulo-info">
                <span class="badge badge-${categoria.toLowerCase()} me-2" style="flex-shrink: 0;" title="Categoría: ${categoria}">
                    ${categoria.toUpperCase()}
                </span>
                ${esExento ? '<span class="badge bg-secondary me-2" style="flex-shrink: 0;">EXENTO</span>' : ''}
                <div style="display: flex; align-items: center; flex: 1; gap: 8px;">
                    <div style="flex: 1;">
    `;
    
    // Si el archivo no existe físicamente, mostrar alerta de archivo renombrado
    if (archivoExiste === false) {
        html += `
                        <div class="archivo-renombrado" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 2px; color: #ff6b6b;">
                            <i class="bi bi-file-earmark-x-fill me-1"></i>
                            ⚠️ Archivo Renombrado o Eliminado
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
    
    console.log('✅ Event listeners inicializados');
    console.log('   - Inputs nombre:', document.querySelectorAll('.nombre-descriptivo-input, .sin-nombre-input').length);
    console.log('   - Checkboxes permisos:', document.querySelectorAll('.permiso-checkbox').length);
}

// Función para toggle de exentos (separada para evitar duplicación de listeners)
function toggleExentos() {
    mostrarExentos = !mostrarExentos;
    
    const btnToggleExentos = document.getElementById('btnToggleExentos');
    if (btnToggleExentos) {
        // Actualizar texto del botón
        const icono = mostrarExentos ? 'bi-eye-slash-fill' : 'bi-eye-fill';
        const texto = mostrarExentos ? 'Ocultar Exentos' : 'Mostrar Exentos';
        btnToggleExentos.innerHTML = `<i class="bi ${icono} me-2"></i>${texto}`;
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
        formData.append('nombre_descriptivo', nuevoNombre);
        
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
