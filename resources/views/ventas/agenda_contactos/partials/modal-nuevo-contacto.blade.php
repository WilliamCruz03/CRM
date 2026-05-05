<!-- Modal Nuevo Contacto -->
<div class="modal fade" id="modalNuevoContacto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i> Registrar nueva agenda
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoContacto">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cliente_busqueda_nuevo" 
                                   placeholder="Buscar cliente por nombre, teléfono o correo" autocomplete="off">
                            <input type="hidden" id="cliente_id_nuevo">
                        </div>
                        <div id="resultados_clientes_nuevo" class="list-group mt-1" style="display: none; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Asunto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="asunto_nuevo" placeholder="Ingrese el asunto" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_nuevo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="hora_nuevo" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_nuevo" required>
                                <option value="">Seleccionar</option>
                                <option value="1">Llamada</option>
                                <option value="2">Mensaje</option>
                                <option value="3">Correo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recordatorio</label>
                            <select class="form-select" id="recordatorio_minutos_nuevo">
                                <option value="">Sin recordatorio</option>
                                @foreach($recordatorios as $rec)
                                    <option value="{{ $rec->valor }}">{{ $rec->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comentario</label>
                        <textarea class="form-control" id="comentario_nuevo" rows="3" placeholder="Notas adicionales..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarNuevoContacto">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Búsqueda de clientes para nuevo contacto
const buscarClienteNuevo = document.getElementById('cliente_busqueda_nuevo');
const resultadosClientesNuevo = document.getElementById('resultados_clientes_nuevo');
const clienteIdNuevo = document.getElementById('cliente_id_nuevo');

let timeoutBusquedaNuevo;

buscarClienteNuevo?.addEventListener('input', function() {
    const termino = this.value.trim();
    
    if (termino.length < 2) {
        resultadosClientesNuevo.style.display = 'none';
        return;
    }
    
    clearTimeout(timeoutBusquedaNuevo);
    timeoutBusquedaNuevo = setTimeout(() => {
        fetch(`{{ route("ventas.agenda_contactos.clientes.buscar") }}?q=${encodeURIComponent(termino)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    resultadosClientesNuevo.innerHTML = data.data.map(cliente => `
                        <button type="button" class="list-group-item list-group-item-action" 
                                data-id="${cliente.id_Cliente}"
                                data-nombre="${cliente.nombre_completo}"
                                data-telefono="${cliente.telefono1 || ''}">
                            <strong>${cliente.nombre_completo}</strong><br>
                            <small>${cliente.telefono1 || 'Sin teléfono'} - ${cliente.correo || ''}</small>
                        </button>
                    `).join('');
                    resultadosClientesNuevo.style.display = 'block';
                    
                    document.querySelectorAll('#resultados_clientes_nuevo .list-group-item').forEach(item => {
                        item.addEventListener('click', function() {
                            buscarClienteNuevo.value = this.dataset.nombre;
                            clienteIdNuevo.value = this.dataset.id;
                            resultadosClientesNuevo.style.display = 'none';
                        });
                    });
                } else {
                    resultadosClientesNuevo.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
                    resultadosClientesNuevo.style.display = 'block';
                }
            })
            .catch(error => console.error('Error:', error));
    }, 300);
});

// Ocultar resultados al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!buscarClienteNuevo?.contains(e.target) && !resultadosClientesNuevo?.contains(e.target)) {
        resultadosClientesNuevo.style.display = 'none';
    }
});

// Guardar nuevo contacto
document.getElementById('btnGuardarNuevoContacto')?.addEventListener('click', function() {
    const clienteId = clienteIdNuevo.value;
    const asunto = document.getElementById('asunto_nuevo').value;
    const fecha = document.getElementById('fecha_nuevo').value;
    const hora = document.getElementById('hora_nuevo').value;
    const tipo = document.getElementById('tipo_nuevo').value;
    
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
        recordatorio_minutos: document.getElementById('recordatorio_minutos_nuevo').value || null,
        comentario: document.getElementById('comentario_nuevo').value,
        _token: '{{ csrf_token() }}'
    };
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    
    fetch('{{ route("ventas.agenda_contactos.store") }}', {
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
            btn.innerHTML = '<i class="bi bi-save"></i> Guardar';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save"></i> Guardar';
    });
});
</script>
@endpush