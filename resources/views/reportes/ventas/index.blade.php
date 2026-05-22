@extends('layouts.app')

@section('title', 'Dashboard de Ventas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dashboard de Ventas</h3>
                    @include('reportes.partials.filtros_fecha', ['route' => 'reportes.ventas.index'])
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($kpis->total_ventas ?? 0, 2) }}</h3>
                    <p>Total de Ventas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($kpis->total_transacciones ?? 0) }}</h3>
                    <p>Transacciones</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($kpis->clientes_activos ?? 0) }}</h3>
                    <p>Clientes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($kpis->ticket_promedio ?? 0, 2) }}</h3>
                    <p>Ticket Promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Top 5 Productos más Vendidos</h5>
                </div>
                <div class="card-body">
                    <canvas id="topProductosChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Ventas por Día</h5>
                </div>
                <div class="card-body">
                    <canvas id="ventasPorDiaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Top 5 Clientes</h5>
                </div>
                <div class="card-body">
                    <canvas id="topClientesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Accesos Rápidos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('reportes.ventas.clientes', request()->all()) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-users"></i> Reporte de Clientes
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('reportes.ventas.top-clientes', request()->all()) }}" class="btn btn-success btn-block">
                                <i class="fas fa-trophy"></i> Top Clientes
                            </a>
                        </div>
                        <div class="col-6 mt-2">
                            <a href="{{ route('reportes.ventas.top-productos', request()->all()) }}" class="btn btn-info btn-block">
                                <i class="fas fa-box"></i> Top Productos
                            </a>
                        </div>
                        <div class="col-6 mt-2">
                            <a href="{{ route('reportes.ventas.top-sucursales', request()->all()) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-store"></i> Top Sucursales
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico Top Productos
    const topProductosCtx = document.getElementById('topProductosChart').getContext('2d');
    new Chart(topProductosCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topProductos->pluck('descripcion')->map(function($d) { 
                return strlen($d) > 20 ? substr($d, 0, 20) . '...' : $d; 
            })) !!},
            datasets: [{
                label: 'Monto de Ventas',
                data: {!! json_encode($topProductos->pluck('monto_total')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico Ventas por Día
    const ventasPorDiaCtx = document.getElementById('ventasPorDiaChart').getContext('2d');
    new Chart(ventasPorDiaCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($ventasPorDia->pluck('fecha')) !!},
            datasets: [{
                label: 'Ventas',
                data: {!! json_encode($ventasPorDia->pluck('total_ventas')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico Top Clientes
    const topClientesCtx = document.getElementById('topClientesChart').getContext('2d');
    new Chart(topClientesCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($topClientes->map(function($c) { return $c->Nombre . ' ' . $c->apPaterno; })) !!},
            datasets: [{
                data: {!! json_encode($topClientes->pluck('monto_total')) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': $' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection