<!-- Modal Nueva Cotización -->
<div class="modal fade" id="modalNuevaCotizacion" tabindex="-1" aria-labelledby="modalNuevaCotizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaCotizacionLabel">
                    <i class="bi bi-plus-circle"></i> Nueva Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaCotizacion">
                    @csrf
                    
                    <!-- Cliente con buscador -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-person"></i> Datos del Cliente</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Buscar cliente <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <div class="search-box flex-grow-1">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="buscarClienteCotizacion" 
                                            placeholder="Buscar por nombre o email..."
                                            autocomplete="off">
                                    </div>
                                    <button type="button" class="btn btn-primary" id="btnMostrarNuevoCliente">
                                        <i class="bi bi-plus-circle"></i> Nuevo Cliente
                                    </button>
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para seleccionarlo.</small>

                                <!-- FORMULARIO PARA NUEVO CLIENTE (oculto inicialmente) -->
                                <div id="formNuevoClienteContainer" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                                    <h6 class="mb-3"><i class="bi bi-person-plus"></i> Registrar nuevo cliente</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_nombre" 
                                                placeholder="Nombre *"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_apellido_paterno" 
                                                placeholder="Apellido paterno *"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_apellido_materno" 
                                                placeholder="Apellido materno"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="email" class="form-control" id="nuevo_cliente_email" 
                                                placeholder="Correo electrónico"
                                                autocomplete="off">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="tel" class="form-control" id="nuevo_cliente_telefono" 
                                                placeholder="Teléfono"
                                                autocomplete="off"
                                                onkeypress="return window.soloNumeros(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[0-9+\-\s]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_domicilio" 
                                                placeholder="Domicilio"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <button type="button" class="btn btn-secondary" id="btnCancelarNuevoCliente">Cancelar</button>
                                        <button type="button" class="btn btn-success" id="btnGuardarNuevoCliente">Guardar y seleccionar</button>
                                    </div>
                                </div>
                                
                                <!-- Resultados de búsqueda -->
                                <div id="resultadosClientes" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Clientes encontrados</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaClientes"></div>
                                    </div>
                                </div>
                                
                                <!-- Cliente seleccionado -->
                                <div id="clienteSeleccionado" class="mt-2 p-2 bg-light rounded" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Cliente seleccionado:</strong>
                                            <p class="mb-0" id="clienteInfo"></p>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarCliente()">
                                            <i class="bi bi-x"></i> Cambiar
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="cliente_id" name="cliente_id">
                            </div>
                        </div>
                    </div>

                    <!-- Datos de la cotización -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-info-circle"></i> Información de la Cotización</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fase <span class="text-danger">*</span></label>
                                    <select class="form-select" id="fase_id" name="fase_id" required>
                                        <option value="">Seleccionar fase...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Clasificación</label>
                                    <select class="form-select" id="clasificacion_id" name="clasificacion_id">
                                        <option value="">Seleccionar clasificación...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sucursal asignada</label>
                                    <select class="form-select" id="sucursal_asignada_id" name="sucursal_asignada_id">
                                        <option value="">Seleccionar sucursal...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Certeza</label>
                                    <select class="form-select" id="certeza" name="certeza">
                                        <option value="1">Baja</option>
                                        <option value="2">Media</option>
                                        <option value="3">Alta</option>
                                    </select>
                                    <small class="text-muted">Si la certeza es <b>alta</b>, los productos se apartarán</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Convenio</label>
                                    <select class="form-select" id="convenio_general" name="convenio_general">
                                        <option value="">Sin convenio</option>
                                    </select>
                                    <small class="text-muted">Selecciona un convenio para aplicar los descuentos</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Comentarios</label>
                                    <textarea class="form-control" id="comentarios" name="comentarios" rows="2" 
                                            placeholder="Notas adicionales sobre la cotización..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Artículos -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-box-seam"></i> Artículos</strong>
                        </div>
                        <div class="card-body">
                            <!-- Buscador de artículos (UNIFICADO) -->
                            <div class="mb-3">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="buscarArticuloModal" 
                                        placeholder="Buscar por nombre, código o sustancia activa (ej: Paracetamol, Ibuprofeno, 7501234567912)..."
                                        autocomplete="off"
                                        style="padding-right: 35px;">
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Puedes buscar por nombre del producto, código EAN o sustancia activa
                                </small>
                                
                                <div id="resultadosArticulos" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Artículos encontrados (haz clic para agregar)</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaArticulos"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de artículos -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 15%">Código</th>
                                            <th style="width: 35%">Descripción</th>
                                            <th style="width: 10%" class="text-center">Cantidad</th>
                                            <th style="width: 15%" class="text-end">Precio</th>
                                            <th style="width: 15%" class="text-end">Importe</th>
                                            <th style="width: 5%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="articulosBody">
                                        <tr id="sin-articulos-row">
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">No hay artículos agregados</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="totalCotizacion">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevaCotizacion()">
                    <i class="bi bi-save"></i> Guardar Cotización
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================
// VARIABLES GLOBALES DEL MODAL
// ============================================
let articulosSeleccionados = [];
let catalogos = {
    fases: [],
    clasificaciones: [],
    sucursales: [],
    convenios: []
};
let esNuevaVersion = false;
let cotizacionOrigenId = null;
let timeoutBusquedaCliente;
let timeoutBusquedaArticulo; // Declarada UNA sola vez
let sucursalUsuarioDefecto = 0; // Variable para la sucursal del usuario logueado

// Función para establecer la sucursal del usuario desde fuera del modal
window.setSucursalUsuarioDefecto = function(sucursalId) {
    sucursalUsuarioDefecto = sucursalId;
    console.log('Sucursal usuario establecida:', sucursalUsuarioDefecto);
};

// Función para establecer el modo nueva versión desde fuera del modal
window.setEsNuevaVersion = function(valor, origenId) {
    esNuevaVersion = valor;
    cotizacionOrigenId = origenId;
    console.log('Modo nueva versión activado:', esNuevaVersion, 'Origen ID:', cotizacionOrigenId);
};

