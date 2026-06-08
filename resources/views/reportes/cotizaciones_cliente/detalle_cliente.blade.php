@extends('layouts.app')

@section('title', 'Detalle de Cotizaciones - ' . $cliente->nombre_completo)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Cotizaciones de: <strong>{{ $cliente->nombre_completo }}</strong>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.cotizaciones-cliente.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros aplicados -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Filtros aplicados:</strong>
                Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                @if($statusFilter !== 'todos')
                    , Estado: 
                    @switch($statusFilter)
                        @case('proceso') En proceso @break
                        @case('completadas') Completadas @break
                        @case('canceladas') Canceladas @break
                    @endswitch
                @endif
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row" id="kpisContainer" style="display: none;">
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3 id="kpiTotalCotizaciones">0</h3>
                    <p>Total Cotizaciones</p>
                </div>
                <div class="icon">
                    <i class="bi bi-file-earmark-text text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3 id="kpiImporteTotal">$0</h3>
                    <p>Importe Total</p>
                </div>
                <div class="icon">
                    <i class="bi bi-currency-dollar text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3 id="kpiTicketPromedio">$0</h3>
                    <p>Ticket Promedio</p>
                </div>
                <div class="icon">
                    <i class="bi bi-receipt text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3 id="kpiUltimaCotizacion">-</h3>
                    <p>Última Cotización</p>
                </div>
                <div class="icon">
                    <i class="bi bi-calendar text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs-custom" id="cotizacionTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla" type="button" role="tab">
                <i class="bi bi-table"></i> Cotizaciones
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="graficas-tab" data-bs-toggle="tab" data-bs-target="#graficas" type="button" role="tab">
                <i class="bi bi-bar-chart"></i> Análisis Gráfico
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab: Tabla de Cotizaciones -->
        <div class="tab-pane fade show active" id="tabla" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Importe</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cotizacionesBody">
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando cotizaciones...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Gráficas -->
        <div class="tab-pane fade" id="graficas" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Montos por Grupo Madre</h5>
                            <small class="text-muted">Distribución de compras por categoría principal</small>
                        </div>
                        <div class="card-body" style="overflow-y: auto; max-height: 500px;">
                            <canvas id="gruposMadreChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de productos -->
