@if(isset($pedido) && $pedido)
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
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de entrega sugerida</label>
                                    <input type="date" class="form-control" id="edit_fecha_entrega" name="fecha_entrega">
                                    <small class="text-muted">Fecha sugerida para la entrega</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hora de entrega sugerida</label>
                                    <input type="time" class="form-control" id="edit_hora_entrega" name="hora_entrega">
                                    <small class="text-muted">Hora sugerida para la entrega</small>
                                </div>
                                
                                <!-- Comentario de cotización (solo lectura) -->
                                <div class="col-md-12 mb-3" id="edit_cotizacion_comentarios_container" style="display: none;">
                                    <label class="text-muted small">Comentario de cotización (original)</label>
                                    <p class="text-muted small bg-light p-2 rounded" id="edit_cotizacion_comentarios"></p>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Comentarios / Observaciones del pedido</label>
                                    <textarea class="form-control" id="edit_comentarios" rows="2" placeholder="Instrucciones especiales para el repartidor..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado por sucursal -->
                    <div class="card mb-3" id="edit_sucursales_section" style="display: none;">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-house-check"></i> Estado por sucursal</strong>
                        </div>
                        <div class="card-body">
                            <div id="edit_sucursales_status" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <!-- Productos del Pedido -->
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-box-seam"></i> Productos del Pedido</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnReprogramarProducto">
                                <i class="bi bi-arrow-repeat"></i> Reprogramar producto
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Botón "Reprogramar seleccionados" (oculto inicialmente) -->
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm btn-danger" id="btnReprogramarSeleccionados" style="display: none;">
                                    <i class="bi bi-check2-circle"></i> Reprogramar seleccionados
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm edit-productos-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 15%">Código</th>
                                            <th style="width: 30%">Producto / Descripción</th>
                                            <th style="width: 8%" class="text-center">Cantidad</th>
                                            <th style="width: 10%" class="text-end">Precio</th>
                                            <th style="width: 10%" class="text-end">Importe</th>
                                            <th style="width: 15%">Sucursal surtido</th>
                                            <th style="width: 7%; display: none;" id="seleccionar_header">Seleccionar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_productos_body">
                                        <!-- Los productos se cargarán como solo lectura -->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="edit_total_pedido">$0.00</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Asignación de Repartidor - Solo visible para CRM cuando todas las sucursales están listas -->
                    @php
                        $sucursalesPendientesEdit = isset($pedido) && $pedido->sucursales ? $pedido->sucursales->contains('status', 0) : true;
                        $todasSucursalesListasEdit = isset($pedido) && $pedido->sucursales && $pedido->sucursales->isNotEmpty() && !$sucursalesPendientesEdit;
                        $mostrarAsignacionRepartidor = ($sucursalAsignada == 0 && $pedido->status == 2 && $todasSucursalesListasEdit);
                    @endphp

                    @if($mostrarAsignacionRepartidor)
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
                    @endif
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
@endif

<!-- Modal Reprogramar Producto (soporta uno o varios) -->
<div class="modal fade" id="modalReprogramarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Reprogramar Producto(s)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" id="reprogramar_info">
                    <strong>Productos seleccionados:</strong> <span id="reprogramar_count">0</span>
                    <div id="reprogramar_lista" class="mt-2 small"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo de reprogramación <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="reprogramar_motivo" rows="3" 
                              placeholder="Ej: Producto no llegó a tiempo, el proveedor no lo surtió, etc." required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sucursal para nuevo pedido <span class="text-danger">*</span></label>
                    <select class="form-select" id="reprogramar_sucursal_id" required>
                        <option value="">Cargando sucursales...</option>
                    </select>
                    <small class="text-muted">El nuevo pedido se asignará a esta sucursal</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarReprogramacion" onclick="confirmarReprogramacion()">
                    <i class="bi bi-check-lg"></i> Confirmar reprogramación
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Diseño par a ajustar tabla del modal */
    .edit-productos-table th,
    .edit-productos-table td {
        vertical-align: middle;
    }
</style>

<script>
// Variables globales para el modal de edición
let editArticulosSeleccionados = [];
let editCatalogos = { convenios: [], sucursales: [] };
let editTimeoutBusqueda;
let editResultadosBusqueda = [];
let sucursalesListas = [];

