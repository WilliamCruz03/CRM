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
                <input type="text" class="form-control" id="buscarPedido" placeholder="Buscar por folio o cliente...">
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
            <div id="tabla-pedidos-container">
                @include('ventas.pedidos.partials.tabla-pedidos', [
                    'pedidos' => $pedidos,
                    'sucursalAsignada' => $sucursalAsignada,
                    'esRepartidor' => $esRepartidor,
                    'permisos' => $permisos
                ])
            </div>
        </div>
    </div>

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
@include('ventas.pedidos.partials.modal-cancelar-pedido')

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
let timeoutBusqueda = null;

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
    refrescarTablaPedidos(false, false);
});

document.getElementById('buscarPedido')?.addEventListener('keyup', function() {
    const searchTerm = this.value.trim();
    
    clearTimeout(timeoutBusqueda);
    
    if (searchTerm.length === 0) {
        refrescarTablaPedidos(false, false);
        return;
    }
    
    if (searchTerm.length >= 3) {
        timeoutBusqueda = setTimeout(() => {
            refrescarTablaPedidos(false, false);
        }, 500);
    }
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

function marcarListoSucursal(pedidoId, tieneExternos, sucursalPedidoId, sucursalId) {    
    if (tieneExternos > 0) {
        abrirModalConvertirEAN(pedidoId, sucursalId);  // Pasar sucursalId
    } else {
        const pedidoRow = document.querySelector(`#pedido-row-${pedidoId}`);
        const folio = pedidoRow?.querySelector('td:first-child .badge')?.textContent || 'este pedido';
        
        window.confirmarEliminar('marcar_listo', pedidoId, folio, function() {
            ejecutarMarcarListoSinExternos(pedidoId, sucursalPedidoId);
        });
    }
}

function abrirModalConvertirEAN(pedidoId, sucursalId) {
    document.getElementById('convertir_pedido_id').value = pedidoId;
    document.getElementById('convertir_sucursal_id').value = sucursalId || '';
    document.getElementById('folio_ticket').value = '';
    document.getElementById('folio_ticket').classList.remove('is-invalid');
    document.getElementById('numero_caja').value = '';
    document.getElementById('numero_caja').classList.remove('is-invalid');
    document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center">Cargando...</td></tr>';
    
    let url = `/ventas/pedidos/${pedidoId}/productos-externos`;
    if (sucursalId) {
        url += `?sucursal_id=${sucursalId}`;  // Enviar el ID real de la sucursal
    }
    
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            window.productosExternosData = data.data;
            let html = '';
            data.data.forEach((item, idx) => {
                html += `<tr>
                    <td><strong>${escapeHtml(item.descripcion || 'Producto sin nombre')}</strong></td>
                    <td class="text-center"><span class="badge bg-secondary">${escapeHtml(item.ean_original)}</span></td>
                    <td>
                        <input type="text" class="form-control form-control-sm nuevo-ean" 
                               data-idx="${idx}" 
                               placeholder="Nuevo EAN (ej. 7501234567890)"
                               required>
                    </td>
                </tr>`;
            });
            document.getElementById('tablaProductosExternos').innerHTML = html;
            document.getElementById('btnGuardarConvertirEAN').disabled = false;
        } else {
            document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay productos externos pendientes en esta sucursal</td></tr>';
            document.getElementById('btnGuardarConvertirEAN').disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error al cargar productos</td></tr>';
        document.getElementById('btnGuardarConvertirEAN').disabled = true;
    });
    
    const modalElement = document.getElementById('modalConvertirEAN');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function ejecutarMarcarListoSinExternos(pedidoId, sucursalPedidoId) {
    if (!sucursalPedidoId) {
        if (window.mostrarToast) window.mostrarToast('Error: No se encontró la sucursal', 'danger');
        return;
    }
    
    fetch(`/ventas/pedidos/sucursal/${sucursalPedidoId}/marcar-listo`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
        if (window.mostrarToast) window.mostrarToast('Error de conexión: ' + error.message, 'danger');
    });
}

// Función para marcar como listo (se ejecuta desde el modal de confirmación)
window.ejecutarMarcarListo = function(pedidoId, folio) {
    // Obtener el ID de la sucursal del pedido
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
        } else if (data.requiere_conversion) {
            // Abrir modal de conversión de EAN
            abrirModalConvertirEAN(pedidoId);
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
    // Mostrar el modal de cancelación con motivo
    document.getElementById('cancelar_pedido_id').value = id;
    document.getElementById('cancelar_pedido_folio').textContent = folio;
    document.getElementById('cancelar_pedido_motivo').value = '';
    document.getElementById('cancelar_pedido_motivo').classList.remove('is-invalid');
    
    const modal = new bootstrap.Modal(document.getElementById('modalCancelarPedido'));
    modal.show();
};

