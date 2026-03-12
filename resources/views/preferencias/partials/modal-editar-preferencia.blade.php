<!-- Modal Editar Preferencia -->
<div class="modal fade" id="modalEditarPreferencia" tabindex="-1" aria-labelledby="modalEditarPreferenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPreferenciaLabel">
                    <i class="bi bi-pencil-square"></i> Editar Preferencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPreferencia">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_preferencia_id" name="preferencia_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="edit_categoria" name="categoria" 
                               placeholder="Ej: Contacto, Notificaciones, Entregas">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detalle de preferencia <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                  rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionPreferencia()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Variable global para el ID de la preferencia actual
let preferenciaActualId = null;

// Función para cargar datos de la preferencia
function cargarDatosPreferencia(id) {
    preferenciaActualId = id;
    
    fetch(`/preferencias/${id}/edit`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_preferencia_id').value = data.data.id;
            document.getElementById('edit_categoria').value = data.data.categoria || '';
            document.getElementById('edit_descripcion').value = data.data.descripcion;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error al cargar la preferencia', 'danger');
    });
}

// Función para guardar edición
window.guardarEdicionPreferencia = function() {
    const id = document.getElementById('edit_preferencia_id')?.value;
    
    const formData = {
        categoria: document.getElementById('edit_categoria')?.value || '',
        descripcion: document.getElementById('edit_descripcion')?.value || '',
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    if (!formData.descripcion) {
        mostrarToast('La descripción es requerida', 'warning');
        return;
    }
    
    fetch(`/preferencias/${id}`, {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPreferencia'));
            modal.hide();
            mostrarToast('Preferencia actualizada correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast('Error al actualizar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('Error de conexión', 'danger');
    });
};

// Función para mostrar toasts
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

// Event listener para el modal
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarPreferencia');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const preferenciaId = button.getAttribute('data-preferencia-id');
            cargarDatosPreferencia(preferenciaId);
        });
    }
});
</script>
@endpush