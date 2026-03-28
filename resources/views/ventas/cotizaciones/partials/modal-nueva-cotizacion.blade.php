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
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="buscarClienteCotizacion" 
                                           placeholder="Buscar por nombre o email...">
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para seleccionarlo.</small>
                                
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
                                    <label class="form-label">Convenio (aplica a todos los artículos)</label>
                                    <select class="form-select" id="convenio_general" name="convenio_general">
                                        <option value="">Sin convenio</option>
                                    </select>
                                    <small class="text-muted">Selecciona un convenio para aplicar descuento a todos los artículos</small>
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
                            <!-- Buscador de artículos -->
                            <div class="mb-3">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="buscarArticuloModal" 
                                           placeholder="Buscar artículo por código o descripción...">
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para agregarlo.</small>
                                
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
            
            if (faseSelect && catalogos.fases) {
                faseSelect.innerHTML = '<option value="">Seleccionar fase...</option>' + 
                    catalogos.fases.map(f => `<option value="${f.id_fase}">${f.fase}</option>`).join('');
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
        }
    })
    .catch(error => console.error('Error al cargar catálogos:', error));
}

// ============================================
// FUNCIONES PARA CLIENTES
// ============================================
let timeoutBusquedaCliente;

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
        
        if (data.success && data.data.length > 0) {
            listaResultados.innerHTML = data.data.map(cliente => `
                <div class="list-group-item list-group-item-action" 
                     onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre.replace(/'/g, "\\'")}', '${cliente.email}')"
                     style="cursor: pointer;">
                    <div>
                        <strong>${cliente.nombre}</strong>
                        <br><small class="text-muted">${cliente.email}</small>
                    </div>
                </div>
            `).join('');
            resultadosDiv.style.display = 'block';
        } else {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
            resultadosDiv.style.display = 'block';
        }
    })
    .catch(error => console.error('Error buscando clientes:', error));
}

