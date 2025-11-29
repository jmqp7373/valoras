/**
 * Gestión de Estudios - JavaScript
 * Maneja todas las operaciones AJAX para estudios, casas, categorías y clases
 */

const API_URL = '../../controllers/EstudiosController.php';

// Función helper para obtener esAdmin en tiempo de ejecución
function getEsAdmin() {
    return window.ESTUDIOS_CONFIG ? window.ESTUDIOS_CONFIG.esAdmin : false;
}

// Plugin de ordenamiento personalizado para DataTables (por atributo data-order)
$.fn.dataTable.ext.order['dom-data-order'] = function(settings, col) {
    return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
        const badge = $(td).find('span[data-order]');
        return badge.length ? badge.attr('data-order') : '999';
    });
};

// ============================================
// VARIABLES GLOBALES
// ============================================
let tablaCasaEstudios, tablaEstudios, tablaCategorias, tablaClases;
let modalEstudio, modalCasa, modalCategoria, modalClase;
let mostrandoInactivosEstudios = false;
let mostrandoInactivosCasas = false;

// ============================================
// INICIALIZACIÓN
// ============================================
// Verificar que jQuery esté cargado
if (typeof jQuery === 'undefined') {
    console.error('jQuery no está cargado. Verifica que jQuery se cargue antes de este script.');
} else {
    $(document).ready(function() {
        console.log('📊 Inicializando sistema de gestión de estudios...');
        
        // Inicializar modales Bootstrap
        try {
            modalEstudio = new bootstrap.Modal('#modalEstudio');
            modalCasa = new bootstrap.Modal('#modalCasa');
            modalCategoria = new bootstrap.Modal('#modalCategoria');
            modalClase = new bootstrap.Modal('#modalClase');
            console.log('✓ Modales inicializados');
        } catch (error) {
            console.error('Error al inicializar modales:', error);
        }

        // Inicializar DataTables
        try {
            inicializarDataTables();
            console.log('✓ DataTables inicializadas');
        } catch (error) {
            console.error('Error al inicializar DataTables:', error);
        }

        // Cargar datos iniciales (esto inicializará las tablas de categorías y clases)
        try {
            cargarEstudios();
            cargarCasas();
            cargarCategorias();
            cargarClases();
            console.log('✓ Carga de datos iniciada');
        } catch (error) {
            console.error('Error al cargar datos iniciales:', error);
        }

        // Event listeners
        try {
            configurarEventListeners();
            console.log('✓ Event listeners configurados');
        } catch (error) {
            console.error('Error al configurar event listeners:', error);
        }
    });
}

// ============================================
// DATATABLES INICIALIZACIÓN
// ============================================
function inicializarDataTables() {
    const configBase = {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        autoWidth: false,
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    };

    try {
        // Tabla Casa Estudios (casas - 6 columnas: ID, Creación, Estudio, Nombre Casa, Estado, Acciones)
        if ($.fn.DataTable.isDataTable('#tablaCasaEstudios')) {
            $('#tablaCasaEstudios').DataTable().destroy();
        }
        tablaCasaEstudios = $('#tablaCasaEstudios').DataTable({
            ...configBase,
            columnDefs: [
                { targets: 1, type: 'date' },
                { targets: 4, orderDataType: 'dom-data-order' },
                { targets: 5, orderable: false }
            ],
            order: [[4, 'asc'], [1, 'desc']]
        });
        
        // Tabla Estudios (estudios base - 5 columnas: ID, Creación, Nombre, Estado, Acciones)
        if ($.fn.DataTable.isDataTable('#tablaEstudios')) {
            $('#tablaEstudios').DataTable().destroy();
        }
        tablaEstudios = $('#tablaEstudios').DataTable({
            ...configBase,
            columnDefs: [
                { targets: 1, type: 'date' },
                { targets: 3, orderDataType: 'dom-data-order' },
                { targets: 4, orderable: false }
            ],
            order: [[3, 'asc'], [1, 'desc']]
        });
        
        // Tabla Categorías
        if ($.fn.DataTable.isDataTable('#tablaCategorias')) {
            $('#tablaCategorias').DataTable().destroy();
        }
        tablaCategorias = $('#tablaCategorias').DataTable({
            ...configBase,
            columnDefs: [
                { targets: 1, type: 'date' }
            ],
            order: [[1, 'desc']]
        });
        
        // Tabla Clases
        if ($.fn.DataTable.isDataTable('#tablaClases')) {
            $('#tablaClases').DataTable().destroy();
        }
        tablaClases = $('#tablaClases').DataTable({
            ...configBase,
            columnDefs: [
                { targets: 1, type: 'date' }
            ],
            order: [[1, 'desc']]
        });
    } catch (error) {
        console.error('Error inicializando DataTables:', error);
        throw error;
    }
}

