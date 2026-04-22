{{-- resources/views/ventas/pedidos/partials/modal-editar-pedido.blade.php --}}
<div class="modal fade" id="modalEditarPedido" tabindex="-1" aria-labelledby="modalEditarPedidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarPedidoLabel">
                    <i class="bi bi-pencil-square"></i> Editar Pedido - <span id="edit_folio_pedido">...</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPedido">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_pedido_id" name="pedido_id">

                    <!-- Información del Cliente (Solo Lectura) -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-person"></i> Información del Cliente</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-muted small">Cliente</label>
                                    <p class="fw-bold" id="edit_cliente_nombre">-</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">Teléfono</label>
                                    <p id="edit_cliente_telefono">-</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">Email</label>
                                    <p id="edit_cliente_email">-</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="text-muted small">Fecha Pedido</label>
                                    <p id="edit_fecha_pedido">-</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small">Última modificación</label>
                                    <p id="edit_fecha_modificacion">-</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small">Modificado por</label>
                                    <p id="edit_modificado_por">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Convenio General y Comentarios -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-tags"></i> Configuración General</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Convenio General</label>
                                    <select class="form-select" id="edit_convenio_general">
                                        <option value="">Sin convenio</option>
                                    </select>
                                    <small class="text-muted">El descuento se aplicará automáticamente según la familia del producto</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Comentarios / Observaciones</label>
                                    <textarea class="form-control" id="edit_comentarios" rows="2" placeholder="Instrucciones especiales para el repartidor..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos del Pedido -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-box-seam"></i> Productos del Pedido</strong>
                        </div>
                        <div class="card-body">
                            <!-- Buscador de productos para agregar nuevos -->
                            <div class="mb-3">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="edit_buscarProducto" 
                                        placeholder="Buscar y agregar nuevo producto (código, nombre o sustancia)"
                                        autocomplete="off">
                                </div>
                                <div id="edit_resultadosProductos" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Productos encontrados (haz clic para agregar)</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="edit_listaProductos"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de productos editables -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 25%">Producto</th>
                                            <th style="width: 10%">Cantidad</th>
                                            <th style="width: 12%">Precio</th>
                                            <th style="width: 10%">Dto.</th>
                                            <th style="width: 12%">Importe</th>
                                            <th style="width: 15%">Sucursal</th>
                                            <th style="width: 6%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_productos_body">
                                        <tr id="edit-sin-productos">
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="bi bi-box-seam"></i> No hay productos en este pedido
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="edit_total_pedido">$0.00</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Asignación de Repartidor -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-person-badge"></i> Asignación de Repartidor</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Repartidor</label>
                                    <select class="form-select" id="edit_repartidor_id">
                                        <option value="">Seleccionar repartidor...</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sucursal del Repartidor</label>
                                    <input type="text" class="form-control" id="edit_repartidor_sucursal" readonly disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarEdicionPedido()">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales para el modal de edición
let editArticulosSeleccionados = [];
let editCatalogos = { convenios: [], sucursales: [] };
let editTimeoutBusqueda;
let editResultadosBusqueda = [];