window.seleccionarCliente = function(id, nombre, email) {
    document.getElementById('cliente_id').value = id;
    document.getElementById('clienteInfo').innerHTML = `<strong>${nombre}</strong><br><small>${email}</small>`;
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
// FUNCIONES PARA ARTÍCULOS
// ============================================
let timeoutBusquedaArticulo;

function buscarArticulos(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosArticulos').style.display = 'none';
        return;
    }
    
    const sucursalAsignadaId = document.getElementById('sucursal_asignada_id')?.value || '';
    
    fetch(`{{ route("ventas.cotizaciones.productos.buscar") }}?q=${encodeURIComponent(termino)}&sucursal_asignada_id=${sucursalAsignadaId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('resultadosArticulos');
        const listaResultados = document.getElementById('listaArticulos');
        
        if (data.success && data.data.length > 0) {
            listaResultados.innerHTML = data.data.map(articulo => {
                const yaExiste = articulosSeleccionados.some(a => a.id_producto === articulo.id);
                
                // Determinar si es la sucursal asignada
                const esSucursalAsignada = articulo.id_sucursal == sucursalAsignadaId;
                const stockClass = articulo.inventario > 0 ? 'text-success' : 'text-danger';
                const badgeClass = esSucursalAsignada ? 'bg-primary' : 'bg-secondary';
                
                // Escapar caracteres especiales
                const nombreEscapado = articulo.nombre.replace(/'/g, "\\'");
                const codbarEscapado = (articulo.codbar || '').replace(/'/g, "\\'");
                const numFamiliaEscapado = (articulo.num_familia || '').replace(/'/g, "\\'");
                
                // Crear un array de sucursales con un solo elemento para este artículo
                const sucursalesArray = [{
                    id_sucursal: articulo.id_sucursal,
                    nombre_sucursal: articulo.nombre_sucursal,
                    inventario: articulo.inventario
                }];
                const sucursalesJson = JSON.stringify(sucursalesArray).replace(/'/g, "\\'");
                
                return `
                    <div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                         onclick="${!yaExiste ? `agregarArticulo(${articulo.id}, '${nombreEscapado}', ${articulo.precio}, '${codbarEscapado}', '${numFamiliaEscapado}', '${sucursalesJson}')` : ''}" 
                         style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${articulo.nombre}</strong>
                                <br><small class="text-muted">Código: ${articulo.codbar || 'N/A'} | Precio: $${articulo.precio.toFixed(2)}</small>
                                <br><small class="text-muted">Familia: ${articulo.num_familia || 'N/A'}</small>
                                <br><span class="badge ${badgeClass} me-1">${articulo.nombre_sucursal}</span>
                                <span class="badge ${stockClass}">Stock: ${articulo.inventario}</span>
                            </div>
                            ${yaExiste ? '<span class="badge bg-secondary">Ya agregado</span>' : '<span class="badge bg-success">Agregar</span>'}
                        </div>
                    </div>
                `;
            }).join('');
            resultadosDiv.style.display = 'block';
        } else {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron artículos con stock disponible</div>';
            resultadosDiv.style.display = 'block';
        }
    })
    .catch(error => console.error('Error buscando artículos:', error));
}
    
window.agregarArticulo = function(id, nombre, precio, codbar, numFamilia, sucursalesInfoStr) {
    if (articulosSeleccionados.some(a => a.id_producto === id)) return;
    
    let descuento = 0;
    let idConvenio = null;
    let sucursalesSurtido = [];
    
    // Parsear el string JSON de sucursales
    let sucursalesInfo = [];
    try {
        sucursalesInfo = JSON.parse(sucursalesInfoStr);
    } catch(e) {
        console.error('Error parsing sucursalesInfo:', e);
        sucursalesInfo = [];
    }
    
    // Obtener el descuento del convenio general si está seleccionado
    const convenioSelect = document.getElementById('convenio_general');
    if (convenioSelect && convenioSelect.value) {
        const convenio = catalogos.convenios?.find(c => c.id == convenioSelect.value);
        if (convenio && convenio.familias) {
            const familiaConDescuento = convenio.familias.find(f => f.num_familia === numFamilia);
            if (familiaConDescuento) {
                descuento = familiaConDescuento.descuento;
                idConvenio = convenio.id;
            }
        }
    }
    
    // Determinar sucursal de surtido según stock
    const sucursalAsignadaId = document.getElementById('sucursal_asignada_id')?.value;
    let sucursalSeleccionada = null;
    
    if (sucursalesInfo.length > 0) {
        // Buscar primero en la sucursal asignada
        sucursalSeleccionada = sucursalesInfo.find(s => s.id_sucursal == sucursalAsignadaId && s.inventario > 0);
        // Si no hay en la asignada, buscar en cualquier sucursal con stock
        if (!sucursalSeleccionada) {
            sucursalSeleccionada = sucursalesInfo.find(s => s.inventario > 0);
        }
    }
    
    if (!sucursalSeleccionada) {
        if (window.mostrarToast) window.mostrarToast('No hay stock suficiente en ninguna sucursal', 'warning');
        return;
    }
    
    // Guardar la sucursal de surtido
    sucursalesSurtido.push({
        id_sucursal: sucursalSeleccionada.id_sucursal,
        cantidad: 1,
        nombre_sucursal: sucursalSeleccionada.nombre_sucursal
    });
    
    articulosSeleccionados.push({
        id_producto: id,
        nombre: nombre,
        codbar: codbar,
        precio: precio,
        cantidad: 1,
        descuento: descuento,
        id_convenio: idConvenio,
        sucursales_surtido: sucursalesSurtido,
        num_familia: numFamilia
    });
    
    renderizarTablaArticulos();
    document.getElementById('buscarArticuloModal').value = '';
    document.getElementById('resultadosArticulos').style.display = 'none';
};

window.eliminarArticulo = function(index) {
    articulosSeleccionados.splice(index, 1);
    renderizarTablaArticulos();
};

window.actualizarCantidad = function(index, cantidad) {
    articulosSeleccionados[index].cantidad = Math.max(1, parseInt(cantidad) || 1);
    renderizarTablaArticulos();
};

window.cambiarConvenioIndividual = function(index, convenioId) {
    articulosSeleccionados[index].id_convenio = convenioId || null;
    
    if (convenioId && catalogos.convenios) {
        const convenio = catalogos.convenios.find(c => c.id == convenioId);
        if (convenio && convenio.familias) {
            const numFamilia = articulosSeleccionados[index].num_familia;
            const familiaConDescuento = convenio.familias.find(f => f.num_familia === numFamilia);
            if (familiaConDescuento) {
                articulosSeleccionados[index].descuento = familiaConDescuento.descuento;
            } else {
                articulosSeleccionados[index].descuento = 0;
            }
        } else {
            articulosSeleccionados[index].descuento = 0;
        }
    } else {
        articulosSeleccionados[index].descuento = 0;
    }
    
    renderizarTablaArticulos();
};

// ============================================
// FUNCIÓN PARA RENDERIZAR TABLA DE ARTÍCULOS
// ============================================
function renderizarTablaArticulos() {
    const tbody = document.getElementById('articulosBody');
    if (!tbody) return;
    
    let totalGeneral = 0;
    
    if (articulosSeleccionados.length === 0) {
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
    articulosSeleccionados.forEach((articulo, index) => {
        const precioConDescuento = articulo.precio * (1 - articulo.descuento / 100);
        const importe = articulo.cantidad * precioConDescuento;
        totalGeneral += importe;
        
        html += `
            <tr id="articulo-row-${index}">
                <td class="text-center">${index + 1}<\/td>
                <td><small>${articulo.codbar || '-'}<\/small><\/td>
                <td>
                    <strong>${articulo.nombre}</strong>
                    ${articulo.descuento > 0 ? `<br><small class="text-muted"><i class="bi bi-tag"></i> ${articulo.descuento}% descuento aplicado</small>` : ''}
                <\/td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center" 
                           value="${articulo.cantidad}" min="1" 
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
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalCotizacion').textContent = `$${totalGeneral.toFixed(2)}`;
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
        id_sucursal_surtido: a.sucursales_surtido && a.sucursales_surtido.length > 0 ? a.sucursales_surtido[0].id_sucursal : null
    }));
    
    const formData = {
        id_cliente: parseInt(clienteId),
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('clasificacion_id').value || null,
        id_sucursal_asignada: document.getElementById('sucursal_asignada_id').value || null,
        comentarios: document.getElementById('comentarios').value,
        articulos: articulos,
        _token: '{{ csrf_token() }}'
    };
    
    fetch('{{ route("ventas.cotizaciones.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaCotizacion'));
            modal.hide();
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
            clearTimeout(timeoutBusquedaArticulo);
            timeoutBusquedaArticulo = setTimeout(() => buscarArticulos(this.value), 300);
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
    
    const modal = document.getElementById('modalNuevaCotizacion');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            limpiarCliente();
            articulosSeleccionados = [];
            renderizarTablaArticulos();
            document.getElementById('buscarArticuloModal').value = '';
            document.getElementById('resultadosArticulos').style.display = 'none';
            document.getElementById('fase_id').value = '';
            document.getElementById('clasificacion_id').value = '';
            document.getElementById('sucursal_asignada_id').value = '';
            document.getElementById('comentarios').value = '';
            document.getElementById('convenio_general').value = '';
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
});
</script>
@endpush