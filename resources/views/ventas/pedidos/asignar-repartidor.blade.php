@php
    $modoSoloLectura = $modoSoloLectura ?? false;
    $user = auth()->user();
    $esCRM = $user->es_crm;
    $esSucursal = $user->es_sucursal;
    $esRepartidor = $user->es_repartidor;

    $perfilesActivos = ($esCRM ? 1 : 0) + ($esSucursal ? 1 : 0) + ($esRepartidor ? 1 : 0);

    // Título y subtítulo según combinación de perfiles (sin duplicidad)
    $titulos = [
        0 => ['Gestión de Repartidores', 'Panel de control'],
        'c' => ['Asignar Repartidor', 'Gestión de Repartidores'],
        's' => ['Ver repartidores', 'Repartidores de mi sucursal'],
        'r' => ['Mis recorridos', 'Mis pedidos asignados'],
        'cs' => ['Gestión CRM y Sucursal', 'CRM + Sucursal'],
        'cr' => ['Gestión CRM y Repartos', 'CRM + Repartidor'],
        'sr' => ['Gestión Sucursal y Repartos', 'Sucursal + Repartidor'],
        'csr' => ['Control total', 'CRM + Sucursal + Repartidor'],
    ];

    $clave = ($esCRM ? 'c' : '') . ($esSucursal ? 's' : '') . ($esRepartidor ? 'r' : '');
    [$titulo, $subtitulo] = $titulos[$clave] ?? $titulos[0];
@endphp

@section('title', $titulo)
@section('page-title', $titulo . ' - ' . $subtitulo)

