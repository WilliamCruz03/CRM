@php
    $modoSoloLectura = $modoSoloLectura ?? false;
@endphp

@extends('layouts.app')

@section('title', 'Asignar Repartidor')
@section('page-title', 'Asignar Repartidor - ' . ($esRepartidor ? 'Mis Pedidos' : 'Gestión de Repartidores'))

@section('content')
<div class="container-fluid">
    <div class="card">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-person-badge"></i> 
                @if($esRepartidor)
                    Mis recorridos
                @elseif($sucursalAsignada > 0)
                    Ver repartidores - {{ $sucursalAsignada }}
                @else
                    Asignar Repartidor
                @endif
            </h5>
            <div>
                <button type="button" class="btn btn-sm btn-outline-light me-2" id="btnRefrescarAsignacion">
                    <i class="bi bi-arrow-repeat"></i> Refrescar
                </button>
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
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
                                <th>Folio Ticket</th>
                                <th>Cliente</th>
                                <th>Direccion</th>
                                <th>Importe</th>
                                <th>Sucursal(es)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="pedidosCRMBody">
                            <tr><td colspan="8" class="text-center">Cargando...</td></tr>
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
                                <th>Folio Pedido</th>
                                <th>Folio Ticket</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Importe</th>
                                <th>Sucursal</th>
                            </tr>
                        </thead>
                        <tbody id="pedidosPendientesBody">
                            <tr><td colspan="7" class="text-center">Cargando...</td></tr>
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
                            <th style="width: 5%">
                                @if($esRepartidor && !$modoSoloLectura)
                                    <input type="checkbox" id="seleccionarTodosRecorridos" title="Seleccionar todos">
                                @else
                                    <span class="text-muted">Seleccionar</span>
                                @endif
                            </th>
                            <th>Repartidor</th>
                            <th>Folio Ticket</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Hora salida</th>
                            <th>Tiempo fuera</th>
                        </tr>
                    </thead>
                    <tbody id="entregasBody">
                        <tr><td colspan="7" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- ========================================== -->
            <!-- BOTONES DE ACCIÓN -->
            <!-- ========================================== -->
            <div class="mt-4 text-end">
                @if($esRepartidor)
                    @if($puedeIniciarRecorrido ?? false)
                        <button type="button" class="btn btn-success" id="btnIniciarRecorrido" disabled>
                            <i class="bi bi-play-circle"></i> Iniciar recorrido seleccionado
                        </button>
                        <button type="button" class="btn btn-warning" id="btnFinalizarRecorrido" disabled>
                            <i class="bi bi-stop-circle"></i> Finalizar recorrido(s) seleccionado(s)
                        </button>
                    @else
                        <div class="alert alert-info text-start">
                            <i class="bi bi-info-circle"></i> No tienes permiso para iniciar recorridos. Solo puedes ver tus pedidos asignados.
                        </div>
                    @endif
                @elseif($sucursalAsignada == 0 && $permisos['crear'])
                    {{-- CRM con permiso de crear --}}
                    <button type="button" class="btn btn-primary" id="btnAsignar" disabled>
                        <i class="bi bi-person-badge"></i> Asignar repartidor a pedidos seleccionados
                    </button>
                @elseif($sucursalAsignada == 0 && !$permisos['crear'])
                    {{-- CRM sin permiso de crear --}}
                    <div class="alert alert-info text-start">
                        <i class="bi bi-info-circle"></i> No tienes permiso para asignar repartidores. Solo puedes ver el listado.
                    </div>
                @elseif($sucursalAsignada > 0 && $permisos['ver'])
                    {{-- Sucursal con permiso de ver --}}
                    <button type="button" class="btn btn-info" disabled>
                        <i class="bi bi-eye"></i> Modo solo lectura
                    </button>
                @endif
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
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="recorrido_hora_salida" readonly style="background-color: #e9ecef; font-family: monospace; font-size: 1rem; font-weight: bold;" value="--:--:--">
                                <span class="input-group-text bg-info text-white"><i class="bi bi-clock-history"></i></span>
                            </div>
                            <small class="text-muted">Hora actual en tiempo real - Se registrará al iniciar</small>
                            <div class="alert alert-warning py-1 px-2 mb-0 small">
                                <i class="bi bi-info-circle"></i> La hora se toma al momento de hacer clic en "Iniciar Recorrido"
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
                        <small>Kilometraje actual al regresar (aplica para todos los pedidos del recorrido)</small>
                    </div>
                    <div class="alert alert-info">
                        <strong>Hora actual:</strong> <span id="finalizar_hora_regreso" style="font-family: monospace; font-size: 1.1rem;">--:--:--</span>
                        <br>
                        <small>La hora se registrará automáticamente al confirmar</small>
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
let puedeAsignar = false;
let esRepartidor = {{ $esRepartidor ? 'true' : 'false' }};
let sucursalAsignada = {{ $sucursalAsignada }};
let pedidosSeleccionados = [];
let recorridosSeleccionados = [];
let pedidosCRMSeleccionados = [];
// Variables para los intervalos de tiempo en entregas
let intervaloHoraInicio = null;
let intervaloHoraFinal = null;
let modoSoloLectura = {{ $modoSoloLectura ? 'true' : 'false' }};

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
// CARGA DE DATOS - SECUENCIAL (evita colisión de sesión)
// ============================================

