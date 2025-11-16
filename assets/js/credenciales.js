/**
 * Script AJAX para Administración de Credenciales
 * Proyecto: Valora.vip
 * Autor: Sistema Valora
 * Fecha: 2025-11-15
 */

document.addEventListener('DOMContentLoaded', function () {
    inicializarFiltrosCredenciales();
    cargarCredenciales(); // Cargar página 1 por defecto
});

/**
 * Inicializar event listeners de filtros
 */
function inicializarFiltrosCredenciales() {
    const filtroModelo = document.getElementById('filtro_modelo');
    const selects = [
        'filtro_plataforma',
        'filtro_estudio',
        'filtro_casa',
        'filtro_cuenta_estudio',
        'filtro_estado'
    ];

    // Input de búsqueda con debounce
    let debounceTimer;
    if (filtroModelo) {
        filtroModelo.addEventListener('keyup', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                cargarCredenciales(1);
            }, 500); // Esperar 500ms después de que el usuario deje de escribir
        });
    }

    // Selects con cambio inmediato
    selects.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                cargarCredenciales(1);
            });
        }
    });
}

/**
 * Obtener parámetros de filtros actuales
 */
function obtenerParametrosFiltros(pagina = 1) {
    return {
        modelo: document.getElementById('filtro_modelo')?.value || '',
        plataforma: document.getElementById('filtro_plataforma')?.value || '',
        estudio: document.getElementById('filtro_estudio')?.value || '',
        casa: document.getElementById('filtro_casa')?.value || '',
        cuenta_estudio: document.getElementById('filtro_cuenta_estudio')?.value || '',
        estado: document.getElementById('filtro_estado')?.value || 'activas',
        pagina: pagina
    };
}

/**
 * Cargar credenciales con filtros y paginación
 */
