<!-- Modal Ver Cotización -->
<div class="modal fade" id="modalVerCotizacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text"></i> Detalle de Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="cotizacionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                            <i class="bi bi-info-circle"></i> Información
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="versiones-tab" data-bs-toggle="tab" data-bs-target="#versiones" type="button" role="tab">
                            <i class="bi bi-files"></i> Historial de versiones
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab Información (incluye productos) -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="text-muted small">Folio</label>
                                <p class="fw-bold" id="ver_folio">-</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Versión</label>
                                <p><span class="badge bg-secondary" id="ver_version">-</span></p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Fecha creación</label>
                                <p id="ver_fecha_creacion">-</p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Fecha entrega sugerida</label>
                                <p id="ver_fecha_entrega">-</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="text-muted small">Cliente</label>
                                <p class="fw-bold" id="ver_cliente">-</p>
                                <small class="text-muted" id="ver_cliente_email">-</small>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Sucursal asignada</label>
                                <p id="ver_sucursal">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Certeza</label>
                                <p><span id="ver_certeza_badge" class="badge">-</span></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="text-muted small">Fase</label>
                                <p><span id="ver_fase_badge" class="badge">-</span></p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Clasificación</label>
                                <p id="ver_clasificacion">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Enviada</label>
                                <p id="ver_enviado_badge"><span class="badge bg-secondary">No</span></p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">Comentarios</label>
                            <p id="ver_comentarios" class="p-2 bg-light rounded">-</p>
                        </div>

                        <!-- Productos dentro de la pestaña Información -->
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
                                            <th>Convenio</th>
                                        </thead>
                                    <tbody id="ver_articulos_body">
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

                    <!-- Tab Historial de versiones -->
                    <div class="tab-pane fade" id="versiones" role="tabpanel">
                        <div id="ver_versiones_container">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDatosVerCotizacion(data) {
    // Información básica
    document.getElementById('ver_folio').textContent = data.folio || '-';
    document.getElementById('ver_version').textContent = data.version || 1;
    document.getElementById('ver_fecha_creacion').textContent = data.fecha_creacion ? new Date(data.fecha_creacion).toLocaleString() : '-';
    document.getElementById('ver_fecha_entrega').textContent = data.fecha_entrega_sugerida ? new Date(data.fecha_entrega_sugerida).toLocaleDateString() : '-';
    
    // Cliente
    let nombreCompleto = '-';
    if (data.cliente) {
        const partes = [];
        if (data.cliente.Nombre) partes.push(data.cliente.Nombre);
        if (data.cliente.apPaterno) partes.push(data.cliente.apPaterno);
        if (data.cliente.apMaterno) partes.push(data.cliente.apMaterno);
        nombreCompleto = partes.join(' ') || data.cliente.nombre_completo || '-';
    }
    document.getElementById('ver_cliente').textContent = nombreCompleto;
    document.getElementById('ver_cliente_email').textContent = data.cliente?.email1 || '-';
    document.getElementById('ver_sucursal').textContent = data.sucursal_asignada?.nombre || 'No asignada';
    document.getElementById('ver_comentarios').textContent = data.comentarios || 'Sin comentarios';
    document.getElementById('ver_clasificacion').textContent = data.clasificacion?.clasificacion || '-';
    
    // Certeza
    const certezaMap = {1: 'Baja', 2: 'Media', 3: 'Alta'};
    const certezaColor = {1: 'secondary', 2: 'warning', 3: 'success'};
    const certezaTexto = certezaMap[data.certeza] || 'N/A';
    const certezaBadge = document.getElementById('ver_certeza_badge');
    certezaBadge.textContent = certezaTexto;
    certezaBadge.className = `badge bg-${certezaColor[data.certeza] || 'secondary'}`;
    
    // Fase
    const faseNombre = data.fase?.fase || '-';
    const faseBadge = document.getElementById('ver_fase_badge');
    faseBadge.textContent = faseNombre;
    let faseClass = 'bg-secondary';
    if (faseNombre === 'En proceso') faseClass = 'bg-warning';
    else if (faseNombre === 'Completada') faseClass = 'bg-success';
    else if (faseNombre === 'Cancelada') faseClass = 'bg-danger';
    faseBadge.className = `badge ${faseClass}`;
    
    // Enviado
    const enviadoBadge = document.getElementById('ver_enviado_badge');
    if (data.enviado) {
        enviadoBadge.innerHTML = '<span class="badge bg-success"><i class="bi bi-envelope-check"></i> Enviada el ' + 
            (data.fecha_envio ? new Date(data.fecha_envio).toLocaleString() : '-') + '</span>';
    } else {
        enviadoBadge.innerHTML = '<span class="badge bg-secondary">No enviada</span>';
    }
    
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
                    <td>${detalle.sucursal_surtido?.nombre || (detalle.id_sucursal_surtido ? 'Pendiente' : 'No asignada')}</td>
                    <td>${detalle.convenio?.nombre || 'No aplica'}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    document.getElementById('ver_total').textContent = `$${total.toFixed(2)}`;
    
    // Cargar historial de versiones
    cargarHistorialVersiones(data.id_cotizacion);
}

function cargarHistorialVersiones(cotizacionId) {
    const container = document.getElementById('ver_versiones_container');
    
    fetch(`/ventas/cotizaciones/${cotizacionId}/versiones`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            let html = `
                <div class="list-group">
                    <div class="list-group-item list-group-item-light">
                        <strong>Historial de versiones</strong>
                        <small class="text-muted float-end">${data.data.length} versiones</small>
                    </div>
            `;
            
            data.data.forEach(version => {
                const esActual = version.activo === 1;
                const badgeClass = esActual ? 'bg-success' : 'bg-secondary';
                const badgeText = esActual ? 'Activa' : 'Cancelada';
                
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge ${badgeClass} me-2">${badgeText}</span>
                                <strong>Versión ${version.version}</strong>
                                <br><small class="text-muted">Folio: ${version.folio}</small>
                                ${version.enviado ? '<br><small class="text-primary"><i class="bi bi-envelope-check"></i> Enviada</small>' : ''}
                            </div>
                            <div class="text-end">
                                <small>${version.fecha_creacion ? new Date(version.fecha_creacion).toLocaleString() : '-'}</small>
                                <br><small class="text-muted">Certeza: ${version.certeza_nombre || version.certeza}</small>
                            </div>
                        </div>
                        ${version.comentarios ? `<div class="mt-2 small text-muted">${version.comentarios.substring(0, 100)}${version.comentarios.length > 100 ? '...' : ''}</div>` : ''}
                    </div>
                `;
            });
            
            html += `</div>`;
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay versiones anteriores de esta cotización.
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error cargando versiones:', error);
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> No se pudo cargar el historial de versiones.
            </div>
        `;
    });
}
</script>