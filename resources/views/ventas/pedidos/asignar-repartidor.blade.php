@extends('layouts.app')

@section('title', 'Asignar Repartidor')
@section('page-title', 'Asignar Repartidor - Pedido ' . $pedido->folio_pedido)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-badge"></i> Asignar Repartidor
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-light btn-sm float-end">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </h5>
        </div>
        <div class="card-body">
            <!-- Información del pedido -->
            <div class="alert alert-info">
                <strong>Pedido:</strong> {{ $pedido->folio_pedido }} |
                <strong>Cliente:</strong> {{ $pedido->cotizacion->nombre_cliente ?? 'N/A' }} |
                <strong>Total:</strong> ${{ number_format($pedido->importe_total ?? 0, 2) }}
            </div>

            <!-- Repartidores -->
            <h6 class="mt-3"><i class="bi bi-truck"></i> Repartidores disponibles</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tablaRepartidores">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">Seleccionar</th>
                            <th>Sucursal</th>
                            <th>Repartidor</th>
                            <th>Horario</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="repartidoresBody">
                        <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Entregas en curso -->
            <h6 class="mt-4"><i class="bi bi-clock-history"></i> Entregas en curso</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Repartidor</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Hora salida</th>
                            <th>Tiempo fuera</th>
                        </tr>
                    </thead>
                    <tbody id="entregasBody">
                        <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                @if($esRepartidor)
                    @php
                        // Verificar si ya tiene un recorrido activo para este pedido
                        $recorridoActivo = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                            ->where('id_personal', auth()->id())
                            ->where('folio_ticket', $pedido->id_pedido)
                            ->where('status', 0)
                            ->first();
                    @endphp
                    @if($recorridoActivo)
                        <button type="button" class="btn btn-warning" onclick="abrirModalFinalizarRecorrido({{ $recorridoActivo->id }})">
                            <i class="bi bi-stop-circle"></i> Finalizar recorrido
                        </button>
                    @else
                        <button type="button" class="btn btn-success" onclick="abrirModalIniciarRecorrido()">
                            <i class="bi bi-play-circle"></i> Iniciar recorrido
                        </button>
                    @endif
                @elseif($sucursalAsignada == 0)
                    @if($pedido->id_repartidor)
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="bi bi-check-circle"></i> Repartidor ya asignado
                        </button>
                    @else
                        <button type="button" class="btn btn-primary" id="btnAsignar" disabled>
                            <i class="bi bi-person-badge"></i> Asignar repartidor
                        </button>
                    @endif
                @endif
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Iniciar Recorrido -->
<div class="modal fade" id="modalIniciarRecorrido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-play-circle"></i> Iniciar Recorrido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formIniciarRecorrido">
                    @csrf
                    <input type="hidden" id="recorrido_pedido_id" value="{{ $pedido->id_pedido }}">
                    <input type="hidden" id="recorrido_id_personal" value="{{ auth()->id() }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Folio ticket (Número de ticket) *</label>
                        <input type="number" class="form-control" id="recorrido_folio_ticket" required>
                        <small class="text-muted">Número de ticket o folio de la venta</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del cliente *</label>
                        <input type="text" class="form-control" id="recorrido_nombrecliente" 
                               value="{{ $pedido->cotizacion->nombre_cliente ?? '' }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Domicilio *</label>
                        <input type="text" class="form-control" id="recorrido_domicilio" 
                               value="{{ $pedido->cotizacion->cliente->Domicilio ?? '' }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Importe del ticket *</label>
                        <input type="number" step="0.01" class="form-control" id="recorrido_importe" 
                               value="{{ $pedido->importe_total ?? 0 }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sucursal que solicita *</label>
                        <select class="form-select" id="recorrido_sucursal" required>
                            <option value="0">CRM (Sistema)</option>
                            <option value="1">Jardín</option>
                            <option value="2">Mercado</option>
                            <option value="3">Zacatipan</option>
                            <option value="4">Boulevard</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kilometraje inicial *</label>
                        <input type="number" class="form-control" id="recorrido_kminicial" required>
                        <small class="text-muted">Kilometraje actual de la moto/vehículo</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="iniciarRecorrido()">
                    <i class="bi bi-play-circle"></i> Iniciar Recorrido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Finalizar Recorrido -->