// ============================================
// CARGA DE CATÁLOGOS
// ============================================
// ============================================
// CARGA DE CATÁLOGOS
// ============================================
function cargarCatalogos() {
    console.log('Cargando catálogos...');
    fetch('{{ route("ventas.cotizaciones.catalogos") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Catálogos recibidos:', data);
        if (data.success) {
            catalogos = data.data;
            
            const faseSelect = document.getElementById('fase_id');
            const clasificacionSelect = document.getElementById('clasificacion_id');
            const sucursalSelect = document.getElementById('sucursal_asignada_id');
            const convenioGeneralSelect = document.getElementById('convenio_general');
            
            // Cargar fases
            if (faseSelect && catalogos.fases) {
                faseSelect.innerHTML = '<option value="">Seleccionar fase...</option>' + 
                    catalogos.fases.map(f => `<option value="${f.id_fase}">${f.fase}</option>`).join('');
                
                // Seleccionar automáticamente la fase "En proceso" si existe
                if (catalogos.fase_en_proceso_id) {
                    faseSelect.value = catalogos.fase_en_proceso_id;
                    console.log('Fase "En proceso" seleccionada automáticamente, ID:', catalogos.fase_en_proceso_id);
                }
            }
            
            // Cargar clasificaciones
            if (clasificacionSelect && catalogos.clasificaciones) {
                clasificacionSelect.innerHTML = '<option value="">Seleccionar clasificación...</option>' + 
                    catalogos.clasificaciones.map(c => `<option value="${c.id_clasificacion}">${c.clasificacion}</option>`).join('');
            }
            
            // Cargar sucursales
            if (sucursalSelect && catalogos.sucursales) {
                sucursalSelect.innerHTML = '<option value="">Seleccionar sucursal...</option>' + 
                    catalogos.sucursales.map(s => `<option value="${s.id_sucursal}">${s.nombre}</option>`).join('');
            }
            
            // Cargar convenios
            if (convenioGeneralSelect && catalogos.convenios) {
                convenioGeneralSelect.innerHTML = '<option value="">Sin convenio</option>' + 
                    catalogos.convenios.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            }
        }
    })
    .catch(error => console.error('Error al cargar catálogos:', error));
}

// ============================================
// FUNCIONES PARA CLIENTES
// ============================================

function buscarClientes(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosClientes').style.display = 'none';
        return;
    }
    
    fetch(`{{ route("ventas.cotizaciones.clientes.buscar") }}?q=${encodeURIComponent(termino)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('resultadosClientes');
        const listaResultados = document.getElementById('listaClientes');
        
        if (data.success && data.data && data.data.length > 0) {
            listaResultados.innerHTML = data.data.map(cliente => {
                // Usar los campos correctos que envía el controlador
                const id = cliente.id || 0;
                const nombre = cliente.nombre_completo || '';
                const nombreCliente = cliente.Nombre || '';
                const apPaterno = cliente.apPaterno || '';
                const apMaterno = cliente.apMaterno || '';
                const email = cliente.email1 || cliente.email || '';
                const telefono1 = cliente.telefono1 || '';
                const telefono2 = cliente.telefono2 || '';
                const titulo = cliente.titulo || '';
                const domicilio = cliente.domicilio || '';
                
                // Construir HTML del contacto
                let contactoHtml = '';
                let tieneContacto = false;
                
                if (telefono1 && telefono1 !== 'null' && telefono1 !== '') {
                    contactoHtml += `<i class="bi bi-telephone"></i> ${telefono1}<br>`;
                    tieneContacto = true;
                }
                if (telefono2 && telefono2 !== 'null' && telefono2 !== '') {
                    contactoHtml += `<i class="bi bi-telephone"></i> ${telefono2} (secundario)<br>`;
                    tieneContacto = true;
                }
                if (email && email !== 'null' && email !== '') {
                    contactoHtml += `<i class="bi bi-envelope"></i> ${email}`;
                    tieneContacto = true;
                }

                if (!tieneContacto) {
                    contactoHtml = '<span class="text-muted">Sin contacto</span>';
                }
                
                let tituloHtml = '';
                if (titulo && titulo !== 'null' && titulo.trim() !== '') {
                    tituloHtml = `<br><small class="text-muted">${escapeHtml(titulo)}</small>`;
                }
                
                let direccionHtml = '';
                if (domicilio && domicilio !== 'null' && domicilio.trim() !== '') {
                    direccionHtml = `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(domicilio)}</small>`;
                }
                
                // Escapar valores para onclick
                const nombreEscapado = escapeHtml(nombre).replace(/'/g, "\\'");
                const emailEscapado = escapeHtml(email).replace(/'/g, "\\'");
                const telefono1Escapado = escapeHtml(telefono1).replace(/'/g, "\\'");
                const telefono2Escapado = escapeHtml(telefono2).replace(/'/g, "\\'");
                const tituloEscapado = escapeHtml(titulo).replace(/'/g, "\\'");
                const domicilioEscapado = escapeHtml(domicilio).replace(/'/g, "\\'");
                
                return `
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor: pointer;">
                        <div class="flex-grow-1" onclick="seleccionarCliente(${id}, '${nombreEscapado}', '${emailEscapado}', '${telefono1Escapado}', '${telefono2Escapado}', '${domicilioEscapado}', '${tituloEscapado}')">
                            <div>
                                <strong>${escapeHtml(nombre)}</strong>
                                ${tituloHtml}
                                <div class="small text-muted">${contactoHtml}</div>
                                ${direccionHtml}
                            </div>
                        </div>
                        <div class="ms-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="event.stopPropagation(); editarClienteExistente(${id}, '${escapeHtml(nombreCliente).replace(/'/g, "\\'")}', '${escapeHtml(apPaterno).replace(/'/g, "\\'")}', '${escapeHtml(apMaterno).replace(/'/g, "\\'")}', '${escapeHtml(email).replace(/'/g, "\\'")}', '${escapeHtml(telefono1).replace(/'/g, "\\'")}', '${escapeHtml(telefono2).replace(/'/g, "\\'")}', '${escapeHtml(domicilio).replace(/'/g, "\\'")}')">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            resultadosDiv.style.display = 'block';
        } else {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
            resultadosDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error buscando clientes:', error);
        const resultadosDiv = document.getElementById('resultadosClientes');
        const listaResultados = document.getElementById('listaClientes');
        if (listaResultados) {
            listaResultados.innerHTML = '<div class="list-group-item text-danger">Error al buscar clientes</div>';
            if (resultadosDiv) resultadosDiv.style.display = 'block';
        }
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

window.seleccionarCliente = function(id, nombre, email, telefono1, telefono2, domicilio, titulo) {
    document.getElementById('cliente_id').value = id;
    
    let html = `<div><strong>${nombre}</strong>`;
    
    if (titulo && titulo !== 'null' && titulo.trim() !== '') {
        html += `<br><small class="text-muted">${titulo}</small>`;
    }
    
    let contactoParts = [];
    if (telefono1 && telefono1 !== 'null' && telefono1 !== '') {
        contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono1}`);
    }
    if (telefono2 && telefono2 !== 'null' && telefono2 !== '') {
        contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono2} (secundario)`);
    }
    if (email && email !== 'null' && email !== '') {
        contactoParts.push(`<i class="bi bi-envelope"></i> ${email}`);
    }
    
    if (contactoParts.length > 0) {
        html += `<br><small class="text-muted">${contactoParts.join(' | ')}</small>`;
    }
    
    if (domicilio && domicilio !== 'null' && domicilio.trim() !== '') {
        html += `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${domicilio}</small>`;
    }
    
    html += `</div>`;
    
    document.getElementById('clienteInfo').innerHTML = html;
    document.getElementById('clienteSeleccionado').style.display = 'block';
    document.getElementById('resultadosClientes').style.display = 'none';
    document.getElementById('buscarClienteCotizacion').value = nombre;
};

