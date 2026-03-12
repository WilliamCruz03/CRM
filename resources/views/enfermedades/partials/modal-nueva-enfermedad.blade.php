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
                        <label class="form-label">Nombre de la enfermedad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nueva_enfermedad_nombre" name="nombre" 
                               placeholder="Ingrese el nombre de la enfermedad" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select" id="nueva_enfermedad_categoria" name="categoria_id" required>
                            <option value="">Seleccionar categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
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
window.guardarNuevaEnfermedad = function() {
    const nombre = document.getElementById('nueva_enfermedad_nombre').value.trim();
    const categoriaId = document.getElementById('nueva_enfermedad_categoria').value;
    
    if (!nombre) {
        mostrarToast('El nombre es requerido', 'warning');
        return;
    }
    
    if (!categoriaId) {
        mostrarToast('Selecciona una categoría', 'warning');
        return;
    }
    
    fetch('/enfermedades', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            nombre: nombre,
            categoria_id: categoriaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaEnfermedad'));
            modal.hide();
            mostrarToast('Enfermedad creada correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast('Error al guardar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error de conexión', 'danger');
    });
};

function mostrarToast(mensaje, tipo = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
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
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
</script>
@endpush