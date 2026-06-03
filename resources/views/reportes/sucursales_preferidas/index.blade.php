@extends('layouts.app')

@section('title', 'Sucursales Preferidas')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-building"></i> Sucursales Preferidas
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
            @include('reportes.partials.filtros_fecha', ['route' => 'reportes.sucursales-preferidas'])

            <div class="row mt-3">
                <div class="col-md-3">
                    <label>Top <span class="text-danger">*</span></label>
                    <select class="form-control" id="topSelect">
                        <option value="">-- Seleccione --</option>
                        <option value="5">Top 5</option>
                        <option value="10" selected>Top 10</option>
                        <option value="25">Top 25</option>
                        <option value="todos">Todas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Ordenar por <span class="text-danger">*</span></label>
                    <select class="form-control" id="sortBySelect">
                        <option value="">-- Seleccione --</option>
                        <option value="ventas">Más Visitada</option>
                        <option value="ventas_asc">Menos Visitada</option>
                        <option value="monto">Mayor Monto</option>
                        <option value="monto_asc">Menor Monto</option>
                        <option value="ticket">Mayor Ticket Promedio</option>
                        <option value="ticket_asc">Menor Ticket Promedio</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" id="btnAplicarFiltros">
                        <i class="bi bi-funnel"></i> Aplicar
                    </button>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row mt-4" id="kpisContainer" style="display: none;">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="kpiTotalSucursales">0</h3>
                            <p>Sucursales Activas</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="kpiTotalVentas">$0</h3>
                            <p>Ventas Totales</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="kpiTopSucursal">-</h3>
                            <p>Sucursal Más Visitada</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-trophy"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="kpiTopMonto">$0</h3>
                            <p>Mayor Facturación</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mt-4" id="graficosContainer" style="display: none;">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top Sucursales por Ventas</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="topSucursalesChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Distribución de Ventas</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="distribucionChart" height="250"></canvas>
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
            <div id="resultadosContainer" class="mt-4">
                <div class="alert alert-secondary text-center">
                    <i class="bi bi-funnel"></i> 
                    Seleccione los filtros y presione <strong>"Aplicar"</strong> para ver los resultados.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartTopSucursales = null;
    let chartDistribucion = null;

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
        document.getElementById('kpisContainer').style.display = 'none';
        document.getElementById('graficosContainer').style.display = 'none';
        
        try {
            const params = new URLSearchParams({
                top: top,
                sort_by: sortBy,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });
            
            const response = await fetch(`{{ route("reportes.sucursales-preferidas.data") }}?${params.toString()}`);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                mostrarResultados(data);
                mostrarKPIs(data);
                mostrarGraficos(data);
                document.getElementById('botonesExportacion').style.display = 'inline-flex';
                document.getElementById('kpisContainer').style.display = 'flex';
                document.getElementById('graficosContainer').style.display = 'flex';
            } else {
                document.getElementById('resultadosContainer').innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> 
                        No se encontraron ventas en el período seleccionado.
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
        const sucursales = data.data;
        
        let html = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> 
                Mostrando <strong>${sucursales.length}</strong> sucursales
                <br><small>Período: ${data.filtros.fecha_inicio} al ${data.filtros.fecha_fin}</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sucursal</th>
                            <th>Ventas</th>
                            <th>Monto Total</th>
                            <th>Ticket Promedio</th>
                            <th>Clientes Únicos</th>
                            <th>% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        const totalVentas = sucursales.reduce((sum, s) => sum + s.total_ventas, 0);
        
        sucursales.forEach((sucursal, index) => {
            const porcentaje = totalVentas > 0 ? (sucursal.total_ventas / totalVentas) * 100 : 0;
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${sucursal.nombre}</strong></td>
                    <td class="text-center">${Number(sucursal.total_ventas).toLocaleString()}</td>
                    <td class="text-right">$${Number(sucursal.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-right">$${Number(sucursal.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">${Number(sucursal.clientes_atendidos).toLocaleString()}</td>
                    <td class="text-center">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: ${porcentaje}%" 
                                 aria-valuenow="${porcentaje}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                ${porcentaje.toFixed(1)}%
                            </div>
                        </div>
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
    
    function mostrarKPIs(data) {
        const sucursales = data.data;
        const totalVentas = sucursales.reduce((sum, s) => sum + s.total_ventas, 0);
        const montoTotal = sucursales.reduce((sum, s) => sum + s.monto_total, 0);
        const topSucursal = sucursales[0];
        const topMonto = sucursales.reduce((max, s) => s.monto_total > max.monto_total ? s : max, sucursales[0]);
        
        document.getElementById('kpiTotalSucursales').textContent = sucursales.length;
        document.getElementById('kpiTotalVentas').textContent = `$${montoTotal.toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
        document.getElementById('kpiTopSucursal').textContent = topSucursal?.nombre || '-';
        document.getElementById('kpiTopMonto').textContent = `$${(topMonto?.monto_total || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
    }
    
    function mostrarGraficos(data) {
        const sucursales = data.data.slice(0, 10);
        const labels = sucursales.map(s => s.nombre);
        const ventasData = sucursales.map(s => s.total_ventas);
        const montoData = sucursales.map(s => s.monto_total);
        
        // Gráfico de barras - Top Sucursales
        if (chartTopSucursales) chartTopSucursales.destroy();
        const ctx1 = document.getElementById('topSucursalesChart').getContext('2d');
        chartTopSucursales = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Número de Ventas',
                    data: ventasData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (value) => value.toLocaleString() }
                    }
                }
            }
        });
        
        // Gráfico de pastel - Distribución
        if (chartDistribucion) chartDistribucion.destroy();
        const ctx2 = document.getElementById('distribucionChart').getContext('2d');
        chartDistribucion = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: montoData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#8BC34A', '#FF5722', '#9C27B0', '#00BCD4'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.label}: $${context.raw.toLocaleString('es-MX', {minimumFractionDigits: 2})}`
                        }
                    }
                }
            }
        });
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
        
        let url;
        if (tipo === 'excel') {
            url = `{{ route("reportes.sucursales-preferidas.exportar.excel") }}?${params.toString()}`;
        } else {
            url = `{{ route("reportes.sucursales-preferidas.exportar.pdf") }}?${params.toString()}`;
        }
        
        if (window.mostrarToast) window.mostrarToast(`Generando ${tipo.toUpperCase()}...`, 'warning');
        window.open(url, '_blank');
    };
    
    document.getElementById('btnAplicarFiltros').addEventListener('click', cargarDatos);
</script>
@endpush
@endsection