<!-- Modal Editar Cotización -->
<div class="modal fade" id="modalEditarCotizacion" tabindex="-1" aria-labelledby="modalEditarCotizacionLabel">
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
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Entrega</label>
                                    <input type="date" class="form-control" id="edit_fecha_entrega_sugerida" 
                                        name="fecha_entrega_sugerida">
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
                                        placeholder="Buscar por código de barras o descripción..."
                                        autocomplete="off"
                                        style="padding-right: 35px;">
                                </div>

                                <!-- Botón para mostrar/ocultar formulario de producto externo -->
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> Puedes buscar por nombre del producto, código EAN o sustancia activa
                                    </small>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="edit_btnMostrarExterno">
                                        <i class="bi bi-plus-circle"></i> Producto sobre pedido
                                    </button>
                                </div>

                                <!-- FORMULARIO PARA PRODUCTO EXTERNO (oculto inicialmente) -->
                                <div id="edit_formProductoExternoContainer" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                                    <h6 class="mb-3"><i class="bi bi-truck"></i> Registrar producto sobre pedido</h6>
                                    <div class="row">
                                        <div class="col-md-8 mb-2">
                                            <input type="text" class="form-control" id="edit_externo_descripcion" 
                                                placeholder="Descripción del producto *"
                                                autocomplete="off"
                                                onkeyup="window.aMayusculas(event)">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="number" class="form-control" id="edit_externo_precio" 
                                                placeholder="Precio *" 
                                                step="0.50"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <button type="button" class="btn btn-secondary" id="edit_btnCancelarExterno">Cancelar</button>
                                        <button type="button" class="btn btn-success" id="edit_btnGuardarExterno">Guardar producto</button>
                                    </div>
                                </div>
                                
                                <div id="edit_resultadosArticulos" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Artículos encontrados <b class="text-success">(HAZ CLICK PARA AGREGAR)</b></small>
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
                                            <th class="text-end">Importe</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_articulosBody">
                                        <tr id="edit-sin-articulos-row">
                                            <td colspan="8" class="text-center py-4">
                                                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">No hay artículos agregados</p>
                                            </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="edit_totalCotizacion">$0.00</td>
                                            <td colspan="1"></td>
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
let editCatalogosCargados = false; // Bandera para evitar recargas innecesarias
let renderTimeoutEdit; // Timeout para renderizado diferido
let editIncluirExternos = false;

// ============================================
// CARGA DE CATÁLOGOS CON REINTENTOS
// ============================================

let editCatalogosTimeout = null;
let editCatalogosIntentos = 0;
const MAX_EDIT_CATALOGOS_INTENTOS = 3;

function cargarCatalogosEdit() {
    // Si ya están cargados, devolver promesa resuelta inmediatamente
    if (editCatalogosCargados && editCatalogos.sucursales.length > 0) {
        return Promise.resolve({ success: true, data: editCatalogos });
    }
    
    // Si ya hay un timeout programado, cancelarlo
    if (editCatalogosTimeout) {
        clearTimeout(editCatalogosTimeout);
        editCatalogosTimeout = null;
    }
    
    return fetch('{{ route("ventas.cotizaciones.catalogos") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => {
        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Respuesta no es JSON (posible error 500)');
        }
        
        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Éxito - Resetear intentos
            editCatalogosIntentos = 0;
            
            editCatalogos = data.data;
            editCatalogosCargados = true;
            
            // Solo actualizar selects si existen
            const faseSelect = document.getElementById('edit_fase_id');
            const clasificacionSelect = document.getElementById('edit_clasificacion_id');
            const sucursalSelect = document.getElementById('edit_sucursal_asignada_id');
            const convenioGeneralSelect = document.getElementById('edit_convenio_general');
            
            if (faseSelect && editCatalogos.fases) {
                faseSelect.innerHTML = '<option value="">Seleccionar fase...</option>' + 
                    editCatalogos.fases.map(f => `<option value="${f.id_fase}">${f.fase}</option>`).join('');
            }
            if (clasificacionSelect && editCatalogos.clasificaciones) {
                clasificacionSelect.innerHTML = '<option value="">Seleccionar clasificación...</option>' + 
                    editCatalogos.clasificaciones.map(c => `<option value="${c.id_clasificacion}">${c.clasificacion}</option>`).join('');
            }
            if (sucursalSelect && editCatalogos.sucursales) {
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
        console.error('Error cargando catálogos para edición:', error);
        
        editCatalogosIntentos++;
        
        // Si no hemos superado el máximo de intentos, reintentar
        if (editCatalogosIntentos < MAX_EDIT_CATALOGOS_INTENTOS) {
            // No mostrar toast durante los reintentos
            if (editCatalogosTimeout) {
                clearTimeout(editCatalogosTimeout);
            }
            editCatalogosTimeout = setTimeout(() => {
                cargarCatalogosEdit();
            }, 5000); // Reintentar después de 5 segundos
            
            // Devolver una promesa rechazada con un mensaje de "reintentando"
            return Promise.reject({
                message: `Reintentando carga de catálogos (${editCatalogosIntentos}/${MAX_EDIT_CATALOGOS_INTENTOS})...`,
                retrying: true
            });
        } else {
            // Máximo de intentos alcanzado - resetear contador
            editCatalogosIntentos = 0;
            
            // Mostrar toast solo al fallar definitivamente
            if (window.mostrarToast) {
                window.mostrarToast('Error de conexión al cargar catálogos. Verifica tu conexión a internet e intenta de nuevo.', 'danger');
            }
            
            // Mostrar mensaje en los selects de edición
            const faseSelect = document.getElementById('edit_fase_id');
            const clasificacionSelect = document.getElementById('edit_clasificacion_id');
            const sucursalSelect = document.getElementById('edit_sucursal_asignada_id');
            const convenioGeneralSelect = document.getElementById('edit_convenio_general');
            
            const errorMsg = '<option value="">Error al cargar datos. Recarga la página.</option>';
            if (faseSelect) faseSelect.innerHTML = errorMsg;
            if (clasificacionSelect) clasificacionSelect.innerHTML = errorMsg;
            if (sucursalSelect) sucursalSelect.innerHTML = errorMsg;
            if (convenioGeneralSelect) convenioGeneralSelect.innerHTML = errorMsg;
            
            throw error;
        }
    });
}

