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
                        <label class="form-label">Cliente</label>
                        <div class="p-2 bg-light rounded">
                            <strong id="edit_cliente_nombre"></strong><br>
                            <small id="edit_cliente_email" class="text-muted"></small>
                        </div>
                    </div>

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
(function() {
    // ============================================
    // FUNCIONES
    // ============================================
    window.cargarDatosPreferencia = function(id) {
        fetch(`/preferencias/${id}/edit`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_preferencia_id').value = data.data.id;
                document.getElementById('edit_cliente_nombre').textContent = 
                    `${data.data.cliente.nombre} ${data.data.cliente.apellidos}`;
                document.getElementById('edit_cliente_email').textContent = data.data.cliente.email;
                document.getElementById('edit_categoria').value = data.data.categoria || '';
                document.getElementById('edit_descripcion').value = data.data.descripcion;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error al cargar la preferencia', 'danger');
        });
    };

    window.guardarEdicionPreferencia = function() {
        const id = document.getElementById('edit_preferencia_id')?.value;
        const formData = {
            categoria: document.getElementById('edit_categoria')?.value || '',
            descripcion: document.getElementById('edit_descripcion')?.value || '',
            _token: '{{ csrf_token() }}',
            _method: 'PUT'
        };

        if (!formData.descripcion) {
            if (window.mostrarToast) window.mostrarToast('La descripción es requerida', 'warning');
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
                if (window.mostrarToast) window.mostrarToast('Preferencia actualizada', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                if (window.mostrarToast) window.mostrarToast('Error al actualizar', 'danger');
            }
        })
        .catch(error => {
            console.error(error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    };

    // ============================================
    // EVENT LISTENERS
    // ============================================
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
})();
</script>
@endpush