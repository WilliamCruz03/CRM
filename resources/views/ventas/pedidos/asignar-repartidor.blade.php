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

            <!-- Pedidos pendientes (para repartidor) -->
            @if($esRepartidor)
            <h6 class="mt-4"><i class="bi bi-list-check"></i> Mis pedidos pendientes</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tablaPedidosPendientes">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">
                                <input type="checkbox" id="seleccionarTodosPedidos" title="Seleccionar todos">
                            </th>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Importe</th>
                            <th>Sucursal</th>
                        </tr>
                    </thead>
                    <tbody id="pedidosPendientesBody">
                        <tr><td colspan="6" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Entregas en curso -->
            <h6 class="mt-4"><i class="bi bi-clock-history"></i> Entregas en curso</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">
                                @if($esRepartidor)
                                <input type="checkbox" id="seleccionarTodosRecorridos" title="Seleccionar todos para finalizar">
                                @endif
                            </th>
                            <th>Repartidor</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Hora salida</th>
                            <th>Tiempo fuera</th>
                        </tr>
                    </thead>
                    <tbody id="entregasBody">
                        <tr><td colspan="6" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                @if($esRepartidor)
                    <button type="button" class="btn btn-success" id="btnIniciarRecorrido" disabled>
                        <i class="bi bi-play-circle"></i> Iniciar recorrido seleccionado
                    </button>
                    <button type="button" class="btn btn-warning" id="btnFinalizarRecorrido" disabled>
                        <i class="bi bi-stop-circle"></i> Finalizar recorrido(s) seleccionado(s)
                    </button>
                @elseif($sucursalAsignada == 0)
                    @if($pedido->id_repartidor)
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="bi bi-check-circle"></i> Repartidor ya asignado
                        </button>
                    @else
                        <button type="button" class="btn btn-primary" id="btnAsignar" disabled>
                            <i class="bi bi-person-badge"></i> Asignar repartidor seleccionado
                        </button>
                    @endif
                @endif
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Iniciar Recorrido (Múltiples pedidos) -->
<div class="modal fade" id="modalIniciarRecorrido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                    
                    <div class="alert alert-info">
                        <strong>Pedidos seleccionados: <span id="totalPedidosSeleccionados">0</span></strong>
                    </div>
                    
                    <!-- Lista de pedidos seleccionados (resumen) -->
                    <div class="mb-3">
                        <label class="form-label">Pedidos a entregar</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Folio ticket</th>
                                        <th>Cliente</th>
                                        <th>Dirección</th>
                                        <th>Importe</th>
                                    </tr>
                                </thead>
                                <tbody id="listaPedidosSeleccionados">
                                    <tr><td colspan="4" class="text-center">Selecciona pedidos para iniciar el recorrido</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kilometraje inicial *</label>
                                <input type="number" class="form-control" id="recorrido_kminicial" required>
                                <small class="text-muted">Kilometraje actual de la moto/vehículo</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hora de salida *</label>
                                <input type="time" class="form-control" id="recorrido_hora_salida" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="iniciarRecorridoMultiple()">
                    <i class="bi bi-play-circle"></i> Iniciar Recorrido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Finalizar Recorrido (Múltiples recorridos) -->
<div class="modal fade" id="modalFinalizarRecorrido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-stop-circle"></i> Finalizar Recorrido(s)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formFinalizarRecorrido">
                    @csrf
                    
                    <div class="alert alert-info">
                        <strong>Recorridos seleccionados: <span id="totalRecorridosSeleccionados">0</span></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kilometraje final *</label>
                        <input type="number" class="form-control" id="finalizar_kmfinal" required>
                        <small class="text-muted">Kilometraje actual al regresar (aplica para todos los recorridos)</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Hora de regreso:</strong> Se registrará automáticamente al confirmar (misma hora para todos).
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarFinalizarRecorridoMultiple()">
                    <i class="bi bi-check-lg"></i> Finalizar Recorrido(s)
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
let pedidosSeleccionados = [];
let recorridosSeleccionados = [];

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
                    btnAsignar.disabled = !puedeAsignar;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    
    // Si es repartidor, cargar sus pedidos pendientes
    if (esRepartidor) {
        cargarPedidosPendientes();
    }
}

