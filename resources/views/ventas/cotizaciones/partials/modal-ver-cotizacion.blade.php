<!-- Modal Ver Cotización -->
<div class="modal fade" id="modalVerCotizacion" tabindex="-1">
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
                        </div>
                        
                        <!-- Quien creo o modifico la cotización -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <small class="text-muted">Creado por:</small>
                                <p class="mb-0 fw-bold" id="detalle_creado_por">-</p>
                                <small class="text-muted" id="detalle_fecha_creacion_text">-</small>  <!-- CAMBIADO EL ID -->
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Última modificación:</small>
                                <p class="mb-0 fw-bold" id="detalle_modificado_por">-</p>
                                <small class="text-muted" id="detalle_fecha_modificacion">-</small>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Sucursal asignada</label>
                                <p id="ver_sucursal">-</p>
                            </div>
                        </div>
                        <!-- Datos del cliente -->
                        <div class="row mb-4">
                        <label class="text-muted"><b>Datos del Cliente/Prospecto</b></label>
                            <div class="col-md-4">
                                <label class="text-muted small">Nombre</label>
                                <p class="fw-bold" id="ver_cliente">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Contacto</label>
                                <p id="ver_contacto">-</p>
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
                                            <th>Convenio</th>
                                        </thead>
                                    <tbody id="ver_articulos_body">
                                        <tr><td colspan="9" class="text-center py-4">Cargando...</td></tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="ver_total">$0.00</td>
                                            <td colspan="1"></td>
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

