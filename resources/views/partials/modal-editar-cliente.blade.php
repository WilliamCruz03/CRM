<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarClienteLabel">
                    <i class="bi bi-pencil-square"></i> Editar Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCliente">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_cliente_id" name="cliente_id">
                    
                    <!-- Datos básicos del cliente -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="edit_apellidos" name="apellidos" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Calle</label>
                        <input type="text" class="form-control" id="edit_calle" name="calle">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Colonia/Barrio/Localidad</label>
                            <input type="text" class="form-control" id="edit_colonia" name="colonia">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad/Municipio</label>
                            <input type="text" class="form-control" id="edit_ciudad" name="ciudad">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="edit_telefono" name="telefono">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="edit_estado" name="estado">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <!-- SECCIÓN DE ENFERMEDADES - TABLA DINÁMICA -->
                        <h6 class="mb-3">Datos clínicos</h6>

                        <!-- Buscador de enfermedades (ahora sin botón) -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="buscarEnfermedadModal" 
                                        placeholder="Buscar enfermedad para agregar (escribe al menos 2 caracteres)...">
                                </div>
                                <small class="text-muted">Los resultados aparecerán automáticamente al escribir</small>
                            </div>
                        </div>

                        <!-- Resultados de búsqueda -->
                        <div id="resultadosBusqueda" class="mb-3" style="display: none;">
                            <div class="card">
                                <div class="card-header bg-light py-2">
                                    <small class="fw-bold">Resultados de búsqueda (haz clic para agregar)</small>
                                </div>
                                <div class="list-group list-group-flush" id="listaResultados">
                                    <!-- Resultados dinámicos -->
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de enfermedades del cliente -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tablaEnfermedadesCliente">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Enfermedad</th>
                                        <th>Categoría</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="enfermedadesClienteBody">
                                    <tr id="sin-enfermedades-row">
                                        <td colspan="4" class="text-center py-4">
                                            <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2">Este cliente no tiene enfermedades registradas</p>
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
                <button type="button" class="btn btn-warning" onclick="guardarEdicionCliente()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let todasEnfermedades = [];
let enfermedadesCliente = [];

// ============================================
// FUNCIONES DE CARGA DE DATOS
// ============================================