// ============================================
// CARGAR DATOS EN EL MODAL DE EDICIÓN
// ============================================
window.cargarDatosEditarPedido = function(data) {    
    // Limpiar variables y UI
    editArticulosSeleccionados = [];
    
    // Datos básicos del pedido
    document.getElementById('edit_pedido_id').value = data.id_pedido;
    document.getElementById('edit_folio_pedido').textContent = data.folio_pedido;
    document.getElementById('edit_fecha_pedido').textContent = data.fecha_pedido ? new Date(data.fecha_pedido).toLocaleString() : '-';
    document.getElementById('edit_comentarios').value = data.comentarios || '';

    // Fecha de entrega sugerida
    if (data.fecha_entrega_sugerida) {
        let fechaStr = data.fecha_entrega_sugerida;
        // Si la fecha viene en formato ISO con T (ej: "2026-04-28T06:00:00.000000Z"), extraer solo la fecha
        if (fechaStr.includes('T')) {
            fechaStr = fechaStr.split('T')[0];
        }
        // Si viene con espacio
        if (fechaStr.includes(' ')) {
            fechaStr = fechaStr.split(' ')[0];
        }
        document.getElementById('edit_fecha_entrega').value = fechaStr;
    } else {
        document.getElementById('edit_fecha_entrega').value = '';
    }

    // Hora de entrega sugerida (como string, sin conversión de zona horaria)
    if (data.hora_entrega_sugerida) {
        let hora = data.hora_entrega_sugerida;
        
        // Si viene en formato ISO largo (ej: "2026-04-28T16:00:00.000000Z")
        if (hora.includes('T')) {
            const partes = hora.split('T');
            if (partes[1]) {
                hora = partes[1];
            }
        }
        
        // Limpiar milisegundos
        if (hora.includes('.')) {
            hora = hora.split('.')[0];
        }
        
        // Extraer solo HH:MM
        if (hora.includes(':')) {
            const partes = hora.split(':');
            hora = `${partes[0].padStart(2, '0')}:${partes[1].padStart(2, '0')}`;
        }
        
        document.getElementById('edit_hora_entrega').value = hora;
    } else {
        document.getElementById('edit_hora_entrega').value = '';
    }

    // Guardar qué sucursales están listas (usando la variable global)
    sucursalesListas = [];
    if (data.sucursales && data.sucursales.length) {
        sucursalesListas = data.sucursales.filter(s => s.status === true).map(s => parseInt(s.id_sucursal));
    }
    
    // ============================================
    // ESTADO POR SUCURSAL (solo visible para CRM)
    // ============================================
    const sucursalUsuarioEdit = data.sucursal_usuario || 0;
    const sucursalesSectionEdit = document.getElementById('edit_sucursales_section');

    if (sucursalesSectionEdit) {
        if (sucursalUsuarioEdit === 0 && data.sucursales && data.sucursales.length) {
            sucursalesSectionEdit.style.display = 'block';
            const sucursalesContainerEdit = document.getElementById('edit_sucursales_status');
            let sucursalesHtmlEdit = '';
            
            data.sucursales.forEach(suc => {
                const statusText = suc.status ? 'Listo' : 'Pendiente';
                const statusClass = suc.status ? 'success' : 'warning';
                sucursalesHtmlEdit += `<span class="badge bg-${statusClass} p-2">
                                            ${suc.sucursal?.nombre || 'Sucursal'} - ${statusText}
                                        </span>`;
            });
            
            sucursalesContainerEdit.innerHTML = sucursalesHtmlEdit;
        } else {
            sucursalesSectionEdit.style.display = 'none';
        }
    }
    
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
        // Filtrar productos no eliminados
        const detallesActivos = data.detalles.filter(detalle => detalle.se_elimino != 1);
        
        // Usar los detalles guardados en orden_pedido_detalle
        editArticulosSeleccionados = detallesActivos.map(detalle => {
            return {
                id_detalle_pedido: detalle.id_detalle_pedido,
                nombre: detalle.nombre || 'Producto',
                codbar: detalle.codbar || detalle.ean || '',
                ean: detalle.ean || detalle.codbar || '',
                cantidad: detalle.cantidad,
                precio_unitario: parseFloat(detalle.precio_unitario),
                descuento: parseFloat(detalle.descuento || 0),
                importe: parseFloat(detalle.importe),
                id_convenio: detalle.id_convenio,
                id_sucursal_surtido: detalle.id_sucursal_surtido,
                num_familia: detalle.num_familia || (detalle.es_externo ? 'EXT' : ''),
                es_agregado: detalle.es_agregado || false,
                es_externo: detalle.es_externo || 0,
                id_cotizacion_detalle: detalle.id_cotizacion_detalle,
                inventario_disponible: detalle.inventario_disponible || 999,
                nombre_sucursal: detalle.sucursalSurtido?.nombre || 'No asignada',
                se_elimino: detalle.se_elimino || 0
            };
        });
    } else if (data.cotizacion && data.cotizacion.detalles && data.cotizacion.detalles.length > 0) {
        // Fallback: usar detalles de cotización
        editArticulosSeleccionados = data.cotizacion.detalles.map(detalle => {
            return {
                id_detalle_pedido: null,
                nombre: detalle.descripcion,
                codbar: detalle.codbar || '',
                ean: detalle.codbar || '',
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
                nombre_sucursal: detalle.sucursal_surtido?.nombre || 'No asignada',
                se_elimino: 0
            };
        });
    }

    // Comentario de cotización (solo lectura)
    const cotizacionComentariosContainer = document.getElementById('edit_cotizacion_comentarios_container');
    const cotizacionComentariosText = document.getElementById('edit_cotizacion_comentarios');

    if (cotizacionComentariosContainer && cotizacionComentariosText && data.cotizacion?.comentarios) {
        cotizacionComentariosText.textContent = data.cotizacion.comentarios;
        cotizacionComentariosContainer.style.display = 'block';
    } else if (cotizacionComentariosContainer) {
        cotizacionComentariosContainer.style.display = 'none';
    }
    
    // ============================================
    // CARGAR REPARTIDORES PRIMERO, LUEGO ASIGNAR VALOR
    // ============================================
    const repartidorSelect = document.getElementById('edit_repartidor_id');
    const repartidorSucursalInput = document.getElementById('edit_repartidor_sucursal');
    
    if (repartidorSelect && repartidorSucursalInput) {
        // Cargar repartidores primero
        cargarRepartidoresEdit(function() {
            // Después de cargar los repartidores, asignar el valor
            if (data.repartidor) {
                repartidorSelect.value = data.repartidor.id_personal_empresa;
                repartidorSucursalInput.value = data.repartidor.sucursal_asignada || '';
            } else {
                repartidorSelect.value = '';
                repartidorSucursalInput.value = '';
            }
        });
    }
    
    // Mostrar/ocultar sección de asignación de repartidor
    const asignacionRepartidorSection = document.querySelector('#modalEditarPedido .card:has(.bi-person-badge)');
    if (asignacionRepartidorSection) {
        asignacionRepartidorSection.style.display = data.mostrar_asignacion_repartidor ? 'block' : 'none';
    }
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
            // Renderizar tabla despues de tener las sucursales
            renderizarTablaEditarProductos();
            
            // Disparar evento cuando los catálogos estén listos
            document.dispatchEvent(new CustomEvent('editCatalogosCargados'));
        }
    })
    .catch(error => console.error('Error cargando catálogos:', error));
}

