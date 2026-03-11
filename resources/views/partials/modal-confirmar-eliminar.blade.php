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

function confirmarEliminar(elemento, id, nombre) {
    elementoAEliminar = elemento;
    idElemento = id;
    tipoElemento = elemento;
    
    // Personalizar mensaje según el tipo
    let mensaje = '';
    if (elemento === 'enfermedad') {
        mensaje = `¿Eliminar la enfermedad "${nombre}"?`;
    } else if (elemento === 'cliente') {
        mensaje = `¿Eliminar el cliente "${nombre}"?`;
    } else if (elemento === 'categoria') {
        mensaje = `¿Eliminar la categoría "${nombre}"?`;
    }
    
    document.getElementById('detalleConfirmacion').textContent = mensaje;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
    modal.show();
}

// Configurar el botón de confirmación
document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
    if (elementoAEliminar === 'enfermedad') {
        ejecutarEliminarEnfermedad(idElemento);
    } else if (elementoAEliminar === 'cliente') {
        ejecutarEliminarCliente(idElemento);
    }
    
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminar'));
    modal.hide();
});
</script>
@endpush