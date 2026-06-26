@extends('layouts.app')

@section('title', 'Sucursales Preferidas')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-house-heart"></i> Sucursales Preferidas
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

            <div class="row mt-3">
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
                    <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                        <i class="bi bi-eraser"></i> Limpiar
                    </button>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row mt-4" id="kpisContainer" style="display: none;">
                <div class="col-md-3">
                    <div class="small-box">
                        <div class="inner">
                            <h3 id="kpiTotalSucursales">0</h3>
                            <p><i class="bi bi-building text-info"></i> Sucursales Activas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box">
                        <div class="inner">
                            <h3 id="kpiTotalVentasNumero">0</h3>
                            <p><i class="bi bi-cart text-success"></i> Ventas Totales</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box">
                        <div class="inner">
                            <h3 id="kpiTotalMonto">$0</h3>
                            <p><i class="bi bi-currency-dollar text-success"></i> Monto Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box">
                        <div class="inner">
                            <h3 id="kpiTopSucursal">-</h3>
                            <p><i class="bi bi-trophy text-warning"></i> Sucursal Más Visitada</p>
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

    function formatearFechaLocal(fecha) {
        const año = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${año}-${mes}-${dia}`;
    }

    // Mostrar/ocultar fechas personalizadas
    document.getElementById('filtroFecha').addEventListener('change', function() {
        const fechaInicioDiv = document.getElementById('fechaInicioDiv');
        const fechaFinDiv = document.getElementById('fechaFinDiv');
        
        if (this.value === 'personalizado') {
            fechaInicioDiv.style.display = 'block';
            fechaFinDiv.style.display = 'block';
            const hoy = new Date();
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            // Usar formatearFechaLocal en lugar de toISOString
            document.getElementById('fechaInicio').value = formatearFechaLocal(inicioMes);
            document.getElementById('fechaFin').value = formatearFechaLocal(hoy);
        } else {
            fechaInicioDiv.style.display = 'none';
            fechaFinDiv.style.display = 'none';
        }
    });

    // Función para obtener fecha inicio/fin según el filtro
    function getFechasByFiltro(filtro) {
        const hoy = new Date();
        let inicio, fin;
        
        switch(filtro) {
            case 'hoy':  // Para los filtros que lo usen.
                inicio = formatearFechaLocal(hoy);
                fin = formatearFechaLocal(hoy);
                break;
            case 'esta_semana':
                const dia = hoy.getDay(); // 0=Domingo, 1=Lunes...
                const diff = dia === 0 ? 6 : dia - 1; // Lunes como inicio
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - diff);
                // FIN = HOY
                inicio = formatearFechaLocal(inicioSemana);
                fin = formatearFechaLocal(hoy);
                break;
            case 'este_mes':
                const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                inicio = formatearFechaLocal(inicioMes);
                fin = formatearFechaLocal(finMes);
                break;
            case 'este_ano':
                const inicioAno = new Date(hoy.getFullYear(), 0, 1);
                // FIN = Fecha actual
                inicio = formatearFechaLocal(inicioAno);
                fin = formatearFechaLocal(hoy);
                break;
            default:
                return null;
        }
        
        return { inicio, fin };
    }

    function cargarFiltrosDesdeURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('sort_by')) {
            document.getElementById('sortBySelect').value = urlParams.get('sort_by');
        }
        if (urlParams.has('filtro_fecha')) {
            const filtroFecha = urlParams.get('filtro_fecha');
            document.getElementById('filtroFecha').value = filtroFecha;
            
            if (filtroFecha === 'personalizado') {
                document.getElementById('fechaInicioDiv').style.display = 'block';
                document.getElementById('fechaFinDiv').style.display = 'block';
                if (urlParams.has('fecha_inicio')) {
                    document.getElementById('fechaInicio').value = urlParams.get('fecha_inicio');
                }
                if (urlParams.has('fecha_fin')) {
                    document.getElementById('fechaFin').value = urlParams.get('fecha_fin');
                }
            }
        }
        if (urlParams.has('fecha_inicio') && document.getElementById('filtroFecha').value !== 'personalizado') {
            document.getElementById('fechaInicio').value = urlParams.get('fecha_inicio');
        }
        if (urlParams.has('fecha_fin') && document.getElementById('filtroFecha').value !== 'personalizado') {
            document.getElementById('fechaFin').value = urlParams.get('fecha_fin');
        }
    }

    async function cargarDatos() {
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        
        if (!sortBy || !filtroFecha) {
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
                    Error al cargar los datos: ${error.message}
                </div>
            `;
        } finally {
            document.getElementById('loadingIndicator').style.display = 'none';
        }
    }
    
    function mostrarResultados(data) {
        const sucursales = data.data;
        // Usar monto_total en lugar de total_ventas
        const totalMonto = sucursales.reduce((sum, s) => sum + s.monto_total, 0);
        
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
                            <th>Clientes Atendidos</th>
                            <th>% del Monto Total de Ventas</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        sucursales.forEach((sucursal, index) => {
            // Calcular porcentaje basado en monto_total
            const porcentaje = totalMonto > 0 ? (sucursal.monto_total / totalMonto) * 100 : 0;
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${sucursal.nombre}</strong></td>
                    <td class="text-center">${Number(sucursal.total_ventas).toLocaleString()}</td>
                    <td class="text-right">$${Number(sucursal.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-right">$${Number(sucursal.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">${Number(sucursal.clientes_atendidos).toLocaleString()}</td>
                    <td style="text-align: center; min-width: 120px;">
                        <div class="progress" style="height: 24px; background-color: #e9ecef; border-radius: 4px; position: relative;">
                            <div class="progress-bar" role="progressbar" 
                                style="width: ${porcentaje}%; 
                                        background-color: #0d6efd;
                                        border-radius: 4px;">
                            </div>
                            <span style="position: absolute;
                                        left: 0;
                                        right: 0;
                                        top: 0;
                                        bottom: 0;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-size: 12px;
                                        font-weight: 500;
                                        color: ${porcentaje > 40 ? 'white' : '#212529'};">
                                ${porcentaje.toFixed(1)}%
                            </span>
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

    // Limpiar todos los filtros
    function limpiarFiltros() {
        // Limpiar selects
        document.getElementById('sortBySelect').value = 'ventas';
        document.getElementById('filtroFecha').value = 'este_mes';
        
        // Limpiar fechas personalizadas
        document.getElementById('fechaInicio').value = '';
        document.getElementById('fechaFin').value = '';
        
        // Ocultar campos de fechas personalizadas
        document.getElementById('fechaInicioDiv').style.display = 'none';
        document.getElementById('fechaFinDiv').style.display = 'none';
        
        // Limpiar URL (remover parámetros)
        const url = new URL(window.location.href);
        url.search = '';
        window.history.pushState({}, '', url);
        
        // Mostrar mensaje de carga
        document.getElementById('resultadosContainer').innerHTML = `
            <div class="alert alert-secondary text-center">
                <i class="bi bi-funnel"></i> 
                Seleccione los filtros (Ordenar y Fecha) y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
            </div>
        `;
        
        document.getElementById('kpisContainer').style.display = 'none';
        document.getElementById('graficosContainer').style.display = 'none';
        document.getElementById('botonesExportacion').style.display = 'none';
        
        // Destruir gráficos si existen
        if (chartTopSucursales) {
            chartTopSucursales.destroy();
            chartTopSucursales = null;
        }
        if (chartDistribucion) {
            chartDistribucion.destroy();
            chartDistribucion = null;
        }
        
        // Recargar datos con los valores por defecto
        cargarDatos();
        
        if (window.mostrarToast) {
            window.mostrarToast('Filtros limpiados correctamente', 'success');
        }
    }
    
    function mostrarKPIs(data) {
        const sucursales = data.data;
        if (!sucursales || sucursales.length === 0) {
            document.getElementById('kpiTotalSucursales').textContent = '0';
            document.getElementById('kpiTotalVentasNumero').textContent = '0';
            document.getElementById('kpiTotalMonto').textContent = '$0.00';
            document.getElementById('kpiTopSucursal').textContent = '-';
            return;
        }
        
        const totalVentasNumero = sucursales.reduce((sum, s) => sum + s.total_ventas, 0);
        const montoTotal = sucursales.reduce((sum, s) => sum + s.monto_total, 0);
        
        // SUCURSAL MÁS VISITADA - LA DE MAYOR total_ventas
        const topSucursal = sucursales.reduce((max, s) => {
            return s.total_ventas > max.total_ventas ? s : max;
        }, sucursales[0]);
        
        document.getElementById('kpiTotalSucursales').textContent = sucursales.length;
        document.getElementById('kpiTotalVentasNumero').textContent = totalVentasNumero.toLocaleString();
        document.getElementById('kpiTotalMonto').textContent = `$${montoTotal.toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
        document.getElementById('kpiTopSucursal').textContent = topSucursal?.nombre || '-';
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
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8BC34A', '#FF5722', '#9C27B0', '#00BCD4']
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
    
    // Event Listeners
    document.getElementById('btnAplicarFiltros').addEventListener('click', cargarDatos);
    document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
    
    // Inicialización
    cargarFiltrosDesdeURL();
    if (window.location.search.length > 0) {
        setTimeout(() => cargarDatos(), 300);
    }
</script>
@endpush
@endsection