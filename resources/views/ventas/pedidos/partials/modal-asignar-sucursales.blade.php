<div class="modal fade" id="modalAsignarSucursales" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-building"></i> Asignar Sucursales - Pedido <span id="asignar_sucursales_folio"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <h6>Productos sin sucursal asignada</h6>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr><th>Producto</th><th>Cantidad</th><th>Acción</th></tr>
                                </thead>
                                <tbody id="productos_sin_asignar"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6>Stock por sucursal</h6>
                        <div id="stock_sucursales_container"></div>
                    </div>
                </div>
                <input type="hidden" id="asignar_sucursales_pedido_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarAsignacionSucursales()">
                    <i class="bi bi-save"></i> Guardar asignaciones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let asignacionesPendientes = {};

function cargarModalAsignarSucursales(pedidoId, folio) {
    document.getElementById('asignar_sucursales_pedido_id').value = pedidoId;
    document.getElementById('asignar_sucursales_folio').textContent = folio;
    asignacionesPendientes = {};
    
    fetch(`/ventas/pedidos/${pedidoId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const sinAsignar = data.data.cotizacion.detalles.filter(d => d.es_externo == 0 && !d.id_sucursal_surtido);
            const tbody = document.getElementById('productos_sin_asignar');
            
            if (sinAsignar.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center">Todos los productos ya tienen sucursal asignada</td></tr>';
            } else {
                let html = '';
                sinAsignar.forEach(detalle => {
                    html += `
                        <tr>
                            <td><small>${escapeHtml(detalle.descripcion)}</small></td>
                            <td class="text-center">${detalle.cantidad}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary" onclick="mostrarStockSucursales(${detalle.id_detalle}, '${escapeHtml(detalle.descripcion)}', ${detalle.cantidad})">
                                    <i class="bi bi-building"></i> Asignar
                                </button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }
        }
    });
}

function mostrarStockSucursales(detalleId, descripcion, cantidadRequerida) {
    // Obtener el productoId del detalle
    fetch(`/ventas/productos/stock-por-sucursal/0?detalle_id=${detalleId}`)
        .then(r => r.json())
        .then(data => {
            // Implementar lógica de asignación
        });
}
</script>