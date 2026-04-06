<!-- Modal Nueva Cotización -->
<div class="modal fade" id="modalNuevaCotizacion" tabindex="-1" aria-labelledby="modalNuevaCotizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaCotizacionLabel">
                    <i class="bi bi-plus-circle"></i> Nueva Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaCotizacion">
                    @csrf
                    
                    <!-- Cliente con buscador -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-person"></i> Datos del Cliente</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Buscar cliente <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <div class="search-box flex-grow-1">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="buscarClienteCotizacion" 
                                            placeholder="Buscar por nombre o email..."
                                            autocomplete="off">
                                    </div>
                                    <button type="button" class="btn btn-primary" id="btnMostrarNuevoCliente">
                                        <i class="bi bi-plus-circle"></i> Nuevo Cliente
                                    </button>
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para seleccionarlo.</small>

                                <!-- FORMULARIO PARA NUEVO CLIENTE (oculto inicialmente) -->
                                <div id="formNuevoClienteContainer" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                                    <h6 class="mb-3"><i class="bi bi-person-plus"></i> Registrar nuevo cliente</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_nombre" 
                                                placeholder="Nombre *"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_apellido_paterno" 
                                                placeholder="Apellido paterno *"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_apellido_materno" 
                                                placeholder="Apellido materno"
                                                autocomplete="off"
                                                onkeypress="return window.soloLetras(event)"
                                                onkeyup="window.aMayusculas(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="email" class="form-control" id="nuevo_cliente_email" 
                                                placeholder="Correo electrónico"
                                                autocomplete="off">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="tel" class="form-control" id="nuevo_cliente_telefono" 
                                                placeholder="Teléfono"
                                                autocomplete="off"
                                                onkeypress="return window.soloNumeros(event)"
                                                onpaste="window.prevenirPegadoInvalido(event, /[0-9+\-\s]/);">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="nuevo_cliente_domicilio" 
                                                placeholder="Domicilio"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <button type="button" class="btn btn-secondary" id="btnCancelarNuevoCliente">Cancelar</button>
                                        <button type="button" class="btn btn-success" id="btnGuardarNuevoCliente">Guardar y seleccionar</button>
                                    </div>
                                </div>
                                
                                <!-- Resultados de búsqueda -->
                                <div id="resultadosClientes" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Clientes encontrados</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaClientes"></div>
                                    </div>
                                </div>
                                
                                <!-- Cliente seleccionado -->
                                <div id="clienteSeleccionado" class="mt-2 p-2 bg-light rounded" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Cliente seleccionado:</strong>
                                            <p class="mb-0" id="clienteInfo"></p>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarCliente()">
                                            <i class="bi bi-x"></i> Cambiar
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="cliente_id" name="cliente_id">
                            </div>
                        </div>
                    </div>

                    <!-- Datos de la cotización -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-info-circle"></i> Información de la Cotización</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fase <span class="text-danger">*</span></label>
                                    <select class="form-select" id="fase_id" name="fase_id" required>
                                        <option value="">Seleccionar fase...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Clasificación</label>
                                    <select class="form-select" id="clasificacion_id" name="clasificacion_id">
                                        <option value="">Seleccionar clasificación...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sucursal asignada</label>
                                    <select class="form-select" id="sucursal_asignada_id" name="sucursal_asignada_id">
                                        <option value="">Seleccionar sucursal...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Certeza</label>
                                    <select class="form-select" id="certeza" name="certeza">
                                        <option value="1">Baja</option>
                                        <option value="2">Media</option>
                                        <option value="3">Alta</option>
                                    </select>
                                    <small class="text-muted">Si la certeza es <b>alta</b>, los productos se apartarán</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Convenio</label>
                                    <select class="form-select" id="convenio_general" name="convenio_general">
                                        <option value="">Sin convenio</option>
                                    </select>
                                    <small class="text-muted">Selecciona un convenio para aplicar los descuentos</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Comentarios</label>
                                    <textarea class="form-control" id="comentarios" name="comentarios" rows="2" 
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
                                    <input type="text" class="form-control" id="buscarArticuloModal" 
                                           placeholder="Buscar artículo por código o descripción..." autocomplete="off">
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para agregarlo.</small>
                                
                                <div id="resultadosArticulos" class="mt-2" style="display: none;">
                                    <div class="card">
                                        <div class="card-header bg-light py-2">
                                            <small class="fw-bold">Artículos encontrados (haz clic para agregar)</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaArticulos"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de artículos -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                            <th style="width: 5%">#</th>
                                            <th style="width: 15%">Código</th>
                                            <th style="width: 35%">Descripción</th>
                                            <th style="width: 10%" class="text-center">Cantidad</th>
                                            <th style="width: 15%" class="text-end">Precio</th>
                                            <th style="width: 15%" class="text-end">Importe</th>
                                            <th style="width: 5%" class="text-center">Acciones</th>
                                        </thead>
                                    <tbody id="articulosBody">
                                        <tr id="sin-articulos-row">
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">No hay artículos agregados</p>
                                             </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                            <td colspan="5" class="text-end fw-bold">Total:</td>
                                            <td class="text-end fw-bold" id="totalCotizacion">$0.00</td>
                                            <td></td>
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
                <button type="button" class="btn btn-success" onclick="guardarNuevaCotizacion()">
                    <i class="bi bi-save"></i> Guardar Cotización
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================
// VARIABLES GLOBALES DEL MODAL
// ============================================
let articulosSeleccionados = [];
let catalogos = {
    fases: [],
    clasificaciones: [],
    sucursales: [],
    convenios: []
};
let esNuevaVersion = false;
let cotizacionOrigenId = null;