<div class="modal fade" id="modalProductos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Productos de la cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody id="productosBody">
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2">Cargando productos...</p>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold" id="modalTotal">$0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs-custom {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 20px;
}
.nav-tabs-custom .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    padding: 10px 20px;
    cursor: pointer;
}
.nav-tabs-custom .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: transparent;
}
.tab-content {
    padding: 20px 0;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartGruposMadre = null;
    let datosCotizaciones = null;

    // Cargar datos
    async function cargarDatosDetalle() {
        const clienteId = {{ $cliente->id_Cliente }};
        const filtroFecha = '{{ request("filtro_fecha", "este_mes") }}';
        const statusFilter = '{{ $statusFilter }}';
        
        let fechaInicio = '{{ $fechaInicio }}';
        let fechaFin = '{{ $fechaFin }}';
        
        const params = new URLSearchParams({
            filtro_fecha: filtroFecha,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
            status_filter: statusFilter
        });
        
        try {
            const response = await fetch(`{{ route("reportes.cotizaciones-cliente.cliente.data", $cliente->id_Cliente) }}?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                datosCotizaciones = data.data;
                
                // Mostrar KPIs
                mostrarKPIs(data.data.resumen);
                
                // Mostrar tabla de cotizaciones
                mostrarTablaCotizaciones(data.data.cotizaciones);
                
                // Guardar datos para gráfica
                window.gruposMadreData = data.data.gruposMadre;
                window.totalGeneral = data.data.totalGeneral;
            } else {
                document.getElementById('cotizacionesBody').innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger">Error al cargar datos</td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('cotizacionesBody').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">Error de conexión</td>
                </tr>
            `;
        }
    }
    
    function mostrarKPIs(resumen) {
        document.getElementById('kpisContainer').style.display = 'flex';
        document.getElementById('kpiTotalCotizaciones').textContent = Number(resumen.total_cotizaciones).toLocaleString();
        document.getElementById('kpiImporteTotal').textContent = `$${Number(resumen.importe_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
        document.getElementById('kpiTicketPromedio').textContent = `$${Number(resumen.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
        
        if (resumen.ultima_cotizacion) {
            const fecha = new Date(resumen.ultima_cotizacion);
            document.getElementById('kpiUltimaCotizacion').textContent = fecha.toLocaleDateString();
        } else {
            document.getElementById('kpiUltimaCotizacion').textContent = '-';
        }
    }
    
    function mostrarTablaCotizaciones(cotizaciones) {
        const tbody = document.getElementById('cotizacionesBody');
        
        if (!cotizaciones || cotizaciones.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="bi bi-info-circle"></i> No hay cotizaciones en el período seleccionado
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        cotizaciones.forEach(cot => {
            let estadoClass = '';
            let estadoNombre = '';
            
            switch(cot.id_fase) {
                case 1:
                    estadoClass = 'bg-warning';
                    estadoNombre = 'En proceso';
                    break;
                case 2:
                    estadoClass = 'bg-success';
                    estadoNombre = 'Completada';
                    break;
                case 3:
                    estadoClass = 'bg-danger';
                    estadoNombre = 'Cancelada';
                    break;
                default:
                    estadoClass = 'bg-secondary';
                    estadoNombre = 'Desconocido';
            }
            
            html += `
                <tr>
                    <td><strong>${cot.folio}</strong></td>
                    <td class="text-center">${new Date(cot.fecha_creacion).toLocaleDateString()}</td>
                    <td class="text-right">$${Number(cot.importe_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-center"><span class="badge ${estadoClass}">${estadoNombre}</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="verProductos(${cot.id_cotizacion}, '${cot.folio}')">
                            <i class="bi bi-box-seam"></i> Ver Productos
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    // Función para dibujar gráfica de grupos madre (barras horizontales)
    function dibujarGraficaGruposMadre() {
        const ctx = document.getElementById('gruposMadreChart').getContext('2d');
        
        if (chartGruposMadre) chartGruposMadre.destroy();
        
        if (!window.gruposMadreData || window.gruposMadreData.length === 0) {
            return;
        }
        
        const labels = window.gruposMadreData.map(g => g.descripciongrupomadre);
        const montos = window.gruposMadreData.map(g => g.monto_total);
        
        chartGruposMadre = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Monto Total',
                    data: montos,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                return `$${context.raw.toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Monto Total ($)'
                        },
                        ticks: {
                            callback: (value) => `$${value.toLocaleString('es-MX')}`
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Grupo Madre'
                        }
                    }
                }
            }
        });
    }
    
    // Ver productos de una cotización
    window.verProductos = async function(cotizacionId, folio) {
        const modal = new bootstrap.Modal(document.getElementById('modalProductos'));
        document.getElementById('modalProductos').querySelector('.modal-title').innerHTML = `<i class="bi bi-box-seam"></i> Productos de la cotización: ${folio}`;
        document.getElementById('productosBody').innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando productos...</p>
                </td>
            </tr>
        `;
        modal.show();
        
        try {
            const response = await fetch(`{{ route("reportes.cotizaciones-cliente.productos", "") }}/${cotizacionId}`);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                let html = '';
                let total = 0;
                
                data.data.forEach(prod => {
                    total += parseFloat(prod.importe);
                    html += `
                        <tr>
                            <td><small>${prod.codbar}</small></td>
                            <td>${prod.descripcion || 'Producto no encontrado'}</td>
                            <td class="text-center">${Number(prod.cantidad).toLocaleString()}</td>
                            <td class="text-end">$${Number(prod.precio_unitario).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            <td class="text-end">$${Number(prod.importe).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `;
                });
                
                document.getElementById('productosBody').innerHTML = html;
                document.getElementById('modalTotal').textContent = `$${total.toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
            } else {
                document.getElementById('productosBody').innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay productos en esta cotización</td>
                    </tr>
                `;
                document.getElementById('modalTotal').textContent = '$0.00';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('productosBody').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">Error al cargar productos</td>
                </tr>
            `;
        }
    };
    
    // Observar cambio de tab
    document.querySelectorAll('#cotizacionTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            if (event.target.getAttribute('data-bs-target') === '#graficas') {
                setTimeout(() => {
                    dibujarGraficaGruposMadre();
                }, 100);
            }
        });
    });
    
    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        cargarDatosDetalle();
        
        // Si la pestaña de gráficas está activa por defecto
        if (document.getElementById('graficas').classList.contains('active')) {
            setTimeout(() => {
                dibujarGraficaGruposMadre();
            }, 100);
        }
    });
</script>
@endpush
@endsection