// ============================================
// CARGAR REPARTIDORES DISPONIBLES
// ============================================
function cargarRepartidoresEdit(callback = null) {
    fetch('/ventas/pedidos/repartidores-disponibles', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const select = document.getElementById('edit_repartidor_id');
            if (select) {
                select.innerHTML = '<option value="">Seleccionar repartidor...</option>';
                data.data.forEach(rep => {
                    select.innerHTML += `<option value="${rep.id_personal_empresa}" data-sucursal="${rep.id_sucursal || ''}">${rep.nombre_completo}</option>`;
                });
            }
            
            // Ejecutar callback después de cargar
            if (callback && typeof callback === 'function') {
                callback();
            }
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
// RENDERIZAR TABLA DE PRODUCTOS (SOLO LECTURA CON SELECT DE SUCURSAL)
// ============================================
function renderizarTablaEditarProductos() {
    const tbody = document.getElementById('edit_productos_body');
    let total = 0;
    let hayProductosExternos = false;
    
    if (!editArticulosSeleccionados.length) {
        tbody.innerHTML = `<tr id="edit-sin-productos"><td colspan="8" class="text-center py-4 text-muted">
            <i class="bi bi-box-seam"></i> No hay productos en este pedido
        <\/td><\/tr>`;
        document.getElementById('edit_total_pedido').textContent = '$0.00';
        
        // Ocultar botón de reprogramación si no hay productos
        const btnReprogramar = document.getElementById('btnReprogramarProducto');
        if (btnReprogramar) btnReprogramar.style.display = 'none';
        return;
    }
    
    let html = '';
    editArticulosSeleccionados.forEach((item, index) => {
        const precioConDescuento = item.precio_unitario * (1 - (item.descuento || 0) / 100);
        const importe = item.cantidad * precioConDescuento;
        total += importe;
        const esExterno = item.es_externo == 1;
        
        // Detectar si hay productos externos
        if (esExterno) hayProductosExternos = true;
        
        const sucursalActualLista = sucursalesListas.includes(parseInt(item.id_sucursal_surtido));
        const selectDisabled = sucursalActualLista ? 'disabled' : '';
        
        // Generar opciones de sucursales para este producto
        let opcionesSucursales = '<option value="">Seleccionar sucursal...</option>';
        if (editCatalogos.sucursales && editCatalogos.sucursales.length > 0) {
            editCatalogos.sucursales.forEach(s => {
                const sucursalLista = sucursalesListas.includes(parseInt(s.id_sucursal));
                const selectedAttr = (item.id_sucursal_surtido == s.id_sucursal) ? 'selected' : '';
                const disabledAttr = (sucursalLista && item.id_sucursal_surtido != s.id_sucursal) ? 'disabled' : '';
                opcionesSucursales += `<option value="${s.id_sucursal}" ${selectedAttr} ${disabledAttr}>${escapeHtml(s.nombre)}${sucursalLista ? ' (Ya lista)' : ''}</option>`;
            });
        }
        
        const selectHtml = esExterno ? `
            <select class="form-select form-select-sm" disabled>
                <option value="">Producto sobre pedido (no requiere sucursal)</option>
            </select>
            <small class="text-muted d-block">Los productos sobre pedido no requieren sucursal</small>
        ` : `
            <select class="form-select form-select-sm" onchange="actualizarSucursalEditar(${index}, this.value)" ${selectDisabled}>
                ${opcionesSucursales}
            </select>
            ${sucursalActualLista ? '<small class="text-muted d-block">Sucursal ya marcada como lista</small>' : ''}
        `;
        
        // Mostrar checkbox solo para productos externos
        const mostrarCheckbox = esExterno;
        
        html += `
            <tr data-index="${index}">
                <td class="text-center">${index + 1}</td>
                <td><small>${escapeHtml(item.codbar || item.ean || '-')}</small></td>
                <td>
                    <strong>${escapeHtml(item.nombre)}</strong>
                    ${esExterno ? '<br><span class="badge bg-info">Sobre pedido</span>' : ''}
                    ${item.descuento > 0 ? `<br><small class="text-muted"><i class="bi bi-tag"></i> ${item.descuento}% descuento aplicado</small>` : ''}
                    <br><small class="text-muted">Máx: ${item.inventario_disponible || 999}</small>
                </td>
                <td class="text-center"><span class="fw-bold">${item.cantidad}</span></td>
                <td class="text-end">
                    <span class="fw-bold">$${precioConDescuento.toFixed(2)}</span>
                    ${item.descuento > 0 ? `<br><small class="text-muted text-decoration-line-through">$${item.precio_unitario.toFixed(2)}</small>` : ''}
                </td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                <td>${selectHtml}</td>
                <td class="text-center seleccionar-columna" style="display: ${mostrarCheckbox ? 'none' : 'none'};">
                    ${mostrarCheckbox ? `<input type="checkbox" class="form-check-input checkbox-producto" data-index="${index}">` : ''}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('edit_total_pedido').textContent = `$${total.toFixed(2)}`;
    
    // Mostrar u ocultar el botón de reprogramación según si hay productos externos
    const btnReprogramar = document.getElementById('btnReprogramarProducto');
    if (btnReprogramar) {
        btnReprogramar.style.display = hayProductosExternos ? 'inline-block' : 'none';
    }
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
    const articulo = editArticulosSeleccionados[index];
    const sucursalIdInt = parseInt(sucursalId);
    
    // Verificar si la sucursal actual ya está marcada como lista
    if (sucursalesListas.includes(parseInt(articulo.id_sucursal_surtido))) {
        if (window.mostrarToast) {
            window.mostrarToast('No puedes cambiar la sucursal porque ya fue marcada como lista', 'warning');
        }
        return;
    }
    
    // Verificar si la nueva sucursal seleccionada ya está marcada como lista
    if (sucursalIdInt && sucursalesListas.includes(sucursalIdInt)) {
        if (window.mostrarToast) {
            window.mostrarToast('No puedes seleccionar esta sucursal porque ya fue marcada como lista', 'warning');
        }
        return;
    }
    
    // Guardar la sucursal seleccionada
    articulo.id_sucursal_surtido = sucursalIdInt || null;
    
    // Para productos externos, solo re-renderizar sin validar stock
    if (articulo.es_externo == 1) {
        renderizarTablaEditarProductos();
        if (sucursalIdInt && window.mostrarToast) {
            window.mostrarToast('Producto sobre pedido - No aplica validación de stock', 'info');
        }
        return;
    }
    
    // Si no hay sucursal seleccionada o no hay código de barras, solo re-renderizar
    if (!sucursalIdInt || !articulo.codbar) {
        renderizarTablaEditarProductos();
        if (!articulo.codbar && window.mostrarToast) {
            window.mostrarToast('El producto no tiene código de barras registrado', 'warning');
        }
        return;
    }
    
    // Mostrar estado de carga
    const row = document.querySelector(`#edit_productos_body tr[data-index="${index}"]`);
    if (row) {
        const stockCell = row.querySelector('td:nth-child(3) small.text-muted:last-child');
        if (stockCell) stockCell.innerHTML = '<i class="bi bi-hourglass-split"></i> Validando stock...';
    }
    
    // Consultar stock en la nueva sucursal usando EAN (código de barras)
    fetch(`/productos/stock-por-sucursal?ean=${encodeURIComponent(articulo.codbar)}&sucursal_id=${sucursalIdInt}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        let stockDisponible = 0;
        let stockData = null;
        
        if (data.success && data.data && data.data.length > 0) {
            stockData = data.data[0];
            if (stockData) {
                stockDisponible = stockData.inventario || stockData.disponible || 0;
            }
        }
        
        articulo.inventario_disponible = stockDisponible;
        
        // Validar si hay stock suficiente
        if (stockDisponible < articulo.cantidad) {
            if (stockDisponible === 0) {
                // No permitir seleccionar esta sucursal
                if (window.mostrarToast) {
                    window.mostrarToast(`No hay stock disponible en esta sucursal para "${articulo.nombre}". Selecciona otra sucursal.`, 'danger');
                }
                // Revertir a la sucursal anterior o dejar vacío
                articulo.id_sucursal_surtido = null;
            } else {
                // Permitir seleccionar pero mostrar advertencia
                if (window.mostrarToast) {
                    window.mostrarToast(`Stock insuficiente en esta sucursal. Solo hay ${stockDisponible} unidades disponibles de "${articulo.nombre}". Necesitas ${articulo.cantidad} unidades.`, 'warning');
                }
            }
        } else if (stockDisponible >= articulo.cantidad) {
            if (window.mostrarToast) {
                window.mostrarToast(`Stock suficiente en esta sucursal: ${stockDisponible} unidades disponibles.`, 'success');
            }
        }
        
        renderizarTablaEditarProductos();
    })
    .catch(error => {
        console.error('Error consultando stock:', error);
        renderizarTablaEditarProductos();
        if (window.mostrarToast) {
            window.mostrarToast('Error al consultar stock', 'warning');
        }
    });
};

// Función global para eliminar producto por índice (sin mensaje)
window.eliminarProductoPorIndice = function(index) {
    editArticulosSeleccionados.splice(index, 1);
    renderizarTablaEditarProductos();
};

// ============================================
// REPROGRAMAR PRODUCTO (UNO O VARIOS)
// ============================================
// Variables
let modoReprogramacion = false;
let productosSeleccionadosIndices = [];

// Función para resetear el modo selección
function resetearModoReprogramacion() {
    modoReprogramacion = false;
    productosSeleccionadosIndices = [];
    
    // Ocultar columna de selección
    const seleccionarHeader = document.getElementById('seleccionar_header');
    if (seleccionarHeader) seleccionarHeader.style.display = 'none';
    
    // Ocultar columnas y checkboxes
    document.querySelectorAll('.seleccionar-columna').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll('.checkbox-producto').forEach(cb => {
        cb.style.display = 'none';
        cb.checked = false;
    });
    
    // Restaurar botones
    const btnReprogramar = document.getElementById('btnReprogramarProducto');
    const btnSeleccionados = document.getElementById('btnReprogramarSeleccionados');
    if (btnReprogramar) btnReprogramar.style.display = 'inline-block';
    if (btnSeleccionados) btnSeleccionados.style.display = 'none';
}

// Botón principal "Reprogramar producto" (con event delegation)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('#btnReprogramarProducto');
    if (btn) {
        e.preventDefault();
        
        if (!modoReprogramacion) {
            modoReprogramacion = true;
            
            // Mostrar columna de selección
            const seleccionarHeader = document.getElementById('seleccionar_header');
            if (seleccionarHeader) seleccionarHeader.style.display = '';
            
            // Mostrar columnas y checkboxes
            document.querySelectorAll('.seleccionar-columna').forEach(el => {
                el.style.display = '';
            });
            document.querySelectorAll('.checkbox-producto').forEach(cb => {
                cb.style.display = '';
                cb.checked = false;
            });
            
            // Cambiar botones
            btn.style.display = 'none';
            const btnSeleccionados = document.getElementById('btnReprogramarSeleccionados');
            if (btnSeleccionados) btnSeleccionados.style.display = 'inline-block';
        }
    }
});

// Botón "Reprogramar seleccionados" (con event delegation)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('#btnReprogramarSeleccionados');
    if (btn && modoReprogramacion) {
        e.preventDefault();
        
        productosSeleccionadosIndices = [];
        document.querySelectorAll('.checkbox-producto:checked').forEach(cb => {
            productosSeleccionadosIndices.push(parseInt(cb.dataset.index));
        });
        
        if (productosSeleccionadosIndices.length === 0) {
            if (window.mostrarToast) window.mostrarToast('Selecciona al menos un producto', 'warning');
            return;
        }
        
        const count = productosSeleccionadosIndices.length;
        document.getElementById('reprogramar_count').textContent = count;
        
        let listaHtml = '<ul class="mb-0">';
        productosSeleccionadosIndices.forEach(idx => {
            const p = editArticulosSeleccionados[idx];
            listaHtml += `<li><strong>${escapeHtml(p.nombre)}</strong> (Cant: ${p.cantidad})</li>`;
        });
        listaHtml += '</ul>';
        document.getElementById('reprogramar_lista').innerHTML = listaHtml;
        document.getElementById('reprogramar_motivo').value = '';
        
        fetch('/sucursales/activas')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('reprogramar_sucursal_id');
                if (data.success && data.data) {
                    select.innerHTML = '<option value="">Seleccionar sucursal...</option>';
                    data.data.forEach(sucursal => {
                        select.innerHTML += `<option value="${sucursal.id_sucursal}">${escapeHtml(sucursal.nombre)}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        
        new bootstrap.Modal(document.getElementById('modalReprogramarProducto')).show();
    }
});

// Confirmar reprogramación
function confirmarReprogramacion() {
    const motivo = document.getElementById('reprogramar_motivo').value.trim();
    const sucursalId = document.getElementById('reprogramar_sucursal_id').value;
    
    if (!motivo) {
        if (window.mostrarToast) window.mostrarToast('Ingrese el motivo de reprogramación', 'warning');
        return;
    }
    if (!sucursalId) {
        if (window.mostrarToast) window.mostrarToast('Seleccione una sucursal', 'warning');
        return;
    }
    
    if (productosSeleccionadosIndices.length === 0) {
        if (window.mostrarToast) window.mostrarToast('No hay productos seleccionados', 'warning');
        return;
    }
    
    const pedidoId = document.getElementById('edit_pedido_id').value;
    const productosData = productosSeleccionadosIndices.map(idx => {
        const p = editArticulosSeleccionados[idx];
        return {
            detalle_id: p.id_detalle_pedido,
            producto_data: {
                ean: p.ean || p.codbar,
                nombre: p.nombre,
                cantidad: p.cantidad,
                precio_unitario: p.precio_unitario,
                descuento: p.descuento,
                importe: p.importe,
                es_externo: p.es_externo || 0,
                id_cotizacion_detalle: p.id_cotizacion_detalle
            }
        };
    });
    
    const btn = document.getElementById('btnConfirmarReprogramacion');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    // URL correcta (sin espacios, usando route)
    const url = '{{ route("ventas.pedidos.reprogramar-multi") }}';
    
    console.log('Enviando a:', url); // Depuración
    console.log('Datos:', { pedido_id: pedidoId, motivo: motivo, sucursal_id: sucursalId, productos: productosData });
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pedido_id: parseInt(pedidoId),
            motivo: motivo,
            sucursal_id: parseInt(sucursalId),
            productos: productosData
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            // Cerrar el modal de reprogramación
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalReprogramarProducto'));
            if (modal) modal.hide();
            // Recargar la página para ver los cambios
            setTimeout(() => location.reload(), 1500);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar reprogramación';
        }
    })
    .catch(error => {
        console.error('Error detallado:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión: ' + error.message, 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar reprogramación';
    });
}

// Resetear modo cuando se cierra el modal de edición (no solo el de reprogramación)
const modalEditar = document.getElementById('modalEditarPedido');
if (modalEditar) {
    modalEditar.addEventListener('hidden.bs.modal', function() {
        resetearModoReprogramacion();
    });
}

// También resetear si se cierra el modal de reprogramación sin guardar
const modalReprogramar = document.getElementById('modalReprogramarProducto');
if (modalReprogramar) {
    modalReprogramar.addEventListener('hidden.bs.modal', function() {
        // No resetear aquí, solo limpiar campos
        document.getElementById('reprogramar_motivo').value = '';
    });
}

// ============================================
// GUARDAR EDICIÓN DEL PEDIDO
// ============================================
window.guardarEdicionPedido = function() {
    const pedidoId = document.getElementById('edit_pedido_id').value;
    const comentarios = document.getElementById('edit_comentarios').value;
    const repartidorId = document.getElementById('edit_repartidor_id')?.value || null;
    const convenioGeneral = document.getElementById('edit_convenio_general').value;
    const productosSinSucursal = editArticulosSeleccionados.filter(p => 
        p.es_externo != 1 && !p.id_sucursal_surtido
    );

    if (productosSinSucursal.length > 0) {
        const nombres = productosSinSucursal.map(p => p.nombre).join(', ');
        if (window.mostrarToast) {
            window.mostrarToast(`Los siguientes productos requieren sucursal: ${nombres}`, 'warning');
        }
        return;
    }
    
    if (editArticulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('El pedido debe tener al menos un producto', 'warning');
        return;
    }
    
    // Obtener fecha y hora
    const fechaEntrega = document.getElementById('edit_fecha_entrega').value || null;
    let horaEntrega = document.getElementById('edit_hora_entrega').value;
    if (horaEntrega) {
        // Asegurar formato HH:MM (sin segundos)
        if (horaEntrega.includes(':')) {
            const partes = horaEntrega.split(':');
            horaEntrega = `${partes[0].padStart(2, '0')}:${partes[1].padStart(2, '0')}`;
        }
    }
    
    // Preparar datos para enviar
    const productos = editArticulosSeleccionados.map(p => ({
        id_detalle_pedido: p.id_detalle_pedido || null,
        ean: p.ean || p.codbar || null,
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
        fecha_entrega_sugerida: fechaEntrega,
        hora_entrega_sugerida: horaEntrega,
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
// Mensaje amigable para ver pedido (sin recargar, sin alertas, solo mostrar modal con datos)
// ============================================
window.verPedido = function(id) {
    fetch(`/ventas/pedidos/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => {
        if (response.status === 403) {
            return response.json().then(data => {
                if (window.mostrarToast) window.mostrarToast(data.message || 'No tienes acceso a este pedido', 'warning');
                return null;
            });
        }
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            if (typeof cargarDatosVerPedido === 'function') {
                cargarDatosVerPedido(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalVerPedido'));
                modal.show();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión al cargar la cotización', 'danger');
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