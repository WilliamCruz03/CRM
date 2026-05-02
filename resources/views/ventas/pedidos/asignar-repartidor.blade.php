@extends('layouts.app')

@section('title', 'Asignar Repartidor')
@section('page-title', 'Asignar Repartidor - ' . ($esRepartidor ? 'Mis Pedidos' : 'Gestión de Repartidores'))

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-badge"></i> 
                @if($esRepartidor)
                    Mis recorridos
                @elseif($sucursalAsignada > 0)
                    Ver repartidores - {{ $sucursalAsignada }}
                @else
                    Asignar Repartidor
                @endif
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-light btn-sm float-end">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </h5>
        </div>
        <div class="card-body">

            <!-- ========================================== -->
            <!-- SECCIÓN: REPARTIDORES DISPONIBLES (TODOS LOS ROLES) -->
            <!-- ========================================== -->
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
                        <tr><td colspan="5" class="text-center">Cargando......</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- ========================================== -->
            <!-- SECCIÓN: PEDIDOS PENDIENTES (SOLO CRM Y REPARTIDOR) -->
            <!-- ========================================== -->
            @if(!$esRepartidor && $sucursalAsignada == 0)
                {{-- CRM: Pedidos pendientes por asignar --}}
                <h6 class="mt-4"><i class="bi bi-list-check"></i> Pedidos pendientes por asignar</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaPedidosCRM">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">
                                    <input type="checkbox" id="seleccionarTodosPedidosCRM" title="Seleccionar todos">
                                </th>
                                <th>Folio Pedido</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Importe</th>
                                <th>Sucursal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="pedidosCRMBody">
                            <tr><td colspan="7" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            @endif

            @if($esRepartidor)
                {{-- Repartidor: Sus pedidos pendientes --}}
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

            <!-- ========================================== -->
            <!-- SECCIÓN: ENTREGAS EN CURSO (TODOS LOS ROLES) -->
            <!-- ========================================== -->
            <h6 class="mt-4"><i class="bi bi-clock-history"></i> Entregas en curso</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            @if($esRepartidor)
                                <th style="width: 5%">
                                    <input type="checkbox" id="seleccionarTodosRecorridos" title="Seleccionar todos para finalizar">
                                </th>
                            @endif
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

            <!-- ========================================== -->
            <!-- BOTONES DE ACCIÓN -->
            <!-- ========================================== -->
            <div class="mt-4 text-end">
                @if($esRepartidor)
                    <button type="button" class="btn btn-success" id="btnIniciarRecorrido" disabled>
                        <i class="bi bi-play-circle"></i> Iniciar recorrido seleccionado
                    </button>
                    <button type="button" class="btn btn-warning" id="btnFinalizarRecorrido" disabled>
                        <i class="bi bi-stop-circle"></i> Finalizar recorrido(s) seleccionado(s)
                    </button>
                @elseif($sucursalAsignada == 0)
                    <button type="button" class="btn btn-primary" id="btnAsignar" disabled>
                        <i class="bi bi-person-badge"></i> Asignar repartidor a pedidos seleccionados
                    </button>
                @elseif($sucursalAsignada > 0)
                    <button type="button" class="btn btn-info" disabled>
                        <i class="bi bi-eye"></i> Solo lectura
                    </button>
                @endif
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL INICIAR RECORRIDO (REPARTIDOR) -->
<!-- ========================================== -->
<div class="modal fade" id="modalIniciarRecorrido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-play-circle"></i> Iniciar Recorrido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formIniciarRecorrido">
                    @csrf
                    
                    <div class="alert alert-info py-2 mb-3">
                        <strong>Pedidos seleccionados: <span id="totalPedidosSeleccionados">0</span></strong>
                    </div>
                    
                    <!-- Tabla de pedidos para editar individualmente -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detalle de pedidos a entregar</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="tablaPedidosRecorrido">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 4%">#</th>
                                        <th style="width: 12%">Folio ticket</th>
                                        <th style="width: 28%">Cliente</th>
                                        <th style="width: 32%">Dirección</th>
                                        <th style="width: 12%">Importe</th>
                                        <th style="width: 12%">Sucursal</th>
                                    </tr>
                                </thead>
                                <tbody id="listaPedidosRecorrido">
                                    <tr><td colspan="6" class="text-center py-3">Selecciona pedidos para iniciar el recorrido</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Kilometraje inicial</label>
                            <input type="number" class="form-control form-control-sm" id="recorrido_kminicial" placeholder="Km inicial" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Hora de salida</label>
                            <input type="time" class="form-control form-control-sm" id="recorrido_hora_salida" readonly disabled>
                        <div class="col-md-4">
                            <small class="text-muted">* Hora del sistema</small>
                        </div>
                    </div>
                        
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-success" onclick="iniciarRecorridoMultiple()">
                    <i class="bi bi-play-circle"></i> Iniciar Recorrido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL FINALIZAR RECORRIDO (REPARTIDOR) -->