// ============================================
// EVENT LISTENERS
// ============================================
function configurarEventListeners() {
    // Estudios
    $('#btnNuevoEstudio').on('click', abrirModalNuevoEstudio);
    $('#btnGuardarEstudio').on('click', guardarEstudio);

    // Casas
    $('#btnNuevaCasa').on('click', abrirModalNuevaCasa);
    $('#btnGuardarCasa').on('click', guardarCasa);

    // Categorías
    $('#btnNuevaCategoria').on('click', abrirModalNuevaCategoria);
    $('#btnGuardarCategoria').on('click', guardarCategoria);

    // Clases
    $('#btnNuevaClase').on('click', abrirModalNuevaClase);
    $('#btnGuardarClase').on('click', guardarClase);

    // Toggle inactivos (solo para Estudios y Casas que tienen estado)
    $('#btnToggleInactivosEstudios').on('click', toggleInactivosEstudios);
    $('#btnToggleInactivosCasas').on('click', toggleInactivosCasas);

    // Historial
    $('#btnFiltrarHistorial').on('click', cargarHistorial);
    $('#btnLimpiarFiltros').on('click', limpiarFiltrosHistorial);

    // Cargar historial cuando se activa el tab
    $('#historial-tab').on('shown.bs.tab', function() {
        cargarHistorial();
    });
}

// ============================================
// ESTUDIOS
// ============================================
function cargarEstudios() {
    console.log('📊 Cargando casas para Casa Estudios...');
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'listar_casas' },
        dataType: 'json',
        success: function(response) {
            console.log('✓ Respuesta de casas:', response);
            if (response.success) {
                actualizarTablaCasaEstudios(response.data);
            } else {
                mostrarError('Error al cargar casas: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al cargar casas:', {xhr, status, error});
            mostrarError('Error de conexión al cargar casas. Por favor, verifica que el servidor esté funcionando.');
        }
    });
}

function actualizarTablaCasaEstudios(casas) {
    tablaCasaEstudios.clear();
    
    // Filtrar según el estado de mostrandoInactivosEstudios
    const casasFiltradas = mostrandoInactivosEstudios 
        ? casas 
        : casas.filter(c => c.estado == 1);
    
    casasFiltradas.forEach(function(casa) {
        const acciones = getEsAdmin() ? `
            <button class="btn btn-sm btn-danger btn-action" onclick="eliminarCasa(${casa.id_estudio_casa}, '${casa.nombre_estudio_casa}')">
                <i class="fas fa-trash"></i>
            </button>
        ` : '<span class="text-muted">Sin permisos</span>';

        // Columna Estudio editable (edita el nombre del estudio padre)
        const estudioHtml = getEsAdmin() && casa.id_estudio
            ? `<span class="editable-nombre" data-id="${casa.id_estudio}" data-tipo="estudio" data-valor="${casa.estudio_nombre || ''}" title="Doble click para editar" style="cursor: pointer;">${casa.estudio_nombre || 'N/A'}</span>`
            : (casa.estudio_nombre || 'N/A');

        const nombreHtml = getEsAdmin() 
            ? `<span class="editable-nombre" data-id="${casa.id_estudio_casa}" data-tipo="casa" data-valor="${casa.nombre_estudio_casa}" title="Doble click para editar" style="cursor: pointer;">${casa.nombre_estudio_casa}</span>`
            : casa.nombre_estudio_casa;

        const estadoBadge = casa.estado == 1 
            ? '<span class="badge bg-success" data-order="0">Activo</span>' 
            : '<span class="badge bg-secondary" data-order="1">Inactivo</span>';
        
        const estadoHtml = getEsAdmin() 
            ? `<span class="editable-estado" data-id="${casa.id_estudio_casa}" data-tipo="casa" data-valor="${casa.estado}" title="Click para cambiar" style="cursor: pointer;">${estadoBadge}</span>`
            : estadoBadge;

        tablaCasaEstudios.row.add([
            casa.id_estudio_casa,
            formatearFecha(casa.fecha_creacion),
            nombreHtml,
            estudioHtml,
            estadoHtml,
            acciones
        ]);
    });
    tablaCasaEstudios.draw();
    
    // Agregar eventos de edición inline
    if (getEsAdmin()) {
        agregarEventosEdicionInline();
    }
}

