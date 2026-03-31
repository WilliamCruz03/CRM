<!-- Modal Editar Cotización -->
<div class="modal fade" id="modalEditarCotizacion" tabindex="-1" aria-labelledby="modalEditarCotizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalEditarCotizacionLabel">
                    <i class="bi bi-pencil-square"></i> Editar Cotización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCotizacion">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_cotizacion_id" name="cotizacion_id">
                    
                    <!-- Cliente (solo lectura) -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-person"></i> Datos del Cliente</strong>
                        </div>
                        <div class="card-body">
                            <div class="p-2 bg-light rounded" id="edit_cliente_info">
                                <strong id="edit_cliente_nombre">-</strong>
                                <br><small id="edit_cliente_email" class="text-muted">-</small>
                            </div>
                            <input type="hidden" id="edit_cliente_id" name="cliente_id">
                        </div>
                    </div>

                    <!-- Datos de la cotización -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-info-circle"></i> Información de la Cotización</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Folio</label>
                                    <p class="fw-bold" id="edit_folio">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Fecha de creación</label>
                                    <p id="edit_fecha_creacion">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Fase <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_fase_id" name="fase_id" required>
                                        <option value="">Seleccionar fase...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Clasificación</label>
                                    <select class="form-select" id="edit_clasificacion_id" name="clasificacion_id">
                                        <option value="">Seleccionar clasificación...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sucursal asignada</label>
                                    <select class="form-select" id="edit_sucursal_asignada_id" name="sucursal_asignada_id">
                                        <option value="">Seleccionar sucursal...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Certeza</label>
                                    <select class="form-select" id="edit_certeza" name="certeza">
                                        <option value="0">Baja (0%)</option>
                                        <option value="25">Media baja (25%)</option>
                                        <option value="50">Media (50%)</option>
                                        <option value="75">Media alta (75%)</option>
                                        <option value="100">Alta (100%)</option>
                                    </select>
                                    <small class="text-muted">Si la certeza es mayor a 50, los productos se apartarán</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Comentarios</label>
                                    <textarea class="form-control" id="edit_comentarios" name="comentarios" rows="2" 
                                              placeholder="Notas adicionales sobre la cotización..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Artículos -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-box-seam"></i> Artículos</strong>
                        </div>
                        <div class="card-body">
                            <!-- Buscador de artículos -->
                            <div class="mb-3">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="edit_buscarArticulo" 
                                           placeholder="Buscar artículo por código o descripción...">
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para agregarlo.</small>
                                
                                <div id="edit_resultadosArticulos" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Artículos encontrados (haz clic para agregar)</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="edit_listaArticulos"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de artículos -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                            <th>#</th>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio</th>
                                            <th class="text-end">Importe</th>
                                            <th class="text-center">Sucursal surtido</th>
                                            <th class="text-center">Acciones</th>
                                        </thead>
                                    <tbody id="edit_articulosBody">
                                        <tr id="edit-sin-articulos-row">
                                            <td colspan="8" class="text-center py-4">
                                                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">No hay artículos agregados</p>
                                              </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                            <td colspan="5" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="edit_totalCotizacion">$0.00</td>
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
                <button type="button" class="btn btn-warning" onclick="guardarEdicionCotizacion()">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================
// VARIABLES GLOBALES DEL MODAL EDITAR
// ============================================
let editArticulosSeleccionados = [];
let editCatalogos = {
    fases: [],
    clasificaciones: [],
    sucursales: [],
    convenios: []
};

// ============================================
// CARGA DE CATÁLOGOS
// ============================================
function cargarCatalogosEdit() {
    fetch('{{ route("ventas.cotizaciones.catalogos") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Catálogos recibidos:', data);
        if (data.success) {
            editCatalogos = data.data;
            
            const faseSelect = document.getElementById('edit_fase_id');
            const clasificacionSelect = document.getElementById('edit_clasificacion_id');
            const sucursalSelect = document.getElementById('edit_sucursal_asignada_id');
            
            if (faseSelect) {
                faseSelect.innerHTML = '<option value="">Seleccionar fase...</option>' + 
                    editCatalogos.fases.map(f => `<option value="${f.id_fase}">${f.fase}</option>`).join('');
            }
            if (clasificacionSelect) {
                clasificacionSelect.innerHTML = '<option value="">Seleccionar clasificación...</option>' + 
                    editCatalogos.clasificaciones.map(c => `<option value="${c.id_clasificacion}">${c.clasificacion}</option>`).join('');
            }
            if (sucursalSelect) {
                sucursalSelect.innerHTML = '<option value="">Seleccionar sucursal...</option>' + 
                    editCatalogos.sucursales.map(s => `<option value="${s.id_sucursal}">${s.nombre}</option>`).join('');
            }
        }
    })
    .catch(error => console.error('Error cargando catálogos:', error));
}