window.limpiarCliente = function() {
    document.getElementById('cliente_id').value = '';
    document.getElementById('clienteSeleccionado').style.display = 'none';
    document.getElementById('buscarClienteCotizacion').value = '';
};

// ============================================
// HANDLERS Y FUNCIONES PARA CLIENTE (DEFINIDOS ANTES DE SER USADOS)
// ============================================

// Handler para guardar nuevo cliente
const guardarNuevoClienteHandler = function() {
    const nombre = document.getElementById('nuevo_cliente_nombre').value.trim();
    const apellidoPaterno = document.getElementById('nuevo_cliente_apellido_paterno').value.trim();
    const apellidoMaterno = document.getElementById('nuevo_cliente_apellido_materno').value.trim();
    const email = document.getElementById('nuevo_cliente_email').value.trim();
    const telefono = document.getElementById('nuevo_cliente_telefono').value.trim();
    const domicilio = document.getElementById('nuevo_cliente_domicilio').value.trim();
    
    if (!nombre) {
        if (window.mostrarToast) window.mostrarToast('El nombre es requerido', 'warning');
        return;
    }
    if (!apellidoPaterno) {
        if (window.mostrarToast) window.mostrarToast('El apellido paterno es requerido', 'warning');
        return;
    }
    
    const btn = this;
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    
    fetch('{{ route("clientes.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            Nombre: nombre,
            apPaterno: apellidoPaterno,
            apMaterno: apellidoMaterno || null,
            email1: email || null,
            telefono1: telefono || null,
            Domicilio: domicilio || null,
            status: 'CLIENTE'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cliente = data.data;
            const idCliente = cliente.id_Cliente;
            const nombreCompleto = `${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}`.trim();
            const emailCliente = cliente.email1 || '';
            
            if (typeof window.seleccionarCliente === 'function') {
                window.seleccionarCliente(idCliente, nombreCompleto, emailCliente, '', '', '', '');
            } else {
                document.getElementById('cliente_id').value = idCliente;
                document.getElementById('clienteInfo').innerHTML = `<strong>${nombreCompleto}</strong><br><small>${emailCliente}</small>`;
                document.getElementById('clienteSeleccionado').style.display = 'block';
                document.getElementById('resultadosClientes').style.display = 'none';
                document.getElementById('buscarClienteCotizacion').value = nombreCompleto;
            }
            
            resetearFormularioEdicionCliente();
            
            if (window.mostrarToast) {
                window.mostrarToast(`Cliente "${nombreCompleto}" creado correctamente`, 'success');
            }
        } else {
            if (data.errors) {
                const errores = Object.values(data.errors).flat().join(', ');
                if (window.mostrarToast) window.mostrarToast(`Error: ${errores}`, 'danger');
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al crear el cliente', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión al crear el cliente', 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
};

// Handler para actualizar cliente
const actualizarClienteHandler = function() {
    const clienteId = this.getAttribute('data-cliente-id');
    if (!clienteId) {
        if (window.mostrarToast) window.mostrarToast('Error: ID de cliente no encontrado', 'danger');
        return;
    }
    
    const nombre = document.getElementById('nuevo_cliente_nombre').value.trim();
    const apellidoPaterno = document.getElementById('nuevo_cliente_apellido_paterno').value.trim();
    const apellidoMaterno = document.getElementById('nuevo_cliente_apellido_materno').value.trim();
    const email = document.getElementById('nuevo_cliente_email').value.trim();
    const telefono1 = document.getElementById('nuevo_cliente_telefono').value.trim();
    const telefono2 = document.getElementById('nuevo_cliente_telefono2')?.value.trim() || '';
    const domicilio = document.getElementById('nuevo_cliente_domicilio').value.trim();
    
    if (!nombre) {
        if (window.mostrarToast) window.mostrarToast('El nombre es requerido', 'warning');
        return;
    }
    if (!apellidoPaterno) {
        if (window.mostrarToast) window.mostrarToast('El apellido paterno es requerido', 'warning');
        return;
    }
    
    const btn = this;
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Actualizando...';
    
    fetch(`/clientes/${clienteId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            Nombre: nombre,
            apPaterno: apellidoPaterno,
            apMaterno: apellidoMaterno || null,
            email1: email || null,
            telefono1: telefono1 || null,
            telefono2: telefono2 || null,
            Domicilio: domicilio || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast('Cliente actualizado correctamente', 'success');
            
            resetearFormularioEdicionCliente();
            
            document.getElementById('buscarClienteCotizacion').value = '';
            document.getElementById('resultadosClientes').style.display = 'none';
            
            const clienteSeleccionadoId = document.getElementById('cliente_id').value;
            if (clienteSeleccionadoId == clienteId) {
                const nombreCompleto = `${nombre} ${apellidoPaterno} ${apellidoMaterno || ''}`.trim();
                let html = `<div><strong>${escapeHtml(nombreCompleto)}</strong>`;
                
                let contactoParts = [];
                if (telefono1) contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono1}`);
                if (telefono2) contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono2} (secundario)`);
                if (email) contactoParts.push(`<i class="bi bi-envelope"></i> ${email}`);
                
                if (contactoParts.length > 0) {
                    html += `<br><small class="text-muted">${contactoParts.join(' | ')}</small>`;
                }
                
                if (domicilio) {
                    html += `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(domicilio)}</small>`;
                }
                
                html += `</div>`;
                document.getElementById('clienteInfo').innerHTML = html;
            }
        } else {
            if (data.errors) {
                const errores = Object.values(data.errors).flat().join(', ');
                if (window.mostrarToast) window.mostrarToast(`Error: ${errores}`, 'danger');
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al actualizar el cliente', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión al actualizar el cliente', 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
};

// Handler para cancelar edición
const cancelarEdicionHandler = function() {
    resetearFormularioEdicionCliente();
};

// Función para resetear el formulario de edición/nuevo cliente
function resetearFormularioEdicionCliente() {
    const container = document.getElementById('formNuevoClienteContainer');
    container.style.display = 'none';
    
    const formTitle = document.querySelector('#formNuevoClienteContainer h6');
    if (formTitle) {
        formTitle.innerHTML = '<i class="bi bi-person-plus"></i> Registrar nuevo cliente';
    }
    
    const btnGuardar = document.getElementById('btnGuardarNuevoCliente');
    if (btnGuardar) {
        btnGuardar.textContent = 'Guardar y seleccionar';
        btnGuardar.removeAttribute('data-cliente-id');
        btnGuardar.removeEventListener('click', actualizarClienteHandler);
        btnGuardar.removeEventListener('click', guardarNuevoClienteHandler);
        btnGuardar.addEventListener('click', guardarNuevoClienteHandler);
    }
    
    const btnCancelar = document.getElementById('btnCancelarNuevoCliente');
    if (btnCancelar) {
        btnCancelar.textContent = 'Cancelar';
        btnCancelar.removeEventListener('click', cancelarEdicionHandler);
        btnCancelar.addEventListener('click', cancelarEdicionHandler);
    }
    
    document.getElementById('nuevo_cliente_nombre').value = '';
    document.getElementById('nuevo_cliente_apellido_paterno').value = '';
    document.getElementById('nuevo_cliente_apellido_materno').value = '';
    document.getElementById('nuevo_cliente_email').value = '';
    document.getElementById('nuevo_cliente_telefono').value = '';
    document.getElementById('nuevo_cliente_domicilio').value = '';
    if (document.getElementById('nuevo_cliente_telefono2')) {
        document.getElementById('nuevo_cliente_telefono2').value = '';
    }
}

// Función para editar cliente existente
window.editarClienteExistente = function(id, nombre, apPaterno, apMaterno, email, telefono1, telefono2, domicilio) {
    console.log('Editando cliente:', {id, nombre, apPaterno, apMaterno, email, telefono1, telefono2, domicilio});
    
    document.getElementById('resultadosClientes').style.display = 'none';
    
    const container = document.getElementById('formNuevoClienteContainer');
    container.style.display = 'block';
    
    const formTitle = document.querySelector('#formNuevoClienteContainer h6');
    if (formTitle) {
        formTitle.innerHTML = '<i class="bi bi-pencil-square"></i> Editar cliente';
    }
    
    const btnGuardar = document.getElementById('btnGuardarNuevoCliente');
    if (btnGuardar) {
        btnGuardar.textContent = 'Actualizar cliente';
        btnGuardar.setAttribute('data-cliente-id', id);
        btnGuardar.removeEventListener('click', guardarNuevoClienteHandler);
        btnGuardar.removeEventListener('click', actualizarClienteHandler);
        btnGuardar.addEventListener('click', actualizarClienteHandler);
    }
    
    const btnCancelar = document.getElementById('btnCancelarNuevoCliente');
    if (btnCancelar) {
        btnCancelar.textContent = 'Cancelar edición';
    }
    
    document.getElementById('nuevo_cliente_nombre').value = nombre || '';
    document.getElementById('nuevo_cliente_apellido_paterno').value = apPaterno || '';
    document.getElementById('nuevo_cliente_apellido_materno').value = apMaterno || '';
    document.getElementById('nuevo_cliente_email').value = email || '';
    document.getElementById('nuevo_cliente_telefono').value = telefono1 || '';
    document.getElementById('nuevo_cliente_domicilio').value = domicilio || '';
    
    let telefono2Input = document.getElementById('nuevo_cliente_telefono2');
    if (!telefono2Input) {
        telefono2Input = document.createElement('input');
        telefono2Input.type = 'hidden';
        telefono2Input.id = 'nuevo_cliente_telefono2';
        document.getElementById('formNuevoClienteContainer').appendChild(telefono2Input);
    }
    telefono2Input.value = telefono2 || '';
    
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// ============================================
// FUNCIONES PARA ARTÍCULOS
// ============================================

function buscarArticulos(termino) {
    const sucursalAsignadaId = document.getElementById('sucursal_asignada_id')?.value || '';
    
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosArticulos').style.display = 'none';
        return;
    }
    
    clearTimeout(timeoutBusquedaArticulo);
    timeoutBusquedaArticulo = setTimeout(() => {
        let url = `{{ route("ventas.cotizaciones.productos.buscar") }}?sucursal_asignada_id=${sucursalAsignadaId}&q=${encodeURIComponent(termino)}`;
        
        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const resultadosDiv = document.getElementById('resultadosArticulos');
            const listaResultados = document.getElementById('listaArticulos');
            
            if (resultadosDiv && listaResultados) {
                if (data.success && data.data && data.data.length > 0) {
                    window.resultadosBusqueda = data.data;
                    
                    listaResultados.innerHTML = data.data.map((articulo, idx) => {
                        const yaExiste = articulosSeleccionados.some(a => 
                            a.id_producto === articulo.id && 
                            a.id_sucursal_surtido === articulo.id_sucursal
                        );
                        const esSucursalAsignada = articulo.id_sucursal == sucursalAsignadaId;
                        const stockClass = articulo.inventario > 0 ? 'text-success' : 'text-danger';
                        const badgeClass = esSucursalAsignada ? 'bg-primary' : 'bg-secondary';
                        const apartadoBadge = articulo.apartado > 0 ? 
                            `<span class="badge bg-warning ms-1">Apartado: ${articulo.apartado}</span>` : '';
                        const existenteBadge = yaExiste ? 
                            '<span class="badge bg-warning ms-1">Ya agregado (se sumará)</span>' : '';
                        
                        const sustanciaBadge = articulo.sustancias_activas && articulo.sustancias_activas !== 'No es medicamento' && articulo.sustancias_activas !== 'No coincide con la búsqueda' ?
                            `<br><small class="text-info"><i class="bi bi-capsule"></i> Sustancia: <strong>${escapeHtml(articulo.sustancias_activas)}</strong></small>` : '';
                        
                        return `
                            <div class="list-group-item list-group-item-action" 
                                 onclick="agregarArticuloPorIndiceNuevo(${idx})"
                                 style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>${escapeHtml(articulo.nombre)}</strong>
                                        ${sustanciaBadge}
                                        <br><small class="text-muted"><strong>Código: </strong>${escapeHtml(articulo.codbar || 'N/A')} | Precio: $${articulo.precio.toFixed(2)}</small>
                                        <br><small class="text-muted"><strong>Familia: </strong>${escapeHtml(articulo.num_familia || 'N/A')}</small>
                                        <br><span class="badge ${badgeClass} me-1">${escapeHtml(articulo.nombre_sucursal)}</span>
                                        <span class="badge ${stockClass}">Stock disponible: ${articulo.inventario}</span>
                                        ${apartadoBadge}
                                        ${existenteBadge}
                                    </div>
                                    <span class="badge bg-success">Agregar</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                    resultadosDiv.style.display = 'block';
                } else {
                    let mensaje = `No se encontraron artículos con "${escapeHtml(termino)}"`;
                    listaResultados.innerHTML = `<div class="list-group-item text-muted">${mensaje}</div>`;
                    resultadosDiv.style.display = 'block';
                }
            }
        })
        .catch(error => console.error('Error buscando artículos:', error));
    }, 300);
}

