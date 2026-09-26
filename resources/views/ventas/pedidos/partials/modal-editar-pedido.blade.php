<div class="modal fade" id="modalEditarPedido" tabindex="-1" aria-labelledby="modalEditarPedidoLabel" data-bs-backdrop="static">
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

<!-- Modal Reprogramar Producto (soporta uno o varios, multi-sucursal) -->
<div class="modal fade" id="modalReprogramarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i> Reprogramar Producto(s)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Motivo -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Motivo de reprogramación <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="reprogramar_motivo" rows="2"
                            placeholder="Ej: Producto no llegó a tiempo, el proveedor no lo surtió, etc."
                            required></textarea>
                </div>

                <!-- Aviso informativo + controles -->
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div class="reprogram-alert" style="flex: 1; margin-bottom: 0;">
                        <i class="bi bi-info-circle-fill"></i>
                        <div>
                            Asigna las cantidades a una o varias sucursales. La suma debe coincidir con la cantidad a reprogramar de cada producto.
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm" style="flex-shrink: 0;">
                        <button type="button" class="btn btn-outline-secondary" onclick="expandirTodosReprogramacion()" title="Expandir todos">
                            <i class="bi bi-arrows-expand"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="colapsarTodosReprogramacion()" title="Colapsar todos">
                            <i class="bi bi-arrows-collapse"></i>
                        </button>
                    </div>
                </div>

                <!-- Contenedor de productos -->
                <div id="reprogramar_productos_container">
                    <div class="alert alert-info text-center" id="reprogramar_cargando">
                        <i class="bi bi-hourglass-split"></i> Cargando disponibilidad...
                    </div>
                </div>

                <!-- Resumen general al pie -->
                <div class="reprogram-resumen" id="reprogramar_resumen_general">
                    <div class="resumen-info">
                        <i class="bi bi-clipboard-check"></i>
                        <span id="reprogramar_resumen_texto">0 productos listos para reprogramar</span>
                    </div>
                    <div class="resumen-totales" id="reprogramar_resumen_totales">
                        Total asignado: <strong>0</strong> unidades
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarReprogramacion" onclick="confirmarReprogramacion()" disabled>
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
window.cargarDatosEditarPedido = async function(data) {
    try {
    // Limpiar variables y UI
    editArticulosSeleccionados = [];
    
    function safeSetValue(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.value = value !== null && value !== undefined ? value : '';
    } else {
        console.warn(`Elemento no encontrado: ${id}`);
    }
    }

    // Establecer permiso de edición
    window.puedeEditarPedido = data.puede_editar || false;

    function safeSetText(id, text) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = text || '-';
        } else {
            console.warn(`Elemento no encontrado: ${id}`);
        }
    }

    // Luego las usamos:
    safeSetValue('edit_pedido_id', data.id_pedido);
    safeSetText('edit_folio_pedido', data.folio_pedido);

    // Datos básicos del pedido
    document.getElementById('edit_pedido_id').value = data.id_pedido;
    document.getElementById('edit_folio_pedido').textContent = data.folio_pedido;
    document.getElementById('edit_fecha_pedido').textContent = data.fecha_pedido ? new Date(data.fecha_pedido).toLocaleString() : '-';
    document.getElementById('edit_comentarios').value = data.comentarios || '';

    // Fecha de entrega sugerida
    if (data.fecha_entrega_sugerida) {
        let fechaStr = data.fecha_entrega_sugerida;
        if (fechaStr.includes('T')) {
            fechaStr = fechaStr.split('T')[0];
        }
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
        if (hora.includes('T')) {
            const partes = hora.split('T');
            if (partes[1]) {
                hora = partes[1];
            }
        }
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

    // Guardar qué sucursales están listas
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
        document.getElementById('edit_modificado_por').textContent = `${data.creador.Nombre || ''} ${data.creador.ApPaterno || ''} ${data.creador.apMaterno || ''}`.trim() || 'Sin modificaciones';
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

    // ============================================
    // PRECARGAR CONVENIO GENERAL
    // ============================================
    if (data.detalles && data.detalles.length > 0) {
        const detalleConConvenio = data.detalles.find(d => d.id_convenio != null);
        if (detalleConConvenio) {
            document.addEventListener('editCatalogosCargados', function onCatalogosListos() {
                document.removeEventListener('editCatalogosCargados', onCatalogosListos);
                const convenioSelect = document.getElementById('edit_convenio_general');
                if (convenioSelect) {
                    convenioSelect.value = detalleConConvenio.id_convenio;
                }
            });
        }
    }
    
    // CARGAR PRODUCTOS
    if (data.detalles && data.detalles.length > 0) {
        // Filtrar productos no eliminados
        const detallesActivos = data.detalles.filter(detalle => detalle.se_elimino != 1);
        
        // Usar los detalles guardados en orden_pedido_detalle
        editArticulosSeleccionados = detallesActivos.map(detalle => {
            // El backend ya envía el nombre correctamente en detalle.nombre
            let nombreProducto = detalle.nombre || (detalle.es_externo == 1 ? 'Producto sobre pedido' : `Producto ${detalle.ean || detalle.codbar}`);

            // Usar stock_actual del backend
            let inventarioActual = detalle.stock_actual ?? 0;
            
            // Si no hay stock_actual, intentar con producto.inventario (fallback)
            if (inventarioActual === 0 && detalle.producto) {
                inventarioActual = detalle.producto.inventario || 0;
            }
            
            // Inventario_global desde el detalle
            let inventarioGlobal = detalle.inventario_global ?? 0;
            
            return {
                id_detalle_pedido: detalle.id_detalle_pedido,
                nombre: nombreProducto,
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
                inventario_actual: inventarioActual,
                inventario_global: inventarioGlobal,
                es_sobre_pedido: detalle.es_sobre_pedido || false,
                nombre_sucursal: detalle.sucursalSurtido?.nombre || 'No asignada',
                se_elimino: detalle.se_elimino || 0,
                detalle_sucursales: detalle.detalle_sucursales || '' // Ya viene del backend
            };
        });
    } else if (data.cotizacion && data.cotizacion.detalles && data.cotizacion.detalles.length > 0) {
        // Fallback: usar detalles de cotización
        editArticulosSeleccionados = data.cotizacion.detalles.map(detalle => {
            let inventarioActual = 0;
            if (detalle.producto) {
                inventarioActual = detalle.producto.inventario || 0;
            }
            
            // Inventario_global
            let inventarioGlobal = detalle.inventario_global ?? 0;
            
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
                inventario_actual: inventarioActual,
                inventario_global: inventarioGlobal,
                es_sobre_pedido: detalle.es_sobre_pedido || false,
                nombre_sucursal: detalle.sucursal_surtido?.nombre || 'No asignada',
                se_elimino: 0,
                detalle_sucursales: detalle.detalle_sucursales || '' // Ya viene del backend
            };
        });
    }

    // ============================================
    // AGRUPAR POR EAN PARA VALIDACIÓN GLOBAL
    // ============================================
    const resumenPorEAN = {};
    editArticulosSeleccionados.forEach(item => {
        const ean = item.ean;
        if (!resumenPorEAN[ean]) {
            resumenPorEAN[ean] = {
                ean: ean,
                cantidadTotal: 0,
                items: []
            };
        }
        resumenPorEAN[ean].cantidadTotal += item.cantidad;
        resumenPorEAN[ean].items.push(item);
    });

    // Guardar el resumen en una variable global para usarlo en validaciones
    window.resumenPorEAN = resumenPorEAN;

    // ============================================
    // OBTENER DESGLOSE DE INVENTARIO POR SUCURSAL (SOLO PARA NO EXTERNOS)
    // ============================================
    if (editArticulosSeleccionados.length > 0) {
        // Obtener EANs de los productos que no son externos
        const eans = editArticulosSeleccionados
            .filter(item => item.codbar && !item.codbar.toString().startsWith('T'))
            .map(item => item.codbar);
        
        if (eans.length > 0) {
            try {
                const response = await fetch('/api/inventario-detalle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ eans: eans })
                });
                
                const result = await response.json();
                if (result.success && result.data) {
                    // Asignar el desglose a cada producto
                    editArticulosSeleccionados = editArticulosSeleccionados.map(item => {
                        if (item.codbar && result.data[item.codbar]) {
                            const sucursales = result.data[item.codbar];
                            const partes = sucursales.map(s => `${s.nombre}: ${s.inventario}`);
                            item.detalle_sucursales = partes.join(' | ');
                        } else if (item.codbar && item.codbar.toString().startsWith('T')) {
                            item.detalle_sucursales = 'Producto sobre pedido';
                        } else {
                            item.detalle_sucursales = '';
                        }
                        return item;
                    });
                }
            } catch (error) {
                console.error('Error al obtener desglose de inventario:', error);
            }
        }
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
    // CARGAR REPARTIDORES
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
    
    // Renderizar la tabla con los datos actualizados
    renderizarTablaEditarProductos();
    
    // Mostrar/ocultar sección de asignación de repartidor
    const asignacionRepartidorSection = document.getElementById('edit_asignacion_repartidor_section');
    if (asignacionRepartidorSection) {
        asignacionRepartidorSection.style.display = data.mostrar_asignacion_repartidor ? 'block' : 'none';
    }
    } catch (error) {
        console.error('Error en cargarDatosEditarPedido:', error);
        console.error('Stack trace', error.stack);
        if (window.mostrarToast) window.mostrarToast('Error al cargar datos del pedido', 'danger');
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
// RENDERIZAR TABLA DE PRODUCTOS
// ============================================
function renderizarTablaEditarProductos() {
    const tbody = document.getElementById('edit_productos_body');
    let total = 0;
    
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
        
        // Determinar si es externo por el EAN (empieza con 'T')
        const esExterno = item.ean && item.ean.toString().startsWith('T');
        const esSobrePedido = item.es_sobre_pedido || false;

        // Verificar si el producto tiene stock insuficiente
        let stockInsuficiente = false;
        if (item.inventario_actual !== undefined && item.inventario_actual < item.cantidad) {
            stockInsuficiente = true;
        }
        
        // Obtener el desglose de sucursales desde el item
        const detalleSucursales = item.detalle_sucursales || '';
        let detalleHtml = '';
        if (detalleSucursales) {
            detalleHtml = `<br><small class="text-muted"><i class="bi bi-building"></i> Disponible por sucursal: ${escapeHtml(detalleSucursales)}</small>`;
        }
        
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
        
        const selectHtml = `
            <select class="form-select form-select-sm" onchange="actualizarSucursalEditar(${index}, this.value)" ${selectDisabled}>
                ${opcionesSucursales}
            </select>
            ${sucursalActualLista ? '<small class="text-muted d-block">Sucursal ya marcada como lista</small>' : ''}
        `;
        
        // Determinar si el precio es editable (solo para externos)
        const precioEditable = esExterno ? '' : 'readonly';
        const precioBg = esExterno ? '#fff3cd' : '#e9ecef';
        const precioBadge = esExterno ? '<span class="badge bg-info ms-1" style="font-size: 0.6rem;">editable</span>' : '';
        
        // Agregar badge de advertencia si hay problemas de stock
        const ean = item.ean;
        const totalRequeridoEAN = window.resumenPorEAN?.[ean]?.cantidadTotal || item.cantidad;

        let badgeAdvertencia = '';
        if (esExterno || esSobrePedido) {
            badgeAdvertencia = `<br><span class="badge bg-info">Sobre pedido</span>`;
        } else {
            const stockGlobal = item.inventario_global ?? 0;
            if (stockGlobal < totalRequeridoEAN) {
                badgeAdvertencia = `<br><span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Stock global insuficiente: ${stockGlobal} disponibles (requeridos: ${totalRequeridoEAN})</span>`;
            } else {
                badgeAdvertencia = `<br><span class="badge bg-success">Stock global: ${stockGlobal} unidades</span>`;
            }
        }
        
        html += `
            <tr data-index="${index}">
                <td class="text-center">${index + 1}</td>
                <td><small>${escapeHtml(item.codbar || item.ean || '-')}</small></td>
                <td>
                    <strong>${escapeHtml(item.nombre)}</strong>
                    ${badgeAdvertencia}
                    ${item.descuento > 0 ? `<br><small class="text-success"><i class="bi bi-tag"></i> ${item.descuento}% descuento aplicado</small>` : ''}
                    <br><small class="text-muted">Máx: ${item.inventario_disponible || 999}</small>
                    ${detalleHtml}
                </td>
                <td class="text-center"><span class="fw-bold">${item.cantidad}</span></td>
                <td class="text-end">
                    <input type="number" step="0.50" class="form-control form-control-sm text-end edit-precio-pedido" 
                        value="${item.precio_unitario.toFixed(2)}" min="0" 
                        data-index="${index}"
                        ${precioEditable}
                        style="width: 120px; margin-left: auto; background-color: ${precioBg};">
                    ${precioBadge}
                    ${item.descuento > 0 ? `<br><small class="text-muted text-decoration-line-through">$${item.precio_unitario.toFixed(2)}</small>` : ''}
                </td>
                <td class="text-end fw-bold" id="edit-importe-pedido-${index}">$${importe.toFixed(2)}</td>
                <td>${selectHtml}</td>
                <td class="text-center seleccionar-columna" style="display: none;">
                    <input type="checkbox" class="checkbox-producto" data-index="${index}" style="display: none;">
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('edit_total_pedido').textContent = `$${total.toFixed(2)}`;
    
    // Agregar event listeners para los precios editables
    document.querySelectorAll('.edit-precio-pedido').forEach(input => {
        input.addEventListener('input', function() {
            const index = parseInt(this.dataset.index);
            const val = parseFloat(this.value) || 0;
            if (val < 0) this.value = 0;
            
            const nuevoPrecio = parseFloat(this.value) || 0;
            
            // Actualizar en el array local
            editArticulosSeleccionados[index].precio_unitario = nuevoPrecio;
            
            // Actualizar solo el importe de la fila y el total
            actualizarImporteFilaPedidoEdit(index);
            
            // Si el producto es externo, actualizar en tmp_catalogo
            const esExterno = editArticulosSeleccionados[index].ean && editArticulosSeleccionados[index].ean.toString().startsWith('T');
            if (esExterno) {
                const ean = editArticulosSeleccionados[index].ean;
                if (ean && ean.startsWith('T')) {
                    actualizarPrecioTmpCatalogo(ean, nuevoPrecio);
                }
            }
        });
    });
    
    // Mostrar u ocultar el botón de reprogramación
    const btnReprogramar = document.getElementById('btnReprogramarProducto');
    if (btnReprogramar) {
        // Verificar si TODAS las sucursales de surtido ya marcaron como listo
        // sucursalesListas contiene los IDs de sucursales que ya están listas
        // Si hay al menos una sucursal de surtido que NO está en sucursalesListas, el botón es visible
        const sucursalesSurtido = editArticulosSeleccionados
            .map(item => item.id_sucursal_surtido)
            .filter(id => id !== null && id !== undefined && id !== '')
            .map(id => parseInt(id));
        
        // Obtener IDs únicos de sucursales de surtido
        const sucursalesUnicas = [...new Set(sucursalesSurtido)];
        
        // Si no hay sucursales de surtido asignadas, mostrar el botón (por si acaso)
        if (sucursalesUnicas.length === 0) {
            btnReprogramar.style.display = 'inline-block';
        } else {
            // Verificar si todas las sucursales de surtido ya están listas
            const todasListas = sucursalesUnicas.every(sucursalId => 
                sucursalesListas.includes(sucursalId)
            );
            
            // Si todas están listas, ocultar el botón; si no, mostrarlo
            btnReprogramar.style.display = todasListas ? 'none' : 'inline-block';
        }
    }
    
    // Asegurar que el botón "Reprogramar seleccionados" esté oculto al inicio
    const btnSeleccionados = document.getElementById('btnReprogramarSeleccionados');
    if (btnSeleccionados) {
        btnSeleccionados.style.display = 'none';
    }
    
    // Asegurar que la columna de selección esté oculta al inicio
    const seleccionarHeader = document.getElementById('seleccionar_header');
    if (seleccionarHeader) {
        seleccionarHeader.style.display = 'none';
    }
    
    // Asegurar que los checkboxes estén ocultos al inicio
    document.querySelectorAll('.seleccionar-columna').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll('.checkbox-producto').forEach(cb => {
        cb.style.display = 'none';
        cb.checked = false;
    });
}
 
// ============================================
// FUNCIÓN PARA ACTUALIZAR IMPORTE DE FILA EN PEDIDO
// ============================================
function actualizarImporteFilaPedidoEdit(index) {
    const articulo = editArticulosSeleccionados[index];
    if (!articulo) return;
    
    const precioConDescuento = articulo.precio_unitario * (1 - (articulo.descuento || 0) / 100);
    const importe = articulo.cantidad * precioConDescuento;
    
    // Actualizar importe de la fila
    const importeSpan = document.getElementById(`edit-importe-pedido-${index}`);
    if (importeSpan) {
        importeSpan.textContent = `$${importe.toFixed(2)}`;
    }
    
    // Recalcular total
    let total = 0;
    for (const item of editArticulosSeleccionados) {
        const precioConDesc = item.precio_unitario * (1 - (item.descuento || 0) / 100);
        total += item.cantidad * precioConDesc;
    }
    
    const totalSpan = document.getElementById('edit_total_pedido');
    if (totalSpan) {
        totalSpan.textContent = `$${total.toFixed(2)}`;
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
    
    // Determinar si es externo por el EAN
    const esExterno = articulo.ean && articulo.ean.toString().startsWith('T');
    
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
    
    // Para productos externos, no validar stock
    if (esExterno) {
        renderizarTablaEditarProductos();
        if (sucursalIdInt && window.mostrarToast) {
            window.mostrarToast('Sucursal asignada para producto sobre pedido', 'warning');
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
        if (stockCell) stockCell.innerHTML = '<i class="bi bi-hourglass-split"></i> Validando stock global...';
    }
    
    // ============================================
    // CONSULTAR STOCK POR SUCURSAL (PARA INFORMACIÓN)
    // ============================================
    fetch(`/productos/stock-por-sucursal?ean=${encodeURIComponent(articulo.codbar)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        let stockGlobal = 0;
        let detalleSucursales = '';
        
        if (data.success && data.data) {
            const sucursales = data.data || [];
            stockGlobal = sucursales.reduce((sum, s) => sum + (parseFloat(s.inventario) || 0), 0);
            stockGlobal = Math.floor(stockGlobal);
            
            const partes = sucursales.map(s => `${s.nombre}: ${parseFloat(s.inventario).toFixed(2)}`);
            detalleSucursales = partes.join(' | ');
        }
        
        // Obtener el total requerido para este EAN (agrupado)
        const ean = articulo.ean;
        const totalRequeridoEAN = window.resumenPorEAN?.[ean]?.cantidadTotal || articulo.cantidad;
        
        articulo.inventario_global = stockGlobal;
        articulo.detalle_sucursales = detalleSucursales;
        
        // Validar contra el total requerido agrupado
        if (stockGlobal < totalRequeridoEAN) {
            if (stockGlobal === 0) {
                if (window.mostrarToast) {
                    window.mostrarToast(
                        `No hay inventario global disponible para "${articulo.nombre}". Puedes reprogramar el producto.`,
                        'danger'
                    );
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast(
                        `Inventario global insuficiente. Solo hay ${stockGlobal} unidades disponibles de "${articulo.nombre}". Necesitas ${totalRequeridoEAN} unidades en total. Puedes reprogramar el producto.`,
                        'warning'
                    );
                }
            }
        } else {
            if (window.mostrarToast) {
                window.mostrarToast(
                    `Inventario global suficiente: ${stockGlobal} unidades disponibles en total.`,
                    'success'
                );
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
    if (seleccionarHeader) {
        seleccionarHeader.style.display = 'none';
    }
    
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
    if (btnReprogramar) {
        btnReprogramar.style.display = 'inline-block';
        btnReprogramar.disabled = false;
    }
    if (btnSeleccionados) {
        btnSeleccionados.style.display = 'none';
    }
}

// Función para mostrar checkboxes
function mostrarCheckboxes() {
    const seleccionarHeader = document.getElementById('seleccionar_header');
    if (seleccionarHeader) {
        seleccionarHeader.style.display = '';
    }
    
    const columnas = document.querySelectorAll('.seleccionar-columna');
    const checkboxes = document.querySelectorAll('.checkbox-producto');
    
    columnas.forEach(el => {
        el.style.display = '';
    });
    checkboxes.forEach(cb => {
        cb.style.display = '';
        cb.checked = false;
    });
    
    // Cambiar botones
    const btnReprogramar = document.getElementById('btnReprogramarProducto');
    const btnSeleccionados = document.getElementById('btnReprogramarSeleccionados');
    if (btnReprogramar) {
        btnReprogramar.style.display = 'none';
        btnReprogramar.disabled = false;
    }
    if (btnSeleccionados) {
        btnSeleccionados.style.display = 'inline-block';
        const count = document.querySelectorAll('.checkbox-producto:checked').length;
        btnSeleccionados.innerHTML = `<i class="bi bi-check2-circle"></i> Reprogramar seleccionados (${count})`;
    }
}

// Evento directo para el botón "Reprogramar producto"
document.getElementById('btnReprogramarProducto')?.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    // Verificar si tiene permiso de editar
    if (!window.puedeEditarPedido) {
        if (window.mostrarToast) {
            window.mostrarToast('No tienes permiso para reprogramar productos. Contacta al administrador.', 'warning');
        }
        return;
    }
    
    // Si ya está en modo reprogramación, salir del modo
    if (modoReprogramacion) {
        resetearModoReprogramacion();
        return;
    }
    
    // Activar modo reprogramación
    modoReprogramacion = true;
    
    // Deshabilitar el botón temporalmente para evitar doble clic
    this.disabled = true;
    
    // Verificar si los checkboxes existen
    let checkboxes = document.querySelectorAll('.checkbox-producto');
    if (checkboxes.length === 0) {
        renderizarTablaEditarProductos();
        setTimeout(() => {
            mostrarCheckboxes();
            this.disabled = false;
        }, 200);
    } else {
        mostrarCheckboxes();
        this.disabled = false;
    }
});

// Event listener para actualizar el contador al cambiar checkboxes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('checkbox-producto')) {
        const count = document.querySelectorAll('.checkbox-producto:checked').length;
        const btnSeleccionados = document.getElementById('btnReprogramarSeleccionados');
        if (btnSeleccionados) {
            btnSeleccionados.innerHTML = `<i class="bi bi-check2-circle"></i> Reprogramar seleccionados (${count})`;
        }
    }
});