// Cargar pedidos pendientes del repartidor
function cargarPedidosPendientes() {
    fetch('{{ route("ventas.pedidos.pendientes.repartidor") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarTablaPedidosPendientes(data.pedidos);
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
    
    // Verificar si el pedido ya tiene repartidor asignado
    const yaTieneRepartidor = repartidorAsignadoId !== null;
    
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
        
        // Solo permitir seleccionar si NO es repartidor, puede asignar, repartidor disponible, y no hay repartidor asignado
        const puedeSeleccionar = !esRepartidor && puedeAsignar && rep.status === 'Disponible' && !yaTieneRepartidor;
        
        let columnaSeleccion = '';
        if (esRepartidor) {
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
    
    // Agregar event listeners a los radios
    if (!esRepartidor && !yaTieneRepartidor) {
        document.querySelectorAll('input[name="repartidor"]:not([disabled])').forEach(radio => {
            radio.addEventListener('change', function() {
                repartidorSeleccionadoId = this.value;
                const btnAsignar = document.getElementById('btnAsignar');
                if (btnAsignar) btnAsignar.disabled = false;
            });
        });
    }
}

function actualizarTablaPedidosPendientes(pedidos) {
    const tbody = document.getElementById('pedidosPendientesBody');
    if (!pedidos || pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay pedidos pendientes</td></tr>';
        const btnIniciar = document.getElementById('btnIniciarRecorrido');
        if (btnIniciar) btnIniciar.disabled = true;
        return;
    }
    
    let html = '';
    pedidos.forEach(pedido => {
        html += `
            <tr data-pedido-id="${pedido.id_pedido}">
                <td class="text-center">
                    <input type="checkbox" class="checkbox-pedido" 
                           data-id="${pedido.id_pedido}"
                           data-folio-ticket="${pedido.folio_ticket}"
                           data-nombrecliente="${pedido.nombrecliente.replace(/"/g, '&quot;')}"
                           data-domicilio="${pedido.Domicilio.replace(/"/g, '&quot;')}"
                           data-importe="${pedido.importeticket}"
                           data-sucursal="${pedido.sucursal}">
                </td>
                <td>${pedido.folio_pedido}</td>
                <td>${pedido.nombrecliente}</td>
                <td>${pedido.Domicilio}</td>
                <td>$${Number(pedido.importeticket).toFixed(2)}</td>
                <td>${sucursalesMap[pedido.sucursal] || 'CRM'}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    
    // Agregar event listeners a los checkboxes
    document.querySelectorAll('.checkbox-pedido').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            actualizarPedidosSeleccionados();
        });
    });
    
    // Checkbox "seleccionar todos"
    const selectAll = document.getElementById('seleccionarTodosPedidos');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.checkbox-pedido').forEach(cb => {
                cb.checked = selectAll.checked;
            });
            actualizarPedidosSeleccionados();
        });
    }
}

function actualizarPedidosSeleccionados() {
    pedidosSeleccionados = [];
    document.querySelectorAll('.checkbox-pedido:checked').forEach(checkbox => {
        pedidosSeleccionados.push({
            id_pedido: parseInt(checkbox.dataset.id),
            folio_ticket: checkbox.dataset.folioTicket,
            nombrecliente: checkbox.dataset.nombrecliente,
            Domicilio: checkbox.dataset.domicilio,
            importeticket: parseFloat(checkbox.dataset.importe),
            sucursal: parseInt(checkbox.dataset.sucursal)
        });
    });
    
    const btnIniciar = document.getElementById('btnIniciarRecorrido');
    if (btnIniciar) {
        btnIniciar.disabled = pedidosSeleccionados.length === 0;
    }
}

function actualizarTablaEntregas(entregas) {
    const tbody = document.getElementById('entregasBody');
    if (!entregas || entregas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay entregas en curso</td></tr>';
        const btnFinalizar = document.getElementById('btnFinalizarRecorrido');
        if (btnFinalizar) btnFinalizar.disabled = true;
        return;
    }
    
    let html = '';
    entregas.forEach(entrega => {
        // Guardar hora_salida como atributo data para actualizar después
        const horaSalida = entrega.hora_salida || '';
        const checkedAttr = recorridosSeleccionados.includes(entrega.id) ? 'checked' : '';
        
        html += `
            <tr data-recibido-id="${entrega.id}">
                <td class="text-center">
                    ${esRepartidor ? `<input type="checkbox" class="checkbox-recorrido" value="${entrega.id}" ${checkedAttr}>` : '---'}
                </td>
                <td><strong>${entrega.repartidor_nombre} ${entrega.repartidor_apaterno || ''}</strong></td>
                <td>${entrega.nombrecliente || 'N/A'}</td>
                <td>${entrega.Domicilio || 'N/A'}</td>
                <td>${horaSalida ? horaSalida.substring(0,5) : 'N/A'}</td>
                <td><span class="badge bg-info tiempo-fuera" data-inicio="${horaSalida}">00:00:00</span></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    
    // Agregar event listeners a los checkboxes de recorridos
    if (esRepartidor) {
        document.querySelectorAll('.checkbox-recorrido').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                actualizarRecorridosSeleccionados();
            });
        });
        
        // Checkbox "seleccionar todos recorridos"
        const selectAllRecorridos = document.getElementById('seleccionarTodosRecorridos');
        if (selectAllRecorridos) {
            selectAllRecorridos.addEventListener('change', function() {
                document.querySelectorAll('.checkbox-recorrido').forEach(cb => {
                    cb.checked = selectAllRecorridos.checked;
                });
                actualizarRecorridosSeleccionados();
            });
        }
    }
    
    // Iniciar actualización en tiempo real
    actualizarTiemposFuera();
}

