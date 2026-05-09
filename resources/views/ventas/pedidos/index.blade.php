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
        $esRepartidor = $esRepartidor ?? false;
        $esUsuarioSucursal = ($sucursalAsignada > 0 && !$esRepartidor);
    @endphp

    <!-- BOTÓN SUPERIOR (siempre visible para roles que pueden asignar/iniciar) -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarPedido" placeholder="Buscar por folio, cliente o repartidor...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            @if($esRepartidor)
                <a href="{{ route('ventas.pedidos.repartidor.recorrido') }}" class="btn btn-outline-primary">
                    <i class="bi bi-truck"></i> Mis recorridos
                </a>
            @elseif($sucursalAsignada > 0 && $permisos['crear'])
                {{-- Sucursal o ex-repartidor con permiso de crear --}}
                <a href="{{ route('ventas.pedidos.asignacion.multipedidos') }}" class="btn btn-info">
                    <i class="bi bi-eye"></i> Ver repartidores y entregas
                </a>
            @elseif($sucursalAsignada == 0 && $permisos['crear'])
                {{-- CRM con permiso de crear --}}
                <a href="{{ route('ventas.pedidos.asignacion.multipedidos') }}" class="btn btn-primary">
                    <i class="bi bi-person-badge"></i> Asignar repartidor a pedidos
                </a>
            @endif
        </div>
    </div>

    <!-- TABLA DE PEDIDOS (solo si tiene permiso de ver) -->
    @if($puedeVer)
        <div class="row mb-4">
            <div class="col-md-12 text-end">
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
                            <th>Seguimiento</th>
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

                            <td class="text-center">
                                @if(in_array($pedido->status, [2, 3]))
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                        onclick="abrirModalSeguimientoPedido({{ $pedido->id_pedido }}, '{{ $pedido->folio_pedido }}', {{ $pedido->status }})"
                                        title="Registrar seguimiento">
                                    <i class="bi bi-chat-dots"></i>
                                </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <!-- Status según tipo de usuario -->
                            <td>
                                {{-- Status según tipo de usuario --}}
                                @if($esRepartidor)
                                    {{-- Repartidor: ver estado del pedido para entrega --}}
                                    @if($pedido->status == 2)
                                        <span class="badge bg-warning">Pendiente de entrega</span>
                                    @elseif($pedido->status == 3)
                                        <span class="badge bg-success">Entregado</span>
                                    @elseif($pedido->status == 1)
                                        <span class="badge bg-danger">Cancelado</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $pedido->status_nombre }}</span>
                                    @endif
                                @elseif($sucursalAsignada == 0)
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
                                            <span class="badge bg-warning">Esperando asignación de sucursal</span>
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
                                    <!-- Ver detalles - SOLO para CRM y Sucursal (NO repartidor) -->
                                    @if(!$esRepartidor)
                                        <button type="button" class="btn btn-sm btn-outline-info btn-action"
                                                onclick="verPedido({{ $pedido->id_pedido }})"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endif
                                    
                                    @php
                                        // Calcular condiciones para los botones
                                        $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
                                        $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
                                        $puedeEditarPedido = ($puedeEditar && $pedido->status == 2 && $sucursalAsignada == 0);
                                    @endphp

                                    <!-- Editar pedido - solo CRM, NO repartidor -->
                                    @if($puedeEditarPedido && !$pedido->id_repartidor && !$esRepartidor)
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-action"
                                                onclick="editarPedido({{ $pedido->id_pedido }})"
                                                title="Editar pedido">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                                                
                                    <!-- Descargar PDF - solo para CRM y Sucursal (NO repartidor) -->
                                    @if(!$esRepartidor)
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-action"
                                                onclick="descargarPDFPedido({{ $pedido->id_pedido }})"
                                                title="Descargar PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </button>
                                    @endif
                                    
                                    <!-- Cancelar pedido - disponible para todos con permiso eliminar (CRM, Sucursal, Repartidor) -->
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

    @if(method_exists($pedidos, 'hasPages') && $pedidos->hasPages())
    <div class="d-flex justify-content-end mt-3">
        {{ $pedidos->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif

    @elseif($esRepartidor)
        {{-- Repartidor sin permiso de ver --}}
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> No tienes permiso para ver el listado general de pedidos, pero puedes ver tus pedidos asignados y gestionar tus recorridos usando el botón superior.
        </div>

    @elseif($sucursalAsignada > 0 && $permisos['crear'])
        {{-- Sucursal o ex-repartidor sin permiso de ver pero con permiso de crear --}}
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> No tienes permiso para ver el listado de pedidos, pero puedes ver los repartidores y pedidos de tu sucursal usando el botón superior.
        </div>

    @elseif($sucursalAsignada == 0 && $permisos['crear'])
        {{-- CRM sin permiso de ver pero con permiso de crear --}}
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> No tienes permiso para ver el listado de pedidos, pero puedes asignar repartidores usando el botón superior.
        </div>

    @else
        {{-- Sin ningún permiso relevante --}}
        <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle"></i> No tienes permiso para acceder a este módulo.
        </div>
    @endif

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
@include('ventas.pedidos.partials.modal-finalizar')
@include('ventas.pedidos.partials.modal-editar-pedido')
@include('ventas.partials.modal-seguimiento')

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

// Función para abrir modal desde pedidos
window.abrirModalSeguimientoPedido = function(id, folio, status) {
    const esVenta = (status == 3);
    const tipo = esVenta ? 'venta' : 'pedido';
    
    if (window.mostrarToast) {
        window.mostrarToast('Cargando datos del ' + tipo + '...', 'warning');
    }
    
    // Usar la nueva ruta unificada
    fetch(`/ventas/seguimiento/pedido/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof cargarDatosModalSeguimiento === 'function') {
                cargarDatosModalSeguimiento(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalSeguimiento'));
                modal.show();
                if (window.mostrarToast) window.mostrarToast('Datos cargados', 'success');
            }
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar datos', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión: ' + error.message, 'danger');
    });
};

// Variable para almacenar el teléfono del cliente (global)
window.telefonoClienteActual = null;

// Función actualizada para cargar datos (unificada)
function cargarDatosModalSeguimiento(data) {
    // Datos ocultos
    const segTipo = document.getElementById('seg_tipo');
    const segFolioReferencia = document.getElementById('seg_folio_referencia');
    const segIdCliente = document.getElementById('seg_id_cliente_maestro');
    
    if (segTipo) segTipo.value = data.tipo;
    if (segFolioReferencia) segFolioReferencia.value = data.folio;
    if (segIdCliente) segIdCliente.value = data.id_cliente_maestro;
    
    // Título del modal según tipo
    const tituloModal = document.getElementById('modalSeguimientoTitulo');
    if (tituloModal) {
        switch(data.tipo) {
            case 'cotizacion':
                tituloModal.textContent = 'Seguimiento a Cotización';
                break;
            case 'pedido':
                tituloModal.textContent = 'Seguimiento a Pedido';
                break;
            case 'venta':
                tituloModal.textContent = 'Seguimiento a Venta';
                break;
            default:
                tituloModal.textContent = 'Seguimiento';
        }
    }
    
    // Información del documento
    const segFolio = document.getElementById('seg_folio');
    const segFechaCreacion = document.getElementById('seg_fecha_creacion');
    const segEstado = document.getElementById('seg_estado');
    
    if (segFolio) segFolio.textContent = data.folio;
    if (segFechaCreacion) segFechaCreacion.textContent = data.fecha_creacion;
    if (segEstado) segEstado.innerHTML = `<span class="badge bg-info">${data.estado_nombre || 'En proceso'}</span>`;
    
    // Calcular días correctamente
    const segDias = document.getElementById('seg_dias');
    if (segDias && data.fecha_creacion) {
        const fechaCreacion = new Date(data.fecha_creacion);
        const hoy = new Date();
        fechaCreacion.setHours(0, 0, 0, 0);
        hoy.setHours(0, 0, 0, 0);
        const diffTime = hoy - fechaCreacion;
        const diffDias = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        segDias.innerHTML = `<span class="badge ${diffDias >= 7 ? 'bg-warning' : 'bg-secondary'}">${diffDias} día(s)</span>`;
    }
    
    // Datos del cliente
    const segClienteNombre = document.getElementById('seg_cliente_nombre');
    const telefonoSpan = document.getElementById('seg_cliente_telefono');
    const btnWhatsApp = document.getElementById('btnEnviarWhatsApp');
    
    if (segClienteNombre) segClienteNombre.textContent = data.cliente_nombre;
    
    if (data.cliente_telefono) {
        let telefonoLimpio = data.cliente_telefono.replace(/[^0-9]/g, '');
        if (telefonoLimpio.startsWith('52')) {
            telefonoLimpio = telefonoLimpio.substring(2);
        }
        if (!telefonoLimpio.startsWith('52')) {
            telefonoLimpio = '52' + telefonoLimpio;
        }
        
        window.telefonoClienteActual = telefonoLimpio;
        if (telefonoSpan) telefonoSpan.textContent = data.cliente_telefono;
        if (btnWhatsApp) btnWhatsApp.style.display = 'block';
    } else {
        if (telefonoSpan) telefonoSpan.textContent = 'No registrado';
        if (btnWhatsApp) btnWhatsApp.style.display = 'none';
    }
    
    // Hora de inicio
    const segHoraInicio = document.getElementById('seg_hora_inicio');
    if (segHoraInicio) {
        const ahora = new Date();
        const fechaFormateada = ahora.toLocaleDateString('es-MX', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
        const horaFormateada = ahora.toLocaleTimeString('es-MX', {
            hour: '2-digit',
            minute: '2-digit'
        });
        segHoraInicio.value = `${fechaFormateada} ${horaFormateada}`;
    }
    
    // Limpiar campos (con validación de existencia)
    const inputsToClear = [
        'seg_hora_fin', 'seg_mensaje_cliente', 'seg_motivo_no_finalizacion',
        'seg_conversacion', 'seg_queja', 'seg_sugerencia'
    ];
    
    inputsToClear.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.value = '';
    });
}

// Función unificada para guardar seguimiento
window.guardarSeguimiento = function() {
    const horaFin = document.getElementById('seg_hora_fin').value;
    
    if (!horaFin) {
        if (window.mostrarToast) {
            window.mostrarToast('La hora de fin es obligatoria', 'warning');
        }
        document.getElementById('seg_hora_fin').focus();
        return;
    }
    
    const formData = {
        tipo: document.getElementById('seg_tipo').value,
        folio_referencia: document.getElementById('seg_folio_referencia').value,
        id_cliente_maestro: document.getElementById('seg_id_cliente_maestro').value,
        hora_fin: horaFin,
        mensaje_cliente: document.getElementById('seg_mensaje_cliente').value || null,
        motivo_no_finalizacion: document.getElementById('seg_motivo_no_finalizacion').value || null,
        conversacion: document.getElementById('seg_conversacion').value || null,
        queja: document.getElementById('seg_queja').value || null,
        sugerencia: document.getElementById('seg_sugerencia').value || null
    };
    
    if (window.mostrarToast) {
        window.mostrarToast('Guardando seguimiento...', 'warning');
    }
    
    fetch('{{ route("ventas.seguimiento.store") }}', {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalSeguimiento'));
            if (modal) modal.hide();
            
            if (window.mostrarToast) {
                window.mostrarToast(data.message, 'success');
            }
            
            setTimeout(() => location.reload(), 1000);
        } else {
            if (data.errors) {
                const errores = Object.values(data.errors).flat().join('\n');
                if (window.mostrarToast) {
                    window.mostrarToast(errores, 'danger');
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast(data.message || 'Error al guardar', 'danger');
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error de conexión al guardar', 'danger');
        }
    });
};
// ============================================
// EVENT LISTENERS
// ============================================

</script>
@endpush