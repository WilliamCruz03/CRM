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

                    <h6 class="mb-3">Datos clínicos</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Enfermedades del cliente</label>
                        <select class="form-select" id="edit_enfermedades" name="enfermedades[]" multiple size="5">
                            @foreach($enfermedades ?? [] as $enfermedad)
                                <option value="{{ $enfermedad->id }}">
                                    {{ $enfermedad->nombre }} ({{ $enfermedad->categoria->nombre ?? 'Sin categoría' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Puedes seleccionar múltiples enfermedades con Ctrl+Click</small>
                    </div>
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
let editEnfermedadCount = 0;

function agregarEnfermedadEdit(enfermedad = '', index = null) {
    const tbody = document.getElementById('edit_enfermedades_body');
    const rowIndex = index !== null ? index : editEnfermedadCount + 1;
    
    const row = document.createElement('tr');
    row.id = `edit_enfermedad_row_${rowIndex}`;
    
    row.innerHTML = `
        <td>${rowIndex}</td>
        <td>
            <select class="form-select form-select-sm" name="edit_enfermedades[${rowIndex}]" id="edit_enfermedad_${rowIndex}">
                <option value="">Buscar y seleccionar</option>
                <option value="Hipertensión Arterial" ${enfermedad === 'Hipertensión Arterial' ? 'selected' : ''}>Hipertensión Arterial</option>
                <option value="Diabetes Tipo 2" ${enfermedad === 'Diabetes Tipo 2' ? 'selected' : ''}>Diabetes Tipo 2</option>
                <option value="Alergia a Penicilina" ${enfermedad === 'Alergia a Penicilina' ? 'selected' : ''}>Alergia a Penicilina</option>
                <option value="Asma Bronquial" ${enfermedad === 'Asma Bronquial' ? 'selected' : ''}>Asma Bronquial</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarEnfermedadEdit(${rowIndex})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    editEnfermedadCount = rowIndex;
}

function eliminarEnfermedadEdit(index) {
    const row = document.getElementById(`edit_enfermedad_row_${index}`);
    if (row) {
        row.remove();
        // Renumerar las filas restantes
        const tbody = document.getElementById('edit_enfermedades_body');
        const rows = tbody.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const cell = rows[i].getElementsByTagName('td')[0];
            if (cell) {
                cell.textContent = (i + 1).toString();
            }
        }
        editEnfermedadCount = rows.length;
    }
}

function cargarDatosCliente(id) {
    // Aquí iría la petición AJAX para obtener los datos del cliente
    // Por ahora, simulamos con datos de ejemplo según el ID
    const clientes = {
        1021: {
            nombre: 'Jorge',
            apellidos: 'Hernández',
            calle: 'Calle Principal 123',
            colonia: 'Centro',
            ciudad: 'Tamazunchale',
            email: 'jorge.hdz@gmail.com',
            telefono: '559 876 5432',
            estado: 'Activo',
            enfermedades: []
        },
        1022: {
            nombre: 'Ana',
            apellidos: 'López',
            calle: 'Av. Reforma 456',
            colonia: 'Zona Centro',
            ciudad: 'Tamazunchale',
            email: 'ana.lopez@gmail.com',
            telefono: '332 211 4155',
            estado: 'Inactivo',
            enfermedades: ['Diabetes Tipo 2']
        },
        1023: {
            nombre: 'Carlos',
            apellidos: 'Ramírez',
            calle: 'Calle S/N',
            colonia: 'Barrio San Juan',
            ciudad: 'Tamazunchale',
            email: 'carlos.ramirez@gmail.com',
            telefono: '818 765 4321',
            estado: 'Activo',
            enfermedades: []
        },
        1024: {
            nombre: 'Maria',
            apellidos: 'Gonzalez',
            calle: 'Calle Hidalgo 78',
            colonia: 'Zona Centro',
            ciudad: 'Tamazunchale',
            email: 'maria.gonzalez@gmail.com',
            telefono: '123 456 789',
            estado: 'Activo',
            enfermedades: ['Hipertensión Arterial', 'Diabetes Tipo 2', 'Alergia a Penicilina']
        }
    };
    
    const cliente = clientes[id] || {
        nombre: '',
        apellidos: '',
        calle: '',
        colonia: '',
        ciudad: '',
        email: '',
        telefono: '',
        estado: 'Activo',
        enfermedades: []
    };
    
    // Llenar el formulario
    document.getElementById('edit_cliente_id').value = id;
    document.getElementById('edit_nombre').value = cliente.nombre;
    document.getElementById('edit_apellidos').value = cliente.apellidos;
    document.getElementById('edit_calle').value = cliente.calle;
    document.getElementById('edit_colonia').value = cliente.colonia;
    document.getElementById('edit_ciudad').value = cliente.ciudad;
    document.getElementById('edit_email').value = cliente.email;
    document.getElementById('edit_telefono').value = cliente.telefono;
    document.getElementById('edit_estado').value = cliente.estado;
    
    // Limpiar y cargar enfermedades
    const tbody = document.getElementById('edit_enfermedades_body');
    tbody.innerHTML = '';
    editEnfermedadCount = 0;
    
    cliente.enfermedades.forEach((enfermedad, index) => {
        agregarEnfermedadEdit(enfermedad, index + 1);
    });
}

function guardarEdicionCliente() {
    // Aquí iría la lógica para guardar los cambios
    // Por ahora, solo mostramos un mensaje y cerramos el modal
    alert('Cliente actualizado correctamente');
    
    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
    modal.hide();
    
    // Recargar la página para ver los cambios (opcional)
    // location.reload();
}

// Inicializar cuando se abre el modal
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarCliente');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clienteId = button.getAttribute('data-cliente-id');
            cargarDatosCliente(clienteId);
        });
    }
});
</script>
@endpush