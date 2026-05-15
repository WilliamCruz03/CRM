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
                                            // Verificar si todos los productos tienen sucursal asignada
                                            $productosSinSucursal = $pedido->detalles->where('se_elimino', 0)->whereNull('id_sucursal_surtido')->count();
                                            $todosProductosAsignados = ($productosSinSucursal === 0);
                                            
                                            $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
                                            $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
                                        @endphp
                                        
                                        @if(!$todosProductosAsignados)
                                            <span class="badge bg-warning">Esperando asignación de sucursal</span>
                                        @elseif($todasSucursalesListas && !$pedido->id_repartidor)
                                            <span class="badge bg-info">Sucursales listas - Esperando repartidor</span>
                                        @elseif($pedido->id_repartidor)
                                            <span class="badge bg-primary">Repartidor asignado</span>
                                        @else
                                            <span class="badge bg-warning">Esperando despacho de sucursales</span>
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
                                    <!-- Marcar como listo - Solo sucursales -->
                                    @if($sucursalAsignada > 0 && $permisos['ver'])
                                        @php
                                            $miSucursal = $pedido->sucursales->firstWhere('id_sucursal', $sucursalAsignada);
                                            $tienePendientes = $miSucursal && $miSucursal->status == 0;
                                            $productosExternos = $pedido->detalles->where('es_externo', 1)->count();
                                        @endphp
                                        @if($tienePendientes)
                                            <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                                    onclick="marcarListoSucursal({{ $pedido->id_pedido }}, {{ $productosExternos }})"
                                                    title="Marcar como listo">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        @endif
                                    @endif
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
@include('ventas.pedidos.partials.modal-finalizar')
@include('ventas.pedidos.partials.modal-editar-pedido')
@include('ventas.partials.modal-seguimiento')
@include('ventas.pedidos.partials.modal-convertir-ean')

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

function marcarListoSucursal(pedidoId, tieneExternos) {
    if (tieneExternos > 0) {
        // Hay productos externos - abrir modal de conversión de EAN
        abrirModalConvertirEAN(pedidoId);
    } else {
        // No hay productos externos - usar modal de confirmación global
        const pedidoRow = document.querySelector(`#pedido-row-${pedidoId}`);
        const folio = pedidoRow?.querySelector('td:first-child .badge')?.textContent || 'este pedido';
        
        window.confirmarEliminar('marcar_listo', pedidoId, folio, function() {
            ejecutarMarcarListoSinExternos(pedidoId);
        });
    }
}

function abrirModalConvertirEAN(pedidoId) {
    // Obtener los detalles del pedido para saber qué productos externos tiene
    fetch(`/ventas/pedidos/${pedidoId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Filtrar productos externos (EAN que empieza con T)
            const productosExternos = data.data.detalles.filter(detalle => 
                detalle.ean && detalle.ean.toString().startsWith('T')
            );
            
            if (productosExternos.length === 0) {
                // No hay externos, proceder con marcado normal
                ejecutarMarcarListoSinExternos(pedidoId);
                return;
            }
            
            // Mostrar modal de conversión
            const modalHtml = `
                <div class="modal fade" id="modalConvertirEAN" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title">
                                    <i class="bi bi-upc-scan"></i> Convertir productos sobre pedido
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Los siguientes productos requieren su código de barras real:</p>
                                <div id="productosEANLista"></div>
                                <hr>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Ingresa el código de barras real para cada producto.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-warning" onclick="confirmarConvertirEAN(${pedidoId})">
                                    <i class="bi bi-check-lg"></i> Confirmar conversión
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Eliminar modal existente si hay
            const modalExistente = document.getElementById('modalConvertirEAN');
            if (modalExistente) modalExistente.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Llenar lista de productos
            const listaContainer = document.getElementById('productosEANLista');
            let listaHtml = '<div class="list-group">';
            productosExternos.forEach(producto => {
                listaHtml += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${escapeHtml(producto.nombre || 'Producto sobre pedido')}</strong>
                                <br><small class="text-muted">EAN actual: ${producto.ean}</small>
                                <br><small class="text-muted">Cantidad: ${producto.cantidad}</small>
                            </div>
                            <div style="width: 200px;">
                                <input type="text" class="form-control form-control-sm" 
                                       id="nuevo_ean_${producto.id_detalle_pedido}" 
                                       data-id-detalle="${producto.id_detalle_pedido}"
                                       placeholder="Nuevo código de barras"
                                       required>
                            </div>
                        </div>
                    </div>
                `;
            });
            listaHtml += '</div>';
            listaContainer.innerHTML = listaHtml;
            
            const modal = new bootstrap.Modal(document.getElementById('modalConvertirEAN'));
            modal.show();
        } else {
            window.mostrarToast('Error al cargar productos del pedido', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.mostrarToast('Error de conexión', 'danger');
    });
}

function confirmarConvertirEAN(pedidoId) {
    const productos = [];
    const inputs = document.querySelectorAll('#productosEANLista input[type="text"]');
    let todosCompletos = true;
    
    inputs.forEach(input => {
        const nuevoEan = input.value.trim();
        const idDetalle = input.getAttribute('data-id-detalle');
        
        if (!nuevoEan) {
            todosCompletos = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
            productos.push({
                id_detalle: parseInt(idDetalle),
                nuevo_ean: nuevoEan
            });
        }
    });
    
    if (!todosCompletos) {
        window.mostrarToast('Completa todos los códigos de barras', 'warning');
        return;
    }
    
    // Mostrar loading en el botón
    const btn = document.querySelector('#modalConvertirEAN .btn-warning');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch('/ventas/pedidos/marcar-listo-ean', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pedido_id: pedidoId,
            productos: productos
        })
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConvertirEAN'));
        if (modal) modal.hide();
        
        if (data.success) {
            window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            window.mostrarToast(data.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
}

function ejecutarMarcarListoSinExternos(pedidoId) {
    // Obtener el ID de la sucursal del pedido (necesitas pasarlo)
    // Alternativa: obtener desde el backend
    fetch(`/ventas/pedidos/${pedidoId}/sucursal-id`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return fetch(`/ventas/pedidos/sucursal/${data.sucursal_id}/marcar-listo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        }
        throw new Error('No se pudo obtener la sucursal');
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
// FUNCIÓN PARA ABRIR MODAL DE SEGUIMIENTO (DESDE PEDIDOS)
// ============================================

window.abrirModalSeguimientoPedido = function(id, folio, status) {
    const esVenta = (status == 3);
    const tipo = esVenta ? 'venta' : 'pedido';
    
    if (window.mostrarToast) {
        window.mostrarToast('Cargando datos ' + tipo + '...', 'warning');
    }
    
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
            if (typeof window.cargarDatosModalSeguimiento === 'function') {
                window.cargarDatosModalSeguimiento(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalSeguimiento'));
                modal.show();
                if (window.mostrarToast) window.mostrarToast('Datos cargados', 'success');
            } else {
                console.error('Error: window.cargarDatosModalSeguimiento no está definida');
                if (window.mostrarToast) window.mostrarToast('Error al cargar los datos', 'danger');
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

// ============================================
// EVENT LISTENERS
// ============================================

</script>
@endpush