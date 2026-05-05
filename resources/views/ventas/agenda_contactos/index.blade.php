@extends('layouts.app')

@section('title', 'Agenda de Próximos Contactos')
@section('page-title', 'Agenda de Próximos Contactos')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarContacto" placeholder="Buscar por cliente o asunto...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            @if($permisos['crear'])
                <button type="button" class="btn btn-primary" id="btnNuevoContacto">
                    <i class="bi bi-plus-circle"></i> Nueva Agenda
                </button>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-end align-items-center gap-2">
                <span class="text-muted"><i class="bi bi-funnel"></i> Filtrar por:</span>
                <select id="filtroEstado" class="form-select w-auto" style="width: auto;">
                    <option value="todos">Todos</option>
                    <option value="1">Pendiente</option>
                    <option value="2">Realizado</option>
                    <option value="3">Cancelado</option>
                </select>
                <span class="text-muted ms-2"><i class="bi bi-calendar-week"></i> Período:</span>
                <select id="filtroPeriodo" class="form-select w-auto" style="width: auto;">
                    <option value="todos">Todos</option>
                    <option value="hoy">Hoy</option>
                    <option value="semana">Esta semana</option>
                    <option value="mes">Este mes</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Cliente</th>
                            <th>Asunto</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            @if($permisos['editar'] || $permisos['eliminar'])
                                <th style="width: 120px">Acciones</th>
                            @endif
                        <tr>
                    </thead>
                    <tbody id="contactosTableBody">
                        @forelse($contactos as $contacto)
                        <tr data-id="{{ $contacto->id_agenda_contacto }}" data-estado="{{ $contacto->estado }}" data-fecha="{{ $contacto->fecha }}">
                            <td>
                                {{ $contacto->fecha->format('d/m/Y') }} <br>
                                <small class="text-muted">{{ substr($contacto->hora, 0, 5) }} hrs</small>
                            </td>
                            <td>
                                <strong>{{ $contacto->nombre_cliente }}</strong><br>
                                <small class="text-muted"><i class="bi bi-telephone"></i> {{ $contacto->telefono_cliente }}</small>
                            </td>
                            <td>{{ $contacto->asunto }}</td>
                            <td>
                                @php
                                    $tipoClass = match($contacto->tipo) {
                                        1 => 'bg-info',
                                        2 => 'bg-success',
                                        3 => 'bg-primary',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $tipoClass }}">{{ $contacto->tipo_nombre }}</span>
                            </td>
                            <td>
                                @php
                                    $estadoClass = match($contacto->estado) {
                                        1 => 'bg-warning',
                                        2 => 'bg-success',
                                        3 => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $estadoClass }}">{{ $contacto->estado_nombre }}</span>
                            </td>
                            @if($permisos['editar'] || $permisos['eliminar'])
                            <td>
                                <div class="btn-group" role="group">
                                    @if($permisos['editar'])
                                        <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                                onclick="marcarRealizado({{ $contacto->id_agenda_contacto }}, '{{ $contacto->nombre_cliente }}')"
                                                title="Marcar como realizado">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                onclick="editarContacto({{ $contacto->id_agenda_contacto }})"
                                                title="Editar contacto">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                    @if($permisos['eliminar'])
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                onclick="eliminarContacto({{ $contacto->id_agenda_contacto }})"
                                                title="Eliminar contacto">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ ($permisos['editar'] || $permisos['eliminar']) ? 6 : 5 }}" class="text-center py-4">
                                <i class="bi bi-calendar-x" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay contactos programados</p>
                                @if($permisos['crear'])
                                    <button class="btn btn-sm btn-primary" onclick="document.getElementById('btnNuevoContacto').click()">
                                        <i class="bi bi-plus-circle"></i> Programar primer contacto
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('ventas.agenda_contactos.partials.modal-nuevo-contacto')
@include('ventas.agenda_contactos.partials.modal-editar-contacto')

<style>
    .search-box {
        position: relative;
    }
    .search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    .search-box input {
        padding-left: 35px;
    }
    .btn-action {
        margin: 0 2px;
    }
</style>
@endsection

@push('scripts')
<script>
const permisos = {
    crear: {{ $permisos['crear'] ? 'true' : 'false' }},
    editar: {{ $permisos['editar'] ? 'true' : 'false' }},
    eliminar: {{ $permisos['eliminar'] ? 'true' : 'false' }}
};

// ============================================
// FILTROS
// ============================================
function filtrarContactos() {
    const searchTerm = document.getElementById('buscarContacto')?.value.toLowerCase().trim() || '';
    const estadoFiltro = document.getElementById('filtroEstado')?.value || 'todos';
    const periodoFiltro = document.getElementById('filtroPeriodo')?.value || 'todos';
    const hoy = new Date();
    const inicioSemana = new Date(hoy);
    inicioSemana.setDate(hoy.getDate() - hoy.getDay());
    const finSemana = new Date(inicioSemana);
    finSemana.setDate(inicioSemana.getDate() + 6);
    
    const rows = document.querySelectorAll('#contactosTableBody tr');
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        
        const texto = row.textContent.toLowerCase();
        const estado = row.dataset.estado || '';
        const fechaStr = row.dataset.fecha || '';
        const fecha = new Date(fechaStr);
        
        let coincideTexto = !searchTerm || texto.includes(searchTerm);
        let coincideEstado = estadoFiltro === 'todos' || estado === estadoFiltro;
        let coincidePeriodo = true;
        
        if (periodoFiltro === 'hoy') {
            coincidePeriodo = fecha.toDateString() === hoy.toDateString();
        } else if (periodoFiltro === 'semana') {
            coincidePeriodo = fecha >= inicioSemana && fecha <= finSemana;
        } else if (periodoFiltro === 'mes') {
            coincidePeriodo = fecha.getMonth() === hoy.getMonth() && fecha.getFullYear() === hoy.getFullYear();
        }
        
        row.style.display = (coincideTexto && coincideEstado && coincidePeriodo) ? '' : 'none';
    });
}