let cargandoDatos = false;
let colaPendiente = false;

async function cargarDatos() {
    // Mutex: si ya está cargando, encolar
    if (cargandoDatos) {
        colaPendiente = true;
        return;
    }

    cargandoDatos = true;
    colaPendiente = false;

    // Timeout de seguridad (15 segundos)
    let timeoutSeguridad = setTimeout(() => {
        if (cargandoDatos) {
            cargandoDatos = false;
            if (window.mostrarToast) {
                window.mostrarToast('La carga está tomando más tiempo de lo esperado.', 'warning');
            }
        }
    }, 15000);

    try {
        // ==========================================
        // PASO 1: Repartidores y entregas (SIEMPRE PRIMERO)
        // ==========================================
        const response1 = await fetch('{{ route("ventas.pedidos.repartidores.status", $pedido->id_pedido) }}', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (!response1.ok) {
            throw new Error(`HTTP error! status: ${response1.status}`);
        }

        const data1 = await response1.json();

        if (data1.success) {
            puedeAsignar = (data1.sucursal_asignada === 0 && !data1.es_repartidor);
            actualizarTablaRepartidores(data1.repartidores);
            actualizarTablaEntregas(data1.entregas_curso);

            const btnAsignar = document.getElementById('btnAsignar');
            if (btnAsignar) {
                btnAsignar.disabled = !puedeAsignar;
            }
        }

        // ==========================================
        // PASO 2: Pedidos pendientes (DESPUÉS DEL PASO 1)
        // ==========================================
        if (esRepartidor) {
            const response2 = await fetch('{{ route("ventas.pedidos.pendientes.repartidor") }}', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (!response2.ok) {
                throw new Error(`HTTP error! status: ${response2.status}`);
            }

            const data2 = await response2.json();

            if (data2.success) {
                actualizarTablaPedidosPendientes(data2.pedidos);
            }

        } else if (sucursalAsignada === 0) {
            const response2 = await fetch('{{ route("ventas.pedidos.pendientes.crm") }}', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (!response2.ok) {
                throw new Error(`HTTP error! status: ${response2.status}`);
            }

            const data2 = await response2.json();

            if (data2.success) {
                actualizarTablaPedidosCRM(data2.pedidos);
            }
        }

    } catch (error) {
        console.error('Error en carga de datos:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error al cargar datos. Recarga la página.', 'danger');
        }
    } finally {
        clearTimeout(timeoutSeguridad);
        cargandoDatos = false;

        if (colaPendiente) {
            colaPendiente = false;
            setTimeout(cargarDatos, 200);
        }
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
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay pedidos pendientes por asignar</td></tr>';
        return;
    }
    
    let html = '';
    pedidos.forEach(pedido => {
        const disponible = pedido.sucursales_listas === true && !modoSoloLectura;
        
        // Formatear folio ticket para mostrar
        const folioCompleto = pedido.folio_ticket || '';
        let folioMostrar = '';
        if (folioCompleto) {
            const str = String(folioCompleto);
            const caja = str.charAt(0);
            const ticket = str.substring(1);
            folioMostrar = `Caja ${caja}: ${ticket}`;
        } else {
            folioMostrar = '';
        }
        
        html += `<tr data-pedido-id="${pedido.id_pedido}">
            <td class="text-center">
                <input type="checkbox" class="checkbox-pedido-crm" 
                       data-id="${pedido.id_pedido}"
                       data-folio="${pedido.folio_pedido}"
                       data-folio-ticket="${pedido.folio_ticket || ''}"
                       data-cliente="${pedido.nombrecliente.replace(/"/g, '&quot;')}"
                       data-direccion="${pedido.Domicilio.replace(/"/g, '&quot;')}"
                       data-importe="${pedido.importeticket}"
                       data-sucursal="${pedido.sucursal}"
                       ${!disponible ? 'disabled' : ''}>
            </td>
            <td><span class="badge bg-primary">${pedido.folio_pedido}</span></td>
            <td>${folioMostrar}</td>
            <td>${pedido.nombrecliente}</td>
            <td>${pedido.Domicilio}</td>
            <td>$${Number(pedido.importeticket).toFixed(2)}</td>
            <td>${sucursalesMap[pedido.sucursal] || 'CRM'}</td>
            <td>${disponible ? '<span class="badge bg-success">Sucursales listas</span>' : '<span class="badge bg-warning">Esperando sucursales</span>'}</td>
        </tr>`;
    });
    tbody.innerHTML = html;
    
    // Mostrar mensaje si no hay pedidos disponibles
    const pedidosDisponibles = document.querySelectorAll('.checkbox-pedido-crm:not([disabled])').length;
    if (pedidosDisponibles === 0 && pedidos.length > 0) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning alert-sm mt-2 py-1';
        alertDiv.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Hay pedidos pendientes, pero las sucursales aún no los han marcado como listos. No puedes asignar repartidor hasta que todas las sucursales estén listas.`;
        tbody.parentNode.insertAdjacentElement('afterend', alertDiv);
    }
    
    // Agregar event listeners a los checkboxes disponibles
    document.querySelectorAll('.checkbox-pedido-crm:not([disabled])').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            actualizarPedidosCRMSeleccionados();
        });
    });
    
    // Checkbox "seleccionar todos" (solo habilitar pedidos disponibles)
    const selectAll = document.getElementById('seleccionarTodosPedidosCRM');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.checkbox-pedido-crm:not([disabled])').forEach(cb => {
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

function actualizarTablaPedidosPendientes(pedidos) {
    const tbody = document.getElementById('pedidosPendientesBody');
    if (!pedidos || pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay pedidos pendientes (las sucursales deben marcar como listo la orden de pedido)</td></tr>';
        document.getElementById('btnIniciarRecorrido').disabled = true;
        return;
    }
    
    // Permitir al repartidor inicar recorrido en cualquier horario
    const repartidorEnHorario = true;
    
    let html = '';
    pedidos.forEach(pedido => {
        const disponible = repartidorEnHorario && (pedido.sucursales_listas === true) && !modoSoloLectura;
        
        // Obtener la sucursal principal
        let sucursalPrincipal = pedido.sucursal || 0;
        if (pedido.sucursales && pedido.sucursales.length > 0) {
            sucursalPrincipal = pedido.sucursales[0].id_sucursal || 0;
        }
        
        // Construir desglose de sucursales
        let sucursalesHtml = '';
        if (pedido.sucursales && pedido.sucursales.length > 0) {
            const nombres = pedido.sucursales.map(s => s.nombre || 'Sin nombre');
            sucursalesHtml = nombres.join(', ');
        } else if (pedido.sucursal) {
            sucursalesHtml = sucursalesMap[pedido.sucursal] || 'CRM';
        } else {
            sucursalesHtml = 'Sin sucursal asignada';
        }
        
        // Formatear folio ticket para mostrar
        const folioCompleto = pedido.folio_ticket || '';
        let folioMostrar = '';
        if (folioCompleto) {
            const str = String(folioCompleto);
            const caja = str.charAt(0);
            const ticket = str.substring(1);
            folioMostrar = `Caja ${caja}: ${ticket}`;
        }
        
        html += `<tr data-pedido-id="${pedido.id_pedido}">
            <td class="text-center">
                <input type="checkbox" class="checkbox-pedido" 
                       data-id="${pedido.id_pedido}"
                       data-folio-ticket="${pedido.folio_ticket || ''}"
                       data-nombrecliente="${(pedido.nombrecliente || '').replace(/"/g, '&quot;')}"
                       data-domicilio="${(pedido.Domicilio || '').replace(/"/g, '&quot;')}"
                       data-importe="${pedido.importeticket || 0}"
                       data-sucursal="${sucursalPrincipal}"
                       data-sucursales='${JSON.stringify(pedido.sucursales || []).replace(/'/g, "\\'")}'
                       ${!disponible ? 'disabled' : ''}>
            </td>
            <td>${pedido.folio_pedido || ''}</td>
            <td>${folioMostrar}</td>
            <td>${pedido.nombrecliente || 'N/A'}</td>
            <td>${pedido.Domicilio || 'N/A'}</td>
            <td>$${Number(pedido.importeticket || 0).toFixed(2)}</td>
            <td>${sucursalesHtml}</td>
        </tr>`;
    });
    tbody.innerHTML = html;
    
    // Si hay pedidos pero no están disponibles por las sucursales
    const pedidosDisponibles = document.querySelectorAll('.checkbox-pedido:not([disabled])').length;
    if (pedidosDisponibles === 0 && pedidos.length > 0) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning alert-sm mt-2 py-1';
        alertDiv.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Hay pedidos asignados, pero las sucursales aun no los han marcado como listos. No puedes iniciar recorrido hasta que todas las sucursales esten listas.`;
        tbody.parentNode.insertAdjacentElement('afterend', alertDiv);
        document.getElementById('btnIniciarRecorrido').disabled = true;
        return;
    }
    
    // Agregar event listeners a los checkboxes disponibles
    document.querySelectorAll('.checkbox-pedido:not([disabled])').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            actualizarPedidosSeleccionados();
        });
    });
    
    // Checkbox "seleccionar todos" (solo habilitar pedidos disponibles)
    const selectAll = document.getElementById('seleccionarTodosPedidos');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.checkbox-pedido:not([disabled])').forEach(cb => {
                cb.checked = selectAll.checked;
            });
            actualizarPedidosSeleccionados();
        });
    }
}
 
