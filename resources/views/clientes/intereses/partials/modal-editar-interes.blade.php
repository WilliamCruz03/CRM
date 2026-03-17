<!-- Modal Editar Interés -->
<div class="modal fade" id="modalEditarInteres" tabindex="-1" aria-labelledby="modalEditarInteresLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarInteresLabel">
                    <i class="bi bi-pencil-square"></i> Editar Interés
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarInteres">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_interes_id" name="interes_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción del interés <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_interes_descripcion" name="Descripcion" 
                               oninput="aMayusculas(event)"
                               required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionInteres()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// La variable interesActualId se define en el index

window.guardarEdicionInteres = function() {
    const id = document.getElementById('edit_interes_id').value;
    const descripcion = document.getElementById('edit_interes_descripcion').value.trim();
    
    if (!descripcion) {
        if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
        return;
    }
    
    fetch(`/intereses/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ Descripcion: descripcion })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarInteres'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast('Interés actualizado', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al actualizar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// Event listener para cargar datos al abrir el modal
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarInteres');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const interesId = button.getAttribute('data-interes-id');
            
            fetch(`/intereses/${interesId}/edit`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_interes_id').value = data.data.id_interes;
                    document.getElementById('edit_interes_descripcion').value = data.data.Descripcion;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>
@endpush