function actualizarSelectoresEstudios(estudios) {
    // Actualizar selector de casas en el modal (mantiene estudios)
    const selectCasaEstudio = $('#casa_estudio');
    selectCasaEstudio.html('<option value="">Seleccione un estudio</option>');
    
    estudios.forEach(function(estudio) {
        selectCasaEstudio.append(`<option value="${estudio.id_estudio}">${estudio.nombre_estudio}</option>`);
    });
}

function abrirModalNuevoEstudio() {
    $('#formEstudio')[0].reset();
    $('#estudio_id').val('');
    $('#modalEstudioTitulo').text('Nuevo Estudio');
    modalEstudio.show();
}

function guardarEstudio() {
    const formData = new FormData($('#formEstudio')[0]);
    const id = $('#estudio_id').val();
    formData.append('accion', id ? 'actualizar_estudio' : 'crear_estudio');

    $.ajax({
        url: API_URL,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarExito(response.message);
                modalEstudio.hide();
                cargarEstudios();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al guardar estudio');
            console.error(xhr);
        }
    });
}

function eliminarEstudio(id, nombre) {
    Swal.fire({
        title: '¿Eliminar estudio?',
        text: `¿Está seguro de eliminar "${nombre}"? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6A1B1B',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { accion: 'eliminar_estudio', id_estudio: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarExito(response.message);
                        cargarEstudios();
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function(xhr) {
                    mostrarError('Error al eliminar estudio');
                    console.error(xhr);
                }
            });
        }
    });
}

// ============================================
// CASAS
// ============================================
function cargarCasas(idEstudio = '') {
    console.log('📊 Cargando estudios base para Estudios...');
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'listar_estudios' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                actualizarTablaEstudios(response.data);
                actualizarSelectoresEstudios(response.data);
            } else {
                mostrarError('Error al cargar estudios: ' + response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error de conexión al cargar estudios');
            console.error(xhr);
        }
    });
}

function actualizarTablaEstudios(estudios) {
    tablaEstudios.clear();
    
    // Filtrar según el estado de mostrandoInactivosCasas
    const estudiosFiltrados = mostrandoInactivosCasas 
        ? estudios 
        : estudios.filter(e => e.estado == 1);
    
    estudiosFiltrados.forEach(function(estudio) {
        const acciones = getEsAdmin() ? `
            <button class="btn btn-sm btn-danger btn-action" onclick="eliminarEstudio(${estudio.id_estudio}, '${estudio.nombre_estudio}')">
                <i class="fas fa-trash"></i>
            </button>
        ` : '<span class="text-muted">Sin permisos</span>';

        const nombreHtml = getEsAdmin() 
            ? `<span class="editable-nombre" data-id="${estudio.id_estudio}" data-tipo="estudio" data-valor="${estudio.nombre_estudio}" title="Doble click para editar" style="cursor: pointer;">${estudio.nombre_estudio}</span>`
            : estudio.nombre_estudio;

        const estadoBadge = estudio.estado == 1 
            ? '<span class="badge bg-success" data-order="0">Activo</span>' 
            : '<span class="badge bg-secondary" data-order="1">Inactivo</span>';
        
        const estadoHtml = getEsAdmin() 
            ? `<span class="editable-estado" data-id="${estudio.id_estudio}" data-tipo="estudio" data-valor="${estudio.estado}" title="Click para cambiar" style="cursor: pointer;">${estadoBadge}</span>`
            : estadoBadge;

        tablaEstudios.row.add([
            estudio.id_estudio,
            formatearFecha(estudio.fecha_creacion),
            nombreHtml,
            estadoHtml,
            acciones
        ]);
    });
    tablaEstudios.draw();
    
    // Agregar eventos de edición inline
    if (getEsAdmin()) {
        agregarEventosEdicionInline();
    }
}

function abrirModalNuevaCasa() {
    $('#formCasa')[0].reset();
    $('#casa_id').val('');
    $('#modalCasaTitulo').text('Nueva Casa/Plataforma');
    modalCasa.show();
}

function guardarCasa() {
    const formData = new FormData($('#formCasa')[0]);
    const id = $('#casa_id').val();
    formData.append('accion', id ? 'actualizar_casa' : 'crear_casa');

    $.ajax({
        url: API_URL,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarExito(response.message);
                modalCasa.hide();
                cargarCasas();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al guardar casa');
            console.error(xhr);
        }
    });
}

function eliminarCasa(id, nombre) {
    Swal.fire({
        title: '¿Eliminar casa?',
        text: `¿Está seguro de eliminar "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6A1B1B',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { accion: 'eliminar_casa', id_casa: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarExito(response.message);
                        cargarCasas();
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function(xhr) {
                    mostrarError('Error al eliminar casa');
                    console.error(xhr);
                }
            });
        }
    });
}

