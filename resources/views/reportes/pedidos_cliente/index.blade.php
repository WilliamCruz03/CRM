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
let clienteSeleccionadoId = null;
let clienteSeleccionadoNombre = null;

// ============================================
// BUSCAR CLIENTES
// ============================================
let timeoutBusquedaCliente = null;

document.getElementById('buscarClienteReporte').addEventListener('input', function() {
    const termino = this.value.trim();
    const resultadosDiv = document.getElementById('resultadosClientes');
    const listaClientes = document.getElementById('listaClientes');
    
    if (termino.length < 3) {
        resultadosDiv.style.display = 'none';
        return;
    }
    
    clearTimeout(timeoutBusquedaCliente);
    timeoutBusquedaCliente = setTimeout(() => {
        fetch(`{{ route('reportes.compras_cliente.buscar-clientes') }}?q=${encodeURIComponent(termino)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    listaClientes.innerHTML = data.data.map(cliente => `
                        <a href="#" class="list-group-item list-group-item-action" 
                           onclick="seleccionarCliente(${cliente.id_Cliente}, '${cliente.nombre_completo}')">
                            ${cliente.nombre_completo}
                            <small class="text-muted d-block">${cliente.correo || ''}</small>
                        </a>
                    `).join('');
                    resultadosDiv.style.display = 'block';
                } else {
                    listaClientes.innerHTML = `<div class="list-group-item text-muted">No se encontraron clientes</div>`;
                    resultadosDiv.style.display = 'block';
                }
            })
            .catch(error => console.error('Error:', error));
    }, 300);
});

// ============================================
// SELECCIONAR CLIENTE
// ============================================
function seleccionarCliente(id, nombre) {
    clienteSeleccionadoId = id;
    clienteSeleccionadoNombre = nombre;
    document.getElementById('cliente_id').value = id;
    document.getElementById('clienteNombre').textContent = nombre;
    document.getElementById('clienteSeleccionado').style.display = 'block';
    document.getElementById('resultadosClientes').style.display = 'none';
    document.getElementById('buscarClienteReporte').value = '';
}

function limpiarCliente() {
    clienteSeleccionadoId = null;
    clienteSeleccionadoNombre = null;
    document.getElementById('cliente_id').value = '';
    document.getElementById('clienteSeleccionado').style.display = 'none';
}

// ============================================
// FILTROS DE FECHA
// ============================================
document.getElementById('filtroFecha').addEventListener('change', function() {
    const fechaInicioDiv = document.getElementById('fechaInicioDiv');
    const fechaFinDiv = document.getElementById('fechaFinDiv');
    
    if (this.value === 'personalizado') {
        fechaInicioDiv.style.display = 'block';
        fechaFinDiv.style.display = 'block';
    } else {
        fechaInicioDiv.style.display = 'none';
        fechaFinDiv.style.display = 'none';
        // Si se selecciona un filtro rápido, limpiar fechas personalizadas
        document.getElementById('fechaInicio').value = '';
        document.getElementById('fechaFin').value = '';
    }
});

// ============================================
// APLICAR FILTROS
// ============================================
document.getElementById('btnAplicarFiltros').addEventListener('click', function() {
    const top = document.getElementById('topSelect').value;
    const sortBy = document.getElementById('sortBySelect').value;
    const filtroFecha = document.getElementById('filtroFecha').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const clienteId = document.getElementById('cliente_id').value;
    
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
    
    // Construir URL con parámetros
    let url = `{{ route('reportes.pedidos-cliente.data') }}?top=${top}&sort_by=${sortBy}&filtro_fecha=${filtroFecha}`;
    if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
    if (fechaFin) url += `&fecha_fin=${fechaFin}`;
    if (clienteId) url += `&cliente_id=${clienteId}`;
    
    cargarDatos(url);
});

// ============================================
// CARGAR DATOS
// ============================================
function cargarDatos(url) {
    const loading = document.getElementById('loadingIndicator');
    const container = document.getElementById('resultadosContainer');
    const botonesExportacion = document.getElementById('botonesExportacion');
    
    loading.style.display = 'block';
    container.innerHTML = '';
    botonesExportacion.style.display = 'none';
    
    fetch(url, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        
        if (data.success && data.data && data.data.length > 0) {
            mostrarResultados(data.data);
            botonesExportacion.style.display = 'block';
        } else {
            container.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No hay pedidos en el período seleccionado
                </div>
            `;
            botonesExportacion.style.display = 'none';
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        container.innerHTML = `
            <div class="alert alert-danger text-center">
                <i class="bi bi-exclamation-triangle"></i> Error al cargar los datos
            </div>
        `;
        console.error('Error:', error);
    });
}

// ============================================
// MOSTRAR RESULTADOS
// ============================================
function mostrarResultados(data) {
    const container = document.getElementById('resultadosContainer');
    
    let totalPedidos = 0;
    let montoTotal = 0;
    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaReporte">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th class="text-center">Total Pedidos</th>
                        <th class="text-end">Monto Total</th>
                        <th class="text-end">Promedio por Pedido</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.forEach((item, index) => {
        totalPedidos += item.total_pedidos;
        montoTotal += item.monto_total;
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.cliente_nombre || 'N/A'}</td>
                <td class="text-center">${item.total_pedidos}</td>
                <td class="text-end">$${Number(item.monto_total).toFixed(2)}</td>
                <td class="text-end">$${Number(item.monto_promedio).toFixed(2)}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">TOTALES:</td>
                        <td class="text-center">${totalPedidos}</td>
                        <td class="text-end">$${montoTotal.toFixed(2)}</td>
                        <td class="text-end">$${(montoTotal / totalPedidos || 0).toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

// ============================================
// LIMPIAR FILTROS
// ============================================
document.getElementById('btnLimpiarFiltros').addEventListener('click', function() {
    document.getElementById('topSelect').value = '';
    document.getElementById('sortBySelect').value = '';
    document.getElementById('filtroFecha').value = '';
    document.getElementById('fechaInicio').value = '';
    document.getElementById('fechaFin').value = '';
    document.getElementById('fechaInicioDiv').style.display = 'none';
    document.getElementById('fechaFinDiv').style.display = 'none';
    document.getElementById('resultadosContainer').innerHTML = `
        <div class="alert alert-secondary text-center">
            <i class="bi bi-funnel"></i> 
            Seleccione los filtros (Top, Ordenar y Fecha) y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
        </div>
    `;
    document.getElementById('botonesExportacion').style.display = 'none';
    limpiarCliente();
});

// ============================================
// EXPORTAR REPORTE
// ============================================
function exportarReporte(tipo) {
    const top = document.getElementById('topSelect').value;
    const sortBy = document.getElementById('sortBySelect').value;
    const filtroFecha = document.getElementById('filtroFecha').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const clienteId = document.getElementById('cliente_id').value;
    
    let url = `{{ route('reportes.pedidos-cliente.exportar') }}?tipo=${tipo}&top=${top}&sort_by=${sortBy}&filtro_fecha=${filtroFecha}`;
    if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
    if (fechaFin) url += `&fecha_fin=${fechaFin}`;
    if (clienteId) url += `&cliente_id=${clienteId}`;
    
    window.open(url, '_blank');
}
</script>
@endpush