<!-- ========================================== -->
<div class="modal fade" id="modalFinalizarRecorrido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-stop-circle"></i> Finalizar Recorrido(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formFinalizarRecorrido">
                    @csrf
                    <div class="alert alert-info">
                        <strong>Recorridos seleccionados: <span id="totalRecorridosSeleccionados">0</span></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kilometraje final <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="finalizar_kmfinal" required>
                        <small>Kilometraje actual al regresar (aplica para todos los recorridos)</small>
                    </div>
                    <div class="alert alert-info">
                        <strong>Hora de regreso:</strong> Se registrará automáticamente al confirmar.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarFinalizarRecorridoMultiple()">Finalizar Recorrido(s)</button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let repartidorSeleccionadoId = null;
let intervaloActualizacion = null;
let puedeAsignar = false;
let esRepartidor = {{ $esRepartidor ? 'true' : 'false' }};
let sucursalAsignada = {{ $sucursalAsignada }};
let pedidosSeleccionados = [];
let recorridosSeleccionados = [];
let pedidosCRMSeleccionados = [];

const repartidorAsignadoId = {{ $pedido->id_repartidor ?? 'null' }};
const yaTieneRepartidor = repartidorAsignadoId !== null;

// Mapeo de sucursales
const sucursalesMap = {};
@foreach($sucursales as $sucursal)
    sucursalesMap[{{ $sucursal->id_sucursal }}] = '{{ $sucursal->nombre }}';
@endforeach
// Agregar CRM (sucursal 0)
sucursalesMap[0] = 'CRM';

// ============================================
// CARGA DE DATOS
// ============================================
function cargarDatos() {
    // Solo cargar repartidores y entregas (siempre)
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
    
    // Cargar pedidos pendientes según el rol
    if (esRepartidor) {
        // Repartidor: cargar sus pedidos pendientes
        fetch('{{ route("ventas.pedidos.pendientes.repartidor") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarTablaPedidosPendientes(data.pedidos);
                }
            })
            .catch(error => console.error('Error:', error));
    } else if (sucursalAsignada === 0) {
        // CRM: cargar pedidos pendientes por asignar
        fetch('{{ route("ventas.pedidos.pendientes.crm") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarTablaPedidosCRM(data.pedidos);
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

// Cargar pedidos pendientes del repartidor
function cargarPedidosPendientesRepartidor() {
    fetch('{{ route("ventas.pedidos.pendientes.repartidor") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarTablaPedidosPendientes(data.pedidos);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Cargar pedidos pendientes para CRM (todos los pedidos en proceso sin repartidor)
function cargarPedidosPendientesCRM() {
    fetch('{{ route("ventas.pedidos.pendientes.crm") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarTablaPedidosCRM(data.pedidos);
            }
        })
        .catch(error => console.error('Error:', error));
}

// ============================================
// TABLA REPARTIDORES
// ============================================
function actualizarTablaRepartidores(repartidores) {
    const tbody = document.getElementById('repartidoresBody');
    if (!repartidores || repartidores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay repartidores disponibles</td></tr>';
        return;
    }
    
    let html = '';
    repartidores.forEach(rep => {
        let statusColor = '';
        let statusIcon = '';
        
        switch(rep.status) {
            case 'Disponible': statusColor = 'success'; statusIcon = 'bi-check-circle'; break;
            case 'En recorrido': statusColor = 'warning'; statusIcon = 'bi-truck'; break;
            case 'Fuera de horario': statusColor = 'secondary'; statusIcon = 'bi-clock'; break;
            default: statusColor = 'danger'; statusIcon = 'bi-exclamation-circle';
        }
        
        const nombreSucursal = sucursalesMap[rep.sucursal] || `Sucursal ${rep.sucursal}`;
        
        // Determinar qué mostrar en la columna "Seleccionar"
        let columnaSeleccion = '';
        
        if (esRepartidor) {
            // Repartidor: texto "---" deshabilitado (mantiene estructura)
            columnaSeleccion = '<span class="text-muted">---</span>';
        } else if (sucursalAsignada === 0 && rep.status === 'Disponible') {
            // CRM: radio para seleccionar (solo disponibles)
            columnaSeleccion = `<input type="radio" name="repartidor" value="${rep.id}" data-nombre="${rep.nombre}">`;
        } else if (sucursalAsignada > 0) {
            // Usuario de sucursal: solo texto "---"
            columnaSeleccion = '<span class="text-muted">---</span>';
        } else {
            // Otros casos: deshabilitado
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
    
    // Agregar event listeners a los radios (solo CRM)
    if (!esRepartidor && sucursalAsignada === 0) {
        document.querySelectorAll('input[name="repartidor"]').forEach(radio => {
            radio.addEventListener('change', function() {
                repartidorSeleccionadoId = this.value;
                const btnAsignar = document.getElementById('btnAsignar');
                if (btnAsignar) {
                    btnAsignar.disabled = !(repartidorSeleccionadoId && pedidosCRMSeleccionados.length > 0);
                }
            });
        });
    }
}

// ============================================
// TABLA PEDIDOS CRM (para asignar múltiples)
// ============================================
function actualizarTablaPedidosCRM(pedidos) {
    const tbody = document.getElementById('pedidosCRMBody');
    if (!pedidos || pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay pedidos pendientes por asignar</td></tr>';
        return;
    }
    
    let html = '';
    pedidos.forEach(pedido => {
        html += `<tr data-pedido-id="${pedido.id_pedido}">
            <td class="text-center">
                <input type="checkbox" class="checkbox-pedido-crm" 
                       data-id="${pedido.id_pedido}"
                       data-folio="${pedido.folio_pedido}"
                       data-cliente="${pedido.nombrecliente.replace(/"/g, '&quot;')}"
                       data-direccion="${pedido.Domicilio.replace(/"/g, '&quot;')}"
                       data-importe="${pedido.importeticket}"
                       data-sucursal="${pedido.sucursal}">
            </td>
            <td><span class="badge bg-primary">${pedido.folio_pedido}</span></td>
            <td>${pedido.nombrecliente}</td>
            <td>${pedido.Domicilio}</td>
            <td>$${Number(pedido.importeticket).toFixed(2)}</td>
            <td>${sucursalesMap[pedido.sucursal] || 'CRM'}</td>
            <td><span class="badge bg-info">Esperando repartidor</span></td>
        </tr>`;
    });
    tbody.innerHTML = html;
    
    document.querySelectorAll('.checkbox-pedido-crm').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            actualizarPedidosCRMSeleccionados();
        });
    });
    
    const selectAll = document.getElementById('seleccionarTodosPedidosCRM');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.checkbox-pedido-crm').forEach(cb => {
                cb.checked = selectAll.checked;
            });
            actualizarPedidosCRMSeleccionados();
        });
    }
}