// ============================================
// CATEGORÍAS
// ============================================
function cargarCategorias() {
    console.log('📊 Cargando categorías...');
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'listar_categorias' },
        dataType: 'json',
        success: function(response) {
            console.log('✓ Respuesta de categorías:', response);
            if (response.success) {
                actualizarTablaCategorias(response.data);
            } else {
                mostrarError('Error al cargar categorías: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('❌ Error al cargar categorías:', xhr);
            mostrarError('Error de conexión al cargar categorías');
        }
    });
}

function actualizarTablaCategorias(categorias) {
    console.log('📝 Actualizando tabla categorías con', categorias?.length || 0, 'registros');
    // Limpiar la tabla existente
    if (tablaCategorias) {
        tablaCategorias.clear();
    }
    
    // Las categorías no tienen columna estado, mostrar todas
    categorias.forEach(function(categoria) {
        const acciones = getEsAdmin() ? `
            <button class="btn btn-sm btn-danger btn-action" onclick="eliminarCategoria(${categoria.id_estudio_categoria}, '${categoria.nombre_estudio_categoria}')">
                <i class="fas fa-trash"></i>
            </button>
        ` : '';

        const nombreHtml = getEsAdmin() 
            ? `<span class="editable-nombre" data-id="${categoria.id_estudio_categoria}" data-tipo="categoria" data-valor="${categoria.nombre_estudio_categoria}" title="Doble click para editar" style="cursor: pointer;">${categoria.nombre_estudio_categoria}</span>`
            : categoria.nombre_estudio_categoria;

        const row = [
            categoria.id_estudio_categoria,
            formatearFecha(categoria.fecha_creacion),
            nombreHtml
        ];

        if (getEsAdmin()) {
            row.push(acciones);
        }

        tablaCategorias.row.add(row);
    });
    tablaCategorias.draw();
    
    // Agregar eventos de edición inline
    if (getEsAdmin()) {
        agregarEventosEdicionInline();
    }
}

function abrirModalNuevaCategoria() {
    $('#formCategoria')[0].reset();
    $('#categoria_id').val('');
    $('#modalCategoriaTitulo').text('Nueva Categoría');
    modalCategoria.show();
}

function guardarCategoria() {
    const formData = new FormData($('#formCategoria')[0]);
    const id = $('#categoria_id').val();
    formData.append('accion', id ? 'actualizar_categoria' : 'crear_categoria');

    $.ajax({
        url: API_URL,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarExito(response.message);
                modalCategoria.hide();
                cargarCategorias();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al guardar categoría');
            console.error(xhr);
        }
    });
}

