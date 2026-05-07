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

        <!-- Modal Motivo Reagenda -->
    <div class="modal fade" id="modalMotivoReagenda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-repeat"></i> Reagendar contacto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Contacto a reagendar:</strong> <span id="reagenda_contacto_nombre"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo de reagenda <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivo_reagenda" rows="3" 
                                placeholder="Ingrese el motivo por el cual se reagenda este contacto..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnConfirmarReagenda">
                        <i class="bi bi-arrow-repeat"></i> Continuar con reagenda
                    </button>
                </div>
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
                        </tr>
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
                                    $tipoInfo = $tiposAgenda->firstWhere('id_tipo', $contacto->tipo);
                                    $tipoNombre = $tipoInfo->nombre ?? 'Desconocido';
                                    $tipoClass = match($contacto->tipo) {
                                        1 => 'bg-info',
                                        2 => 'bg-success',
                                        3 => 'bg-primary',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $tipoClass }}">{{ $tipoNombre }}</span>
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
                                    @if($permisos['editar'] && $contacto->estado == 1)
                                        <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                                onclick="marcarRealizado({{ $contacto->id_agenda_contacto }}, '{{ $contacto->nombre_cliente }}')"
                                                title="Realizado">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                onclick="reagendarContacto({{ $contacto->id_agenda_contacto }}, '{{ $contacto->nombre_cliente }}')"
                                                title="Reagendar">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                onclick="editarContacto({{ $contacto->id_agenda_contacto }})"
                                                title="Editar contacto">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                    
                                    @if($permisos['eliminar'])
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                onclick="confirmarEliminarContacto({{ $contacto->id_agenda_contacto }}, '{{ $contacto->nombre_cliente }}')"
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
    @if(is_object($contactos) && method_exists($contactos, 'links'))
    <div class="d-flex justify-content-end mt-3">
        {{ $contactos->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif

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
    .highlight-row {
    background-color: #fff3cd !important;
    transition: background-color 0.5s ease;
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
window.confirmarEliminarContacto = function(id, nombre) {
    window.confirmarEliminar('agenda_contacto', id, nombre, function() {
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
    });
};

// ============================================
// RESALTAR REGISTRO DESDE NOTIFICACIÓN
// ============================================
const destacarId = {{ $destacarId ?? 'null' }};

if (destacarId) {
    // Remover el parámetro 'destacar' de la URL sin recargar la página
    const url = new URL(window.location.href);
    if (url.searchParams.has('destacar')) {
        url.searchParams.delete('destacar');
        window.history.replaceState({}, document.title, url.toString());
    }
    
    // Esperar a que la tabla esté cargada
    setTimeout(() => {
        const fila = document.querySelector(`tr[data-id="${destacarId}"]`);
        
        if (fila) {
            // Aplicar resaltado
            fila.classList.add('table-warning', 'highlight-row');
            
            // Desplazar hacia la fila
            fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Quitar resaltado después de 3 segundos
            setTimeout(() => {
                fila.classList.remove('table-warning', 'highlight-row');
            }, 3000);
        }
    }, 500);
}

// ============================================
// REAGENDAR CONTACTO
// ============================================
// Variables para reagenda
let reagendaIdOriginal = null;
let reagendaMotivo = null;

window.reagendarContacto = function(id, nombre) {
    reagendaIdOriginal = id;
    reagendaMotivo = null;
    document.getElementById('reagenda_contacto_nombre').textContent = nombre;
    document.getElementById('motivo_reagenda').value = '';
    new bootstrap.Modal(document.getElementById('modalMotivoReagenda')).show();
};

document.getElementById('btnConfirmarReagenda')?.addEventListener('click', function() {
    const motivo = document.getElementById('motivo_reagenda').value.trim();
    
    if (!motivo) {
        if (window.mostrarToast) window.mostrarToast('Ingrese el motivo de la reagenda', 'warning');
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch(`/ventas/agenda-contactos/${reagendaIdOriginal}/reagendar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ motivo: motivo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Guardar motivo y datos para cuando se guarde el nuevo contacto
            reagendaMotivo = data.motivo;
            
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            
            // Abrir modal nuevo con los datos del cliente pre-cargados
            abrirNuevoConDatosCliente(data.nuevo_contacto);
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalMotivoReagenda'));
            modal.hide();
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Continuar con reagenda';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Continuar con reagenda';
    });
});

// Función para abrir modal nuevo con datos del cliente pre-cargados
function abrirNuevoConDatosCliente(datos) {
    // Limpiar campos del modal nuevo
    document.getElementById('cliente_id_nuevo').value = datos.id_cliente;
    document.getElementById('buscarClienteNuevo').value = datos.nombre_cliente;
    document.getElementById('asunto_nuevo').value = datos.asunto;
    document.getElementById('tipo_nuevo').value = datos.tipo;
    document.getElementById('comentario_nuevo').value = datos.comentario || '';
    document.getElementById('recordatorio_minutos_nuevo').value = datos.recordatorio_minutos || '';
    
    // Guardar el ID del origen para el nuevo registro
    document.getElementById('agenda_origen').value = datos.id_original;
    
    // Mostrar cliente seleccionado
    let html = `<div><strong>${datos.nombre_cliente}</strong>`;
    if (datos.telefono1) {
        html += `<br><small class="text-muted"><i class="bi bi-telephone"></i> ${datos.telefono1}</small>`;
    }
    if (datos.email1) {
        html += `<br><small class="text-muted"><i class="bi bi-envelope"></i> ${datos.email1}</small>`;
    }
    if (datos.domicilio) {
        html += `<br><small class="text-muted"><i class="bi bi-geo-alt"></i> ${datos.domicilio}</small>`;
    }
    html += `</div>`;
    
    document.getElementById('clienteInfoNuevo').innerHTML = html;
    document.getElementById('clienteSeleccionadoNuevo').style.display = 'block';
    
    // Usar fecha y hora del registro original si existen, si no, usar valores por defecto
    if (datos.fecha_original && datos.hora_original) {
        document.getElementById('fecha_nuevo').value = datos.fecha_original;
        document.getElementById('hora_nuevo').value = datos.hora_original;
    } else {
        // Fecha y hora por defecto (hoy + 1 hora)
        const hoy = new Date();
        const fechaDefault = hoy.toISOString().split('T')[0];
        const horaActual = `${hoy.getHours().toString().padStart(2, '0')}:${hoy.getMinutes().toString().padStart(2, '0')}`;
        document.getElementById('fecha_nuevo').value = fechaDefault;
        document.getElementById('hora_nuevo').value = horaActual;
    }
    
    new bootstrap.Modal(document.getElementById('modalNuevoContacto')).show();
}

// Resetear botón cuando se cierra el modal de motivo
document.getElementById('modalMotivoReagenda')?.addEventListener('hidden.bs.modal', function() {
    const btn = document.getElementById('btnConfirmarReagenda');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Continuar con reagenda';
    }
    reagendaIdOriginal = null;
    reagendaMotivo = null;
});
</script>
@endpush