// ============================================
// CARGAR DATOS EN EL MODAL DE EDICIÓN
// ============================================
window.cargarDatosEditarPedido = function(data) {
    console.log('Cargando datos para editar pedido:', data);
    
    // Limpiar variables y UI
    editArticulosSeleccionados = [];
    
    // Datos básicos del pedido
    document.getElementById('edit_pedido_id').value = data.id_pedido;
    document.getElementById('edit_folio_pedido').textContent = data.folio_pedido;
    document.getElementById('edit_fecha_pedido').textContent = data.fecha_pedido ? new Date(data.fecha_pedido).toLocaleString() : '-';
    document.getElementById('edit_comentarios').value = data.comentarios || '';
    
    // Fechas de modificación
    if (data.updated_at) {
        document.getElementById('edit_fecha_modificacion').textContent = new Date(data.updated_at).toLocaleString();
    } else if (data.created_at) {
        document.getElementById('edit_fecha_modificacion').textContent = new Date(data.created_at).toLocaleString();
    }
    
    // Quién modificó
    if (data.creador) {
        document.getElementById('edit_modificado_por').textContent = `${data.creador.Nombre || ''} ${data.creador.ApPaterno || ''} ${data.creador.ApMaterno || ''}`.trim() || 'Sin modificaciones';
    } else {
        document.getElementById('edit_modificado_por').textContent = 'CRM Sistema';
    }
    
    // Datos del cliente
    if (data.cotizacion && data.cotizacion.cliente) {
        const cliente = data.cotizacion.cliente;
        const nombreCompleto = `${cliente.Nombre || ''} ${cliente.apPaterno || ''} ${cliente.apMaterno || ''}`.trim();
        document.getElementById('edit_cliente_nombre').textContent = nombreCompleto || '-';
        document.getElementById('edit_cliente_telefono').innerHTML = cliente.telefono1 ? `<i class="bi bi-telephone"></i> ${cliente.telefono1}` : '-';
        document.getElementById('edit_cliente_email').innerHTML = cliente.email1 ? `<i class="bi bi-envelope"></i> ${cliente.email1}` : '-';
    }
    
    // Cargar convenios y sucursales
    cargarCatalogosEdit();
    
    // Cargar productos (priorizar detalles de orden_pedido_detalle)
    if (data.detalles && data.detalles.length > 0) {
        // Usar los detalles guardados en orden_pedido_detalle
        editArticulosSeleccionados = data.detalles.map(detalle => ({
            id_detalle_pedido: detalle.id_detalle_pedido,
            id_producto: detalle.id_producto,
            nombre: detalle.producto ? detalle.producto.descripcion : (detalle.cotizacionDetalle?.descripcion || 'Producto'),
            codbar: detalle.producto ? detalle.producto.ean : (detalle.cotizacionDetalle?.codbar || ''),
            cantidad: detalle.cantidad,
            precio_unitario: parseFloat(detalle.precio_unitario),
            descuento: parseFloat(detalle.descuento || 0),
            importe: parseFloat(detalle.importe),
            id_convenio: detalle.id_convenio,
            id_sucursal_surtido: detalle.id_sucursal_surtido,
            num_familia: detalle.producto?.num_familia || '',
            es_agregado: detalle.es_agregado || false,
            es_externo: detalle.es_externo || 0,
            id_cotizacion_detalle: detalle.id_cotizacion_detalle,
            inventario_disponible: detalle.stock_actual || 999,
            nombre_sucursal: detalle.sucursalSurtido?.nombre || 'No asignada'
        }));
    } else if (data.cotizacion && data.cotizacion.detalles && data.cotizacion.detalles.length > 0) {
        // Fallback: usar detalles de cotización
        editArticulosSeleccionados = data.cotizacion.detalles.map(detalle => ({
            id_detalle_pedido: null,
            id_producto: detalle.id_producto,
            nombre: detalle.descripcion,
            codbar: detalle.codbar || '',
            cantidad: detalle.cantidad,
            precio_unitario: parseFloat(detalle.precio_unitario),
            descuento: parseFloat(detalle.descuento || 0),
            importe: parseFloat(detalle.importe),
            id_convenio: detalle.id_convenio,
            id_sucursal_surtido: detalle.id_sucursal_surtido,
            num_familia: detalle.producto?.num_familia || '',
            es_agregado: false,
            es_externo: detalle.es_externo || 0,
            id_cotizacion_detalle: detalle.id_cotizacion_detalle,
            inventario_disponible: 999,
            nombre_sucursal: detalle.sucursal_surtido?.nombre || 'No asignada'
        }));
    }
    
    // Cargar repartidor
    if (data.repartidor) {
        document.getElementById('edit_repartidor_id').value = data.repartidor.id_personal_empresa;
        document.getElementById('edit_repartidor_sucursal').value = data.repartidor.sucursal_asignada || '';
    } else {
        document.getElementById('edit_repartidor_id').value = '';
        document.getElementById('edit_repartidor_sucursal').value = '';
    }
    
    // Cargar repartidores disponibles
    cargarRepartidoresEdit();
    
    // Renderizar tabla
    renderizarTablaEditarProductos();
};

