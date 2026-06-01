@extends('layouts.app')

@section('title', 'Montos Promedio de Compra por Cliente')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-calculator"></i> Montos Promedio de Compra por Cliente
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
            <form method="GET" id="filtrosForm" onsubmit="return false;">
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
                            <option value="total_compras">Más Compras</option>
                            <option value="total_compras_asc">Menos Compras</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Buscar cliente (opcional)</label>
                        <input type="text" class="form-control" id="buscarCliente" placeholder="Nombre o apellido">
                        <input type="hidden" id="cliente_id">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary btn-block" id="btnAplicarFiltros">
                            <i class="bi bi-funnel"></i> Aplicar Filtros
                        </button>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

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
                    Seleccione los filtros y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Compras -->
<div class="modal fade" id="modalDetalleCompras" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-receipt"></i> Historial de Compras - <span id="detalleClienteNombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Ticket</th>
                                <th>Monto</th>
                                <th>Acumulado</th>
                            </tr>
                        </thead>
                        <tbody id="detalleComprasBody">
                            <tr>
                                <td colspan="4" class="text-center">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let clienteSeleccionadoId = null;
    let timeoutBusqueda = null;

    function getFechasByFiltro(filtro) {
        const hoy = new Date();
        let inicio, fin;
        
        switch(filtro) {
            case 'hoy':
                inicio = hoy.toISOString().split('T')[0];
                fin = hoy.toISOString().split('T')[0];
                break;
            case 'esta_semana':
                const dia = hoy.getDay();
                const diff = dia === 0 ? 6 : dia - 1;
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - diff);
                const finSemana = new Date(inicioSemana);
                finSemana.setDate(inicioSemana.getDate() + 6);
                inicio = inicioSemana.toISOString().split('T')[0];
                fin = finSemana.toISOString().split('T')[0];
                break;
            case 'este_mes':
                inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];
                break;
            case 'este_ano':
                inicio = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
                fin = new Date(hoy.getFullYear(), 11, 31).toISOString().split('T')[0];
                break;
            default:
                return null;
        }
        
        return { inicio, fin };
    }

    async function cargarDatos() {
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        
        if (!top || !sortBy || !filtroFecha) {
            if (window.mostrarToast) window.mostrarToast('Debe seleccionar todos los filtros', 'warning');
            return;
        }
        
        let fechaInicio, fechaFin;
        
        if (filtroFecha === 'personalizado') {
            fechaInicio = document.getElementById('fechaInicio').value;
            fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                if (window.mostrarToast) window.mostrarToast('Debe seleccionar ambas fechas', 'warning');
                return;
            }
        } else {
            const fechas = getFechasByFiltro(filtroFecha);
            if (!fechas) return;
            fechaInicio = fechas.inicio;
            fechaFin = fechas.fin;
        }
        
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('resultadosContainer').innerHTML = '';
        document.getElementById('botonesExportacion').style.display = 'none';
        
        try {
            const params = new URLSearchParams({
                top: top,
                sort_by: sortBy,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });
            
            if (clienteSeleccionadoId) {
                params.append('search_cliente', clienteSeleccionadoId);
            }
            
            const response = await fetch(`{{ route("reportes.ventas.montos-promedio.data") }}?${params.toString()}`);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                mostrarResultados(data);
                document.getElementById('botonesExportacion').style.display = 'inline-flex';
            } else {
                document.getElementById('resultadosContainer').innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> 
                        No se encontraron clientes en el período seleccionado.
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('resultadosContainer').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Error al cargar los datos
                </div>
            `;
        } finally {
            document.getElementById('loadingIndicator').style.display = 'none';
        }
    }
    
    function mostrarResultados(data) {
        const clientes = data.data;
        
        let html = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> 
                Mostrando <strong>${clientes.length}</strong> clientes
                <br><small>Período: ${data.filtros.fecha_inicio} al ${data.filtros.fecha_fin}</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Total Compras</th>
                            <th>Monto Total</th>
                            <th>Promedio</th>
                            <th>Primera Compra</th>
                            <th>Última Compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        clientes.forEach(cliente => {
            html += `
                <tr>
                    <td>${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}<br>
                        <small class="text-muted">ID: ${cliente.id_Cliente}</small>
                    </td>
                    <td style="text-align: center">${Number(cliente.total_compras).toLocaleString()}</td>
                    <td style="text-align: right">$${Number(cliente.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td style="text-align: right">$${Number(cliente.monto_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td style="text-align: left">
                        ${cliente.fecha_primera_compra ? new Date(cliente.fecha_primera_compra).toLocaleDateString() : 'N/A'}<br>
                        <small>$${Number(cliente.monto_primera_compra || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</small>
                    </td>
                    <td style="text-align: left">
                        ${cliente.fecha_ultima_compra ? new Date(cliente.fecha_ultima_compra).toLocaleDateString() : 'N/A'}<br>
                        <small>$${Number(cliente.monto_ultima_compra || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</small>
                    </td>
                    <td style="text-align: center">
                        <button type="button" class="btn btn-info btn-sm" onclick="verDetalleCompras(${cliente.id_Cliente})">
                            <i class="bi bi-receipt"></i> Ver Detalle
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('resultadosContainer').innerHTML = html;
    }
    
    async function verDetalleCompras(clienteId) {
        const filtroFecha = document.getElementById('filtroFecha').value;
        let fechaInicio, fechaFin;
        
        if (filtroFecha === 'personalizado') {
            fechaInicio = document.getElementById('fechaInicio').value;
            fechaFin = document.getElementById('fechaFin').value;
        } else {
            const fechas = getFechasByFiltro(filtroFecha);
            if (fechas) {
                fechaInicio = fechas.inicio;
                fechaFin = fechas.fin;
            }
        }
        
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) loadingIndicator.style.display = 'block';
        
        try {
            const response = await fetch(`{{ route("reportes.ventas.montos-promedio.detalle", '') }}/${clienteId}?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
            const data = await response.json();
            
            if (data.success) {
                mostrarModalDetalle(data.data);
            } else {
                if (window.mostrarToast) window.mostrarToast('Error al cargar detalles', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        } finally {
            if (loadingIndicator) loadingIndicator.style.display = 'none';
        }
    }
    
    function mostrarModalDetalle(data) {
        const modal = document.getElementById('modalDetalleCompras');
        const tbody = document.getElementById('detalleComprasBody');
        const clienteNombre = document.getElementById('detalleClienteNombre');
        
        clienteNombre.textContent = `${data.cliente.Nombre} ${data.cliente.apPaterno} ${data.cliente.apMaterno || ''}`;
        
        if (data.compras.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron compras en el período</td></tr>';
        } else {
            let html = '';
            data.compras.forEach(compra => {
                html += `
                    <tr>
                        <td style="text-align: center">${new Date(compra.fecha).toLocaleDateString()}</td>
                        <td style="text-align: center">${compra.ticket}</td>
                        <td style="text-align: right">$${Number(compra.monto).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                        <td style="text-align: right">$${Number(compra.acumulado).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }
        
        new bootstrap.Modal(modal).show();
    }
    
    window.exportarReporte = function(tipo) {
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        
        let fechaInicio, fechaFin;
        
        if (filtroFecha === 'personalizado') {
            fechaInicio = document.getElementById('fechaInicio').value;
            fechaFin = document.getElementById('fechaFin').value;
        } else {
            const fechas = getFechasByFiltro(filtroFecha);
            if (fechas) {
                fechaInicio = fechas.inicio;
                fechaFin = fechas.fin;
            }
        }
        
        const params = new URLSearchParams({
            top: top,
            sort_by: sortBy,
            filtro_fecha: filtroFecha,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        });
        
        if (clienteSeleccionadoId) {
            params.append('search_cliente', clienteSeleccionadoId);
        }
        
        let url;
        if (tipo === 'excel') {
            url = `{{ route("reportes.ventas.montos-promedio.exportar.excel") }}?${params.toString()}`;
        } else {
            url = `{{ route("reportes.ventas.montos-promedio.exportar.pdf") }}?${params.toString()}`;
        }
        
        if (window.mostrarToast) window.mostrarToast(`Generando ${tipo.toUpperCase()}...`, 'warning');
        window.open(url, '_blank');
    };
    
    // Eventos
    document.getElementById('btnAplicarFiltros').addEventListener('click', cargarDatos);
    
    document.getElementById('filtroFecha').addEventListener('change', function() {
        const fechaInicioDiv = document.getElementById('fechaInicioDiv');
        const fechaFinDiv = document.getElementById('fechaFinDiv');
        
        if (this.value === 'personalizado') {
            fechaInicioDiv.style.display = 'block';
            fechaFinDiv.style.display = 'block';
            const hoy = new Date();
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            document.getElementById('fechaInicio').value = inicioMes.toISOString().split('T')[0];
            document.getElementById('fechaFin').value = hoy.toISOString().split('T')[0];
        } else {
            fechaInicioDiv.style.display = 'none';
            fechaFinDiv.style.display = 'none';
        }
    });
</script>
@endpush
@endsection