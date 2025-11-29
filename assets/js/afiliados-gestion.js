// Gestión de Afiliados - DataTables
console.log('Cargando afiliados-gestion.js...');

// Función para decodificar caracteres especiales mal codificados
function fixEncoding(str) {
    if (!str) return str;
    
    // Reemplazos comunes de codificación incorrecta
    let fixed = str;
    fixed = fixed.replace(/Ã¡/g, 'á');
    fixed = fixed.replace(/Ã©/g, 'é');
    fixed = fixed.replace(/Ã­/g, 'í');
    fixed = fixed.replace(/Ã³/g, 'ó');
    fixed = fixed.replace(/Ãº/g, 'ú');
    fixed = fixed.replace(/Ã±/g, 'ñ');
    fixed = fixed.replace(/Ã¼/g, 'ü');
    fixed = fixed.replace(/Â¿/g, '¿');
    fixed = fixed.replace(/Â¡/g, '¡');
    
    return fixed;
}

// Esperar a que el DOM esté listo
$(document).ready(function() {
    console.log('DOM listo, jQuery version:', $.fn.jquery);
    console.log('DataTables disponible:', typeof $.fn.DataTable);
    
    // Configuración común de idioma
    const idiomaES = {
        processing: "Procesando...",
        search: "Buscar:",
        lengthMenu: "Mostrar _MENU_ registros",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        loadingRecords: "Cargando...",
        zeroRecords: "No se encontraron registros",
        emptyTable: "No hay datos disponibles",
        paginate: {
            first: "Primero",
            previous: "Anterior",
            next: "Siguiente",
            last: "Último"
        }
    };
    
    // TABLA DE MODELOS
    console.log('Inicializando tabla de modelos...');
    const tablaModelos = $('#tablaModelos').DataTable({
        processing: true,
        ajax: {
            url: '../../controllers/AfiliadosController.php?accion=obtenerModelos',
            type: 'GET',
            dataSrc: function(json) {
                console.log('Respuesta modelos:', json);
                return json.data || [];
            },
            error: function(xhr, error, code) {
                console.error('Error AJAX modelos:', {xhr, error, code});
                alert('Error cargando modelos: ' + error);
            }
        },
        columns: [
            { data: 'id_usuario' },
            { data: 'usuario' },
            { data: 'nombres' },
            { data: 'apellidos' },
            { data: 'celular', defaultContent: '-' },
            { data: 'email', defaultContent: '-' },
            { data: 'casa_nombre', defaultContent: '-' },
            { data: 'estudio_nombre', defaultContent: '-' },
            { 
                data: 'fecha_creacion',
                defaultContent: '-',
                render: function(data) {
                    if (!data) return '-';
                    return new Date(data).toLocaleDateString('es-ES');
                }
            }
        ],
        language: idiomaES,
        pageLength: 25,
        order: [[0, 'desc']],
        createdRow: function(row, data, dataIndex) {
            // Agregar data attributes con la información adicional
            $(row).attr('data-usuario-info', JSON.stringify(data));
            $(row).addClass('usuario-row');
            $(row).css('cursor', 'pointer');
        }
    });
    
    // Click en fila para mostrar modal
    $('#tablaModelos').on('click', 'tbody tr.usuario-row', function() {
        const dataAttr = $(this).attr('data-usuario-info');
        if (dataAttr) {
            try {
                const data = JSON.parse(dataAttr);
                mostrarModalUsuario(data);
            } catch(e) {
                console.error('Error parseando data-usuario-info:', e);
            }
        }
    });
    
    // TABLA DE LÍDERES
    console.log('Inicializando tabla de líderes...');
    const tablaLideres = $('#tablaLideres').DataTable({
        processing: true,
        ajax: {
            url: '../../controllers/AfiliadosController.php?accion=obtenerLideres',
            type: 'GET',
            dataSrc: function(json) {
                console.log('Respuesta líderes:', json);
                return json.data || [];
            },
            error: function(xhr, error, code) {
                console.error('Error AJAX líderes:', {xhr, error, code});
                alert('Error cargando líderes: ' + error);
            }
        },
        columns: [
            { data: 'id_usuario' },
            { data: 'usuario' },
            { data: 'nombres' },
            { data: 'apellidos' },
            { data: 'nivel_orden' },
            { data: 'celular', defaultContent: '-' },
            { data: 'email', defaultContent: '-' },
            { data: 'casa_nombre', defaultContent: '-' },
            { data: 'estudio_nombre', defaultContent: '-' },
            { 
                data: 'fecha_creacion',
                defaultContent: '-',
                render: function(data) {
                    if (!data) return '-';
                    return new Date(data).toLocaleDateString('es-ES');
                }
            }
        ],
        language: idiomaES,
        pageLength: 25,
        order: [[4, 'asc']],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-usuario-info', JSON.stringify(data));
            $(row).addClass('usuario-row');
            $(row).css('cursor', 'pointer');
        }
    });
    
    // Click en fila de líderes
    $('#tablaLideres').on('click', 'tbody tr.usuario-row', function() {
        const dataAttr = $(this).attr('data-usuario-info');
        if (dataAttr) {
            try {
                const data = JSON.parse(dataAttr);
                mostrarModalUsuario(data);
            } catch(e) {
                console.error('Error parseando data-usuario-info:', e);
            }
        }
    });
    
    // TABLA DE REFERENTES
    console.log('Inicializando tabla de referentes...');
    const tablaReferentes = $('#tablaReferentes').DataTable({
        processing: true,
        ajax: {
            url: '../../controllers/AfiliadosController.php?accion=obtenerReferentes',
            type: 'GET',
            dataSrc: function(json) {
                console.log('Respuesta referentes:', json);
                return json.data || [];
            },
            error: function(xhr, error, code) {
                console.error('Error AJAX referentes:', {xhr, error, code});
                alert('Error cargando referentes: ' + error);
            }
        },
        columns: [
            { data: 'id_usuario' },
            { data: 'usuario' },
            { data: 'nombres' },
            { data: 'apellidos' },
            { 
                data: 'cantidad_referidos',
                render: function(data) {
                    return '<span class="badge bg-primary">' + (data || 0) + '</span>';
                }
            }
        ],
        language: idiomaES,
        pageLength: 25,
        order: [[4, 'desc']]
    });
    
    // Manejar cambio de pestañas
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).data('bs-target');
        
        if (target === '#modelos') {
            tablaModelos.ajax.reload();
        } else if (target === '#lideres') {
            tablaLideres.ajax.reload();
        } else if (target === '#referentes') {
            tablaReferentes.ajax.reload();
        }
    });
    
    console.log('Todas las tablas inicializadas');
});

