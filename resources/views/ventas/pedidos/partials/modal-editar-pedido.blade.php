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
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-box-seam"></i> Productos del Pedido</strong>
                        </div>
                        <div class="card-body">
                            <!-- Buscador de productos NO SE AGREGAN PRODUCTOS A PEDIDO, SOLO SE MUESTRA LA INFORMACIÓN
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
                            -->

                            <!-- Tabla de productos -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 15%">Código</th>
                                            <th style="width: 35%">Producto / Descripción</th>
                                            <th style="width: 10%" class="text-center">Cantidad</th>
                                            <th style="width: 10%" class="text-end">Precio</th>
                                            <th style="width: 10%" class="text-end">Importe</th>
                                            <th style="width: 25%">Sucursal surtido</th>
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
    console.log('Cargando datos para editar pedido:', data);
    
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
        // Si la fecha viene con hora (ej: "2026-04-28 18:00:00"), extraer solo la fecha
        if (fechaStr.includes(' ')) {
            fechaStr = fechaStr.split(' ')[0];
        }
        document.getElementById('edit_fecha_entrega').value = fechaStr;
    } else {
        document.getElementById('edit_fecha_entrega').value = '';
    }

    // Hora de entrega sugerida
    if (data.hora_entrega_sugerida) {
        let hora = data.hora_entrega_sugerida;
        if (hora.includes('.')) {
            hora = hora.split('.')[0];
        }
        if (hora.includes(':')) {
            const partes = hora.split(':');
            hora = `${partes[0].padStart(2, '0')}:${partes[1].padStart(2, '0')}`;
        }
        document.getElementById('edit_hora_entrega').value = hora.substring(0, 5);
    } else {
        document.getElementById('edit_hora_entrega').value = '';
    }

    console.log('=== FECHAS RECIBIDAS ===');
    console.log('fecha_entrega_sugerida:', data.fecha_entrega_sugerida);
    console.log('hora_entrega_sugerida:', data.hora_entrega_sugerida);

    // Guardar qué sucursales están listas (usando la variable global)
    sucursalesListas = [];
    if (data.sucursales && data.sucursales.length) {
        sucursalesListas = data.sucursales.filter(s => s.status === true).map(s => parseInt(s.id_sucursal));
        console.log('=== SUCURSALES LISTAS CARGADAS ===');
        console.log('sucursalesListas:', sucursalesListas);
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

    console.log('Asignando fecha:', data.fecha_entrega_sugerida);
    console.log('Asignando hora:', data.hora_entrega_sugerida);
    
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
        editArticulosSeleccionados = data.detalles.map(detalle => {
            console.log('Detalle ID:', detalle.id_detalle_pedido);
            return {
                id_detalle_pedido: detalle.id_detalle_pedido,
                id_producto: detalle.id_producto,
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
                nombre_sucursal: detalle.sucursalSurtido?.nombre || 'No asignada'
            };
        });
    } else if (data.cotizacion && data.cotizacion.detalles && data.cotizacion.detalles.length > 0) {
        // Fallback: usar detalles de cotización
        editArticulosSeleccionados = data.cotizacion.detalles.map(detalle => {
            return {
                id_detalle_pedido: null,
                id_producto: detalle.id_producto,
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
                nombre_sucursal: detalle.sucursal_surtido?.nombre || 'No asignada'
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
// AGREGAR PRODUCTO POR ÍNDICE
// ============================================
window.agregarArticuloEditPorIndice = function(idx) {
    if (!editResultadosBusqueda || !editResultadosBusqueda[idx]) return;
    
    const articuloData = editResultadosBusqueda[idx];
    const esExterno = articuloData.es_externo == 1 || articuloData.es_externo === true || articuloData.es_externo === "1";
    const productosActivos = editArticulosSeleccionados.filter(p => !p.se_elimino);

    const nuevoArticulo = {
        id_producto: esExterno ? null : articuloData.id,
        nombre: articuloData.nombre,
        ean: articuloData.codbar,
        codbar: articuloData.codbar,
        precio_unitario: articuloData.precio,
        cantidad: 1,
        descuento: 0,
        id_convenio: null,
        id_sucursal_surtido: null,  // El operador debe seleccionar manualmente
        num_familia: articuloData.num_familia || (esExterno ? 'EXT' : ''),
        inventario_disponible: articuloData.inventario || 999,
        nombre_sucursal: articuloData.nombre_sucursal || (esExterno ? 'Sobre Pedido' : 'No asignada'),
        es_externo: esExterno ? 1 : 0, 
        es_agregado: true,
        id_detalle_pedido: null,
        id_cotizacion_detalle: null
    };

    // Aplicar descuento del convenio general SOLO si NO es externo
    if (!esExterno) {
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
    // Buscar si ya existe (mismo producto, misma sucursal, mismo tipo, NO eliminado)
    const existe = listaArticulos.find(a => 
        !a.se_elimino &&  // ← Ignorar productos marcados como eliminados
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
// RENDERIZAR TABLA DE PRODUCTOS (SOLO LECTURA CON SELECT DE SUCURSAL)
// ============================================
function renderizarTablaEditarProductos() {
    const tbody = document.getElementById('edit_productos_body');
    let total = 0;
    console.log('=== RENDERIZANDO TABLA ===');
    console.log('sucursalesListas actual:', sucursalesListas);
    console.log('editArticulosSeleccionados:', editArticulosSeleccionados);
    
    if (!editArticulosSeleccionados.length) {
        tbody.innerHTML = `<tr id="edit-sin-productos"><td colspan="7" class="text-center py-4 text-muted">
            <i class="bi bi-box-seam"></i> No hay productos en este pedido
        <\/td><\/tr>`;
        document.getElementById('edit_total_pedido').textContent = '$0.00';
        return;
    }
    
    let html = '';
    editArticulosSeleccionados.forEach((item, index) => {
        const precioConDescuento = item.precio_unitario * (1 - (item.descuento || 0) / 100);
        const importe = item.cantidad * precioConDescuento;
        total += importe;
        const esExterno = item.es_externo == 1;
        
        // Log para cada producto
        console.log(`Producto ${index}: ${item.nombre}, sucursal: ${item.id_sucursal_surtido}, ¿está en lista?`, sucursalesListas.includes(parseInt(item.id_sucursal_surtido)));
        console.log('editCatalogos.sucursales en renderizar:', editCatalogos.sucursales);
        
        // Verificar si la sucursal actual ya está marcada como lista
        const sucursalActualLista = sucursalesListas.includes(parseInt(item.id_sucursal_surtido));
        const selectDisabled = sucursalActualLista ? 'disabled' : '';
        
        // Generar opciones del select, deshabilitando las sucursales que ya están listas
        let opcionesSucursales = '<option value="">Seleccionar sucursal...</option>';
        console.log('Generando opciones, sucursales:', editCatalogos.sucursales);
        editCatalogos.sucursales.forEach(s => {
            const sucursalLista = sucursalesListas.includes(parseInt(s.id_sucursal));
            const selectedAttr = (item.id_sucursal_surtido == s.id_sucursal) ? 'selected' : '';
            const disabledAttr = (sucursalLista && item.id_sucursal_surtido != s.id_sucursal) ? 'disabled' : '';
            opcionesSucursales += `<option value="${s.id_sucursal}" ${selectedAttr} ${disabledAttr}>${escapeHtml(s.nombre)}${sucursalLista ? ' (Ya lista)' : ''}</option>`;
        });
        
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
                <td class="text-center">
                    <span class="fw-bold">${item.cantidad}</span>
                </td>
                <td class="text-end">
                    <span class="fw-bold">$${precioConDescuento.toFixed(2)}</span>
                    ${item.descuento > 0 ? `<br><small class="text-muted text-decoration-line-through">$${item.precio_unitario.toFixed(2)}</small>` : ''}
                </td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                <td>
                    <select class="form-select form-select-sm" onchange="actualizarSucursalEditar(${index}, this.value)" ${selectDisabled}>
                        ${opcionesSucursales}
                    </select>
                    ${sucursalActualLista ? '<small class="text-muted d-block">Sucursal ya marcada como lista</small>' : ''}
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
    
    console.log('Artículo actualizado:', {
        id_detalle_pedido: articulo.id_detalle_pedido,
        id_producto: articulo.id_producto,
        id_sucursal_surtido: articulo.id_sucursal_surtido
    });
    
    // Para productos externos, solo re-renderizar sin validar stock
    if (articulo.es_externo == 1) {
        renderizarTablaEditarProductos();
        if (sucursalIdInt && window.mostrarToast) {
            window.mostrarToast('Producto sobre pedido - No aplica validación de stock', 'warning');
        }
        return;
    }
    
    // Si no hay sucursal seleccionada, solo re-renderizar
    if (!sucursalIdInt || !articulo.id_producto) {
        renderizarTablaEditarProductos();
        return;
    }
    
    // Mostrar estado de carga
    const row = document.querySelector(`#edit_productos_body tr[data-index="${index}"]`);
    if (row) {
        const stockCell = row.querySelector('td:nth-child(3) small.text-muted:last-child');
        if (stockCell) stockCell.innerHTML = '<i class="bi bi-hourglass-split"></i> Validando stock...';
    }
    
    // Consultar stock en la nueva sucursal
    fetch(`/productos/stock-por-sucursal/${articulo.id_producto}?sucursal_id=${sucursalIdInt}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        let stockDisponible = 0;
        let stockData = null;
        
        if (data.success && data.data && data.data.length > 0) {
            stockData = data.data.find(s => s.id_sucursal == sucursalIdInt);
            if (stockData) {
                stockDisponible = stockData.disponible || 0;
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

{{--  NO APLICA LA ELIMINACION DE PRODUCTOS DE LA LISTA (SOLO LECTURA)
// Modificar eliminarProductoEditar para usar el modal
window.eliminarProductoEditar = function(index) {
    // Eliminar directamente sin confirmación
    editArticulosSeleccionados.splice(index, 1);
    renderizarTablaEditarProductos();
};
--}}

// ============================================
// GUARDAR EDICIÓN DEL PEDIDO
// ============================================
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
        id_producto: p.id_producto,
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
    console.log('Productos a enviar:', productos);
    
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