@extends('layouts.app')

@section('title', 'Detalle de Pedidos - ' . $cliente->nombre_completo)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Detalle de Pedidos: <strong>{{ $cliente->nombre_completo }}</strong>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.pedidos-cliente.index', array_merge(
                            request()->except('page'),
                            array_filter([
                                'top' => request('top', 'todos'),
                                'sort_by' => request('sort_by', 'monto_total'),
                                'filtro_fecha' => request('filtro_fecha', 'este_mes'),
                                'fecha_inicio' => request('fecha_inicio', $fechaInicio),
                                'fecha_fin' => request('fecha_fin', $fechaFin),
                                'search_cliente' => request('search_cliente') ? request('search_cliente') : null
                            ])
                        )) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mostrar los filtros aplicados -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Filtros aplicados:</strong>
                Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row" id="kpisContainer">
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3 id="kpiTotalPedidos">-</h3>
                    <p>Total Pedidos</p>
                </div>
                <div class="icon">
                    <i class="bi bi-receipt text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3 id="kpiImporteTotal">-</h3>
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
                    <h3 id="kpiTicketPromedio">-</h3>
                    <p>Ticket Promedio</p>
                </div>
                <div class="icon">
                    <i class="bi bi-calculator text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3 id="kpiUltimoPedido">-</h3>
                    <p>Último Pedido</p>
                </div>
                <div class="icon">
                    <i class="bi bi-clock-history text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs-custom" id="pedidoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla" type="button" role="tab">
                <i class="bi bi-table"></i> Listado de Pedidos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="graficas-tab" data-bs-toggle="tab" data-bs-target="#graficas" type="button" role="tab">
                <i class="bi bi-bar-chart"></i> Análisis Gráfico
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab: Tabla de Pedidos -->
        <div class="tab-pane fade show active" id="tabla" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th class="text-end">Importe</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidosBody">
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando pedidos...</p>
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
                            <h5>Distribución por Grupo Madre</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="gruposMadreChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
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
    let datosPedidos = null;

    async function cargarDatosDetalle() {
        const clienteId = {{ $cliente->id_Cliente }};
        const filtroFecha = '{{ request("filtro_fecha", "este_mes") }}';
        let fechaInicio = '{{ $fechaInicio }}';
        let fechaFin = '{{ $fechaFin }}';
        
        const params = new URLSearchParams({
            filtro_fecha: filtroFecha,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        });
        
        try {
            const response = await fetch(`/reportes/pedidos-cliente/cliente/${clienteId}/data?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                datosPedidos = data.data;
                
                mostrarKPIs(data.data.resumen);
                mostrarTablaPedidos(data.data.pedidos, clienteId);
                
                window.gruposMadreData = data.data.gruposMadre;
                window.totalGeneral = data.data.totalGeneral;
            } else {
                document.getElementById('pedidosBody').innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger">Error al cargar datos</td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('pedidosBody').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">Error de conexión</td>
                </tr>
            `;
        }
    }
    
    function mostrarKPIs(resumen) {
        document.getElementById('kpiTotalPedidos').textContent = Number(resumen.total_pedidos).toLocaleString();
        document.getElementById('kpiImporteTotal').textContent = `$${Number(resumen.importe_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
        document.getElementById('kpiTicketPromedio').textContent = `$${Number(resumen.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
        
        if (resumen.ultimo_pedido) {
            const fecha = new Date(resumen.ultimo_pedido);
            document.getElementById('kpiUltimoPedido').textContent = fecha.toLocaleDateString();
        } else {
            document.getElementById('kpiUltimoPedido').textContent = '-';
        }
    }
    
    function mostrarTablaPedidos(pedidos, clienteId) {
        const tbody = document.getElementById('pedidosBody');
        
        if (!pedidos || pedidos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-box-seam"></i> No hay pedidos en el período seleccionado
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        pedidos.forEach(pedido => {
            // Construir URL con los filtros actuales
            const urlProductos = `/reportes/pedidos-cliente/cliente/${clienteId}/pedido/${pedido.id_pedido}/productos?filtro_fecha={{ request('filtro_fecha', 'este_mes') }}&fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}&top={{ request('top', 'todos') }}&sort_by={{ request('sort_by', 'monto_total') }}&search_cliente={{ request('search_cliente', $searchCliente ?? '') }}`;
            
            html += `
                <tr>
                    <td><strong>${pedido.folio_pedido}</strong></td>
                    <td class="text-center">${new Date(pedido.fecha_pedido).toLocaleDateString()}</td>
                    <td class="text-end">$${Number(pedido.importe_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-center"><span class="badge bg-${pedido.estado_color || 'secondary'}">${pedido.estado_nombre || 'Desconocido'}</span></td>
                    <td class="text-center">
                        <a href="${urlProductos}" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-box-seam"></i> Ver Productos
                        </a>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function dibujarGraficaGruposMadre() {
        const ctx = document.getElementById('gruposMadreChart').getContext('2d');
        
        if (chartGruposMadre) chartGruposMadre.destroy();
        
        if (!window.gruposMadreData || window.gruposMadreData.length === 0) {
            document.getElementById('gruposMadreChart').parentElement.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No hay datos para mostrar en la gráfica
                </div>
            `;
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
                            text: 'Grupo Madre'
                        },
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Monto Total ($)'
                        },
                        ticks: {
                            callback: (value) => `$${value.toLocaleString('es-MX')}`
                        }
                    }
                }
            }
        });
    }

    document.querySelectorAll('#pedidoTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            if (event.target.getAttribute('data-bs-target') === '#graficas') {
                setTimeout(() => {
                    dibujarGraficaGruposMadre();
                }, 100);
            }
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        cargarDatosDetalle();
    });
</script>
@endpush
@endsection