// ============================================
// REPROGRAMACIÓN DE PRODUCTOS (MULTI-SUCURSAL)
// ============================================

// Estado interno del modal de reprogramación
window.reprogramacionState = {
    pedidoId: null,
    productos: [],
    seleccionados: [],
    cantidades: {},
    asignaciones: {},
};

// ============================================
// BOTÓN "Reprogramar seleccionados"
// ============================================
document.addEventListener('click', function(e) {
    if (e.target.closest('#btnReprogramarProducto')) {
        return;
    }

    const btn = e.target.closest('#btnReprogramarSeleccionados');
    if (!btn || !modoReprogramacion) return;

    e.preventDefault();
    e.stopPropagation();

    // Recolectar índices seleccionados
    const indicesSeleccionados = [];
    document.querySelectorAll('.checkbox-producto:checked').forEach(cb => {
        indicesSeleccionados.push(parseInt(cb.dataset.index));
    });

    if (indicesSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Selecciona al menos un producto', 'warning');
        return;
    }

    // Obtener el pedidoId actual
    const pedidoIdInput = document.getElementById('edit_pedido_id');
    const pedidoId = pedidoIdInput ? parseInt(pedidoIdInput.value) : null;

    if (!pedidoId) {
        if (window.mostrarToast) window.mostrarToast('No se pudo identificar el pedido', 'danger');
        return;
    }

    // Obtener los detalle_ids de los productos seleccionados
    const detalleIdsSeleccionados = indicesSeleccionados.map(idx => {
        const p = editArticulosSeleccionados[idx];
        return p ? (p.id_detalle_pedido || p.id_detalle || null) : null;
    }).filter(Boolean);

    if (detalleIdsSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Los productos no tienen ID de detalle', 'danger');
        return;
    }

    abrirModalReprogramacion(pedidoId, detalleIdsSeleccionados);
});