// ============================================
// CARGAR CATÁLOGOS (Convenios y Sucursales)
// ============================================
function cargarCatalogosEdit() {
    fetch('{{ route("ventas.cotizaciones.catalogos") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            editCatalogos.convenios = data.data.convenios || [];
            editCatalogos.sucursales = data.data.sucursales || [];
            
            // Cargar select de convenios
            const convenioSelect = document.getElementById('edit_convenio_general');
            if (convenioSelect && editCatalogos.convenios.length) {
                convenioSelect.innerHTML = '<option value="">Sin convenio</option>' + 
                    editCatalogos.convenios.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            }
            
            // Disparar evento cuando los catálogos estén listos
            document.dispatchEvent(new CustomEvent('editCatalogosCargados'));
        }
    })
    .catch(error => console.error('Error cargando catálogos:', error));
}

// ============================================
// CARGAR REPARTIDORES DISPONIBLES
// ============================================
function cargarRepartidoresEdit() {
    fetch('/ventas/pedidos/repartidores-disponibles', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const select = document.getElementById('edit_repartidor_id');
            select.innerHTML = '<option value="">Seleccionar repartidor...</option>';
            data.data.forEach(rep => {
                select.innerHTML += `<option value="${rep.id_personal_empresa}" data-sucursal="${rep.id_sucursal || ''}">${rep.nombre_completo}</option>`;
            });
            
            // Evento para mostrar sucursal al seleccionar repartidor
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const sucursal = selectedOption.getAttribute('data-sucursal') || '';
                document.getElementById('edit_repartidor_sucursal').value = sucursal;
            });
        }
    })
    .catch(error => console.error('Error cargando repartidores:', error));
}