// Cargar catálogo de enfermedades
function cargarCatalogoEnfermedades() {
    return fetch('/enfermedades/todas', {
        headers: {
            'Accept': 'application/json'
        }
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

// Cargar datos del cliente
function cargarDatosCliente(clienteId) {
    fetch(`/clientes/${clienteId}/edit`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(async data => {
        if (data.success) {
            // Datos básicos
            document.getElementById('edit_cliente_id').value = data.data.id;
            document.getElementById('edit_nombre').value = data.data.nombre;
            document.getElementById('edit_apellidos').value = data.data.apellidos;
            document.getElementById('edit_email').value = data.data.email;
            document.getElementById('edit_telefono').value = data.data.telefono || '';
            document.getElementById('edit_calle').value = data.data.calle || '';
            document.getElementById('edit_colonia').value = data.data.colonia || '';
            document.getElementById('edit_ciudad').value = data.data.ciudad || '';
            document.getElementById('edit_estado').value = data.data.estado;
            
            // Cargar catálogo si es necesario
            if (todasEnfermedades.length === 0) {
                await cargarCatalogoEnfermedades();
            }
            
            // Cargar enfermedades del cliente
            enfermedadesCliente = [];
            if (data.data.enfermedades && todasEnfermedades.length > 0) {
                data.data.enfermedades.forEach(enfId => {
                    const enf = todasEnfermedades.find(e => e.id === enfId);
                    if (enf) {
                        enfermedadesCliente.push({
                            id: enf.id,
                            nombre: enf.nombre,
                            categoria: enf.categoria?.nombre || 'Sin categoría'
                        });
                    }
                });
            }
            
            renderizarTablaEnfermedades();
        }
    })
    .catch(error => console.error('Error:', error));
}

// ============================================
// FUNCIONES DE LA TABLA
// ============================================

function renderizarTablaEnfermedades() {
    const tbody = document.getElementById('enfermedadesClienteBody');
    if (!tbody) return;
    
    if (enfermedadesCliente.length === 0) {
        tbody.innerHTML = `
            <tr id="sin-enfermedades-row">
                <td colspan="4" class="text-center py-4">
                    <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Este cliente no tiene enfermedades registradas</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    enfermedadesCliente.forEach((enf, index) => {
        html += `
            <tr id="enfermedad-row-${enf.id}">
                <td>${index + 1}</td>
                <td>${enf.nombre}</td>
                <td><span class="badge bg-info">${enf.categoria}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                            onclick="eliminarEnfermedadDeTabla(${enf.id})"
                            title="Eliminar enfermedad">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function eliminarEnfermedadDeTabla(enfermedadId) {
    if (confirm('¿Eliminar esta enfermedad del cliente?')) {
        enfermedadesCliente = enfermedadesCliente.filter(e => e.id !== enfermedadId);
        renderizarTablaEnfermedades();
        mostrarToast('Enfermedad eliminada de la lista', 'warning');
    }
}

// ============================================
// FUNCIONES DE BÚSQUEDA (AHORA CON CLICK DIRECTO)
// ============================================

function buscarEnfermedades(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosBusqueda').style.display = 'none';
        return;
    }
    
    const resultados = todasEnfermedades.filter(enf => 
        enf.nombre.toLowerCase().includes(termino.toLowerCase()) ||
        (enf.categoria?.nombre || '').toLowerCase().includes(termino.toLowerCase())
    );
    
    const resultadosDiv = document.getElementById('resultadosBusqueda');
    const listaResultados = document.getElementById('listaResultados');
    
    if (resultados.length === 0) {
        listaResultados.innerHTML = `
            <div class="list-group-item text-muted">
                <i class="bi bi-exclamation-circle"></i> No se encontraron resultados
            </div>
        `;
    } else {
        listaResultados.innerHTML = resultados.map(enf => {
            const yaExiste = enfermedadesCliente.some(e => e.id === enf.id);
            // Si ya existe, mostramos deshabilitado visualmente pero sin botón
            return `
                <div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                     onclick="${!yaExiste ? `agregarEnfermedadACliente(${enf.id})` : ''}"
                     style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${enf.nombre}</strong>
                            <br><small class="text-muted">${enf.categoria?.nombre || 'Sin categoría'}</small>
                        </div>
                        ${yaExiste ? '<span class="badge bg-secondary">Ya agregada</span>' : '<span class="badge bg-success">Click para agregar</span>'}
                    </div>
                </div>
            `;
        }).join('');
    }
    
    resultadosDiv.style.display = 'block';
}

function agregarEnfermedadACliente(enfermedadId) {
    const enfermedad = todasEnfermedades.find(e => e.id === enfermedadId);
    if (!enfermedad) return;
    
    if (enfermedadesCliente.some(e => e.id === enfermedadId)) {
        mostrarToast('Esta enfermedad ya está agregada', 'warning');
        return;
    }
    
    enfermedadesCliente.push({
        id: enfermedad.id,
        nombre: enfermedad.nombre,
        categoria: enfermedad.categoria?.nombre || 'Sin categoría'
    });
    
    renderizarTablaEnfermedades();
    
    // Limpiar búsqueda y ocultar resultados
    document.getElementById('buscarEnfermedadModal').value = '';
    document.getElementById('resultadosBusqueda').style.display = 'none';
    
    mostrarToast('Enfermedad agregada correctamente', 'success');
}

// ============================================
// FUNCIÓN PARA GUARDAR
// ============================================

window.guardarEdicionCliente = function() {
    const id = document.getElementById('edit_cliente_id')?.value;
    
    const formData = {
        nombre: document.getElementById('edit_nombre')?.value || '',
        apellidos: document.getElementById('edit_apellidos')?.value || '',
        email: document.getElementById('edit_email')?.value || '',
        telefono: document.getElementById('edit_telefono')?.value || '',
        calle: document.getElementById('edit_calle')?.value || '',
        colonia: document.getElementById('edit_colonia')?.value || '',
        ciudad: document.getElementById('edit_ciudad')?.value || '',
        estado: document.getElementById('edit_estado')?.value || 'Activo',
        enfermedades: enfermedadesCliente.map(e => e.id),
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    if (!formData.nombre || !formData.apellidos || !formData.email) {
        mostrarToast('Completa los campos requeridos', 'danger');
        return;
    }
    
    fetch(`/clientes/${id}`, {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
            modal.hide();
            mostrarToast('Cliente actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast('Error al actualizar', 'danger');
        }
    })
    .catch(error => {
        console.error(error);
        mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// FUNCIÓN PARA TOASTS (MENSAJES EMERGENTES)
// ============================================

function mostrarToast(mensaje, tipo = 'success') {
    // Crear contenedor si no existe
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Crear toast
    const toastId = 'toast-' + Date.now();
    const bgClass = tipo === 'success' ? 'bg-success' : (tipo === 'warning' ? 'bg-warning' : 'bg-danger');
    
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
            <div class="toast-header ${bgClass} text-white">
                <strong class="me-auto">CRM</strong>
                <small>ahora</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${mensaje}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Eliminar del DOM después de ocultarse
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// ============================================
// EVENT LISTENERS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarCliente');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clienteId = button.getAttribute('data-cliente-id');
            
            // Limpiar búsqueda
            document.getElementById('buscarEnfermedadModal').value = '';
            document.getElementById('resultadosBusqueda').style.display = 'none';
            
            // Cargar datos
            cargarDatosCliente(clienteId);
        });
    }
    
    // Buscador en tiempo real
    document.getElementById('buscarEnfermedadModal')?.addEventListener('input', function() {
        buscarEnfermedades(this.value);
    });

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(event) {
        const resultados = document.getElementById('resultadosBusqueda');
        const buscador = document.getElementById('buscarEnfermedadModal');
        
        if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
            resultados.style.display = 'none';
        }
    });
});
</script>
@endpush