function actualizarRecorridosSeleccionados() {
    recorridosSeleccionados = [];
    document.querySelectorAll('.checkbox-recorrido:checked').forEach(checkbox => {
        recorridosSeleccionados.push(parseInt(checkbox.value));
    });
    
    const btnFinalizar = document.getElementById('btnFinalizarRecorrido');
    if (btnFinalizar) {
        btnFinalizar.disabled = recorridosSeleccionados.length === 0;
    }
}

function actualizarTiemposFuera() {
    const elementos = document.querySelectorAll('.tiempo-fuera');
    elementos.forEach(el => {
        const horaInicioStr = el.getAttribute('data-inicio');
        if (horaInicioStr) {
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

function iniciarActualizacionTiempos() {
    if (intervaloTiempos) clearInterval(intervaloTiempos);
    intervaloTiempos = setInterval(actualizarTiemposFuera, 1000);
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

// ============================================
// FUNCIONES PARA CRM - ASIGNAR REPARTIDOR MÚLTIPLE
// ============================================

const btnAsignar = document.getElementById('btnAsignar');
if (btnAsignar) {
    btnAsignar.addEventListener('click', function() {
        if (!repartidorSeleccionadoId) {
            if (window.mostrarToast) window.mostrarToast('Selecciona un repartidor antes de asignar', 'warning');
            return;
        }
        
        // Obtener los pedidos seleccionados (por ahora solo el actual, pero se puede expandir)
        const pedidosIds = [{{ $pedido->id_pedido }}];
        
        fetch('{{ route("ventas.pedidos.asignarRepartidor") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                id_repartidor: parseInt(repartidorSeleccionadoId),
                pedidos_ids: pedidosIds
            })
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
// FUNCIONES PARA REPARTIDOR - INICIAR RECORRIDO MÚLTIPLE
// ============================================

function abrirModalIniciarRecorrido() {
    if (pedidosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Selecciona al menos un pedido para iniciar el recorrido', 'warning');
        return;
    }
    
    // Actualizar contador y lista de pedidos
    document.getElementById('totalPedidosSeleccionados').innerText = pedidosSeleccionados.length;
    
    let html = '';
    pedidosSeleccionados.forEach(pedido => {
        html += `
            <tr>
                <td>${pedido.folio_ticket}</td>
                <td>${pedido.nombrecliente}</td>
                <td>${pedido.Domicilio}</td>
                <td>$${pedido.importeticket.toFixed(2)}</td>
            </tr>
        `;
    });
    document.getElementById('listaPedidosSeleccionados').innerHTML = html;
    
    // Limpiar campos
    document.getElementById('recorrido_kminicial').value = '';
    document.getElementById('recorrido_hora_salida').value = new Date().toLocaleTimeString('es-MX', { hour12: false }).substring(0, 5);
    
    const modal = new bootstrap.Modal(document.getElementById('modalIniciarRecorrido'));
    modal.show();
}

function iniciarRecorridoMultiple() {
    const kmInicial = document.getElementById('recorrido_kminicial').value;
    const horaSalida = document.getElementById('recorrido_hora_salida').value;
    
    if (!kmInicial) {
        if (window.mostrarToast) window.mostrarToast('El kilometraje inicial es obligatorio', 'warning');
        return;
    }
    if (!horaSalida) {
        if (window.mostrarToast) window.mostrarToast('La hora de salida es obligatoria', 'warning');
        return;
    }
    if (pedidosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('No hay pedidos seleccionados', 'warning');
        return;
    }
    
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
            pedidos: pedidosSeleccionados,
            kminicial: parseInt(kmInicial),
            hora_salida: horaSalida
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

// ============================================
// FUNCIONES PARA REPARTIDOR - FINALIZAR RECORRIDO MÚLTIPLE
// ============================================

function abrirModalFinalizarRecorrido() {
    if (recorridosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Selecciona al menos un recorrido para finalizar', 'warning');
        return;
    }
    
    document.getElementById('totalRecorridosSeleccionados').innerText = recorridosSeleccionados.length;
    document.getElementById('finalizar_kmfinal').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalFinalizarRecorrido'));
    modal.show();
}

function confirmarFinalizarRecorridoMultiple() {
    const kmFinal = document.getElementById('finalizar_kmfinal').value;
    
    if (!kmFinal) {
        if (window.mostrarToast) window.mostrarToast('El kilometraje final es obligatorio', 'warning');
        return;
    }
    if (recorridosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('No hay recorridos seleccionados', 'warning');
        return;
    }
    
    const btn = document.querySelector('#modalFinalizarRecorrido .btn-warning');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch('{{ route("recorridos.finalizar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            kmfinal: parseInt(kmFinal),
            recorridos_ids: recorridosSeleccionados
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

// Event listeners para botones de repartidor
document.getElementById('btnIniciarRecorrido')?.addEventListener('click', abrirModalIniciarRecorrido);
document.getElementById('btnFinalizarRecorrido')?.addEventListener('click', abrirModalFinalizarRecorrido);
</script>
@endsection