function cargarCredenciales(pagina = 1) {
    const params = obtenerParametrosFiltros(pagina);
    const queryString = new URLSearchParams(params).toString();
    const url = '../../controllers/CredencialesController.php?action=listarAjax&' + queryString;

    // Mostrar indicador de carga
    const tbody = document.querySelector('#tabla-credenciales tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="11" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando credenciales...</p>
            </td>
        </tr>
    `;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(json => {
            if (json.success) {
                renderizarTablaCredenciales(json.data);
                renderizarPaginacion(json.pagina_actual, json.total_paginas);
                actualizarBadgeTotalRegistros(json.total_registros);
            } else {
                mostrarError(json.message || 'Error al cargar credenciales');
            }
        })
        .catch(error => {
            console.error('Error cargando credenciales:', error);
            mostrarError('Error de conexión. Por favor, intente nuevamente.');
        });
}

/**
 * Renderizar filas de la tabla
 */
function renderizarTablaCredenciales(filas) {
    const tbody = document.querySelector('#tabla-credenciales tbody');
    tbody.innerHTML = '';

    if (!filas || filas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No se encontraron credenciales con los filtros seleccionados.
                </td>
            </tr>
        `;
        return;
    }

    filas.forEach(fila => {
        const tr = document.createElement('tr');
        
        const nombreModelo = ((fila.nombre || '') + ' ' + (fila.apellidos || '')).trim();
        const estadoTexto = fila.eliminado == 1 ? 'Eliminada' : 'Activa';
        const estadoBadge = fila.eliminado == 1 ? 'badge bg-danger' : 'badge bg-success';
        const colorPagina = fila.color_pagina || '#6c757d';
        const fechaCreacion = fila.fecha_creacion ? new Date(fila.fecha_creacion).toLocaleDateString('es-ES') : 'N/A';

        tr.innerHTML = `
            <td>
                <strong>${escapeHtml(nombreModelo)}</strong>
            </td>
            <td>
                <span class="badge" style="background-color: ${escapeHtml(colorPagina)}">
                    ${escapeHtml(fila.nombre_pagina || 'N/A')}
                </span>
            </td>
            <td>
                <code class="text-primary">${escapeHtml(fila.usuario_credencial || 'N/A')}</code>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <span class="password-mask font-monospace" data-password="${escapeHtml(fila.password || '')}">••••••••</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-password" title="Ver contraseña">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </td>
            <td>
                <small class="text-muted">${escapeHtml(fila.email_de_registro || 'N/A')}</small>
            </td>
            <td>${escapeHtml(fila.usuario_cuenta_estudio || 'N/A')}</td>
            <td>${escapeHtml(fila.nombre_estudio || 'N/A')}</td>
            <td>${escapeHtml(fila.nombre_estudio_casa || 'N/A')}</td>
            <td><small>${fechaCreacion}</small></td>
            <td><span class="${estadoBadge}">${estadoTexto}</span></td>
            <td class="text-center">
                <button 
                    type="button" 
                    class="btn btn-sm btn-info btn-detalle" 
                    data-id="${fila.id_credencial}"
                    data-bs-toggle="modal" 
                    data-bs-target="#modalDetalleCredencial"
                    title="Ver detalle">
                    <i class="bi bi-info-circle"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });

    // Agregar listeners a botones de toggle password
    document.querySelectorAll('.btn-toggle-password').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const maskSpan = this.previousElementSibling;
            const password = maskSpan.getAttribute('data-password');
            const icon = this.querySelector('i');
            
            if (maskSpan.textContent === '••••••••') {
                maskSpan.textContent = password;
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                this.title = 'Ocultar contraseña';
            } else {
                maskSpan.textContent = '••••••••';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                this.title = 'Ver contraseña';
            }
        });
    });

    // Agregar listeners a botones de detalle
    document.querySelectorAll('.btn-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            const idCredencial = this.getAttribute('data-id');
            cargarDetalleCredencial(idCredencial);
        });
    });
}

/**
 * Renderizar paginación
 */
function renderizarPaginacion(paginaActual, totalPaginas) {
    const container = document.getElementById('paginacion-credenciales');
    container.innerHTML = '';

    if (totalPaginas <= 1) {
        return;
    }

    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center mb-0';

    // Botón "Anterior"
    const liPrev = document.createElement('li');
    liPrev.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
    liPrev.innerHTML = `
        <a class="page-link" href="#" data-page="${paginaActual - 1}">
            <i class="bi bi-chevron-left"></i> Anterior
        </a>
    `;
    ul.appendChild(liPrev);

    // Páginas numeradas (mostrar máximo 7 botones)
    const maxBotones = 7;
    let inicio = Math.max(1, paginaActual - Math.floor(maxBotones / 2));
    let fin = Math.min(totalPaginas, inicio + maxBotones - 1);

    if (fin - inicio < maxBotones - 1) {
        inicio = Math.max(1, fin - maxBotones + 1);
    }

    if (inicio > 1) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
        ul.appendChild(li);

        if (inicio > 2) {
            const liDots = document.createElement('li');
            liDots.className = 'page-item disabled';
            liDots.innerHTML = `<span class="page-link">...</span>`;
            ul.appendChild(liDots);
        }
    }

    for (let i = inicio; i <= fin; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        ul.appendChild(li);
    }

    if (fin < totalPaginas) {
        if (fin < totalPaginas - 1) {
            const liDots = document.createElement('li');
            liDots.className = 'page-item disabled';
            liDots.innerHTML = `<span class="page-link">...</span>`;
            ul.appendChild(liDots);
        }

        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#" data-page="${totalPaginas}">${totalPaginas}</a>`;
        ul.appendChild(li);
    }

    // Botón "Siguiente"
    const liNext = document.createElement('li');
    liNext.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
    liNext.innerHTML = `
        <a class="page-link" href="#" data-page="${paginaActual + 1}">
            Siguiente <i class="bi bi-chevron-right"></i>
        </a>
    `;
    ul.appendChild(liNext);

    container.appendChild(ul);

    // Event listeners para los botones de página
    ul.querySelectorAll('a.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.parentElement.classList.contains('disabled') && !this.parentElement.classList.contains('active')) {
                const page = parseInt(this.getAttribute('data-page'));
                if (page > 0 && page <= totalPaginas) {
                    cargarCredenciales(page);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    });
}

/**
 * Actualizar badge de total de registros
 */
function actualizarBadgeTotalRegistros(total) {
    const badge = document.getElementById('badge-total-registros');
    if (badge) {
        badge.textContent = `${total} registro${total !== 1 ? 's' : ''}`;
    }
}

/**
 * Cargar detalle de credencial en modal
 */
function cargarDetalleCredencial(idCredencial) {
    const modalBody = document.getElementById('modal-body-detalle');
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando detalle...</p>
        </div>
    `;

    // Por ahora mostrar mensaje de funcionalidad en desarrollo
    setTimeout(() => {
        modalBody.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Funcionalidad en desarrollo</strong><br>
                El detalle completo de la credencial (ID: ${idCredencial}) estará disponible próximamente.
            </div>
        `;
    }, 500);
}

/**
 * Mostrar error en la tabla
 */
function mostrarError(mensaje) {
    const tbody = document.querySelector('#tabla-credenciales tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="11" class="text-center py-4">
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    ${escapeHtml(mensaje)}
                </div>
            </td>
        </tr>
    `;
}

/**
 * Escapar HTML para prevenir XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text || '').replace(/[&<>"']/g, m => map[m]);
}