// Evento del botón confirmar en el modal de cancelación
document.addEventListener('DOMContentLoaded', function() {
    const btnConfirmar = document.getElementById('btnConfirmarCancelar');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            const id = document.getElementById('cancelar_pedido_id').value;
            const folio = document.getElementById('cancelar_pedido_folio').textContent;
            const motivo = document.getElementById('cancelar_pedido_motivo').value.trim();
            
            if (!motivo) {
                document.getElementById('cancelar_pedido_motivo').classList.add('is-invalid');
                if (window.mostrarToast) window.mostrarToast('Debe ingresar un motivo de cancelación', 'warning');
                return;
            }
            
            // Deshabilitar el botón mientras se procesa
            const btn = document.getElementById('btnConfirmarCancelar');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
            
            fetch(`/ventas/pedidos/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ motivo: motivo })
            })
            .then(response => response.json())
            .then(data => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCancelarPedido'));
                if (modal) modal.hide();
                
                if (data.success) {
                    if (window.mostrarToast) window.mostrarToast(`Pedido "${folio}" cancelado correctamente`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cancelar el pedido', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Sí, cancelar pedido';
                }
            })
            .catch(error => {
                console.error('Error al cancelar:', error);
                if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Sí, cancelar pedido';
            });
        });
    }
});

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

// ============================================
// POLLING LIGERO PARA ACTUALIZAR TABLA DE PEDIDOS
// ============================================
let pollingPedidosInterval = null;
let ultimoIdPedido = {{ $pedidos->isNotEmpty() ? $pedidos->first()->id_pedido : 0 }};
let filtroStatusActual = 'todos';
let busquedaActual = '';

function refrescarTablaPedidos(mostrarNotificacion = false, desdePolling = false) {
    // Obtener valores actuales
    const filtroSelect = document.getElementById('filtroSelect');
    const buscarInput = document.getElementById('buscarPedido');
    
    filtroStatusActual = filtroSelect ? filtroSelect.value : 'todos';
    busquedaActual = buscarInput ? buscarInput.value.trim() : '';
    
    // Construir URL con parámetros
    let url = '{{ route("ventas.pedidos.refrescar-tabla") }}?ultimo_id=' + ultimoIdPedido;
    url += '&status_filter=' + encodeURIComponent(filtroStatusActual);
    url += '&search_term=' + encodeURIComponent(busquedaActual);
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.html) {
            const container = document.getElementById('tabla-pedidos-container');
            if (container) {
                container.innerHTML = data.html;
                ultimoIdPedido = data.ultimo_id;
                
                // Agregar event listeners a los links de paginación
                document.querySelectorAll('#tabla-pedidos-container .pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const pageUrl = this.getAttribute('href');
                        if (pageUrl) {
                            cargarPaginaPedidos(pageUrl);
                        }
                    });
                });
                
                if (!desdePolling && mostrarNotificacion && window.mostrarToast) {
                    window.mostrarToast('Pedidos actualizados', 'success');
                }
            }
        }
    })
    .catch(error => console.error('Error refrescando tabla pedidos:', error));
}

function cargarPaginaPedidos(url) {
    // Extraer el número de página de la URL
    const urlParams = new URLSearchParams(url.split('?')[1]);
    const page = urlParams.get('page') || 1;
    
    // Obtener filtros actuales
    const filtroSelect = document.getElementById('filtroSelect');
    const buscarInput = document.getElementById('buscarPedido');
    
    const statusFilter = filtroSelect ? filtroSelect.value : 'todos';
    const searchTerm = buscarInput ? buscarInput.value.trim() : '';
    
    // Construir URL con los mismos parámetros + página
    let fetchUrl = '{{ route("ventas.pedidos.refrescar-tabla") }}';
    fetchUrl += '?page=' + page;
    fetchUrl += '&status_filter=' + encodeURIComponent(statusFilter);
    fetchUrl += '&search_term=' + encodeURIComponent(searchTerm);
    fetchUrl += '&ultimo_id=' + ultimoIdPedido;
    
    fetch(fetchUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.html) {
            const container = document.getElementById('tabla-pedidos-container');
            if (container) {
                container.innerHTML = data.html;
                ultimoIdPedido = data.ultimo_id;
                
                // Reasignar event listeners a los nuevos links
                document.querySelectorAll('#tabla-pedidos-container .pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const pageUrl = this.getAttribute('href');
                        if (pageUrl) {
                            cargarPaginaPedidos(pageUrl);
                        }
                    });
                });
            }
        }
    })
    .catch(error => console.error('Error cargando página:', error));
}

function iniciarPollingPedidos() {
    if (pollingPedidosInterval) clearInterval(pollingPedidosInterval);
    
    pollingPedidosInterval = setInterval(() => {
        if (!document.hidden) {
            refrescarTablaPedidos(false, true);
        }
    }, 30000);
}

// Evento para cuando la pestaña se vuelve visible
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        refrescarTablaPedidos(false, true);
    }
});

// Botón de refrescar manual (opcional)
function agregarBotonRefrescarPedidos() {
    const headerRow = document.querySelector('.row.mb-4 .col-md-6.text-end');
    if (headerRow && !document.getElementById('btnRefrescarPedidos')) {
        const btnHtml = `
            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btnRefrescarPedidos">
                <i class="bi bi-arrow-repeat"></i> Refrescar
            </button>
        `;
        headerRow.insertAdjacentHTML('beforeend', btnHtml);
        
        document.getElementById('btnRefrescarPedidos')?.addEventListener('click', () => {
            refrescarTablaPedidos(true, false);
        });
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    agregarBotonRefrescarPedidos();
    iniciarPollingPedidos();
    
    // Escuchar cambios en el filtro y buscador para actualizar el polling
    const filtroSelect = document.getElementById('filtroSelect');
    const buscarInput = document.getElementById('buscarPedido');
    
    if (filtroSelect) {
        filtroSelect.addEventListener('change', function() {
            refrescarTablaPedidos(false, false);
        });
    }
    
    if (buscarInput) {
        buscarInput.addEventListener('keyup', function() {
            refrescarTablaPedidos(false, false);
        });
    }
});

// Limpiar intervalo al salir
window.addEventListener('beforeunload', function() {
    if (pollingPedidosInterval) clearInterval(pollingPedidosInterval);
});
</script>
@endpush