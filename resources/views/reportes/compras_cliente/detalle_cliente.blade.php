@extends('layouts.app')

@section('title', 'Detalle de Compras - ' . $cliente->nombre_completo)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Detalle de Compras: <strong>{{ $cliente->nombre_completo }}</strong>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.compras_cliente.clientes', array_merge(
                            request()->except('page'),
                            [
                                'top' => $top ?? 'todos',
                                'sort_by' => $sortBy ?? 'monto_total',
                                'filtro_fecha' => request('filtro_fecha', 'este_mes'),
                                'fecha_inicio' => request('fecha_inicio', $fechaInicio),
                                'fecha_fin' => request('fecha_fin', $fechaFin),
                                'indicacion_id' => request('indicacion_id'),
                                'search_cliente' => request('search_cliente', $searchCliente ?? '')
                            ]
                        )) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mostrar los filtros aplicados --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Filtros aplicados:</strong>
                Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                @if($gruposMadre->isEmpty())
                    <br><span class="text-warning"><i class="bi bi-exclamation-triangle text-warning"></i>No hay ventas en este período para este cliente.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Resumen del Cliente -->
    <div class="row">
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>${{ number_format($totalGeneral, 2) }}</h3>
                    <p><i class="bi bi-graph-up text-info"></i> Total General</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ $familias->count() }}</h3>
                    <p><i class="bi bi-tags text-success"></i> Familias Compradas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</h3>
                    <p><i class="bi bi-calendar text-warning"></i> Período de Análisis</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3><i class="bi bi-clock-history"></i> {!! $frecuenciaTexto !!}</h3>
                    <p><i class="bi bi-calendar-week text-primary"></i> Frecuencia de Compra</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs-custom" id="graficoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla" type="button" role="tab">
                <i class="bi bi-table"></i> Detalle por Familia
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="graficas-tab" data-bs-toggle="tab" data-bs-target="#graficas" type="button" role="tab">
                <i class="bi bi-bar-chart"></i> Análisis Gráfico
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab: Tabla de Grupos Madre -->
        <div class="tab-pane fade show active" id="tabla" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Grupo Madre</th>
                            <th>Monto Total</th>
                            <th>% del Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gruposMadre as $grupo)
                        <tr>
                            <td><strong>{{ $grupo->descripciongrupomadre ?? 'Sin Categoría' }}</strong></td>
                            <td class="text-right">${{ number_format($grupo->monto_total, 2) }}</td>
                            <td style="min-width: 120px;">
                                <div class="progress" style="height: 24px; background-color: #e9ecef; border-radius: 4px; position: relative;">
                                    <div class="progress-bar" role="progressbar" 
                                        style="width: {{ $totalGeneral > 0 ? ($grupo->monto_total / $totalGeneral) * 100 : 0 }}%; 
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
                                                color: {{ ($totalGeneral > 0 && ($grupo->monto_total / $totalGeneral) * 100 > 40) ? 'white' : '#212529' }};">
                                        {{ number_format(($grupo->monto_total / $totalGeneral) * 100, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('reportes.compras_cliente.cliente.grupo-madre', [
                                    'clienteId' => $cliente->id_Cliente,
                                    'grupoMadreId' => $grupo->id_grupo_madre,
                                    'top' => $top ?? 'todos',
                                    'sort_by' => $sortBy ?? 'monto_total',
                                    'filtro_fecha' => request('filtro_fecha', 'este_mes'),
                                    'fecha_inicio' => request('fecha_inicio', $fechaInicio),
                                    'fecha_fin' => request('fecha_fin', $fechaFin),
                                    'indicacion_id' => request('indicacion_id'),
                                    'search_cliente' => request('search_cliente', $searchCliente ?? '')
                                ]) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-boxes"></i> Ver Productos
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Gráficas -->
        <div class="tab-pane fade" id="graficas" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Distribución por Grupo Madre</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="gruposMadreChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Montos por Familia</h5>
                            <small class="text-muted">Top 20 familias</small>
                        </div>
                        <div class="card-body" style="overflow-y: auto; max-height: 500px;">
                            <canvas id="familiasChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para los tabs */
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
.chart-container {
    position: relative;
    height: 400px;
    margin-bottom: 30px;
}
</style>
@push('scripts')
<script>
    function initFamiliasTable() {
        const table = document.getElementById('familiasTable');
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        if (tbody && tbody.rows.length === 0) return;
        
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#familiasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                order: [[4, 'desc']],
                pageLength: 25,
                searching: true,
                paging: true,
                info: true
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFamiliasTable);
    } else {
        initFamiliasTable();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartGruposMadre = null;
    let chartFamilias = null;

    // Datos desde PHP
    const familiasData = @json($familias);
    const gruposMadreData = @json($gruposMadre);
    const totalGeneral = {{ $totalGeneral }};

    // Función para dibujar gráfica de grupos madre (pastel)
    function dibujarGraficaGruposMadre() {
        const ctx = document.getElementById('gruposMadreChart').getContext('2d');
        
        if (chartGruposMadre) chartGruposMadre.destroy();
        
        if (!gruposMadreData || gruposMadreData.length === 0) {
            return;
        }
        
        const montos = gruposMadreData.map(g => parseFloat(g.monto_total) || 0);
        const totalMontos = montos.reduce((a, b) => a + b, 0);
        
        // Labels CON porcentaje
        const labelsConPorcentaje = gruposMadreData.map(g => {
            const porcentaje = totalMontos > 0 ? (parseFloat(g.monto_total) / totalMontos) * 100 : 0;
            return `${g.descripciongrupomadre} (${porcentaje.toFixed(1)}%)`;
        });
        
        chartGruposMadre = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labelsConPorcentaje,
                datasets: [{
                    data: montos,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                // Mostrar solo el valor, sin repetir el porcentaje
                                return `$${context.raw.toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Función para dibujar gráfica de familias (barras horizontales)
    function dibujarGraficaFamilias() {
        const ctx = document.getElementById('familiasChart').getContext('2d');
        
        if (chartFamilias) chartFamilias.destroy();
        
        if (!familiasData || familiasData.length === 0) {
            return;
        }
        
        const LIMITE_FAMILIAS = 20;
        const topFamilias = [...familiasData]
            .sort((a, b) => b.monto_total - a.monto_total)
            .slice(0, LIMITE_FAMILIAS);
        
        const labels = topFamilias.map(f => f.nombre_familia);
        const montos = topFamilias.map(f => f.monto_total);
        
        chartFamilias = new Chart(ctx, {
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
                            text: 'Familia'
                        }
                    }
                }
            }
        });
    }

    // Función para redibujar gráficas (al cambiar de tab)
    function redibujarGraficas() {
        if (document.getElementById('graficas').classList.contains('active')) {
            setTimeout(() => {
                dibujarGraficaGruposMadre();
                dibujarGraficaFamilias();
            }, 100);
        }
    }

    // Observar cambio de tab
    document.querySelectorAll('#graficoTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            if (event.target.getAttribute('data-bs-target') === '#graficas') {
                // Pequeño retraso para que el canvas se renderice
                setTimeout(() => {
                    dibujarGraficaGruposMadre();
                    dibujarGraficaFamilias();
                }, 100);
            }
        });
    });

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('graficas').classList.contains('active')) {
            dibujarGraficaGruposMadre();
            dibujarGraficaFamilias();
        }
    });
</script>
@endpush
@endsection