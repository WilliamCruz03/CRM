<!-- Modal Nueva Cotización -->
<div class="modal fade" id="modalNuevaCotizacion" tabindex="-1" aria-labelledby="modalNuevaCotizacionLabel">
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
                                            placeholder="Buscar por nombre o telefono..."
                                            onkeyup="window.aMayusculas(event)"
                                            autocomplete="off">
                                    </div>
                                    <button type="button" class="btn btn-primary" id="btnMostrarNuevoCliente">
                                        <i class="bi bi-plus-circle"></i> Nuevo Cliente
                                    </button>
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente. <b class="text-success">HAZ CLIC EN UNO PARA SELECCIONARLO.</b></small>

                                <!-- FORMULARIO PARA NUEVO CLIENTE (oculto inicialmente) -->
                                <div id="formNuevoClienteContainer" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                                    <h6 class="mb-3"><i class="bi bi-person-plus"></i> Registrar nuevo cliente</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_nombre" 
                                                placeholder="Nombre *"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_apellido_paterno" 
                                                placeholder="Apellido paterno *"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_apellido_materno" 
                                                placeholder="Apellido materno"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)">
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
                                                onkeypress="return window.soloNumeros(event)">
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
                                <div class="col-md-12 mb-3">
                                <div class="card card-light border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-calendar-check text-success me-2"></i>
                                            Fecha y Hora de Entrega Sugerida
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Fecha de Entrega</label>
                                                <input type="date" class="form-control" id="fecha_entrega_sugerida" 
                                                    name="fecha_entrega_sugerida">
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="recalcularFechaEntrega()">
                                                <i class="bi bi-arrow-repeat"></i> Recalcular
                                            </button>
                                            <span class="ms-2 text-muted small">
                                                <i class="bi bi-info-circle"></i> 
                                                La fecha se calcula según disponibilidad de stock y horario
                                            </span>
                                        </div>
                                    </div>
                                </div>
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
                                        placeholder="Buscar por nombre, código o sustancia activa"
                                        autocomplete="off"
                                        style="padding-right: 35px;">
                                </div>

                                <!-- Botón para mostrar/ocultar formulario de producto externo -->
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> Puedes buscar por nombre del producto, código EAN o sustancia activa
                                    </small>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnMostrarExterno">
                                        <i class="bi bi-plus-circle"></i> Producto externo
                                    </button>
                                </div>

                                <!-- FORMULARIO PARA PRODUCTO EXTERNO (oculto inicialmente) -->
                                <div id="formProductoExternoContainer" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                                    <h6 class="mb-3"><i class="bi bi-truck"></i> Registrar producto externo</h6>
                                    <div class="row">
                                        <div class="col-md-8 mb-2">
                                            <input type="text" class="form-control" id="externo_descripcion" 
                                                placeholder="Descripción del producto *"
                                                autocomplete="off"
                                                onkeyup="window.aMayusculas(event)">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="number" class="form-control" id="externo_precio" 
                                                placeholder="Precio *" 
                                                step="0.50"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <button type="button" class="btn btn-secondary" id="btnCancelarExterno">Cancelar</button>
                                        <button type="button" class="btn btn-success" id="btnGuardarExterno">Guardar producto</button>
                                    </div>
                                </div>
                                
                                <div id="resultadosArticulos" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Artículos encontrados <b class="text-success">(HAZ CLICK PARA AGREGAR)</b></small>
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
let abortController = null;
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
};

// Función para establecer el modo nueva versión desde fuera del modal
window.setEsNuevaVersion = function(valor, origenId) {
    esNuevaVersion = valor;
    cotizacionOrigenId = origenId;
};