// ============================================
// CARGA DE DATOS DE LA COTIZACIÓN
// ============================================
window.cargarDatosEditarCotizacion = function(data) {
    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val;
    };
    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };
    
    setVal('edit_cotizacion_id', data.id_cotizacion);
    setVal('edit_cliente_id', data.id_cliente);
    
    // Mostrar nombre completo (Nombre + ApPaterno + ApMaterno)
    let nombreCompleto = '-';
    if (data.cliente) {
        const partes = [];
        if (data.cliente.Nombre) partes.push(data.cliente.Nombre);
        if (data.cliente.apPaterno) partes.push(data.cliente.apPaterno);
        if (data.cliente.apMaterno) partes.push(data.cliente.apMaterno);
        nombreCompleto = partes.join(' ') || data.cliente.nombre_completo || '-';
    }
    setText('edit_cliente_nombre', nombreCompleto);
    setText('edit_cliente_email', data.cliente?.email1 || '-');
    setText('edit_folio', data.folio || '-');
    setText('edit_fecha_creacion', data.fecha_creacion ? new Date(data.fecha_creacion).toLocaleString() : '-');
    setVal('edit_comentarios', data.comentarios || '');
    setVal('edit_certeza', data.certeza || 0);
    
    if (data.id_fase) setVal('edit_fase_id', data.id_fase);
    if (data.id_clasificacion) setVal('edit_clasificacion_id', data.id_clasificacion);
    if (data.id_sucursal_asignada) setVal('edit_sucursal_asignada_id', data.id_sucursal_asignada);
    
    editArticulosSeleccionados = [];
    if (data.detalles && data.detalles.length > 0) {
        data.detalles.forEach(detalle => {
            editArticulosSeleccionados.push({
                id_producto: detalle.id_producto,
                nombre: detalle.descripcion || '-',
                codbar: detalle.codbar || '',
                precio: parseFloat(detalle.precio_unitario || 0),
                cantidad: parseInt(detalle.cantidad || 1),
                descuento: parseFloat(detalle.descuento || 0),
                id_convenio: detalle.id_convenio,
                id_sucursal_surtido: detalle.id_sucursal_surtido,
                num_familia: detalle.producto?.num_familia || '',
                inventario_disponible: detalle.producto?.inventario || 0,
                nombre_sucursal_surtido: detalle.sucursal_surtido?.nombre || ''
            });
        });
    }
    renderizarTablaArticulosEdit();
};

// ============================================
// FUNCIONES PARA ARTÍCULOS (EDITAR)
// ============================================
let timeoutBusquedaArticuloEdit;

