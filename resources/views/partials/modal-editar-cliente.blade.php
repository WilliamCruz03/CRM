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

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="edit_estado" name="estado">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <!-- SECCIÓN DE ENFERMEDADES - TABLA DINÁMICA -->
                    <h6 class="mb-3">Datos clínicos</h6>
                    
                    <!-- Buscador de enfermedades -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="buscarEnfermedadModal" 
                                       placeholder="Buscar enfermedad para agregar...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-success w-100" id="btnAgregarEnfermedad">
                                <i class="bi bi-plus-circle"></i> Agregar Enfermedad
                            </button>
                        </div>
                    </div>
                    
                    <!-- Resultados de búsqueda (ocultos por defecto) -->
                    <div id="resultadosBusqueda" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Resultados de búsqueda</small>
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
                                <!-- Las enfermedades se cargarán dinámicamente -->
                                <tr id="sin-enfermedades-row">
                                    <td colspan="4" class="text-center py-3">
                                        <i class="bi bi-heart-pulse text-muted"></i>
                                        <p class="text-muted mb-0">Este cliente no tiene enfermedades registradas</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Para agregar una enfermedad, búscala en el campo superior y haz clic en "Agregar" en los resultados.
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
// Variable global para almacenar todas las enfermedades
let todasEnfermedades = [];

// Cargar enfermedades cuando se abre el modal
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarCliente');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clienteId = button.getAttribute('data-cliente-id');
            
            // Mostrar loading
            document.getElementById('enfermedades-loading').style.display = 'block';
            document.getElementById('edit_enfermedades').style.display = 'none';
            
            // Cargar todas las enfermedades primero (solo una vez)
            if (todasEnfermedades.length === 0) {
                fetch('/enfermedades/categorias', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Aquí necesitamos un endpoint que devuelva todas las enfermedades
                        // Por ahora, haremos una petición adicional
                        return fetch('/enfermedades');
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Extraer enfermedades del HTML (no es ideal)
                    // Mejor: crear un endpoint específico
                    console.log('Enfermedades cargadas');
                })
                .catch(error => console.error('Error:', error));
            }
            
            // Cargar datos del cliente
            cargarDatosCliente(clienteId);
        });
    }
});

function cargarDatosCliente(clienteId) {
    fetch(`/clientes/${clienteId}/edit`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Llenar datos básicos
            document.getElementById('edit_cliente_id').value = data.data.id;
            document.getElementById('edit_nombre').value = data.data.nombre;
            document.getElementById('edit_apellidos').value = data.data.apellidos;
            document.getElementById('edit_email').value = data.data.email;
            document.getElementById('edit_telefono').value = data.data.telefono || '';
            document.getElementById('edit_calle').value = data.data.calle || '';
            document.getElementById('edit_colonia').value = data.data.colonia || '';
            document.getElementById('edit_ciudad').value = data.data.ciudad || '';
            document.getElementById('edit_estado').value = data.data.estado;
            
            // Ahora cargar enfermedades
            cargarEnfermedadesParaEdicion(data.data.enfermedades);
        }
    })
    .catch(error => console.error('Error:', error));
}

function cargarEnfermedadesParaEdicion(enfermedadesSeleccionadas) {
    fetch('/enfermedades/todas', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('edit_enfermedades');
            select.innerHTML = '';
            
            data.data.forEach(enfermedad => {
                const option = document.createElement('option');
                option.value = enfermedad.id;
                option.textContent = `${enfermedad.nombre} (${enfermedad.categoria?.nombre || 'Sin categoría'})`;
                
                // Seleccionar si el cliente ya tiene esta enfermedad
                if (enfermedadesSeleccionadas && enfermedadesSeleccionadas.includes(enfermedad.id)) {
                    option.selected = true;
                }
                
                select.appendChild(option);
            });
            
            // Ocultar loading y mostrar select
            document.getElementById('enfermedades-loading').style.display = 'none';
            select.style.display = 'block';
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush