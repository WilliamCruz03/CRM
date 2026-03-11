<!-- Modal Nueva Enfermedad -->
<div class="modal fade" id="modalNuevaEnfermedad" tabindex="-1" aria-labelledby="modalNuevaEnfermedadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaEnfermedadLabel">
                    <i class="bi bi-plus-circle"></i> Nueva Enfermedad
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaEnfermedad">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre de la enfermedad</label>
                        <input type="text" class="form-control" id="nueva_enfermedad_nombre" name="nombre" 
                               placeholder="Ingrese el nombre de la enfermedad" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <div class="input-group">
                            <select class="form-select" id="nueva_enfermedad_categoria" name="categoria_id" required>
                                <option value="">Buscar categorías</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-primary" type="button" onclick="toggleNuevaCategoria()">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Campo oculto para nueva categoría -->
                    <div id="nuevaCategoriaContainer" style="display: none;" class="mb-3 p-3 border rounded bg-light">
                        <label class="form-label">Nombre de la nueva categoría</label>
                        <input type="text" class="form-control mb-2" id="nueva_categoria_nombre" placeholder="Ej: Crónico-Degenerativa">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleNuevaCategoria()">Cancelar</button>
                            <button type="button" class="btn btn-sm btn-success" onclick="guardarNuevaCategoria()">Guardar y seleccionar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevaEnfermedad()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleNuevaCategoria() {
    const container = document.getElementById('nuevaCategoriaContainer');
    const categoriaSelect = document.getElementById('nueva_enfermedad_categoria');
    
    if (container.style.display === 'none') {
        container.style.display = 'block';
        categoriaSelect.disabled = true;
    } else {
        container.style.display = 'none';
        categoriaSelect.disabled = false;
        document.getElementById('nueva_categoria_nombre').value = '';
    }
}

function guardarNuevaCategoria() {
    const nombre = document.getElementById('nueva_categoria_nombre').value.trim();
    
    if (!nombre) {
        alert('Por favor ingresa un nombre para la categoría');
        return;
    }
    
    fetch('/enfermedades/categorias', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ nombre: nombre })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar la nueva categoría al select
            const select = document.getElementById('nueva_enfermedad_categoria');
            const option = document.createElement('option');
            option.value = data.data.id;
            option.textContent = data.data.nombre;
            option.selected = true;
            select.appendChild(option);
            
            // Cerrar el campo de nueva categoría
            toggleNuevaCategoria();
            
            alert('Categoría creada correctamente');
        }
    })
    .catch(error => console.error('Error:', error));
}

function guardarNuevaEnfermedad() {
    const nombre = document.getElementById('nueva_enfermedad_nombre').value.trim();
    let categoriaId;
    
    // Verificar si se está creando una nueva categoría
    if (document.getElementById('nuevaCategoriaContainer').style.display === 'block') {
        alert('Primero guarda la nueva categoría');
        return;
    } else {
        categoriaId = document.getElementById('nueva_enfermedad_categoria').value;
    }
    
    if (!nombre) {
        alert('Por favor ingresa el nombre de la enfermedad');
        return;
    }
    
    if (!categoriaId) {
        alert('Por favor selecciona una categoría');
        return;
    }
    
    fetch('/enfermedades', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
            // Cerrar modal y recargar la página o agregar la fila dinámicamente
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaEnfermedad'));
            modal.hide();
            
            // Recargar la página para ver el nuevo registro
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush