<!-- Modal Nuevo Interés -->
<div class="modal fade" id="modalNuevoInteres" tabindex="-1" aria-labelledby="modalNuevoInteresLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoInteresLabel">
                    <i class="bi bi-plus-circle"></i> Nuevo Interés
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoInteres">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción del interés <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nuevo_interes_descripcion" name="Descripcion" 
                               placeholder="Ej: Cremas, Maquillaje..." 
                               oninput="aMayusculas(event)"
                               required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevoInteres()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.guardarNuevoInteres = function() {
    const descripcion = document.getElementById('nuevo_interes_descripcion').value.trim();
    
    if (!descripcion) {
        if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
        return;
    }
    
    fetch('/intereses', {
        method: 'POST',
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoInteres'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast('Interés creado correctamente', 'success');
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