function eliminarCategoria(id, nombre) {
    Swal.fire({
        title: '¿Eliminar categoría?',
        text: `¿Está seguro de eliminar "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6A1B1B',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { accion: 'eliminar_categoria', id_categoria: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarExito(response.message);
                        cargarCategorias();
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function(xhr) {
                    mostrarError('Error al eliminar categoría');
                    console.error(xhr);
                }
            });
        }
    });
}

// ============================================
// CLASES
// ============================================
function cargarClases() {
    console.log('📊 Cargando clases...');
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'listar_clases' },
        dataType: 'json',
        success: function(response) {
            console.log('✓ Respuesta de clases:', response);
            if (response.success) {
                actualizarTablaClases(response.data);
            } else {
                mostrarError('Error al cargar clases: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('❌ Error al cargar clases:', xhr);
            mostrarError('Error de conexión al cargar clases');
        }
    });
}

function actualizarTablaClases(clases) {
    console.log('📝 Actualizando tabla clases con', clases?.length || 0, 'registros');
    // Limpiar la tabla existente
    if (tablaClases) {
        tablaClases.clear();
    }
    
    // Las clases no tienen columna estado, mostrar todas
    clases.forEach(function(clase) {
        const acciones = getEsAdmin() ? `
            <button class="btn btn-sm btn-danger btn-action" onclick="eliminarClase(${clase.id_estudio_clase}, '${clase.nombre_estudio_clase}')">
                <i class="fas fa-trash"></i>
            </button>
        ` : '';

        const nombreHtml = getEsAdmin() 
            ? `<span class="editable-nombre" data-id="${clase.id_estudio_clase}" data-tipo="clase" data-valor="${clase.nombre_estudio_clase}" title="Doble click para editar" style="cursor: pointer;">${clase.nombre_estudio_clase}</span>`
            : clase.nombre_estudio_clase;

        const row = [
            clase.id_estudio_clase,
            formatearFecha(clase.fecha_creacion),
            nombreHtml
        ];

        if (getEsAdmin()) {
            row.push(acciones);
        }

        tablaClases.row.add(row);
    });
    tablaClases.draw();
    
    // Agregar eventos de edición inline
    if (getEsAdmin()) {
        agregarEventosEdicionInline();
    }
}

function abrirModalNuevaClase() {
    $('#formClase')[0].reset();
    $('#clase_id').val('');
    $('#modalClaseTitulo').text('Nueva Clase');
    modalClase.show();
}

function guardarClase() {
    const formData = new FormData($('#formClase')[0]);
    const id = $('#clase_id').val();
    formData.append('accion', id ? 'actualizar_clase' : 'crear_clase');

    $.ajax({
        url: API_URL,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarExito(response.message);
                modalClase.hide();
                cargarClases();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al guardar clase');
            console.error(xhr);
        }
    });
}

function eliminarClase(id, nombre) {
    Swal.fire({
        title: '¿Eliminar clase?',
        text: `¿Está seguro de eliminar "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6A1B1B',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: API_URL,
                method: 'POST',
                data: { accion: 'eliminar_clase', id_clase: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarExito(response.message);
                        cargarClases();
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function(xhr) {
                    mostrarError('Error al eliminar clase');
                    console.error(xhr);
                }
            });
        }
    });
}

// ============================================
// HISTORIAL
// ============================================
function cargarHistorial() {
    const tabla = $('#filtroHistorialTabla').val();
    const tipoAccion = $('#filtroHistorialAccion').val();

    const params = { accion: 'historial' };
    if (tabla) params.tabla = tabla;
    if (tipoAccion) params.tipo_accion = tipoAccion;

    $.ajax({
        url: API_URL,
        method: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarHistorial(response.data);
            } else {
                $('#contenedorHistorial').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No se pudo cargar el historial</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#contenedorHistorial').html('<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No hay cambios registrados aún. Los cambios aparecerán aquí cuando se creen, editen o eliminen estudios, casas, categorías o clases.</div>');
        }
    });
}