// ============================================
// CARGA DE CATÁLOGOS
// ============================================
function cargarCatalogos() {
    // Usar originalFetch para evitar el interceptor
    const originalFetch = window.originalFetch || window.fetch;
    
    return originalFetch('{{ route("ventas.cotizaciones.catalogos") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => {
        if (!response.ok) {
            // Si es 500, leer el mensaje de error del JSON
            if (response.status === 500) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Error del servidor al cargar catálogos');
                });
            }
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Procesar datos...
            catalogos = data.data;
            
            // Llenar selects del modal
            const faseSelect = document.getElementById('fase_id');
            const clasificacionSelect = document.getElementById('clasificacion_id');
            const sucursalSelect = document.getElementById('sucursal_asignada_id');
            const convenioGeneralSelect = document.getElementById('convenio_general');
            
            if (faseSelect && catalogos.fases) {
                faseSelect.innerHTML = '<option value="">Seleccionar fase...</option>' + 
                    catalogos.fases.map(f => `<option value="${f.id_fase}">${f.fase}</option>`).join('');
                
                if (catalogos.fase_en_proceso_id) {
                    faseSelect.value = catalogos.fase_en_proceso_id;
                }
            }
            
            if (clasificacionSelect && catalogos.clasificaciones) {
                clasificacionSelect.innerHTML = '<option value="">Seleccionar clasificación...</option>' + 
                    catalogos.clasificaciones.map(c => `<option value="${c.id_clasificacion}">${c.clasificacion}</option>`).join('');
            }
            
            if (sucursalSelect && catalogos.sucursales) {
                sucursalSelect.innerHTML = '<option value="">Seleccionar sucursal...</option>' + 
                    catalogos.sucursales.map(s => `<option value="${s.id_sucursal}">${s.nombre}</option>`).join('');
            }
            
            if (convenioGeneralSelect && catalogos.convenios) {
                convenioGeneralSelect.innerHTML = '<option value="">Sin convenio</option>' + 
                    catalogos.convenios.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            }
            
            return data;
        } else {
            throw new Error(data.message || 'Error al cargar catálogos');
        }
    })
    .catch(error => {
        console.error('Error al cargar catálogos:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error al cargar catálogos: ' + error.message, 'danger');
        }
        throw error;
    });
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
                const id = cliente.id || 0;
                const nombre = cliente.nombre_completo || '';
                const email = cliente.email1 || '';
                const telefono1 = cliente.telefono1 || '';
                const telefono2 = cliente.telefono2 || '';
                const titulo = cliente.titulo || '';
                const domicilio = cliente.domicilio || '';
                const localidadNombre = cliente.localidad_nombre || '';
                const interesesHtml = cliente.intereses_html || '';
                const patologiasHtml = cliente.patologias_html || '';

                // Construir HTML del contacto
                let contactoHtml = '';
                let tieneContacto = false;

                if (telefono1 && telefono1 !== 'null' && telefono1 !== '') {
                    contactoHtml += `<i class="bi bi-telephone"></i> ${escapeHtml(telefono1)}<br>`;
                    tieneContacto = true;
                }
                if (telefono2 && telefono2 !== 'null' && telefono2 !== '') {
                    contactoHtml += `<i class="bi bi-telephone"></i> ${escapeHtml(telefono2)} (secundario)<br>`;
                    tieneContacto = true;
                }
                if (email && email !== 'null' && email !== '') {
                    contactoHtml += `<i class="bi bi-envelope"></i> ${escapeHtml(email)}`;
                    tieneContacto = true;
                }

                if (!tieneContacto) {
                    contactoHtml = '<span class="text-muted">Sin contacto</span>';
                }

                let tituloHtml = '';
                if (titulo && titulo !== 'null' && titulo.trim() !== '') {
                    tituloHtml = `<br><small class="text-muted">${escapeHtml(titulo)}</small>`;
                }

                // Dirección
                let direccionHtml = '';
                if (domicilio && domicilio !== 'null' && domicilio.trim() !== '') {
                    direccionHtml = `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(domicilio)}`;
                    if (localidadNombre && localidadNombre !== 'null' && localidadNombre.trim() !== '') {
                        direccionHtml += `, ${escapeHtml(localidadNombre)}`;
                    }
                    direccionHtml += `</small>`;
                } else if (localidadNombre && localidadNombre !== 'null' && localidadNombre.trim() !== '') {
                    direccionHtml = `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(localidadNombre)}</small>`;
                }

                // Escapar valores para onclick
                const nombreEscapado = escapeHtml(nombre).replace(/'/g, "\\'");

                return `
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" style="cursor: pointer;">
                        <div class="flex-grow-1" 
                             data-cliente-id="${id}"
                             data-cliente-nombre="${escapeHtml(nombre)}"
                             data-cliente-email="${escapeHtml(email)}"
                             data-cliente-telefono1="${escapeHtml(telefono1)}"
                             data-cliente-telefono2="${escapeHtml(telefono2)}"
                             data-cliente-domicilio="${escapeHtml(domicilio)}"
                             data-cliente-titulo="${escapeHtml(titulo)}"
                             data-cliente-localidad="${escapeHtml(localidadNombre)}"
                             data-cliente-intereses="${escapeHtml(interesesHtml)}"
                             data-cliente-patologias="${escapeHtml(patologiasHtml)}"
                             onclick="seleccionarClienteDesdeData(this)">
                            <div>
                                <strong>${escapeHtml(nombre)}</strong>
                                ${tituloHtml}
                                <div class="small text-muted">${contactoHtml}</div>
                                ${direccionHtml}
                                ${interesesHtml ? `<div class="mt-1"><small class="text-muted"><i class="bi bi-tags"></i> Intereses: ${interesesHtml}</small></div>` : ''}
                                ${patologiasHtml ? `<div class="mt-1"><small class="text-muted"><i class="bi bi-heart-pulse"></i> Patologías: ${patologiasHtml}</small></div>` : ''}
                            </div>
                        </div>
                        <div class="ms-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="event.stopPropagation(); editarClienteExistente(${id}, '${escapeHtml(cliente.Nombre || '').replace(/'/g, "\\'")}', '${escapeHtml(cliente.apPaterno || '').replace(/'/g, "\\'")}', '${escapeHtml(cliente.apMaterno || '').replace(/'/g, "\\'")}', '${escapeHtml(email).replace(/'/g, "\\'")}', '${escapeHtml(telefono1).replace(/'/g, "\\'")}', '${escapeHtml(telefono2).replace(/'/g, "\\'")}', '${escapeHtml(domicilio).replace(/'/g, "\\'")}')">
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
    if (str === null || str === undefined) return '';
    if (typeof str !== 'string') str = String(str);
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

window.seleccionarClienteDesdeData = function(element) {
    const id = element.dataset.clienteId;
    const nombre = element.dataset.clienteNombre || '';
    const email = element.dataset.clienteEmail || '';
    const telefono1 = element.dataset.clienteTelefono1 || '';
    const telefono2 = element.dataset.clienteTelefono2 || '';
    const domicilio = element.dataset.clienteDomicilio || '';
    const titulo = element.dataset.clienteTitulo || '';
    const localidadNombre = element.dataset.clienteLocalidad || '';
    const interesesHtml = element.dataset.clienteIntereses || '';
    const patologiasHtml = element.dataset.clientePatologias || '';
    
    seleccionarCliente(id, nombre, email, telefono1, telefono2, domicilio, titulo, localidadNombre, interesesHtml, patologiasHtml);
};

window.seleccionarCliente = function(id, nombre, email, telefono1, telefono2, domicilio, titulo, localidadNombre, interesesHtml, patologiasHtml) {
    document.getElementById('cliente_id').value = id;
    
    let html = `<div><strong>${escapeHtml(nombre)}</strong>`;
    
    if (titulo && titulo !== 'null' && titulo.trim() !== '') {
        html += `<br><small class="text-muted">${escapeHtml(titulo)}</small>`;
    }
    
    // Contacto en una sola línea
    let contactoParts = [];
    if (telefono1 && telefono1 !== 'null' && telefono1 !== '') {
        contactoParts.push(`<i class="bi bi-telephone"></i> ${escapeHtml(telefono1)}`);
    }
    if (telefono2 && telefono2 !== 'null' && telefono2 !== '') {
        contactoParts.push(`<i class="bi bi-telephone"></i> ${escapeHtml(telefono2)} (secundario)`);
    }
    if (email && email !== 'null' && email !== '') {
        contactoParts.push(`<i class="bi bi-envelope"></i> ${escapeHtml(email)}`);
    }
    
    if (contactoParts.length > 0) {
        html += `<br><small class="text-muted">${contactoParts.join(' | ')}</small>`;
    }
    
    // Dirección
    let direccionCompleta = '';
    if (domicilio && domicilio !== 'null' && domicilio.trim() !== '') {
        direccionCompleta = escapeHtml(domicilio);
        if (localidadNombre && localidadNombre !== 'null' && localidadNombre.trim() !== '') {
            direccionCompleta += `, ${escapeHtml(localidadNombre)}`;
        }
    } else if (localidadNombre && localidadNombre !== 'null' && localidadNombre.trim() !== '') {
        direccionCompleta = escapeHtml(localidadNombre);
    }
    
    if (direccionCompleta) {
        html += `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${direccionCompleta}</small>`;
    }
    
    // Intereses (solo un icono)
    if (interesesHtml && interesesHtml !== 'null' && interesesHtml.trim() !== '') {
        html += `<br><small class="text-muted"><i class="bi bi-tags"></i> ${interesesHtml}</small>`;
    }
    
    // Patologías (solo un icono)
    if (patologiasHtml && patologiasHtml !== 'null' && patologiasHtml.trim() !== '') {
        html += `<br><small class="text-muted"><i class="bi bi-heart-pulse"></i> ${patologiasHtml}</small>`;
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
            
            // Resetear el formulario de edición
            resetearFormularioEdicionCliente();
            
            // Limpiar la búsqueda
            document.getElementById('buscarClienteCotizacion').value = '';
            document.getElementById('resultadosClientes').style.display = 'none';
            
            // Seleccionar automáticamente el cliente editado ---
            const nombreCompleto = `${nombre} ${apellidoPaterno} ${apellidoMaterno || ''}`.trim();
            const emailCliente = email || '';
            const telefono1Cliente = telefono1 || '';
            const telefono2Cliente = telefono2 || '';
            const domicilioCliente = domicilio || '';
            
            // Usar la función seleccionarCliente para actualizar la UI
            if (typeof window.seleccionarCliente === 'function') {
                window.seleccionarCliente(
                    clienteId, 
                    nombreCompleto, 
                    emailCliente, 
                    telefono1Cliente, 
                    telefono2Cliente, 
                    domicilioCliente, 
                    '' // título (si no se tiene)
                );
            } else {
                // Fallback
                document.getElementById('cliente_id').value = clienteId;
                let html = `<div><strong>${escapeHtml(nombreCompleto)}</strong>`;
                
                let contactoParts = [];
                if (telefono1Cliente) contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono1Cliente}`);
                if (telefono2Cliente) contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono2Cliente} (secundario)`);
                if (emailCliente) contactoParts.push(`<i class="bi bi-envelope"></i> ${emailCliente}`);
                
                if (contactoParts.length > 0) {
                    html += `<br><small class="text-muted">${contactoParts.join(' | ')}</small>`;
                }
                
                if (domicilioCliente) {
                    html += `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(domicilioCliente)}</small>`;
                }
                
                html += `</div>`;
                document.getElementById('clienteInfo').innerHTML = html;
                document.getElementById('clienteSeleccionado').style.display = 'block';
                document.getElementById('buscarClienteCotizacion').value = nombreCompleto;
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
    
    document.getElementById('resultadosClientes').style.display = 'none';
    
    const container = document.getElementById('formNuevoClienteContainer');
    container.style.display = 'block';
    
    const formTitle = document.querySelector('#formNuevoClienteContainer h6');
    if (formTitle) {
        formTitle.innerHTML = '<i class="bi bi-pencil-square"></i> Editar cliente';
    }
    
    const btnGuardar = document.getElementById('btnGuardarNuevoCliente');
    if (btnGuardar) {
        btnGuardar.textContent = 'Actualizar y seleccionar';
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
    
    // Si el término está vacío o tiene menos de 3 caracteres, no buscar
    if (!termino || termino.length < 3) {
        const resultadosDiv = document.getElementById('resultadosArticulos');
        const listaResultados = document.getElementById('listaArticulos');
        
        if (resultadosDiv && listaResultados) {
            if (termino && termino.length > 0 && termino.length < 3) {
                // Mostrar mensaje informativo si tiene 1 o 2 caracteres
                listaResultados.innerHTML = `<div class="list-group-item text-muted">Escribe al menos 3 caracteres para buscar</div>`;
                resultadosDiv.style.display = 'block';
            } else {
                resultadosDiv.style.display = 'none';
            }
        }
        return;
    }
    
    clearTimeout(timeoutBusquedaArticulo);
    timeoutBusquedaArticulo = setTimeout(() => {
        let url = `{{ route("ventas.cotizaciones.productos.buscar") }}?q=${encodeURIComponent(termino)}`;
        
        // Agregar cotizacion_id si existe (para edición)
        if (window.cotizacionIdActual) {
            url += `&cotizacion_id=${window.cotizacionIdActual}`;
        }
        
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
                    
                    // Función segura para escape HTML
                    const safe = (val) => {
                        if (val === null || val === undefined) return '';
                        if (typeof val !== 'string') val = String(val);
                        return val
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                    };
                    
                    listaResultados.innerHTML = data.data.map((articulo, idx) => {
                        const yaExiste = articulosSeleccionados.some(a => a.codbar === articulo.codbar);
                        const esExterno = articulo.es_externo === true || articulo.es_externo === 1;
                        const stockClass = (articulo.inventario || 0) > 0 ? 'text-success' : 'text-danger';
                        const badgeClass = esExterno ? 'bg-info' : 'bg-primary';
                        const apartadoBadge = (articulo.apartado || 0) > 0 ? 
                            `<span class="badge bg-warning ms-1">Apartado: ${articulo.apartado}</span>` : '';
                        const existenteBadge = yaExiste ? 
                            '<span class="badge bg-warning ms-1">Ya agregado (se sumará)</span>' : '';
                        const externoBadge = esExterno ? 
                            '<span class="badge bg-info ms-1">Pedido a Proveedor</span>' : '';
                        
                        const sustanciaBadge = articulo.sustancias_activas && 
                            articulo.sustancias_activas !== 'No es medicamento' && 
                            articulo.sustancias_activas !== 'No coincide con la búsqueda' &&
                            articulo.sustancias_activas !== 'Error al cargar sustancia' &&
                            !esExterno ?
                            `<br><small class="text-info"><i class="bi bi-capsule"></i> Sustancia: <strong>${safe(articulo.sustancias_activas)}</strong></small>` : '';

                        //  Mostrar desglose por sucursal si existe
                        const detalleSucursalHtml = articulo.detalle_sucursales && articulo.detalle_sucursales !== '' ? 
                            `<br><small class="text-muted"><i class="bi bi-building"></i> Disponible por sucursal: ${safe(articulo.detalle_sucursales)}</small>` : '';

                        return `
                            <div class="list-group-item list-group-item-action" 
                                onclick="agregarArticuloPorIndiceNuevo(${idx})"
                                style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>${safe(articulo.nombre || 'Sin nombre')}</strong>
                                        ${sustanciaBadge}
                                        ${externoBadge}
                                        <br><small class="text-muted"><strong>Código: </strong>${safe(articulo.codbar || 'N/A')} | Precio: $${(articulo.precio || 0).toFixed(2)}</small>
                                        <br><small class="text-muted"><strong>Familia: </strong>${safe(articulo.num_familia || 'N/A')}</small>
                                        <br><span class="badge ${badgeClass} me-1">${esExterno ? 'Pedido a Proveedor' : 'Inventario Global'}</span>
                                        ${!esExterno ? `<span class="badge ${stockClass}">Stock global disponible: ${articulo.inventario || 0}</span>` : ''}
                                        ${detalleSucursalHtml}
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
                    let mensaje = `No se encontraron artículos con "${safe(termino)}"`;
                    listaResultados.innerHTML = `<div class="list-group-item text-muted">${mensaje}</div>`;
                    resultadosDiv.style.display = 'block';
                }
            }
        })
        .catch(error => {
            if (error.name === 'AbortError') {
                return;
            }
            console.error('Error buscando artículos:', error);
        });
    }, 300);
}

