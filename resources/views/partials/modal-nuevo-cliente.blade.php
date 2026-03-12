<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoClienteLabel">
                    <i class="bi bi-person-plus"></i> Nuevo Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoCliente">
                    @csrf
                    
                    <!-- Datos básicos del cliente -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Calle</label>
                        <input type="text" class="form-control" id="calle" name="calle">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Colonia/Barrio/Localidad</label>
                            <input type="text" class="form-control" id="colonia" name="colonia">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad/Municipio</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- SECCIÓN DE ENFERMEDADES - TABLA DINÁMICA -->
                    <h6 class="mb-3">Datos clínicos</h6>

                    <!-- Buscador de enfermedades -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="buscarEnfermedadNuevoModal" 
                                       placeholder="Buscar enfermedad para agregar (escribe al menos 2 caracteres)...">
                            </div>
                            <small class="text-muted">Los resultados aparecerán automáticamente al escribir. Haz clic en uno para agregarlo.</small>
                        </div>
                    </div>

                    <!-- Resultados de búsqueda -->
                    <div id="resultadosBusquedaNuevo" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Resultados de búsqueda (haz clic para agregar)</small>
                            </div>
                            <div class="list-group list-group-flush" id="listaResultadosNuevo">
                                <!-- Resultados dinámicos -->
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de enfermedades del nuevo cliente -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaEnfermedadesNuevoCliente">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Enfermedad</th>
                                    <th>Categoría</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="enfermedadesNuevoClienteBody">
                                <tr id="sin-enfermedades-nuevo-row">
                                    <td colspan="4" class="text-center py-4">
                                        <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No hay enfermedades agregadas</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Haz clic en cualquier resultado de búsqueda para agregar la enfermedad automáticamente.
                    </small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevoCliente()">Guardar</button>
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
    let todasEnfermedades = [];
    let enfermedadesNuevoCliente = [];

    // ============================================
    // FUNCIONES DE CARGA DE DATOS
    // ============================================
    function cargarCatalogoEnfermedades() {
        return fetch('/enfermedades/todas', { 
            headers: { 'Accept': 'application/json' } 
        })
        .then(response => response.json())
        .then(data => { 
            if (data.success) { 
                todasEnfermedades = data.data; 
                console.log('Catálogo cargado:', todasEnfermedades.length);
            } 
            return data; 
        })
        .catch(error => console.error('Error:', error));
    }

    // ============================================
    // FUNCIONES DE LA TABLA
    // ============================================
    function renderizarTablaEnfermedades() {
        const tbody = document.getElementById('enfermedadesNuevoClienteBody');
        if (!tbody) return;
        
        if (enfermedadesNuevoCliente.length === 0) {
            tbody.innerHTML = `<tr id="sin-enfermedades-nuevo-row">
                <td colspan="4" class="text-center py-4">
                    <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No hay enfermedades agregadas</p>
                </td>
            </tr>`;
            return;
        }
        
        let html = '';
        enfermedadesNuevoCliente.forEach((enf, index) => {
            html += `<tr id="nuevo-enfermedad-row-${enf.id}">
                <td>${index + 1}</td>
                <td>${enf.nombre}</td>
                <td><span class="badge bg-info">${enf.categoria}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                            onclick="window.eliminarEnfermedadNuevoCliente(${enf.id})" 
                            title="Eliminar enfermedad">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    // ============================================
    // FUNCIONES DE BÚSQUEDA Y AGREGADO
    // ============================================
    function buscarEnfermedades(termino) {
        if (!termino || termino.length < 2) { 
            document.getElementById('resultadosBusquedaNuevo').style.display = 'none'; 
            return; 
        }
        
        const resultados = todasEnfermedades.filter(enf => 
            enf.nombre.toLowerCase().includes(termino.toLowerCase()) || 
            (enf.categoria?.nombre || '').toLowerCase().includes(termino.toLowerCase())
        );
        
        const resultadosDiv = document.getElementById('resultadosBusquedaNuevo');
        const listaResultados = document.getElementById('listaResultadosNuevo');
        
        if (resultados.length === 0) {
            listaResultados.innerHTML = `<div class="list-group-item text-muted">
                <i class="bi bi-exclamation-circle"></i> No se encontraron resultados
            </div>`;
        } else {
            listaResultados.innerHTML = resultados.map(enf => {
                const yaExiste = enfermedadesNuevoCliente.some(e => e.id === enf.id);
                return `<div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                        onclick="${!yaExiste ? `window.agregarEnfermedadNuevoCliente(${enf.id})` : ''}" 
                        style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${enf.nombre}</strong>
                                <br><small class="text-muted">${enf.categoria?.nombre || 'Sin categoría'}</small>
                            </div>
                            ${yaExiste ? '<span class="badge bg-secondary">Ya agregada</span>' : '<span class="badge bg-success">Click para agregar</span>'}
                        </div>
                    </div>`;
            }).join('');
        }
        resultadosDiv.style.display = 'block';
    }

    // EXPONER FUNCIONES GLOBALES
    window.agregarEnfermedadNuevoCliente = function(enfermedadId) {
        const enfermedad = todasEnfermedades.find(e => e.id === enfermedadId);
        if (!enfermedad || enfermedadesNuevoCliente.some(e => e.id === enfermedadId)) return;
        
        enfermedadesNuevoCliente.push({ 
            id: enfermedad.id, 
            nombre: enfermedad.nombre, 
            categoria: enfermedad.categoria?.nombre || 'Sin categoría' 
        });
        
        renderizarTablaEnfermedades();
        document.getElementById('buscarEnfermedadNuevoModal').value = '';
        document.getElementById('resultadosBusquedaNuevo').style.display = 'none';
        mostrarToast('Enfermedad agregada a la lista', 'success');
    };

    window.eliminarEnfermedadNuevoCliente = function(enfermedadId) {
        if (confirm('¿Eliminar esta enfermedad?')) {
            enfermedadesNuevoCliente = enfermedadesNuevoCliente.filter(e => e.id !== enfermedadId);
            renderizarTablaEnfermedades();
            mostrarToast('Enfermedad eliminada de la lista', 'warning');
        }
    };

    // ============================================
    // FUNCIÓN PARA GUARDAR
    // ============================================
    window.guardarNuevoCliente = function() {
        const formData = {
            nombre: document.getElementById('nombre')?.value || '',
            apellidos: document.getElementById('apellidos')?.value || '',
            email: document.getElementById('email')?.value || '',
            telefono: document.getElementById('telefono')?.value || '',
            calle: document.getElementById('calle')?.value || '',
            colonia: document.getElementById('colonia')?.value || '',
            ciudad: document.getElementById('ciudad')?.value || '',
            enfermedades: enfermedadesNuevoCliente.map(e => e.id),
            _token: '{{ csrf_token() }}'
        };
        
        // Validar campos requeridos
        if (!formData.nombre || !formData.apellidos || !formData.email) { 
            mostrarToast('Completa los campos requeridos', 'warning'); 
            return; 
        }
        
        // Validar formato de email
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            mostrarToast('Correo electrónico no válido', 'warning');
            return;
        }
        
        fetch('{{ route("clientes.store") }}', {
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
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
                modal.hide();
                mostrarToast('Cliente creado correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else { 
                mostrarToast('Error: ' + (data.message || 'Error desconocido'), 'danger'); 
            }
        }).catch(error => { 
            console.error(error); 
            mostrarToast('Error de conexión', 'danger'); 
        });
    };

    // ============================================
    // FUNCIÓN PARA TOASTS
    // ============================================
    function mostrarToast(mensaje, tipo = 'success') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = tipo === 'success' ? 'bg-success' : (tipo === 'warning' ? 'bg-warning' : 'bg-danger');
        
        const toastHtml = `<div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
            <div class="toast-header ${bgClass} text-white">
                <strong class="me-auto">CRM</strong>
                <small>ahora</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${mensaje}</div>
        </div>`;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.getElementById(toastId);
        new bootstrap.Toast(toastElement).show();
        toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
    }

    // ============================================
    // INICIALIZACIÓN Y EVENT LISTENERS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Precargar catálogo cuando se abre el modal
        const modal = document.getElementById('modalNuevoCliente');
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                // Resetear enfermedades
                enfermedadesNuevoCliente = [];
                renderizarTablaEnfermedades();
                
                // Cargar catálogo si está vacío
                if (todasEnfermedades.length === 0) {
                    cargarCatalogoEnfermedades();
                }
                
                // Limpiar búsqueda
                document.getElementById('buscarEnfermedadNuevoModal').value = '';
                document.getElementById('resultadosBusquedaNuevo').style.display = 'none';
            });
        }
        
        // Buscador en tiempo real
        document.getElementById('buscarEnfermedadNuevoModal')?.addEventListener('input', function() { 
            buscarEnfermedades(this.value); 
        });
        
        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(event) {
            const resultados = document.getElementById('resultadosBusquedaNuevo');
            const buscador = document.getElementById('buscarEnfermedadNuevoModal');
            if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
                resultados.style.display = 'none';
            }
        });
    });
})();
</script>
@endpush