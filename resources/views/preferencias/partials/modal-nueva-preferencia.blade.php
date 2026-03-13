<!-- Modal Nueva Preferencia -->
<div class="modal fade" id="modalNuevaPreferencia" tabindex="-1" aria-labelledby="modalNuevaPreferenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaPreferenciaLabel">
                    <i class="bi bi-plus-circle"></i> Registrar nueva preferencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaPreferencia">
                    @csrf
                    
                    <!-- Buscador de clientes -->
                    <div class="mb-3">
                        <label class="form-label">Buscar Cliente <span class="text-danger">*</span></label>
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="buscarClienteNuevoModal" 
                                   placeholder="Escribe nombre, apellidos o email...">
                        </div>
                        <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para seleccionarlo.</small>
                    </div>

                    <!-- Resultados de búsqueda de clientes -->
                    <div id="resultadosClientesNuevo" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Clientes encontrados</small>
                            </div>
                            <div class="list-group list-group-flush" id="listaClientesNuevo">
                                <!-- Resultados dinámicos -->
                            </div>
                        </div>
                    </div>

                    <!-- Cliente seleccionado -->
                    <div id="clienteSeleccionadoNuevo" class="mb-3 p-3 bg-light rounded" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Cliente seleccionado:</strong>
                                <p class="mb-0" id="clienteSeleccionadoInfo"></p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarClienteNuevo()">
                                <i class="bi bi-x"></i> Cambiar
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="nuevo_cliente_id" name="cliente_id">

                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="nueva_categoria" name="categoria" 
                               placeholder="Ej: Contacto, Notificaciones, Entregas">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detalle de preferencia <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="nueva_descripcion" name="descripcion" 
                                  rows="4" placeholder="Describe la preferencia del cliente..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevaPreferencia()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // ============================================
    // VARIABLES
    // ============================================
    let timeoutId = null;

    // ============================================
    // BUSCADOR DE CLIENTES
    // ============================================
    function buscarClientes(termino) {
        if (!termino || termino.length < 2) {
            document.getElementById('resultadosClientesNuevo').style.display = 'none';
            return;
        }

        fetch(`/clientes/buscar?q=${encodeURIComponent(termino)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const resultadosDiv = document.getElementById('resultadosClientesNuevo');
                const listaResultados = document.getElementById('listaClientesNuevo');

                if (data.data.length === 0) {
                    listaResultados.innerHTML = `<div class="list-group-item text-muted">No se encontraron clientes</div>`;
                } else {
                    listaResultados.innerHTML = data.data.map(cliente => {
                        const nombreCompleto = `${cliente.nombre} ${cliente.apellidos}`;
                        return `<div class="list-group-item list-group-item-action" 
                                onclick="seleccionarClienteNuevo(${cliente.id}, '${nombreCompleto}', '${cliente.email}')"
                                style="cursor: pointer;">
                                <div>
                                    <strong>${nombreCompleto}</strong>
                                    <br><small class="text-muted">${cliente.email}</small>
                                </div>
                            </div>`;
                    }).join('');
                }
                resultadosDiv.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Hacer funciones globales
    window.seleccionarClienteNuevo = function(id, nombreCompleto, email) {
        document.getElementById('nuevo_cliente_id').value = id;
        document.getElementById('clienteSeleccionadoInfo').innerHTML = 
            `<strong>${nombreCompleto}</strong><br><small>${email}</small>`;
        document.getElementById('clienteSeleccionadoNuevo').style.display = 'block';
        document.getElementById('resultadosClientesNuevo').style.display = 'none';
        document.getElementById('buscarClienteNuevoModal').value = nombreCompleto;
    };

    window.limpiarClienteNuevo = function() {
        document.getElementById('nuevo_cliente_id').value = '';
        document.getElementById('clienteSeleccionadoNuevo').style.display = 'none';
        document.getElementById('buscarClienteNuevoModal').value = '';
        document.getElementById('clienteSeleccionadoInfo').innerHTML = '';
    };

    window.guardarNuevaPreferencia = function() {
        const formData = {
            cliente_id: document.getElementById('nuevo_cliente_id')?.value,
            categoria: document.getElementById('nueva_categoria')?.value || '',
            descripcion: document.getElementById('nueva_descripcion')?.value || '',
            _token: '{{ csrf_token() }}'
        };

        if (!formData.cliente_id) {
            if (window.mostrarToast) window.mostrarToast('Selecciona un cliente', 'warning');
            return;
        }

        if (!formData.descripcion) {
            if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
            return;
        }

        fetch('/preferencias', {
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
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaPreferencia'));
                modal.hide();
                if (window.mostrarToast) window.mostrarToast('Preferencia registrada', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                if (window.mostrarToast) window.mostrarToast('Error al guardar', 'danger');
            }
        })
        .catch(error => {
            console.error(error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    };

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalNuevaPreferencia');
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                limpiarClienteNuevo();
                document.getElementById('nueva_categoria').value = '';
                document.getElementById('nueva_descripcion').value = '';
            });
        }

        const buscador = document.getElementById('buscarClienteNuevoModal');
        if (buscador) {
            buscador.addEventListener('input', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    buscarClientes(this.value);
                }, 300);
            });
        }

        document.addEventListener('click', function(event) {
            const resultados = document.getElementById('resultadosClientesNuevo');
            const buscador = document.getElementById('buscarClienteNuevoModal');
            if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
                resultados.style.display = 'none';
            }
        });
    });
})();
</script>
@endpush