function mostrarHistorial(registros) {
    const contenedor = $('#contenedorHistorial');
    
    if (registros.length === 0) {
        contenedor.html('<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No hay cambios registrados con los filtros seleccionados. Los cambios aparecerán aquí cuando se creen, editen o eliminen registros.</div>');
        return;
    }

    let html = '';
    registros.forEach(function(registro) {
        const badgeColor = {
            'INSERT': 'success',
            'UPDATE': 'warning',
            'DELETE': 'danger'
        }[registro.accion] || 'secondary';
        
        const badgeText = {
            'INSERT': 'CREACIÓN',
            'UPDATE': 'ACTUALIZACIÓN',
            'DELETE': 'ELIMINACIÓN'
        }[registro.accion] || registro.accion;

        const usuario = registro.nombres && registro.apellidos ? 
            `${registro.nombres} ${registro.apellidos}` : 
            registro.usuario || 'Sistema';
        
        // Mapear nombres de tablas a nombres legibles
        const tablaNombre = {
            'estudios': 'Estudio',
            'estudios_casas': 'Casa',
            'estudios_categorias': 'Categoría',
            'estudios_clases': 'Clase'
        }[registro.tabla_afectada] || registro.tabla_afectada;

        html += `
            <div class="audit-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-${badgeColor} audit-badge">${badgeText}</span>
                        <strong>${tablaNombre}</strong> #${registro.id_registro}
                    </div>
                    <small class="text-muted">${new Date(registro.fecha_modificacion).toLocaleString('es-ES')}</small>
                </div>
                <p class="mb-1">${registro.descripcion || 'Sin descripción'}</p>
                <p class="mb-1"><small><strong>Usuario:</strong> ${usuario} | <strong>IP:</strong> ${registro.ip_usuario || 'N/A'}</small></p>
        `;

        // Parsear JSON si viene como string
        let datosAnteriores = registro.datos_anteriores;
        let datosNuevos = registro.datos_nuevos;
        
        try {
            if (typeof datosAnteriores === 'string') {
                datosAnteriores = JSON.parse(datosAnteriores);
            }
        } catch (e) {
            console.error('Error parseando datos_anteriores:', e);
        }
        
        try {
            if (typeof datosNuevos === 'string') {
                datosNuevos = JSON.parse(datosNuevos);
            }
        } catch (e) {
            console.error('Error parseando datos_nuevos:', e);
        }

        if (datosAnteriores || datosNuevos) {
            html += '<div class="row mt-2">';
            
            if (datosAnteriores) {
                html += `
                    <div class="col-md-6">
                        <small class="text-muted"><strong>Antes:</strong></small>
                        <pre class="json-diff" style="background:#f8f9fa; padding:8px; border-radius:4px; font-size:12px;">${JSON.stringify(datosAnteriores, null, 2)}</pre>
                    </div>
                `;
            }
            
            if (datosNuevos) {
                html += `
                    <div class="col-md-6">
                        <small class="text-muted"><strong>Después:</strong></small>
                        <pre class="json-diff" style="background:#f8f9fa; padding:8px; border-radius:4px; font-size:12px;">${JSON.stringify(datosNuevos, null, 2)}</pre>
                    </div>
                `;
            }
            
            html += '</div>';
        }

        html += '</div><hr>';
    });

    contenedor.html(html);
}

function limpiarFiltrosHistorial() {
    $('#filtroHistorialTabla').val('');
    $('#filtroHistorialAccion').val('');
    cargarHistorial();
}

// ============================================
// UTILIDADES
// ============================================
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    const mes = meses[date.getMonth()];
    const año = date.getFullYear();
    const horas = String(date.getHours()).padStart(2, '0');
    const minutos = String(date.getMinutes()).padStart(2, '0');
    
    return `${dia}/${mes}/${año} ${horas}:${minutos}`;
}

function mostrarExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: mensaje,
        timer: 3000,
        showConfirmButton: false,
        confirmButtonColor: '#6A1B1B'
    });
}

function mostrarError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonColor: '#6A1B1B',
        confirmButtonText: 'Entendido'
    });
}

// ============================================
// EDICIÓN INLINE
// ============================================
function agregarEventosEdicionInline() {
    // Editar nombre con doble click
    $(document).off('dblclick', '.editable-nombre').on('dblclick', '.editable-nombre', function() {
        const $span = $(this);
        const $tr = $span.closest('tr');
        const id = $span.data('id');
        const tipo = $span.data('tipo');
        const valorActual = $span.data('valor');
        
        const $input = $('<input>')
            .attr('type', 'text')
            .addClass('form-control form-control-sm')
            .val(valorActual)
            .css({
                'width': '200px',
                'display': 'inline-block'
            });
        
        $span.replaceWith($input);
        $input.focus().select();
        
        // Guardar al perder foco o presionar Enter
        $input.on('blur keypress', function(e) {
            if (e.type === 'blur' || e.key === 'Enter') {
                const nuevoValor = $(this).val().trim();
                if (nuevoValor && nuevoValor !== valorActual) {
                    // Iluminar la fila ANTES de guardar
                    $tr.addClass('row-saved');
                    guardarEdicionInline(id, tipo, 'nombre', nuevoValor, $tr);
                } else {
                    // Restaurar span original
                    $(this).replaceWith($span);
                }
            }
        });
        
        // Cancelar con Escape
        $input.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).replaceWith($span);
            }
        });
    });
    
    // Cambiar estado con un click
    $(document).off('click', '.editable-estado').on('click', '.editable-estado', function() {
        const $span = $(this);
        const $tr = $span.closest('tr');
        const id = $span.data('id');
        const tipo = $span.data('tipo');
        const estadoActual = parseInt($span.data('valor'));
        const nuevoEstado = estadoActual === 1 ? 0 : 1;
        
        // Iluminar la fila ANTES de guardar
        $tr.addClass('row-saved');
        guardarEdicionInline(id, tipo, 'estado', nuevoEstado, $tr);
    });
}