// Función renombrada para evitar conflicto con modal-editar-cotizacion
window.agregarArticuloPorIndiceNuevo = function(idx) {
    console.log('agregarArticuloPorIndiceNuevo llamado, idx:', idx);
    
    if (!window.resultadosBusqueda || !window.resultadosBusqueda[idx]) {
        console.error('No hay resultadosBusqueda o índice inválido');
        return;
    }
    
    const articuloData = window.resultadosBusqueda[idx];
    console.log('Artículo seleccionado:', articuloData);
    
    const nuevoArticulo = {
        id_producto: articuloData.id,
        nombre: articuloData.nombre,
        codbar: articuloData.codbar || '',
        precio: articuloData.precio,
        cantidad: 1,
        descuento: 0,
        id_convenio: null,
        id_sucursal_surtido: Number(articuloData.id_sucursal),
        num_familia: articuloData.num_familia || '',
        inventario_disponible: articuloData.inventario,
        nombre_sucursal_surtido: articuloData.nombre_sucursal
    };
    
    console.log('Nuevo artículo creado:', nuevoArticulo);
    
    const convenioSelect = document.getElementById('convenio_general');
    if (convenioSelect && convenioSelect.value && catalogos.convenios) {
        const convenio = catalogos.convenios.find(c => c.id == convenioSelect.value);
        if (convenio && convenio.familias) {
            const familiaConDescuento = convenio.familias.find(f => f.num_familia === nuevoArticulo.num_familia);
            if (familiaConDescuento) {
                nuevoArticulo.descuento = familiaConDescuento.descuento;
                nuevoArticulo.id_convenio = convenio.id;
                console.log('Descuento aplicado:', nuevoArticulo.descuento);
            }
        }
    }
    
    console.log('Llamando a agregarOSumarArticuloNuevo...');
    agregarOSumarArticuloNuevo(nuevoArticulo, articulosSeleccionados, false);
    
    document.getElementById('buscarArticuloModal').value = '';
    document.getElementById('resultadosArticulos').style.display = 'none';
};

