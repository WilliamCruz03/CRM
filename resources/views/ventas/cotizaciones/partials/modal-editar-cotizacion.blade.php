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
                                        <tr>
                                            <th>#</th>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio</th>
                                            <th class="text-end">Descuento</th>
                                            <th class="text-end">Importe</th>
                                            <th class="text-center">Convenio</th>
                                            <th class="text-center">Sucursal surtido</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_articulosBody">
                                        <tr id="edit-sin-articulos-row">
                                            <td colspan="10" class="text-center py-4">
                                                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">No hay artículos agregados</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="6" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="edit_totalCotizacion">$0.00</td>
                                            <td colspan="3"></td>
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
        if (data.success) {
            editCatalogos = data.data;
            
            const faseSelect = document.getElementById('edit_fase_id');
            const clasificacionSelect = document.getElementById('edit_clasificacion_id');
            const sucursalSelect = document.getElementById('edit_sucursal_asignada_id');
            
            faseSelect.innerHTML = '<option value="">Seleccionar fase...</option>' + 
                editCatalogos.fases.map(f => `<option value="${f.id_fase}">${f.fase}</option>`).join('');
            
            clasificacionSelect.innerHTML = '<option value="">Seleccionar clasificación...</option>' + 
                editCatalogos.clasificaciones.map(c => `<option value="${c.id_clasificacion}">${c.clasificacion}</option>`).join('');
            
            sucursalSelect.innerHTML = '<option value="">Seleccionar sucursal...</option>' + 
                editCatalogos.sucursales.map(s => `<option value="${s.id_sucursal}">${s.nombre}</option>`).join('');
        }
    })
    .catch(error => console.error('Error cargando catálogos:', error));
}

// ============================================
// CARGA DE DATOS DE LA COTIZACIÓN
// ============================================
function cargarDatosEditarCotizacion(data) {
    document.getElementById('edit_cotizacion_id').value = data.id_cotizacion;
    document.getElementById('edit_cliente_id').value = data.id_cliente;
    document.getElementById('edit_cliente_nombre').textContent = data.cliente?.nombre_completo || '-';
    document.getElementById('edit_cliente_email').textContent = data.cliente?.email1 || '-';
    document.getElementById('edit_folio').textContent = data.folio || '-';
    document.getElementById('edit_fecha_creacion').textContent = data.fecha_creacion ? new Date(data.fecha_creacion).toLocaleString() : '-';
    document.getElementById('edit_comentarios').value = data.comentarios || '';
    
    // Seleccionar valores en selects
    if (data.id_fase) document.getElementById('edit_fase_id').value = data.id_fase;
    if (data.id_clasificacion) document.getElementById('edit_clasificacion_id').value = data.id_clasificacion;
    if (data.id_sucursal_asignada) document.getElementById('edit_sucursal_asignada_id').value = data.id_sucursal_asignada;
    
    // Cargar artículos
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
                id_sucursal_surtido: detalle.id_sucursal_surtido
            });
        });
    }
    renderizarTablaArticulosEdit();
}

// ============================================
// FUNCIONES PARA ARTÍCULOS (EDITAR)
// ============================================
let timeoutBusquedaArticuloEdit;

function buscarArticulosEdit(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('edit_resultadosArticulos').style.display = 'none';
        return;
    }
    
    fetch(`{{ route("ventas.cotizaciones.productos.buscar") }}?q=${encodeURIComponent(termino)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('edit_resultadosArticulos');
        const listaResultados = document.getElementById('edit_listaArticulos');
        
        if (data.success && data.data.length > 0) {
            listaResultados.innerHTML = data.data.map(articulo => {
                const yaExiste = editArticulosSeleccionados.some(a => a.id_producto === articulo.id);
                return `
                    <div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                         onclick="${!yaExiste ? `agregarArticuloEdit(${articulo.id}, '${articulo.nombre.replace(/'/g, "\\'")}', ${articulo.precio}, '${articulo.codbar || ''}')` : ''}" 
                         style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${articulo.nombre}</strong>
                                <br><small class="text-muted">Código: ${articulo.codbar || 'N/A'} | Precio: $${articulo.precio.toFixed(2)}</small>
                            </div>
                            ${yaExiste ? '<span class="badge bg-secondary">Ya agregado</span>' : '<span class="badge bg-success">Agregar</span>'}
                        </div>
                    </div>
                `;
            }).join('');
            resultadosDiv.style.display = 'block';
        } else {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron artículos</div>';
            resultadosDiv.style.display = 'block';
        }
    })
    .catch(error => console.error('Error buscando artículos:', error));
}

window.agregarArticuloEdit = function(id, nombre, precio, codbar) {
    if (editArticulosSeleccionados.some(a => a.id_producto === id)) return;
    
    editArticulosSeleccionados.push({
        id_producto: id,
        nombre: nombre,
        codbar: codbar,
        precio: precio,
        cantidad: 1,
        descuento: 0,
        id_convenio: null,
        id_sucursal_surtido: null
    });
    
    renderizarTablaArticulosEdit();
    document.getElementById('edit_buscarArticulo').value = '';
    document.getElementById('edit_resultadosArticulos').style.display = 'none';
};