function guardarEdicionInline(id, tipo, campo, valor, $tr) {
    const accionMap = {
        'estudio': 'actualizar_estudio_inline',
        'casa': 'actualizar_casa_inline',
        'categoria': 'actualizar_categoria_inline',
        'clase': 'actualizar_clase_inline'
    };
    
    $.ajax({
        url: API_URL,
        method: 'POST',
        data: {
            accion: accionMap[tipo],
            id: id,
            campo: campo,
            valor: valor
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Mostrar indicador de guardado
                mostrarIndicadorGuardado('✓ Guardado correctamente');
                
                // Mantener el verde por 2 segundos antes de recargar
                setTimeout(() => {
                    // Recargar solo la tabla correspondiente
                    switch(tipo) {
                        case 'estudio':
                            cargarEstudios();
                            break;
                        case 'casa':
                            cargarCasas();
                            break;
                        case 'categoria':
                            cargarCategorias();
                            break;
                        case 'clase':
                            cargarClases();
                            break;
                    }
                }, 2000);
            } else {
                // Quitar el verde si hay error
                if ($tr) {
                    $tr.removeClass('row-saved');
                }
                mostrarError(response.message || 'Error al actualizar');
                // Recargar para restaurar valor original
                switch(tipo) {
                    case 'estudio':
                        cargarEstudios();
                        break;
                    case 'casa':
                        cargarCasas();
                        break;
                    case 'categoria':
                        cargarCategorias();
                        break;
                    case 'clase':
                        cargarClases();
                        break;
                }
            }
        },
        error: function() {
            // Quitar el verde si hay error
            if ($tr) {
                $tr.removeClass('row-saved');
            }
            mostrarError('Error de conexión al actualizar');
        }
    });
}

function mostrarIndicadorGuardado(mensaje) {
    // Crear indicador si no existe
    let indicador = document.getElementById('autosave-indicator');
    if (!indicador) {
        indicador = document.createElement('div');
        indicador.id = 'autosave-indicator';
        indicador.className = 'autosave-indicator';
        document.body.appendChild(indicador);
    }
    
    indicador.textContent = mensaje;
    indicador.className = 'autosave-indicator show success';
    
    // Ocultar después de 2 segundos
    setTimeout(() => {
        indicador.classList.remove('show');
    }, 2000);
}

// ============================================
// TOGGLE INACTIVOS EN CASA ESTUDIOS
// ============================================
function toggleInactivosEstudios() {
    mostrandoInactivosEstudios = !mostrandoInactivosEstudios;
    const btn = $('#btnToggleInactivosEstudios');
    const icono = btn.find('i');
    
    if (mostrandoInactivosEstudios) {
        // Cambiar a "Ocultar Inactivos"
        icono.removeClass('fa-eye-slash').addClass('fa-eye');
        btn.html('<i class="fas fa-eye" style="font-size: 0.9rem; margin-right: 6px;"></i>Ocultar Inactivos');
    } else {
        // Cambiar a "Mostrar Inactivos"
        icono.removeClass('fa-eye').addClass('fa-eye-slash');
        btn.html('<i class="fas fa-eye-slash" style="font-size: 0.9rem; margin-right: 6px;"></i>Mostrar Inactivos');
    }
    
    // Recargar la tabla con el filtro actualizado
    cargarEstudios();
}

// ============================================
// TOGGLE INACTIVOS EN ESTUDIOS (TAB 2)
// ============================================
function toggleInactivosCasas() {
    mostrandoInactivosCasas = !mostrandoInactivosCasas;
    const btn = $('#btnToggleInactivosCasas');
    const icono = btn.find('i');
    
    if (mostrandoInactivosCasas) {
        icono.removeClass('fa-eye-slash').addClass('fa-eye');
        btn.html('<i class="fas fa-eye" style="font-size: 0.9rem; margin-right: 6px;"></i>Ocultar Inactivos');
    } else {
        icono.removeClass('fa-eye').addClass('fa-eye-slash');
        btn.html('<i class="fas fa-eye-slash" style="font-size: 0.9rem; margin-right: 6px;"></i>Mostrar Inactivos');
    }
    
    cargarCasas();
}
