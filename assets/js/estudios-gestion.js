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
let tablaEstudios, tablaCasas, tablaCategorias, tablaClases;
let modalEstudio, modalCasa, modalCategoria, modalClase;
let mostrandoInactivosEstudios = false;

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
        // Tabla Estudios
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
        
        // Tabla Casas
        if ($.fn.DataTable.isDataTable('#tablaCasas')) {
            $('#tablaCasas').DataTable().destroy();
        }
        tablaCasas = $('#tablaCasas').DataTable({
            ...configBase,
            columnDefs: [
                { targets: 1, type: 'date' },
                { targets: 4, orderDataType: 'dom-data-order' },
                { targets: 5, orderable: false }
            ],
            order: [[4, 'asc'], [1, 'desc']]
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
    $('#filtroCasaEstudio').on('change', function() {
        const idEstudio = $(this).val();
        cargarCasas(idEstudio);
    });

    // Categorías
    $('#btnNuevaCategoria').on('click', abrirModalNuevaCategoria);
    $('#btnGuardarCategoria').on('click', guardarCategoria);

    // Clases
    $('#btnNuevaClase').on('click', abrirModalNuevaClase);
    $('#btnGuardarClase').on('click', guardarClase);

    // Toggle inactivos en Casa Estudios
    $('#btnToggleInactivosEstudios').on('click', toggleInactivosEstudios);

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
    console.log('📊 Cargando estudios...');
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'listar_estudios' },
        dataType: 'json',
        success: function(response) {
            console.log('✓ Respuesta de estudios:', response);
            if (response.success) {
                actualizarTablaEstudios(response.data);
                actualizarSelectoresEstudios(response.data);
            } else {
                mostrarError('Error al cargar estudios: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al cargar estudios:', {xhr, status, error});
            mostrarError('Error de conexión al cargar estudios. Por favor, verifica que el servidor esté funcionando.');
        }
    });
}

