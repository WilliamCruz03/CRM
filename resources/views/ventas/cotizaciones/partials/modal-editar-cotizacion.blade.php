<!-- Modal Editar Cotización -->
<div class="modal fade" id="modalEditarCotizacion" tabindex="-1" aria-labelledby="modalEditarCotizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarCotizacionLabel">
                    <i class="bi bi-pencil-square"></i> Editar Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCotizacion">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_cotizacion_id" name="cotizacion_id">
                    
                    <!-- Cliente (solo lectura) -->
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <div class="p-2 bg-light rounded" id="edit_cliente_info">
                            <strong id="edit_cliente_nombre">Roberto Sánchez</strong>
                            <br><small id="edit_cliente_email" class="text-muted">roberto@email.com</small>
                        </div>
                        <input type="hidden" id="edit_cliente_id" name="cliente_id">
                    </div>

                    <!-- Fecha y Monto -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha y hora</label>
                            <input type="datetime-local" class="form-control" id="edit_fecha_hora" name="fecha_hora" value="2026-02-17T08:30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_monto" name="monto" step="0.01" value="800.00">
                            </div>
                        </div>
                    </div>

                    <!-- Repartidor y Estado -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Repartidor</label>
                            <select class="form-select" id="edit_repartidor" name="repartidor">
                                <option value="Emanuel Robles">Emanuel Robles</option>
                                <option value="Genaro Martínez">Genaro Martínez</option>
                                <option value="Juan Manuel">Juan Manuel</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="edit_estado" name="estado">
                                <option value="En proceso">En proceso</option>
                                <option value="Concretada">Concretada</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>

                    <!-- Clasificación -->
                    <div class="mb-3">
                        <label class="form-label">Clasificación</label>
                        <select class="form-select" id="edit_clasificacion" name="clasificacion">
                            <option value="Tienda">Tienda</option>
                            <option value="Programa de gobierno">Programa de gobierno</option>
                            <option value="Empresas">Empresas</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <!-- ARTÍCULOS -->
                    <h6 class="mb-3">Artículos</h6>

                    <!-- Buscador de artículos -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="edit_buscarArticuloModal" 
                                       placeholder="Buscar artículo para agregar...">
                            </div>
                            <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para agregarlo.</small>
                        </div>
                    </div>

                    <!-- Resultados de búsqueda de artículos -->
                    <div id="edit_resultadosArticulos" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Resultados de búsqueda (haz clic para agregar)</small>
                            </div>
                            <div class="list-group list-group-flush" id="edit_listaArticulos"></div>
                        </div>
                    </div>

                    <!-- Tabla de artículos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="edit_tablaArticulos">
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
                            <tbody id="edit_articulosBody">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong id="edit_totalCotizacion">$345.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionCotizacion()">Guardar cambios</button>
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
        { id: 1, nombre: 'Pañales bebé 1 año', precio: 110 },
        { id: 2, nombre: 'Toallitas húmedas', precio: 40 },
        { id: 3, nombre: 'Biberón 240 ml', precio: 85 }
    ];
    
    let articulosSeleccionados = [];

    // ============================================
    // FUNCIÓN PARA CARGAR DATOS DE LA COTIZACIÓN
    // ============================================
    window.cargarDatosCotizacion = function(id) {
        // Simulación de carga de datos
        articulosSeleccionados = [
            { id: 1, nombre: 'Pañales bebé 1 año', precio: 110, cantidad: 2 },
            { id: 2, nombre: 'Toallitas húmedas', precio: 40, cantidad: 1 },
            { id: 3, nombre: 'Biberón 240 ml', precio: 85, cantidad: 1 }
        ];
        
        renderizarTablaArticulosEdit();
    };

    // ============================================
    // FUNCIONES PARA ARTÍCULOS
    // ============================================
    function buscarArticulosEdit(termino) {
        if (!termino || termino.length < 2) {
            document.getElementById('edit_resultadosArticulos').style.display = 'none';
            return;
        }

        const resultados = todosArticulos.filter(a => 
            a.nombre.toLowerCase().includes(termino.toLowerCase())
        );

        const resultadosDiv = document.getElementById('edit_resultadosArticulos');
        const listaResultados = document.getElementById('edit_listaArticulos');

        if (resultados.length === 0) {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron artículos</div>';
        } else {
            listaResultados.innerHTML = resultados.map(articulo => {
                const yaExiste = articulosSeleccionados.some(a => a.id === articulo.id);
                return `
                    <div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                         onclick="${!yaExiste ? `agregarArticuloEdit(${articulo.id}, '${articulo.nombre}', ${articulo.precio})` : ''}" 
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

    window.agregarArticuloEdit = function(id, nombre, precio) {
        if (articulosSeleccionados.some(a => a.id === id)) return;

        articulosSeleccionados.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });

        renderizarTablaArticulosEdit();
        document.getElementById('edit_buscarArticuloModal').value = '';
        document.getElementById('edit_resultadosArticulos').style.display = 'none';
    };

    window.eliminarArticuloEdit = function(id) {
        articulosSeleccionados = articulosSeleccionados.filter(a => a.id !== id);
        renderizarTablaArticulosEdit();
    };

    window.actualizarCantidadEdit = function(id, cantidad) {
        const articulo = articulosSeleccionados.find(a => a.id === id);
        if (articulo) {
            articulo.cantidad = Math.max(1, parseInt(cantidad) || 1);
            renderizarTablaArticulosEdit();
        }
    };

    function renderizarTablaArticulosEdit() {
        const tbody = document.getElementById('edit_articulosBody');
        let total = 0;

        if (articulosSeleccionados.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-box-seam text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No hay artículos agregados</p>
                    </td>
                </tr>
            `;
            document.getElementById('edit_totalCotizacion').textContent = '$0.00';
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
                               onchange="actualizarCantidadEdit(${articulo.id}, this.value)"
                               style="width: 80px;">
                    </td>
                    <td>$${articulo.precio.toFixed(2)}</td>
                    <td>$${subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="eliminarArticuloEdit(${articulo.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        document.getElementById('edit_totalCotizacion').textContent = `$${total.toFixed(2)}`;
    }

    // ============================================
    // FUNCIÓN PARA GUARDAR
    // ============================================
    window.guardarEdicionCotizacion = function() {
        if (window.mostrarToast) window.mostrarToast('Cotización actualizada (simulado)', 'success');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
        modal.hide();
        setTimeout(() => location.reload(), 1000);
    };

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Buscador de artículos
        document.getElementById('edit_buscarArticuloModal')?.addEventListener('input', function() {
            buscarArticulosEdit(this.value);
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(event) {
            const resultados = document.getElementById('edit_resultadosArticulos');
            const buscador = document.getElementById('edit_buscarArticuloModal');
            
            if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
                resultados.style.display = 'none';
            }
        });

        // Cargar datos al abrir el modal
        const modal = document.getElementById('modalEditarCotizacion');
        if (modal) {
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const cotizacionId = button.getAttribute('data-cotizacion-id');
                cargarDatosCotizacion(cotizacionId);
            });
        }
    });
})();
</script>
@endpush