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
                                        <option value="1">Baja</option>
                                        <option value="2">Media</option>
                                        <option value="3">Alta</option>
                                    </select>
                                    <small class="text-muted">Si la certeza es <b>alta</b>, los productos se apartarán</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Convenio</label>
                                    <select class="form-select" id="edit_convenio_general" name="convenio_general">
                                        <option value="">Sin convenio</option>
                                    </select>
                                    <small class="text-muted">Selecciona un convenio para aplicar los descuentos</small>
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
                                           placeholder="Buscar artículo por código o descripción..."
                                           autocomplete="off">
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
    return fetch('{{ route("ventas.cotizaciones.catalogos") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Catálogos recibidos:', data);
        if (data.success) {
            editCatalogos = data.data;

            console.log('Sucursales cargadas:', editCatalogos.sucursales);
            console.log('Convenios cargados:', editCatalogos.convenios);
            
            const faseSelect = document.getElementById('edit_fase_id');
            const clasificacionSelect = document.getElementById('edit_clasificacion_id');
            const sucursalSelect = document.getElementById('edit_sucursal_asignada_id');
            const convenioGeneralSelect = document.getElementById('edit_convenio_general');
            
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
            if (convenioGeneralSelect && editCatalogos.convenios) {
                convenioGeneralSelect.innerHTML = '<option value="">Sin convenio</option>' + 
                    editCatalogos.convenios.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            }
        }
        return data;
    })
    .catch(error => {
        console.error('Error cargando catálogos:', error);
        throw error;
    });
}

// ============================================
// APLICAR CONVENIO GENERAL A TODOS LOS ARTÍCULOS
// ============================================
function aplicarConvenioGeneralEdit() {
    const convenioId = document.getElementById('edit_convenio_general')?.value;
    
    if (!convenioId) {
        // Si no hay convenio, quitar descuentos
        editArticulosSeleccionados.forEach(articulo => {
            articulo.descuento = 0;
            articulo.id_convenio = null;
        });
        renderizarTablaArticulosEdit();
        return;
    }
    
    // Buscar el convenio en el catálogo
    const convenio = editCatalogos.convenios?.find(c => c.id == convenioId);
    
    if (convenio && convenio.familias) {
        editArticulosSeleccionados.forEach(articulo => {
            // Buscar si la familia del artículo tiene descuento en este convenio
            const familiaConDescuento = convenio.familias.find(f => f.num_familia == articulo.num_familia);
            
            if (familiaConDescuento) {
                articulo.descuento = familiaConDescuento.descuento;
                articulo.id_convenio = convenio.id;
            } else {
                articulo.descuento = 0;
                articulo.id_convenio = null;
            }
        });
        renderizarTablaArticulosEdit();
        
        if (window.mostrarToast) {
            window.mostrarToast(`✅ Convenio "${convenio.nombre}" aplicado a los artículos correspondientes`, 'success');
        }
    }
}

