@extends('layouts.app')

@section('title', 'Mis Recorridos')
@section('page-title', 'Mis Recorridos')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-truck"></i> Mis Recorridos
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-light btn-sm float-end">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </h5>
        </div>
        <div class="card-body">
            <!-- Mostrar recorrido activo si existe -->
            @php
                $recorridoActivo = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                    ->where('id_personal', auth()->id())
                    ->where('status', 0)
                    ->first();
            @endphp

            @if($recorridoActivo)
                <!-- Recorrido activo -->
                <div class="alert alert-info">
                    <strong><i class="bi bi-play-circle"></i> Recorrido en curso</strong><br>
                    Km inicial: {{ $recorridoActivo->kminicial }} | 
                    Hora salida: {{ $recorridoActivo->hora_salida ? substr($recorridoActivo->hora_salida, 0, 5) : 'N/A' }}
                </div>

                <h6 class="mt-3"><i class="bi bi-list-check"></i> Pedidos en este recorrido</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Sucursal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $recorridos = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                                    ->where('id_personal', auth()->id())
                                    ->where('status', 0)
                                    ->get();
                            @endphp
                            @forelse($recorridos as $rec)
                                @php
                                    $pedido = App\Models\Pedidos\OrdenPedido::where('folio_pedido', $rec->folio_pedido)->first();
                                @endphp
                                <tr>
                                    <td>{{ $rec->folio_pedido }}</td>
                                    <td>{{ $rec->nombrecliente }}</td>
                                    <td>{{ $rec->Domicilio }}</td>
                                    <td>
                                        @if($rec->Solicitadoensucursal == 0)
                                            CRM
                                        @else
                                            Sucursal {{ $rec->Solicitadoensucursal }}
                                        @endif
                                    </td>
                                    <td><span class="badge bg-warning">En recorrido</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No hay pedidos en este recorrido</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" class="btn btn-warning" onclick="abrirModalFinalizarRecorrido()">
                        <i class="bi bi-stop-circle"></i> Finalizar recorrido
                    </button>
                </div>

            @else
                <!-- No hay recorrido activo - mostrar pedidos pendientes -->
                <h6 class="mt-3"><i class="bi bi-box-seam"></i> Pedidos pendientes por entregar</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaPedidosPendientes">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%"><input type="checkbox" id="seleccionarTodos"></th>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Sucursal</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $pedidosPendientes = App\Models\Pedidos\OrdenPedido::with('cotizacion')
                                    ->where('id_repartidor', auth()->id())
                                    ->where('status', 2)
                                    ->get();
                            @endphp
                            @forelse($pedidosPendientes as $pedido)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="pedido-checkbox" value="{{ $pedido->folio_pedido }}">
                                    </td>
                                    <td>{{ $pedido->folio_pedido }}</td>
                                    <td>{{ $pedido->cotizacion->nombre_cliente ?? 'N/A' }}</td>
                                    <td>{{ $pedido->cotizacion->cliente->Domicilio ?? 'N/A' }}</td>
                                    <td>
                                        @if($pedido->id_sucursal_asignada == 0)
                                            CRM
                                        @else
                                            Sucursal {{ $pedido->id_sucursal_asignada }}
                                        @endif
                                    </td>
                                    <td>${{ number_format($pedido->importe_total ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No hay pedidos pendientes asignados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" class="btn btn-success" id="btnIniciarRecorrido">
                        <i class="bi bi-play-circle"></i> Iniciar recorrido con seleccionados
                    </button>
                </div>
            @endif
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
                    <div class="mb-3">
                        <label class="form-label">Kilometraje inicial *</label>
                        <input type="number" class="form-control" id="kminicial" required>
                        <small class="text-muted">Kilometraje actual de la moto/vehículo</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora de salida *</label>
                        <input type="time" class="form-control" id="hora_salida" required>
                        <small class="text-muted">Hora en que comienza el recorrido</small>
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
                    <div class="mb-3">
                        <label class="form-label">Kilometraje final *</label>
                        <input type="number" class="form-control" id="kmfinal" required>
                        <small class="text-muted">Kilometraje actual al regresar</small>
                    </div>
                    <div class="alert alert-info">
                        <strong>Hora de regreso:</strong> Se registrará automáticamente al confirmar.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="finalizarRecorrido()">
                    <i class="bi bi-check-lg"></i> Finalizar Recorrido
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Seleccionar todos los checkboxes
document.getElementById('seleccionarTodos')?.addEventListener('change', function() {
    document.querySelectorAll('.pedido-checkbox').forEach(cb => cb.checked = this.checked);
});

// Iniciar recorrido
function iniciarRecorrido() {
    const kmInicial = document.getElementById('kminicial').value;
    const horaSalida = document.getElementById('hora_salida').value;
    
    // Obtener folios seleccionados
    const foliosSeleccionados = [];
    document.querySelectorAll('.pedido-checkbox:checked').forEach(cb => {
        foliosSeleccionados.push(cb.value);
    });
    
    if (foliosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Selecciona al menos un pedido', 'warning');
        return;
    }
    
    if (!kmInicial || !horaSalida) {
        if (window.mostrarToast) window.mostrarToast('Completa todos los campos', 'warning');
        return;
    }
    
    fetch('{{ route("recorridos.iniciar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            kminicial: parseInt(kmInicial),
            hora_salida: horaSalida,
            folio_pedidos: foliosSeleccionados
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
}

function abrirModalFinalizarRecorrido() {
    document.getElementById('kmfinal').value = '';
    const modal = new bootstrap.Modal(document.getElementById('modalFinalizarRecorrido'));
    modal.show();
}

function finalizarRecorrido() {
    const kmFinal = document.getElementById('kmfinal').value;
    
    if (!kmFinal) {
        if (window.mostrarToast) window.mostrarToast('El kilometraje final es obligatorio', 'warning');
        return;
    }
    
    fetch('{{ route("recorridos.finalizar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ kmfinal: parseInt(kmFinal) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
}
</script>
@endsection