// ============================================
// FUNCIÓN PARA MOSTRAR MODAL DE USUARIO
// ============================================

function mostrarModalUsuario(data) {
    console.log('Mostrando modal para usuario:', data.usuario);
    
    // Procesar datos y corregir codificación
    const cedula = fixEncoding(data.cedula_info || data.cedula || '-');
    const direccion = fixEncoding(data.direccion || '-');
    const ciudad = fixEncoding(data.ciudad || '-');
    const tipoSangre = fixEncoding(data.tipo_sangre || '-');
    const alergias = fixEncoding(data.alergias || 'Ninguna');
    const contactoEmergencia = fixEncoding(data.contacto_emergencia_nombre || '-');
    const parentescoEmergencia = fixEncoding(data.contacto_emergencia_parentesco || '');
    const telefonoEmergencia = fixEncoding(data.contacto_emergencia_telefono || '-');
    const ref1Nombre = fixEncoding(data.ref1_nombre || '-');
    const ref1Parentesco = fixEncoding(data.ref1_parentesco || '');
    const ref1Celular = fixEncoding(data.ref1_celular || '');
    const banco = fixEncoding(data.banco_nombre || '-');
    const tipoCuenta = fixEncoding(data.banco_tipo_cuenta || '-');
    const numeroCuenta = fixEncoding(data.banco_numero_cuenta || '-');
    const disponibilidad = fixEncoding(data.disponibilidad || '-');
    const notas = fixEncoding(data.notas || '');
    const nombres = fixEncoding(data.nombres || '');
    const apellidos = fixEncoding(data.apellidos || '');
    const casaNombre = fixEncoding(data.casa_nombre || '');
    const estudioNombre = fixEncoding(data.estudio_nombre || '');
    
    let diasDescanso = '-';
    try {
        diasDescanso = data.dias_descanso ? JSON.parse(data.dias_descanso).join(', ') : '-';
    } catch(e) {
        console.error('Error parseando dias_descanso:', e);
    }
    
    const urlEntrevista = data.url_entrevista || '';
    const progresoPerfil = data.progreso_perfil || 0;
    
    // Calcular edad
    let edad = '';
    if (data.fecha_nacimiento && data.fecha_nacimiento !== '0000-00-00 00:00:00') {
        const nacimiento = new Date(data.fecha_nacimiento);
        const hoy = new Date();
        let edadCalculada = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edadCalculada--;
        }
        edad = edadCalculada + ' años';
    }
    
    // Ruta de la foto de perfil
    const fotoPerfilPath = `../../assets/images/uploads/diamantewebcam_fotos_de_perfil/${data.id_usuario}.jpg`;
    
    // Crear modal HTML
    const modalHTML = `
        <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-user-circle"></i> 
                            ${nombres} ${apellidos} (@${data.usuario})
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Columna izquierda: Foto -->
                            <div class="col-md-4 text-center mb-3">
                                <img data-src="${fotoPerfilPath}" 
                                     src="../../assets/images/default-avatar.png"
                                     class="img-fluid rounded shadow foto-perfil-modal"
                                     style="max-height: 300px; object-fit: cover; width: 100%;"
                                     alt="Foto de perfil">
                                ${edad ? `<div class="mt-2"><i class="fas fa-birthday-cake"></i> ${edad}</div>` : ''}
                                ${progresoPerfil > 0 ? `
                                <div class="mt-3">
                                    <small class="text-muted">Progreso del perfil</small>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: ${progresoPerfil}%">${progresoPerfil}%</div>
                                    </div>
                                </div>` : ''}
                            </div>
                            
                            <!-- Columna derecha: Información -->
                            <div class="col-md-8">
                                
                                <!-- Información básica -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <i class="fas fa-id-card"></i> <strong>Información Personal</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td><strong>Usuario:</strong></td>
                                                <td>${data.usuario}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nombres:</strong></td>
                                                <td>${nombres}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Apellidos:</strong></td>
                                                <td>${apellidos}</td>
                                            </tr>
                                            ${cedula !== '-' ? `
                                            <tr>
                                                <td><strong>Cédula:</strong></td>
                                                <td>${cedula}</td>
                                            </tr>` : ''}
                                            <tr>
                                                <td><strong>Celular:</strong></td>
                                                <td>${data.celular || '-'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>${data.email || '-'}</td>
                                            </tr>
                                            ${ciudad !== '-' ? `
                                            <tr>
                                                <td><strong>Ciudad:</strong></td>
                                                <td>${ciudad}</td>
                                            </tr>` : ''}
                                            ${direccion !== '-' ? `
                                            <tr>
                                                <td><strong>Dirección:</strong></td>
                                                <td>${direccion}</td>
                                            </tr>` : ''}
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Información laboral -->
                                ${disponibilidad !== '-' || diasDescanso !== '-' || casaNombre || estudioNombre ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <i class="fas fa-briefcase"></i> <strong>Información Laboral</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            ${casaNombre ? `
                                            <tr>
                                                <td><strong>Casa:</strong></td>
                                                <td>${casaNombre}</td>
                                            </tr>` : ''}
                                            ${estudioNombre ? `
                                            <tr>
                                                <td><strong>Estudio:</strong></td>
                                                <td>${estudioNombre}</td>
                                            </tr>` : ''}
                                            ${disponibilidad !== '-' ? `
                                            <tr>
                                                <td><strong>Disponibilidad:</strong></td>
                                                <td>${disponibilidad}</td>
                                            </tr>` : ''}
                                            ${diasDescanso !== '-' ? `
                                            <tr>
                                                <td><strong>Días de descanso:</strong></td>
                                                <td>${diasDescanso}</td>
                                            </tr>` : ''}
                                            ${urlEntrevista ? `
                                            <tr>
                                                <td><strong>Entrevista:</strong></td>
                                                <td><a href="${urlEntrevista}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-video"></i> Ver video
                                                </a></td>
                                            </tr>` : ''}
                                        </table>
                                    </div>
                                </div>` : ''}
                                
                                <!-- Información médica -->
                                ${tipoSangre !== '-' || alergias !== 'Ninguna' ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <i class="fas fa-heartbeat"></i> <strong>Información Médica</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            ${tipoSangre !== '-' ? `
                                            <tr>
                                                <td><strong>Tipo de sangre:</strong></td>
                                                <td><span class="badge bg-danger">${tipoSangre}</span></td>
                                            </tr>` : ''}
                                            <tr>
                                                <td><strong>Alergias:</strong></td>
                                                <td>${alergias !== 'Ninguna' ? 
                                                    `<span class="text-danger">${alergias}</span>` : 
                                                    `<span class="text-success">Ninguna registrada</span>`}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>` : ''}
                                
                                <!-- Contactos de emergencia -->
                                ${contactoEmergencia !== '-' || ref1Nombre !== '-' ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <i class="fas fa-phone-alt"></i> <strong>Contactos de Emergencia</strong>
                                    </div>
                                    <div class="card-body">
                                        ${contactoEmergencia !== '-' ? `
                                        <div class="alert alert-warning mb-2">
                                            <strong><i class="fas fa-user-shield"></i> ${contactoEmergencia}</strong>
                                            ${parentescoEmergencia ? `<br><small>${parentescoEmergencia}</small>` : ''}
                                            ${telefonoEmergencia !== '-' ? `<br><i class="fas fa-phone"></i> ${telefonoEmergencia}` : ''}
                                        </div>` : ''}
                                        ${ref1Nombre !== '-' ? `
                                        <div class="alert alert-info mb-0">
                                            <strong><i class="fas fa-user-friends"></i> ${ref1Nombre}</strong>
                                            ${ref1Parentesco ? `<br><small>${ref1Parentesco}</small>` : ''}
                                            ${ref1Celular ? `<br><i class="fas fa-phone"></i> ${ref1Celular}` : ''}
                                        </div>` : ''}
                                    </div>
                                </div>` : ''}
                                
                                <!-- Información bancaria -->
                                ${banco !== '-' || numeroCuenta !== '-' ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <i class="fas fa-university"></i> <strong>Información Bancaria</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            ${banco !== '-' ? `
                                            <tr>
                                                <td><strong>Banco:</strong></td>
                                                <td>${banco}</td>
                                            </tr>` : ''}
                                            ${tipoCuenta !== '-' ? `
                                            <tr>
                                                <td><strong>Tipo de cuenta:</strong></td>
                                                <td>${tipoCuenta}</td>
                                            </tr>` : ''}
                                            ${numeroCuenta !== '-' ? `
                                            <tr>
                                                <td><strong>Número de cuenta:</strong></td>
                                                <td><code>${numeroCuenta}</code></td>
                                            </tr>` : ''}
                                        </table>
                                    </div>
                                </div>` : ''}
                                
                                <!-- Notas -->
                                ${notas ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <i class="fas fa-sticky-note"></i> <strong>Notas Especiales</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0 fst-italic">${notas}</p>
                                    </div>
                                </div>` : ''}
                                
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal anterior si existe
    $('#modalUsuario').remove();
    
    // Agregar modal al body y mostrarlo
    $('body').append(modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
    
    // Cargar la foto de perfil después de que se muestre el modal
    setTimeout(function() {
        const $img = $('#modalUsuario .foto-perfil-modal');
        const fotoSrc = $img.attr('data-src');
        
        // Crear una imagen temporal para verificar si existe
        const tempImg = new Image();
        tempImg.onload = function() {
            $img.attr('src', fotoSrc);
        };
        tempImg.onerror = function() {
            console.log('No se encontró foto para el usuario, usando avatar predeterminado');
        };
        tempImg.src = fotoSrc;
    }, 100);
    
    // Limpiar del DOM cuando se cierre
    $('#modalUsuario').on('hidden.bs.modal', function () {
        $(this).remove();
    });
}
