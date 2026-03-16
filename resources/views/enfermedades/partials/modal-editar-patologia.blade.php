<!-- Modal Editar Patología -->
<div class="modal fade" id="modalEditarPatologia" tabindex="-1" aria-labelledby="modalEditarPatologiaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPatologiaLabel">
                    <i class="bi bi-pencil-square"></i> Editar Patología
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPatologia">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_patologia_id" name="patologia_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción de la patología <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_patologia_descripcion" name="descripcion" 
                               oninput="aMayusculas(event)"
                               required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionPatologia()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// La variable patologiaActualId se define en el index

window.guardarEdicionPatologia = function() {
    const id = document.getElementById('edit_patologia_id').value;
    const descripcion = document.getElementById('edit_patologia_descripcion').value.trim();
    
    if (!descripcion) {
        if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
        return;
    }
    
    fetch(`/enfermedades/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ descripcion: descripcion })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPatologia'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast('Patología actualizada', 'success');
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
    const modalEditar = document.getElementById('modalEditarPatologia');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const patologiaId = button.getAttribute('data-patologia-id');
            
            fetch(`/enfermedades/${patologiaId}/edit`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_patologia_id').value = data.data.id_patologia;
                    document.getElementById('edit_patologia_descripcion').value = data.data.descripcion;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>
@endpush