// ============================================
// BUSCADOR DE PRODUCTOS CON RESALTADO DE SUSTANCIAS
// ============================================
function buscarProductosEditar(termino) {
    if (!termino || termino.length < 3) {
        const resultadosDiv = document.getElementById('edit_resultadosProductos');
        const listaResultados = document.getElementById('edit_listaProductos');
        
        if (resultadosDiv && listaResultados) {
            if (termino && termino.length > 0 && termino.length < 3) {
                listaResultados.innerHTML = `<div class="list-group-item text-muted">Escribe al menos 3 caracteres para buscar</div>`;
                resultadosDiv.style.display = 'block';
            } else {
                resultadosDiv.style.display = 'none';
            }
        }
        return;
    }
    
    clearTimeout(editTimeoutBusqueda);
    editTimeoutBusqueda = setTimeout(() => {
        const sucursalAsignadaId = document.getElementById('sucursal_asignada_id')?.value || '';
        const url = `/ventas/cotizaciones/productos/buscar?sucursal_asignada_id=${sucursalAsignadaId}&q=${encodeURIComponent(termino)}`;
        
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(response => response.json())
            .then(data => {
                const resultadosDiv = document.getElementById('edit_resultadosProductos');
                const listaResultados = document.getElementById('edit_listaProductos');
                
                if (data.success && data.data && data.data.length > 0) {
                    editResultadosBusqueda = data.data;
                    
                    listaResultados.innerHTML = data.data.map((producto, idx) => {
                        const esExterno = producto.es_externo === true;
                        const esSucursalAsignada = producto.id_sucursal == sucursalAsignadaId;
                        const stockClass = producto.inventario > 0 ? 'text-success' : 'text-danger';
                        const badgeClass = esSucursalAsignada ? 'bg-primary' : (esExterno ? 'bg-info' : 'bg-secondary');
                        
                        const sustanciaBadge = producto.sustancias_activas && 
                                              producto.sustancias_activas !== 'No es medicamento' && 
                                              producto.sustancias_activas !== 'No coincide con la búsqueda' ?
                            `<br><small class="text-info"><i class="bi bi-capsule"></i> Sustancia: <strong>${escapeHtml(producto.sustancias_activas)}</strong></small>` : '';
                        
                        const externoBadge = esExterno ? 
                            '<span class="badge bg-info ms-1">Pedido a Proveedor</span>' : '';
                        
                        return `
                            <div class="list-group-item list-group-item-action" 
                                 onclick="agregarArticuloEditPorIndice(${idx})"
                                 style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>${escapeHtml(producto.nombre)}</strong>
                                        ${externoBadge}
                                        ${sustanciaBadge}
                                        <br><small class="text-muted"><strong>Código: </strong>${escapeHtml(producto.codbar || 'N/A')} | Precio: $${producto.precio.toFixed(2)}</small>
                                        <br><small class="text-muted"><strong>Familia: </strong>${escapeHtml(producto.num_familia || 'N/A')}</small>
                                        <br><span class="badge ${badgeClass} me-1">${escapeHtml(producto.nombre_sucursal)}</span>
                                        <span class="badge ${stockClass}">Stock disponible: ${producto.inventario}</span>
                                    </div>
                                    <span class="badge bg-success">Agregar</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                    resultadosDiv.style.display = 'block';
                } else {
                    let mensaje = `No se encontraron productos con "${escapeHtml(termino)}"`;
                    listaResultados.innerHTML = `<div class="list-group-item text-muted">${mensaje}</div>`;
                    resultadosDiv.style.display = 'block';
                }
            })
            .catch(error => console.error('Error buscando productos:', error));
    }, 300);
}

// ============================================
// AGREGAR PRODUCTO POR ÍNDICE
// ============================================
window.agregarArticuloEditPorIndice = function(idx) {
    if (!editResultadosBusqueda || !editResultadosBusqueda[idx]) return;
    
    const articuloData = editResultadosBusqueda[idx];
    const esExterno = articuloData.es_externo == 1 || articuloData.es_externo === true || articuloData.es_externo === "1";

    const nuevoArticulo = {
        id_producto: articuloData.id,
        nombre: articuloData.nombre,
        codbar: articuloData.codbar || '',
        precio_unitario: articuloData.precio,
        cantidad: 1,
        descuento: 0,
        id_convenio: null,
        id_sucursal_surtido: articuloData.id_sucursal || null,
        num_familia: articuloData.num_familia || (esExterno ? 'EXT' : ''),
        inventario_disponible: articuloData.inventario || 999,
        nombre_sucursal: articuloData.nombre_sucursal || (esExterno ? 'Sobre Pedido' : 'No asignada'),
        es_externo: esExterno ? 1 : 0,
        es_agregado: true,
        id_detalle_pedido: null,
        id_cotizacion_detalle: null
    };
    
    // Aplicar descuento del convenio general si existe
    const convenioSelect = document.getElementById('edit_convenio_general');
    if (convenioSelect && convenioSelect.value && editCatalogos.convenios) {
        const convenio = editCatalogos.convenios.find(c => c.id == convenioSelect.value);
        if (convenio && convenio.familias) {
            const familiaConDescuento = convenio.familias.find(f => f.num_familia === nuevoArticulo.num_familia);
            if (familiaConDescuento) {
                nuevoArticulo.descuento = familiaConDescuento.descuento;
                nuevoArticulo.id_convenio = convenio.id;
            }
        }
    }
    
    agregarOSumarArticuloEdit(nuevoArticulo, editArticulosSeleccionados);
    
    // Limpiar buscador
    const buscador = document.getElementById('edit_buscarProducto');
    if (buscador) buscador.value = '';
    const resultadosDiv = document.getElementById('edit_resultadosProductos');
    if (resultadosDiv) resultadosDiv.style.display = 'none';
};

// ============================================
// AGREGAR O SUMAR PRODUCTO
// ============================================
function agregarOSumarArticuloEdit(articulo, listaArticulos) {
    // Buscar si ya existe (mismo producto, misma sucursal, mismo tipo)
    const existe = listaArticulos.find(a => 
        Number(a.id_producto) === Number(articulo.id_producto) && 
        Number(a.id_sucursal_surtido) === Number(articulo.id_sucursal_surtido) &&
        a.es_externo === articulo.es_externo
    );
    
    if (existe) {
        const nuevaCantidad = existe.cantidad + 1;
        const maxDisponible = existe.inventario_disponible;
        
        if (nuevaCantidad <= maxDisponible) {
            existe.cantidad = nuevaCantidad;
            if (window.mostrarToast) {
                window.mostrarToast(`Sumado 1 unidad a "${articulo.nombre}". Total: ${nuevaCantidad} unidades.`, 'success');
            }
        } else {
            if (window.mostrarToast) {
                window.mostrarToast(`No se puede sumar más. Stock máximo: ${maxDisponible} unidades.`, 'warning');
            }
        }
    } else {
        listaArticulos.push(articulo);
        if (window.mostrarToast) {
            window.mostrarToast(`Agregado "${articulo.nombre}" al pedido.`, 'success');
        }
    }
    
    renderizarTablaEditarProductos();
}

// ============================================
// RENDERIZAR TABLA DE PRODUCTOS (EDITABLE)
// ============================================
function renderizarTablaEditarProductos() {
    const tbody = document.getElementById('edit_productos_body');
    let total = 0;
    
    if (!editArticulosSeleccionados.length) {
        tbody.innerHTML = `<tr id="edit-sin-productos"><td colspan="8" class="text-center py-4 text-muted">
            <i class="bi bi-box-seam"></i> No hay productos en este pedido
        </td></tr>`;
        document.getElementById('edit_total_pedido').textContent = '$0.00';
        return;
    }
    
    let html = '';
    editArticulosSeleccionados.forEach((item, index) => {
        const importe = item.cantidad * item.precio_unitario * (1 - (item.descuento || 0) / 100);
        total += importe;
        const esExterno = item.es_externo == 1;
        
        html += `
            <tr data-index="${index}">
                <td class="text-center">${index + 1}</td>
                <td>
                    <strong>${escapeHtml(item.nombre)}</strong>
                    <br><small class="text-muted">Código: ${escapeHtml(item.codbar || '-')}</small>
                    ${esExterno ? '<br><span class="badge bg-info">Sobre pedido</span>' : ''}
                </td>
                <td class="text-center" style="width: 100px;">
                    <input type="number" class="form-control form-control-sm text-center" 
                           value="${item.cantidad}" min="1" max="${item.inventario_disponible || 999}"
                           onchange="actualizarCantidadEditar(${index}, this.value)">
                </td>
                <td class="text-end">$${item.precio_unitario.toFixed(2)}</td>
                <td class="text-end">${item.descuento > 0 ? item.descuento + '%' : '-'}</td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                <td>
                    ${!esExterno ? `
                        <select class="form-select form-select-sm" onchange="actualizarSucursalEditar(${index}, this.value)">
                            <option value="">Seleccionar sucursal...</option>
                            ${editCatalogos.sucursales.map(s => `
                                <option value="${s.id_sucursal}" ${item.id_sucursal_surtido == s.id_sucursal ? 'selected' : ''}>
                                    ${escapeHtml(s.nombre)}
                                </option>
                            `).join('')}
                        </select>
                    ` : '<span class="text-muted">No aplica (sobre pedido)</span>'}
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProductoEditar(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('edit_total_pedido').textContent = `$${total.toFixed(2)}`;
}

// ============================================
// FUNCIONES DE MANIPULACIÓN DE PRODUCTOS
// ============================================
window.actualizarCantidadEditar = function(index, nuevaCantidad) {
    const cantidad = Math.max(1, parseInt(nuevaCantidad) || 1);
    const articulo = editArticulosSeleccionados[index];
    const maxDisponible = articulo.inventario_disponible || 999;
    
    if (cantidad > maxDisponible) {
        if (window.mostrarToast) {
            window.mostrarToast(`Solo hay ${maxDisponible} unidades disponibles.`, 'warning');
        }
        articulo.cantidad = maxDisponible;
    } else {
        articulo.cantidad = cantidad;
    }
    
    renderizarTablaEditarProductos();
};

window.actualizarSucursalEditar = function(index, sucursalId) {
    editArticulosSeleccionados[index].id_sucursal_surtido = sucursalId || null;
    renderizarTablaEditarProductos();
};

window.eliminarProductoEditar = function(index) {
    if (confirm('¿Eliminar este producto del pedido?')) {
        editArticulosSeleccionados.splice(index, 1);
        renderizarTablaEditarProductos();
    }
};

// ============================================
// GUARDAR EDICIÓN DEL PEDIDO
// ============================================
window.guardarEdicionPedido = function() {
    const pedidoId = document.getElementById('edit_pedido_id').value;
    const comentarios = document.getElementById('edit_comentarios').value;
    const repartidorId = document.getElementById('edit_repartidor_id').value;
    const convenioGeneral = document.getElementById('edit_convenio_general').value;
    
    if (editArticulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('El pedido debe tener al menos un producto', 'warning');
        return;
    }
    
    // Preparar datos para enviar
    const productos = editArticulosSeleccionados.map(p => ({
        id_detalle_pedido: p.id_detalle_pedido,
        id_producto: p.id_producto,
        cantidad: p.cantidad,
        precio_unitario: p.precio_unitario,
        descuento: p.descuento,
        id_convenio: p.id_convenio,
        id_sucursal_surtido: p.id_sucursal_surtido,
        es_agregado: p.es_agregado ? 1 : 0,
        id_cotizacion_detalle: p.id_cotizacion_detalle
    }));
    
    const formData = {
        comentarios: comentarios,
        id_repartidor: repartidorId || null,
        id_convenio_general: convenioGeneral || null,
        productos: productos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    fetch(`/ventas/pedidos/${pedidoId}`, {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPedido'));
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
// EVENTO CAMBIO DE CONVENIO GENERAL
// ============================================
document.addEventListener('editCatalogosCargados', function() {
    const convenioSelect = document.getElementById('edit_convenio_general');
    if (convenioSelect) {
        convenioSelect.addEventListener('change', function() {
            const convenioId = this.value;
            if (convenioId && editCatalogos.convenios) {
                const convenio = editCatalogos.convenios.find(c => c.id == convenioId);
                if (convenio && convenio.familias) {
                    editArticulosSeleccionados.forEach(articulo => {
                        const familiaConDescuento = convenio.familias.find(f => f.num_familia === articulo.num_familia);
                        if (familiaConDescuento) {
                            articulo.descuento = familiaConDescuento.descuento;
                            articulo.id_convenio = convenio.id;
                        } else if (!articulo.es_agregado) {
                            articulo.descuento = 0;
                            articulo.id_convenio = null;
                        }
                    });
                    renderizarTablaEditarProductos();
                }
            } else if (!convenioId) {
                // Sin convenio, resetear descuentos solo a productos no agregados
                editArticulosSeleccionados.forEach(articulo => {
                    if (!articulo.es_agregado) {
                        articulo.descuento = 0;
                        articulo.id_convenio = null;
                    }
                });
                renderizarTablaEditarProductos();
            }
        });
    }
});

// ============================================
// INICIALIZAR BUSCADOR
// ============================================
document.getElementById('edit_buscarProducto')?.addEventListener('input', function() {
    buscarProductosEditar(this.value.trim());
});

// ============================================
// CERRAR RESULTADOS AL HACER CLIC FUERA
// ============================================
document.addEventListener('click', function(event) {
    const resultadosDiv = document.getElementById('edit_resultadosProductos');
    const buscador = document.getElementById('edit_buscarProducto');
    if (resultadosDiv && !resultadosDiv.contains(event.target) && event.target !== buscador) {
        resultadosDiv.style.display = 'none';
    }
});

// ============================================
// FUNCIÓN ESCAPE HTML
// ============================================
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>