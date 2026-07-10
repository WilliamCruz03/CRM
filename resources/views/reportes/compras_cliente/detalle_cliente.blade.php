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
                        <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
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
            <!-- Filtro de ordenamiento -->
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <span class="text-muted"><i class="bi bi-arrow-up-down"></i> Ordenar por:</span>
                        <select id="ordenarPor" class="form-select w-auto" style="width: auto;">
                            <option value="completadas">Completadas (mayor a menor)</option>
                            <option value="canceladas">Canceladas (mayor a menor)</option>
                            <option value="devoluciones">Devoluciones (mayor a menor)</option>
                            <option value="total">Subtotal (mayor a menor)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="gruposMadreTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Grupo Madre</th>
                            <th>Tickets Totales</th>
                            <th>Productos</th>
                            <th>Monto Total</th>
                            <th>Canceladas</th>
                            <th>Devoluciones</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gruposMadre as $index => $grupo)
                        <tr data-completadas="{{ $grupo->monto_total }}" 
                            data-canceladas="{{ $grupo->monto_canceladas }}" 
                            data-devoluciones="{{ $grupo->monto_devoluciones }}"
                            data-subtotal="{{ $grupo->subtotal }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $grupo->descripciongrupomadre }}</td>
                            <td class="text-center">{{ number_format($grupo->transacciones) }}</td>
                            <td class="text-center">{{ number_format($grupo->cantidad_productos) }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-success fw-bold">${{ number_format($grupo->monto_total, 2) }}</span>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                            style="width: {{ $grupo->porc_completadas }}%;"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($grupo->porc_completadas, 1) }}%</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-danger fw-bold">${{ number_format($grupo->monto_canceladas, 2) }}</span>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                            style="width: {{ $grupo->porc_canceladas }}%;"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($grupo->porc_canceladas, 1) }}%</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-secondary fw-bold">${{ number_format($grupo->monto_devoluciones, 2) }}</span>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                            style="width: {{ $grupo->porc_devoluciones }}%;"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($grupo->porc_devoluciones, 1) }}%</small>
                                </div>
                            </td>
                            <td class="fw-bold text-primary">${{ number_format($grupo->subtotal, 2) }}</td>
                            <td>
                                <a href="{{ route('reportes.compras_cliente.cliente.grupo-madre', [
                                    'clienteId' => $cliente->id_Cliente,
                                    'grupoMadreId' => $grupo->id_grupo_madre,
                                    'top' => $top ?? 'todos',
                                    'sort_by' => $sortBy ?? 'monto_total',
                                    'filtro_fecha' => $filtroFecha ?? 'este_mes',
                                    'fecha_inicio' => $fechaInicio,
                                    'fecha_fin' => $fechaFin,
                                    'search_cliente' => $searchCliente ?? '',
                                    'indicacion_id' => $indicacionId ?? ''
                                ]) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-box-seam"></i> Ver Productos
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
    .progress {
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
</style>

@push('scripts')
            <!-- JavaScript para ordenamiento -->
<script>
    document.getElementById('ordenarPor')?.addEventListener('change', function() {
        const valor = this.value;
        const tbody = document.querySelector('#gruposMadreTable tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
                
    rows.sort((a, b) => {
        let aVal = 0, bVal = 0;
            switch(valor) {
                case 'completadas':
                        aVal = parseFloat(a.dataset.completadas) || 0;
                        bVal = parseFloat(b.dataset.completadas) || 0;
                    break;
                case 'canceladas':
                        aVal = parseFloat(a.dataset.canceladas) || 0;
                        bVal = parseFloat(b.dataset.canceladas) || 0;
                    break;
                case 'devoluciones':
                        aVal = parseFloat(a.dataset.devoluciones) || 0;
                        bVal = parseFloat(b.dataset.devoluciones) || 0;
                    break;
                default: // subtotal
                        aVal = parseFloat(a.dataset.subtotal) || 0;
                    bVal = parseFloat(b.dataset.subtotal) || 0;
            }
            return bVal - aVal;
        });
                
        rows.forEach(row => tbody.appendChild(row));
    });
</script>

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
        // 1. Verificar si venimos de grupo madre (estado guardado)
        const estadoGuardado = sessionStorage.getItem('reporte_compras_cliente_estado');
        
        if (estadoGuardado) {
            try {
                const estado = JSON.parse(estadoGuardado);
                // Si el estado existe y tiene filtros, lo mantenemos para que clientes lo use
                // No eliminamos el estado aquí, lo dejamos para que clientes lo consuma
                if (estado.filtros) {
                    // Solo logueamos que tenemos estado, no hacemos nada más
                    console.log('Estado de filtros disponible para regresar a clientes');
                }
            } catch (e) {
                console.error('Error al procesar estado:', e);
                // Si hay error, limpiar el estado para evitar problemas
                sessionStorage.removeItem('reporte_compras_cliente_estado');
            }
        }
        
        // 2. Dibujar gráficas si están activas
        if (document.getElementById('graficas')?.classList.contains('active')) {
            dibujarGraficaGruposMadre();
            dibujarGraficaFamilias();
        }
        
        // 3. Observar cambio de tab para redibujar gráficas
        document.querySelectorAll('#graficoTabs .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                if (event.target.getAttribute('data-bs-target') === '#graficas') {
                    setTimeout(() => {
                        dibujarGraficaGruposMadre();
                        dibujarGraficaFamilias();
                    }, 100);
                }
            });
        });
    });
</script>
@endpush
@endsection