@extends('layouts.app')

@section('title', 'Pedidos - CRM')
@section('page-title', 'Gestión de Pedidos')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-truck"></i> Gestión de Pedidos</h3>
        <p class="text-muted">Monitorea el estado y seguimiento de los pedidos</p>
    </div>

    @php
        $puedeVer = $permisos['ver'] ?? false;
        $puedeEditar = $permisos['editar'] ?? false;
        $puedeEliminar = $permisos['eliminar'] ?? false;
    @endphp

    @if($puedeVer)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarPedido" placeholder="Buscar por folio, cliente o repartidor...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-flex justify-content-end align-items-center gap-2">
                <span class="text-muted"><i class="bi bi-funnel"></i> Filtrar por:</span>
                <select id="filtroSelect" class="form-select w-auto" style="width: auto;">
                    <option value="todos">Todos</option>
                    <option value="proceso">En proceso</option>
                    <option value="finalizados">Finalizados</option>
                    <option value="cancelados">Cancelados</option>
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
                            <th>Folio Pedido</th>
                            <th>Cotización Origen</th>
                            <th>Cliente</th>
                            <th>Fecha y Hora</th>
                            @if($sucursalAsignada == 0)
                                <th>Sucursales</th>
                            @endif
                            <th>Repartidor</th>
                            <th>Status</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidosTableBody">
                        @forelse($pedidos as $pedido)
                        <tr id="pedido-row-{{ $pedido->id_pedido }}" data-status="{{ $pedido->status }}">
                            <td>
                                <span class="badge bg-primary">{{ $pedido->folio_pedido }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $pedido->cotizacion->folio ?? '-' }}</span>
                            </td>
                            <td>
                                <strong>{{ $pedido->cotizacion->nombre_cliente ?? '-' }}</strong>
                                @if($pedido->cotizacion->cliente)
                                    <br><small class="text-muted">
                                        <i class="bi bi-telephone"></i> {{ $pedido->cotizacion->cliente->telefono1 ?? '' }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                {{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : '-' }}
                            </td>
                            
                            @if($sucursalAsignada == 0)
                            <td>
                                @php
                                    $sucursalesPedido = $pedido->sucursales->pluck('sucursal.nombre')->implode(', ');
                                @endphp
                                <span class="badge bg-info">{{ $pedido->sucursales->count() }}</span>
                                <br><small class="text-muted">{{ Str::limit($sucursalesPedido, 50) }}</small>
                            </td>
                            @endif
                            
                            <td>
                                @if($pedido->repartidor)
                                    {{ $pedido->repartidor->Nombre }} {{ $pedido->repartidor->apPaterno }} {{ $pedido->repartidor->apMaterno }}
                                @else
                                    <span class="text-muted">Sin asignar</span>
                                @endif
                            </td>
                            
                            <!-- Status según tipo de usuario -->
                            <td>
                                @if($sucursalAsignada == 0)
                                    {{-- Usuario CRM: ver status general del pedido --}}
                                    @if($pedido->status == 2)
                                        @php
                                            $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
                                            $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
                                        @endphp
                                        @if($todasSucursalesListas && !$pedido->id_repartidor)
                                            <span class="badge bg-info">Sucursales listas - Esperando repartidor</span>
                                        @elseif($pedido->id_repartidor)
                                            <span class="badge bg-primary">Repartidor asignado</span>
                                        @else
                                            <span class="badge bg-warning">En proceso</span>
                                        @endif
                                    @elseif($pedido->status == 3)
                                        <span class="badge bg-success">Finalizado</span>
                                    @elseif($pedido->status == 1)
                                        <span class="badge bg-danger">Cancelado</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $pedido->status_nombre }}</span>
                                    @endif
                                @else
                                    {{-- Usuario de sucursal: ver status de su sucursal --}}
                                    @php
                                        $miSucursal = $pedido->sucursales->firstWhere('id_sucursal', $sucursalAsignada);
                                    @endphp
                                    @if($miSucursal)
                                        @if($miSucursal->status == 1)
                                            <span class="badge bg-success">Despachado</span>
                                        @else
                                            <span class="badge bg-warning">Pendiente</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Sin asignar</span>
                                    @endif
                                @endif
                            </td>
                            
                            <td>
                                <div class="btn-group" role="group">
                                    <!-- Ver detalles - SIEMPRE visible -->
                                    <button type="button" class="btn btn-sm btn-outline-info btn-action"
                                            onclick="verPedido({{ $pedido->id_pedido }})"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    @php
                                        // Calcular condiciones para los botones
                                        $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
                                        $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
                                        $puedeAsignarRepartidor = ($pedido->status == 2 && $todasSucursalesListas && !$pedido->id_repartidor);
                                        $puedeFinalizar = ($pedido->status == 2 && $pedido->id_repartidor);
                                        $puedeEditarPedido = ($puedeEditar && $pedido->status == 2 && $sucursalAsignada == 0);
                                    @endphp
                                    
                                    <!-- Asignar repartidor - solo cuando todas las sucursales están listas, sin repartidor, y pedido en proceso -->
                                    @if($puedeEditar && $puedeAsignarRepartidor && !$pedido->id_repartidor)
                                        <a href="{{ route('ventas.pedidos.repartidores.vista', $pedido->id_pedido) }}" 
                                        class="btn btn-sm btn-outline-primary btn-action"
                                        title="Asignar repartidor">
                                            <i class="bi bi-person-badge"></i>
                                        </a>
                                    @endif
                                    
                                    <!-- Finalizar pedido - solo cuando tiene repartidor asignado -->
                                    @if($puedeEditar && $puedeFinalizar)
                                        <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                                onclick="mostrarModalFinalizar({{ $pedido->id_pedido }}, '{{ $pedido->folio_pedido }}')"
                                                title="Marcar como entregado">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endif

                                    <!-- Editar pedido - solo CRM y pedido en proceso -->
                                    @if($puedeEditarPedido)
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-action"
                                                onclick="editarPedido({{ $pedido->id_pedido }})"
                                                title="Editar pedido">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                    
                                    <!-- Descargar PDF - SIEMPRE visible (si tiene permiso) -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-action"
                                            onclick="descargarPDFPedido({{ $pedido->id_pedido }})"
                                            title="Descargar PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </button>
                                    
                                    <!-- Cancelar pedido - solo si no está finalizado -->
                                    @if($puedeEliminar && $pedido->status != 3)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                onclick="confirmarCancelarPedido({{ $pedido->id_pedido }}, '{{ $pedido->folio_pedido }}')"
                                                title="Cancelar pedido">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-truck" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay pedidos registrados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $pedidos->links() }}
    </div>
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No tienes permiso para ver este módulo.
    </div>
    @endif