@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> {{ $titulo }}
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

                <!-- REPARTIDORES DISPONIBLES -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-truck"></i> Repartidores disponibles
                        </h6>
                        <span class="badge rounded-pill bg-primary" id="contadorRepartidores">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaRepartidores">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 py-3 small fw-bold" style="width: 5%">Seleccionar</th>
                                        <th class="ps-3 py-3 small fw-bold">Sucursal</th>
                                        <th class="ps-3 py-3 small fw-bold">Repartidor</th>
                                        <th class="ps-3 py-3 small fw-bold">Horario</th>
                                        <th class="ps-3 py-3 small fw-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="repartidoresBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                            <span class="ms-2">Cargando...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PEDIDOS PENDIENTES POR ASIGNAR (CRM) -->
                @if($esCRM)
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-list-check"></i> Pedidos pendientes por asignar
                            </h6>
                            <span class="badge rounded-pill bg-warning text-dark" id="contadorPedidosCRM">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="tablaPedidosCRM">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3 py-3 small fw-bold" style="width: 5%">
                                                <input type="checkbox" id="seleccionarTodosPedidosCRM"
                                                    title="Seleccionar todos">
                                            </th>
                                            <th class="ps-3 py-3 small fw-bold">Folio Pedido</th>
                                            <th class="ps-3 py-3 small fw-bold">Folio Ticket</th>
                                            <th class="ps-3 py-3 small fw-bold">Cliente</th>
                                            <th class="ps-3 py-3 small fw-bold">Dirección</th>
                                            <th class="ps-3 py-3 small fw-bold">Importe</th>
                                            <th class="ps-3 py-3 small fw-bold">Sucursal(es)</th>
                                            <th class="ps-3 py-3 small fw-bold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pedidosCRMBody">
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2">Cargando...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- MIS PEDIDOS PENDIENTES (REPARTIDOR) -->
                @if($esRepartidor)
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-list-check"></i> Mis pedidos pendientes
                            </h6>
                            <span class="badge rounded-pill bg-success" id="contadorPedidosPendientes">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="tablaPedidosPendientes">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3 py-3 small fw-bold" style="width: 5%">
                                                <input type="checkbox" id="seleccionarTodosPedidos" title="Seleccionar todos">
                                            </th>
                                            <th class="ps-3 py-3 small fw-bold">Folio Pedido</th>
                                            <th class="ps-3 py-3 small fw-bold">Folio Ticket</th>
                                            <th class="ps-3 py-3 small fw-bold">Cliente</th>
                                            <th class="ps-3 py-3 small fw-bold">Dirección</th>
                                            <th class="ps-3 py-3 small fw-bold">Importe</th>
                                            <th class="ps-3 py-3 small fw-bold">Sucursal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pedidosPendientesBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2">Cargando...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- ENTREGAS EN CURSO -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-clock-history"></i> Entregas en curso
                        </h6>
                        <span class="badge rounded-pill bg-info text-dark" id="contadorEntregas">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 py-3 small fw-bold" style="width: 5%">
                                            @if($esRepartidor && !$modoSoloLectura)
                                                <input type="checkbox" id="seleccionarTodosRecorridos"
                                                    title="Seleccionar todos">
                                            @else
                                                <span class="text-muted">Seleccionar</span>
                                            @endif
                                        </th>
                                        <th class="ps-3 py-3 small fw-bold">Repartidor</th>
                                        <th class="ps-3 py-3 small fw-bold">Folio Ticket</th>
                                        <th class="ps-3 py-3 small fw-bold">Cliente</th>
                                        <th class="ps-3 py-3 small fw-bold">Dirección</th>
                                        <th class="ps-3 py-3 small fw-bold">Hora salida</th>
                                        <th class="ps-3 py-3 small fw-bold">Tiempo fuera</th>
                                    </tr>
                                </thead>
                                <tbody id="entregasBody">
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                            <span class="ms-2">Cargando...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
                    @if($esCRM)
                        <button type="button" class="btn btn-primary" id="btnAsignar" disabled>
                            <i class="bi bi-person-badge"></i> Asignar repartidor a pedidos seleccionados
                        </button>
                    @endif

                    @if($esRepartidor)
                        @if($puedeIniciarRecorrido)
                            <button type="button" class="btn btn-success" id="btnIniciarRecorrido" disabled>
                                <i class="bi bi-play-circle"></i> Iniciar recorrido
                            </button>
                            <button type="button" class="btn btn-warning" id="btnFinalizarRecorrido" disabled>
                                <i class="bi bi-stop-circle"></i> Finalizar recorrido(s) seleccionado(s)
                            </button>
                        @else
                            <div class="alert alert-info mb-0 py-2 small">
                                <i class="bi bi-info-circle"></i> No tienes permiso para iniciar recorridos. Solo puedes ver tus
                                pedidos asignados.
                            </div>
                        @endif
                    @endif

                    @if($esSucursal && !$esCRM && !$esRepartidor)
                        <div class="alert alert-info mb-0 py-2 small">
                            <i class="bi bi-info-circle"></i> Solo lectura. Puedes ver los repartidores y pedidos de tu
                            sucursal.
                        </div>
                    @endif

                    @if(!$esCRM && !$esSucursal && !$esRepartidor)
                        <div class="alert alert-warning mb-0 py-2 small">
                            <i class="bi bi-exclamation-triangle"></i> No tienes permisos para gestionar repartidores.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL INICIAR RECORRIDO -->
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
                                        <tr>
                                            <td colspan="6" class="text-center py-3">Selecciona pedidos para iniciar el
                                                recorrido</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Kilometraje inicial <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" id="recorrido_kminicial"
                                    placeholder="Km inicial" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Hora de salida</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm" id="recorrido_hora_salida"
                                        readonly
                                        style="background-color: #e9ecef; font-family: monospace; font-size: 1rem; font-weight: bold;"
                                        value="--:--:--">
                                    <span class="input-group-text bg-info text-white"><i
                                            class="bi bi-clock-history"></i></span>
                                </div>
                                <small class="text-muted">Hora actual en tiempo real - Se registrará al iniciar</small>
                                <div class="alert alert-warning py-1 px-2 mb-0 small">
                                    <i class="bi bi-info-circle"></i> La hora se toma al momento de hacer clic en "Iniciar
                                    Recorrido"
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
    <!-- MODAL FINALIZAR RECORRIDO -->
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
                            <strong>Hora actual:</strong> <span id="finalizar_hora_regreso"
                                style="font-family: monospace; font-size: 1.1rem;">--:--:--</span>
                            <br>
                            <small>La hora se registrará automáticamente al confirmar</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="confirmarFinalizarRecorridoMultiple()">Finalizar
                        Recorrido(s)</button>
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
        let esCRM = {{ $esCRM ? 'true' : 'false' }};
        let esSucursal = {{ $esSucursal ? 'true' : 'false' }};
        let sucursalAsignada = {{ $sucursalAsignada }};
        let pedidosSeleccionados = [];
        let recorridosSeleccionados = [];
        let pedidosCRMSeleccionados = [];
        let intervaloHoraInicio = null;
        let intervaloHoraFinal = null;
        let modoSoloLectura = {{ $modoSoloLectura ? 'true' : 'false' }};

        const puedeAsignarRepartidor = esCRM;
        const puedeIniciarRecorrido = esRepartidor;
        const puedeVerRepartidores = esCRM || esSucursal || esRepartidor;
        const puedeVerPedidosCRM = esCRM;
        const puedeVerMisPedidos = esRepartidor;

        // Mapeo de sucursales
        const sucursalesMap = {};
        @foreach($sucursales as $sucursal)
            sucursalesMap[{{ $sucursal->id_sucursal }}] = '{{ $sucursal->nombre }}';
        @endforeach
        sucursalesMap[0] = 'CRM';

        // Ocultar botones según permisos
        document.addEventListener('DOMContentLoaded', function () {
            if (!puedeAsignarRepartidor) {
                const btn = document.getElementById('btnAsignar');
                if (btn) btn.style.display = 'none';
            }
            if (!puedeIniciarRecorrido) {
                const btnIniciar = document.getElementById('btnIniciarRecorrido');
                const btnFinalizar = document.getElementById('btnFinalizarRecorrido');
                if (btnIniciar) btnIniciar.style.display = 'none';
                if (btnFinalizar) btnFinalizar.style.display = 'none';
            }
        });

        // ============================================
        // CARGA DE DATOS
        // ============================================
        let cargandoDatos = false;

        async function cargarDatos() {
            if (cargandoDatos) return;
            cargandoDatos = true;

            const timeoutSeguridad = setTimeout(() => {
                if (cargandoDatos) {
                    cargandoDatos = false;
                    if (window.mostrarToast) {
                        window.mostrarToast('La carga está tomando más tiempo de lo esperado.', 'warning');
                    }
                }
            }, 15000);

            try {
                if (puedeVerRepartidores) {
                    const res1 = await fetch('{{ route("ventas.pedidos.repartidores.status", $pedido->id_pedido) }}', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!res1.ok) throw new Error(`HTTP error! status: ${res1.status}`);
                    const data1 = await res1.json();

                    if (data1.success) {
                        puedeAsignar = (esCRM && !esRepartidor);
                        actualizarTablaRepartidores(data1.repartidores);
                        actualizarTablaEntregas(data1.entregas_curso);
                        const btnAsignar = document.getElementById('btnAsignar');
                        if (btnAsignar) btnAsignar.disabled = !puedeAsignar;
                    }
                } else {
                    const repartidoresBody = document.getElementById('repartidoresBody');
                    if (repartidoresBody) {
                        repartidoresBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No tienes permisos para ver repartidores</td></tr>';
                    }
                    const entregasBody = document.getElementById('entregasBody');
                    if (entregasBody) {
                        entregasBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No tienes permisos para ver entregas</td></tr>';
                    }
                }

                if (puedeVerPedidosCRM) {
                    const res2 = await fetch('{{ route("ventas.pedidos.pendientes.crm") }}', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!res2.ok) throw new Error(`HTTP error! status: ${res2.status}`);
                    const data2 = await res2.json();
                    if (data2.success) actualizarTablaPedidosCRM(data2.pedidos);
                }

                if (puedeVerMisPedidos) {
                    const res3 = await fetch('{{ route("ventas.pedidos.pendientes.repartidor") }}', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!res3.ok) throw new Error(`HTTP error! status: ${res3.status}`);
                    const data3 = await res3.json();
                    if (data3.success) actualizarTablaPedidosPendientes(data3.pedidos);
                }
            } catch (error) {
                console.error('Error en carga de datos:', error);
                if (window.mostrarToast) {
                    window.mostrarToast('Error al cargar datos. Recarga la página.', 'danger');
                }
            } finally {
                clearTimeout(timeoutSeguridad);
                cargandoDatos = false;
            }
        }

        // ============================================
        // TABLA REPARTIDORES
        // ============================================
        function actualizarTablaRepartidores(repartidores) {
            const tbody = document.getElementById('repartidoresBody');
            const contador = document.getElementById('contadorRepartidores');

            if (!repartidores || repartidores.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No hay repartidores disponibles</td></tr>';
                if (contador) contador.textContent = '0';
                return;
            }

            const repartidoresFiltrados = (esSucursal && !esCRM)
                ? repartidores.filter(rep => rep.sucursal === sucursalAsignada)
                : repartidores;

            if (contador) contador.textContent = repartidoresFiltrados.length;

            if (repartidoresFiltrados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No hay repartidores disponibles para tu sucursal</td></tr>';
                return;
            }

            const statusMap = {
                'Disponible': { color: 'success', icon: 'bi-check-circle' },
                'En recorrido': { color: 'warning', icon: 'bi-truck' },
                'Fuera de horario': { color: 'secondary', icon: 'bi-clock' }
            };

            let html = '';
            repartidoresFiltrados.forEach(rep => {
                const status = statusMap[rep.status] || { color: 'danger', icon: 'bi-exclamation-circle' };
                const nombreSucursal = sucursalesMap[rep.sucursal] || `Sucursal ${rep.sucursal}`;

                let columnaSeleccion = '<span class="text-muted">---</span>';
                if (esCRM && rep.status === 'Disponible') {
                    columnaSeleccion = `<input type="radio" name="repartidor" value="${rep.id}" data-nombre="${rep.nombre}">`;
                }

                html += `
                    <tr>
                        <td class="ps-3 text-center">${columnaSeleccion}</td>
                        <td class="ps-3">${nombreSucursal}</td>
                        <td class="ps-3"><strong>${rep.nombre}</strong></td>
                        <td class="ps-3">${rep.horario_entrada ? rep.horario_entrada.substring(0, 5) : '--'} - ${rep.horario_salida ? rep.horario_salida.substring(0, 5) : '--'}</td>
                        <td class="ps-3"><span class="badge bg-${status.color} ${status.color === 'warning' ? 'text-dark' : 'text-white'}"><i class="bi ${status.icon}"></i> ${rep.status}</span></td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;

            if (esCRM) {
                document.querySelectorAll('input[name="repartidor"]').forEach(radio => {
                    radio.addEventListener('change', function () {
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
        // TABLA PEDIDOS CRM
        // ============================================
        function actualizarTablaPedidosCRM(pedidos) {
            const tbody = document.getElementById('pedidosCRMBody');
            const contador = document.getElementById('contadorPedidosCRM');
            if (!tbody) return;

            if (!esCRM) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No tienes permisos para ver pedidos pendientes</td></tr>';
                if (contador) contador.textContent = '0';
                return;
            }

            if (!pedidos || pedidos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No hay pedidos pendientes por asignar</td></tr>';
                if (contador) contador.textContent = '0';
                return;
            }

            if (contador) contador.textContent = pedidos.length;

            const puedeAsignarAhora = esCRM && !modoSoloLectura;
            let html = '';

            pedidos.forEach(pedido => {
                const sucursalesListas = pedido.sucursales_listas === true;
                const disponible = sucursalesListas && puedeAsignarAhora;

                const folioCompleto = pedido.folio_ticket || '';
                let folioMostrar = '';
                if (folioCompleto) {
                    const str = String(folioCompleto);
                    folioMostrar = `Caja ${str.charAt(0)}: ${str.substring(1)}`;
                }

                html += `<tr data-pedido-id="${pedido.id_pedido}">
                    <td class="ps-3 text-center">
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
                    <td class="ps-3"><span class="badge bg-primary">${pedido.folio_pedido}</span></td>
                    <td class="ps-3">${folioMostrar}</td>
                    <td class="ps-3">${pedido.nombrecliente}</td>
                    <td class="ps-3">${pedido.Domicilio}</td>
                    <td class="ps-3">$${Number(pedido.importeticket).toFixed(2)}</td>
                    <td class="ps-3">${sucursalesMap[pedido.sucursal] || 'CRM'}</td>
                    <td class="ps-3">
                        ${disponible
                        ? '<span class="badge bg-success">Sucursales listas</span>'
                        : '<span class="badge bg-warning text-dark">Esperando sucursales</span>'}
                    </td>
                </tr>`;
            });

            tbody.innerHTML = html;

            document.querySelectorAll('.checkbox-pedido-crm:not([disabled])').forEach(checkbox => {
                checkbox.addEventListener('change', actualizarPedidosCRMSeleccionados);
            });

            const selectAll = document.getElementById('seleccionarTodosPedidosCRM');
            if (selectAll) {
                const clone = selectAll.cloneNode(true);
                selectAll.parentNode.replaceChild(clone, selectAll);
                clone.addEventListener('change', function () {
                    document.querySelectorAll('.checkbox-pedido-crm:not([disabled])').forEach(cb => {
                        cb.checked = clone.checked;
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
        // TABLA MIS PEDIDOS PENDIENTES
        // ============================================
        function actualizarTablaPedidosPendientes(pedidos) {
            const tbody = document.getElementById('pedidosPendientesBody');
            const contador = document.getElementById('contadorPedidosPendientes');
            if (!tbody) return;

            if (!esRepartidor) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No tienes permisos para ver pedidos pendientes</td></tr>';
                if (contador) contador.textContent = '0';
                return;
            }

            if (!pedidos || pedidos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay pedidos pendientes</td></tr>';
                if (contador) contador.textContent = '0';
                const btnIniciar = document.getElementById('btnIniciarRecorrido');
                if (btnIniciar) btnIniciar.disabled = true;
                return;
            }

            if (contador) contador.textContent = pedidos.length;

            let html = '';
            pedidos.forEach(pedido => {
                const disponible = (pedido.sucursales_listas === true) && !modoSoloLectura;

                let sucursalPrincipal = pedido.sucursal || 0;
                if (pedido.sucursales && pedido.sucursales.length > 0) {
                    sucursalPrincipal = pedido.sucursales[0].id_sucursal || 0;
                }

                let sucursalesHtml = '';
                if (pedido.sucursales && pedido.sucursales.length > 0) {
                    sucursalesHtml = pedido.sucursales.map(s => s.nombre || 'Sin nombre').join(', ');
                } else if (pedido.sucursal) {
                    sucursalesHtml = sucursalesMap[pedido.sucursal] || 'CRM';
                } else {
                    sucursalesHtml = 'Sin sucursal asignada';
                }

                const folioCompleto = pedido.folio_ticket || '';
                let folioMostrar = '';
                if (folioCompleto) {
                    const str = String(folioCompleto);
                    folioMostrar = `Caja ${str.charAt(0)}: ${str.substring(1)}`;
                }

                html += `<tr data-pedido-id="${pedido.id_pedido}">
                    <td class="ps-3 text-center">
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
                    <td class="ps-3">${pedido.folio_pedido || ''}</td>
                    <td class="ps-3">${folioMostrar}</td>
                    <td class="ps-3">${pedido.nombrecliente || 'N/A'}</td>
                    <td class="ps-3">${pedido.Domicilio || 'N/A'}</td>
                    <td class="ps-3">$${Number(pedido.importeticket || 0).toFixed(2)}</td>
                    <td class="ps-3">${sucursalesHtml}</td>
                </tr>`;
            });
            tbody.innerHTML = html;

            document.querySelectorAll('.checkbox-pedido:not([disabled])').forEach(checkbox => {
                checkbox.addEventListener('change', actualizarPedidosSeleccionados);
            });

            const selectAll = document.getElementById('seleccionarTodosPedidos');
            if (selectAll) {
                const clone = selectAll.cloneNode(true);
                selectAll.parentNode.replaceChild(clone, selectAll);
                clone.addEventListener('change', function () {
                    document.querySelectorAll('.checkbox-pedido:not([disabled])').forEach(cb => {
                        cb.checked = clone.checked;
                    });
                    actualizarPedidosSeleccionados();
                });
            }
        }

        function actualizarPedidosSeleccionados() {
            pedidosSeleccionados = [];
            document.querySelectorAll('.checkbox-pedido:checked').forEach(checkbox => {
                let sucursal = parseInt(checkbox.dataset.sucursal) || 0;
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
            if (btnIniciar) btnIniciar.disabled = pedidosSeleccionados.length === 0;
        }

        // ============================================
        // TABLA ENTREGAS EN CURSO
        // ============================================
        function actualizarTablaEntregas(entregas) {
            const tbody = document.getElementById('entregasBody');
            const contador = document.getElementById('contadorEntregas');

            if (!entregas || entregas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay entregas en curso</td></tr>';
                if (contador) contador.textContent = '0';
                const btnFinalizar = document.getElementById('btnFinalizarRecorrido');
                if (btnFinalizar) btnFinalizar.disabled = true;
                return;
            }

            const entregasFiltradas = (esSucursal && !esCRM)
                ? entregas.filter(e => e.sucursal === sucursalAsignada)
                : entregas;

            if (contador) contador.textContent = entregasFiltradas.length;

            if (entregasFiltradas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay entregas en curso para tu perfil</td></tr>';
                return;
            }

            let html = '';
            entregasFiltradas.forEach(entrega => {
                const horaSalida = entrega.hora_salida || '';
                const checkedAttr = recorridosSeleccionados.includes(entrega.id) ? 'checked' : '';
                const folioMostrar = entrega.folio_ticket || '';
                const checkboxHabilitado = esRepartidor && !modoSoloLectura;

                html += `<tr data-recibido-id="${entrega.id}">
                    <td class="ps-3 text-center">
                        <input type="checkbox" class="checkbox-recorrido"
                               value="${entrega.id}"
                               ${checkedAttr}
                               ${!checkboxHabilitado ? 'disabled' : ''}>
                    </td>
                    <td class="ps-3"><strong>${entrega.repartidor_nombre} ${entrega.repartidor_apaterno || ''}</strong></td>
                    <td class="ps-3">${folioMostrar}</td>
                    <td class="ps-3">${entrega.nombrecliente || 'N/A'}</td>
                    <td class="ps-3">${entrega.Domicilio || 'N/A'}</td>
                    <td class="ps-3">${horaSalida ? horaSalida.substring(0, 5) : 'N/A'}</td>
                    <td class="ps-3"><span class="badge bg-info text-dark tiempo-fuera" data-inicio="${horaSalida}">00:00:00</span></td>
                </tr>`;
            });
            tbody.innerHTML = html;

            if (esRepartidor && !modoSoloLectura) {
                document.querySelectorAll('.checkbox-recorrido:not([disabled])').forEach(checkbox => {
                    checkbox.addEventListener('change', actualizarRecorridosSeleccionados);
                });

                const selectAllRecorridos = document.getElementById('seleccionarTodosRecorridos');
                if (selectAllRecorridos) {
                    const clone = selectAllRecorridos.cloneNode(true);
                    selectAllRecorridos.parentNode.replaceChild(clone, selectAllRecorridos);
                    clone.addEventListener('change', function () {
                        document.querySelectorAll('.checkbox-recorrido:not([disabled])').forEach(cb => {
                            cb.checked = clone.checked;
                        });
                        actualizarRecorridosSeleccionados();
                    });
                }
            }

            actualizarTiemposFuera();
        }

        function actualizarRecorridosSeleccionados() {
            recorridosSeleccionados = [];
            document.querySelectorAll('.checkbox-recorrido:checked').forEach(checkbox => {
                recorridosSeleccionados.push(parseInt(checkbox.value));
            });
            const btnFinalizar = document.getElementById('btnFinalizarRecorrido');
            if (btnFinalizar) btnFinalizar.disabled = recorridosSeleccionados.length === 0;
        }

        function actualizarTiemposFuera() {
            document.querySelectorAll('.tiempo-fuera').forEach(el => {
                const horaInicioStr = el.getAttribute('data-inicio');
                if (!horaInicioStr) return;
                const partes = horaInicioStr.split(':');
                if (partes.length < 2) return;
                const ahora = new Date();
                const inicio = new Date(ahora);
                inicio.setHours(parseInt(partes[0]), parseInt(partes[1]), partes[2] ? parseInt(partes[2]) : 0, 0);
                const diffMs = Math.max(0, ahora - inicio);
                const horas = Math.floor(diffMs / 3600000);
                const minutos = Math.floor((diffMs % 3600000) / 60000);
                const segundos = Math.floor((diffMs % 60000) / 1000);
                el.textContent = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
            });
        }

        setInterval(actualizarTiemposFuera, 1000);

        // ============================================
        // ASIGNAR REPARTIDOR (CRM)
        // ============================================
        let asignandoEnProgreso = false;

        const btnAsignar = document.getElementById('btnAsignar');
        if (btnAsignar) {
            btnAsignar.addEventListener('click', function () {
                if (asignandoEnProgreso) return;

                if (!repartidorSeleccionadoId) {
                    if (window.mostrarToast) window.mostrarToast('Selecciona un repartidor', 'warning');
                    return;
                }
                if (pedidosCRMSeleccionados.length === 0) {
                    if (window.mostrarToast) window.mostrarToast('Selecciona al menos un pedido', 'warning');
                    return;
                }

                asignandoEnProgreso = true;
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i> Asignando...';

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
                        if (window.mostrarToast) window.mostrarToast(data.message, data.success ? 'success' : 'danger');
                        if (data.success) setTimeout(() => window.location.reload(), 1500);
                    })
                    .catch(error => {
                        console.error('Error al asignar repartidor:', error);
                        if (window.mostrarToast) window.mostrarToast(error.message || 'Error de conexión', 'danger');
                    })
                    .finally(() => {
                        setTimeout(() => {
                            asignandoEnProgreso = false;
                            btnAsignar.disabled = false;
                            btnAsignar.innerHTML = originalText;
                        }, 500);
                    });
            });
        }

        // ============================================
        // INICIAR RECORRIDO
        // ============================================
        function abrirModalIniciarRecorrido() {
            if (pedidosSeleccionados.length === 0) {
                if (window.mostrarToast) window.mostrarToast('Selecciona al menos un pedido', 'warning');
                return;
            }

            document.getElementById('totalPedidosSeleccionados').innerText = pedidosSeleccionados.length;

            let html = '';
            pedidosSeleccionados.forEach((pedido, index) => {
                const sucursalNombre = sucursalesMap[pedido.sucursal] || 'Sin sucursal';
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
                            <input type="text" class="form-control form-control-sm" value="${sucursalNombre}" readonly disabled>
                            <input type="hidden" class="campo-sucursal-id" value="${pedido.sucursal || 0}">
                        </td>
                    </tr>
                `;
            });
            document.getElementById('listaPedidosRecorrido').innerHTML = html;

            document.getElementById('recorrido_kminicial').value = '';

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

        let iniciandoRecorrido = false;

        function iniciarRecorridoMultiple() {
            if (iniciandoRecorrido) return;

            const kmInicial = document.getElementById('recorrido_kminicial').value;
            const ahora = new Date();
            const horaSalida = ahora.toLocaleTimeString('es-MX', { hour12: false });

            if (!kmInicial) {
                if (window.mostrarToast) window.mostrarToast('Kilometraje inicial obligatorio', 'warning');
                return;
            }

            const pedidosActualizados = [];
            const filas = document.querySelectorAll('#listaPedidosRecorrido tr');
            let hayError = false;

            for (let i = 0; i < filas.length; i++) {
                const fila = filas[i];
                const pedidoOriginal = pedidosSeleccionados[i];

                const folioTicket = fila.querySelector('.campo-folio-ticket')?.value || '';
                const nombreCliente = fila.querySelector('.campo-cliente')?.value || '';
                const domicilio = fila.querySelector('.campo-direccion')?.value || '';
                const importe = fila.querySelector('.campo-importe')?.value || '';
                const sucursalHidden = fila.querySelector('.campo-sucursal-id');

                let sucursal = pedidoOriginal.sucursal || 0;
                if (sucursalHidden) sucursal = parseInt(sucursalHidden.value) || sucursal;

                if (!folioTicket) {
                    if (window.mostrarToast) window.mostrarToast(`Folio ticket es obligatorio para pedido ${i + 1}`, 'warning');
                    hayError = true;
                    break;
                }

                const folioTicketNum = parseInt(folioTicket, 10);
                if (isNaN(folioTicketNum) || folioTicketNum < 0) {
                    if (window.mostrarToast) window.mostrarToast(`Folio ticket debe ser un número válido para pedido ${i + 1}`, 'warning');
                    hayError = true;
                    break;
                }

                if (!nombreCliente) {
                    if (window.mostrarToast) window.mostrarToast(`Nombre de cliente obligatorio para pedido ${i + 1}`, 'warning');
                    hayError = true;
                    break;
                }

                if (!domicilio) {
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

            if (pedidosActualizados.length === 0) {
                if (window.mostrarToast) window.mostrarToast('No hay pedidos válidos para iniciar el recorrido', 'warning');
                return;
            }

            iniciandoRecorrido = true;
            const btn = document.querySelector('#modalIniciarRecorrido .btn-success');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

            const timeoutSeguridad = setTimeout(() => {
                if (iniciandoRecorrido) {
                    iniciandoRecorrido = false;
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (window.mostrarToast) window.mostrarToast('La operación está tomando más tiempo de lo esperado.', 'warning');
                }
            }, 30000);

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
                    if (window.mostrarToast) window.mostrarToast(data.message, data.success ? 'success' : 'danger');
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalIniciarRecorrido'));
                        if (modal) modal.hide();
                        setTimeout(() => window.location.reload(), 1000);
                    }
                })
                .catch(error => {
                    console.error('Error al iniciar recorrido:', error);
                    if (window.mostrarToast) window.mostrarToast(error.message || 'Error de conexión', 'danger');
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
        // FINALIZAR RECORRIDO
        // ============================================
        function abrirModalFinalizarRecorrido() {
            if (recorridosSeleccionados.length === 0) {
                if (window.mostrarToast) window.mostrarToast('Selecciona al menos un recorrido', 'warning');
                return;
            }

            document.getElementById('totalRecorridosSeleccionados').innerText = recorridosSeleccionados.length;
            document.getElementById('finalizar_kmfinal').value = '';

            if (intervaloHoraFinal) clearInterval(intervaloHoraFinal);
            actualizarHoraFinal();
            intervaloHoraFinal = setInterval(actualizarHoraFinal, 1000);

            new bootstrap.Modal(document.getElementById('modalFinalizarRecorrido')).show();
        }

        function actualizarHoraFinal() {
            const ahora = new Date();
            const horaActual = ahora.toLocaleTimeString('es-MX', { hour12: false });
            const horaElement = document.getElementById('finalizar_hora_regreso');
            if (horaElement) horaElement.textContent = horaActual;
        }

        let finalizandoRecorrido = false;

        function confirmarFinalizarRecorridoMultiple() {
            if (finalizandoRecorrido) return;

            const kmFinal = document.getElementById('finalizar_kmfinal').value;
            const ahora = new Date();
            const horaRegreso = ahora.toLocaleTimeString('es-MX', { hour12: false });

            if (!kmFinal) {
                if (window.mostrarToast) window.mostrarToast('Kilometraje final obligatorio', 'warning');
                return;
            }
            if (recorridosSeleccionados.length === 0) {
                if (window.mostrarToast) window.mostrarToast('No hay recorridos seleccionados', 'warning');
                return;
            }

            finalizandoRecorrido = true;
            const btn = document.querySelector('#modalFinalizarRecorrido .btn-warning');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

            const timeoutSeguridad = setTimeout(() => {
                if (finalizandoRecorrido) {
                    finalizandoRecorrido = false;
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (window.mostrarToast) window.mostrarToast('La operación está tomando más tiempo de lo esperado.', 'warning');
                }
            }, 30000);

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
                    if (window.mostrarToast) window.mostrarToast(data.message, data.success ? 'success' : 'danger');
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalFinalizarRecorrido'));
                        if (modal) modal.hide();
                        setTimeout(() => window.location.reload(), 1000);
                    }
                })
                .catch(error => {
                    console.error('Error al finalizar recorrido:', error);
                    if (window.mostrarToast) window.mostrarToast(error.message || 'Error de conexión', 'danger');
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
        // POLLING PARA ACTUALIZAR ASIGNACIÓN
        // ============================================
        let pollingAsignacionInterval = null;
        let ultimoIdRepartidor = 0;
        let ultimoIdEntrega = 0;
        let ultimoIdPedido = 0;
        let refrescandoAsignacion = false;

        function refrescarAsignacion(mostrarNotificacion = false, desdePolling = false) {
            const isManual = mostrarNotificacion && !desdePolling;

            if (refrescandoAsignacion) return;
            refrescandoAsignacion = true;

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
                    if (data.success && data.hay_cambios) {
                        if (data.repartidores) {
                            actualizarTablaRepartidores(data.repartidores);
                            ultimoIdRepartidor = data.ultimo_id_repartidor || 0;
                        }
                        if (data.entregas_curso) {
                            actualizarTablaEntregas(data.entregas_curso);
                            ultimoIdEntrega = data.ultimo_id_entrega || 0;
                        }
                        if (data.pedidos_pendientes) {
                            actualizarTablaPedidosPendientes(data.pedidos_pendientes);
                            ultimoIdPedido = data.ultimo_id_pedido || 0;
                        }
                        if (data.pedidos_crm) {
                            actualizarTablaPedidosCRM(data.pedidos_crm);
                            ultimoIdPedido = data.ultimo_id_pedido || 0;
                        }

                        if (isManual && window.mostrarToast) {
                            window.mostrarToast('Datos actualizados correctamente', 'success');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error refrescando asignación:', error);
                    if (isManual && window.mostrarToast) {
                        window.mostrarToast(error.message || 'Error al actualizar datos', 'danger');
                    }
                })
                .finally(() => {
                    refrescandoAsignacion = false;
                });
        }

        function iniciarPollingAsignacion() {
            if (pollingAsignacionInterval) clearInterval(pollingAsignacionInterval);
            pollingAsignacionInterval = setInterval(() => {
                if (!document.hidden) refrescarAsignacion(false, true);
            }, 30000);
        }

        // ============================================
        // INICIALIZACIÓN
        // ============================================
        setTimeout(cargarDatos, 100);
        setInterval(actualizarTiemposFuera, 1000);

        document.getElementById('modalIniciarRecorrido')?.addEventListener('hidden.bs.modal', function () {
            if (intervaloHoraInicio) clearInterval(intervaloHoraInicio);
        });

        document.getElementById('modalFinalizarRecorrido')?.addEventListener('hidden.bs.modal', function () {
            if (intervaloHoraFinal) clearInterval(intervaloHoraFinal);
        });

        document.getElementById('btnIniciarRecorrido')?.addEventListener('click', abrirModalIniciarRecorrido);
        document.getElementById('btnFinalizarRecorrido')?.addEventListener('click', abrirModalFinalizarRecorrido);

        window.addEventListener('beforeunload', () => {
            if (pollingAsignacionInterval) clearInterval(pollingAsignacionInterval);
            if (intervaloHoraInicio) clearInterval(intervaloHoraInicio);
            if (intervaloHoraFinal) clearInterval(intervaloHoraFinal);
        });

        // Botón Refrescar con spinner anti-flicker (300ms)
        document.addEventListener('DOMContentLoaded', function () {
            const btnRefrescar = document.getElementById('btnRefrescarAsignacion');
            if (btnRefrescar) {
                btnRefrescar.addEventListener('click', function () {
                    const originalHtml = this.innerHTML;
                    const spinnerTimeout = setTimeout(() => {
                        btnRefrescar.innerHTML = '<i class="bi bi-arrow-repeat" style="display:inline-block; animation: spin 1s linear infinite;"></i> Actualizando...';
                        btnRefrescar.disabled = true;
                    }, 300);

                    const checkInterval = setInterval(() => {
                        if (!refrescandoAsignacion) {
                            clearInterval(checkInterval);
                            clearTimeout(spinnerTimeout);
                            btnRefrescar.disabled = false;
                            btnRefrescar.innerHTML = originalHtml;
                        }
                    }, 100);

                    refrescarAsignacion(true, false);
                });
            }
        });

        // Animación de spin
        const styleSpin = document.createElement('style');
        styleSpin.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
        document.head.appendChild(styleSpin);

        setTimeout(iniciarPollingAsignacion, 2000);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) refrescarAsignacion(false, true);
        });
    </script>
@endsection