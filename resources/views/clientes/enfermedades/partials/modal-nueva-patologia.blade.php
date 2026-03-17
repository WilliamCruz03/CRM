<!-- Modal Nueva Patología -->
<div class="modal fade" id="modalNuevaPatologia" tabindex="-1" aria-labelledby="modalNuevaPatologiaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaPatologiaLabel">
                    <i class="bi bi-plus-circle"></i> Nueva Patología
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaPatologia">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción de la patología <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nueva_patologia_descripcion" name="descripcion" 
                               placeholder="Ingrese la patología" 
                               oninput="aMayusculas(event)"
                               required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevaPatologia()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.guardarNuevaPatologia = function() {
    const descripcion = document.getElementById('nueva_patologia_descripcion').value.trim();
    
    if (!descripcion) {
        if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
        return;
    }
    
    fetch('/enfermedades', {
        method: 'POST',
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaPatologia'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast('Patología creada correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};
</script>
@endpush