// ============================================
// APLICAR CONVENIO GENERAL A TODOS LOS ARTÍCULOS
// ============================================
function aplicarConvenioGeneralEdit() {
    const convenioId = document.getElementById('edit_convenio_general')?.value;
    
    if (!convenioId) {
        editArticulosSeleccionados.forEach(articulo => {
            articulo.descuento = 0;
            articulo.id_convenio = null;
        });
        renderizarTablaArticulosEdit();
        return;
    }
    
    const convenio = editCatalogos.convenios?.find(c => c.id == convenioId);
    
    if (convenio && convenio.familias) {
        editArticulosSeleccionados.forEach(articulo => {
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
            window.mostrarToast(`Convenio "${convenio.nombre}" aplicado a los artículos correspondientes`, 'success');
        }
    }
}

// ============================================
// RECALCULAR STOCK CON APARTADOS (cuando cambia certeza)
// ============================================
function recalcularStockPorApartadoEdit() {
    const certeza = parseInt(document.getElementById('edit_certeza')?.value || 0);
    const aparta = certeza === 3;
    
    if (aparta) {
        editArticulosSeleccionados.forEach((articulo, idx) => {
            if (articulo.id_sucursal_surtido) {
                actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
            }
        });
        
        if (window.mostrarToast) {
            window.mostrarToast(
                'Los productos se apartarán automáticamente al guardar (solo cuando la certeza es Alta)', 
                'info'
            );
        }
    }
}

// ============================================
// CARGA DE DATOS DE LA COTIZACIÓN
// ============================================
window.cargarDatosEditarCotizacion = function(cotizacionData) {
    // Validación: si recibimos un ID en lugar del objeto, hacer fetch
    if (typeof cotizacionData === 'number' || typeof cotizacionData === 'string') {
        fetch(`/ventas/cotizaciones/${cotizacionData}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarDatosEditarCotizacion(data.data);
            } else {
                console.error('Error al obtener cotización:', data.message);
                if (window.mostrarToast) window.mostrarToast('Error al cargar la cotización', 'danger');
            }
        })
        .catch(error => {
            console.error('Error de red:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
        return;
    }
    
    // Si no hay datos, salir
    if (!cotizacionData || typeof cotizacionData !== 'object') {
        console.error('Datos de cotización inválidos:', cotizacionData);
        if (window.mostrarToast) window.mostrarToast('Datos de cotización inválidos', 'danger');
        return;
    }
    
    // Usar requestAnimationFrame para no bloquear el UI
    requestAnimationFrame(() => {
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val !== null && val !== undefined ? val : '';
        };
        const setText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text || '-';
        };
        
        // Datos básicos de la cotización
        setVal('edit_cotizacion_id', cotizacionData.id_cotizacion);
        setVal('edit_cliente_id', cotizacionData.id_cliente);
        
        // ============================================
        // MOSTRAR INFORMACIÓN COMPLETA DEL CLIENTE
        // ============================================
        let clienteHtml = '';
        if (cotizacionData.cliente) {
            const partes = [];
            if (cotizacionData.cliente.Nombre) partes.push(cotizacionData.cliente.Nombre);
            if (cotizacionData.cliente.apPaterno) partes.push(cotizacionData.cliente.apPaterno);
            if (cotizacionData.cliente.apMaterno) partes.push(cotizacionData.cliente.apMaterno);
            const nombreCompleto = partes.join(' ') || cotizacionData.cliente.nombre_completo || '-';
            
            clienteHtml = `<strong>${escapeHtml(nombreCompleto)}</strong>`;
            
            // Título
            if (cotizacionData.cliente.titulo && cotizacionData.cliente.titulo.trim() !== '') {
                clienteHtml += `<br><small class="text-muted">${escapeHtml(cotizacionData.cliente.titulo)}</small>`;
            }
            
            // Contacto
            let tieneContacto = false;
            let contactoHtml = '';
            
            if (cotizacionData.cliente.telefono1 && cotizacionData.cliente.telefono1.trim() !== '') {
                contactoHtml += `<i class="bi bi-telephone"></i> ${escapeHtml(cotizacionData.cliente.telefono1)}<br>`;
                tieneContacto = true;
            }
            if (cotizacionData.cliente.telefono2 && cotizacionData.cliente.telefono2.trim() !== '') {
                contactoHtml += `<i class="bi bi-telephone"></i> ${escapeHtml(cotizacionData.cliente.telefono2)} (secundario)<br>`;
                tieneContacto = true;
            }
            if (cotizacionData.cliente.email1 && cotizacionData.cliente.email1.trim() !== '') {
                contactoHtml += `<i class="bi bi-envelope"></i> ${escapeHtml(cotizacionData.cliente.email1)}`;
                tieneContacto = true;
            }
            
            if (tieneContacto) {
                clienteHtml += `<br><small class="text-muted">${contactoHtml}</small>`;
            }
            
            // Dirección
            if (cotizacionData.cliente.Domicilio && cotizacionData.cliente.Domicilio.trim() !== '') {
                clienteHtml += `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(cotizacionData.cliente.Domicilio)}</small>`;
            }
            
            // ============================================
            // INTERESES DEL CLIENTE
            // ============================================
            if (cotizacionData.cliente.intereses && cotizacionData.cliente.intereses.length > 0) {
                const interesesList = cotizacionData.cliente.intereses.map(i => escapeHtml(i)).join(', ');
                clienteHtml += `<br><small class="text-muted"><i class="bi bi-tags"></i> ${interesesList}</small>`;
            }
            
            // ============================================
            // PATOLOGÍAS DEL CLIENTE
            // ============================================
            if (cotizacionData.cliente.patologias && cotizacionData.cliente.patologias.length > 0) {
                const patologiasList = cotizacionData.cliente.patologias.map(p => escapeHtml(p)).join(', ');
                clienteHtml += `<br><small class="text-muted"><i class="bi bi-heart-pulse"></i> ${patologiasList}</small>`;
            }
        }
        
        const clienteInfoDiv = document.getElementById('edit_cliente_info');
        if (clienteInfoDiv) {
            clienteInfoDiv.innerHTML = clienteHtml || '<span class="text-muted">Sin información de cliente</span>';
        }
        
        setText('edit_folio', cotizacionData.folio);
        setText('edit_fecha_creacion', cotizacionData.fecha_creacion ? new Date(cotizacionData.fecha_creacion).toLocaleString() : '-');
        setVal('edit_comentarios', cotizacionData.comentarios);
        setVal('edit_certeza', cotizacionData.certeza || 0);
        // ============================================
        // ASIGNAR FECHA DE ENTREGA SUGERIDA
        // ============================================
        let fechaEntrega = cotizacionData.fecha_entrega_sugerida;
        if (fechaEntrega) {
            // Si es string ISO (contiene 'T'), extraer solo la fecha
            if (typeof fechaEntrega === 'string' && fechaEntrega.includes('T')) {
                fechaEntrega = fechaEntrega.split('T')[0];
            }
            // Si es un objeto Date
            else if (fechaEntrega instanceof Date) {
                fechaEntrega = fechaEntrega.toISOString().split('T')[0];
            }
            // Si ya es Y-m-d, no hacer nada
        } else {
            fechaEntrega = '';
        }

        setVal('edit_fecha_entrega_sugerida', fechaEntrega);
        
        if (cotizacionData.id_fase) setVal('edit_fase_id', cotizacionData.id_fase);
        if (cotizacionData.id_clasificacion) setVal('edit_clasificacion_id', cotizacionData.id_clasificacion);
        if (cotizacionData.id_sucursal_asignada) setVal('edit_sucursal_asignada_id', cotizacionData.id_sucursal_asignada);
        
        // Cargar los artículos
        editArticulosSeleccionados = [];

        if (cotizacionData.detalles && cotizacionData.detalles.length > 0) {
            for (const detalle of cotizacionData.detalles) {
                // Determinar si es externo 0/1
                const esExterno = detalle.es_externo == 1 || detalle.es_externo === true;
                
                let nombreSucursal = 'No asignada';
                let inventarioDisponible = 999;
                let numFamilia = esExterno ? 'EXT' : '';
                let detalleSucursales = '';
                
                if (esExterno) {
                    nombreSucursal = 'Pedido especial';
                    inventarioDisponible = 999;
                    detalleSucursales = 'No aplica (pedido a proveedor)';
                } else if (detalle.producto) {
                    // Obtener inventario del producto
                    inventarioDisponible = parseInt(detalle.producto.inventario || 0);
                    numFamilia = detalle.producto.num_familia || '';
                    nombreSucursal = detalle.sucursal_surtido?.nombre || detalle.producto?.sucursal?.nombre || 'No asignada';
                    
                    // Si el producto tiene desglose de sucursales, obtenerlo
                    if (detalle.producto.detalle_sucursales) {
                        detalleSucursales = detalle.producto.detalle_sucursales;
                    }
                } else if (detalle.sucursal_surtido) {
                    nombreSucursal = detalle.sucursal_surtido.nombre || 'No asignada';
                }
                
                // Si inventario_disponible es 0, intentar obtener del producto original (como fallback)
                if (inventarioDisponible === 0 && detalle.codbar) {
                    const productoEncontrado = window.resultadosBusqueda?.find(p => p.codbar === detalle.codbar);
                    if (productoEncontrado) {
                        inventarioDisponible = parseInt(productoEncontrado.inventario || 0);
                        detalleSucursales = productoEncontrado.detalle_sucursales || '';
                    }
                }
                
                editArticulosSeleccionados.push({
                    nombre: detalle.descripcion || '-',
                    codbar: detalle.codbar || '',
                    precio: parseFloat(detalle.precio_unitario || 0),
                    cantidad: parseInt(detalle.cantidad || 1),
                    descuento: parseFloat(detalle.descuento || 0),
                    id_convenio: detalle.id_convenio,
                    num_familia: numFamilia,
                    inventario_global: detalle.inventario_global ?? 0,
                    inventario_disponible: detalle.inventario_disponible ?? 0,
                    nombre_sucursal_surtido: detalle.nombre_sucursal_surtido || nombreSucursal || 'No asignada',
                    es_externo: esExterno ? 1 : 0,
                    detalle_sucursales: detalleSucursales || detalle.detalle_sucursales || ''
                });
            }
        }
        
        renderizarTablaArticulosEdit();
        
        // Seleccionar convenio general si todos los artículos tienen el mismo
        const conveniosUnicos = [...new Set(editArticulosSeleccionados.map(a => a.id_convenio).filter(id => id))];
        if (conveniosUnicos.length === 1) {
            setVal('edit_convenio_general', conveniosUnicos[0]);
        }
    });
};

// ============================================
// FUNCIONES PARA ARTÍCULOS (EDITAR)
// ============================================
let timeoutBusquedaArticuloEdit;

function buscarArticulosEdit(termino) {
    // Si el término está vacío o tiene menos de 3 caracteres
    if (!termino || termino.length < 3) {
        const resultadosDiv = document.getElementById('edit_resultadosArticulos');
        const listaResultados = document.getElementById('edit_listaArticulos');
        
        if (resultadosDiv && listaResultados) {
            if (termino && termino.length > 0 && termino.length < 3) {
                // Mostrar mensaje informativo si tiene 1 o 2 caracteres
                listaResultados.innerHTML = `<div class="list-group-item text-muted">Escribe al menos 3 caracteres para buscar</div>`;
                resultadosDiv.style.display = 'block';
            } else {
                resultadosDiv.style.display = 'none';
            }
        }
        return;
    }
    
    const sucursalAsignadaId = document.getElementById('edit_sucursal_asignada_id')?.value || '';
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value || '';
    
    // Usar la variable global editIncluirExternos
    const incluirExternosValue = editIncluirExternos ? 'true' : 'false';
    
    let url = `{{ route("ventas.cotizaciones.productos.buscar") }}?q=${encodeURIComponent(termino)}&sucursal_asignada_id=${sucursalAsignadaId}&cotizacion_id=${cotizacionId}&incluir_externos=${incluirExternosValue}`;

    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {        
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
                    const esExterno = articulo.es_externo == 1 || articulo.es_externo === true || articulo.es_externo === "1";
                    // Mostrar informacion de apartados si los hay
                    const stockClass = articulo.inventario > 0 ? 'text-success' : 'text-danger';
                    const badgeClass = esSucursalAsignada ? 'bg-primary' : (esExterno ? 'bg-info' : 'bg-secondary');
                    const apartadoInfo = articulo.apartado > 0 ? `<span class="badge bg-warning ms-1">Apartado: ${articulo.apartado}</span>` : '';
                    // Si ya existe, mostrar badge de advertencia pero permitir agregar (sumar)
                    const existenteBadge = yaExiste ? '<span class="badge bg-warning ms-1">Ya agregado (se sumará)</span>' : '';
                    const externoBadge = esExterno ? '<span class="badge bg-info ms-1">Sobre Pedido</span>' : '';
                    
                    const sustanciaInfo = articulo.sustancias_activas && articulo.sustancias_activas !== 'No coincide con la búsqueda' && articulo.sustancias_activas !== 'No es medicamento' && !esExterno
                        ? `<br><small class="text-info"><i class="bi bi-capsule"></i> Sustancia activa: <strong>${escapeHtml(articulo.sustancias_activas)}</strong></small>`
                        : '';
                    
                    // DESGLOSE DE SUCURSALES EN LA BÚSQUEDA
                    const detalleSucursalHtml = articulo.detalle_sucursales && articulo.detalle_sucursales !== '' && !esExterno
                        ? `<br><small class="text-muted"><i class="bi bi-building"></i> <b>Disponible por sucursal:</b> ${escapeHtml(articulo.detalle_sucursales)}</small>`
                        : '';
                    
                    // INVENTARIO GLOBAL ORIGINAL (sin descontar apartados)
                    const inventarioOriginal = articulo.inventario_original || articulo.inventario || 0;
                    
                    return `
                        <div class="list-group-item list-group-item-action" 
                             onclick="agregarArticuloEditPorIndice(${idx})"
                             style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${escapeHtml(articulo.nombre)}</strong>
                                    ${externoBadge}
                                    ${sustanciaInfo}
                                    <br><small class="text-muted"><strong>Código: </strong>${escapeHtml(articulo.codbar || 'N/A')} | 
                                        Precio: $${articulo.precio.toFixed(2)}
                                    </small>
                                    <br><small class="text-muted"><strong>Familia: </strong>${escapeHtml(articulo.nombre_familia || articulo.num_familia || 'N/A')}</small>
                                    <br><span class="badge ${badgeClass} me-1">${escapeHtml(articulo.nombre_sucursal)}</span>
                                    <span class="badge ${stockClass}">Stock: ${articulo.inventario}</span>
                                    ${apartadoInfo}
                                    ${existenteBadge}
                                    ${detalleSucursalHtml}
                                </div>
                                <span class="badge bg-success">Agregar</span>
                            </div>
                        </div>
                    `;
                }).join('');
                resultadosDiv.style.display = 'block';
            } else {
                // Término tiene 3 o más caracteres pero no hay resultados
                let mensaje = `No se encontraron artículos con "${escapeHtml(termino)}"`;
                listaResultados.innerHTML = `<div class="list-group-item text-muted">${mensaje}</div>`;
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

function abrirModalAgregarExternoEdit() {
    document.getElementById('edit_externo_descripcion').value = '';
    document.getElementById('edit_externo_precio').value = '';
    const modal = new bootstrap.Modal(document.getElementById('edit_modalAgregarExterno'));
    modal.show();
}


function guardarProductoExternoEdit() {
    const descripcion = document.getElementById('edit_externo_descripcion').value.trim();
    const precio = parseFloat(document.getElementById('edit_externo_precio').value);
    
    if (!descripcion) {
        if (window.mostrarToast) window.mostrarToast('Ingresa la descripción del producto', 'warning');
        return;
    }
    
    if (isNaN(precio) || precio <= 0) {
        if (window.mostrarToast) window.mostrarToast('Ingresa un precio válido', 'warning');
        return;
    }
    
    // Mostrar loading en el botón
    const btn = document.querySelector('#edit_modalAgregarExterno .btn-success');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    
    fetch('/ventas/cotizaciones/guardar-producto-externo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            descripcion: descripcion,
            precio: precio
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar el modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('edit_modalAgregarExterno'));
            if (modal) modal.hide();
            
            // Crear el objeto del artículo externo
            const nuevoArticulo = {
                nombre: data.data.descripcion,
                codbar: data.data.ean,
                precio: Number(parseFloat(data.data.precio).toFixed(2)),
                cantidad: 1,
                descuento: 0,
                id_convenio: null,
                id_sucursal_surtido: null,
                num_familia: 'EXT',
                inventario_disponible: 999,
                nombre_sucursal_surtido: 'Sobre pedido (externo)',
                es_externo: 1
            };
            
            // Agregar a la lista de artículos
            agregarOSumarArticulo(nuevoArticulo, editArticulosSeleccionados, true);
            
            if (window.mostrarToast) window.mostrarToast('Producto sobre pedido agregado correctamente', 'success');
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar el producto', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
}


window.agregarArticuloEditPorIndice = function(idx) {
    if (!window.resultadosBusquedaEdit || !window.resultadosBusquedaEdit[idx]) return;
    
    const articuloData = window.resultadosBusquedaEdit[idx];
    // Determinar si es externo
    const esExterno = articuloData.es_externo == 1 || articuloData.es_externo === true || articuloData.es_externo === "1";
    
    // USAR INVENTARIO GLOBAL (original) en lugar de disponible
    const inventarioReal = articuloData.inventario_original || articuloData.inventario || 0;
    // Si es externo, inventario_disponible = 999
    const maxDisponible = esExterno ? 999 : inventarioReal;
    
    // Verificar si hay stock disponible
    if (inventarioReal <= 0 && !esExterno) {
        if (window.mostrarToast) {
            window.mostrarToast('No hay stock disponible de este artículo', 'warning');
        }
        return;
    }
    
    // Obtener el desglose de sucursales
    const detalleSucursales = articuloData.detalle_sucursales || '';
    
    const nuevoArticulo = {
        nombre: articuloData.nombre,
        codbar: articuloData.codbar || '',
        precio: articuloData.precio,
        cantidad: 1,
        descuento: 0,
        id_convenio: null,
        id_sucursal_surtido: articuloData.id_sucursal || null,
        num_familia: articuloData.num_familia || (esExterno ? 'EXT' : ''),
        inventario_disponible: maxDisponible, // Máximo = inventario real
        nombre_sucursal_surtido: articuloData.nombre_sucursal || (esExterno ? 'Sobre Pedido' : 'No asignada'),
        es_externo: esExterno ? 1 : 0,
        detalle_sucursales: detalleSucursales // Guardar desglose
    };
    
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
    
    agregarOSumarArticulo(nuevoArticulo, editArticulosSeleccionados, true);
    
    const buscador = document.getElementById('edit_buscarArticulo');
    if (buscador) buscador.value = '';
    const resultadosDiv = document.getElementById('edit_resultadosArticulos');
    if (resultadosDiv) resultadosDiv.style.display = 'none';
};

// Función genérica para agregar o sumar producto
function agregarOSumarArticulo(articulo, listaArticulos, esEdicion = false) {
    const existe = listaArticulos.find(a => a.codbar === articulo.codbar && a.es_externo === articulo.es_externo);
    
    if (existe) {
        existe.cantidad += 1;
        if (window.mostrarToast) window.mostrarToast(`"${articulo.nombre}" ya está agregado. Se sumará 1 unidad.`, 'success');
    } else {
        listaArticulos.push(articulo);
        if (window.mostrarToast) window.mostrarToast(`Agregado "${articulo.nombre}" a la cotización.`, 'success');
    }
    
    // Renderizar según el contexto
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
        // Mostrar desglose completo en el toast
        let mensaje = `Solo hay ${maxDisponible} unidades disponibles.`;
        if (articulo.detalle_sucursales) {
            mensaje += `\nDistribución: ${articulo.detalle_sucursales}`;
        }
        
        if (window.mostrarToast) {
            window.mostrarToast(mensaje, 'warning');
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
    
    if (select) select.disabled = true;
    
    let url = `/ventas/cotizaciones/productos-por-sucursal/${sucursalId}`;
    url += `?ean=${encodeURIComponent(articulo.codbar)}`;
    if (cotizacionId) {
        url += `&cotizacion_id=${cotizacionId}`;
    }
    
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            const producto = data.data[0];
            const stockDisponible = producto.inventario || 0;
            
            let nombreSucursal = '';
            
            if (editCatalogos.sucursales) {
                const sucursalEncontrada = editCatalogos.sucursales.find(s => s.id_sucursal == sucursalId);
                if (sucursalEncontrada) {
                    nombreSucursal = sucursalEncontrada.nombre;
                }
            }
            
            if (!nombreSucursal && producto.nombre_sucursal && producto.nombre_sucursal !== 'N/A' && producto.nombre_sucursal !== 'Sin sucursal') {
                nombreSucursal = producto.nombre_sucursal;
            }
            
            if (!nombreSucursal) {
                const selectOption = select?.options[select.selectedIndex];
                nombreSucursal = selectOption ? selectOption.text : 'Sucursal';
            }
            
            const recalcularDescuentoPorConvenio = (articuloActual, nuevoProducto) => {
                const convenioId = document.getElementById('edit_convenio_general')?.value;
                if (convenioId && editCatalogos.convenios) {
                    const convenio = editCatalogos.convenios.find(c => c.id == convenioId);
                    if (convenio && convenio.familias) {
                        const familiaConDescuento = convenio.familias.find(f => f.num_familia == nuevoProducto.num_familia);
                        if (familiaConDescuento) {
                            articuloActual.descuento = familiaConDescuento.descuento;
                            articuloActual.id_convenio = convenio.id;
                        } else {
                            articuloActual.descuento = 0;
                            articuloActual.id_convenio = null;
                        }
                    }
                }
            };
            
            if (stockDisponible >= articulo.cantidad) {
                articulo.nombre = producto.nombre;
                articulo.codbar = producto.codbar;
                articulo.num_familia = producto.num_familia;
                articulo.precio = producto.precio;
                articulo.id_sucursal_surtido = parseInt(sucursalId);
                articulo.nombre_sucursal_surtido = nombreSucursal;
                articulo.inventario_disponible = stockDisponible;
                
                recalcularDescuentoPorConvenio(articulo, producto);
                renderizarTablaArticulosEdit();
                
                if (window.mostrarToast) {
                    const mensajeDescuento = articulo.descuento > 0 ? ` con ${articulo.descuento}% de descuento` : '';
                    window.mostrarToast(`Producto cambiado a ${nombreSucursal}. Stock disponible: ${stockDisponible} unidades${mensajeDescuento}.`, 'success');
                }
            } else if (stockDisponible > 0) {
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
                
                recalcularDescuentoPorConvenio(articulo, producto);
                renderizarTablaArticulosEdit();
            } else {
                const mensaje = `El producto "${articulo.nombre}" no tiene stock disponible en ${nombreSucursal}.`;
                if (window.mostrarToast) window.mostrarToast(mensaje, 'danger');
                if (select) select.value = articulo.id_sucursal_surtido || '';
            }
        } else {
            let nombreSucursal = '';
            
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
            if (window.mostrarToast) window.mostrarToast(mensaje, 'danger');
            if (select) select.value = articulo.id_sucursal_surtido || '';
        }
    })
    .catch(error => {
        console.error('Error al verificar stock:', error);
        if (window.mostrarToast) window.mostrarToast('Error al verificar stock en la sucursal', 'danger');
        if (select) select.value = articulo.id_sucursal_surtido || '';
    })
    .finally(() => {
        if (select) select.disabled = false;
    });
};

// Renderizado optimizado con debounce
function renderizarTablaArticulosEdit() {
    // Limpiar timeout anterior
    if (renderTimeoutEdit) clearTimeout(renderTimeoutEdit);
    
    // Ejecutar renderizado con debounce para evitar múltiples renders seguidos
    renderTimeoutEdit = setTimeout(() => {
        const tbody = document.getElementById('edit_articulosBody');
        if (!tbody) return;
        
        let totalGeneral = 0;
        
        if (editArticulosSeleccionados.length === 0) {
            tbody.innerHTML = `
                <tr id="edit-sin-articulos-row">
                    <td colspan="7" class="text-center py-4">
                        <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No hay artículos agregados</p>
                    </td>
                </tr>
            `;
            const totalSpan = document.getElementById('edit_totalCotizacion');
            if (totalSpan) totalSpan.textContent = '$0.00';
            return;
        }
        
        // Cache de sucursales para evitar recalcular en cada iteración
        const sucursalesMap = editCatalogos.sucursales ? 
            new Map(editCatalogos.sucursales.map(s => [s.id_sucursal, s.nombre])) : new Map();
        
        let html = '';
        for (let index = 0; index < editArticulosSeleccionados.length; index++) {
            const articulo = editArticulosSeleccionados[index];
            const esExterno = articulo.es_externo == 1;
            const precioConDescuento = articulo.precio * (1 - articulo.descuento / 100);
            const importe = articulo.cantidad * precioConDescuento;
            totalGeneral += importe;
            
            // Calcular máximo disponible
            let maxDisponible = articulo.inventario_global || articulo.inventario_disponible || 0;
            if (articulo.es_externo) {
                maxDisponible = 999;
            }

            // Si es 0, mostrar 999
            if (maxDisponible <= 0) {
                maxDisponible = 999;
            }
            
            // Generar HTML del desglose de sucursales
            let desgloseHtml = '';
            if (articulo.detalle_sucursales && articulo.detalle_sucursales !== '') {
                desgloseHtml = `<br><small class="text-muted"><i class="bi bi-building"></i> Disponible por sucursal: ${escapeHtml(articulo.detalle_sucursales)}</small>`;
            } else if (articulo.es_externo) {
                desgloseHtml = `<br><small class="text-muted"><i class="bi bi-building"></i> No aplica (pedido a proveedor)</small>`;
            }
            
            let sucursalesOptions = '';
            if (sucursalesMap.size > 0) {
                for (const [id, nombre] of sucursalesMap) {
                    const selected = articulo.id_sucursal_surtido == id ? 'selected' : '';
                    sucursalesOptions += `<option value="${id}" ${selected}>${escapeHtml(nombre)}</option>`;
                }
            }
            
            // Determinar si el precio es editable (solo para externos)
            const precioEditable = esExterno ? '' : 'readonly';
            const precioBg = esExterno ? '#fff3cd' : '#e9ecef';
            const precioBadge = esExterno ? '<span class="badge bg-info ms-1" style="font-size: 0.6rem;">editable</span>' : '';
            
            html += `
                <tr id="edit-articulo-row-${index}">
                    <td class="text-center">${index + 1}</td>
                    <td><small>${escapeHtml(articulo.codbar || '-')}</small></td>
                    <td>
                        <strong>${escapeHtml(articulo.nombre)}</strong>
                        ${articulo.es_externo ? '<br><span class="badge bg-info">Sobre Pedido</span>' : ''}
                        ${articulo.descuento > 0 ? `<br><small class="text-muted"><i class="bi bi-tag"></i> ${articulo.descuento}% descuento aplicado</small>` : ''}
                        <br><small class="text-muted">Máx: ${maxDisponible}</small>
                        ${desgloseHtml}
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center" 
                            value="${articulo.cantidad}" min="1" 
                            max="${maxDisponible}"
                            onchange="actualizarCantidadEdit(${index}, this.value)"
                            style="width: 80px;">
                    </td>
                    <td class="text-end">
                        <input type="number" step="0.01" class="form-control form-control-sm text-end edit-precio-cotizacion" 
                            value="${articulo.precio.toFixed(2)}" min="0" 
                            data-index="${index}"
                            ${precioEditable}
                            style="width: 120px; margin-left: auto; background-color: ${precioBg};">
                        ${precioBadge}
                        ${articulo.precio !== precioConDescuento ? `<br><small class="text-muted text-decoration-line-through">$${articulo.precio.toFixed(2)}</small>` : ''}
                    </td>
                    <td class="text-end fw-bold" id="edit-importe-${index}">$${importe.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArticuloEdit(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }
        
        tbody.innerHTML = html;
        const totalSpan = document.getElementById('edit_totalCotizacion');
        if (totalSpan) totalSpan.textContent = `$${totalGeneral.toFixed(2)}`;
        
        // Agregar event listeners para los precios editables
        document.querySelectorAll('.edit-precio-cotizacion').forEach(input => {
            input.addEventListener('input', function() {
                const index = parseInt(this.dataset.index);
                const val = parseFloat(this.value) || 0;
                if (val < 0) this.value = 0;
                
                const nuevoPrecio = parseFloat(this.value) || 0;
                
                // Actualizar en el array local
                editArticulosSeleccionados[index].precio = nuevoPrecio;
                
                // Actualizar solo el importe de la fila y el total
                actualizarImporteFilaEdit(index);
                
                // Si el producto es externo, actualizar en tmp_catalogo
                if (editArticulosSeleccionados[index].es_externo == 1) {
                    const codbar = editArticulosSeleccionados[index].codbar;
                    if (codbar && codbar.startsWith('T')) {
                        actualizarPrecioTmpCatalogo(codbar, nuevoPrecio);
                    }
                }
            });
        });
        
    }, 10); // Debounce de 10ms para agrupar renders múltiples
}

// Función para actualizar solo el importe de una fila y el total
function actualizarImporteFilaEdit(index) {
    const articulo = editArticulosSeleccionados[index];
    if (!articulo) return;
    
    const precioConDescuento = articulo.precio * (1 - articulo.descuento / 100);
    const importe = articulo.cantidad * precioConDescuento;
    
    // Actualizar importe de la fila
    const importeSpan = document.getElementById(`edit-importe-${index}`);
    if (importeSpan) {
        importeSpan.textContent = `$${importe.toFixed(2)}`;
    }
    
    // Recalcular total
    let totalGeneral = 0;
    for (const item of editArticulosSeleccionados) {
        const precioConDesc = item.precio * (1 - item.descuento / 100);
        totalGeneral += item.cantidad * precioConDesc;
    }
    
    const totalSpan = document.getElementById('edit_totalCotizacion');
    if (totalSpan) {
        totalSpan.textContent = `$${totalGeneral.toFixed(2)}`;
    }
}



// ============================================
// GUARDAR EDICIÓN (CORREGIDO)
// ============================================
window.guardarEdicionCotizacion = function() {
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value;
    const faseId = document.getElementById('edit_fase_id')?.value;

    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }

    if (typeof editArticulosSeleccionados === 'undefined' || editArticulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }

    // Mapear artículos - Asegurar que es_externo se envía correctamente
    const articulos = editArticulosSeleccionados.map((a) => ({
        codbar: a.codbar || a.ean || '',
        cantidad: parseInt(a.cantidad),
        precio_unitario: parseFloat(a.precio),
        descuento: parseFloat(a.descuento || 0),
        id_convenio: a.id_convenio ? parseInt(a.id_convenio) : null,
        id_sucursal: a.id_sucursal || null,
        es_externo: a.es_externo ? 1 : 0 
    }));

    const formData = {
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('edit_clasificacion_id')?.value || null,
        id_sucursal_asignada: document.getElementById('edit_sucursal_asignada_id')?.value || null,
        certeza: parseInt(document.getElementById('edit_certeza')?.value || 0),
        comentarios: document.getElementById('edit_comentarios')?.value || '',
        articulos: articulos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT',
        accion: 'editar'
    };

    if (window.mostrarToast) window.mostrarToast('Guardando cambios...', 'info');

    fetch(`/ventas/cotizaciones/${cotizacionId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (response.status === 422) {
            return response.json().then(err => {
                console.error('Errores de validación (422):', err);
                let mensajeError = 'Error de validación: ';
                if (err.errors) {
                    mensajeError += Object.values(err.errors).flat().join(', ');
                } else {
                    mensajeError += err.message || 'Datos inválidos';
                }
                if (window.mostrarToast) window.mostrarToast(mensajeError, 'danger');
                throw new Error(mensajeError);
            });
        }
        if (response.status === 409) {
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            
            setTimeout(() => {
                response.json().then(data => {
                    window.similitudData = data;
                    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmarCambios'));
                    modalConfirmacion.show();
                });
            }, 300);
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else if (data && !data.success && data.message) {
            console.error('Error del servidor:', data.message);
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        if (!error.message.includes('Error de validación')) {
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        }
    });
};

// ============================================
// EVENT LISTENERS
// ============================================
let editEventListenersInicializados = false;

function inicializarEventListenersEdit() {
    if (editEventListenersInicializados) return;
    editEventListenersInicializados = true;
    
    // Buscador de artículos
    const buscadorArticulos = document.getElementById('edit_buscarArticulo');
    if (buscadorArticulos) {
        buscadorArticulos.addEventListener('input', function() {
            const termino = this.value.trim();
            
            // Si el buscador está vacío, ocultar resultados inmediatamente
            if (termino === '') {
                const resultadosDiv = document.getElementById('edit_resultadosArticulos');
                if (resultadosDiv) {
                    resultadosDiv.style.display = 'none';
                }
                return;
            }
            
            // Si tiene contenido, buscar
            clearTimeout(timeoutBusquedaArticuloEdit);
            timeoutBusquedaArticuloEdit = setTimeout(() => buscarArticulosEdit(termino), 300);
        });
    }
    
    // Cerrar resultados al hacer clic fuera (MODIFICADO)
    document.addEventListener('click', function(event) {
        const resultados = document.getElementById('edit_resultadosArticulos');
        const buscador = document.getElementById('edit_buscarArticulo');
        
        // ✅ Solo cerrar si el clic no es en el buscador ni en los resultados
        if (resultados && buscador && 
            !resultados.contains(event.target) && 
            event.target !== buscador &&
            !buscador.contains(event.target)) {
            resultados.style.display = 'none';
        }
    });
    
    // Evento para cambio de certeza
    const certezaSelect = document.getElementById('edit_certeza');
    if (certezaSelect) {
        certezaSelect.addEventListener('change', function() {
            const nuevaCerteza = parseInt(this.value || 0);
            const aparta = nuevaCerteza === 3;
            
            if (aparta) {
                editArticulosSeleccionados.forEach((articulo, idx) => {
                    if (articulo.id_sucursal_surtido) {
                        actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
                    }
                });
            }
            
            if (window.mostrarToast) {
                window.mostrarToast(
                    aparta ? 'Los productos se apartarán automáticamente al guardar (Certeza Alta)' : 'Los productos ya no se apartarán', 
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
            
            const convenio = editCatalogos.convenios?.find(c => c.id == convenioId);
            
            if (convenio && convenio.familias) {
                let articulosAfectados = 0;
                
                editArticulosSeleccionados.forEach(articulo => {
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
        
        // Función para guardar producto externo desde el modal editar
        window.guardarProductoExternoEdit = function() {
            const descripcion = document.getElementById('edit_externo_descripcion')?.value.trim();
            const precio = document.getElementById('edit_externo_precio')?.value;
            
            if (!descripcion) {
                if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
                return;
            }
            
            if (!precio || parseFloat(precio) <= 0) {
                if (window.mostrarToast) window.mostrarToast('El precio es requerido y debe ser mayor a 0', 'warning');
                return;
            }
            
            fetch('{{ route("ventas.cotizaciones.guardar-producto-externo") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    descripcion: descripcion,
                    precio: parseFloat(precio)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('edit_modalAgregarExterno'));
                    if (modal) modal.hide();
                    
                    document.getElementById('edit_externo_descripcion').value = '';
                    document.getElementById('edit_externo_precio').value = '';
                    
                    const termino = document.getElementById('edit_buscarArticulo')?.value;
                    if (termino && termino.length >= 2) {
                        buscarArticulosEdit(termino);
                    }
                    
                    if (window.mostrarToast) window.mostrarToast('Producto sobre pedido guardado correctamente', 'success');
                } else {
                    if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
            });
        };
    }
    
    // Evento para cambio de sucursal asignada
    const sucursalAsignadaSelect = document.getElementById('edit_sucursal_asignada_id');
    if (sucursalAsignadaSelect) {
        sucursalAsignadaSelect.addEventListener('change', function() {
            const nuevaSucursalAsignada = this.value;
            
            if (nuevaSucursalAsignada) {
                editArticulosSeleccionados.forEach((articulo, idx) => {
                    if (articulo.id_sucursal_surtido) {
                        actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
                    }
                });
            }
        });
    }
    
    // Evento para fase cancelada
    const faseSelect = document.getElementById('edit_fase_id');
    if (faseSelect) {
        faseSelect.addEventListener('change', function() {
            const faseSeleccionada = this.options[this.selectedIndex]?.text;
            const certezaSelect = document.getElementById('edit_certeza');
            
            if (faseSeleccionada === 'Cancelada' && certezaSelect) {
                const certezaActual = parseInt(certezaSelect.value || 0);
                
                if (certezaActual !== 0) {
                    certezaSelect.value = '0';
                    
                    if (window.mostrarToast) {
                        window.mostrarToast(
                            'La fase "Cancelada" ha sido seleccionada. La certeza se ha ajustado automáticamente a 0% para liberar los productos apartados.', 
                            'info'
                        );
                    }
                    
                    editArticulosSeleccionados.forEach((articulo, idx) => {
                        if (articulo.id_sucursal_surtido) {
                            actualizarSucursalSurtidoEdit(idx, articulo.id_sucursal_surtido);
                        }
                    });
                }
            }
        });
    }

    // Evento del botón para abrir modal de producto externo
    const btnAgregarExternoEdit = document.getElementById('edit_btnAgregarExterno');
    if (btnAgregarExternoEdit) {
        btnAgregarExternoEdit.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('edit_modalAgregarExterno'));
            modal.show();
        });
    }
}

// Mostrar/ocultar formulario de producto externo
document.getElementById('edit_btnMostrarExterno')?.addEventListener('click', function() {
    const container = document.getElementById('edit_formProductoExternoContainer');
    if (container.style.display === 'none') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
});

document.getElementById('edit_btnCancelarExterno')?.addEventListener('click', function() {
    document.getElementById('edit_formProductoExternoContainer').style.display = 'none';
    document.getElementById('edit_externo_descripcion').value = '';
    document.getElementById('edit_externo_precio').value = '';
});

document.getElementById('edit_btnGuardarExterno')?.addEventListener('click', function() {
    const descripcion = document.getElementById('edit_externo_descripcion').value.trim();
    let precio = parseFloat(document.getElementById('edit_externo_precio').value);
    
    if (!descripcion) {
        if (window.mostrarToast) window.mostrarToast('Ingresa la descripción del producto', 'warning');
        return;
    }
    
    if (isNaN(precio) || precio <= 0) {
        if (window.mostrarToast) window.mostrarToast('Ingresa un precio válido', 'warning');
        return;
    }
    
    // Mostrar loading en el botón
    const btn = document.getElementById('edit_btnGuardarExterno');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    
    fetch('/ventas/cotizaciones/guardar-producto-externo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            descripcion: descripcion,
            precio: precio
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Crear el objeto en el formato que espera agregarArticuloEditPorIndice
            const articuloData = {
                id: data.data.id,
                id_sucursal: null,
                nombre_sucursal: null,
                codbar: data.data.ean,
                nombre: data.data.descripcion,
                precio: parseFloat(data.data.precio), // Asegurar número
                inventario: 999,
                num_familia: 'EXT',
                es_externo: true,
                es_medicamento: false,
                sustancias_activas: 'Producto sobre pedido'
            };
            
            // Agregar temporalmente a los resultados de búsqueda
            if (!window.resultadosBusquedaEdit) {
                window.resultadosBusquedaEdit = [];
            }
            window.resultadosBusquedaEdit.unshift(articuloData);
            
            // Agregar el artículo usando la función existente
            agregarArticuloEditPorIndice(0);
            
            // Ocultar formulario y limpiar
            document.getElementById('edit_formProductoExternoContainer').style.display = 'none';
            document.getElementById('edit_externo_descripcion').value = '';
            document.getElementById('edit_externo_precio').value = '';
            
            // Limpiar el buscador
            const buscador = document.getElementById('edit_buscarArticulo');
            if (buscador) buscador.value = '';
            
            // Ocultar resultados de búsqueda
            const resultadosDiv = document.getElementById('edit_resultadosArticulos');
            if (resultadosDiv) resultadosDiv.style.display = 'none';
            
            if (window.mostrarToast) window.mostrarToast('Producto sobre pedido guardado y agregado a la cotización', 'success');
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
});

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Cargar catálogos
    cargarCatalogosEdit();
    // Inicializar event listeners
    inicializarEventListenersEdit();
});
</script>
@endpush