// ============================================
// RECALCULAR STOCK CON APARTADOS (cuando cambia certeza)
// ============================================
function recalcularStockPorApartadoEdit() {
    const certeza = parseInt(document.getElementById('edit_certeza')?.value || 0);
    const aparta = certeza >= 75;
    
    if (aparta) {
        // Recalcular stock para todos los productos considerando apartados
        editArticulosSeleccionados.forEach((articulo, idx) => {
            if (articulo.id_sucursal_surtido) {
                // Forzar recálculo de stock con la nueva certeza
                actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
            }
        });
    }
    
    if (window.mostrarToast) {
        window.mostrarToast(
            aparta ? 'Los productos se apartarán automáticamente al guardar' : 'Los productos ya no se apartarán', 
            'info'
        );
    }
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
    
    // Cargar los artículos
     editArticulosSeleccionados = [];
    if (data.detalles && data.detalles.length > 0) {
        data.detalles.forEach(detalle => {
            editArticulosSeleccionados.push({
                id_producto: parseInt(detalle.id_producto), // Convertir a número
                nombre: detalle.descripcion || '-',
                codbar: detalle.codbar || '',
                precio: parseFloat(detalle.precio_unitario || 0),
                cantidad: parseInt(detalle.cantidad || 1),
                descuento: parseFloat(detalle.descuento || 0),
                id_convenio: detalle.id_convenio,
                id_sucursal_surtido: parseInt(detalle.id_sucursal_surtido || 0), // Convertir a número
                num_familia: detalle.producto?.num_familia || '',
                inventario_disponible: parseInt(detalle.producto?.inventario || 0),
                nombre_sucursal_surtido: detalle.sucursal_surtido?.nombre || ''
            });
        });
    }
    
    renderizarTablaArticulosEdit();
    
    // Determinar si todos los artículos tienen el mismo convenio
    const conveniosUnicos = [...new Set(editArticulosSeleccionados.map(a => a.id_convenio).filter(id => id))];
    if (conveniosUnicos.length === 1) {
        setVal('edit_convenio_general', conveniosUnicos[0]);
    }
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
    
    console.log('Buscando artículos para edición:', {
        termino,
        sucursalAsignadaId,
        cotizacionId
    });

    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resultados de búsqueda edición:', data);
        
        const resultadosDiv = document.getElementById('edit_resultadosArticulos');
        const listaResultados = document.getElementById('edit_listaArticulos');
        
        if (resultadosDiv && listaResultados) {
            if (data.success && data.data && data.data.length > 0) {
                window.resultadosBusquedaEdit = data.data;
                
                listaResultados.innerHTML = data.data.map((articulo, idx) => {
                    // Verificar si ya existe en la misma sucursal (para mostrar advertencia)
                    const yaExiste = editArticulosSeleccionados.some(a => 
                        a.id_producto === articulo.id && 
                        a.id_sucursal_surtido === articulo.id_sucursal
                    );
                    const esSucursalAsignada = articulo.id_sucursal == sucursalAsignadaId;
                    const stockClass = articulo.inventario > 0 ? 'text-success' : 'text-danger';
                    const badgeClass = esSucursalAsignada ? 'bg-primary' : 'bg-secondary';
                    
                    // Mostrar informacion de apartados si los hay
                    const apartadoInfo = articulo.apartado > 0 ? `<span class="badge bg-warning ms-1">Apartado: ${articulo.apartado}</span>` : '';
                    
                    // Si ya existe, mostrar badge de advertencia pero permitir agregar (sumar)
                    const existenteBadge = yaExiste ? 
                        '<span class="badge bg-warning ms-1">Ya agregado (se sumará)</span>' : '';
                    
                    return `
                        <div class="list-group-item list-group-item-action" 
                             onclick="agregarArticuloEditPorIndice(${idx})"
                             style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${escapeHtml(articulo.nombre)}</strong>
                                    <br><small class="text-muted">Código: ${escapeHtml(articulo.codbar || 'N/A')} | Precio: $${articulo.precio.toFixed(2)}</small>
                                    <br><small class="text-muted">Familia: ${escapeHtml(articulo.num_familia || 'N/A')}</small>
                                    <br><span class="badge ${badgeClass} me-1">${escapeHtml(articulo.nombre_sucursal)}</span>
                                    <span class="badge ${stockClass}">Stock: ${articulo.inventario}</span>
                                    ${apartadoInfo}
                                    ${existenteBadge}
                                </div>
                                <span class="badge bg-success">Agregar</span>
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
    
    const articuloData = window.resultadosBusquedaEdit[idx];
    
    // Ya no mostramos el toast aquí, se mostrará en agregarOSumarArticulo
    const yaExiste = editArticulosSeleccionados.some(a => 
        Number(a.id_producto) === Number(articuloData.id) && 
        Number(a.id_sucursal_surtido) === Number(articuloData.id_sucursal)
    );
    
    const sucursalesArray = [{
        id_sucursal: articuloData.id_sucursal,
        nombre_sucursal: articuloData.nombre_sucursal,
        inventario: articuloData.inventario
    }];
    
    // Crear el objeto del artículo
    const nuevoArticulo = {
        id_producto: articuloData.id,
        nombre: articuloData.nombre,
        codbar: articuloData.codbar || '',
        precio: articuloData.precio,
        cantidad: 1,
        descuento: 0,
        id_convenio: null,
        id_sucursal_surtido: Number(articuloData.id_sucursal),
        num_familia: articuloData.num_familia || '',
        inventario_disponible: articuloData.inventario,
        nombre_sucursal_surtido: articuloData.nombre_sucursal
    };
    
    // Aplicar convenio si existe
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
    
    // Usar la función unificada para agregar o sumar
    agregarOSumarArticulo(nuevoArticulo, editArticulosSeleccionados, true);
    
    // Limpiar buscador
    const buscador = document.getElementById('edit_buscarArticulo');
    if (buscador) buscador.value = '';
    const resultadosDiv = document.getElementById('edit_resultadosArticulos');
    if (resultadosDiv) resultadosDiv.style.display = 'none';
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

// Función genérica para agregar o sumar producto
function agregarOSumarArticulo(articulo, listaArticulos, esEdicion = false) {
    // Buscar si el producto YA EXISTE en la misma sucursal
    const existe = listaArticulos.find(a => 
        Number(a.id_producto) === Number(articulo.id_producto) && 
        Number(a.id_sucursal_surtido) === Number(articulo.id_sucursal_surtido)
    );
    
    if (existe) {
        // Producto ya existe - sumar cantidades
        const nuevaCantidad = existe.cantidad + 1;
        const maxDisponible = existe.inventario_disponible;
        
        if (nuevaCantidad <= maxDisponible) {
            existe.cantidad = nuevaCantidad;
            // SOLO mostrar el mensaje de que ya está agregado y se sumará
            if (window.mostrarToast) {
                window.mostrarToast(
                    `"${articulo.nombre}" ya está agregado. Se sumará 1 unidad a la cantidad existente.`, 
                    'success'  // Cambiado de 'info' a 'success' (verde)
                );
            }
        } else {
            if (window.mostrarToast) {
                window.mostrarToast(
                    `No se puede sumar más. Stock máximo: ${maxDisponible} unidades.`, 
                    'warning'
                );
            }
        }
    } else {
        // Producto nuevo - agregar normalmente
        listaArticulos.push(articulo);
        if (window.mostrarToast) {
            window.mostrarToast(
                `Agregado "${articulo.nombre}" a la cotización.`, 
                'success'
            );
        }
    }
    
    // Renderizar según el modal
    if (esEdicion) {
        renderizarTablaArticulosEdit();
    } else {
        renderizarTablaArticulos();
    }
}

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
    const select = document.getElementById(`edit_surtido_${index}`);
    
    if (!sucursalId || sucursalId === articulo.id_sucursal_surtido) {
        return;
    }
    
    // VERIFICAR QUE EL CATÁLOGO DE SUCURSALES ESTÉ CARGADO
    if (!editCatalogos.sucursales || editCatalogos.sucursales.length === 0) {
        console.warn('Catálogo de sucursales no cargado, recargando...');
        cargarCatalogosEdit().then(() => {
            // Reintentar después de cargar
            actualizarSucursalSurtidoEdit(index, sucursalId);
        });
        return;
    }
    
    if (select) select.disabled = true;
    
    // Buscar por EAN
    let url = `/ventas/cotizaciones/productos-por-sucursal/${sucursalId}`;
    url += `?ean=${encodeURIComponent(articulo.codbar)}`;
    if (cotizacionId) {
        url += `&cotizacion_id=${cotizacionId}`;
    }
    
    console.log('Consultando stock para producto:', articulo.codbar, 'en sucursal:', sucursalId);
    console.log('Catálogo de sucursales disponible:', editCatalogos.sucursales);
    
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta completa:', data);
        
        if (data.success && data.data && data.data.length > 0) {
            const producto = data.data[0];
            const stockDisponible = producto.inventario || 0;
            
            // Obtener el nombre de la sucursal desde el catálogo local
            let nombreSucursal = '';
            
            // PRIMERO: Buscar en editCatalogos.sucursales (más confiable)
            if (editCatalogos.sucursales) {
                const sucursalEncontrada = editCatalogos.sucursales.find(s => s.id_sucursal == sucursalId);
                if (sucursalEncontrada) {
                    nombreSucursal = sucursalEncontrada.nombre;
                    console.log('Nombre desde catálogo local:', nombreSucursal);
                }
            }
            
            // SEGUNDO: Si no se encontró, usar el del servidor
            if (!nombreSucursal && producto.nombre_sucursal && producto.nombre_sucursal !== 'N/A' && producto.nombre_sucursal !== 'Sin sucursal') {
                nombreSucursal = producto.nombre_sucursal;
                console.log('Nombre desde servidor:', nombreSucursal);
            }
            
            // TERCERO: Último fallback - obtener del select
            if (!nombreSucursal) {
                const selectOption = select?.options[select.selectedIndex];
                nombreSucursal = selectOption ? selectOption.text : 'Sucursal';
                console.log('Nombre desde select (fallback):', nombreSucursal);
            }
            
            console.log(`Producto encontrado en sucursal:`, {
                id: producto.id,
                nombre: producto.nombre,
                sucursal: nombreSucursal,
                stock: stockDisponible
            });
            
            // Función para recalcular descuento según convenio seleccionado
            const recalcularDescuentoPorConvenio = (articuloActual, nuevoProducto) => {
                const convenioId = document.getElementById('edit_convenio_general')?.value;
                if (convenioId && editCatalogos.convenios) {
                    const convenio = editCatalogos.convenios.find(c => c.id == convenioId);
                    if (convenio && convenio.familias) {
                        const familiaConDescuento = convenio.familias.find(f => f.num_familia == nuevoProducto.num_familia);
                        if (familiaConDescuento) {
                            articuloActual.descuento = familiaConDescuento.descuento;
                            articuloActual.id_convenio = convenio.id;
                            console.log(`Descuento aplicado: ${articuloActual.descuento}% por convenio ${convenio.nombre}`);
                        } else {
                            articuloActual.descuento = 0;
                            articuloActual.id_convenio = null;
                            console.log('Sin descuento para esta familia en el convenio seleccionado');
                        }
                    }
                }
            };
            
            if (stockDisponible >= articulo.cantidad) {
                // Stock suficiente - actualizar TODOS los campos
                articulo.id_producto = producto.id;
                articulo.nombre = producto.nombre;
                articulo.codbar = producto.codbar;
                articulo.num_familia = producto.num_familia;
                articulo.precio = producto.precio;
                articulo.id_sucursal_surtido = parseInt(sucursalId);
                articulo.nombre_sucursal_surtido = nombreSucursal;
                articulo.inventario_disponible = stockDisponible;
                
                // Recalcular descuento según convenio seleccionado
                recalcularDescuentoPorConvenio(articulo, producto);
                
                console.log('Campos actualizados:', {
                    id_producto: articulo.id_producto,
                    nombre_sucursal_surtido: articulo.nombre_sucursal_surtido,
                    inventario_disponible: articulo.inventario_disponible,
                    descuento: articulo.descuento
                });
                
                renderizarTablaArticulosEdit();
                
                if (window.mostrarToast) {
                    const mensajeDescuento = articulo.descuento > 0 ? ` con ${articulo.descuento}% de descuento` : '';
                    window.mostrarToast(`Producto cambiado a ${nombreSucursal}. Stock disponible: ${stockDisponible} unidades${mensajeDescuento}.`, 'success');
                }
            } else if (stockDisponible > 0) {
                // Stock insuficiente - ajustar cantidad
                if (window.mostrarToast) {
                    window.mostrarToast(
                        `La sucursal ${nombreSucursal} solo tiene ${stockDisponible} unidades. La cantidad se ajustará.`, 
                        'warning'
                    );
                }
                articulo.id_producto = producto.id;
                articulo.nombre = producto.nombre;
                articulo.codbar = producto.codbar;
                articulo.num_familia = producto.num_familia;
                articulo.precio = producto.precio;
                articulo.cantidad = stockDisponible;
                articulo.id_sucursal_surtido = parseInt(sucursalId);
                articulo.nombre_sucursal_surtido = nombreSucursal;
                articulo.inventario_disponible = stockDisponible;
                
                // Recalcular descuento según convenio seleccionado
                recalcularDescuentoPorConvenio(articulo, producto);
                
                renderizarTablaArticulosEdit();
            } else {
                // Sin stock - NO permitir cambio
                const mensaje = `El producto "${articulo.nombre}" no tiene stock disponible en ${nombreSucursal}.`;
                if (window.mostrarToast) {
                    window.mostrarToast(mensaje, 'danger');
                }
                if (select) select.value = articulo.id_sucursal_surtido || '';
            }
        } else {
            // Producto no existe en esta sucursal
            let nombreSucursal = '';
            
            // Buscar en catálogo local
            if (editCatalogos.sucursales) {
                const sucursalEncontrada = editCatalogos.sucursales.find(s => s.id_sucursal == sucursalId);
                if (sucursalEncontrada) {
                    nombreSucursal = sucursalEncontrada.nombre;
                }
            }
            
            if (!nombreSucursal) {
                const selectOption = select?.options[select.selectedIndex];
                nombreSucursal = selectOption ? selectOption.text : 'la sucursal seleccionada';
            }
            
            const mensaje = `El producto "${articulo.nombre}" no está disponible en ${nombreSucursal}.`;
            console.log(mensaje);
            
            if (window.mostrarToast) {
                window.mostrarToast(mensaje, 'danger');
            }
            if (select) select.value = articulo.id_sucursal_surtido || '';
        }
    })
    .catch(error => {
        console.error('Error al verificar stock:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error al verificar stock en la sucursal', 'danger');
        }
        if (select) select.value = articulo.id_sucursal_surtido || '';
    })
    .finally(() => {
        if (select) select.disabled = false;
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
                <td><small>${escapeHtml(articulo.codbar || '-')}<\/small><\/td>
                <td>
                    <strong>${escapeHtml(articulo.nombre)}</strong>
                    ${articulo.descuento > 0 ? `<br><small class="text-muted"><i class="bi bi-tag"></i> ${articulo.descuento}% descuento aplicado</small>` : ''}
                    <br><small class="text-muted">Sucursal: <strong>${escapeHtml(articulo.nombre_sucursal_surtido || 'No asignada')}</strong> | Máx: ${articulo.inventario_disponible}</small>
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
    // Cargar catálogos
    cargarCatalogosEdit();
    
    // Buscador de artículos
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
        if (resultados && buscador && !resultados.contains(event.target) && event.target !== buscador) {
            resultados.style.display = 'none';
        }
    });
    
    // Evento para cambio de certeza
    const certezaSelect = document.getElementById('edit_certeza');
    if (certezaSelect) {
        certezaSelect.addEventListener('change', function() {
            const nuevaCerteza = parseInt(this.value || 0);
            const aparta = nuevaCerteza >= 75;
            
            if (aparta) {
                // Recalcular stock para todos los productos (considerando apartados)
                editArticulosSeleccionados.forEach((articulo, idx) => {
                    if (articulo.id_sucursal_surtido) {
                        // Forzar recálculo de stock con la nueva certeza
                        actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
                    }
                });
            }
            
            if (window.mostrarToast) {
                window.mostrarToast(
                    aparta ? 'Los productos se apartarán automáticamente al guardar' : 'Los productos ya no se apartarán', 
                    'info'
                );
            }
        });
    }
    
    // Evento para cambio de convenio general
    const convenioGeneralSelect = document.getElementById('edit_convenio_general');
    if (convenioGeneralSelect) {
        convenioGeneralSelect.addEventListener('change', function() {
            const convenioId = this.value;
            
            if (!convenioId) {
                // Si no hay convenio, quitar descuentos
                editArticulosSeleccionados.forEach(articulo => {
                    articulo.descuento = 0;
                    articulo.id_convenio = null;
                });
                renderizarTablaArticulosEdit();
                
                if (window.mostrarToast) {
                    window.mostrarToast('Descuentos eliminados', 'info');
                }
                return;
            }
            
            // Buscar el convenio en el catálogo
            const convenio = editCatalogos.convenios?.find(c => c.id == convenioId);
            
            if (convenio && convenio.familias) {
                let articulosAfectados = 0;
                
                editArticulosSeleccionados.forEach(articulo => {
                    // Buscar si la familia del artículo tiene descuento en este convenio
                    const familiaConDescuento = convenio.familias.find(f => f.num_familia == articulo.num_familia);
                    
                    if (familiaConDescuento) {
                        articulo.descuento = familiaConDescuento.descuento;
                        articulo.id_convenio = convenio.id;
                        articulosAfectados++;
                    } else {
                        articulo.descuento = 0;
                        articulo.id_convenio = null;
                    }
                });
                
                renderizarTablaArticulosEdit();
                
                if (window.mostrarToast) {
                    if (articulosAfectados > 0) {
                        window.mostrarToast(`Convenio "${convenio.nombre}" aplicado a ${articulosAfectados} artículo(s)`, 'success');
                    } else {
                        window.mostrarToast(`Ningún artículo coincide con las familias del convenio "${convenio.nombre}"`, 'warning');
                    }
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast('No se pudo aplicar el convenio', 'danger');
                }
            }
        });
    }
    
    // Evento para cambio de sucursal asignada (afecta al stock disponible)
    const sucursalAsignadaSelect = document.getElementById('edit_sucursal_asignada_id');
    if (sucursalAsignadaSelect) {
        sucursalAsignadaSelect.addEventListener('change', function() {
            const nuevaSucursalAsignada = this.value;
            
            if (nuevaSucursalAsignada) {
                // Recalcular stock para todos los productos con la nueva sucursal asignada
                editArticulosSeleccionados.forEach((articulo, idx) => {
                    if (articulo.id_sucursal_surtido) {
                        actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
                    }
                });
            }
        });
    }
    
    // Evento para fase cancelada - cambiar certeza a 0%
    const faseSelect = document.getElementById('edit_fase_id');
    if (faseSelect) {
        faseSelect.addEventListener('change', function() {
            const faseSeleccionada = this.options[this.selectedIndex]?.text;
            const certezaSelect = document.getElementById('edit_certeza');
            
            // Verificar si la fase seleccionada es "Cancelada"
            if (faseSeleccionada === 'Cancelada' && certezaSelect) {
                const certezaActual = parseInt(certezaSelect.value || 0);
                
                if (certezaActual !== 0) {
                    // Cambiar certeza a 0%
                    certezaSelect.value = '0';
                    
                    if (window.mostrarToast) {
                        window.mostrarToast(
                            'La fase "Cancelada" ha sido seleccionada. La certeza se ha ajustado automáticamente a 0% para liberar los productos apartados.', 
                            'info'
                        );
                    }
                    
                    // Recalcular stock para todos los productos (liberar apartados)
                    editArticulosSeleccionados.forEach((articulo, idx) => {
                        if (articulo.id_sucursal_surtido) {
                            actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
                        }
                    });
                }
            }
        });
    }
});
</script>
@endpush