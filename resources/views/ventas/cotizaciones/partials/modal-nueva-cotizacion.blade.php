<!-- Modal Nueva Cotización -->
<div class="modal fade" id="modalNuevaCotizacion" tabindex="-1" aria-labelledby="modalNuevaCotizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaCotizacionLabel">
                    <i class="bi bi-plus-circle"></i> Nueva Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaCotizacion">
                    @csrf
                    
                    <!-- Cliente (con buscador) -->
                    <div class="mb-3">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <div class="search-box mb-2">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="buscarClienteCotizacion" 
                                   placeholder="Buscar cliente por nombre o email...">
                        </div>
                        <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para seleccionarlo.</small>
                        
                        <!-- Resultados de búsqueda de clientes -->
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

                    <!-- Fecha y Monto -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha y hora</label>
                            <input type="datetime-local" class="form-control" name="fecha_hora" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="monto" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Repartidor y Estado -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Repartidor</label>
                            <select class="form-select" name="repartidor">
                                <option value="">Seleccionar repartidor</option>
                                <option value="Emanuel Robles">Emanuel Robles</option>
                                <option value="Genaro Martínez">Genaro Martínez</option>
                                <option value="Juan Manuel">Juan Manuel</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="En proceso">En proceso</option>
                                <option value="Concretada">Concretada</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>

                    <!-- Clasificación -->
                    <div class="mb-3">
                        <label class="form-label">Clasificación</label>
                        <select class="form-select" name="clasificacion">
                            <option value="">Seleccionar clasificación</option>
                            <option value="Tienda">Tienda</option>
                            <option value="Programa de gobierno">Programa de gobierno</option>
                            <option value="Empresas">Empresas</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <!-- ARTÍCULOS - IGUAL QUE EN CLIENTES -->
                    <h6 class="mb-3">Artículos</h6>

                    <!-- Buscador de artículos -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="buscarArticuloModal" 
                                       placeholder="Buscar artículo para agregar...">
                            </div>
                            <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para agregarlo.</small>
                        </div>
                    </div>

                    <!-- Resultados de búsqueda de artículos -->
                    <div id="resultadosArticulos" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Resultados de búsqueda (haz clic para agregar)</small>
                            </div>
                            <div class="list-group list-group-flush" id="listaArticulos"></div>
                        </div>
                    </div>

                    <!-- Tabla de artículos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaArticulos">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Artículo</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="articulosBody">
                                <tr id="sin-articulos-row">
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No hay artículos agregados</p>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong id="totalCotizacion">$0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevaCotizacion()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // ============================================
    // VARIABLES LOCALES
    // ============================================
    let todosArticulos = [
        // Datos de ejemplo - Esto se reemplazará con datos reales de la BD
        { id: 1, nombre: 'Pañales bebé 1 año', precio: 110 },
        { id: 2, nombre: 'Toallitas húmedas', precio: 40 },
        { id: 3, nombre: 'Biberón 240 ml', precio: 85 },
        { id: 4, nombre: 'Leche en polvo', precio: 250 },
        { id: 5, nombre: 'Cobija para bebé', precio: 180 }
    ];
    
    let articulosSeleccionados = [];
    let clientesDisponibles = [
        // Datos de ejemplo - Se reemplazarán con datos reales
        { id: 1, nombre: 'Roberto Sánchez', email: 'roberto@email.com' },
        { id: 2, nombre: 'Ana Torres', email: 'ana@email.com' },
        { id: 3, nombre: 'Carlos González', email: 'carlos@email.com' }
    ];

    // ============================================
    // FUNCIONES PARA CLIENTES
    // ============================================
    function buscarClientes(termino) {
        if (!termino || termino.length < 2) {
            document.getElementById('resultadosClientes').style.display = 'none';
            return;
        }

        const resultados = clientesDisponibles.filter(c => 
            c.nombre.toLowerCase().includes(termino.toLowerCase()) ||
            c.email.toLowerCase().includes(termino.toLowerCase())
        );

        const resultadosDiv = document.getElementById('resultadosClientes');
        const listaResultados = document.getElementById('listaClientes');

        if (resultados.length === 0) {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
        } else {
            listaResultados.innerHTML = resultados.map(cliente => `
                <div class="list-group-item list-group-item-action" 
                     onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre}', '${cliente.email}')"
                     style="cursor: pointer;">
                    <div>
                        <strong>${cliente.nombre}</strong>
                        <br><small class="text-muted">${cliente.email}</small>
                    </div>
                </div>
            `).join('');
        }
        resultadosDiv.style.display = 'block';
    }

    window.seleccionarCliente = function(id, nombre, email) {
        document.getElementById('cliente_id').value = id;
        document.getElementById('clienteInfo').innerHTML = `<strong>${nombre}</strong><br><small>${email}</small>`;
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
    // FUNCIONES PARA ARTÍCULOS
    // ============================================
    function buscarArticulos(termino) {
        if (!termino || termino.length < 2) {
            document.getElementById('resultadosArticulos').style.display = 'none';
            return;
        }

        const resultados = todosArticulos.filter(a => 
            a.nombre.toLowerCase().includes(termino.toLowerCase())
        );

        const resultadosDiv = document.getElementById('resultadosArticulos');
        const listaResultados = document.getElementById('listaArticulos');

        if (resultados.length === 0) {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron artículos</div>';
        } else {
            listaResultados.innerHTML = resultados.map(articulo => {
                const yaExiste = articulosSeleccionados.some(a => a.id === articulo.id);
                return `
                    <div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                         onclick="${!yaExiste ? `agregarArticulo(${articulo.id}, '${articulo.nombre}', ${articulo.precio})` : ''}" 
                         style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${articulo.nombre}</strong>
                                <br><small class="text-muted">$${articulo.precio.toFixed(2)}</small>
                            </div>
                            ${yaExiste ? '<span class="badge bg-secondary">Ya agregado</span>' : '<span class="badge bg-success">Agregar</span>'}
                        </div>
                    </div>
                `;
            }).join('');
        }
        resultadosDiv.style.display = 'block';
    }

    window.agregarArticulo = function(id, nombre, precio) {
        if (articulosSeleccionados.some(a => a.id === id)) return;

        articulosSeleccionados.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });

        renderizarTablaArticulos();
        document.getElementById('buscarArticuloModal').value = '';
        document.getElementById('resultadosArticulos').style.display = 'none';
    };

    window.eliminarArticulo = function(id) {
        articulosSeleccionados = articulosSeleccionados.filter(a => a.id !== id);
        renderizarTablaArticulos();
    };

    window.actualizarCantidad = function(id, cantidad) {
        const articulo = articulosSeleccionados.find(a => a.id === id);
        if (articulo) {
            articulo.cantidad = Math.max(1, parseInt(cantidad) || 1);
            renderizarTablaArticulos();
        }
    };

    function renderizarTablaArticulos() {
        const tbody = document.getElementById('articulosBody');
        let total = 0;

        if (articulosSeleccionados.length === 0) {
            tbody.innerHTML = `
                <tr id="sin-articulos-row">
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No hay artículos agregados</p>
                    </td>
                </tr>
            `;
            document.getElementById('totalCotizacion').textContent = '$0.00';
            return;
        }

        let html = '';
        articulosSeleccionados.forEach((articulo, index) => {
            const subtotal = articulo.cantidad * articulo.precio;
            total += subtotal;
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${articulo.nombre}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" 
                               value="${articulo.cantidad}" min="1" 
                               onchange="actualizarCantidad(${articulo.id}, this.value)"
                               style="width: 80px;">
                    </td>
                    <td>$${articulo.precio.toFixed(2)}</td>
                    <td>$${subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="eliminarArticulo(${articulo.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        document.getElementById('totalCotizacion').textContent = `$${total.toFixed(2)}`;
    }

    // ============================================
    // FUNCIÓN PARA GUARDAR
    // ============================================
    window.guardarNuevaCotizacion = function() {
        const clienteId = document.getElementById('cliente_id').value;
        
        if (!clienteId) {
            if (window.mostrarToast) window.mostrarToast('Selecciona un cliente', 'warning');
            return;
        }

        if (articulosSeleccionados.length === 0) {
            if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
            return;
        }

        // Aquí iría la petición fetch para guardar
        if (window.mostrarToast) window.mostrarToast('Cotización guardada (simulado)', 'success');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaCotizacion'));
        modal.hide();
        setTimeout(() => location.reload(), 1000);
    };

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Buscador de clientes
        document.getElementById('buscarClienteCotizacion')?.addEventListener('input', function() {
            buscarClientes(this.value);
        });

        // Buscador de artículos
        document.getElementById('buscarArticuloModal')?.addEventListener('input', function() {
            buscarArticulos(this.value);
        });

        // Cerrar resultados al hacer clic fuera
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

        // Limpiar al abrir el modal
        const modal = document.getElementById('modalNuevaCotizacion');
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                limpiarCliente();
                articulosSeleccionados = [];
                renderizarTablaArticulos();
                document.getElementById('buscarArticuloModal').value = '';
                document.getElementById('resultadosArticulos').style.display = 'none';
            });
        }
    });
})();
</script>
@endpush