<div class="modal fade" id="modalFinalizarRecorrido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-stop-circle"></i> Finalizar Recorrido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formFinalizarRecorrido">
                    @csrf
                    <input type="hidden" id="finalizar_recorrido_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Kilometraje final *</label>
                        <input type="number" class="form-control" id="finalizar_kmfinal" required>
                        <small class="text-muted">Kilometraje actual al regresar</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Hora de regreso:</strong> Se registrará automáticamente al confirmar.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarFinalizarRecorrido()">
                    <i class="bi bi-check-lg"></i> Finalizar Recorrido
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let repartidorSeleccionadoId = null;
let intervaloActualizacion = null;
let puedeAsignar = false;
let esRepartidor = {{ $esRepartidor ? 'true' : 'false' }};
let sucursalAsignada = {{ $sucursalAsignada }};

const repartidorAsignadoId = {{ $pedido->id_repartidor ?? 'null' }};
const yaTieneRepartidor = repartidorAsignadoId !== null;

// Mapeo de sucursales
const sucursalesMap = {};
@foreach($sucursales as $sucursal)
    sucursalesMap[{{ $sucursal->id_sucursal }}] = '{{ $sucursal->nombre }}';
@endforeach
// Agregar CRM (sucursal 0)
sucursalesMap[0] = 'CRM';