function buscarArticulosEdit(termino) {
    if (!termino || termino.length < 2) {
        const resultadosDiv = document.getElementById('edit_resultadosArticulos');
        if (resultadosDiv) resultadosDiv.style.display = 'none';
        return;
    }
    
    const sucursalAsignadaId = document.getElementById('edit_sucursal_asignada_id')?.value || '';
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value || '';
    
    let url = `{{ route("ventas.cotizaciones.productos.buscar") }}?q=${encodeURIComponent(termino)}&sucursal_asignada_id=${sucursalAsignadaId}&cotizacion_id=${cotizacionId}`;
    
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('edit_resultadosArticulos');
        const listaResultados = document.getElementById('edit_listaArticulos');
        
        if (resultadosDiv && listaResultados) {
            if (data.success && data.data.length > 0) {
                window.resultadosBusquedaEdit = data.data;
                
                listaResultados.innerHTML = data.data.map((articulo, idx) => {
                    const yaExiste = editArticulosSeleccionados.some(a => a.id_producto === articulo.id);
                    const esSucursalAsignada = articulo.id_sucursal == sucursalAsignadaId;
                    const stockClass = articulo.inventario > 0 ? 'text-success' : 'text-danger';
                    const badgeClass = esSucursalAsignada ? 'bg-primary' : 'bg-secondary';
                    
                    return `
                        <div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                             onclick="agregarArticuloEditPorIndice(${idx})"
                             style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${escapeHtml(articulo.nombre)}</strong>
                                    <br><small class="text-muted">Código: ${escapeHtml(articulo.codbar || 'N/A')} | Precio: $${articulo.precio.toFixed(2)}</small>
                                    <br><small class="text-muted">Familia: ${escapeHtml(articulo.num_familia || 'N/A')}</small>
                                    <br><span class="badge ${badgeClass} me-1">${escapeHtml(articulo.nombre_sucursal)}</span>
                                    <span class="badge ${stockClass}">Stock: ${articulo.inventario}</span>
                                </div>
                                ${yaExiste ? '<span class="badge bg-secondary">Ya agregado</span>' : '<span class="badge bg-success">Agregar</span>'}
                            </div>
                        </div>
                    `;
                }).join('');
                resultadosDiv.style.display = 'block';
            } else {
                listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron artículos con stock disponible</div>';
                resultadosDiv.style.display = 'block';
            }
        }
    })
    .catch(error => console.error('Error buscando artículos:', error));
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

window.agregarArticuloEditPorIndice = function(idx) {
    if (!window.resultadosBusquedaEdit || !window.resultadosBusquedaEdit[idx]) return;
    
    const articulo = window.resultadosBusquedaEdit[idx];
    const yaExiste = editArticulosSeleccionados.some(a => a.id_producto === articulo.id);
    if (yaExiste) return;
    
    const sucursalesArray = [{
        id_sucursal: articulo.id_sucursal,
        nombre_sucursal: articulo.nombre_sucursal,
        inventario: articulo.inventario
    }];
    
    agregarArticuloEdit(
        articulo.id,
        articulo.nombre,
        articulo.precio,
        articulo.codbar || '',
        articulo.num_familia || '',
        sucursalesArray
    );
};

window.agregarArticuloEdit = function(id, nombre, precio, codbar, numFamilia, sucursalesInfo) {
    if (editArticulosSeleccionados.some(a => a.id_producto === id)) return;
    
    let descuento = 0;
    let idConvenio = null;
    
    const sucursalAsignadaId = document.getElementById('edit_sucursal_asignada_id')?.value;
    let sucursalSeleccionada = null;
    let inventarioDisponible = 0;
    
    if (sucursalesInfo && sucursalesInfo.length > 0) {
        sucursalSeleccionada = sucursalesInfo.find(s => s.id_sucursal == sucursalAsignadaId && s.inventario > 0);
        if (!sucursalSeleccionada) {
            sucursalSeleccionada = sucursalesInfo.find(s => s.inventario > 0);
        }
        inventarioDisponible = sucursalSeleccionada?.inventario || 0;
    }
    
    if (!sucursalSeleccionada) {
        if (window.mostrarToast) window.mostrarToast('No hay stock suficiente en ninguna sucursal', 'warning');
        return;
    }
    
    if (inventarioDisponible < 1) {
        if (window.mostrarToast) window.mostrarToast(`Solo hay ${inventarioDisponible} unidades disponibles en ${sucursalSeleccionada.nombre_sucursal}`, 'warning');
        return;
    }
    
    editArticulosSeleccionados.push({
        id_producto: id,
        nombre: nombre,
        codbar: codbar,
        precio: precio,
        cantidad: 1,
        descuento: descuento,
        id_convenio: idConvenio,
        id_sucursal_surtido: sucursalSeleccionada.id_sucursal,
        num_familia: numFamilia,
        inventario_disponible: inventarioDisponible,
        nombre_sucursal_surtido: sucursalSeleccionada.nombre_sucursal
    });
    
    renderizarTablaArticulosEdit();
    const buscador = document.getElementById('edit_buscarArticulo');
    if (buscador) buscador.value = '';
    const resultadosDiv = document.getElementById('edit_resultadosArticulos');
    if (resultadosDiv) resultadosDiv.style.display = 'none';
};