window.eliminarArticuloEdit = function(index) {
    editArticulosSeleccionados.splice(index, 1);
    renderizarTablaArticulosEdit();
};

window.actualizarCantidadEdit = function(index, cantidad) {
    editArticulosSeleccionados[index].cantidad = Math.max(1, parseInt(cantidad) || 1);
    renderizarTablaArticulosEdit();
};

window.actualizarDescuentoEdit = function(index, descuento) {
    editArticulosSeleccionados[index].descuento = Math.min(100, Math.max(0, parseFloat(descuento) || 0));
    renderizarTablaArticulosEdit();
};

window.cambiarConvenioEdit = function(index, convenioId) {
    editArticulosSeleccionados[index].id_convenio = convenioId || null;
    if (convenioId && editCatalogos.convenios) {
        const convenio = editCatalogos.convenios.find(c => c.id == convenioId);
        if (convenio && convenio.porcentaje_descuento) {
            editArticulosSeleccionados[index].descuento = convenio.porcentaje_descuento;
            renderizarTablaArticulosEdit();
        }
    }
};

function renderizarTablaArticulosEdit() {
    const tbody = document.getElementById('edit_articulosBody');
    let totalGeneral = 0;
    
    if (editArticulosSeleccionados.length === 0) {
        tbody.innerHTML = `
            <tr id="edit-sin-articulos-row">
                <td colspan="10" class="text-center py-4">
                    <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No hay artículos agregados</p>
                </td>
            </tr>
        `;
        document.getElementById('edit_totalCotizacion').textContent = '$0.00';
        return;
    }
    
    let html = '';
    editArticulosSeleccionados.forEach((articulo, index) => {
        const importe = articulo.cantidad * articulo.precio * (1 - articulo.descuento / 100);
        totalGeneral += importe;
        
        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td><small>${articulo.codbar || '-'}</small></td>
                <td>${articulo.nombre}</td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center" 
                           value="${articulo.cantidad}" min="1" 
                           onchange="actualizarCantidadEdit(${index}, this.value)"
                           style="width: 80px;">
                </td>
                <td class="text-end">$${articulo.precio.toFixed(2)}</td>
                <td class="text-end">
                    <input type="number" class="form-control form-control-sm text-end" 
                           value="${articulo.descuento}" min="0" max="100" step="0.5"
                           onchange="actualizarDescuentoEdit(${index}, this.value)"
                           style="width: 70px;">%
                </td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}</td>
                <td class="text-center">
                    <select class="form-select form-select-sm" onchange="cambiarConvenioEdit(${index}, this.value)">
                        <option value="">Sin convenio</option>
                        ${editCatalogos.convenios ? editCatalogos.convenios.map(c => 
                            `<option value="${c.id}" ${articulo.id_convenio == c.id ? 'selected' : ''}>${c.convenio} (${c.porcentaje_descuento || 0}%)</option>`
                        ).join('') : ''}
                    </select>
                </td>
                <td class="text-center">
                    <select class="form-select form-select-sm" id="edit_surtido_${index}">
                        <option value="">Seleccionar...</option>
                        ${editCatalogos.sucursales ? editCatalogos.sucursales.map(s => 
                            `<option value="${s.id_sucursal}" ${articulo.id_sucursal_surtido == s.id_sucursal ? 'selected' : ''}>${s.nombre}</option>`
                        ).join('') : ''}
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArticuloEdit(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('edit_totalCotizacion').textContent = `$${totalGeneral.toFixed(2)}`;
}

// ============================================
// GUARDAR EDICIÓN
// ============================================
window.guardarEdicionCotizacion = function() {
    const cotizacionId = document.getElementById('edit_cotizacion_id').value;
    const faseId = document.getElementById('edit_fase_id').value;
    
    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }
    
    if (editArticulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }
    
    const articulos = editArticulosSeleccionados.map((a, index) => ({
        id_producto: a.id_producto,
        cantidad: a.cantidad,
        precio_unitario: a.precio,
        descuento: a.descuento,
        id_convenio: a.id_convenio,
        id_sucursal_surtido: document.getElementById(`edit_surtido_${index}`)?.value || null
    }));
    
    const formData = {
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('edit_clasificacion_id').value || null,
        id_sucursal_asignada: document.getElementById('edit_sucursal_asignada_id').value || null,
        comentarios: document.getElementById('edit_comentarios').value,
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
    
    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(event) {
        const resultados = document.getElementById('edit_resultadosArticulos');
        const buscador = document.getElementById('edit_buscarArticulo');
        
        if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
            resultados.style.display = 'none';
        }
    });
});
</script>
@endpush