// Cargar datos iniciales y cada 60 segundos
function cargarDatos() {
    fetch('{{ route("ventas.pedidos.repartidores.status", $pedido->id_pedido) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                puedeAsignar = (data.sucursal_asignada === 0 && !data.es_repartidor);
                actualizarTablaRepartidores(data.repartidores);
                actualizarTablaEntregas(data.entregas_curso);
                
                const btnAsignar = document.getElementById('btnAsignar');
                if (btnAsignar) {
                    if (puedeAsignar) {
                        btnAsignar.disabled = false;
                    } else {
                        btnAsignar.disabled = true;
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

function actualizarTablaRepartidores(repartidores) {
    const tbody = document.getElementById('repartidoresBody');
    if (!repartidores || repartidores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay repartidores disponibles</td></tr>';
        return;
    }
    
    // Verificar si el usuario es repartidor
    const esRepartidorUsuario = {{ $esRepartidor ? 'true' : 'false' }};
    
    // Verificar si el pedido ya tiene repartidor asignado
    const repartidorAsignado = {{ $pedido->id_repartidor ?? 'null' }};
    const yaTieneRepartidor = repartidorAsignado !== null;
    
    let html = '';
    repartidores.forEach(rep => {
        let statusColor = '';
        let statusIcon = '';
        let checkedAttr = '';
        let disabledAttr = '';
        
        switch(rep.status) {
            case 'Disponible':
                statusColor = 'success';
                statusIcon = 'bi-check-circle';
                break;
            case 'En recorrido':
                statusColor = 'warning';
                statusIcon = 'bi-truck';
                break;
            case 'Fuera de horario':
                statusColor = 'secondary';
                statusIcon = 'bi-clock';
                break;
            default:
                statusColor = 'danger';
                statusIcon = 'bi-exclamation-circle';
        }
        
        // Obtener nombre de la sucursal
        const nombreSucursal = sucursalesMap[rep.sucursal] || `Sucursal ${rep.sucursal}`;
        
        // Si el pedido ya tiene repartidor, deshabilitar todos los radios
        if (yaTieneRepartidor) {
            disabledAttr = 'disabled';
            // Si es el repartidor asignado, marcarlo como checked
            if (rep.id === repartidorAsignado) {
                checkedAttr = 'checked';
                disabledAttr = ''; // Habilitar solo el seleccionado (para mostrarlo marcado)
            }
        }
        
        // Solo permitir seleccionar si NO es repartidor, puede asignar, repartidor disponible, y no hay repartidor asignado
        const puedeSeleccionar = !esRepartidorUsuario && puedeAsignar && rep.status === 'Disponible' && !yaTieneRepartidor;
        
        // Columna de selección: oculta para repartidores
        let columnaSeleccion = '';
        if (esRepartidorUsuario) {
            columnaSeleccion = '<span class="text-muted">---</span>';
        } else if (puedeSeleccionar) {
            columnaSeleccion = `<input type="radio" name="repartidor" value="${rep.id}" data-nombre="${rep.nombre}">`;
        } else if (checkedAttr) {
            columnaSeleccion = `<input type="radio" name="repartidor" value="${rep.id}" data-nombre="${rep.nombre}" ${checkedAttr} ${disabledAttr}>`;
        } else {
            columnaSeleccion = '<span class="text-muted">---</span>';
        }
        
        html += `
            <tr>
                <td class="text-center">${columnaSeleccion}</td>
                <td>${nombreSucursal}</td>
                <td><strong>${rep.nombre}</strong></td>
                <td>${rep.horario_entrada ? rep.horario_entrada.substring(0,5) : '--'} - ${rep.horario_salida ? rep.horario_salida.substring(0,5) : '--'}</td>
                <td><span class="badge bg-${statusColor}"><i class="bi ${statusIcon}"></i> ${rep.status}</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Agregar event listeners a los radios (solo si NO es repartidor y no tiene repartidor asignado)
    if (!esRepartidorUsuario && !yaTieneRepartidor) {
        document.querySelectorAll('input[name="repartidor"]:not([disabled])').forEach(radio => {
            radio.addEventListener('change', function() {
                repartidorSeleccionadoId = this.value;
                const btnAsignar = document.getElementById('btnAsignar');
                if (btnAsignar) btnAsignar.disabled = false;
            });
        });
    } else {
        // Si ya tiene repartidor, deshabilitar el botón de asignar
        const btnAsignar = document.getElementById('btnAsignar');
        if (btnAsignar) btnAsignar.disabled = true;
    }
}

function actualizarTablaEntregas(entregas) {
    const tbody = document.getElementById('entregasBody');
    if (!entregas || entregas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay entregas en curso</td></tr>';
        return;
    }
    
    let html = '';
    entregas.forEach(entrega => {
        // Guardar hora_salida como atributo data para actualizar después
        const horaSalida = entrega.hora_salida || '';
        
        html += `
            <tr data-recibido-id="${entrega.id}">
                <td><strong>${entrega.repartidor_nombre} ${entrega.repartidor_apaterno || ''}</strong></td>
                <td>${entrega.nombrecliente || 'N/A'}</td>
                <td>${entrega.Domicilio || 'N/A'}</td>
                <td>${horaSalida ? horaSalida.substring(0,5) : 'N/A'}</td>
                <td><span class="badge bg-info tiempo-fuera" data-inicio="${horaSalida}">00:00:00</span></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    
    // Iniciar actualización en tiempo real
    actualizarTiemposFuera();
}

function actualizarTiemposFuera() {
    const elementos = document.querySelectorAll('.tiempo-fuera');
    elementos.forEach(el => {
        const horaInicioStr = el.getAttribute('data-inicio');
        if (horaInicioStr) {
            // Extraer hora, minuto, segundo de la hora de inicio
            const partes = horaInicioStr.split(':');
            if (partes.length >= 2) {
                const ahora = new Date();
                const inicio = new Date(ahora);
                inicio.setHours(parseInt(partes[0]), parseInt(partes[1]), partes[2] ? parseInt(partes[2]) : 0, 0);
                
                let diffMs = ahora - inicio;
                if (diffMs < 0) diffMs = 0;
                
                const diffHoras = Math.floor(diffMs / 3600000);
                const diffMinutos = Math.floor((diffMs % 3600000) / 60000);
                const diffSegundos = Math.floor((diffMs % 60000) / 1000);
                
                el.textContent = `${String(diffHoras).padStart(2, '0')}:${String(diffMinutos).padStart(2, '0')}:${String(diffSegundos).padStart(2, '0')}`;
            }
        }
    });
}

// Iniciar intervalo para actualizar tiempos cada segundo
let intervaloTiempos = null;

// Al cargar los datos, asegurar que el intervalo esté corriendo
function iniciarActualizacionTiempos() {
    if (intervaloTiempos) clearInterval(intervaloTiempos);
    intervaloTiempos = setInterval(actualizarTiemposFuera, 1000);
}

// Llamar a esta función después de cargar los datos
function cargarDatos() {
    fetch('{{ route("ventas.pedidos.repartidores.status", $pedido->id_pedido) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                puedeAsignar = (data.sucursal_asignada === 0 && !data.es_repartidor);
                actualizarTablaRepartidores(data.repartidores);
                actualizarTablaEntregas(data.entregas_curso);
                
                const btnAsignar = document.getElementById('btnAsignar');
                if (btnAsignar) {
                    btnAsignar.disabled = !puedeAsignar;
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Iniciar polling cada 60 segundos y actualización de tiempos cada 1 segundo
cargarDatos();
intervaloActualizacion = setInterval(cargarDatos, 60000);
iniciarActualizacionTiempos();

// Limpiar intervalos al salir
window.addEventListener('beforeunload', function() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    if (intervaloTiempos) clearInterval(intervaloTiempos);
});

// Asignar repartidor
const btnAsignar = document.getElementById('btnAsignar');
if (btnAsignar) {
    btnAsignar.addEventListener('click', function() {
        if (!repartidorSeleccionadoId) {
            if (window.mostrarToast) {
                window.mostrarToast('Selecciona un repartidor antes de asignar', 'warning');
            }
            return;
        }
        
        fetch('{{ route("ventas.pedidos.asignarRepartidor", $pedido->id_pedido) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id_repartidor: parseInt(repartidorSeleccionadoId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.mostrarToast) window.mostrarToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("ventas.pedidos.index") }}';
                }, 1000);
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al asignar repartidor', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    });
}

// ============================================
// FUNCIONES PARA REPARTIDOR
// ============================================

// ============================================
// FUNCIONES PARA REPARTIDOR
// ============================================

function abrirModalIniciarRecorrido() {
    // Limpiar campos
    document.getElementById('recorrido_folio_ticket').value = '';
    document.getElementById('recorrido_nombrecliente').value = '{{ $pedido->cotizacion->nombre_cliente ?? '' }}';
    document.getElementById('recorrido_domicilio').value = '{{ $pedido->cotizacion->cliente->Domicilio ?? '' }}';
    document.getElementById('recorrido_importe').value = '{{ $pedido->importe_total ?? 0 }}';
    document.getElementById('recorrido_kminicial').value = '';
    document.getElementById('recorrido_sucursal').value = '{{ $sucursalAsignada }}';
    
    const modal = new bootstrap.Modal(document.getElementById('modalIniciarRecorrido'));
    modal.show();
}

function iniciarRecorrido() {
    const folioTicket = document.getElementById('recorrido_folio_ticket').value;
    const nombreCliente = document.getElementById('recorrido_nombrecliente').value;
    const domicilio = document.getElementById('recorrido_domicilio').value;
    const importe = document.getElementById('recorrido_importe').value;
    const kmInicial = document.getElementById('recorrido_kminicial').value;
    const sucursal = document.getElementById('recorrido_sucursal').value;
    const pedidoId = document.getElementById('recorrido_pedido_id').value;
    const idPersonal = document.getElementById('recorrido_id_personal').value;
    
    // Validaciones con toast
    if (!folioTicket) {
        if (window.mostrarToast) window.mostrarToast('El folio ticket es obligatorio', 'warning');
        return;
    }
    if (!nombreCliente) {
        if (window.mostrarToast) window.mostrarToast('El nombre del cliente es obligatorio', 'warning');
        return;
    }
    if (!domicilio) {
        if (window.mostrarToast) window.mostrarToast('El domicilio es obligatorio', 'warning');
        return;
    }
    if (!kmInicial) {
        if (window.mostrarToast) window.mostrarToast('El kilometraje inicial es obligatorio', 'warning');
        return;
    }
    
    // Deshabilitar botón mientras se procesa
    const btn = document.querySelector('#modalIniciarRecorrido .btn-success');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch('{{ route("recorridos.iniciar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id_personal: parseInt(idPersonal),
            fecha: new Date().toISOString().split('T')[0],
            folio_ticket: parseInt(folioTicket),
            importeticket: parseFloat(importe),
            nombrecliente: nombreCliente,
            Domicilio: domicilio,
            kminicial: parseInt(kmInicial),
            Solicitadoensucursal: parseInt(sucursal),
            hora_salida: new Date().toLocaleTimeString('es-MX', { hour12: false }),
            pedido_id: parseInt(pedidoId)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalIniciarRecorrido'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al iniciar recorrido', 'danger');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

let recorridoIdActual = null;

function abrirModalFinalizarRecorrido(recorridoId) {
    recorridoIdActual = recorridoId;
    document.getElementById('finalizar_recorrido_id').value = recorridoId;
    document.getElementById('finalizar_kmfinal').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalFinalizarRecorrido'));
    modal.show();
}

function confirmarFinalizarRecorrido() {
    const kmFinal = document.getElementById('finalizar_kmfinal').value;
    const recorridoId = document.getElementById('finalizar_recorrido_id').value;
    
    if (!kmFinal) {
        if (window.mostrarToast) window.mostrarToast('El kilometraje final es obligatorio', 'warning');
        return;
    }
    
    const btn = document.querySelector('#modalFinalizarRecorrido .btn-warning');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch(`/recorridos/${recorridoId}/finalizar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            kmfinal: parseInt(kmFinal),
            hora_regreso: new Date().toLocaleTimeString('es-MX', { hour12: false })
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalFinalizarRecorrido'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al finalizar recorrido', 'danger');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
@endsection