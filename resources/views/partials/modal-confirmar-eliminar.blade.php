<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalConfirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalConfirmarEliminarLabel">
                    <i class="bi bi-exclamation-triangle-fill"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-trash3-fill text-danger" style="font-size: 3rem;"></i>
                <h4 class="mt-3">¿Estás seguro?</h4>
                <p class="text-muted mb-0" id="mensajeConfirmacion">Esta acción no se puede deshacer.</p>
                <p class="text-muted" id="detalleConfirmacion"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="bi bi-trash"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let elementoAEliminar = null;
let tipoElemento = '';
let idElemento = null;
let nombreElemento = '';

// Función para mostrar el modal de confirmación
window.confirmarEliminar = function(tipo, id, nombre) {
    elementoAEliminar = tipo;
    idElemento = id;
    nombreElemento = nombre;
    tipoElemento = tipo;
    
    // Personalizar mensaje según el tipo
    let mensaje = '';
    if (tipo === 'enfermedad') {
        mensaje = `¿Eliminar la enfermedad "${nombre}"?`;
    } else if (tipo === 'cliente') {
        mensaje = `¿Eliminar el cliente "${nombre}"?`;
    } else if (tipo === 'preferencia') {
        mensaje = `¿Eliminar la preferencia "${nombre}"?`;
    }
    
    document.getElementById('detalleConfirmacion').textContent = mensaje;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
    modal.show();
};

// Configurar el botón de confirmación
document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminar'));
    modal.hide();
    
    // Ejecutar la eliminación según el tipo
    if (tipoElemento === 'enfermedad') {
        ejecutarEliminarEnfermedad(idElemento, nombreElemento);
    } else if (tipoElemento === 'cliente') {
        ejecutarEliminarCliente(idElemento, nombreElemento);
    } else if (tipoElemento === 'preferencia') {
        ejecutarEliminarPreferencia(idElemento, nombreElemento);
    }
});

// Función para mostrar toasts (unificada)
function mostrarToast(mensaje, tipo = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const bgClass = tipo === 'success' ? 'bg-success' : (tipo === 'warning' ? 'bg-warning' : 'bg-danger');
    const iconClass = tipo === 'success' ? 'bi-check-circle-fill' : (tipo === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill');
    
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
            <div class="toast-header ${bgClass} text-white">
                <i class="bi ${iconClass} me-2"></i>
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
    new bootstrap.Toast(toastElement).show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Hacer la función global
window.mostrarToast = mostrarToast;
</script>
@endpush