window.eliminarArticuloEdit = function(index) {
    editArticulosSeleccionados.splice(index, 1);
    renderizarTablaArticulosEdit();
};

window.actualizarCantidadEdit = function(index, cantidad) {
    const articulo = editArticulosSeleccionados[index];
    const nuevaCantidad = Math.max(1, parseInt(cantidad) || 1);
    const maxDisponible = articulo.inventario_disponible || 999;
    
    if (nuevaCantidad > maxDisponible) {
        if (window.mostrarToast) {
            window.mostrarToast(`Solo hay ${maxDisponible} unidades disponibles en ${articulo.nombre_sucursal_surtido || 'esta sucursal'}`, 'warning');
        }
        articulo.cantidad = maxDisponible;
    } else {
        articulo.cantidad = nuevaCantidad;
    }
    
    renderizarTablaArticulosEdit();
};

window.actualizarSucursalSurtidoEdit = function(index, sucursalId) {
    const articulo = editArticulosSeleccionados[index];
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value;
    
    if (!sucursalId || sucursalId === articulo.id_sucursal_surtido) {
        return;
    }
    
    // Usar el nombre de ruta correcto
    let url = `{{ route("ventas.cotizaciones.productos.por-sucursal", ['sucursalId' => '__SUCURSAL_ID__']) }}`;
    url = url.replace('__SUCURSAL_ID__', sucursalId);
    url += `?producto_id=${articulo.id_producto}`;
    if (cotizacionId) {
        url += `&cotizacion_id=${cotizacionId}`;
    }
    
    console.log('Verificando stock en sucursal:', sucursalId, 'producto:', articulo.id_producto);
    
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta stock:', data);
        if (data.success && data.data.length > 0) {
            const producto = data.data[0];
            const stockDisponible = producto.inventario || 0;
            
            console.log('Stock disponible en sucursal:', stockDisponible);
            
            if (stockDisponible < articulo.cantidad) {
                if (window.mostrarToast) {
                    window.mostrarToast(`La sucursal seleccionada solo tiene ${stockDisponible} unidades disponibles. La cantidad se ajustará.`, 'warning');
                }
                articulo.cantidad = Math.min(articulo.cantidad, stockDisponible);
            }
            
            articulo.id_sucursal_surtido = sucursalId;
            articulo.nombre_sucursal_surtido = producto.nombre_sucursal || '';
            articulo.inventario_disponible = stockDisponible;
            renderizarTablaArticulosEdit();
        } else {
            console.log('No hay stock en esta sucursal');
            if (window.mostrarToast) {
                window.mostrarToast('Esta sucursal no tiene stock de este producto', 'danger');
            }
            const select = document.getElementById(`edit_surtido_${index}`);
            if (select) select.value = articulo.id_sucursal_surtido || '';
        }
    })
    .catch(error => {
        console.error('Error al obtener stock:', error);
        if (window.mostrarToast) window.mostrarToast('Error al verificar stock en la sucursal', 'danger');
        const select = document.getElementById(`edit_surtido_${index}`);
        if (select) select.value = articulo.id_sucursal_surtido || '';
    });
};