// Función renombrada para evitar conflicto
function agregarOSumarArticuloNuevo(articulo, listaArticulos, esEdicion = false) {
    console.log('agregarOSumarArticuloNuevo EJECUTÁNDOSE correctamente');
    console.log('Artículo a agregar:', articulo);
    console.log('Lista actual longitud:', listaArticulos.length);
    
    const existe = listaArticulos.find(a => 
        a.id_producto === articulo.id_producto && 
        parseInt(a.id_sucursal_surtido) === parseInt(articulo.id_sucursal_surtido)
    );
    
    if (existe) {
        console.log('Artículo ya existe, sumando cantidad');
        const nuevaCantidad = existe.cantidad + 1;
        const maxDisponible = existe.inventario_disponible;
        
        if (nuevaCantidad <= maxDisponible) {
            existe.cantidad = nuevaCantidad;
            console.log('Nueva cantidad:', existe.cantidad);
            if (window.mostrarToast) {
                window.mostrarToast(
                    `Sumado 1 unidad a "${articulo.nombre}". Total: ${nuevaCantidad} unidades.`, 
                    'success'
                );
            }
        } else {
            if (window.mostrarToast) {
                window.mostrarToast(
                    `No se puede sumar más. Stock máximo: ${maxDisponible} unidades.`, 
                    'warning'
                );
            }
        }
    } else {
        console.log('Artículo nuevo, agregando a la lista');
        listaArticulos.push(articulo);
        console.log('Lista después de push, nueva longitud:', listaArticulos.length);
        if (window.mostrarToast) {
            window.mostrarToast(
                `Agregado "${articulo.nombre}" a la cotización.`, 
                'success'
            );
        }
    }
    
    console.log('Llamando a renderizarTablaArticulos...');
    renderizarTablaArticulos();
}