document.getElementById('filtroEstado')?.addEventListener('change', filtrarContactos);
document.getElementById('filtroPeriodo')?.addEventListener('change', filtrarContactos);
document.getElementById('buscarContacto')?.addEventListener('keyup', filtrarContactos);

// ============================================
// NUEVO CONTACTO
// ============================================
document.getElementById('btnNuevoContacto')?.addEventListener('click', function() {
    // Limpiar campos manualmente en lugar de reset()
    const clienteIdInput = document.getElementById('cliente_id_nuevo');
    const buscarInput = document.getElementById('buscarClienteNuevo');
    const clienteSeleccionado = document.getElementById('clienteSeleccionadoNuevo');
    const resultadosDiv = document.getElementById('resultadosClientesNuevo');
    const asunto = document.getElementById('asunto_nuevo');
    const fecha = document.getElementById('fecha_nuevo');
    const hora = document.getElementById('hora_nuevo');
    const tipo = document.getElementById('tipo_nuevo');
    const recordatorio = document.getElementById('recordatorio_minutos_nuevo');
    const comentario = document.getElementById('comentario_nuevo');
    
    // Limpiar cliente
    if (clienteIdInput) clienteIdInput.value = '';
    if (buscarInput) buscarInput.value = '';
    if (clienteSeleccionado) clienteSeleccionado.style.display = 'none';
    if (resultadosDiv) resultadosDiv.style.display = 'none';
    
    // Limpiar campos del formulario
    if (asunto) asunto.value = '';
    if (tipo) tipo.value = '';
    if (recordatorio) recordatorio.value = '';
    if (comentario) comentario.value = '';
    
    // Establecer fecha y hora por defecto
    const hoy = new Date().toISOString().split('T')[0];
    const horaActual = new Date().toLocaleTimeString('es-MX', { hour12: false }).substring(0, 5);
    if (fecha) fecha.value = hoy;
    if (hora) hora.value = horaActual;
    
    new bootstrap.Modal(document.getElementById('modalNuevoContacto')).show();
});

// ============================================
// EDITAR CONTACTO
// ============================================
window.editarContacto = function(id) {
    fetch(`/ventas/agenda-contactos/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Datos básicos
                document.getElementById('contacto_id_edit').value = data.data.id_agenda_contacto;
                document.getElementById('cliente_id_edit').value = data.data.id_cliente;
                document.getElementById('asunto_edit').value = data.data.asunto;
                document.getElementById('fecha_edit').value = data.data.fecha;
                document.getElementById('hora_edit').value = data.data.hora.substring(0, 5);
                document.getElementById('tipo_edit').value = data.data.tipo;
                document.getElementById('recordatorio_minutos_edit').value = data.data.recordatorio_minutos || '';
                document.getElementById('comentario_edit').value = data.data.comentario || '';
                
                // Datos del cliente (solo lectura)
                document.getElementById('cliente_nombre_edit').innerHTML = data.data.nombre_cliente || 'N/A';
                
                let contactoHtml = '';
                if (data.data.telefono1) {
                    contactoHtml += `<i class="bi bi-telephone"></i> ${data.data.telefono1} `;
                }
                if (data.data.email1) {
                    contactoHtml += ` | <i class="bi bi-envelope"></i> ${data.data.email1}`;
                }
                document.getElementById('cliente_contacto_edit').innerHTML = contactoHtml || 'Sin contacto';
                
                if (data.data.domicilio) {
                    document.getElementById('cliente_direccion_edit').innerHTML = `<i class="bi bi-geo-alt"></i> ${data.data.domicilio}`;
                } else {
                    document.getElementById('cliente_direccion_edit').innerHTML = '';
                }
                
                new bootstrap.Modal(document.getElementById('modalEditarContacto')).show();
            }
        })
        .catch(error => console.error('Error:', error));
};

// ============================================
// MARCAR REALIZADO
// ============================================
window.marcarRealizado = function(id, nombre) {
    window.confirmarEliminar('contacto_realizado', id, nombre, function() {
        fetch(`/ventas/agenda-contactos/${id}/estado`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ estado: 2 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.mostrarToast) window.mostrarToast(data.message, 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
            }
        })
        .catch(error => console.error('Error:', error));
    });
};

// ============================================
// ELIMINAR CONTACTO
// ============================================
window.eliminarContacto = function(id) {
    if (!confirm('¿Eliminar este contacto permanentemente?')) return;
    
    fetch(`/ventas/agenda-contactos/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
        }
    })
    .catch(error => console.error('Error:', error));
};
</script>
@endpush