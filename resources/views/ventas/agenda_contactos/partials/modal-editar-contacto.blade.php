<!-- Modal Editar Contacto -->
<div class="modal fade" id="modalEditarContacto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
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
                    
                    <div class="mb-3">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cliente_busqueda_edit" 
                                   placeholder="Buscar cliente por nombre, teléfono o correo" autocomplete="off">
                            <input type="hidden" id="cliente_id_edit">
                        </div>
                        <div id="resultados_clientes_edit" class="list-group mt-1" style="display: none; max-height: 200px; overflow-y: auto;"></div>
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
// Búsqueda de clientes para editar contacto
const buscarClienteEdit = document.getElementById('cliente_busqueda_edit');
const resultadosClientesEdit = document.getElementById('resultados_clientes_edit');
const clienteIdEdit = document.getElementById('cliente_id_edit');

let timeoutBusquedaEdit;

buscarClienteEdit?.addEventListener('input', function() {
    const termino = this.value.trim();
    
    if (termino.length < 2) {
        resultadosClientesEdit.style.display = 'none';
        return;
    }
    
    clearTimeout(timeoutBusquedaEdit);
    timeoutBusquedaEdit = setTimeout(() => {
        fetch(`{{ route("ventas.agenda_contactos.clientes.buscar") }}?q=${encodeURIComponent(termino)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    resultadosClientesEdit.innerHTML = data.data.map(cliente => `
                        <button type="button" class="list-group-item list-group-item-action" 
                                data-id="${cliente.id_Cliente}"
                                data-nombre="${cliente.nombre_completo}"
                                data-telefono="${cliente.telefono1 || ''}">
                            <strong>${cliente.nombre_completo}</strong><br>
                            <small>${cliente.telefono1 || 'Sin teléfono'} - ${cliente.correo || ''}</small>
                        </button>
                    `).join('');
                    resultadosClientesEdit.style.display = 'block';
                    
                    document.querySelectorAll('#resultados_clientes_edit .list-group-item').forEach(item => {
                        item.addEventListener('click', function() {
                            buscarClienteEdit.value = this.dataset.nombre;
                            clienteIdEdit.value = this.dataset.id;
                            resultadosClientesEdit.style.display = 'none';
                        });
                    });
                } else {
                    resultadosClientesEdit.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
                    resultadosClientesEdit.style.display = 'block';
                }
            })
            .catch(error => console.error('Error:', error));
    }, 300);
});

// Ocultar resultados al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!buscarClienteEdit?.contains(e.target) && !resultadosClientesEdit?.contains(e.target)) {
        resultadosClientesEdit.style.display = 'none';
    }
});

// Guardar edición
document.getElementById('btnGuardarEditarContacto')?.addEventListener('click', function() {
    const id = document.getElementById('contacto_id_edit').value;
    const clienteId = clienteIdEdit.value;
    const asunto = document.getElementById('asunto_edit').value;
    const fecha = document.getElementById('fecha_edit').value;
    const hora = document.getElementById('hora_edit').value;
    const tipo = document.getElementById('tipo_edit').value;
    
    if (!clienteId) {
        if (window.mostrarToast) window.mostrarToast('Seleccione un cliente', 'warning');
        return;
    }
    if (!asunto) {
        if (window.mostrarToast) window.mostrarToast('Ingrese el asunto', 'warning');
        return;
    }
    if (!fecha) {
        if (window.mostrarToast) window.mostrarToast('Seleccione la fecha', 'warning');
        return;
    }
    if (!hora) {
        if (window.mostrarToast) window.mostrarToast('Seleccione la hora', 'warning');
        return;
    }
    if (!tipo) {
        if (window.mostrarToast) window.mostrarToast('Seleccione el tipo de contacto', 'warning');
        return;
    }
    
    const data = {
        id_cliente: parseInt(clienteId),
        asunto: asunto,
        tipo: parseInt(tipo),
        fecha: fecha,
        hora: hora,
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