// ============================================
// ABRIR MODAL Y CARGAR DISPONIBILIDAD
// ============================================
function abrirModalReprogramacion(pedidoId, detalleIdsSeleccionados) {
    window.reprogramacionState = {
        pedidoId: pedidoId,
        productos: [],
        seleccionados: detalleIdsSeleccionados,
        cantidades: {},
        asignaciones: {},
    };

    document.getElementById('reprogramar_motivo').value = '';
    document.getElementById('reprogramar_productos_container').innerHTML =
        '<div class="alert alert-info text-center" id="reprogramar_cargando"><i class="bi bi-hourglass-split"></i> Cargando disponibilidad...</div>';
    document.getElementById('reprogramar_resumen_texto').textContent = 'Cargando...';
    document.getElementById('reprogramar_resumen_totales').textContent = 'Total asignado: 0 unidades';
    document.getElementById('reprogramar_resumen_general').className =
        'mt-3 p-3 rounded border border-2 border-secondary-subtle bg-light d-flex align-items-center justify-content-between';
    document.getElementById('btnConfirmarReprogramacion').disabled = true;

    const modal = new bootstrap.Modal(document.getElementById('modalReprogramarProducto'));
    modal.show();

    fetch(`/ventas/pedidos/${pedidoId}/disponibilidad-inventario`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(res => {
        if (!res.ok) throw new Error(`Error ${res.status}`);
        return res.json();
    })
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Error al cargar disponibilidad');

        const seleccionadosSet = new Set(detalleIdsSeleccionados);
        const productos = (data.data || []).filter(p => seleccionadosSet.has(p.detalle_id));

        if (productos.length === 0) {
            document.getElementById('reprogramar_productos_container').innerHTML =
                '<div class="alert alert-warning text-center">No se encontraron productos para reprogramar.</div>';
            return;
        }

        productos.forEach(p => {
            window.reprogramacionState.cantidades[p.detalle_id] = p.cantidad;
            window.reprogramacionState.asignaciones[p.detalle_id] = {};

            if (p.sucursal_original_id) {
                window.reprogramacionState.asignaciones[p.detalle_id][p.sucursal_original_id] = p.cantidad;
            }
        });

        window.reprogramacionState.productos = productos;
        renderizarCardsReprogramacion();
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('reprogramar_productos_container').innerHTML =
            `<div class="alert alert-danger text-center">Error al cargar disponibilidad: ${err.message}</div>`;
    });
}

