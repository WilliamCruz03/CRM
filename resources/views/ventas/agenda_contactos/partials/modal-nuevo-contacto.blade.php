<!-- Modal Nuevo Contacto -->
<div class="modal fade" id="modalNuevoContacto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                    <input type="hidden" id="cliente_id_nuevo">
                    <input type="hidden" id="agenda_origen_nuevo">
                    
                    <div class="mb-3">
                        <label class="form-label">Buscar cliente <span class="text-danger">*</span></label>
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="buscarClienteNuevo" 
                                placeholder="Buscar por nombre o teléfono..."
                                autocomplete="off">
                        </div>
                        <small class="text-muted">Los resultados aparecerán automáticamente. <b class="text-success">HAZ CLICK EN UNO PARA SELECCIONARLO.</b></small>
                    </div>
                    
                    <!-- Cliente seleccionado -->
                    <div id="clienteSeleccionadoNuevo" class="alert alert-info mt-2" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="clienteInfoNuevo"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarClienteNuevo()">
                                <i class="bi bi-x-circle"></i> Cambiar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Resultados de búsqueda -->
                    <div id="resultadosClientesNuevo" class="mt-2" style="display: none;">
                        <div class="list-group" id="listaClientesNuevo"></div>
                    </div>
                    
                    <div class="mb-3 mt-3">
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
                                @foreach($tiposAgenda as $tipo)
                                    <option value="{{ $tipo->id_tipo }}">{{ $tipo->nombre }}</option>
                                @endforeach
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
const buscarClienteNuevoInput = document.getElementById('buscarClienteNuevo');
let timeoutBusquedaNuevo;

function buscarClientesNuevo(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosClientesNuevo').style.display = 'none';
        return;
    }
    
    fetch(`{{ route("ventas.agenda_contactos.clientes.buscar") }}?q=${encodeURIComponent(termino)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const resultadosDiv = document.getElementById('resultadosClientesNuevo');
        const listaResultados = document.getElementById('listaClientesNuevo');
        
        if (data.success && data.data && data.data.length > 0) {
            listaResultados.innerHTML = data.data.map(cliente => {
                const id = cliente.id_Cliente || 0;
                const nombre = cliente.nombre_completo || '';
                const telefono1 = cliente.telefono1 || '';
                const telefono2 = cliente.telefono2 || '';
                const email1 = cliente.email1 || '';
                const titulo = cliente.titulo || '';
                const domicilio = cliente.domicilio || '';
                
                let contactoHtml = '';
                let tieneContacto = false;
                
                if (telefono1 && telefono1 !== 'null' && telefono1 !== '') {
                    contactoHtml += `<i class="bi bi-telephone"></i> ${telefono1}<br>`;
                    tieneContacto = true;
                }
                if (telefono2 && telefono2 !== 'null' && telefono2 !== '') {
                    contactoHtml += `<i class="bi bi-telephone"></i> ${telefono2} (secundario)<br>`;
                    tieneContacto = true;
                }
                if (email1 && email1 !== 'null' && email1 !== '') {
                    contactoHtml += `<i class="bi bi-envelope"></i> ${email1}`;
                    tieneContacto = true;
                }
                
                if (!tieneContacto) {
                    contactoHtml = '<span class="text-muted">Sin contacto</span>';
                }
                
                let tituloHtml = '';
                if (titulo && titulo !== 'null' && titulo.trim() !== '') {
                    tituloHtml = `<br><small class="text-muted">${escapeHtml(titulo)}</small>`;
                }
                
                let direccionHtml = '';
                if (domicilio && domicilio !== 'null' && domicilio.trim() !== '') {
                    direccionHtml = `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(domicilio)}</small>`;
                }
                
                const nombreEscapado = escapeHtml(nombre).replace(/'/g, "\\'");
                const emailEscapado = escapeHtml(email1).replace(/'/g, "\\'");
                const telefono1Escapado = escapeHtml(telefono1).replace(/'/g, "\\'");
                const telefono2Escapado = escapeHtml(telefono2).replace(/'/g, "\\'");
                const tituloEscapado = escapeHtml(titulo).replace(/'/g, "\\'");
                const domicilioEscapado = escapeHtml(domicilio).replace(/'/g, "\\'");
                
                return `
                    <div class="list-group-item list-group-item-action" style="cursor: pointer;" 
                         onclick="seleccionarClienteNuevo(${id}, '${nombreEscapado}', '${emailEscapado}', '${telefono1Escapado}', '${telefono2Escapado}', '${domicilioEscapado}', '${tituloEscapado}')">
                        <div>
                            <strong>${escapeHtml(nombre)}</strong>
                            ${tituloHtml}
                            <div class="small text-muted mt-1">${contactoHtml}</div>
                            ${direccionHtml}
                        </div>
                    </div>
                `;
            }).join('');
            resultadosDiv.style.display = 'block';
        } else {
            listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
            resultadosDiv.style.display = 'block';
        }
    })
    .catch(error => console.error('Error buscando clientes:', error));
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

window.seleccionarClienteNuevo = function(id, nombre, email, telefono1, telefono2, domicilio, titulo) {
    document.getElementById('cliente_id_nuevo').value = id;
    
    let html = `<div><strong>${nombre}</strong>`;
    
    if (titulo && titulo !== 'null' && titulo.trim() !== '') {
        html += `<br><small class="text-muted">${titulo}</small>`;
    }
    
    let contactoParts = [];
    if (telefono1 && telefono1 !== 'null' && telefono1 !== '') {
        contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono1}`);
    }
    if (telefono2 && telefono2 !== 'null' && telefono2 !== '') {
        contactoParts.push(`<i class="bi bi-telephone"></i> ${telefono2} (secundario)`);
    }
    if (email && email !== 'null' && email !== '') {
        contactoParts.push(`<i class="bi bi-envelope"></i> ${email}`);
    }
    
    if (contactoParts.length > 0) {
        html += `<br><small class="text-muted">${contactoParts.join(' | ')}</small>`;
    }
    
    if (domicilio && domicilio !== 'null' && domicilio.trim() !== '') {
        html += `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${domicilio}</small>`;
    }
    
    html += `</div>`;
    
    document.getElementById('clienteInfoNuevo').innerHTML = html;
    document.getElementById('clienteSeleccionadoNuevo').style.display = 'block';
    document.getElementById('resultadosClientesNuevo').style.display = 'none';
    document.getElementById('buscarClienteNuevo').value = nombre;
};