// Agregar articulo al listado o sumar si existe
window.agregarArticuloPorIndiceNuevo = function(idx) {
    
    if (!window.resultadosBusqueda || !window.resultadosBusqueda[idx]) {
        console.error('No hay resultadosBusqueda o índice inválido');
        return;
    }
    
    const articuloData = window.resultadosBusqueda[idx];
    
    // Verificar si hay stock disponible
    const stockDisponible = articuloData.inventario || 0;
    if (stockDisponible <= 0 && !articuloData.es_externo) {
        if (window.mostrarToast) {
            window.mostrarToast('No hay stock disponible de este artículo', 'warning');
        }
        return;
    }
    
    const nuevoArticulo = {
        nombre: articuloData.nombre,
        codbar: String(articuloData.codbar || ''),
        precio: parseFloat(articuloData.precio),
        cantidad: 1,
        descuento: 0,
        id_convenio: null,
        id_sucursal: articuloData.id_sucursal || null,
        num_familia: articuloData.num_familia || (articuloData.es_externo ? 'EXT' : ''),
        inventario_disponible: articuloData.inventario || 999,
        nombre_sucursal_surtido: articuloData.nombre_sucursal || (articuloData.es_externo ? 'Sobre Pedido' : 'No asignada'),
        es_externo: articuloData.es_externo == 1 || articuloData.es_externo === true || articuloData.es_externo === '1' ? 1 : 0
    };
    
    
    const convenioSelect = document.getElementById('convenio_general');
    if (convenioSelect && convenioSelect.value && catalogos.convenios) {
        const convenio = catalogos.convenios.find(c => c.id == convenioSelect.value);
        if (convenio && convenio.familias) {
            const familiaConDescuento = convenio.familias.find(f => f.num_familia === nuevoArticulo.num_familia);
            if (familiaConDescuento) {
                nuevoArticulo.descuento = familiaConDescuento.descuento;
                nuevoArticulo.id_convenio = convenio.id;
            }
        }
    }
    
    agregarOSumarArticuloNuevo(nuevoArticulo, articulosSeleccionados, false);
    
    document.getElementById('buscarArticuloModal').value = '';
    document.getElementById('resultadosArticulos').style.display = 'none';
};