function actualizarPedidosCRMSeleccionados() {
    pedidosCRMSeleccionados = [];
    document.querySelectorAll('.checkbox-pedido-crm:checked').forEach(checkbox => {
        pedidosCRMSeleccionados.push({
            id_pedido: parseInt(checkbox.dataset.id),
            folio_pedido: checkbox.dataset.folio,
            nombrecliente: checkbox.dataset.cliente,
            Domicilio: checkbox.dataset.direccion,
            importeticket: parseFloat(checkbox.dataset.importe),
            sucursal: parseInt(checkbox.dataset.sucursal)
        });
    });
    
    const btnAsignar = document.getElementById('btnAsignar');
    if (btnAsignar) {
        btnAsignar.disabled = !(repartidorSeleccionadoId && pedidosCRMSeleccionados.length > 0);
    }
}

// ============================================
// TABLA PEDIDOS REPARTIDOR
// ============================================
function actualizarTablaPedidosPendientes(pedidos) {
    const tbody = document.getElementById('pedidosPendientesBody');
    if (!pedidos || pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay pedidos pendientes</td></tr>';
        document.getElementById('btnIniciarRecorrido').disabled = true;
        return;
    }
    
    // Obtener horario del repartidor desde la tabla de repartidores
    let repartidorEnHorario = true;
    let mensajeHorario = '';
    
    // Buscar la fila del repartidor actual en la tabla de repartidores
    const filaRepartidor = document.querySelector('#tablaRepartidores tbody tr');
    if (filaRepartidor) {
        const horarioTexto = filaRepartidor.cells[3]?.textContent || '';
        const statusTexto = filaRepartidor.cells[4]?.textContent || '';
        
        if (statusTexto.includes('Fuera de horario')) {
            repartidorEnHorario = false;
            mensajeHorario = 'Fuera de horario laboral';
        }
    }
    
    let html = '';
    pedidos.forEach(pedido => {
        html += `<tr data-pedido-id="${pedido.id_pedido}">
            <td class="text-center">
                <input type="checkbox" class="checkbox-pedido" 
                       data-id="${pedido.id_pedido}"
                       data-folio-ticket="${pedido.folio_ticket}"
                       data-nombrecliente="${pedido.nombrecliente.replace(/"/g, '&quot;')}"
                       data-domicilio="${pedido.Domicilio.replace(/"/g, '&quot;')}"
                       data-importe="${pedido.importeticket}"
                       data-sucursal="${pedido.sucursal}"
                       ${!repartidorEnHorario ? 'disabled' : ''}>
            </td>
            <td>${pedido.folio_pedido}</td>
            <td>${pedido.nombrecliente}</td>
            <td>${pedido.Domicilio}</td>
            <td>$${Number(pedido.importeticket).toFixed(2)}</td>
            <td>${sucursalesMap[pedido.sucursal] || 'CRM'}</td>
        </tr>`;
    });
    tbody.innerHTML = html;
    
    // Mostrar mensaje de advertencia si está fuera de horario
    if (!repartidorEnHorario) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning alert-sm mt-2 py-1';
        alertDiv.innerHTML = `<i class="bi bi-exclamation-triangle"></i> ${mensajeHorario} - No puedes iniciar nuevos recorridos`;
        tbody.parentNode.insertAdjacentElement('afterend', alertDiv);
        document.getElementById('btnIniciarRecorrido').disabled = true;
        return;
    }
    
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