window.limpiarClienteNuevo = function() {
    const clienteIdInput = document.getElementById('cliente_id_nuevo');
    const clienteSeleccionado = document.getElementById('clienteSeleccionadoNuevo');
    const buscarInput = document.getElementById('buscarClienteNuevo');
    const resultadosDiv = document.getElementById('resultadosClientesNuevo');
    
    if (clienteIdInput) clienteIdInput.value = '';
    if (clienteSeleccionado) clienteSeleccionado.style.display = 'none';
    if (buscarInput) buscarInput.value = '';
    if (resultadosDiv) resultadosDiv.style.display = 'none';
};

buscarClienteNuevoInput?.addEventListener('input', function() {
    const termino = this.value.trim();
    const clienteIdInput = document.getElementById('cliente_id_nuevo');
    
    if (termino === '' && clienteIdInput && clienteIdInput.value !== '') {
        limpiarClienteNuevo();
        return;
    }
    
    clearTimeout(timeoutBusquedaNuevo);
    timeoutBusquedaNuevo = setTimeout(() => {
        buscarClientesNuevo(termino);
    }, 300);
});

// Ocultar resultados al hacer clic fuera
document.addEventListener('click', function(e) {
    const resultadosDiv = document.getElementById('resultadosClientesNuevo');
    const buscarInput = document.getElementById('buscarClienteNuevo');
    if (!buscarInput?.contains(e.target) && !resultadosDiv?.contains(e.target)) {
        resultadosDiv.style.display = 'none';
    }
});

// Guardar nuevo contacto
document.getElementById('btnGuardarNuevoContacto')?.addEventListener('click', function() {
    const campos = [
        { id: 'cliente_id_nuevo', nombre: 'cliente', mensaje: 'Seleccione un cliente' },
        { id: 'asunto_nuevo', nombre: 'asunto', mensaje: 'Ingrese el asunto' },
        { id: 'fecha_nuevo', nombre: 'fecha', mensaje: 'Seleccione la fecha' },
        { id: 'hora_nuevo', nombre: 'hora', mensaje: 'Seleccione la hora' },
        { id: 'tipo_nuevo', nombre: 'tipo', mensaje: 'Seleccione el tipo de contacto' }
    ];
    
    for (const campo of campos) {
        const valor = document.getElementById(campo.id)?.value;
        if (!valor) {
            if (window.mostrarToast) window.mostrarToast(campo.mensaje, 'warning');
            return;
        }
    }
    
    const data = {
        id_cliente: parseInt(document.getElementById('cliente_id_nuevo').value),
        asunto: document.getElementById('asunto_nuevo').value,
        tipo: parseInt(document.getElementById('tipo_nuevo').value),
        fecha: document.getElementById('fecha_nuevo').value,
        hora: document.getElementById('hora_nuevo').value,
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