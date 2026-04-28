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
                        <label class="text-muted small">Cotización de origen</label>
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
                    {{-- 
                    <div class="col-md-6">
                        <label class="text-muted small">Sucursal asignada (CRM)</label>
                        <p id="ver_sucursal_asignada">-</p>
                    </div>
                    --}}
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
                <div class="mt-4 mb-3" id="ver_sucursales_section">
                    <h6 class="mb-3"><i class="bi bi-house-check"></i>Estado por sucursal</h6>
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
                                    <th class="text-end">Importe</th>
                                    <th>Sucursal surtido</th>
                                </tr>
                            </thead>
                            <tbody id="ver_productos_body">
                                <tr><td colspan="7" class="text-center py-4">Cargando...</td></tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold" id="ver_total">$0.00</td>
                                    <td colspan="1"></td>
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
// ============================================
// FUNCIÓN ESCAPE HTML
// ============================================
function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

let pedidoDataActual = null;
let pedidoSucursalIdActual = null;

function cargarDatosVerPedido(data) {
    pedidoDataActual = data;
    
    document.getElementById('ver_folio_pedido').textContent = data.folio_pedido || '-';
    document.getElementById('ver_folio_cotizacion').innerHTML = data.cotizacion?.folio ? 
        `<span class="badge bg-secondary">${data.cotizacion.folio}</span>` : '-';
    document.getElementById('ver_fecha_pedido').textContent = data.fecha_pedido ? new Date(data.fecha_pedido).toLocaleString() : '-';
    
    // Status según tipo de usuario
    const sucursalUsuarioVer = data.sucursal_usuario || 0;
    let statusTexto = '';
    let statusColorLocal = '';

    if (sucursalUsuarioVer === 0) {
        // Usuario CRM: ver status general del pedido
        if (data.status == 2) {
            const sucursalesPendientes = data.sucursales?.some(s => s.status === false);
            const todasSucursalesListas = data.sucursales?.length > 0 && !sucursalesPendientes;
            
            if (todasSucursalesListas && !data.id_repartidor) {
                statusTexto = 'Sucursales listas - Esperando repartidor';
                statusColorLocal = 'info';
            } else if (data.id_repartidor) {
                statusTexto = 'Repartidor asignado';
                statusColorLocal = 'primary';
            } else {
                statusTexto = 'En proceso';
                statusColorLocal = 'warning';
            }
        } else if (data.status == 3) {
            statusTexto = 'Finalizado';
            statusColorLocal = 'success';
        } else if (data.status == 1) {
            statusTexto = 'Cancelado';
            statusColorLocal = 'danger';
        } else {
            statusTexto = 'Desconocido';
            statusColorLocal = 'secondary';
        }
    } else {
        // Usuario de sucursal: ver status de su sucursal
        const miSucursal = data.sucursales?.find(s => s.id_sucursal == sucursalUsuarioVer);
        if (miSucursal) {
            if (miSucursal.status === true || miSucursal.status == 1) {
                statusTexto = 'Despachado';
                statusColorLocal = 'success';
            } else {
                statusTexto = 'Pendiente';
                statusColorLocal = 'warning';
            }
        } else {
            statusTexto = 'Sin asignar';
            statusColorLocal = 'secondary';
        }
    }

    const statusBadge = document.getElementById('ver_status_badge');
    statusBadge.textContent = statusTexto;
    statusBadge.className = `badge bg-${statusColorLocal}`;
    
    let nombreCliente = '-';
    let fechaEntregaTexto = 'Pendiente';
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
    `${data.repartidor.Nombre} ${data.repartidor.apPaterno || ''} ${data.repartidor.apMaterno || ''}` : 
    '<span class="text-muted">Sin asignar</span>';
    //document.getElementById('ver_sucursal_asignada').textContent = data.cotizacion?.sucursal_asignada?.nombre || 'No asignada';
    if (data.fecha_entrega_real) {
    fechaEntregaTexto = new Date(data.fecha_entrega_real).toLocaleString();
    } else if (data.fecha_entrega_sugerida) {
        const fecha = new Date(data.fecha_entrega_sugerida);
        const dia = fecha.getDate();
        const mes = fecha.getMonth() + 1;
        const anio = fecha.getFullYear();
        let hora = '';
        if (data.hora_entrega_sugerida) {
            hora = ` ${data.hora_entrega_sugerida.substring(0, 5)}`;
        }
        fechaEntregaTexto = `${dia}/${mes}/${anio}${hora}`;
    }
    document.getElementById('ver_fecha_entrega').textContent = fechaEntregaTexto;
    
        // Mostrar comentarios de cotización y pedido
    let comentariosTexto = '';
    if (data.cotizacion?.comentarios) {
        comentariosTexto += `Cotización: ${data.cotizacion.comentarios}`;
    }
    if (data.comentarios) {
        if (comentariosTexto) comentariosTexto += '\n';
        comentariosTexto += `Pedido: ${data.comentarios}`;
    }
    document.getElementById('ver_comentarios').textContent = comentariosTexto || 'Sin comentarios';
    
    // Estado por sucursal
    const sucursalUsuario = Number(data.sucursal_usuario || 0);

    // Mostrar/ocultar la sección completa (solo visible para CRM)
    const sucursalesSection = document.getElementById('ver_sucursales_section');
    if (sucursalesSection) {
        sucursalesSection.style.display = sucursalUsuario === 0 ? 'block' : 'none';
    }

    const sucursalesContainer = document.getElementById('ver_sucursales_status');
    let sucursalesHtml = '';

    // Recorrer sucursales para asignar pedidoSucursalIdActual y generar HTML
    if (data.sucursales && data.sucursales.length) {
        data.sucursales.forEach(suc => {
            // ASIGNAR pedidoSucursalIdActual (convertir a número)
            if (suc.id_pedido_sucursal && suc.status === false && sucursalUsuario === Number(suc.id_sucursal)) {
                pedidoSucursalIdActual = suc.id_pedido_sucursal;
                console.log('pedidoSucursalIdActual asignado:', pedidoSucursalIdActual);
            }
            
            // Generar HTML solo para CRM
            if (sucursalUsuario === 0) {
                const statusText = suc.status ? 'Listo' : 'Pendiente';
                const statusClass = suc.status ? 'success' : 'warning';
                sucursalesHtml += `<span class="badge bg-${statusClass} p-2">
                                    ${suc.sucursal?.nombre || 'Sucursal'} - ${statusText}
                                </span>`;
            }
        });
    }

    if (sucursalUsuario === 0) {
        sucursalesContainer.innerHTML = sucursalesHtml || '<span class="text-muted">No hay sucursales asignadas a este pedido</span>';
    } else {
        sucursalesContainer.innerHTML = '';
    }
    
    // Botón marcar listo
    const btnMarcarListo = document.getElementById('btnMarcarListo');
    console.log('Usuario puede marcar listo:', data.usuario_puede_marcar_listo);
    console.log('Pedido sucursal ID actual:', pedidoSucursalIdActual);

    if (data.usuario_puede_marcar_listo && pedidoSucursalIdActual) {
        btnMarcarListo.style.display = 'inline-block';
    } else {
        btnMarcarListo.style.display = 'none';
    }
    
    // ============================================
    // PRODUCTOS - PRIORIZAR detalles_procesados
    // ============================================
    const tbodyProductos = document.getElementById('ver_productos_body');
    let total = 0;
    
    // Determinar qué detalles usar (priorizar los procesados del servidor)
    const detallesMostrar = data.detalles_procesados || data.cotizacion?.detalles || [];
    
    if (detallesMostrar.length === 0) {
        tbodyProductos.innerHTML = '<tr><td colspan="7" class="text-center py-4">No hay productos registrados</td><tr>';
    } else {
        let html = '';
        detallesMostrar.forEach((detalle, index) => {
            const importe = parseFloat(detalle.importe || 0);
            total += importe;
            const esExterno = detalle.es_externo == 1;
            
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
                    <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                    <td>${detalle.sucursal_surtido?.nombre || (esExterno ? 'Pedido a proveedor' : 'No asignada')}</td>
                </tr>
            `;
        });
        tbodyProductos.innerHTML = html;
    }
    document.getElementById('ver_total').textContent = `$${total.toFixed(2)}`;
}

function marcarListoSucursal() {
    if (!pedidoSucursalIdActual) {
        if (window.mostrarToast) window.mostrarToast('No se pudo identificar la sucursal', 'danger');
        return;
    }
    
    window.confirmarEliminar('marcar_listo', pedidoSucursalIdActual, 'esta sucursal', function() {
        ejecutarMarcarListo();
    });
}

function ejecutarMarcarListo() {
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