window.eliminarArticulo = function(index) {
    articulosSeleccionados.splice(index, 1);
    renderizarTablaArticulos();
};

window.actualizarCantidad = function(index, cantidad) {
    const articulo = articulosSeleccionados[index];
    const nuevaCantidad = Math.max(1, parseInt(cantidad) || 1);
    const maxDisponible = articulo.inventario_disponible || 999;
    
    if (nuevaCantidad > maxDisponible) {
        if (window.mostrarToast) {
            window.mostrarToast(`Solo hay ${maxDisponible} unidades disponibles en ${articulo.nombre_sucursal_surtido || 'esta sucursal'}`, 'warning');
        }
        articulo.cantidad = maxDisponible;
    } else {
        articulo.cantidad = nuevaCantidad;
    }
    
    renderizarTablaArticulos();
};

function renderizarTablaArticulos() {
    console.log('renderizarTablaArticulos llamado');
    console.log('articulosSeleccionados longitud:', articulosSeleccionados.length);
    
    const tbody = document.getElementById('articulosBody');
    if (!tbody) {
        console.error('No se encontró el elemento articulosBody');
        return;
    }
    
    let totalGeneral = 0;
    
    if (articulosSeleccionados.length === 0) {
        console.log('No hay artículos, mostrando mensaje vacío');
        tbody.innerHTML = `<tr id="sin-articulos-row">
            <td colspan="7" class="text-center py-4">
                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">No hay artículos agregados</p>
            <\/td>
        <\/tr>`;
        document.getElementById('totalCotizacion').textContent = '$0.00';
        return;
    }
    
    let html = '';
    for (let index = 0; index < articulosSeleccionados.length; index++) {
        const articulo = articulosSeleccionados[index];
        const precioConDescuento = articulo.precio * (1 - articulo.descuento / 100);
        const importe = articulo.cantidad * precioConDescuento;
        totalGeneral += importe;
        
        html += `
            <tr id="articulo-row-${index}">
                <td class="text-center">${index + 1}<\/td>
                <td><small>${escapeHtml(articulo.codbar || '-')}<\/small><\/td>
                <td>
                    <strong>${escapeHtml(articulo.nombre)}</strong>
                    ${articulo.descuento > 0 ? `<br><small class="text-muted"><i class="bi bi-tag"></i> ${articulo.descuento}% descuento aplicado</small>` : ''}
                    <br><small class="text-muted">Sucursal: ${escapeHtml(articulo.nombre_sucursal_surtido || 'No asignada')} | Máx: ${articulo.inventario_disponible}</small>
                <\/td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center" 
                           value="${articulo.cantidad}" min="1" 
                           max="${articulo.inventario_disponible}"
                           onchange="actualizarCantidad(${index}, this.value)"
                           style="width: 80px;">
                <\/td>
                <td class="text-end">
                    <span class="fw-bold">$${precioConDescuento.toFixed(2)}<\/span>
                    ${articulo.precio !== precioConDescuento ? `<br><small class="text-muted text-decoration-line-through">$${articulo.precio.toFixed(2)}</small>` : ''}
                <\/td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}<\/td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArticulo(${index})">
                        <i class="bi bi-trash"><\/i>
                    <\/button>
                <\/td>
            <\/tr>
        `;
    }
    
    console.log('HTML generado, longitud:', html.length);
    tbody.innerHTML = html;
    document.getElementById('totalCotizacion').textContent = `$${totalGeneral.toFixed(2)}`;
    console.log('Total:', totalGeneral.toFixed(2));
}

// ============================================
// FUNCIÓN PARA PRECARGAR DATOS EN MODO NUEVA VERSIÓN
// ============================================
function precargarDatosCotizacion(data) {
    if (!data) return;

    // Seleccionar cliente
    if (data.id_cliente && data.cliente_nombre) {
        window.seleccionarCliente(data.id_cliente, data.cliente_nombre, data.cliente_email || '');
    }

    // Cargar selectores
    if (data.id_fase) document.getElementById('fase_id').value = data.id_fase;
    if (data.id_clasificacion) document.getElementById('clasificacion_id').value = data.id_clasificacion;
    if (data.id_sucursal_asignada) document.getElementById('sucursal_asignada_id').value = data.id_sucursal_asignada;
    if (data.certeza) document.getElementById('certeza').value = data.certeza;
    if (data.comentarios) document.getElementById('comentarios').value = data.comentarios;
    if (data.id_convenio_general) document.getElementById('convenio_general').value = data.id_convenio_general;

    // Cargar artículos
    if (data.articulos && Array.isArray(data.articulos)) {
        articulosSeleccionados = data.articulos.map(art => ({
            id_producto: art.id_producto,
            nombre: art.nombre,
            codbar: art.codbar,
            precio: parseFloat(art.precio),
            cantidad: art.cantidad,
            descuento: art.descuento || 0,
            id_convenio: art.id_convenio,
            id_sucursal_surtido: art.id_sucursal_surtido,
            num_familia: art.num_familia,
            inventario_disponible: art.inventario_disponible,
            nombre_sucursal_surtido: art.nombre_sucursal_surtido
        }));
        renderizarTablaArticulos();
    }
}

