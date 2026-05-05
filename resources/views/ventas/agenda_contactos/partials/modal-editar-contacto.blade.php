<!-- Modal Editar Contacto -->
<div class="modal fade" id="modalEditarContacto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar agenda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarContacto">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="contacto_id_edit">
                    <input type="hidden" id="cliente_id_edit">
                    
                    <div class="mb-3">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <div class="alert alert-info">
                            <strong id="cliente_nombre_edit"></strong>
                            <br><small id="cliente_contacto_edit" class="text-muted"></small>
                            <br><small id="cliente_direccion_edit" class="text-muted"></small>
                        </div>
                        <small class="text-muted">El cliente no se puede modificar en edición. Si necesita cambiar el cliente, cree una nueva agenda.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Asunto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="asunto_edit" placeholder="Ingrese el asunto" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_edit" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="hora_edit" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_edit" required>
                                <option value="">Seleccionar</option>
                                <option value="1">Llamada</option>
                                <option value="2">Mensaje</option>
                                <option value="3">Correo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recordatorio</label>
                            <select class="form-select" id="recordatorio_minutos_edit">
                                <option value="">Sin recordatorio</option>
                                @foreach($recordatorios as $rec)
                                    <option value="{{ $rec->valor }}">{{ $rec->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comentario</label>
                        <textarea class="form-control" id="comentario_edit" rows="3" placeholder="Notas adicionales..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEditarContacto">
                    <i class="bi bi-save"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Guardar edición
document.getElementById('btnGuardarEditarContacto')?.addEventListener('click', function() {
    const campos = [
        { id: 'cliente_id_edit', mensaje: 'Seleccione un cliente' },
        { id: 'asunto_edit', mensaje: 'Ingrese el asunto' },
        { id: 'fecha_edit', mensaje: 'Seleccione la fecha' },
        { id: 'hora_edit', mensaje: 'Seleccione la hora' },
        { id: 'tipo_edit', mensaje: 'Seleccione el tipo de contacto' }
    ];
    
    for (const campo of campos) {
        const valor = document.getElementById(campo.id)?.value;
        if (!valor) {
            if (window.mostrarToast) window.mostrarToast(campo.mensaje, 'warning');
            return;
        }
    }
    
    const id = document.getElementById('contacto_id_edit').value;
    
    const data = {
        id_cliente: parseInt(document.getElementById('cliente_id_edit').value),
        asunto: document.getElementById('asunto_edit').value,
        tipo: parseInt(document.getElementById('tipo_edit').value),
        fecha: document.getElementById('fecha_edit').value,
        hora: document.getElementById('hora_edit').value,
        recordatorio_minutos: document.getElementById('recordatorio_minutos_edit').value || null,
        comentario: document.getElementById('comentario_edit').value,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Actualizando...';
    
    fetch(`/ventas/agenda-contactos/${id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save"></i> Actualizar';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save"></i> Actualizar';
    });
});
</script>
@endpush