</div>

<!-- Modal Confirmación Genérico -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmacionTitulo">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalConfirmacionMensaje">
                ¿Estás seguro de realizar esta acción?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="modalConfirmacionBtnSi">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('ventas.pedidos.partials.modal-ver-pedido')
@include('ventas.pedidos.partials.modal-asignar-sucursales')
@include('ventas.pedidos.partials.modal-asignar-repartidor')
@include('ventas.pedidos.partials.modal-finalizar')
@include('ventas.pedidos.partials.modal-editar-pedido')

<style>
    .btn-group .btn-action {
        margin: 0 2px;
    }
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
</style>
@endsection

@push('scripts')
<script>
let statusFiltroActual = 'todos';

function filtrarPorStatus(status) {
    console.log('Filtrando por:', status);
    
    statusFiltroActual = status;
    const rows = document.querySelectorAll('#pedidosTableBody tr');
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        
        const rowStatus = parseInt(row.dataset.status);
        let mostrar = false;
        
        switch(status) {
            case 'todos':
                mostrar = true;
                break;
            case 'proceso':
                mostrar = rowStatus === 2;
                break;
            case 'finalizados':
                mostrar = rowStatus === 3;
                break;
            case 'cancelados':
                mostrar = rowStatus === 1;
                break;
        }
        
        row.style.display = mostrar ? '' : 'none';
    });
}

// ============================================
// MODAL DE CONFIRMACIÓN PARA ELIMINAR ARTICULOS DEL PEDIDO
// ============================================
function confirmarAccion(mensaje, titulo, onConfirmar) {
    const modalElement = document.getElementById('modalConfirmacion');
    const modal = new bootstrap.Modal(modalElement);
    
    document.getElementById('modalConfirmacionMensaje').innerHTML = mensaje;
    document.getElementById('modalConfirmacionTitulo').innerHTML = titulo || 'Confirmar acción';
    
    const btnSi = document.getElementById('modalConfirmacionBtnSi');
    
    // Remover event listener anterior si existe
    const oldListener = btnSi._confirmListener;
    if (oldListener) {
        btnSi.removeEventListener('click', oldListener);
    }
    
    // Crear nuevo listener
    const newListener = function() {
        onConfirmar();
        modal.hide();
    };
    
    btnSi.addEventListener('click', newListener);
    btnSi._confirmListener = newListener;
    
    modal.show();
}

window.editarPedido = function(id) {
    fetch(`/ventas/pedidos/${id}/edit`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof cargarDatosEditarPedido === 'function') {
                cargarDatosEditarPedido(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalEditarPedido'));
                modal.show();
            }
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// Event listener para el select
document.getElementById('filtroSelect')?.addEventListener('change', function() {
    filtrarPorStatus(this.value);
});

document.getElementById('buscarPedido')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#pedidosTableBody tr');
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

window.verPedido = function(id) {
    fetch(`/ventas/pedidos/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof cargarDatosVerPedido === 'function') {
                cargarDatosVerPedido(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalVerPedido'));
                modal.show();
            }
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};


window.mostrarModalFinalizar = function(id, folio) {
    document.getElementById('finalizar_pedido_id').value = id;
    document.getElementById('finalizar_pedido_folio').textContent = folio;
    const modal = new bootstrap.Modal(document.getElementById('modalFinalizarPedido'));
    modal.show();
};

window.confirmarFinalizarPedido = function() {
    const id = document.getElementById('finalizar_pedido_id').value;
    const folio = document.getElementById('finalizar_pedido_folio').textContent;
    
    fetch(`/ventas/pedidos/${id}/entregar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalFinalizarPedido'));
            modal.hide();
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
};

window.confirmarCancelarPedido = function(id, folio) {
    if (typeof window.confirmarEliminar === 'function') {
        window.confirmarEliminar('cancelar_pedido', id, folio);
    } else {
        // Fallback
        if (confirm(`¿Cancelar pedido ${folio}?`)) {
            fetch(`/ventas/pedidos/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
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
    }
};

window.descargarPDFPedido = function(id) {
    window.open(`/ventas/pedidos/${id}/pdf`, '_blank');
};

// ============================================
// EVENT LISTENERS
// ============================================

</script>
@endpush