// Función para limpiar todo el formulario
function limpiarFormularioCotizacion() {
    if (typeof window.limpiarCliente === 'function') window.limpiarCliente();
    articulosSeleccionados = [];
    renderizarTablaArticulos();
    document.getElementById('buscarArticuloModal').value = '';
    document.getElementById('resultadosArticulos').style.display = 'none';
    document.getElementById('clasificacion_id').value = '';
    document.getElementById('sucursal_asignada_id').value = '';
    document.getElementById('comentarios').value = '';
    document.getElementById('convenio_general').value = '';
    document.getElementById('certeza').value = '1';
    
    // Restablecer la fase "En proceso" por defecto
    if (catalogos.fase_en_proceso_id) {
        document.getElementById('fase_id').value = catalogos.fase_en_proceso_id;
    } else {
        document.getElementById('fase_id').value = '';
    }
    
    resetearFormularioEdicionCliente();
}

// ============================================
// GUARDAR COTIZACIÓN
// ============================================
window.guardarNuevaCotizacion = function() {
    const clienteId = document.getElementById('cliente_id').value;
    const faseId = document.getElementById('fase_id').value;
    
    if (!clienteId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona un cliente', 'warning');
        return;
    }
    
    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }
    
    if (articulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }
    
    const articulos = articulosSeleccionados.map((a) => ({
        id_producto: a.id_producto,
        cantidad: a.cantidad,
        precio_unitario: a.precio,
        descuento: a.descuento,
        id_convenio: a.id_convenio,
        id_sucursal_surtido: a.id_sucursal_surtido
    }));
    
    let url = '{{ route("ventas.cotizaciones.store") }}';
    let method = 'POST';
    
    if (esNuevaVersion && cotizacionOrigenId) {
        url = `/ventas/cotizaciones/${cotizacionOrigenId}/guardar-version`;
        method = 'POST';
    }
    
    const formData = {
        id_cliente: parseInt(clienteId),
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('clasificacion_id').value || null,
        id_sucursal_asignada: document.getElementById('sucursal_asignada_id').value || null,
        certeza: parseInt(document.getElementById('certeza')?.value || 0),
        comentarios: document.getElementById('comentarios').value,
        articulos: articulos,
        _token: '{{ csrf_token() }}'
    };
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaCotizacion'));
            modal.hide();
            esNuevaVersion = false;
            cotizacionOrigenId = null;
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// CARGAR DATOS PARA EDITAR COTIZACIÓN
// ============================================
window.cargarDatosEditarCotizacion = function(cotizacionId) {
    console.log('Cargando datos de cotización para editar ID:', cotizacionId);
    
    // Mostrar loading si es necesario
    if (window.mostrarToast) {
        window.mostrarToast('Cargando datos de la cotización...', 'info');
    }
    
    fetch(`/ventas/cotizaciones/${cotizacionId}`, {
        headers: { 
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cotizacion = data.data;
            
            // Seleccionar cliente
            if (cotizacion.id_cliente && cotizacion.cliente) {
                const cliente = cotizacion.cliente;
                const nombreCompleto = `${cliente.Nombre} ${cliente.apPaterno || ''} ${cliente.apMaterno || ''}`.trim();
                const emailCliente = cliente.email1 || '';
                const telefono1 = cliente.telefono1 || '';
                const telefono2 = cliente.telefono2 || '';
                const domicilio = cliente.Domicilio || '';
                const titulo = cliente.titulo || '';
                
                window.seleccionarCliente(
                    cotizacion.id_cliente, 
                    nombreCompleto, 
                    emailCliente, 
                    telefono1, 
                    telefono2, 
                    domicilio, 
                    titulo
                );
            }
            
            // Cargar selectores
            if (cotizacion.id_fase) {
                document.getElementById('fase_id').value = cotizacion.id_fase;
            }
            if (cotizacion.id_clasificacion) {
                document.getElementById('clasificacion_id').value = cotizacion.id_clasificacion;
            }
            if (cotizacion.id_sucursal_asignada) {
                document.getElementById('sucursal_asignada_id').value = cotizacion.id_sucursal_asignada;
            }
            if (cotizacion.certeza) {
                document.getElementById('certeza').value = cotizacion.certeza;
            }
            if (cotizacion.comentarios) {
                document.getElementById('comentarios').value = cotizacion.comentarios;
            }
            
            // Cargar artículos
            if (cotizacion.detalles && cotizacion.detalles.length > 0) {
                articulosSeleccionados = cotizacion.detalles.map(detalle => ({
                    id_producto: detalle.id_producto,
                    nombre: detalle.descripcion,
                    codbar: detalle.codbar || '',
                    precio: parseFloat(detalle.precio_unitario),
                    cantidad: detalle.cantidad,
                    descuento: detalle.descuento || 0,
                    id_convenio: detalle.id_convenio,
                    id_sucursal_surtido: detalle.id_sucursal_surtido,
                    num_familia: detalle.producto?.num_familia || '',
                    inventario_disponible: detalle.producto?.inventario || 0,
                    nombre_sucursal_surtido: detalle.sucursal_surtido?.nombre || 'No asignada'
                }));
                renderizarTablaArticulos();
            }
            
            if (window.mostrarToast) {
                window.mostrarToast('Datos cargados correctamente', 'success');
            }
        } else {
            console.error('Error al cargar cotización:', data.message);
            if (window.mostrarToast) {
                window.mostrarToast(data.message || 'Error al cargar la cotización', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error de red:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error de conexión al cargar la cotización', 'danger');
        }
    });
};

// ============================================
// EVENT LISTENERS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    cargarCatalogos();
    
    const buscadorClientes = document.getElementById('buscarClienteCotizacion');
    if (buscadorClientes) {
        buscadorClientes.addEventListener('input', function() {
            clearTimeout(timeoutBusquedaCliente);
            timeoutBusquedaCliente = setTimeout(() => buscarClientes(this.value), 300);
        });
    }
    
    const buscadorArticulos = document.getElementById('buscarArticuloModal');
    if (buscadorArticulos) {
        buscadorArticulos.addEventListener('input', function() {
            buscarArticulos(this.value);
        });
    }
    
    document.addEventListener('click', function(event) {
        const resultadosClientes = document.getElementById('resultadosClientes');
        const resultadosArticulos = document.getElementById('resultadosArticulos');
        const buscadorClientes = document.getElementById('buscarClienteCotizacion');
        const buscadorArticulos = document.getElementById('buscarArticuloModal');
        
        if (resultadosClientes && !resultadosClientes.contains(event.target) && event.target !== buscadorClientes) {
            resultadosClientes.style.display = 'none';
        }
        if (resultadosArticulos && !resultadosArticulos.contains(event.target) && event.target !== buscadorArticulos) {
            resultadosArticulos.style.display = 'none';
        }
    });
    
    const modalElement = document.getElementById('modalNuevaCotizacion');
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function() {
            if (!esNuevaVersion) {
                console.log('Limpiando modal (nueva cotización normal)');
                limpiarFormularioCotizacion();
                
                // Establecer la sucursal asignada del usuario logueado (solo si es mayor a 0)
                const sucursalSelect = document.getElementById('sucursal_asignada_id');
                if (sucursalSelect && window.sucursalUsuarioDefecto && window.sucursalUsuarioDefecto > 0) {
                    // Verificar que el valor exista en el select
                    let optionExists = false;
                    for (let i = 0; i < sucursalSelect.options.length; i++) {
                        if (sucursalSelect.options[i].value == window.sucursalUsuarioDefecto) {
                            optionExists = true;
                            break;
                        }
                    }
                    if (optionExists) {
                        sucursalSelect.value = window.sucursalUsuarioDefecto;
                        console.log('Sucursal asignada por defecto (usuario):', window.sucursalUsuarioDefecto);
                    }
                }
            } else {
                console.log('Modal en modo nueva versión, cargando datos de cotización origen ID:', cotizacionOrigenId);
                // Intentar cargar datos desde el servidor
                fetch(`/ventas/cotizaciones/${cotizacionOrigenId}/preparar-version`)
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            precargarDatosCotizacion(response.data);
                        } else {
                            console.error('Error al precargar cotización:', response.message);
                            limpiarFormularioCotizacion();
                        }
                    })
                    .catch(err => {
                        console.error('Error de red al precargar cotización:', err);
                        limpiarFormularioCotizacion();
                    });
            }
        });
        
        modalElement.addEventListener('hidden.bs.modal', function() {
            esNuevaVersion = false;
            cotizacionOrigenId = null;
            window.datosCotizacionOrigen = null;
        });
    }
    
    const convenioGeneral = document.getElementById('convenio_general');
    if (convenioGeneral) {
        convenioGeneral.addEventListener('change', function() {
            const convenioId = this.value;
            
            if (convenioId && catalogos.convenios) {
                const convenio = catalogos.convenios.find(c => c.id == convenioId);
                if (convenio && convenio.familias) {
                    articulosSeleccionados.forEach((articulo) => {
                        const familiaConDescuento = convenio.familias.find(f => f.num_familia === articulo.num_familia);
                        if (familiaConDescuento) {
                            articulo.descuento = familiaConDescuento.descuento;
                            articulo.id_convenio = convenio.id;
                        } else {
                            articulo.descuento = 0;
                            articulo.id_convenio = null;
                        }
                    });
                    renderizarTablaArticulos();
                }
            } else {
                articulosSeleccionados.forEach(articulo => {
                    articulo.descuento = 0;
                    articulo.id_convenio = null;
                });
                renderizarTablaArticulos();
            }
        });
    }
    
    // Inicializar botones de cliente
    const btnGuardar = document.getElementById('btnGuardarNuevoCliente');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarNuevoClienteHandler);
    }
    
    const btnCancelar = document.getElementById('btnCancelarNuevoCliente');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', cancelarEdicionHandler);
    }
    
    const btnMostrarNuevoCliente = document.getElementById('btnMostrarNuevoCliente');
    if (btnMostrarNuevoCliente) {
        btnMostrarNuevoCliente.addEventListener('click', function() {
            const container = document.getElementById('formNuevoClienteContainer');
            if (container) {
                const isVisible = container.style.display !== 'none';
                if (isVisible) {
                    resetearFormularioEdicionCliente();
                } else {
                    container.style.display = 'block';
                    const btnGuardarLocal = document.getElementById('btnGuardarNuevoCliente');
                    if (btnGuardarLocal && !btnGuardarLocal.hasAttribute('data-cliente-id')) {
                        btnGuardarLocal.textContent = 'Guardar y seleccionar';
                        btnGuardarLocal.removeEventListener('click', actualizarClienteHandler);
                        btnGuardarLocal.addEventListener('click', guardarNuevoClienteHandler);
                    }
                    document.getElementById('nuevo_cliente_nombre').value = '';
                    document.getElementById('nuevo_cliente_apellido_paterno').value = '';
                    document.getElementById('nuevo_cliente_apellido_materno').value = '';
                    document.getElementById('nuevo_cliente_email').value = '';
                    document.getElementById('nuevo_cliente_telefono').value = '';
                    document.getElementById('nuevo_cliente_domicilio').value = '';
                }
            }
        });
    }
});
</script>
@endpush