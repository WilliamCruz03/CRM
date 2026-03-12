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
                    
                    <div class="mb-3">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select" id="preferencia_cliente_id" name="cliente_id" required>
                            <option value="">Buscar y seleccionar cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre_completo }} ({{ $cliente->email }})</option>
                            @endforeach
                        </select>
                    </div>

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
// Función para guardar nueva preferencia
window.guardarNuevaPreferencia = function() {
    const formData = {
        cliente_id: document.getElementById('preferencia_cliente_id')?.value,
        categoria: document.getElementById('nueva_categoria')?.value || '',
        descripcion: document.getElementById('nueva_descripcion')?.value || '',
        _token: '{{ csrf_token() }}'
    };
    
    if (!formData.cliente_id) {
        mostrarToast('Por favor selecciona un cliente', 'warning');
        return;
    }
    
    if (!formData.descripcion) {
        mostrarToast('La descripción es requerida', 'warning');
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
            mostrarToast('Preferencia registrada correctamente', 'success');
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
</script>
@endpush