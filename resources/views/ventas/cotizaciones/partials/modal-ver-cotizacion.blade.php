<!-- Modal Ver Cotización -->
<div class="modal fade" id="modalVerCotizacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text"></i> Detalle de Cotización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Info básica -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="text-muted small">Folio</label>
                        <p class="fw-bold" id="ver_folio">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Fecha</label>
                        <p id="ver_fecha">-</p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Fase</label>
                        <p><span id="ver_fase_badge" class="badge">-</span></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Clasificación</label>
                        <p id="ver_clasificacion">-</p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Cliente</label>
                        <p class="fw-bold" id="ver_cliente">-</p>
                        <small class="text-muted" id="ver_cliente_email">-</small>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Sucursal asignada</label>
                        <p id="ver_sucursal">-</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="text-muted small">Comentarios</label>
                    <p id="ver_comentarios" class="p-2 bg-light rounded">-</p>
                </div>
                
                <!-- Tabla de artículos -->
                <h6 class="mb-3">Artículos</h6>
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
                            </thead>
                            <tbody id="ver_articulos_body">
                                <tr>
                                    <td colspan="7" class="text-center py-4">Cargando...</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold" id="ver_total">$0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDatosVerCotizacion(data) {
    document.getElementById('ver_folio').textContent = data.folio || '-';
    document.getElementById('ver_fecha').textContent = data.fecha_creacion ? new Date(data.fecha_creacion).toLocaleString() : '-';
    document.getElementById('ver_cliente').textContent = data.cliente?.nombre_completo || data.cliente?.Nombre || '-';
    document.getElementById('ver_cliente_email').textContent = data.cliente?.email1 || '-';
    document.getElementById('ver_sucursal').textContent = data.sucursal_asignada?.nombre || 'No asignada';
    document.getElementById('ver_comentarios').textContent = data.comentarios || 'Sin comentarios';
    document.getElementById('ver_clasificacion').textContent = data.clasificacion?.clasificacion || '-';
    
    // Badge de fase
    const faseBadge = document.getElementById('ver_fase_badge');
    const faseNombre = data.fase?.fase || '-';
    faseBadge.textContent = faseNombre;
    
    let faseClass = 'bg-secondary';
    if (faseNombre === 'En proceso') faseClass = 'bg-warning';
    else if (faseNombre === 'Completada') faseClass = 'bg-success';
    else if (faseNombre === 'Cancelada') faseClass = 'bg-danger';
    faseBadge.className = `badge ${faseClass}`;
    
    // Artículos
    const tbody = document.getElementById('ver_articulos_body');
    let total = 0;
    
    if (!data.detalles || data.detalles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4">No hay artículos registrados</td></tr>';
    } else {
        let html = '';
        data.detalles.forEach((detalle, index) => {
            const importe = parseFloat(detalle.importe || 0);
            total += importe;
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${detalle.codbar || '-'}</td>
                    <td>${detalle.descripcion || '-'}</td>
                    <td class="text-center">${detalle.cantidad || 0}</td>
                    <td class="text-end">$${parseFloat(detalle.precio_unitario || 0).toFixed(2)}</td>
                    <td class="text-end">${detalle.descuento > 0 ? detalle.descuento + '%' : '-'}</td>
                    <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                    <td class="text-center">${detalle.convenio?.nombre || 'No aplica'}</td>
                    <td class="text-center">${detalle.sucursal_surtido?.nombre || (detalle.id_sucursal_surtido ? 'Pendiente' : 'No asignada')}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    
    document.getElementById('ver_total').textContent = `$${total.toFixed(2)}`;
}
</script>