function renderizarTablaArticulosEdit() {
    const tbody = document.getElementById('edit_articulosBody');
    if (!tbody) return;

    console.log('Sucursales disponibles en catálogo:', editCatalogos.sucursales);
    
    let totalGeneral = 0;
    
    if (editArticulosSeleccionados.length === 0) {
        tbody.innerHTML = `
            <tr id="edit-sin-articulos-row">
                <td colspan="8" class="text-center py-4">
                    <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No hay artículos agregados</p>
                <\/td>
            <\/tr>
        `;
        const totalSpan = document.getElementById('edit_totalCotizacion');
        if (totalSpan) totalSpan.textContent = '$0.00';
        return;
    }
    
    let html = '';
    editArticulosSeleccionados.forEach((articulo, index) => {
        const precioConDescuento = articulo.precio * (1 - articulo.descuento / 100);
        const importe = articulo.cantidad * precioConDescuento;
        totalGeneral += importe;
        
        html += `
            <tr id="edit-articulo-row-${index}">
                <td class="text-center">${index + 1}<\/td>
                <td><small>${articulo.codbar || '-'}<\/small><\/td>
                <td>
                    <strong>${articulo.nombre}</strong>
                    ${articulo.descuento > 0 ? `<br><small class="text-muted"><i class="bi bi-tag"></i> ${articulo.descuento}% descuento aplicado</small>` : ''}
                    <br><small class="text-muted">Sucursal: ${articulo.nombre_sucursal_surtido || 'No asignada'} | Máx: ${articulo.inventario_disponible}</small>
                <\/td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center" 
                           value="${articulo.cantidad}" min="1" 
                           max="${articulo.inventario_disponible}"
                           onchange="actualizarCantidadEdit(${index}, this.value)"
                           style="width: 80px;">
                <\/td>
                <td class="text-end">
                    <span class="fw-bold">$${precioConDescuento.toFixed(2)}<\/span>
                    ${articulo.precio !== precioConDescuento ? `<br><small class="text-muted text-decoration-line-through">$${articulo.precio.toFixed(2)}</small>` : ''}
                <\/td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}<\/td>
                <td class="text-center">
                    <select class="form-select form-select-sm" id="edit_surtido_${index}" onchange="actualizarSucursalSurtidoEdit(${index}, this.value)">
                        <option value="">Seleccionar sucursal<\/option>
                        ${editCatalogos.sucursales ? editCatalogos.sucursales.map(s => 
                            `<option value="${s.id_sucursal}" ${articulo.id_sucursal_surtido == s.id_sucursal ? 'selected' : ''}>${s.nombre}</option>`
                        ).join('') : ''}
                    <\/select>
                <\/td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArticuloEdit(${index})">
                        <i class="bi bi-trash"><\/i>
                    <\/button>
                <\/td>
            <\/tr>
        `;
    });
    
    tbody.innerHTML = html;
    const totalSpan = document.getElementById('edit_totalCotizacion');
    if (totalSpan) totalSpan.textContent = `$${totalGeneral.toFixed(2)}`;
}

// ============================================
// GUARDAR EDICIÓN
// ============================================
window.guardarEdicionCotizacion = function() {
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value;
    const faseId = document.getElementById('edit_fase_id')?.value;
    
    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }
    
    if (editArticulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }
    
    const articulos = editArticulosSeleccionados.map((a) => ({
        id_producto: a.id_producto,
        cantidad: a.cantidad,
        precio_unitario: a.precio,
        descuento: a.descuento,
        id_convenio: a.id_convenio,
        id_sucursal_surtido: a.id_sucursal_surtido
    }));
    
    const formData = {
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('edit_clasificacion_id')?.value || null,
        id_sucursal_asignada: document.getElementById('edit_sucursal_asignada_id')?.value || null,
        certeza: parseInt(document.getElementById('edit_certeza')?.value || 0),
        comentarios: document.getElementById('edit_comentarios')?.value || '',
        articulos: articulos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    fetch(`/ventas/cotizaciones/${cotizacionId}`, {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
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
// EVENT LISTENERS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    cargarCatalogosEdit();
    
    const buscadorArticulos = document.getElementById('edit_buscarArticulo');
    if (buscadorArticulos) {
        buscadorArticulos.addEventListener('input', function() {
            clearTimeout(timeoutBusquedaArticuloEdit);
            timeoutBusquedaArticuloEdit = setTimeout(() => buscarArticulosEdit(this.value), 300);
        });
    }
    
    document.addEventListener('click', function(event) {
        const resultados = document.getElementById('edit_resultadosArticulos');
        const buscador = document.getElementById('edit_buscarArticulo');
        if (resultados && buscador && !resultados.contains(event.target) && event.target !== buscador) {
            resultados.style.display = 'none';
        }
    });
    
    const certezaSelect = document.getElementById('edit_certeza');
    if (certezaSelect) {
        certezaSelect.addEventListener('change', function() {
            const nuevaCerteza = parseInt(this.value || 0);
            const aparta = nuevaCerteza >= 75;
            
            if (aparta) {
                editArticulosSeleccionados.forEach((articulo, idx) => {
                    if (articulo.id_sucursal_surtido) {
                        actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
                    }
                });
            }
            
            if (window.mostrarToast) {
                window.mostrarToast(aparta ? 'Los productos se apartarán automáticamente' : 'Los productos ya no se apartarán', 'info');
            }
        });
    }
});
</script>
@endpush