<style>
    /* Estilos para el acordeón de versiones */
    .accordion-button:not(.collapsed) {
        background-color: #e8f4f8;
        color: #2c3e50;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    
    .accordion-item {
        margin-bottom: 8px;
        border: 1px solid rgba(0,0,0,.125);
        border-radius: 6px;
        overflow: hidden;
    }
    
    .accordion-button .badge {
        font-size: 11px;
    }
</style>

<script>
function cargarDatosVerCotizacion(data) {
    // Información básica
    document.getElementById('ver_folio').textContent = data.folio || '-';
    document.getElementById('ver_version').textContent = data.version || 1;
    document.getElementById('ver_fecha_creacion').textContent = data.fecha_creacion ? new Date(data.fecha_creacion).toLocaleString() : '-';
    
    // Creado por
    document.getElementById('detalle_creado_por').innerHTML = 
        `${data.creador?.Nombre || 'N/A'} ${data.creador?.ApPaterno || ''}`.trim() || 'Sistema';
    document.getElementById('detalle_fecha_creacion_text').innerHTML = 
        data.fecha_creacion ? new Date(data.fecha_creacion).toLocaleString() : '-';
    
    // Modificado por
    document.getElementById('detalle_modificado_por').innerHTML = 
        `${data.modificador?.Nombre || 'N/A'} ${data.modificador?.ApPaterno || ''}`.trim() || 'Sin modificaciones';
    document.getElementById('detalle_fecha_modificacion').innerHTML = 
        data.fecha_ultima_modificacion ? new Date(data.fecha_ultima_modificacion).toLocaleString() : '-';
    
    // Cliente y contacto - Mostrar TODOS los contactos disponibles
    let nombreCompleto = '-';
    let contactosArray = [];

    if (data.cliente) {
        const partes = [];
        if (data.cliente.Nombre) partes.push(data.cliente.Nombre);
        if (data.cliente.apPaterno) partes.push(data.cliente.apPaterno);
        if (data.cliente.apMaterno) partes.push(data.cliente.apMaterno);
        nombreCompleto = partes.join(' ') || data.cliente.nombre_completo || '-';
        
        // Recopilar TODOS los contactos disponibles
        if (data.cliente.telefono1 && data.cliente.telefono1.trim() !== '') {
            contactosArray.push(`<i class="bi bi-telephone"></i> ${escapeHtml(data.cliente.telefono1)}`);
        }
        if (data.cliente.telefono2 && data.cliente.telefono2.trim() !== '') {
            contactosArray.push(`<i class="bi bi-telephone"></i> ${escapeHtml(data.cliente.telefono2)} <span class="text-muted">(secundario)</span>`);
        }
        if (data.cliente.email1 && data.cliente.email1.trim() !== '') {
            contactosArray.push(`<i class="bi bi-envelope"></i> ${escapeHtml(data.cliente.email1)}`);
        }
    }

    let contactoHtml = contactosArray.length > 0 ? contactosArray.join('<br>') : '<span class="text-muted">Sin contacto</span>';

    document.getElementById('ver_cliente').textContent = nombreCompleto;
    document.getElementById('ver_contacto').innerHTML = contactoHtml;
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
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No hay artículos registrados</td</tr>';
    } else {
        let html = '';
        data.detalles.forEach((detalle, index) => {
            const importe = parseFloat(detalle.importe || 0);
            total += importe;
            
            // Determinar si es producto externo por el código (empieza con T)
            const esExterno = detalle.codbar && detalle.codbar.startsWith('T');
            
            // Obtener código y descripción
            let codigo = detalle.codbar || '-';
            let descripcion = detalle.descripcion || '-';
            
            // Badge para productos externos
            const badgeExterno = esExterno ? '<br><span class="badge bg-info mt-1">Sobre Pedido</span>' : '';
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(codigo)}</td>
                    <td>
                        ${escapeHtml(descripcion)}
                        ${badgeExterno}
                    </td>
                    <td class="text-center">${detalle.cantidad || 0}</td>
                    <td class="text-end">$${parseFloat(detalle.precio_unitario || 0).toFixed(2)}</td>
                    <td class="text-end">${detalle.descuento > 0 ? detalle.descuento + '%' : '-'}</td>
                    <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                    <td>${detalle.convenio?.nombre || 'No aplica'}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    document.getElementById('ver_total').textContent = `$${total.toFixed(2)}`;
    
    // Cargar historial de versiones
    if (typeof cargarHistorialVersiones === 'function') {
        cargarHistorialVersiones(data.id_cotizacion);
    }
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
                <div class="accordion" id="versionesAccordion">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> 
                        <small>Versiones anteriores de esta cotización (${data.data.length} versiones)</small>
                    </div>
            `;
            
            data.data.forEach((version, index) => {
                const accordionId = `version-${version.id_cotizacion}`;
                const isFirst = index === 0;
                const totalProductos = version.detalles ? version.detalles.length : 0;
                
                html += `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-${accordionId}">
                            <button class="accordion-button ${!isFirst ? 'collapsed' : ''}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse-${accordionId}" 
                                    aria-expanded="${isFirst ? 'true' : 'false'}" 
                                    aria-controls="collapse-${accordionId}">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div>
                                        <span class="badge bg-secondary me-2">Cancelada</span>
                                        <strong>Versión ${version.version}</strong>
                                        <br><small class="text-muted">Folio: ${version.folio}</small>
                                    </div>
                                    <div class="text-end">
                                        <div>
                                            <small class="text-muted">Total: $${parseFloat(version.importe_total || 0).toFixed(2)}</small>
                                            <br><small class="text-muted">${totalProductos} producto${totalProductos !== 1 ? 's' : ''}</small>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse-${accordionId}" 
                             class="accordion-collapse collapse ${isFirst ? 'show' : ''}" 
                             data-bs-parent="#versionesAccordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Código</th>
                                                <th>Descripción</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-end">Precio</th>
                                                <th class="text-end">Descuento</th>
                                                <th class="text-end">Importe</th>
                                                <th>Sucursal</th>
                                                <th>Convenio</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                `;
                
                if (version.detalles && version.detalles.length > 0) {
                    version.detalles.forEach((detalle, idx) => {
                        const importe = parseFloat(detalle.importe || 0);
                        const precioUnitario = parseFloat(detalle.precio_unitario || 0);
                        const descuento = parseFloat(detalle.descuento || 0);
                        const precioConDescuento = precioUnitario * (1 - descuento / 100);
                        
                        html += `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td><small>${detalle.codbar || '-'}</small></td>
                                <td>${detalle.descripcion || '-'}</td>
                                <td class="text-center">${detalle.cantidad || 0}</td>
                                <td class="text-end">
                                    $${precioUnitario.toFixed(2)}
                                    ${descuento > 0 ? `<br><small class="text-muted text-decoration-line-through">$${precioUnitario.toFixed(2)}</small>` : ''}
                                </td>
                                <td class="text-end">${descuento > 0 ? descuento + '%' : '-'}</td>
                                <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                                <td><small>${detalle.nombre_sucursal_surtido || 'No asignada'}</small></td>
                                <td><small>${detalle.nombre_convenio || 'No aplica'}</small></td>
                            </tr>
                        `;
                    });
                } else {
                    html += `
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">
                                <i class="bi bi-box-seam"></i> No hay productos registrados
                            </td>
                        </tr>
                    `;
                }
                
                html += `
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold">Total:</td>
                                                <td class="text-end fw-bold">$${parseFloat(version.importe_total || 0).toFixed(2)}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                ${version.comentarios ? `
                                <div class="p-2 bg-light small">
                                    <strong>Comentarios:</strong> ${version.comentarios}
                                </div>
                                ` : ''}
                            </div>
                        </div>
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