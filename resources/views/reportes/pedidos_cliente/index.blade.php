@extends('layouts.app')

@section('page-title', 'Pedidos por Cliente')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-clipboard2-check"></i> Pedidos por Cliente
            </h3>
            <div class="btn-group" id="botonesExportacion" style="display: none;">
                <button type="button" class="btn btn-success btn-sm" onclick="exportarReporte('excel')">
                    <i class="bi bi-filetype-xls"></i> Excel
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="exportarReporte('pdf')">
                    <i class="bi bi-filetype-pdf"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row">
                <div class="col-md-3">
                    <label>Top <span class="text-danger">*</span></label>
                    <select class="form-control" id="topSelect">
                        <option value="">-- Seleccione --</option>
                        <option value="10">Top 10</option>
                        <option value="25">Top 25</option>
                        <option value="50">Top 50</option>
                        <option value="100">Top 100</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Ordenar por <span class="text-danger">*</span></label>
                    <select class="form-control" id="sortBySelect">
                        <option value="">-- Seleccione --</option>
                        <option value="monto_promedio">Mayor Promedio</option>
                        <option value="monto_promedio_asc">Menor Promedio</option>
                        <option value="total_pedidos">Más Pedidos</option>
                        <option value="total_pedidos_asc">Menos Pedidos</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Buscar cliente (opcional)</label>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="buscarClienteReporte" 
                            placeholder="Escriba al menos 3 caracteres..."
                            autocomplete="off">
                    </div>
                    <div id="resultadosClientes" class="mt-2" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Clientes encontrados</small>
                            </div>
                            <div class="list-group list-group-flush" id="listaClientes"></div>
                        </div>
                    </div>
                    <!-- CAMPO OCULTO PARA GUARDAR EL ID DEL CLIENTE -->
                    <input type="hidden" id="cliente_id" value="">
                    
                    <div id="clienteSeleccionado" style="display: none;" class="mt-2">
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                            <div id="clienteInfo">
                                <strong>Cliente seleccionado:</strong> 
                                <span id="clienteNombre"></span>
                            </div>
                            <button type="button" class="btn-close" onclick="limpiarCliente()"></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros de Fecha -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h5 class="card-title">Filtros de Fecha <span class="text-danger">*</span></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Rápido:</label>
                                    <select class="form-control" id="filtroFecha">
                                        <option value="">-- Seleccione --</option>
                                        <option value="hoy">Hoy</option>
                                        <option value="esta_semana">Esta semana</option>
                                        <option value="este_mes">Este mes</option>
                                        <option value="este_ano">Este año</option>
                                        <option value="personalizado">Personalizado</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="fechaInicioDiv" style="display: none;">
                                    <label>Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="fechaInicio">
                                </div>
                                <div class="col-md-3" id="fechaFinDiv" style="display: none;">
                                    <label>Fecha Fin:</label>
                                    <input type="date" class="form-control" id="fechaFin">
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary flex-grow-1" id="btnAplicarFiltros">
                                            <i class="bi bi-funnel"></i> Aplicar
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                                            <i class="bi bi-eraser"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="loadingIndicator" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando datos...</p>
            </div>

            <!-- Resultados -->
            <div id="resultadosContainer">
                <div class="alert alert-secondary text-center">
                    <i class="bi bi-funnel"></i> 
                    Seleccione los filtros (Top, Ordenar y Fecha) y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let clienteSeleccionadoId = null;
    let clienteSeleccionadoNombre = null;
    let timeoutBusquedaCliente = null;

    function formatearFechaLocal(fecha) {
        const año = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${año}-${mes}-${dia}`;
    }

    // ============================================
    // CARGAR FILTROS DESDE URL
    // ============================================
    function cargarFiltrosDesdeURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('top')) {
            const el = document.getElementById('topSelect');
            if (el) el.value = urlParams.get('top');
        }
        if (urlParams.has('sort_by')) {
            const el = document.getElementById('sortBySelect');
            if (el) el.value = urlParams.get('sort_by');
        }
        if (urlParams.has('filtro_fecha')) {
            const filtroFecha = urlParams.get('filtro_fecha');
            const el = document.getElementById('filtroFecha');
            if (el) el.value = filtroFecha;
            
            if (filtroFecha === 'personalizado') {
                const fechaInicioDiv = document.getElementById('fechaInicioDiv');
                const fechaFinDiv = document.getElementById('fechaFinDiv');
                if (fechaInicioDiv) fechaInicioDiv.style.display = 'block';
                if (fechaFinDiv) fechaFinDiv.style.display = 'block';
                
                // Si es personalizado, cargar fechas de la URL
                if (urlParams.has('fecha_inicio')) {
                    const elInicio = document.getElementById('fechaInicio');
                    if (elInicio) elInicio.value = urlParams.get('fecha_inicio');
                }
                if (urlParams.has('fecha_fin')) {
                    const elFin = document.getElementById('fechaFin');
                    if (elFin) elFin.value = urlParams.get('fecha_fin');
                }
            } else if (filtroFecha && filtroFecha !== '') {
                // Si no es personalizado, calcular fechas usando getFechasByFiltro
                const fechas = getFechasByFiltro(filtroFecha);
                if (fechas) {
                    const elInicio = document.getElementById('fechaInicio');
                    const elFin = document.getElementById('fechaFin');
                    if (elInicio) elInicio.value = fechas.inicio;
                    if (elFin) elFin.value = fechas.fin;
                }
            }
        } else {
            // Si no hay filtro_fecha en la URL, usar 'este_mes' por defecto
            const filtroFecha = 'este_mes';
            const el = document.getElementById('filtroFecha');
            if (el) el.value = filtroFecha;
            
            const fechas = getFechasByFiltro(filtroFecha);
            if (fechas) {
                const elInicio = document.getElementById('fechaInicio');
                const elFin = document.getElementById('fechaFin');
                if (elInicio) elInicio.value = fechas.inicio;
                if (elFin) elFin.value = fechas.fin;
            }
        }
        
        // Cargar cliente desde URL
        if (urlParams.has('search_cliente')) {
            const clienteId = urlParams.get('search_cliente');
            const clienteIdInput = document.getElementById('cliente_id');
            if (clienteIdInput) {
                clienteIdInput.value = clienteId;
            }
            // Cargar nombre del cliente si existe
            if (clienteId) {
                fetch(`/clientes/${clienteId}/edit`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const nombreCompleto = `${data.data.Nombre} ${data.data.apPaterno} ${data.data.apMaterno || ''}`.trim();
                        document.getElementById('clienteNombre').innerHTML = nombreCompleto;
                        document.getElementById('buscarClienteReporte').value = nombreCompleto;
                        document.getElementById('clienteSeleccionado').style.display = 'block';
                    }
                })
                .catch(error => console.error('Error al cargar cliente:', error));
            }
        }
    }
  
    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        cargarFiltrosDesdeURL();
        
        // Si hay parámetros en la URL, cargar datos automáticamente
        if (window.location.search.length > 0) {
            const topSelect = document.getElementById('topSelect');
            const filtroFecha = document.getElementById('filtroFecha');
            if (topSelect && topSelect.value && filtroFecha && filtroFecha.value) {
                setTimeout(() => {
                    const clienteIdInput = document.getElementById('cliente_id');
                    const clienteId = clienteIdInput ? clienteIdInput.value : null;
                    
                    // Obtener fechas de los inputs (ya están cargados por cargarFiltrosDesdeURL)
                    const fechaInicioEl = document.getElementById('fechaInicio');
                    const fechaFinEl = document.getElementById('fechaFin');
                    const fechaInicio = fechaInicioEl ? fechaInicioEl.value : '';
                    const fechaFin = fechaFinEl ? fechaFinEl.value : '';
                    
                    let url = `{{ route('reportes.pedidos-cliente.data') }}?top=${topSelect.value}&sort_by=${document.getElementById('sortBySelect').value}&filtro_fecha=${filtroFecha.value}`;
                    
                    if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
                    if (fechaFin) url += `&fecha_fin=${fechaFin}`;
                    if (clienteId && clienteId !== '' && clienteId !== 'null' && clienteId !== 'undefined') {
                        url += `&cliente_id=${clienteId}`;
                    }
                    
                    cargarDatos(url);
                }, 300);
            }
        }
    });

    // ============================================
    // BUSCAR CLIENTES
    // ============================================
    const buscarInput = document.getElementById('buscarClienteReporte');
    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            const termino = this.value.trim();
            const resultadosDiv = document.getElementById('resultadosClientes');
            const listaClientes = document.getElementById('listaClientes');
            
            if (termino.length < 3) {
                if (resultadosDiv) resultadosDiv.style.display = 'none';
                return;
            }
            
            clearTimeout(timeoutBusquedaCliente);
            timeoutBusquedaCliente = setTimeout(() => {
                fetch(`{{ route('reportes.compras_cliente.buscar-clientes') }}?q=${encodeURIComponent(termino)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            listaClientes.innerHTML = data.data.map(cliente => {
                                const clienteId = cliente.id_Cliente || cliente.id || cliente.id_cliente || '';
                                return `
                                    <a href="#" class="list-group-item list-group-item-action" 
                                    onclick="seleccionarCliente(${clienteId}, '${cliente.nombre_completo.replace(/'/g, "\\'")}')">
                                        ${cliente.nombre_completo}
                                        <small class="text-muted d-block">${cliente.correo || ''}</small>
                                    </a>
                                `;
                            }).join('');
                            resultadosDiv.style.display = 'block';
                        } else {
                            listaClientes.innerHTML = `<div class="list-group-item text-muted">No se encontraron clientes</div>`;
                            resultadosDiv.style.display = 'block';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }, 300);
        });
    }

    // ============================================
    // SELECCIONAR CLIENTE (FUNCIÓN GLOBAL)
    // ============================================
    window.seleccionarCliente = function(id, nombre) {
        clienteSeleccionadoId = id;
        clienteSeleccionadoNombre = nombre;
        
        const clienteIdInput = document.getElementById('cliente_id');
        if (clienteIdInput) {
            clienteIdInput.value = id;
        }
        
        const clienteNombreSpan = document.getElementById('clienteNombre');
        if (clienteNombreSpan) clienteNombreSpan.textContent = nombre;
        
        const clienteSeleccionadoDiv = document.getElementById('clienteSeleccionado');
        if (clienteSeleccionadoDiv) clienteSeleccionadoDiv.style.display = 'block';
        
        const resultadosDiv = document.getElementById('resultadosClientes');
        if (resultadosDiv) resultadosDiv.style.display = 'none';
        
        const buscarInput = document.getElementById('buscarClienteReporte');
        if (buscarInput) buscarInput.value = '';
        
        if (window.mostrarToast) {
            window.mostrarToast(`Cliente "${nombre}" seleccionado. Aplique filtros para ver sus datos.`, 'success');
        }
    };

    window.limpiarCliente = function() {
        clienteSeleccionadoId = null;
        clienteSeleccionadoNombre = null;
        
        const clienteIdInput = document.getElementById('cliente_id');
        const clienteSeleccionadoDiv = document.getElementById('clienteSeleccionado');
        
        if (clienteIdInput) clienteIdInput.value = '';
        if (clienteSeleccionadoDiv) clienteSeleccionadoDiv.style.display = 'none';
    };

    // ============================================
    // FILTROS DE FECHA
    // ============================================
    // Función para obtener fecha inicio/fin según el filtro
    function getFechasByFiltro(filtro) {
        const hoy = new Date();
        let inicio, fin;
        
        switch(filtro) {
            case 'hoy':
                inicio = formatearFechaLocal(hoy);
                fin = formatearFechaLocal(hoy);
                break;
            case 'esta_semana':
                const dia = hoy.getDay();
                const diff = dia === 0 ? 6 : dia - 1;
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - diff);
                const finSemana = new Date(inicioSemana);
                finSemana.setDate(inicioSemana.getDate() + 6);
                inicio = formatearFechaLocal(inicioSemana);
                fin = formatearFechaLocal(finSemana);
                break;
            case 'este_mes':
                const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                inicio = formatearFechaLocal(inicioMes);
                fin = formatearFechaLocal(finMes);
                break;
            case 'este_ano':
                const inicioAno = new Date(hoy.getFullYear(), 0, 1);
                const finAno = new Date(hoy.getFullYear(), 11, 31);
                inicio = formatearFechaLocal(inicioAno);
                fin = formatearFechaLocal(finAno);
                break;
            default:
                return null;
        }
        
        return { inicio, fin };
    }

    // ============================================
    // APLICAR FILTROS
    // ============================================
    const btnAplicar = document.getElementById('btnAplicarFiltros');
    if (btnAplicar) {
        btnAplicar.addEventListener('click', function() {
            const top = document.getElementById('topSelect').value;
            const sortBy = document.getElementById('sortBySelect').value;
            const filtroFecha = document.getElementById('filtroFecha').value;
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            const clienteIdInput = document.getElementById('cliente_id');
            let clienteId = clienteIdInput ? clienteIdInput.value : null;
            
            if (!top || !sortBy || !filtroFecha) {
                if (window.mostrarToast) {
                    window.mostrarToast('Seleccione Top, Ordenar y Fecha', 'warning');
                }
                return;
            }
            
            if (filtroFecha === 'personalizado' && (!fechaInicio || !fechaFin)) {
                if (window.mostrarToast) {
                    window.mostrarToast('Seleccione fechas de inicio y fin', 'warning');
                }
                return;
            }
            
            let url = `{{ route('reportes.pedidos-cliente.data') }}?top=${top}&sort_by=${sortBy}&filtro_fecha=${filtroFecha}`;
            if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
            if (fechaFin) url += `&fecha_fin=${fechaFin}`;
            
            if (clienteId && clienteId !== '' && clienteId !== 'null' && clienteId !== 'undefined') {
                const clienteIdNum = parseInt(clienteId);
                if (!isNaN(clienteIdNum) && clienteIdNum > 0) {
                    url += `&cliente_id=${clienteIdNum}`;
                }
            }
            
            cargarDatos(url);
        });
    }

    // ============================================
    // CARGAR DATOS
    // ============================================
    function cargarDatos(url) {
        const loading = document.getElementById('loadingIndicator');
        const container = document.getElementById('resultadosContainer');
        const botonesExportacion = document.getElementById('botonesExportacion');
        
        if (loading) loading.style.display = 'block';
        if (container) container.innerHTML = '';
        if (botonesExportacion) botonesExportacion.style.display = 'none';
        
        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (loading) loading.style.display = 'none';
            
            if (data.success && data.data && data.data.length > 0) {
                // Pasar los filtros correctamente a mostrarResultados
                mostrarResultados(data);
                if (botonesExportacion) botonesExportacion.style.display = 'inline-flex';
            } else {
                // Mostrar mensaje con los filtros aunque no haya datos
                mostrarResultados(data);
                if (botonesExportacion) botonesExportacion.style.display = 'none';
            }
        })
        .catch(error => {
            if (loading) loading.style.display = 'none';
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Error al cargar los datos: ${error.message}
                    </div>
                `;
            }
            if (botonesExportacion) botonesExportacion.style.display = 'none';
            console.error('Error:', error);
        });
    }     

    // ============================================
    // MOSTRAR RESULTADOS
    // ============================================
    function mostrarResultados(data) {
        const clientes = data.data || [];
        const container = document.getElementById('resultadosContainer');
        if (!container) return;
        
        // Verificar que data.filtros exista
        const filtros = data.filtros || {};
        const top = document.getElementById('topSelect')?.value || 'todos';
        const sortBy = document.getElementById('sortBySelect')?.value || 'monto_total';
        const filtroFecha = document.getElementById('filtroFecha')?.value || 'este_mes';
        const fechaInicio = filtros.fecha_inicio || 'Sin fecha';
        const fechaFin = filtros.fecha_fin || 'Sin fecha';
        const clienteSeleccionadoId = document.getElementById('cliente_id')?.value || '';
        
        if (!clientes || clientes.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> 
                    No se encontraron clientes con pedidos en el período seleccionado.
                    <br><small>Período: ${fechaInicio} al ${fechaFin}</small>
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> 
                Mostrando <strong>${clientes.length}</strong> clientes
                <br><small>Período: ${fechaInicio} al ${fechaFin}</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th class="text-center">Total Pedidos</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-end">Promedio por Pedido</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        clientes.forEach((cliente, index) => {
            const nombreCompleto = `${cliente.Nombre || ''} ${cliente.apPaterno || ''} ${cliente.apMaterno || ''}`.trim() || 'Cliente sin nombre';
            
            let urlDetalle = `/reportes/pedidos-cliente/cliente/${cliente.id_Cliente}/detalle?filtro_fecha=${filtroFecha}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&top=${top}&sort_by=${sortBy}`;
            if (clienteSeleccionadoId) {
                urlDetalle += `&search_cliente=${clienteSeleccionadoId}`;
            }
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${nombreCompleto}</strong></td>
                    <td class="text-center"><span class="badge bg-secondary">${cliente.total_pedidos || 0}</span></td>
                    <td class="text-end">$${Number(cliente.monto_total || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-end">$${Number(cliente.monto_promedio || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">
                        <a href="${urlDetalle}" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i> Ver Detalle
                        </a>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = html;
    }

    // ============================================
    // LIMPIAR FILTROS
    // ============================================
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            document.getElementById('topSelect').value = '';
            document.getElementById('sortBySelect').value = '';
            document.getElementById('filtroFecha').value = '';
            document.getElementById('fechaInicio').value = '';
            document.getElementById('fechaFin').value = '';
            document.getElementById('fechaInicioDiv').style.display = 'none';
            document.getElementById('fechaFinDiv').style.display = 'none';
            
            const container = document.getElementById('resultadosContainer');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-secondary text-center">
                        <i class="bi bi-funnel"></i> 
                        Seleccione los filtros (Top, Ordenar y Fecha) y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
                    </div>
                `;
            }
            
            document.getElementById('botonesExportacion').style.display = 'none';
            window.limpiarCliente();
        });
    }

    // ============================================
    // EXPORTAR REPORTE
    // ============================================
    window.exportarReporte = function(tipo) {
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const clienteIdInput = document.getElementById('cliente_id');
        const clienteId = clienteIdInput ? clienteIdInput.value : null;
        
        let url = `{{ route('reportes.pedidos-cliente.exportar') }}?tipo=${tipo}&top=${top}&sort_by=${sortBy}&filtro_fecha=${filtroFecha}`;
        if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
        if (fechaFin) url += `&fecha_fin=${fechaFin}`;
        if (clienteId && clienteId !== '' && clienteId !== 'null' && clienteId !== 'undefined') {
            url += `&cliente_id=${clienteId}`;
        }
        
        window.open(url, '_blank');
    };
});
</script>
@endpush