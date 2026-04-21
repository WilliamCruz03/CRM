<div class="modal fade" id="modalVerPedido" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-truck"></i> Detalle de Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="text-muted small">Folio Pedido</label>
                        <p class="fw-bold" id="ver_folio_pedido">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Cotización</label>
                        <p id="ver_folio_cotizacion">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Fecha pedido</label>
                        <p id="ver_fecha_pedido">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Status</label>
                        <p><span id="ver_status_badge" class="badge">-</span></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="text-muted small">Cliente</label>
                        <p class="fw-bold" id="ver_cliente">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Contacto</label>
                        <p id="ver_contacto">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Repartidor</label>
                        <p id="ver_repartidor">-</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Sucursal asignada (CRM)</label>
                        <p id="ver_sucursal_asignada">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Fecha entrega</label>
                        <p id="ver_fecha_entrega">-</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Comentarios</label>
                    <p id="ver_comentarios" class="p-2 bg-light rounded">-</p>
                </div>

                <!-- Estado por sucursal -->
                <div class="mt-4 mb-3">
                    <h6 class="mb-3">Estado por sucursal</h6>
                    <div id="ver_sucursales_status" class="d-flex flex-wrap gap-2"></div>
                </div>

                <!-- Productos -->
                <div class="mt-4">
                    <h6 class="mb-3">Productos</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Descuento</th>
                                    <th class="text-end">Importe</th>
                                    <th>Sucursal surtido</th>
                                    <th>Stock</th>
                                </thead>
                            <tbody id="ver_productos_body">
                                <tr><td colspan="9" class="text-center py-4">Cargando...</td></tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold" id="ver_total">$0.00</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="btnMarcarListo" style="display: none;" onclick="marcarListoSucursal()">
                    <i class="bi bi-check-circle"></i> Marcar como listo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let pedidoDataActual = null;
let pedidoSucursalIdActual = null;

function cargarDatosVerPedido(data) {
    pedidoDataActual = data;
    
    document.getElementById('ver_folio_pedido').textContent = data.folio_pedido || '-';
    document.getElementById('ver_folio_cotizacion').innerHTML = data.cotizacion?.folio ? 
        `<span class="badge bg-secondary">${data.cotizacion.folio}</span>` : '-';
    document.getElementById('ver_fecha_pedido').textContent = data.fecha_pedido ? new Date(data.fecha_pedido).toLocaleString() : '-';
    
    const statusMap = {1: 'Cancelado', 2: 'En proceso', 3: 'Finalizado'};
    const statusColor = {1: 'danger', 2: 'warning', 3: 'success'};
    const statusBadge = document.getElementById('ver_status_badge');
    statusBadge.textContent = statusMap[data.status] || 'Desconocido';
    statusBadge.className = `badge bg-${statusColor[data.status] || 'secondary'}`;
    
    let nombreCliente = '-';
    let contactosArray = [];
    if (data.cotizacion?.cliente) {
        const c = data.cotizacion.cliente;
        const partes = [c.Nombre, c.apPaterno, c.apMaterno].filter(Boolean);
        nombreCliente = partes.join(' ') || '-';
        if (c.telefono1) contactosArray.push(`<i class="bi bi-telephone"></i> ${escapeHtml(c.telefono1)}`);
        if (c.telefono2) contactosArray.push(`<i class="bi bi-telephone"></i> ${escapeHtml(c.telefono2)} (secundario)`);
        if (c.email1) contactosArray.push(`<i class="bi bi-envelope"></i> ${escapeHtml(c.email1)}`);
    }
    document.getElementById('ver_cliente').textContent = nombreCliente;
    document.getElementById('ver_contacto').innerHTML = contactosArray.length ? contactosArray.join('<br>') : '-';
    
    document.getElementById('ver_repartidor').innerHTML = data.repartidor ? 
        `${data.repartidor.Nombre} ${data.repartidor.apPaterno}` : '<span class="text-muted">Sin asignar</span>';
    document.getElementById('ver_sucursal_asignada').textContent = data.cotizacion?.sucursal_asignada?.nombre || 'No asignada';
    document.getElementById('ver_fecha_entrega').textContent = data.fecha_entrega_real ? 
        new Date(data.fecha_entrega_real).toLocaleString() : (data.fecha_entrega_sugerida || 'Pendiente');
    document.getElementById('ver_comentarios').textContent = data.comentarios || 'Sin comentarios';
    
    // Sucursales status
    const sucursalesContainer = document.getElementById('ver_sucursales_status');
    if (data.sucursales && data.sucursales.length) {
        let html = '';
        data.sucursales.forEach(suc => {
            const statusText = suc.status ? 'Listo' : 'Pendiente';
            const statusClass = suc.status ? 'success' : 'warning';
            html += `<span class="badge bg-${statusClass} p-2">
                        ${suc.sucursal?.nombre || 'Sucursal'} - ${statusText}
                    </span>`;
            if (suc.id_pedido_sucursal && suc.status === 0 && data.sucursal_usuario === suc.id_sucursal) {
                pedidoSucursalIdActual = suc.id_pedido_sucursal;
            }
        });
        sucursalesContainer.innerHTML = html;
    } else {
        sucursalesContainer.innerHTML = '<span class="text-muted">Sin sucursales asignadas</span>';
    }
    
    // Botón marcar listo
    const btnMarcarListo = document.getElementById('btnMarcarListo');
    if (data.usuario_puede_marcar_listo && pedidoSucursalIdActual) {
        btnMarcarListo.style.display = 'inline-block';
    } else {
        btnMarcarListo.style.display = 'none';
    }
    
    // Productos
    const tbody = document.getElementById('ver_productos_body');
    let total = 0;
    
    if (!data.cotizacion?.detalles || data.cotizacion.detalles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4">No hay productos registrados</td></tr>';
    } else {
        let html = '';
        data.cotizacion.detalles.forEach((detalle, index) => {
            const importe = parseFloat(detalle.importe || 0);
            total += importe;
            const esExterno = detalle.es_externo == 1;
            const stockBadge = detalle.stock_actual !== null ? 
                (detalle.stock_actual >= detalle.cantidad ? 
                    `<span class="badge bg-success">Stock: ${detalle.stock_actual}</span>` : 
                    `<span class="badge bg-danger">Stock insuficiente: ${detalle.stock_actual}</span>`) : 
                (esExterno ? '<span class="badge bg-info">Sobre pedido</span>' : '<span class="badge bg-secondary">Sin stock</span>');
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><small>${escapeHtml(detalle.codbar || '-')}</small></td>
                    <td>
                        ${escapeHtml(detalle.descripcion || '-')}
                        ${esExterno ? '<br><span class="badge bg-info mt-1">Requiere pedido a proveedor</span>' : ''}
                    </td>
                    <td class="text-center">${detalle.cantidad || 0}</td>
                    <td class="text-end">$${parseFloat(detalle.precio_unitario || 0).toFixed(2)}</td>
                    <td class="text-end">${detalle.descuento > 0 ? detalle.descuento + '%' : '-'}</td>
                    <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                    <td>${detalle.sucursal_surtido?.nombre || (esExterno ? 'Pedido a proveedor' : 'No asignada')}</td>
                    <td class="text-center">${stockBadge}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    document.getElementById('ver_total').textContent = `$${total.toFixed(2)}`;
}

function marcarListoSucursal() {
    if (!pedidoSucursalIdActual) {
        if (window.mostrarToast) window.mostrarToast('No se pudo identificar la sucursal', 'danger');
        return;
    }
    
    fetch(`/ventas/pedidos/sucursal/${pedidoSucursalIdActual}/marcar-listo`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalVerPedido'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
}
</script>