// Agregar articulo al listado o sumar si existe
function agregarOSumarArticuloNuevo(articulo, listaArticulos, esEdicion = false) {
    
    // Buscar existencia con criterios claros
    const existe = listaArticulos.find(a => {        
        return a.codbar === articulo.codbar && 
            a.es_externo === articulo.es_externo;
    });
    
    if (existe) {
        const nuevaCantidad = existe.cantidad + 1;
        const maxDisponible = existe.inventario_disponible;
        
        if (nuevaCantidad <= maxDisponible) {
            existe.cantidad = nuevaCantidad;
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
        listaArticulos.push(articulo);
        if (window.mostrarToast) {
            window.mostrarToast(
                `Agregado "${articulo.nombre}" a la cotización.`, 
                'success'
            );
        }
    }
    
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
    const tbody = document.getElementById('articulosBody');
    if (!tbody) {
        console.error('No se encontró el elemento articulosBody');
        return;
    }
    
    let totalGeneral = 0;
    
    if (!articulosSeleccionados || articulosSeleccionados.length === 0) {
        tbody.innerHTML = `<tr id="sin-articulos-row">
            <td colspan="7" class="text-center py-4">
                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">No hay artículos agregados</p>
            </td>
        </tr>`;
        const totalElement = document.getElementById('totalCotizacion');
        if (totalElement) totalElement.textContent = '$0.00';
        return;
    }
    
    // Función segura para escape HTML
    const safeEscape = (val) => {
        if (val === null || val === undefined) return '';
        if (typeof val !== 'string') val = String(val);
        return val
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };
    
    let html = '';
    for (let index = 0; index < articulosSeleccionados.length; index++) {
        const articulo = articulosSeleccionados[index];
        
        // Valores por defecto para campos que podrían ser null/undefined
        const codbar = articulo.codbar || '-';
        const nombre = articulo.nombre || 'Sin nombre';
        const descuento = articulo.descuento || 0;
        const precio = parseFloat(articulo.precio) || 0;
        const cantidad = parseInt(articulo.cantidad) || 1;
        
        // USAR INVENTARIO_GLOBAL
        let maxDisponible = articulo.inventario_global || 999;
        if (articulo.es_externo) {
            maxDisponible = 999;
        }
        if (maxDisponible <= 0) {
            maxDisponible = 999;
        }
        
        const nombreSucursal = articulo.nombre_sucursal_surtido || 'No asignada';
        const detalleSucursales = articulo.detalle_sucursales || '';
        
        const precioConDescuento = precio * (1 - descuento / 100);
        const importe = cantidad * precioConDescuento;
        totalGeneral += importe;
        
        // Mostrar desglose si existe
        let desgloseHtml = '';
        if (detalleSucursales && detalleSucursales !== '') {
            desgloseHtml = `<br><small class="text-muted"><i class="bi bi-building"></i> Disponible por sucursal: ${safeEscape(detalleSucursales)}</small>`;
        } else if (articulo.es_externo) {
            desgloseHtml = `<br><small class="text-muted"><i class="bi bi-building"></i> No aplica (pedido a proveedor)</small>`;
        }
        
        html += `
            <tr id="articulo-row-${index}">
                <td class="text-center">${index + 1}</td>
                <td><small>${safeEscape(codbar)}</small></td>
                <td>
                    <strong>${safeEscape(nombre)}</strong>
                    ${articulo.es_externo ? '<br><span class="badge bg-info">Sobre Pedido</span>' : ''}
                    ${descuento > 0 ? `<br><small class="text-muted"><i class="bi bi-tag text-danger"></i> ${descuento}% descuento aplicado</small>` : ''}
                    <br><small class="text-muted">En inventario: ${safeEscape(nombreSucursal)} | Máx: ${maxDisponible}</small>
                    ${desgloseHtml}
                </td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center" 
                           value="${cantidad}" min="1" 
                           max="${maxDisponible}"
                           onchange="actualizarCantidad(${index}, this.value)"
                           style="width: 80px;">
                </td>
                <td class="text-end">
                    <span class="fw-bold">$${precioConDescuento.toFixed(2)}</span>
                    ${precio !== precioConDescuento ? `<br><small class="text-muted text-decoration-line-through">$${precio.toFixed(2)}</small>` : ''}
                </td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArticulo(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }
    
    tbody.innerHTML = html;
    const totalElement = document.getElementById('totalCotizacion');
    if (totalElement) totalElement.textContent = `$${totalGeneral.toFixed(2)}`;
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

    // Restablecer la fase "En proceso" por defecto
    if (catalogos.fase_en_proceso_id) {
        document.getElementById('fase_id').value = catalogos.fase_en_proceso_id;
    } else {
        document.getElementById('fase_id').value = '';
    }

    // Cargar selectores
    if (data.id_clasificacion) document.getElementById('clasificacion_id').value = data.id_clasificacion;
    if (data.id_sucursal_asignada) document.getElementById('sucursal_asignada_id').value = data.id_sucursal_asignada;
    if (data.certeza) document.getElementById('certeza').value = data.certeza;
    if (data.comentarios) document.getElementById('comentarios').value = data.comentarios;
    if (data.id_convenio_general) document.getElementById('convenio_general').value = data.id_convenio_general;

    console.log('🔍 Dato recibido para precarga:', data);
    console.log('📅 fecha_entrega_sugerida en precarga:', data.fecha_entrega_sugerida);

    // ASIGNAR FECHA DE ENTREGA SUGERIDA
    const fechaInput = document.getElementById('fecha_entrega_sugerida');
    if (fechaInput && data.fecha_entrega_sugerida) {
        let fechaEntrega = data.fecha_entrega_sugerida;
        // Si es string ISO, extraer solo la fecha
        if (typeof fechaEntrega === 'string' && fechaEntrega.includes('T')) {
            fechaEntrega = fechaEntrega.split('T')[0];
        }
        fechaInput.value = fechaEntrega;
    }

    // Cargar artículos
    if (data.articulos && Array.isArray(data.articulos)) {
        articulosSeleccionados = data.articulos.map(art => {
            return {
                nombre: art.nombre,
                codbar: art.codbar,
                precio: parseFloat(art.precio),
                cantidad: art.cantidad,
                descuento: art.descuento || 0,
                id_convenio: art.id_convenio,
                num_familia: art.num_familia || (art.es_externo == 1 ? 'EXT' : ''),
                inventario_disponible: art.inventario_disponible || (art.es_externo == 1 ? 999 : 0),
                nombre_sucursal_surtido: art.nombre_sucursal_surtido || (art.es_externo == 1 ? 'Pedido a Proveedor' : 'No asignada'),
                es_externo: art.es_externo == 1 ? 1 : 0
            };
        });
        renderizarTablaArticulos();
    }
}

function precargarDatosCotizacionIndependiente(cotizacion) {
    if (!cotizacion) return;
    
    // ============================================
    // INICIALIZAR ARRAY DE ARTÍCULOS VACÍO
    // ============================================
    articulosSeleccionados = [];

    // ============================================
    // SELECCIONAR CLIENTE
    // ============================================
    if (cotizacion.id_cliente && cotizacion.cliente) {
        const cliente = cotizacion.cliente;
        const nombreCompleto = `${cliente.Nombre || ''} ${cliente.apPaterno || ''} ${cliente.apMaterno || ''}`.trim();
        
        if (typeof window.seleccionarCliente === 'function') {
            window.seleccionarCliente(
                cotizacion.id_cliente, 
                nombreCompleto, 
                cliente.email1 || '', 
                cliente.telefono1 || '', 
                cliente.telefono2 || '', 
                cliente.Domicilio || '', 
                cliente.titulo || ''
            );
        }
    } else {
        console.warn('No se pudo cargar el cliente:', cotizacion.id_cliente);
    }

    // ============================================
    // CARGAR SELECTORES
    // ============================================
    if (cotizacion.id_clasificacion) {
        const clasificacionSelect = document.getElementById('clasificacion_id');
        if (clasificacionSelect) clasificacionSelect.value = cotizacion.id_clasificacion;
    }
    
    if (cotizacion.id_sucursal_asignada) {
        const sucursalSelect = document.getElementById('sucursal_asignada_id');
        if (sucursalSelect) sucursalSelect.value = cotizacion.id_sucursal_asignada;
    }
    
    if (cotizacion.certeza) {
        const certezaSelect = document.getElementById('certeza');
        if (certezaSelect) certezaSelect.value = cotizacion.certeza;
    }
    
    if (cotizacion.comentarios) {
        const comentariosTextarea = document.getElementById('comentarios');
        if (comentariosTextarea) comentariosTextarea.value = cotizacion.comentarios;
    }
    
    if (cotizacion.id_convenio_general) {
        const convenioSelect = document.getElementById('convenio_general');
        if (convenioSelect) convenioSelect.value = cotizacion.id_convenio_general;
    }

    // ============================================
    // FORZAR FASE "EN PROCESO"
    // ============================================
    if (catalogos.fase_en_proceso_id) {
        const faseSelect = document.getElementById('fase_id');
        if (faseSelect) faseSelect.value = catalogos.fase_en_proceso_id;
    }

    console.log('🔍 Dato recibido para precarga:', cotizacion);
    console.log('📅 fecha_entrega_sugerida en precarga:', cotizacion.fecha_entrega_sugerida);

    // ASIGNAR FECHA DE ENTREGA SUGERIDA (YA ESTÁ CORRECTO, usa cotizacion)
    const fechaInput = document.getElementById('fecha_entrega_sugerida');
    if (fechaInput && cotizacion.fecha_entrega_sugerida) {
        let fechaEntrega = cotizacion.fecha_entrega_sugerida;
        if (typeof fechaEntrega === 'string' && fechaEntrega.includes('T')) {
            fechaEntrega = fechaEntrega.split('T')[0];
        }
        fechaInput.value = fechaEntrega;
    }

    // ============================================
    // CARGAR ARTÍCULOS CON INVENTARIO CORRECTO
    // ============================================
    if (cotizacion.detalles && cotizacion.detalles.length > 0) {
        cotizacion.detalles.forEach(detalle => {
            const esExterno = detalle.es_externo == 1 || detalle.es_externo === true;
            
            // Obtener nombre del producto
            let nombre = detalle.descripcion || detalle.nombre_producto || '-';
            
            // Obtener datos del producto si existe
            let numFamilia = '';
            let inventarioGlobal = 0;
            let inventarioDisponible = 0;
            let nombreSucursalSurtido = '';
            let detalleSucursales = '';
            
            // OBTENER DATOS DEL DETALLE (prioridad)
            if (detalle.inventario_global) {
                inventarioGlobal = parseInt(detalle.inventario_global) || 0;
            }
            if (detalle.inventario_disponible) {
                inventarioDisponible = parseInt(detalle.inventario_disponible) || 0;
            }
            if (detalle.detalle_sucursales) {
                detalleSucursales = detalle.detalle_sucursales;
            }
            if (detalle.nombre_sucursal_surtido) {
                nombreSucursalSurtido = detalle.nombre_sucursal_surtido;
            }
            
            // Si no hay datos en el detalle, intentar obtener del producto
            if (detalle.producto) {
                if (!inventarioGlobal) {
                    inventarioGlobal = parseInt(detalle.producto.inventario) || 0;
                }
                if (!numFamilia) {
                    numFamilia = detalle.producto.num_familia || '';
                }
                if (!nombreSucursalSurtido && detalle.producto.sucursal) {
                    nombreSucursalSurtido = detalle.producto.sucursal.nombre || '';
                }
                // Si el producto tiene desglose de sucursales
                if (detalle.producto.detalle_sucursales && !detalleSucursales) {
                    detalleSucursales = detalle.producto.detalle_sucursales;
                }
            }
            
            // Si no hay sucursal surtido pero hay id_sucursal
            if (!nombreSucursalSurtido && detalle.id_sucursal && catalogos.sucursales) {
                const sucursal = catalogos.sucursales.find(s => s.id_sucursal == detalle.id_sucursal);
                nombreSucursalSurtido = sucursal ? sucursal.nombre : 'Sucursal ' + detalle.id_sucursal;
            }
            
            // PARA PRODUCTOS EXTERNOS
            if (esExterno) {
                numFamilia = 'EXT';
                inventarioGlobal = 999;
                inventarioDisponible = 999;
                nombreSucursalSurtido = 'Pedido a Proveedor';
                detalleSucursales = 'No aplica (pedido a proveedor)';
            }
            
            // Valores por defecto
            if (!nombreSucursalSurtido) nombreSucursalSurtido = 'No asignada';
            if (!numFamilia) numFamilia = '';
            
            // Si no hay inventario, usar 999 como fallback
            if (inventarioGlobal <= 0 && !esExterno) {
                inventarioGlobal = 999;
                inventarioDisponible = 999;
            }
            
            articulosSeleccionados.push({
                nombre: nombre,
                codbar: detalle.codbar || '',
                precio: parseFloat(detalle.precio_unitario || 0),
                cantidad: parseInt(detalle.cantidad || 1),
                descuento: parseFloat(detalle.descuento || 0),
                id_convenio: detalle.id_convenio,
                num_familia: numFamilia,
                inventario_global: inventarioGlobal,
                inventario_disponible: inventarioDisponible,
                nombre_sucursal_surtido: nombreSucursalSurtido,
                es_externo: esExterno ? 1 : 0,
                detalle_sucursales: detalleSucursales
            });
        });
        renderizarTablaArticulos();
    } else {
        console.warn('No hay detalles en la cotización');
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
        codbar: a.codbar || a.ean || '',
        cantidad: a.cantidad,
        precio_unitario: a.precio,
        descuento: a.descuento,
        id_convenio: a.id_convenio,
        es_externo: a.es_externo ? 1 : 0
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
            
            // Cerrar modal de forma segura
            const modalElement = document.getElementById('modalNuevaCotizacion');
            
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                }
            }
            
            // Eliminar manualmente el backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Eliminar la clase modal-open del body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            esNuevaVersion = false;
            cotizacionOrigenId = null;
            
            setTimeout(() => {
                refrescarTablaCotizaciones();
            }, 1000);
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
                    nombre: detalle.descripcion,
                    codbar: detalle.codbar || '',
                    precio: parseFloat(detalle.precio_unitario),
                    cantidad: detalle.cantidad,
                    descuento: detalle.descuento || 0,
                    id_convenio: detalle.id_convenio,
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
    
    // Variables para productos externos
    let incluirExternos = false;
    
    // ============================================
    // BÚSQUEDA DE ARTÍCULOS EXTERNOS (tmp_catalogo)
    // ============================================
    const buscarArticulosConExternos = function(termino) {
        //  Cancelar petición anterior si existe
        if (abortController) {
            abortController.abort();
        }
        
        const resultadosDiv = document.getElementById('resultadosArticulos');
        const listaResultados = document.getElementById('listaArticulos');
        
        // Solo buscar si tiene 3 o más caracteres
        if (!termino || termino.length < 3) {
            if (resultadosDiv) resultadosDiv.style.display = 'none';
            return;
        }
        
        clearTimeout(timeoutBusquedaArticulo);
        timeoutBusquedaArticulo = setTimeout(() => {
            // Crear nuevo abort controller
            abortController = new AbortController();
            
            let url = `{{ route("ventas.cotizaciones.productos.buscar") }}?q=${encodeURIComponent(termino)}`;
            
            if (window.cotizacionIdActual) {
                url += `&cotizacion_id=${window.cotizacionIdActual}`;
            }
            
            // Mostrar indicador de carga
            if (listaResultados) {
                listaResultados.innerHTML = '<div class="list-group-item text-muted"><i class="bi bi-hourglass-split"></i> Buscando...</div>';
                if (resultadosDiv) resultadosDiv.style.display = 'block';
            }
            
            fetch(url, {
                headers: { 'Accept': 'application/json' },
                signal: abortController.signal
            })
            .then(response => response.json())
            .then(data => {
                if (resultadosDiv && listaResultados) {
                    if (data.success && data.data && data.data.length > 0) {
                        window.resultadosBusqueda = data.data;
                        
                        const safe = (val) => {
                            if (val === null || val === undefined) return '';
                            if (typeof val !== 'string') val = String(val);
                            return val
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#39;');
                        };
                        
                        listaResultados.innerHTML = data.data.map((articulo, idx) => {
                            const yaExiste = window.articulosSeleccionados ? 
                                window.articulosSeleccionados.some(a => a.codbar === articulo.codbar) : false;
                            const esExterno = articulo.es_externo === true || articulo.es_externo === 1;
                            
                            const badgeClass = esExterno ? 'bg-info' : 'bg-primary';
                            const externoBadge = esExterno ? 
                                '<span class="badge bg-info ms-1">Sobre Pedido</span>' : '';
                            const stockClass = (articulo.inventario || 0) > 0 ? 'text-success' : 'text-danger';
                            const apartadoBadge = (articulo.apartado || 0) > 0 ? 
                                `<span class="badge bg-warning ms-1">Apartado: ${articulo.apartado}</span>` : '';
                            const existenteBadge = yaExiste ? 
                                '<span class="badge bg-warning ms-1">Ya agregado (se sumará)</span>' : '';

                            const sustanciaBadge = articulo.sustancias_activas && 
                                articulo.sustancias_activas !== 'No es medicamento' && 
                                articulo.sustancias_activas !== 'No coincide con la búsqueda' &&
                                articulo.sustancias_activas !== 'Error al cargar sustancia' &&
                                !esExterno ?
                                `<br><small class="text-info"><i class="bi bi-capsule"></i> Sustancia: <strong>${safe(articulo.sustancias_activas)}</strong></small>` : '';

                            // Desglose de inventario por sucursal
                            const detalleSucursalHtml = articulo.detalle_sucursales && articulo.detalle_sucursales !== '' ? 
                                `<br><small class="text-muted"><i class="bi bi-box2 text-success"></i><b> Disponible en sucursal: </b>${safe(articulo.detalle_sucursales)}</small>` : '';

                            const inventarioGlobalHtml = !esExterno ? 
                                `<span class="badge ${stockClass}">Stock: ${articulo.inventario || 0}</span>` : 
                                `<span class="badge bg-info">Sobre Pedido</span>`;

                            return `
                                <div class="list-group-item list-group-item-action" 
                                    onclick="agregarArticuloPorIndiceNuevo(${idx})"
                                    style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>${safe(articulo.nombre || 'Sin nombre')}</strong>
                                            ${externoBadge}
                                            ${sustanciaBadge}
                                            <br><small class="text-muted"><strong>Código: </strong>${safe(articulo.codbar || 'N/A')} | Precio: $${(articulo.precio || 0).toFixed(2)}</small>
                                            <br><small class="text-muted"><strong>Familia: </strong>${safe(articulo.num_familia || 'N/A')}</small>
                                            <br><span class="badge ${badgeClass} me-1">${esExterno ? 'Pedido a Proveedor' : 'Inventario Global'}</span>
                                            ${inventarioGlobalHtml}
                                            ${detalleSucursalHtml}
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
                        let mensaje = `No se encontraron artículos con "${termino}"`;
                        listaResultados.innerHTML = `<div class="list-group-item text-muted">${mensaje}</div>`;
                        resultadosDiv.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                // Ignorar errores de abort (son normales)
                if (error.name === 'AbortError') {
                    return;
                }
                console.error('Error buscando artículos:', error);
            });
        }, 500);
    };

    // Asignar la función al buscador de artículos
    const buscadorArticulos = document.getElementById('buscarArticuloModal');
    if (buscadorArticulos) {
        buscadorArticulos.addEventListener('input', function() {
            const termino = this.value.trim();
            
            // Si el buscador está vacío, ocultar resultados inmediatamente
            if (termino === '') {
                const resultadosDiv = document.getElementById('resultadosArticulos');
                if (resultadosDiv) {
                    resultadosDiv.style.display = 'none';
                }
                return;
            }
            
            // Si tiene contenido, buscar
            buscarArticulosConExternos(termino);
        });
    }
    
    
    // Función para guardar producto externo
    window.guardarProductoExterno = function() {
        const descripcion = document.getElementById('externo_descripcion')?.value.trim();
        const precio = document.getElementById('externo_precio')?.value;
        
        if (!descripcion) {
            if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
            return;
        }
        
        if (!precio || parseFloat(precio) <= 0) {
            if (window.mostrarToast) window.mostrarToast('El precio es requerido y debe ser mayor a 0', 'warning');
            return;
        }
        
        // Mostrar loading en el botón
        const btn = document.querySelector('#modalAgregarExterno .btn-success');
        const textoOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
        
        fetch('{{ route("ventas.cotizaciones.guardar-producto-externo") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                descripcion: descripcion,
                precio: parseFloat(precio)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar el modal de agregar externo
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarExterno'));
                if (modal) modal.hide();
                
                // Limpiar campos
                document.getElementById('externo_descripcion').value = '';
                document.getElementById('externo_precio').value = '';
                
                // Crear el objeto del nuevo artículo para agregarlo directamente
                const nuevoArticulo = {
                    id: data.data.id,
                    id_sucursal: null,
                    nombre_sucursal: null,
                    codbar: data.data.ean,
                    nombre: data.data.descripcion,
                    precio: data.data.precio,
                    inventario: 999,
                    inventario_original: 999,
                    apartado: 0,
                    num_familia: 'EXT',
                    sustancias_activas: '(Pedido a proveedor)',
                    es_medicamento: false,
                    es_externo: true
                };
                
                // Agregar el producto directamente a la cotización
                // Crear el objeto en el formato que espera agregarArticuloPorIndiceNuevo
                const articuloData = {
                    id: nuevoArticulo.id,
                    id_sucursal: nuevoArticulo.id_sucursal,
                    nombre_sucursal: nuevoArticulo.nombre_sucursal,
                    codbar: nuevoArticulo.codbar,
                    nombre: nuevoArticulo.nombre,
                    precio: nuevoArticulo.precio,
                    inventario: nuevoArticulo.inventario,
                    num_familia: nuevoArticulo.num_familia,
                    es_externo: nuevoArticulo.es_externo,
                    es_medicamento: nuevoArticulo.es_medicamento,
                    sustancias_activas: nuevoArticulo.sustancias_activas
                };
                
                // Agregar temporalmente a los resultados de búsqueda para que exista
                if (!window.resultadosBusqueda) {
                    window.resultadosBusqueda = [];
                }
                window.resultadosBusqueda.unshift(articuloData);
                
                // Agregar el artículo a la cotización usando la función existente
                agregarArticuloPorIndiceNuevo(0);
                
                // Mostrar mensaje de éxito
                if (window.mostrarToast) window.mostrarToast('Producto externo guardado y agregado a la cotización', 'success');
                
                // Limpiar el buscador por si acaso
                const buscador = document.getElementById('buscarArticuloModal');
                if (buscador) buscador.value = '';
                
                // Ocultar resultados de búsqueda
                const resultadosDiv = document.getElementById('resultadosArticulos');
                if (resultadosDiv) resultadosDiv.style.display = 'none';
                
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        });
    };
    
    // Evento del botón para mostrar/ocultar formulario de producto externo
    const btnMostrarExterno = document.getElementById('btnMostrarExterno');
    const formExternoContainer = document.getElementById('formProductoExternoContainer');
    const btnCancelarExterno = document.getElementById('btnCancelarExterno');
    const btnGuardarExterno = document.getElementById('btnGuardarExterno');

    if (btnMostrarExterno) {
        btnMostrarExterno.addEventListener('click', function() {
            if (formExternoContainer.style.display === 'none' || formExternoContainer.style.display === '') {
                formExternoContainer.style.display = 'block';
                // Enfocar el primer campo
                document.getElementById('externo_descripcion').focus();
            } else {
                formExternoContainer.style.display = 'none';
                // Limpiar campos al cancelar
                document.getElementById('externo_descripcion').value = '';
                document.getElementById('externo_precio').value = '';
            }
        });
    }

    if (btnCancelarExterno) {
        btnCancelarExterno.addEventListener('click', function() {
            formExternoContainer.style.display = 'none';
            document.getElementById('externo_descripcion').value = '';
            document.getElementById('externo_precio').value = '';
        });
    }

    if (btnGuardarExterno) {
        btnGuardarExterno.addEventListener('click', function() {
            const descripcion = document.getElementById('externo_descripcion')?.value.trim();
            const precio = document.getElementById('externo_precio')?.value;
            
            if (!descripcion) {
                if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
                return;
            }
            
            if (!precio || parseFloat(precio) <= 0) {
                if (window.mostrarToast) window.mostrarToast('El precio es requerido y debe ser mayor a 0', 'warning');
                return;
            }
            
            // Deshabilitar botón mientras se guarda
            btnGuardarExterno.disabled = true;
            btnGuardarExterno.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
            
            fetch('{{ route("ventas.cotizaciones.guardar-producto-externo") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    descripcion: descripcion,
                    precio: parseFloat(precio)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ocultar formulario
                    formExternoContainer.style.display = 'none';
                    
                    // Limpiar campos
                    document.getElementById('externo_descripcion').value = '';
                    document.getElementById('externo_precio').value = '';
                    
                    // Crear el objeto del nuevo artículo con precio como número
                    const articuloData = {
                        id: data.data.id,
                        id_sucursal: null,
                        nombre_sucursal: 'Pedido a Proveedor',
                        codbar: data.data.ean,
                        nombre: data.data.descripcion,
                        precio: parseFloat(data.data.precio),  // ← Convertir a número
                        inventario: 999,
                        num_familia: 'EXT',
                        es_externo: 1,
                        es_medicamento: false,
                        sustancias_activas: ''
                    };
                    
                    // Agregar a resultados de búsqueda
                    if (!window.resultadosBusqueda) {
                        window.resultadosBusqueda = [];
                    }
                    window.resultadosBusqueda.unshift(articuloData);
                    
                    // Agregar a la cotización
                    agregarArticuloPorIndiceNuevo(0);
                    
                    if (window.mostrarToast) window.mostrarToast('Producto externo guardado y agregado', 'success');
                } else {
                    if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
            })
            .finally(() => {
                btnGuardarExterno.disabled = false;
                btnGuardarExterno.innerHTML = 'Guardar producto';
            });
        });
    }
    
    // Cerrar resultados al hacer clic fuera
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
    
    // Modal de nueva cotización
    const modalElement = document.getElementById('modalNuevaCotizacion');
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function() {
            // Si es nueva versión O es independiente (cotizacionOrigenId existe para independiente también)
            // Usamos un flag para diferenciar
            if (esNuevaVersion) {
                // Intentar cargar datos desde el servidor (nueva versión)
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
                return;
            }
            
            // Si es independiente (nueva cotización desde una existente sin versionado)
            if (window.esNuevaIndependiente) {
                // No limpiar nada, los datos ya están cargados por precargarDatosCotizacionIndependiente
                // Solo resetear el flag
                window.esNuevaIndependiente = false;
                return;
            }
            
            // Nueva cotización normal
            limpiarFormularioCotizacion();
            
            // Función para establecer la sucursal por defecto
            function establecerSucursalPorDefecto() {
                const sucursalSelect = document.getElementById('sucursal_asignada_id');
                
                if (sucursalSelect && window.sucursalUsuarioDefecto !== undefined && window.sucursalUsuarioDefecto !== null) {
                    const sucursalId = parseInt(window.sucursalUsuarioDefecto);
                    
                    // Verificar si el select tiene opciones cargadas
                    if (sucursalSelect.options.length > 1) {
                        // Verificar que el valor exista en el select
                        let optionExists = false;
                        for (let i = 0; i < sucursalSelect.options.length; i++) {
                            if (parseInt(sucursalSelect.options[i].value) === sucursalId) {
                                optionExists = true;
                                break;
                            }
                        }
                        
                        if (optionExists && sucursalId > 0) {
                            sucursalSelect.value = sucursalId;
                        }
                    } else {
                        // Si el select aún no tiene opciones, esperar un poco más
                        setTimeout(establecerSucursalPorDefecto, 200);
                    }
                }
            }
            
            // Verificar si los catálogos ya están cargados
            if (catalogos.sucursales && catalogos.sucursales.length > 0) {
                establecerSucursalPorDefecto();
            } else {
                setTimeout(establecerSucursalPorDefecto, 500);
            }
        });
        
        modalElement.addEventListener('hidden.bs.modal', function() {
            esNuevaVersion = false;
            cotizacionOrigenId = null;
            window.esNuevaIndependiente = false;
            window.datosCotizacionOrigen = null;
        });
    }
    
    // Evento cambio de convenio general
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