// Función para establecer el modo nueva versión desde fuera del modal
window.setEsNuevaVersion = function(valor, origenId) {
    esNuevaVersion = valor;
    cotizacionOrigenId = origenId;
    console.log('Modo nueva versión activado:', esNuevaVersion, 'Origen ID:', cotizacionOrigenId);
};

// ============================================
// CARGA DE CATÁLOGOS
// ============================================
function cargarCatalogos() {
    console.log('Cargando catálogos...');
    fetch('{{ route("ventas.cotizaciones.catalogos") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Catálogos recibidos:', data);
        if (data.success) {
            catalogos = data.data;
            
            const faseSelect = document.getElementById('fase_id');
            const clasificacionSelect = document.getElementById('clasificacion_id');
            const sucursalSelect = document.getElementById('sucursal_asignada_id');
            const convenioGeneralSelect = document.getElementById('convenio_general');
            
            if (faseSelect && catalogos.fases) {
                faseSelect.innerHTML = '<option value="">Seleccionar fase...</option>' + 
                    catalogos.fases.map(f => `<option value="${f.id_fase}">${f.fase}</option>`).join('');
            }
            
            if (clasificacionSelect && catalogos.clasificaciones) {
                clasificacionSelect.innerHTML = '<option value="">Seleccionar clasificación...</option>' + 
                    catalogos.clasificaciones.map(c => `<option value="${c.id_clasificacion}">${c.clasificacion}</option>`).join('');
            }
            
            if (sucursalSelect && catalogos.sucursales) {
                sucursalSelect.innerHTML = '<option value="">Seleccionar sucursal...</option>' + 
                    catalogos.sucursales.map(s => `<option value="${s.id_sucursal}">${s.nombre}</option>`).join('');
            }
            
            if (convenioGeneralSelect && catalogos.convenios) {
                convenioGeneralSelect.innerHTML = '<option value="">Sin convenio</option>' + 
                    catalogos.convenios.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            }
        }
    })
    .catch(error => console.error('Error al cargar catálogos:', error));
}

// ============================================
// FUNCIONES PARA CLIENTES
// ============================================
let timeoutBusquedaCliente;