// ============================================
// TABLA ENTREGAS EN CURSO
// ============================================
function actualizarTablaEntregas(entregas) {
    const tbody = document.getElementById('entregasBody');
    if (!entregas || entregas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay entregas en curso</td></tr>';
        return;
    }
    
    let html = '';
    entregas.forEach(entrega => {
        const horaSalida = entrega.hora_salida || '';
        html += `<tr data-recibido-id="${entrega.id}">
            <td class="text-center">`;
        if (esRepartidor) {
            html += `<input type="checkbox" class="checkbox-recorrido" value="${entrega.id}">`;
        } else {
            html += '---';
        }
        html += `</td>
            <td><strong>${entrega.repartidor_nombre} ${entrega.repartidor_apaterno || ''}</strong></td>
            <td>${entrega.nombrecliente || 'N/A'}</td>
            <td>${entrega.Domicilio || 'N/A'}</td>
            <td>${horaSalida ? horaSalida.substring(0,5) : 'N/A'}</td>
            <td><span class="badge bg-info tiempo-fuera" data-inicio="${horaSalida}">00:00:00</span></td>
        </tr>`;
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
    document.querySelectorAll('.tiempo-fuera').forEach(el => {
        const horaInicioStr = el.getAttribute('data-inicio');
        if (horaInicioStr) {
            const partes = horaInicioStr.split(':');
            if (partes.length >= 2) {
                const ahora = new Date();
                const inicio = new Date(ahora);
                inicio.setHours(parseInt(partes[0]), parseInt(partes[1]), partes[2] ? parseInt(partes[2]) : 0, 0);
                let diffMs = Math.max(0, ahora - inicio);
                const horas = Math.floor(diffMs / 3600000);
                const minutos = Math.floor((diffMs % 3600000) / 60000);
                const segundos = Math.floor((diffMs % 60000) / 1000);
                el.textContent = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
            }
        }
    });
}

// Inicializar intervalos
cargarDatos();
intervaloActualizacion = setInterval(cargarDatos, 60000);
setInterval(actualizarTiemposFuera, 1000);

// ============================================
// CRM: ASIGNAR REPARTIDOR A MÚLTIPLES PEDIDOS
// ============================================
const btnAsignar = document.getElementById('btnAsignar');
if (btnAsignar) {
    btnAsignar.addEventListener('click', function() {
        if (!repartidorSeleccionadoId) {
            if (window.mostrarToast) window.mostrarToast('Selecciona un repartidor', 'warning');
            return;
        }
        if (pedidosCRMSeleccionados.length === 0) {
            if (window.mostrarToast) window.mostrarToast('Selecciona al menos un pedido', 'warning');
            return;
        }
        
        const pedidosIds = pedidosCRMSeleccionados.map(p => p.id_pedido);
        
        fetch('{{ route("ventas.pedidos.asignarRepartidor") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id_repartidor: parseInt(repartidorSeleccionadoId), pedidos_ids: pedidosIds })
        })
        .then(response => response.json())
        .then(data => {
            if (window.mostrarToast) window.mostrarToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) setTimeout(() => window.location.reload(), 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    });
}

// ============================================
// REPARTIDOR: INICIAR RECORRIDO MÚLTIPLE
// ============================================
function abrirModalIniciarRecorrido() {
    if (pedidosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Selecciona al menos un pedido', 'warning');
        return;
    }
    
    // Actualizar contador
    document.getElementById('totalPedidosSeleccionados').innerText = pedidosSeleccionados.length;
    
    // Generar tabla editable con inputs compactos
    let html = '';
    pedidosSeleccionados.forEach((pedido, index) => {
        html += `
            <tr data-pedido-index="${index}">
                <td class="text-center align-middle">${index + 1}</td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-folio-ticket" 
                           value="" placeholder="Ingrese" data-index="${index}" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-cliente" 
                           value="${pedido.nombrecliente.replace(/"/g, '&quot;')}" data-index="${index}" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-direccion" 
                           value="${pedido.Domicilio.replace(/"/g, '&quot;')}" data-index="${index}" required>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm campo-importe text-end" 
                           value="${pedido.importeticket}" data-index="${index}" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           value="${sucursalesMap[pedido.sucursal] || 'CRM'}" readonly disabled>
                </td>
            </tr>
        `;
    });
    document.getElementById('listaPedidosRecorrido').innerHTML = html;
    
    // Limpiar campo km inicial
    document.getElementById('recorrido_kminicial').value = '';
    
    // Hora de salida: hora actual del sistema (no editable)
    const ahora = new Date();
    const horaActual = ahora.toLocaleTimeString('es-MX', { hour12: false }).substring(0, 5);
    document.getElementById('recorrido_hora_salida').value = horaActual;
    
    new bootstrap.Modal(document.getElementById('modalIniciarRecorrido')).show();
}

function iniciarRecorridoMultiple() {
    const kmInicial = document.getElementById('recorrido_kminicial').value;
    const horaSalida = document.getElementById('recorrido_hora_salida').value;
    
    if (!kmInicial) {
        if (window.mostrarToast) window.mostrarToast('Kilometraje inicial obligatorio', 'warning');
        return;
    }
    
    // Recoger datos editados de la tabla
    const pedidosActualizados = [];
    const filas = document.querySelectorAll('#listaPedidosRecorrido tr');
    let hayError = false;
    
    for (let i = 0; i < filas.length; i++) {
        const fila = filas[i];
        const pedidoOriginal = pedidosSeleccionados[i];
        
        let folioTicket = fila.querySelector('.campo-folio-ticket').value;
        const nombreCliente = fila.querySelector('.campo-cliente').value;
        const domicilio = fila.querySelector('.campo-direccion').value;
        const importe = fila.querySelector('.campo-importe').value;
        
        // Validar folio_ticket: puede ser 0 o string, pero no vacío
        if (folioTicket === null || folioTicket === '') {
            if (window.mostrarToast) window.mostrarToast(`Folio ticket es obligatorio para pedido ${i + 1} (puede ser 0)`, 'warning');
            hayError = true;
            return;
        }
        
        if (!nombreCliente) {
            if (window.mostrarToast) window.mostrarToast(`Nombre de cliente obligatorio para pedido ${i + 1}`, 'warning');
            hayError = true;
            return;
        }
        if (!domicilio) {
            if (window.mostrarToast) window.mostrarToast(`Dirección obligatoria para pedido ${i + 1}`, 'warning');
            hayError = true;
            return;
        }
        if (!importe || importe < 0) {
            if (window.mostrarToast) window.mostrarToast(`Importe válido obligatorio para pedido ${i + 1}`, 'warning');
            hayError = true;
            return;
        }
        
        pedidosActualizados.push({
            id_pedido: pedidoOriginal.id_pedido,
            folio_ticket: folioTicket.toString(),
            nombrecliente: nombreCliente,
            Domicilio: domicilio,
            importeticket: parseFloat(importe),
            sucursal: pedidoOriginal.sucursal
        });
    }
    
    if (hayError) return;
    
    const btn = document.querySelector('#modalIniciarRecorrido .btn-success');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch('{{ route("recorridos.iniciar") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ 
            pedidos: pedidosActualizados, 
            kminicial: parseInt(kmInicial), 
            hora_salida: horaSalida 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (window.mostrarToast) window.mostrarToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) setTimeout(() => window.location.reload(), 1000);
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (!data.success) return;
        bootstrap.Modal.getInstance(document.getElementById('modalIniciarRecorrido')).hide();
    })
    .catch(error => { 
        console.error('Error:', error); 
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger'); 
        btn.disabled = false; 
        btn.innerHTML = originalText; 
    });
}

// ============================================
// REPARTIDOR: FINALIZAR RECORRIDO MÚLTIPLE
// ============================================
function abrirModalFinalizarRecorrido() {
    if (recorridosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Selecciona al menos un recorrido', 'warning');
        return;
    }
    document.getElementById('totalRecorridosSeleccionados').innerText = recorridosSeleccionados.length;
    document.getElementById('finalizar_kmfinal').value = '';
    new bootstrap.Modal(document.getElementById('modalFinalizarRecorrido')).show();
}

function confirmarFinalizarRecorridoMultiple() {
    const kmFinal = document.getElementById('finalizar_kmfinal').value;
    if (!kmFinal) { if (window.mostrarToast) window.mostrarToast('Kilometraje final obligatorio', 'warning'); return; }
    
    const btn = document.querySelector('#modalFinalizarRecorrido .btn-warning');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch('{{ route("recorridos.finalizar") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ kmfinal: parseInt(kmFinal), recorridos_ids: recorridosSeleccionados })
    })
    .then(response => response.json())
    .then(data => {
        if (window.mostrarToast) window.mostrarToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) setTimeout(() => window.location.reload(), 1000);
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (!data.success) return;
        bootstrap.Modal.getInstance(document.getElementById('modalFinalizarRecorrido')).hide();
    })
    .catch(error => { console.error('Error:', error); if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger'); btn.disabled = false; btn.innerHTML = originalText; });
}

// Event listeners
document.getElementById('btnIniciarRecorrido')?.addEventListener('click', abrirModalIniciarRecorrido);
document.getElementById('btnFinalizarRecorrido')?.addEventListener('click', abrirModalFinalizarRecorrido);

// Limpiar intervalos
window.addEventListener('beforeunload', () => {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
});
</script>
@endsection