function actualizarTablaEstudios(estudios) {
    tablaEstudios.clear();
    
    // Filtrar según el estado de mostrandoInactivosEstudios
    const estudiosFiltrados = mostrandoInactivosEstudios 
        ? estudios 
        : estudios.filter(e => e.estado == 1);
    
    estudiosFiltrados.forEach(function(estudio) {
        const acciones = getEsAdmin() ? `
            <button class="btn btn-sm btn-warning btn-action" onclick="editarEstudio(${estudio.id_estudio})">
                <i class="fas fa-edit"></i>
            </button>
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

function actualizarSelectoresEstudios(estudios) {
    // Actualizar selector de casas
    const selectCasaEstudio = $('#casa_estudio');
    const selectFiltro = $('#filtroCasaEstudio');
    
    selectCasaEstudio.html('<option value="">Seleccione un estudio</option>');
    selectFiltro.html('<option value="">Todos los estudios</option>');
    
    estudios.forEach(function(estudio) {
        selectCasaEstudio.append(`<option value="${estudio.id_estudio}">${estudio.nombre_estudio}</option>`);
        selectFiltro.append(`<option value="${estudio.id_estudio}">${estudio.nombre_estudio}</option>`);
    });
}

function abrirModalNuevoEstudio() {
    $('#formEstudio')[0].reset();
    $('#estudio_id').val('');
    $('#modalEstudioTitulo').text('Nuevo Estudio');
    modalEstudio.show();
}

function editarEstudio(id) {
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'obtener_estudio', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const estudio = response.data;
                $('#estudio_id').val(estudio.id_estudio);
                $('#estudio_nombre').val(estudio.nombre);
                $('#estudio_descripcion').val(estudio.descripcion);
                $('#modalEstudioTitulo').text('Editar Estudio');
                modalEstudio.show();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al cargar datos del estudio');
            console.error(xhr);
        }
    });
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
    const params = { accion: 'listar_casas' };
    if (idEstudio) {
        params.id_estudio = idEstudio;
    }

    $.ajax({
        url: API_URL,
        method: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                actualizarTablaCasas(response.data);
            } else {
                mostrarError('Error al cargar casas: ' + response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error de conexión al cargar casas');
            console.error(xhr);
        }
    });
}

function actualizarTablaCasas(casas) {
    tablaCasas.clear();
    casas.forEach(function(casa) {
        const acciones = `
            <button class="btn btn-sm btn-warning btn-action" onclick="editarCasa(${casa.id_estudio_casa})">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-action" onclick="eliminarCasa(${casa.id_estudio_casa}, '${casa.nombre_estudio_casa}')">
                <i class="fas fa-trash"></i>
            </button>
        `;

        tablaCasas.row.add([
            casa.id_estudio_casa,
            formatearFecha(casa.fecha_creacion),
            casa.estudio_nombre || 'N/A',
            casa.nombre_estudio_casa,
            casa.estado == 1 ? '<span class="badge bg-success" data-order="0">Activo</span>' : '<span class="badge bg-secondary" data-order="1">Inactivo</span>',
            acciones
        ]);
    });
    tablaCasas.draw();
}

function abrirModalNuevaCasa() {
    $('#formCasa')[0].reset();
    $('#casa_id').val('');
    $('#modalCasaTitulo').text('Nueva Casa/Plataforma');
    modalCasa.show();
}

function editarCasa(id) {
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'obtener_casa', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const casa = response.data;
                $('#casa_id').val(casa.id_casa);
                $('#casa_estudio').val(casa.id_estudio);
                $('#casa_nombre').val(casa.nombre_casa);
                $('#casa_url').val(casa.url_casa);
                $('#modalCasaTitulo').text('Editar Casa/Plataforma');
                modalCasa.show();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al cargar datos de la casa');
            console.error(xhr);
        }
    });
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
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'listar_categorias' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                actualizarTablaCategorias(response.data);
            } else {
                mostrarError('Error al cargar categorías: ' + response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error de conexión al cargar categorías');
            console.error(xhr);
        }
    });
}

function actualizarTablaCategorias(categorias) {
    // Inicializar DataTable si no existe
    if (!$.fn.DataTable.isDataTable('#tablaCategorias')) {
        tablaCategorias = $('#tablaCategorias').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            responsive: true,
            autoWidth: false,
            order: [[1, 'desc']]
        });
    } else if (tablaCategorias) {
        tablaCategorias.clear();
    }
    categorias.forEach(function(categoria) {
        const acciones = getEsAdmin() ? `
            <button class="btn btn-sm btn-warning btn-action" onclick="editarCategoria(${categoria.id_estudio_categoria})">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-action" onclick="eliminarCategoria(${categoria.id_estudio_categoria}, '${categoria.nombre_estudio_categoria}')">
                <i class="fas fa-trash"></i>
            </button>
        ` : '';

        const row = [
            categoria.id_estudio_categoria,
            formatearFecha(categoria.fecha_creacion),
            categoria.nombre_estudio_categoria
        ];

        if (getEsAdmin()) {
            row.push(acciones);
        }

        tablaCategorias.row.add(row);
    });
    tablaCategorias.draw();
}

function abrirModalNuevaCategoria() {
    $('#formCategoria')[0].reset();
    $('#categoria_id').val('');
    $('#modalCategoriaTitulo').text('Nueva Categoría');
    modalCategoria.show();
}

function editarCategoria(id) {
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'obtener_categoria', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const categoria = response.data;
                $('#categoria_id').val(categoria.id_categoria);
                $('#categoria_nombre').val(categoria.nombre_categoria);
                $('#categoria_descripcion').val(categoria.descripcion);
                $('#modalCategoriaTitulo').text('Editar Categoría');
                modalCategoria.show();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al cargar datos de la categoría');
            console.error(xhr);
        }
    });
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
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'listar_clases' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                actualizarTablaClases(response.data);
            } else {
                mostrarError('Error al cargar clases: ' + response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error de conexión al cargar clases');
            console.error(xhr);
        }
    });
}

function actualizarTablaClases(clases) {
    // Inicializar DataTable si no existe
    if (!$.fn.DataTable.isDataTable('#tablaClases')) {
        tablaClases = $('#tablaClases').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            responsive: true,
            autoWidth: false,
            order: [[1, 'desc']]
        });
    } else if (tablaClases) {
        tablaClases.clear();
    }
    clases.forEach(function(clase) {
        const acciones = getEsAdmin() ? `
            <button class="btn btn-sm btn-warning btn-action" onclick="editarClase(${clase.id_estudio_clase})">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-action" onclick="eliminarClase(${clase.id_estudio_clase}, '${clase.nombre_estudio_clase}')">
                <i class="fas fa-trash"></i>
            </button>
        ` : '';

        const row = [
            clase.id_estudio_clase,
            formatearFecha(clase.fecha_creacion),
            clase.nombre_estudio_clase
        ];

        if (getEsAdmin()) {
            row.push(acciones);
        }

        tablaClases.row.add(row);
    });
    tablaClases.draw();
}

function abrirModalNuevaClase() {
    $('#formClase')[0].reset();
    $('#clase_id').val('');
    $('#modalClaseTitulo').text('Nueva Clase');
    modalClase.show();
}

function editarClase(id) {
    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { accion: 'obtener_clase', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const clase = response.data;
                $('#clase_id').val(clase.id_clase);
                $('#clase_nombre').val(clase.nombre_clase);
                $('#clase_descripcion').val(clase.descripcion);
                $('#modalClaseTitulo').text('Editar Clase');
                modalClase.show();
            } else {
                mostrarError(response.message);
            }
        },
        error: function(xhr) {
            mostrarError('Error al cargar datos de la clase');
            console.error(xhr);
        }
    });
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
            console.error('Error al cargar historial:', {xhr, status, error});
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

        const usuario = registro.nombres && registro.apellidos ? 
            `${registro.nombres} ${registro.apellidos}` : 
            registro.usuario || 'Sistema';

        html += `
            <div class="audit-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-${badgeColor} audit-badge">${registro.accion}</span>
                        <strong>${registro.tabla_afectada}</strong> #${registro.id_registro}
                    </div>
                    <small class="text-muted">${new Date(registro.fecha_modificacion).toLocaleString('es-ES')}</small>
                </div>
                <p class="mb-1">${registro.descripcion || 'Sin descripción'}</p>
                <p class="mb-1"><small><strong>Usuario:</strong> ${usuario} | <strong>IP:</strong> ${registro.ip_usuario || 'N/A'}</small></p>
        `;

        if (registro.datos_anteriores_json || registro.datos_nuevos_json) {
            html += '<div class="row mt-2">';
            
            if (registro.datos_anteriores_json) {
                html += `
                    <div class="col-md-6">
                        <small class="text-muted">Datos anteriores:</small>
                        <div class="json-diff">${JSON.stringify(registro.datos_anteriores_json, null, 2)}</div>
                    </div>
                `;
            }
            
            if (registro.datos_nuevos_json) {
                html += `
                    <div class="col-md-6">
                        <small class="text-muted">Datos nuevos:</small>
                        <div class="json-diff">${JSON.stringify(registro.datos_nuevos_json, null, 2)}</div>
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
