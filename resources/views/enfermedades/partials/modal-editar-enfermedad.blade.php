<!-- Modal Editar Enfermedad -->
<div class="modal fade" id="modalEditarEnfermedad" tabindex="-1" aria-labelledby="modalEditarEnfermedadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarEnfermedadLabel">
                    <i class="bi bi-pencil-square"></i> Editar Enfermedad
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarEnfermedad">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_enfermedad_id" name="enfermedad_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre de la enfermedad</label>
                        <input type="text" class="form-control" id="edit_enfermedad_nombre" name="nombre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <div class="input-group">
                            <select class="form-select" id="edit_enfermedad_categoria" name="categoria_id" required>
                                <option value="">Seleccionar categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-primary" type="button" onclick="toggleEditNuevaCategoria()">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Campo oculto para nueva categoría en edición -->
                    <div id="editNuevaCategoriaContainer" style="display: none;" class="mb-3 p-3 border rounded bg-light">
                        <label class="form-label">Nombre de la nueva categoría</label>
                        <input type="text" class="form-control mb-2" id="edit_nueva_categoria_nombre" placeholder="Ej: Respiratoria">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditNuevaCategoria()">Cancelar</button>
                            <button type="button" class="btn btn-sm btn-success" onclick="guardarEditNuevaCategoria()">Guardar y seleccionar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionEnfermedad()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let enfermedadActualId = null;

// Función para editar enfermedad
function editarEnfermedad(id) {
    enfermedadActualId = id;
    
    fetch(`/enfermedades/${id}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_enfermedad_id').value = data.data.id;
            document.getElementById('edit_enfermedad_nombre').value = data.data.nombre;
            document.getElementById('edit_enfermedad_categoria').value = data.data.categoria_id;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarEnfermedad'));
            modal.show();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para guardar edición
function guardarEdicionEnfermedad() {
    const nombre = document.getElementById('edit_enfermedad_nombre').value.trim();
    const categoriaId = document.getElementById('edit_enfermedad_categoria').value;
    
    if (!nombre || !categoriaId) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    fetch(`/enfermedades/${enfermedadActualId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            nombre: nombre,
            categoria_id: categoriaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarEnfermedad'));
            modal.hide();
            location.reload(); // Recargar para ver cambios
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para eliminar enfermedad
function eliminarEnfermedad(id) {
    if (confirm('¿Estás seguro de eliminar esta enfermedad?')) {
        fetch(`/enfermedades/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`enfermedad-row-${id}`)?.remove();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Funciones para nueva categoría en edición
function toggleEditNuevaCategoria() {
    const container = document.getElementById('editNuevaCategoriaContainer');
    const categoriaSelect = document.getElementById('edit_enfermedad_categoria');
    
    if (container.style.display === 'none') {
        container.style.display = 'block';
        categoriaSelect.disabled = true;
    } else {
        container.style.display = 'none';
        categoriaSelect.disabled = false;
    }
}

function guardarEditNuevaCategoria() {
    const nombre = document.getElementById('edit_nueva_categoria_nombre').value.trim();
    
    if (!nombre) {
        alert('Ingresa un nombre para la categoría');
        return;
    }
    
    fetch('/enfermedades/categorias', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ nombre: nombre })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('edit_enfermedad_categoria');
            const option = document.createElement('option');
            option.value = data.data.id;
            option.textContent = data.data.nombre;
            option.selected = true;
            select.appendChild(option);
            
            toggleEditNuevaCategoria();
            document.getElementById('edit_nueva_categoria_nombre').value = '';
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>