function actualizarPedidosSeleccionados() {
    pedidosSeleccionados = [];
    document.querySelectorAll('.checkbox-pedido:checked').forEach(checkbox => {
        // Obtener sucursal desde data-sucursal
        let sucursal = parseInt(checkbox.dataset.sucursal) || 0;
        
        // Si no hay sucursal en data-sucursal, intentar desde data-sucursales
        if (!sucursal && checkbox.dataset.sucursales) {
            try {
                const sucursales = JSON.parse(checkbox.dataset.sucursales);
                if (sucursales && sucursales.length > 0) {
                    sucursal = sucursales[0].id_sucursal || 0;
                }
            } catch (e) {
                console.error('Error parsing sucursales:', e);
            }
        }
        
        pedidosSeleccionados.push({
            id_pedido: parseInt(checkbox.dataset.id),
            folio_ticket: checkbox.dataset.folioTicket || '',
            nombrecliente: checkbox.dataset.nombrecliente || '',
            Domicilio: checkbox.dataset.domicilio || '',
            importeticket: parseFloat(checkbox.dataset.importe) || 0,
            sucursal: sucursal
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
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay entregas en curso</td></tr>';
        const btnFinalizar = document.getElementById('btnFinalizarRecorrido');
        if (btnFinalizar) btnFinalizar.disabled = true;
        return;
    }
    
    let html = '';
    entregas.forEach(entrega => {
        const horaSalida = entrega.hora_salida || '';
        const checkedAttr = recorridosSeleccionados.includes(entrega.id) ? 'checked' : '';
        
        // Mostrar folio_ticket de oper_recorridos_choferes (6 dígitos)
        const folioMostrar = entrega.folio_ticket || '';
        
        html += `<tr data-recibido-id="${entrega.id}">
            <td class="text-center">
                <input type="checkbox" class="checkbox-recorrido" 
                       value="${entrega.id}" 
                       ${checkedAttr}
                       ${!esRepartidor || modoSoloLectura ? 'disabled' : ''}>
            </td>
            <td><strong>${entrega.repartidor_nombre} ${entrega.repartidor_apaterno || ''}</strong></td>
            <td>${folioMostrar}</td>
            <td>${entrega.nombrecliente || 'N/A'}</td>
            <td>${entrega.Domicilio || 'N/A'}</td>
            <td>${horaSalida ? horaSalida.substring(0,5) : 'N/A'}</td>
            <td><span class="badge bg-info tiempo-fuera" data-inicio="${horaSalida}">00:00:00</span></td>
        </tr>`;
    });
    tbody.innerHTML = html;
    
    // Agregar event listeners a los checkboxes de recorridos (solo repartidor)
    if (esRepartidor && !modoSoloLectura) {
        document.querySelectorAll('.checkbox-recorrido:not([disabled])').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                actualizarRecorridosSeleccionados();
            });
        });
        
        // Checkbox "seleccionar todos recorridos"
        const selectAllRecorridos = document.getElementById('seleccionarTodosRecorridos');
        if (selectAllRecorridos) {
            selectAllRecorridos.addEventListener('change', function() {
                document.querySelectorAll('.checkbox-recorrido:not([disabled])').forEach(cb => {
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
setInterval(actualizarTiemposFuera, 1000);

// ============================================
// CRM: ASIGNAR REPARTIDOR A MÚLTIPLES PEDIDOS - CON MUTEX
// ============================================
let asignandoEnProgreso = false;

const btnAsignar = document.getElementById('btnAsignar');
if (btnAsignar) {
    btnAsignar.addEventListener('click', function() {
        // ==========================================
        // 1. PREVENIR MÚLTIPLES CLICS
        // ==========================================
        if (asignandoEnProgreso) {
            if (window.mostrarToast) {
                window.mostrarToast('Ya hay una asignación en proceso, espera...', 'warning');
            }
            return;
        }
        
        // ==========================================
        // 2. VALIDACIONES
        // ==========================================
        if (!repartidorSeleccionadoId) {
            if (window.mostrarToast) {
                window.mostrarToast('Selecciona un repartidor', 'warning');
            }
            return;
        }
        
        if (pedidosCRMSeleccionados.length === 0) {
            if (window.mostrarToast) {
                window.mostrarToast('Selecciona al menos un pedido', 'warning');
            }
            return;
        }
        
        // ==========================================
        // 3. BLOQUEAR BOTÓN Y MOSTRAR ESTADO
        // ==========================================
        asignandoEnProgreso = true;
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Asignando...';
        
        // ==========================================
        // 4. PREPARAR Y EJECUTAR FETCH
        // ==========================================
        const pedidosIds = pedidosCRMSeleccionados.map(p => p.id_pedido);
        
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
        .then(response => {
            if (!response.ok) {
                if (response.status === 401 || response.status === 419) {
                    throw new Error('Sesión expirada. Por favor recarga la página.');
                }
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (window.mostrarToast) {
                window.mostrarToast(data.message, data.success ? 'success' : 'danger');
            }
            if (data.success) {
                setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(error => {
            console.error('Error al asignar repartidor:', error);
            let mensajeError = 'Error de conexión';
            if (error.message && error.message.includes('Sesión expirada')) {
                mensajeError = error.message;
            } else if (error.message) {
                mensajeError = error.message;
            }
            if (window.mostrarToast) {
                window.mostrarToast(mensajeError, 'danger');
            }
        })
        .finally(() => {
            // ==========================================
            // 5. DESBLOQUEAR BOTÓN (con retraso mínimo)
            // ==========================================
            setTimeout(() => {
                asignandoEnProgreso = false;
                btnAsignar.disabled = false;
                btnAsignar.innerHTML = originalText;
            }, 500);
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
        // Asegurar que sucursal tenga un valor
        const sucursalNombre = sucursalesMap[pedido.sucursal] || 'Sin sucursal';
        
        // Obtener el folio ticket del pedido (si existe)
        const folioTicket = pedido.folio_ticket || '';
        
        html += `
            <tr data-pedido-index="${index}">
                <td class="text-center align-middle">${index + 1}</td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-folio-ticket" 
                           value="${folioTicket}" placeholder="Ingrese" data-index="${index}" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-cliente" 
                           value="${(pedido.nombrecliente || '').replace(/"/g, '&quot;')}" data-index="${index}" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm campo-direccion" 
                           value="${(pedido.Domicilio || '').replace(/"/g, '&quot;')}" data-index="${index}" required>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm campo-importe text-end" 
                           value="${pedido.importeticket || 0}" data-index="${index}" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           value="${sucursalNombre}" readonly disabled>
                    <!-- Campo oculto para almacenar el ID de la sucursal -->
                    <input type="hidden" class="campo-sucursal-id" value="${pedido.sucursal || 0}">
                </td>
            </tr>
        `;
    });
    document.getElementById('listaPedidosRecorrido').innerHTML = html;
    
    // Limpiar campo km inicial
    document.getElementById('recorrido_kminicial').value = '';
    
    // Iniciar contador de hora en tiempo real
    if (intervaloHoraInicio) clearInterval(intervaloHoraInicio);
    actualizarHoraInicio();
    intervaloHoraInicio = setInterval(actualizarHoraInicio, 1000);
    
    new bootstrap.Modal(document.getElementById('modalIniciarRecorrido')).show();
}

function actualizarHoraInicio() {
    const ahora = new Date();
    const horaActual = ahora.toLocaleTimeString('es-MX', { hour12: false });
    document.getElementById('recorrido_hora_salida').value = horaActual;
}

// ============================================
// REPARTIDOR: INICIAR RECORRIDO MÚLTIPLE - CON MUTEX
// ============================================
let iniciandoRecorrido = false;

function iniciarRecorridoMultiple() {
    // ==========================================
    // 1. PREVENIR EJECUCIÓN SIMULTÁNEA
    // ==========================================
    if (iniciandoRecorrido) {
        if (window.mostrarToast) {
            window.mostrarToast('Ya se está iniciando un recorrido, espera...', 'warning');
        }
        return;
    }
    
    // ==========================================
    // 2. VALIDACIONES INICIALES
    // ==========================================
    const kmInicial = document.getElementById('recorrido_kminicial').value;
    const ahora = new Date();
    const horaSalida = ahora.toLocaleTimeString('es-MX', { hour12: false });
    
    if (!kmInicial) {
        if (window.mostrarToast) {
            window.mostrarToast('Kilometraje inicial obligatorio', 'warning');
        }
        return;
    }
    
    // ==========================================
    // 3. RECOGER DATOS DE LA TABLA
    // ==========================================
    const pedidosActualizados = [];
    const filas = document.querySelectorAll('#listaPedidosRecorrido tr');
    let hayError = false;
    
    for (let i = 0; i < filas.length; i++) {
        const fila = filas[i];
        const pedidoOriginal = pedidosSeleccionados[i];
        
        // Obtener valores de los campos
        const folioTicketInput = fila.querySelector('.campo-folio-ticket');
        const nombreClienteInput = fila.querySelector('.campo-cliente');
        const domicilioInput = fila.querySelector('.campo-direccion');
        const importeInput = fila.querySelector('.campo-importe');
        const sucursalHidden = fila.querySelector('.campo-sucursal-id');
        
        const folioTicket = folioTicketInput ? folioTicketInput.value : '';
        const nombreCliente = nombreClienteInput ? nombreClienteInput.value : '';
        const domicilio = domicilioInput ? domicilioInput.value : '';
        const importe = importeInput ? importeInput.value : '';
        
        // Obtener la sucursal del hidden input o del pedido original
        let sucursal = pedidoOriginal.sucursal || 0;
        if (sucursalHidden) {
            sucursal = parseInt(sucursalHidden.value) || sucursal;
        }
        
        // Validaciones
        if (!folioTicket || folioTicket === '') {
            if (window.mostrarToast) window.mostrarToast(`Folio ticket es obligatorio para pedido ${i + 1}`, 'warning');
            hayError = true;
            break;
        }

        // Convertir a número y validar que sea un entero válido
        const folioTicketNum = parseInt(folioTicket, 10);
        if (isNaN(folioTicketNum)) {
            if (window.mostrarToast) window.mostrarToast(`Folio ticket debe ser un número válido para pedido ${i + 1}`, 'warning');
            hayError = true;
            break;
        }

        // Validar que no sea negativo
        if (folioTicketNum < 0) {
            if (window.mostrarToast) window.mostrarToast(`Folio ticket no puede ser negativo para pedido ${i + 1}`, 'warning');
            hayError = true;
            break;
        }
        
        if (!nombreCliente || nombreCliente === '') {
            if (window.mostrarToast) window.mostrarToast(`Nombre de cliente obligatorio para pedido ${i + 1}`, 'warning');
            hayError = true;
            break;
        }
        
        if (!domicilio || domicilio === '') {
            if (window.mostrarToast) window.mostrarToast(`Dirección obligatoria para pedido ${i + 1}`, 'warning');
            hayError = true;
            break;
        }
        
        const importeNum = parseFloat(importe);
        if (isNaN(importeNum) || importeNum < 0) {
            if (window.mostrarToast) window.mostrarToast(`Importe válido obligatorio para pedido ${i + 1}`, 'warning');
            hayError = true;
            break;
        }
        
        pedidosActualizados.push({
            id_pedido: pedidoOriginal.id_pedido,
            folio_ticket: folioTicketNum,
            nombrecliente: nombreCliente,
            Domicilio: domicilio,
            importeticket: importeNum,
            sucursal: sucursal
        });
    }
    
    if (hayError) return;
    
    // Verificar que haya pedidos para enviar
    if (pedidosActualizados.length === 0) {
        if (window.mostrarToast) {
            window.mostrarToast('No hay pedidos válidos para iniciar el recorrido', 'warning');
        }
        return;
    }
    
    // ==========================================
    // 4. BLOQUEAR BOTÓN Y MOSTRAR ESTADO
    // ==========================================
    iniciandoRecorrido = true;
    const btn = document.querySelector('#modalIniciarRecorrido .btn-success');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    // ==========================================
    // 5. TIMEOUT DE SEGURIDAD (30 segundos)
    // ==========================================
    let timeoutSeguridad = setTimeout(() => {
        if (iniciandoRecorrido) {
            iniciandoRecorrido = false;
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (window.mostrarToast) {
                window.mostrarToast('La operación está tomando más tiempo de lo esperado. Intenta nuevamente.', 'warning');
            }
        }
    }, 30000);
    
    // ==========================================
    // 6. EJECUTAR FETCH
    // ==========================================
    fetch('{{ route("recorridos.iniciar") }}', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({ 
            pedidos: pedidosActualizados, 
            kminicial: parseInt(kmInicial), 
            hora_salida: horaSalida
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401 || response.status === 419) {
                throw new Error('Sesión expirada. Por favor recarga la página.');
            }
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (window.mostrarToast) {
            window.mostrarToast(data.message, data.success ? 'success' : 'danger');
        }
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalIniciarRecorrido'));
            if (modal) {
                modal.hide();
            }
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error al iniciar recorrido:', error);
        let mensajeError = 'Error de conexión';
        if (error.message && error.message.includes('Sesión expirada')) {
            mensajeError = error.message;
        } else if (error.message) {
            mensajeError = error.message;
        }
        if (window.mostrarToast) {
            window.mostrarToast(mensajeError, 'danger');
        }
    })
    .finally(() => {
        clearTimeout(timeoutSeguridad);
        setTimeout(() => {
            iniciandoRecorrido = false;
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, 500);
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
    
    // Iniciar contador de hora en tiempo real
    if (intervaloHoraFinal) clearInterval(intervaloHoraFinal);
    actualizarHoraFinal();
    intervaloHoraFinal = setInterval(actualizarHoraFinal, 1000);
    
    new bootstrap.Modal(document.getElementById('modalFinalizarRecorrido')).show();
}

function actualizarHoraFinal() {
    const ahora = new Date();
    const horaActual = ahora.toLocaleTimeString('es-MX', { hour12: false });
    const horaElement = document.getElementById('finalizar_hora_regreso');
    if (horaElement) {
        horaElement.textContent = horaActual;
    }
}

// ============================================
// REPARTIDOR: FINALIZAR RECORRIDO MÚLTIPLE - CON MUTEX
// ============================================
let finalizandoRecorrido = false;

function confirmarFinalizarRecorridoMultiple() {
    // ==========================================
    // 1. PREVENIR EJECUCIÓN SIMULTÁNEA
    // ==========================================
    if (finalizandoRecorrido) {
        if (window.mostrarToast) {
            window.mostrarToast('Ya se está finalizando un recorrido, espera...', 'warning');
        }
        return;
    }
    
    // ==========================================
    // 2. VALIDACIONES
    // ==========================================
    const kmFinal = document.getElementById('finalizar_kmfinal').value;
    const ahora = new Date();
    const horaRegreso = ahora.toLocaleTimeString('es-MX', { hour12: false });
    
    if (!kmFinal) {
        if (window.mostrarToast) {
            window.mostrarToast('Kilometraje final obligatorio', 'warning');
        }
        return;
    }
    
    if (recorridosSeleccionados.length === 0) {
        if (window.mostrarToast) {
            window.mostrarToast('No hay recorridos seleccionados', 'warning');
        }
        return;
    }
    
    
    // ==========================================
    // 3. BLOQUEAR BOTÓN Y MOSTRAR ESTADO
    // ==========================================
    finalizandoRecorrido = true;
    const btn = document.querySelector('#modalFinalizarRecorrido .btn-warning');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    // ==========================================
    // 4. TIMEOUT DE SEGURIDAD (30 segundos)
    // ==========================================
    let timeoutSeguridad = setTimeout(() => {
        if (finalizandoRecorrido) {
            finalizandoRecorrido = false;
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (window.mostrarToast) {
                window.mostrarToast('La operación está tomando más tiempo de lo esperado. Intenta nuevamente.', 'warning');
            }
        }
    }, 30000);
    
    // ==========================================
    // 5. EJECUTAR FETCH
    // ==========================================
    fetch('{{ route("recorridos.finalizar") }}', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({ 
            kmfinal: parseInt(kmFinal), 
            recorridos_ids: recorridosSeleccionados,
            hora_regreso: horaRegreso
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401 || response.status === 419) {
                throw new Error('Sesión expirada. Por favor recarga la página.');
            }
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (window.mostrarToast) {
            window.mostrarToast(data.message, data.success ? 'success' : 'danger');
        }
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalFinalizarRecorrido'));
            if (modal) {
                modal.hide();
            }
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error al finalizar recorrido:', error);
        let mensajeError = 'Error de conexión';
        if (error.message && error.message.includes('Sesión expirada')) {
            mensajeError = error.message;
        } else if (error.message) {
            mensajeError = error.message;
        }
        if (window.mostrarToast) {
            window.mostrarToast(mensajeError, 'danger');
        }
    })
    .finally(() => {
        clearTimeout(timeoutSeguridad);
        setTimeout(() => {
            finalizandoRecorrido = false;
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, 500);
    });
}

// ============================================
// POLLING LIGERO PARA ACTUALIZAR ASIGNACIÓN DE REPARTIDORES
// ============================================

let pollingAsignacionInterval = null;
let ultimoIdRepartidor = 0;
let ultimoIdEntrega = 0;
let ultimoIdPedido = 0;
let refrescandoAsignacion = false;

/**
 * Refrescar los datos de asignación vía AJAX
 * Solo actualiza si hubo cambios reales en el backend
 * 
 * @param {boolean} mostrarNotificacion - Si debe mostrar un toast al actualizar
 * @param {boolean} desdePolling - Si la llamada viene del polling automático
 */
function refrescarAsignacion(mostrarNotificacion = false, desdePolling = false) {
    // Determinar si es una solicitud manual (para mostrar toasts)
    const isManual = mostrarNotificacion && !desdePolling;
    
    // Si ya hay un refresco en curso, mostrar toast informativo (solo si es manual)
    if (refrescandoAsignacion) {
        if (isManual && window.mostrarToast) {
            window.mostrarToast('Ya hay una actualización en proceso, espera un momento...', 'warning');
        }
        return;
    }
    refrescandoAsignacion = true;

    // Si es una solicitud manual (botón), mostrar estado de carga
    const btnRefrescar = document.getElementById('btnRefrescarAsignacion');
    let originalText = '';
    
    if (isManual && btnRefrescar) {
        originalText = btnRefrescar.innerHTML;
        btnRefrescar.disabled = true;
        btnRefrescar.innerHTML = '<i class="bi bi-hourglass-split"></i> Cargando...';
    }
    
    // Construir URL con los últimos IDs conocidos
    let url = '{{ route("ventas.pedidos.refrescar-asignacion") }}';
    url += '?ultimo_id_repartidor=' + ultimoIdRepartidor;
    url += '&ultimo_id_entrega=' + ultimoIdEntrega;
    url += '&ultimo_id_pedido=' + ultimoIdPedido;
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401 || response.status === 419) {
                throw new Error('Sesión expirada. Por favor recarga la página.');
            }
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            let actualizado = false;
            
            // Solo procesar si hay cambios
            if (data.hay_cambios) {
                // Actualizar repartidores
                if (data.repartidores) {
                    actualizarTablaRepartidores(data.repartidores);
                    ultimoIdRepartidor = data.ultimo_id_repartidor || 0;
                    actualizado = true;
                }
                
                // Actualizar entregas en curso
                if (data.entregas_curso) {
                    actualizarTablaEntregas(data.entregas_curso);
                    ultimoIdEntrega = data.ultimo_id_entrega || 0;
                    actualizado = true;
                }
                
                // Actualizar pedidos pendientes (si es repartidor)
                if (data.pedidos_pendientes) {
                    actualizarTablaPedidosPendientes(data.pedidos_pendientes);
                    ultimoIdPedido = data.ultimo_id_pedido || 0;
                    actualizado = true;
                }
                
                // Actualizar pedidos CRM (si es CRM)
                if (data.pedidos_crm) {
                    actualizarTablaPedidosCRM(data.pedidos_crm);
                    ultimoIdPedido = data.ultimo_id_pedido || 0;
                    actualizado = true;
                }
                
                // Notificar al usuario solo si la actualización fue manual
                if (isManual && window.mostrarToast) {
                    if (actualizado) {
                        window.mostrarToast('Datos actualizados correctamente', 'success');
                    } else {
                        window.mostrarToast('No se detectaron cambios', 'info');
                    }
                }
            } else {
                // No hubo cambios, informar al usuario si es manual
                if (isManual && window.mostrarToast) {
                    window.mostrarToast('No hay cambios nuevos', 'warning');
                }
            }
        }
    })
    .catch(error => {
        console.error('Error refrescando asignación:', error);
        if (isManual && window.mostrarToast) {
            let mensajeError = 'Error al actualizar datos';
            if (error.message && error.message.includes('Sesión expirada')) {
                mensajeError = error.message;
            }
            window.mostrarToast(mensajeError, 'danger');
        }
    })
    .finally(() => {
        refrescandoAsignacion = false;
        // Restaurar botón si era una solicitud manual
        if (isManual && btnRefrescar) {
            btnRefrescar.disabled = false;
            btnRefrescar.innerHTML = originalText;
        }
    });
}

/**
 * Iniciar el polling para actualizar automáticamente
 */
function iniciarPollingAsignacion() {
    // Limpiar intervalo anterior si existe
    if (pollingAsignacionInterval) {
        clearInterval(pollingAsignacionInterval);
    }
    
    // Ejecutar cada 30 segundos
    pollingAsignacionInterval = setInterval(() => {
        // Solo actualizar si la pestaña está visible
        if (!document.hidden) {
            refrescarAsignacion(false, true);
        }
    }, 30000); // 30 segundos
}

// INICIALIZACIÓN Y EVENT LISTENERS

// 1. Carga inicial de datos (una sola vez al entrar a la página)
setTimeout(cargarDatos, 100);

// 2. Actualizar tiempos fuera cada 1 segundo (solo visual)
setInterval(actualizarTiemposFuera, 1000);

// 3. NOTA: Ya no usamos intervalo para cargarDatos()
//    El polling de 30 segundos (refrescarAsignacion) se encarga de mantener los datos actualizados
//    Esto reduce la carga en el servidor y evita duplicación de consultas

// 4. Limpiar intervalos al cerrar modales
document.getElementById('modalIniciarRecorrido')?.addEventListener('hidden.bs.modal', function () {
    if (intervaloHoraInicio) clearInterval(intervaloHoraInicio);
});

document.getElementById('modalFinalizarRecorrido')?.addEventListener('hidden.bs.modal', function () {
    if (intervaloHoraFinal) clearInterval(intervaloHoraFinal);
});

// 5. Event listeners de botones
document.getElementById('btnIniciarRecorrido')?.addEventListener('click', abrirModalIniciarRecorrido);
document.getElementById('btnFinalizarRecorrido')?.addEventListener('click', abrirModalFinalizarRecorrido);

// 6. Limpiar intervalos al salir de la página
window.addEventListener('beforeunload', () => {
    if (pollingAsignacionInterval) {
        clearInterval(pollingAsignacionInterval);
    }
    if (intervaloHoraInicio) clearInterval(intervaloHoraInicio);
    if (intervaloHoraFinal) clearInterval(intervaloHoraFinal);
});

// ============================================
// INICIALIZACIÓN DEL POLLING Y BOTÓN DE REFRESCAR
// ============================================

// El botón ya existe en el HTML estático, solo asignamos el evento
document.addEventListener('DOMContentLoaded', function() {
    const btnRefrescar = document.getElementById('btnRefrescarAsignacion');
    if (btnRefrescar) {
        btnRefrescar.addEventListener('click', function() {
            refrescarAsignacion(true, false);
        });
    }
});

// Iniciar polling después de la carga inicial
setTimeout(() => {
    iniciarPollingAsignacion();
}, 2000);

// Evento: cuando la pestaña se vuelve visible, refrescar inmediatamente
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        refrescarAsignacion(false, true);
    }
});
</script>
@endsection