function buscarClientes(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosClientes').style.display = 'none';
        return;
    }
    
    fetch(`{{ route("ventas.cotizaciones.clientes.buscar") }}?q=${encodeURIComponent(termino)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('resultadosClientes');
        const listaResultados = document.getElementById('listaClientes');
        
        if (data.success && data.data.length > 0) {
            listaResultados.innerHTML = data.data.map(cliente => `
                <div class="list-group-item list-group-item-action" 
                     onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre.replace(/'/g, "\\'")}', '${cliente.contacto_principal}', '${cliente.contacto_html.replace(/'/g, "\\'")}')"
                     style="cursor: pointer;">
                    <div>
                        <strong>${cliente.nombre}</strong>
                        <br><small class="text-muted">${cliente.contacto_html}</small>
                    </div>
                </div>
            `).join('');
            resultadosDiv.style.display = 'block';
        } else {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
            resultadosDiv.style.display = 'block';
        }
    })
    .catch(error => console.error('Error buscando clientes:', error));
}

window.seleccionarCliente = function(id, nombre, contactoPrincipal, contactoHtml) {
    document.getElementById('cliente_id').value = id;
    // Mostrar el HTML completo en el cliente seleccionado
    document.getElementById('clienteInfo').innerHTML = `<strong>${nombre}</strong><br><small>${contactoHtml || contactoPrincipal}</small>`;
    document.getElementById('clienteSeleccionado').style.display = 'block';
    document.getElementById('resultadosClientes').style.display = 'none';
    document.getElementById('buscarClienteCotizacion').value = nombre;
};

window.limpiarCliente = function() {
    document.getElementById('cliente_id').value = '';
    document.getElementById('clienteSeleccionado').style.display = 'none';
    document.getElementById('buscarClienteCotizacion').value = '';
};

// ============================================
// FUNCIONES PARA NUEVO CLIENTE RÁPIDO
// ============================================

// Mostrar/ocultar formulario de nuevo cliente
const btnMostrarNuevoCliente = document.getElementById('btnMostrarNuevoCliente');
if (btnMostrarNuevoCliente) {
    btnMostrarNuevoCliente.addEventListener('click', function() {
        const container = document.getElementById('formNuevoClienteContainer');
        if (container) {
            const isVisible = container.style.display !== 'none';
            container.style.display = isVisible ? 'none' : 'block';
            // Limpiar campos al mostrar
            if (!isVisible) {
                document.getElementById('nuevo_cliente_nombre').value = '';
                document.getElementById('nuevo_cliente_apellido_paterno').value = '';
                document.getElementById('nuevo_cliente_apellido_materno').value = '';
                document.getElementById('nuevo_cliente_email').value = '';
                document.getElementById('nuevo_cliente_telefono').value = '';
                document.getElementById('nuevo_cliente_domicilio').value = '';
            }
        }
    });
}

// Cancelar nuevo cliente
const btnCancelarNuevoCliente = document.getElementById('btnCancelarNuevoCliente');
if (btnCancelarNuevoCliente) {
    btnCancelarNuevoCliente.addEventListener('click', function() {
        document.getElementById('formNuevoClienteContainer').style.display = 'none';
    });
}

// Guardar nuevo cliente y seleccionarlo automáticamente
const btnGuardarNuevoCliente = document.getElementById('btnGuardarNuevoCliente');
if (btnGuardarNuevoCliente) {
    btnGuardarNuevoCliente.addEventListener('click', function() {
        const nombre = document.getElementById('nuevo_cliente_nombre').value.trim();
        const apellidoPaterno = document.getElementById('nuevo_cliente_apellido_paterno').value.trim();
        const apellidoMaterno = document.getElementById('nuevo_cliente_apellido_materno').value.trim();
        const email = document.getElementById('nuevo_cliente_email').value.trim();
        const telefono = document.getElementById('nuevo_cliente_telefono').value.trim();
        const domicilio = document.getElementById('nuevo_cliente_domicilio').value.trim();
        
        // Validar campos requeridos
        if (!nombre) {
            if (window.mostrarToast) window.mostrarToast('El nombre es requerido', 'warning');
            return;
        }
        if (!apellidoPaterno) {
            if (window.mostrarToast) window.mostrarToast('El apellido paterno es requerido', 'warning');
            return;
        }
        
        // Deshabilitar botón mientras se guarda
        const btn = this;
        const textoOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
        
        // Enviar datos al servidor
        fetch('{{ route("clientes.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                Nombre: nombre,
                apPaterno: apellidoPaterno,
                apMaterno: apellidoMaterno || null,
                email1: email || null,
                telefono1: telefono || null,
                Domicilio: domicilio || null,
                status: 'CLIENTE'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cliente = data.data;
                const idCliente = cliente.id_Cliente;
                const nombreCompleto = `${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}`.trim();
                const emailCliente = cliente.email1 || '';
                
                // Seleccionar el cliente automáticamente
                if (typeof window.seleccionarCliente === 'function') {
                    window.seleccionarCliente(idCliente, nombreCompleto, emailCliente);
                } else {
                    // Fallback
                    document.getElementById('cliente_id').value = idCliente;
                    document.getElementById('clienteInfo').innerHTML = `<strong>${nombreCompleto}</strong><br><small>${emailCliente}</small>`;
                    document.getElementById('clienteSeleccionado').style.display = 'block';
                    document.getElementById('resultadosClientes').style.display = 'none';
                    document.getElementById('buscarClienteCotizacion').value = nombreCompleto;
                }
                
                // Ocultar formulario
                document.getElementById('formNuevoClienteContainer').style.display = 'none';
                
                if (window.mostrarToast) {
                    window.mostrarToast(`Cliente "${nombreCompleto}" creado correctamente`, 'success');
                }
            } else {
                // Mostrar errores de validación
                if (data.errors) {
                    const errores = Object.values(data.errors).flat().join(', ');
                    if (window.mostrarToast) window.mostrarToast(`Error: ${errores}`, 'danger');
                } else {
                    if (window.mostrarToast) window.mostrarToast(data.message || 'Error al crear el cliente', 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión al crear el cliente', 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        });
    });
}

// ============================================
// FUNCIONES PARA ARTÍCULOS
// ============================================
let timeoutBusquedaArticulo;

function buscarArticulos(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosArticulos').style.display = 'none';
        return;
    }
    
    const sucursalAsignadaId = document.getElementById('sucursal_asignada_id')?.value || '';
    
    let url = `{{ route("ventas.cotizaciones.productos.buscar") }}?q=${encodeURIComponent(termino)}&sucursal_asignada_id=${sucursalAsignadaId}`;
    
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('resultadosArticulos');
        const listaResultados = document.getElementById('listaArticulos');
        
        if (resultadosDiv && listaResultados) {
            if (data.success && data.data && data.data.length > 0) {
                window.resultadosBusqueda = data.data;
                
                listaResultados.innerHTML = data.data.map((articulo, idx) => {
                    // Verificar si ya existe en la misma sucursal (para mostrar advertencia, no para deshabilitar)
                    const yaExiste = articulosSeleccionados.some(a => 
                        a.id_producto === articulo.id && 
                        a.id_sucursal_surtido === articulo.id_sucursal
                    );
                    const esSucursalAsignada = articulo.id_sucursal == sucursalAsignadaId;
                    const stockClass = articulo.inventario > 0 ? 'text-success' : 'text-danger';
                    const badgeClass = esSucursalAsignada ? 'bg-primary' : 'bg-secondary';
                    const apartadoBadge = articulo.apartado > 0 ? 
                        `<span class="badge bg-warning ms-1">Apartado: ${articulo.apartado}</span>` : '';
                    const existenteBadge = yaExiste ? 
                        '<span class="badge bg-warning ms-1">Ya agregado (se sumará)</span>' : '';
                    
                    return `
                        <div class="list-group-item list-group-item-action" 
                             onclick="agregarArticuloPorIndice(${idx})"
                             style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${escapeHtml(articulo.nombre)}</strong>
                                    <br><small class="text-muted">Código: ${escapeHtml(articulo.codbar || 'N/A')} | Precio: $${articulo.precio.toFixed(2)}</small>
                                    <br><small class="text-muted">Familia: ${escapeHtml(articulo.num_familia || 'N/A')}</small>
                                    <br><span class="badge ${badgeClass} me-1">${escapeHtml(articulo.nombre_sucursal)}</span>
                                    <span class="badge ${stockClass}">Stock disponible: ${articulo.inventario}</span>
                                    ${apartadoBadge}
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

window.agregarArticuloPorIndice = function(idx) {
    if (!window.resultadosBusqueda || !window.resultadosBusqueda[idx]) return;
    
    const articuloData = window.resultadosBusqueda[idx];
    
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
    
    // Aplicar convenio general si existe
    const convenioSelect = document.getElementById('convenio_general');
    if (convenioSelect && convenioSelect.value && catalogos.convenios) {
        const convenio = catalogos.convenios.find(c => c.id == convenioSelect.value);
        if (convenio && convenio.familias) {
            const familiaConDescuento = convenio.familias.find(f => f.num_familia === nuevoArticulo.num_familia);
            if (familiaConDescuento) {
                nuevoArticulo.descuento = familiaConDescuento.descuento;
                nuevoArticulo.id_convenio = convenio.id;
            }
        }
    }
    
    // Usar función unificada
    agregarOSumarArticulo(nuevoArticulo, articulosSeleccionados, false);
    
    // Limpiar buscador
    document.getElementById('buscarArticuloModal').value = '';
    document.getElementById('resultadosArticulos').style.display = 'none';
};

// Funcion generica para buscar y sumar articulo
function agregarOSumarArticulo(articulo, listaArticulos, esEdicion = false) {
    const existe = listaArticulos.find(a => 
        a.id_producto === articulo.id_producto && 
        parseInt(a.id_sucursal_surtido) === parseInt(articulo.id_sucursal_surtido)
    );
    
    if (existe) {
        // Producto ya existe - sumar cantidades
        const nuevaCantidad = existe.cantidad + 1;
        const maxDisponible = existe.inventario_disponible;
        
        if (nuevaCantidad <= maxDisponible) {
            existe.cantidad = nuevaCantidad;
            if (window.mostrarToast) {
                window.mostrarToast(
                    `Sumado 1 unidad a "${articulo.nombre}". Total: ${nuevaCantidad} unidades.`, 
                    'info'
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
    
    if (esEdicion) {
        if (typeof renderizarTablaArticulosEdit === 'function') {
            renderizarTablaArticulosEdit();
        }
    } else {
        renderizarTablaArticulos();
    }
}

window.eliminarArticulo = function(index) {
    articulosSeleccionados.splice(index, 1);
    renderizarTablaArticulos();
};

window.actualizarCantidad = function(index, cantidad) {
    const articulo = articulosSeleccionados[index];
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
    
    renderizarTablaArticulos();
};

function renderizarTablaArticulos() {
    const tbody = document.getElementById('articulosBody');
    if (!tbody) return;
    
    let totalGeneral = 0;
    
    if (articulosSeleccionados.length === 0) {
        tbody.innerHTML = `<tr id="sin-articulos-row">
            <td colspan="7" class="text-center py-4">
                <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">No hay artículos agregados</p>
            <\/td>
        <\/tr>`;
        document.getElementById('totalCotizacion').textContent = '$0.00';
        return;
    }
    
    let html = '';
    articulosSeleccionados.forEach((articulo, index) => {
        const precioConDescuento = articulo.precio * (1 - articulo.descuento / 100);
        const importe = articulo.cantidad * precioConDescuento;
        totalGeneral += importe;
        
        html += `
            <tr id="articulo-row-${index}">
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
                           onchange="actualizarCantidad(${index}, this.value)"
                           style="width: 80px;">
                <\/td>
                <td class="text-end">
                    <span class="fw-bold">$${precioConDescuento.toFixed(2)}<\/span>
                    ${articulo.precio !== precioConDescuento ? `<br><small class="text-muted text-decoration-line-through">$${articulo.precio.toFixed(2)}</small>` : ''}
                <\/td>
                <td class="text-end fw-bold">$${importe.toFixed(2)}<\/td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArticulo(${index})">
                        <i class="bi bi-trash"><\/i>
                    <\/button>
                <\/td>
            <\/tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalCotizacion').textContent = `$${totalGeneral.toFixed(2)}`;
}

// ============================================
// GUARDAR COTIZACIÓN
// ============================================
window.guardarNuevaCotizacion = function() {
    const clienteId = document.getElementById('cliente_id').value;
    const faseId = document.getElementById('fase_id').value;
    
    if (!clienteId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona un cliente', 'warning');
        return;
    }
    
    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }
    
    if (articulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }
    
    const articulos = articulosSeleccionados.map((a) => ({
        id_producto: a.id_producto,
        cantidad: a.cantidad,
        precio_unitario: a.precio,
        descuento: a.descuento,
        id_convenio: a.id_convenio,
        id_sucursal_surtido: a.id_sucursal_surtido
    }));
    
    let url = '{{ route("ventas.cotizaciones.store") }}';
    let method = 'POST';
    
    if (esNuevaVersion && cotizacionOrigenId) {
        url = `/ventas/cotizaciones/${cotizacionOrigenId}/guardar-version`;
        method = 'POST';
    }
    
    const formData = {
        id_cliente: parseInt(clienteId),
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('clasificacion_id').value || null,
        id_sucursal_asignada: document.getElementById('sucursal_asignada_id').value || null,
        certeza: parseInt(document.getElementById('certeza')?.value || 0),
        comentarios: document.getElementById('comentarios').value,
        articulos: articulos,
        _token: '{{ csrf_token() }}'
    };
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaCotizacion'));
            modal.hide();
            esNuevaVersion = false;
            cotizacionOrigenId = null;
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
// EVENT LISTENERS (UNIFICADO)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    cargarCatalogos();
    
    const buscadorClientes = document.getElementById('buscarClienteCotizacion');
    if (buscadorClientes) {
        buscadorClientes.addEventListener('input', function() {
            clearTimeout(timeoutBusquedaCliente);
            timeoutBusquedaCliente = setTimeout(() => buscarClientes(this.value), 300);
        });
    }
    
    const buscadorArticulos = document.getElementById('buscarArticuloModal');
    if (buscadorArticulos) {
        buscadorArticulos.addEventListener('input', function() {
            clearTimeout(timeoutBusquedaArticulo);
            timeoutBusquedaArticulo = setTimeout(() => buscarArticulos(this.value), 300);
        });
    }
    
    document.addEventListener('click', function(event) {
        const resultadosClientes = document.getElementById('resultadosClientes');
        const resultadosArticulos = document.getElementById('resultadosArticulos');
        const buscadorClientes = document.getElementById('buscarClienteCotizacion');
        const buscadorArticulos = document.getElementById('buscarArticuloModal');
        
        if (resultadosClientes && !resultadosClientes.contains(event.target) && event.target !== buscadorClientes) {
            resultadosClientes.style.display = 'none';
        }
        if (resultadosArticulos && !resultadosArticulos.contains(event.target) && event.target !== buscadorArticulos) {
            resultadosArticulos.style.display = 'none';
        }
    });
    
    // UN SOLO EVENTO show.bs.modal
    const modalElement = document.getElementById('modalNuevaCotizacion');
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function() {
            // Solo limpiar si NO es una nueva versión
            if (!esNuevaVersion) {
                console.log('Limpiando modal (nueva cotización normal)');
                if (typeof window.limpiarCliente === 'function') {
                    window.limpiarCliente();
                }
                articulosSeleccionados = [];
                renderizarTablaArticulos();
                document.getElementById('buscarArticuloModal').value = '';
                document.getElementById('resultadosArticulos').style.display = 'none';
                document.getElementById('fase_id').value = '';
                document.getElementById('clasificacion_id').value = '';
                document.getElementById('sucursal_asignada_id').value = '';
                document.getElementById('comentarios').value = '';
                document.getElementById('convenio_general').value = '';
                document.getElementById('certeza').value = '1';
            } else {
                console.log('Modal en modo nueva versión, NO se limpia');
            }
        });
        
        modalElement.addEventListener('hidden.bs.modal', function() {
            // Resetear banderas al cerrar
            esNuevaVersion = false;
            cotizacionOrigenId = null;
        });
    }
    
    const convenioGeneral = document.getElementById('convenio_general');
    if (convenioGeneral) {
        convenioGeneral.addEventListener('change', function() {
            const convenioId = this.value;
            
            if (convenioId && catalogos.convenios) {
                const convenio = catalogos.convenios.find(c => c.id == convenioId);
                if (convenio && convenio.familias) {
                    articulosSeleccionados.forEach((articulo) => {
                        const familiaConDescuento = convenio.familias.find(f => f.num_familia === articulo.num_familia);
                        if (familiaConDescuento) {
                            articulo.descuento = familiaConDescuento.descuento;
                            articulo.id_convenio = convenio.id;
                        } else {
                            articulo.descuento = 0;
                            articulo.id_convenio = null;
                        }
                    });
                    renderizarTablaArticulos();
                }
            } else {
                articulosSeleccionados.forEach(articulo => {
                    articulo.descuento = 0;
                    articulo.id_convenio = null;
                });
                renderizarTablaArticulos();
            }
        });
    }
});
</script>
@endpush