// ============================================
// RENDERIZAR CARDS DE PRODUCTOS
// ============================================
function renderizarCardsReprogramacion() {
    const container = document.getElementById('reprogramar_productos_container');
    const { productos, cantidades, asignaciones } = window.reprogramacionState;

    if (productos.length === 0) {
        container.innerHTML = `
            <div class="reprogram-empty">
                <i class="bi bi-inbox"></i>
                <p class="mb-0">No hay productos seleccionados para reprogramar.</p>
            </div>
        `;
        recalcularResumenGeneral();
        return;
    }

    let html = '';
    let primerIncompletoAsignado = false;

    productos.forEach((p) => {
        const cantidadReprog = cantidades[p.detalle_id] || p.cantidad;
        const asignacionesProducto = asignaciones[p.detalle_id] || {};
        const totalAsignado = Object.values(asignacionesProducto).reduce((s, v) => s + v, 0);

        // Estado del progreso
        let progresoEstado = 'bg-danger';
        let progresoPct = 0;
        let cardEstado = '';
        let pctColor = 'text-muted';

        if (cantidadReprog > 0) {
            progresoPct = Math.min(100, Math.round((totalAsignado / cantidadReprog) * 100));
        }

        if (totalAsignado > cantidadReprog) {
            progresoEstado = 'bg-primary';
            cardEstado = 'excedido';
            pctColor = 'text-primary';
        } else if (totalAsignado === cantidadReprog && cantidadReprog > 0) {
            progresoEstado = 'bg-success';
            cardEstado = 'completo';
            pctColor = 'text-success';
        } else if (totalAsignado > 0) {
            progresoEstado = 'bg-warning';
            cardEstado = 'incompleto';
            pctColor = 'text-warning';
        } else {
            cardEstado = 'incompleto';
        }

        const completo = totalAsignado === cantidadReprog && cantidadReprog > 0;

        // Badge reprogramado antes
        const badgeReprog = p.fue_reprogramado
            ? '<span class="badge bg-secondary ms-2">Reprogramado antes</span>'
            : '';

        // Separar sucursales con y sin stock
        const sucursalesConStock = p.stock_por_sucursal.filter(s => s.inventario > 0);
        const sucursalesSinStock = p.stock_por_sucursal.filter(s => s.inventario <= 0);

        // Generar filas de sucursal
        const generarFilaSucursal = (suc) => {
            const asignado = asignacionesProducto[suc.id_sucursal] || 0;
            const esOriginal = suc.id_sucursal === p.sucursal_original_id;
            const sinStock = suc.inventario <= 0;
            const estaAsignada = asignado > 0;

            const clases = [
                'sucursal-row',
                sinStock ? 'sin-stock' : '',
                estaAsignada ? 'asignada' : '',
                esOriginal ? 'es-original' : ''
            ].filter(Boolean).join(' ');

            return `
                <div class="${clases}">
                    <span class="status-icon">
                        <i class="bi ${estaAsignada ? 'bi-check-lg' : 'bi-dash'}"></i>
                    </span>
                    <span class="sucursal-name">
                        ${escapeHtml(suc.nombre)}
                    </span>
                    ${esOriginal ? '<span class="badge-original">Original</span>' : ''}
                    <span class="stock-info">
                        ${sinStock 
                            ? 'Sin stock' 
                            : `Stock: <strong>${suc.inventario}</strong>`}
                    </span>
                    <input type="number"
                           class="form-control form-control-sm asignar-cantidad"
                           min="0"
                           value="${asignado}"
                           data-sucursal="${suc.id_sucursal}"
                           data-detalle="${p.detalle_id}"
                           data-max="${suc.inventario}">
                </div>
            `;
        };

        // Construir secciones
        let sucursalesHtml = '';

        if (sucursalesConStock.length > 0) {
            sucursalesHtml += `
                <div class="sucursales-section">
                    <div class="sucursales-section-title">
                        <span class="dot"></span>
                        Sucursales con stock
                    </div>
                    ${sucursalesConStock.map(generarFilaSucursal).join('')}
                </div>
            `;
        }

        if (sucursalesSinStock.length > 0) {
            sucursalesHtml += `
                <div class="sucursales-section">
                    <div class="sucursales-section-title sin-stock">
                        <span class="dot"></span>
                        Sucursales sin stock
                    </div>
                    ${sucursalesSinStock.map(generarFilaSucursal).join('')}
                </div>
            `;
        }

        // El primer producto incompleto se expande automáticamente
        const debeExpandir = !primerIncompletoAsignado && !completo;
        if (debeExpandir) primerIncompletoAsignado = true;

        // Card completo
        html += `
            <div class="reprogram-card ${cardEstado} ${debeExpandir ? 'expandido' : ''}" data-detalle="${p.detalle_id}">
                <div class="reprogram-card-header" onclick="toggleCardReprogramacion(${p.detalle_id})">
                    <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                        <div class="icon-box">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div style="min-width: 0;">
                            <div class="title" title="${escapeHtml(p.nombre)}">
                                ${escapeHtml(p.nombre)}
                                ${badgeReprog}
                            </div>
                        </div>
                    </div>

                    <span class="badge bg-primary" data-requerido="${cantidadReprog}">
                        Requerido: ${cantidadReprog}
                    </span>

                    <div class="header-summary">
                        <div class="mini-progress">
                            <div class="progress-bar ${progresoEstado}" style="width: ${progresoPct}%"></div>
                        </div>
                        <span class="mini-pct ${pctColor}">${progresoPct}%</span>
                        <span class="mini-check"><i class="bi bi-check-lg"></i></span>
                    </div>

                    <i class="bi bi-chevron-down toggle-chevron"></i>
                </div>

                <div class="reprogram-card-context">
                    <div class="item">
                        <i class="bi bi-hash"></i>
                        <span>Cantidad original: <strong>${p.cantidad}</strong></span>
                    </div>
                    ${p.sucursal_original_nombre ? `
                        <div class="item">
                            <i class="bi bi-geo-alt"></i>
                            <span>Sucursal original: <strong>${escapeHtml(p.sucursal_original_nombre)}</strong></span>
                        </div>
                    ` : ''}
                    ${p.es_externo ? `
                        <div class="item">
                            <i class="bi bi-exclamation-circle"></i>
                            <span class="badge bg-warning text-dark">Sobre pedido</span>
                        </div>
                    ` : ''}
                </div>

                <div class="reprogram-card-body">
                    <div class="cantidad-reprogramar-wrapper">
                        <label>Cantidad a reprogramar:</label>
                        <input type="number"
                               class="cantidad-reprogramar"
                               min="1"
                               max="${p.cantidad}"
                               value="${cantidadReprog}"
                               data-detalle="${p.detalle_id}">
                        <span class="hint">Máximo: ${p.cantidad} unidades</span>
                    </div>

                    ${sucursalesHtml}

                    <div class="reprogram-progress ${completo ? 'completo' : ''}">
                        <div class="progress-info">
                            Asignado: <strong class="progress-asignado">${totalAsignado} / ${cantidadReprog}</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar ${progresoEstado}" role="progressbar" style="width: ${progresoPct}%"></div>
                        </div>
                        <div class="progress-pct ${pctColor}">${progresoPct}%</div>
                        <span class="progress-check"><i class="bi bi-check-lg"></i></span>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    recalcularResumenGeneral();

    // Listeners de inputs
    container.querySelectorAll('.asignar-cantidad').forEach(input => {
        input.addEventListener('input', onCambiarAsignacion);
    });

    container.querySelectorAll('.cantidad-reprogramar').forEach(input => {
        input.addEventListener('input', onCambiarCantidadReprogramar);
    });

    // Prevenir propagación del click en inputs y botón remove (evita colapso/expansión accidental)
    container.querySelectorAll('.asignar-cantidad, .cantidad-reprogramar, .remove-btn').forEach(el => {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
}

// ============================================
// EVENTOS DE INPUTS
// ============================================
// ============================================
// EVENTO: CAMBIO DE ASIGNACIÓN POR SUCURSAL
// ============================================
function onCambiarAsignacion(e) {
    const input = e.target;
    const detalleId = parseInt(input.dataset.detalle);
    const sucursalId = parseInt(input.dataset.sucursal);
    const maxPermitido = parseInt(input.dataset.max) || 0;
    let valor = parseInt(input.value) || 0;
    if (valor < 0) valor = 0;

    // --- Validación 1: no exceder el stock de la sucursal (solo advertencia) ---
    if (maxPermitido > 0 && valor > maxPermitido) {
        input.value = maxPermitido;
        valor = maxPermitido;
        if (window.mostrarToast) {
            window.mostrarToast(`No puedes asignar más de ${maxPermitido} unidades en esta sucursal`, 'warning');
        }
    }

    // Guardar valor temporal
    if (!window.reprogramacionState.asignaciones[detalleId]) {
        window.reprogramacionState.asignaciones[detalleId] = {};
    }
    window.reprogramacionState.asignaciones[detalleId][sucursalId] = valor;

    // --- Validación 2: no exceder el total a reprogramar ---
    const cantidadReprog = window.reprogramacionState.cantidades[detalleId] || 0;
    const asigs = window.reprogramacionState.asignaciones[detalleId];
    const totalAsignado = Object.values(asigs).reduce((s, v) => s + v, 0);

    if (totalAsignado > cantidadReprog) {
        const excedente = totalAsignado - cantidadReprog;
        const nuevoValor = Math.max(0, valor - excedente);
        input.value = nuevoValor;
        asigs[sucursalId] = nuevoValor;

        if (window.mostrarToast) {
            const totalFinal = Object.values(asigs).reduce((s, v) => s + v, 0);
            window.mostrarToast(
                `El total asignado (${totalFinal}) no puede exceder el requerido (${cantidadReprog})`,
                'warning'
            );
        }
    }

    actualizarCardProducto(detalleId);
    recalcularResumenGeneral();
}

// ============================================
// EVENTO: CAMBIO DE CANTIDAD A REPROGRAMAR
// ============================================
function onCambiarCantidadReprogramar(e) {
    const input = e.target;
    const detalleId = parseInt(input.dataset.detalle);
    const max = parseInt(input.max) || 1;
    let valor = parseInt(input.value) || 1;
    if (valor < 1) valor = 1;
    if (valor > max) valor = max;
    input.value = valor;

    // --- Validación 3: si se reduce la cantidad, ajustar asignaciones sobrantes ---
    const cantidadAnterior = window.reprogramacionState.cantidades[detalleId] || 0;
    const asigs = window.reprogramacionState.asignaciones[detalleId] || {};
    const totalAsignado = Object.values(asigs).reduce((s, v) => s + v, 0);

    if (totalAsignado > valor) {
        // Hay que reducir. Quitamos del final (por orden de sucursal) hasta cubrir el excedente.
        let excedente = totalAsignado - valor;
        const sucursalesOrdenadas = Object.keys(asigs).sort((a, b) => b - a); // descendente por id (aproximación)

        for (const sucId of sucursalesOrdenadas) {
            if (excedente <= 0) break;
            const actual = asigs[sucId];
            if (actual <= 0) continue;

            const reduccion = Math.min(actual, excedente);
            asigs[sucId] = actual - reduccion;
            excedente -= reduccion;

            // Actualizar el input visual
            const inputSuc = document.querySelector(
                `.asignar-cantidad[data-detalle="${detalleId}"][data-sucursal="${sucId}"]`
            );
            if (inputSuc) inputSuc.value = asigs[sucId];
        }

        if (window.mostrarToast) {
            window.mostrarToast(
                `Se ajustaron las asignaciones para no exceder ${valor} unidades`,
                'warning'
            );
        }
    }

    window.reprogramacionState.cantidades[detalleId] = valor;

    actualizarCardProducto(detalleId);
    recalcularResumenGeneral();
}

// ============================================
// ACTUALIZAR UNA SOLA CARD
// ============================================
function actualizarCardProducto(detalleId) {
    const card = document.querySelector(`.reprogram-card[data-detalle="${detalleId}"]`);
    if (!card) return;

    const cantidadReprog = window.reprogramacionState.cantidades[detalleId] || 0;
    const asignacionesProducto = window.reprogramacionState.asignaciones[detalleId] || {};
    const totalAsignado = Object.values(asignacionesProducto).reduce((s, v) => s + v, 0);

    let progresoEstado = 'bg-danger';
    let progresoPct = 0;
    let cardEstado = 'incompleto';
    let pctColor = 'text-muted';

    if (cantidadReprog > 0) {
        progresoPct = Math.min(100, Math.round((totalAsignado / cantidadReprog) * 100));
    }

    if (totalAsignado > cantidadReprog) {
        progresoEstado = 'bg-primary';
        cardEstado = 'excedido';
        pctColor = 'text-primary';
    } else if (totalAsignado === cantidadReprog && cantidadReprog > 0) {
        progresoEstado = 'bg-success';
        cardEstado = 'completo';
        pctColor = 'text-success';
    } else if (totalAsignado > 0) {
        progresoEstado = 'bg-warning';
        cardEstado = 'incompleto';
        pctColor = 'text-warning';
    }

    const completo = totalAsignado === cantidadReprog && cantidadReprog > 0;

    // Actualizar clase del card
    card.classList.remove('completo', 'incompleto', 'excedido');
    card.classList.add(cardEstado);

    // Actualizar badge "Requerido" (por si cambia la cantidad a reprogramar)
    const badgeRequerido = card.querySelector('.reprogram-card-header .badge.bg-primary');
    if (badgeRequerido) {
        badgeRequerido.dataset.requerido = cantidadReprog;
        badgeRequerido.textContent = `Requerido: ${cantidadReprog}`;
    }

    // Actualizar header-summary (mini barra)
    const headerSummary = card.querySelector('.header-summary');
    if (headerSummary) {
        const miniBar = headerSummary.querySelector('.mini-progress .progress-bar');
        const miniPct = headerSummary.querySelector('.mini-pct');

        if (miniBar) {
            miniBar.className = `progress-bar ${progresoEstado}`;
            miniBar.style.width = `${progresoPct}%`;
        }

        if (miniPct) {
            miniPct.textContent = `${progresoPct}%`;
            miniPct.className = `mini-pct ${pctColor}`;
        }
    }

    // Actualizar info de progreso del body
    const progressInfo = card.querySelector('.reprogram-progress .progress-info .progress-asignado');
    if (progressInfo) {
        progressInfo.textContent = `${totalAsignado} / ${cantidadReprog}`;
    }

    const barra = card.querySelector('.reprogram-progress .progress-bar');
    if (barra) {
        barra.className = `progress-bar ${progresoEstado}`;
        barra.style.width = `${progresoPct}%`;
    }

    const pct = card.querySelector('.reprogram-progress .progress-pct');
    if (pct) {
        pct.textContent = `${progresoPct}%`;
        pct.className = `progress-pct ${pctColor}`;
    }

    // Actualizar estado del progreso completo
    const progressBox = card.querySelector('.reprogram-progress');
    if (progressBox) {
        if (completo) {
            progressBox.classList.add('completo');
        } else {
            progressBox.classList.remove('completo');
        }
    }

    // Actualizar clases de las filas de sucursal
    card.querySelectorAll('.sucursal-row').forEach(row => {
        const input = row.querySelector('.asignar-cantidad');
        const asignado = parseInt(input?.value) || 0;
        const icon = row.querySelector('.status-icon i');

        if (asignado > 0) {
            row.classList.add('asignada');
            if (icon) icon.className = 'bi bi-check-lg';
        } else {
            row.classList.remove('asignada');
            if (icon) icon.className = 'bi bi-dash';
        }
    });

    // Auto-colapsar si está completo
    if (completo) {
        autoColapsarSiCompletoReprogramacion(detalleId);
    }
}

// ============================================
// TOGGLE Y AUTO-COLAPSO - REPROGRAMACIÓN
// ============================================

function toggleCardReprogramacion(detalleId) {
    const card = document.querySelector(`.reprogram-card[data-detalle="${detalleId}"]`);
    if (!card) return;
    card.classList.toggle('expandido');
}

function expandirTodosReprogramacion() {
    document.querySelectorAll('#reprogramar_productos_container .reprogram-card').forEach(card => {
        card.classList.add('expandido');
    });
}

function colapsarTodosReprogramacion() {
    document.querySelectorAll('#reprogramar_productos_container .reprogram-card').forEach(card => {
        card.classList.remove('expandido');
    });
}

function autoColapsarSiCompletoReprogramacion(detalleId) {
    const card = document.querySelector(`.reprogram-card[data-detalle="${detalleId}"]`);
    if (!card) return;

    const completo = card.classList.contains('completo');
    if (!completo) return;

    // No colapsar si el usuario está interactuando con la card
    const focusedElement = document.activeElement;
    if (focusedElement && card.contains(focusedElement)) return;

    setTimeout(() => {
        card.classList.remove('expandido');
    }, 600);
}

// ============================================
// QUITAR PRODUCTO
// ============================================
window.quitarProductoReprogramacion = function(detalleId) {
    window.reprogramacionState.productos = window.reprogramacionState.productos.filter(p => p.detalle_id !== detalleId);
    window.reprogramacionState.seleccionados = window.reprogramacionState.seleccionados.filter(id => id !== detalleId);
    delete window.reprogramacionState.cantidades[detalleId];
    delete window.reprogramacionState.asignaciones[detalleId];

    const card = document.querySelector(`.reprogram-card[data-detalle="${detalleId}"]`);
    if (card) card.remove();

    if (window.reprogramacionState.productos.length === 0) {
        document.getElementById('reprogramar_productos_container').innerHTML =
            '<div class="alert alert-warning text-center">No hay productos seleccionados para reprogramar.</div>';
    }

    recalcularResumenGeneral();
};

// ============================================
// RECALCULAR RESUMEN GENERAL
// ============================================
function recalcularResumenGeneral() {
    const { productos, cantidades, asignaciones } = window.reprogramacionState;

    let productosListos = 0;
    let productosIncompletos = 0;
    let totalUnidades = 0;
    let todosCompletos = productos.length > 0;

    productos.forEach(p => {
        const cantidadReprog = cantidades[p.detalle_id] || 0;
        const asigs = asignaciones[p.detalle_id] || {};
        const totalAsignado = Object.values(asigs).reduce((s, v) => s + v, 0);

        totalUnidades += totalAsignado;

        if (cantidadReprog > 0 && totalAsignado === cantidadReprog) {
            productosListos++;
        } else {
            productosIncompletos++;
            todosCompletos = false;
        }
    });

    const texto = document.getElementById('reprogramar_resumen_texto');
    const totales = document.getElementById('reprogramar_resumen_totales');
    const contenedor = document.getElementById('reprogramar_resumen_general');
    const btn = document.getElementById('btnConfirmarReprogramacion');

    if (texto) {
        if (productos.length === 0) {
            texto.textContent = 'No hay productos seleccionados';
        } else if (todosCompletos) {
            texto.innerHTML = `<strong>${productosListos}</strong> producto${productosListos !== 1 ? 's' : ''} listo${productosListos !== 1 ? 's' : ''} para reprogramar`;
        } else {
            texto.innerHTML = `<strong>${productosListos}</strong> listo${productosListos !== 1 ? 's' : ''} · <strong>${productosIncompletos}</strong> pendiente${productosIncompletos !== 1 ? 's' : ''}`;
        }
    }

    if (totales) {
        totales.innerHTML = `Total asignado: <strong>${totalUnidades}</strong> unidad${totalUnidades !== 1 ? 'es' : ''}`;
    }

    if (contenedor) {
        contenedor.classList.remove('completo', 'incompleto');
        if (productos.length === 0) {
            // sin estado
        } else if (todosCompletos) {
            contenedor.classList.add('completo');
        } else {
            contenedor.classList.add('incompleto');
        }
    }

    if (btn) {
        btn.disabled = !todosCompletos;
        const count = productos.length;
        btn.innerHTML = `<i class="bi bi-check-lg"></i> Confirmar reprogramación${count > 0 ? ` <span class="badge">${count}</span>` : ''}`;
    }

    // Actualizar icono del resumen
    const iconoResumen = contenedor?.querySelector('.resumen-info i');
    if (iconoResumen) {
        if (productos.length === 0) {
            iconoResumen.className = 'bi bi-clipboard';
        } else if (todosCompletos) {
            iconoResumen.className = 'bi bi-clipboard-check-fill';
        } else {
            iconoResumen.className = 'bi bi-clipboard-exclamation';
        }
    }
}

// ============================================
// CONFIRMAR REPROGRAMACIÓN
// ============================================
function confirmarReprogramacion() {
    const motivo = document.getElementById('reprogramar_motivo').value.trim();
    if (!motivo) {
        if (window.mostrarToast) window.mostrarToast('Ingrese el motivo de reprogramación', 'warning');
        return;
    }

    const { pedidoId, productos, cantidades, asignaciones } = window.reprogramacionState;

    if (productos.length === 0) {
        if (window.mostrarToast) window.mostrarToast('No hay productos seleccionados', 'warning');
        return;
    }

    // --- Validación 4: todos los productos deben estar completos ---
    let productosIncompletos = [];
    productos.forEach(p => {
        const cantidadReprog = cantidades[p.detalle_id] || 0;
        const asigs = asignaciones[p.detalle_id] || {};
        const totalAsignado = Object.values(asigs).reduce((s, v) => s + v, 0);

        if (cantidadReprog <= 0 || totalAsignado !== cantidadReprog) {
            productosIncompletos.push(p.nombre || `Detalle ${p.detalle_id}`);
        }
    });

    if (productosIncompletos.length > 0) {
        if (window.mostrarToast) {
            const nombres = productosIncompletos.slice(0, 2).join(', ');
            const sufijo = productosIncompletos.length > 2 ? ` y ${productosIncompletos.length - 2} más` : '';
            window.mostrarToast(
                `Faltan asignar unidades en: ${nombres}${sufijo}`,
                'warning'
            );
        }
        return;
    }

    const productosPayload = [];
    let hayError = false;
    let mensajeError = '';

    productos.forEach(p => {
        const cantidadReprog = cantidades[p.detalle_id] || 0;
        const asigs = asignaciones[p.detalle_id] || {};
        const asignacionesArray = Object.entries(asigs)
            .map(([sucId, cant]) => ({ sucursal_id: parseInt(sucId), cantidad: cant }))
            .filter(a => a.cantidad > 0);

        const totalAsignado = asignacionesArray.reduce((s, a) => s + a.cantidad, 0);

        if (totalAsignado !== cantidadReprog) {
            hayError = true;
            mensajeError = `"${p.nombre}" tiene ${totalAsignado} asignadas pero requiere ${cantidadReprog}.`;
            return;
        }

        productosPayload.push({
            detalle_id: p.detalle_id,
            cantidad_reprogramada: cantidadReprog,
            producto_data: {
                ean: p.codbar,
                nombre: p.nombre,
                cantidad: p.cantidad,
                precio_unitario: p.precio_unitario,
                descuento: p.descuento,
                importe: p.importe,
                id_convenio: p.id_convenio ?? null,
                es_externo: p.es_externo ? 1 : 0,
                id_cotizacion_detalle: p.id_cotizacion_detalle
            },
            asignaciones: asignacionesArray
        });
    });

    if (hayError) {
        if (window.mostrarToast) window.mostrarToast(mensajeError, 'danger');
        return;
    }

    const btn = document.getElementById('btnConfirmarReprogramacion');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    const url = '{{ route("ventas.pedidos.reprogramar-multi") }}';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            pedido_id: pedidoId,
            motivo: motivo,
            productos: productosPayload
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalReprogramarProducto'));
            if (modal) modal.hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al reprogramar', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar reprogramación';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión: ' + error.message, 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar reprogramación';
    });
}

// ============================================
// RESET AL CERRAR MODAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const modalReprogramar = document.getElementById('modalReprogramarProducto');
    if (modalReprogramar) {
        modalReprogramar.addEventListener('hidden.bs.modal', function() {
            window.reprogramacionState = {
                pedidoId: null,
                productos: [],
                seleccionados: [],
                cantidades: {},
                asignaciones: {},
            };
            const btn = document.getElementById('btnConfirmarReprogramacion');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmar reprogramación';
            }
        });
    }
});

// Resetear modo cuando se cierra el modal de edición
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
            
            // Normalizar num_familia: quitar ceros a la izquierda y comparar como string
            const normalizarFamilia = (f) => String(f ?? '').replace(/^0+/, '') || '0';
            
            if (convenioId && editCatalogos.convenios) {
                const convenio = editCatalogos.convenios.find(c => c.id == convenioId);
                if (convenio && convenio.familias) {
                    editArticulosSeleccionados.forEach(articulo => {
                        const familiaConDescuento = convenio.familias.find(f => 
                            normalizarFamilia(f.num_familia) === normalizarFamilia(articulo.num_familia)
                        );
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