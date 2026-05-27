{{-- resources/views/reportes/ventas/detalle_cliente.blade.php --}}
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
                        <a href="{{ route('reportes.ventas.clientes', array_merge(
                            request()->except('page'),
                            ['indicacion_id' => request('indicacion_id')]
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
                @if($familias->isEmpty())
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
                    <p>Total General</p>
                </div>
                <div class="icon">
                    <i class="bi bi-graph-up text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ $familias->count() }}</h3>
                    <p>Familias Compradas</p>
                </div>
                <div class="icon">
                    <i class="bi bi-tags text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</h3>
                    <p>Período de Análisis</p>
                </div>
                <div class="icon">
                    <i class="bi bi-calendar text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Familias -->
    <div class="card">
        <div class="card-header">
            <h5>Compras por Familia</h5>
        </div>
        <div class="card-body">
            @if($familias->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron productos para este cliente en el período seleccionado.
                    <br>
                    <small>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="familiasTable">
                        <thead>
                            <tr>
                                <th>Familia</th>
                                <th>Grupo</th>
                                <th>Transacciones</th>
                                <th>Cantidad Productos</th>
                                <th>Monto Total</th>
                                <th>% del Total</th>
                                <th>Ticket Promedio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($familias as $familia)
                            <tr>
                                <td>{{ $familia->nombre_familia }}</td>
                                <td>{{ $familia->descripciongrupo ?? 'N/A' }}</td>
                                <td style="text-align: center">{{ number_format($familia->transacciones) }}</td>
                                <td style="text-align: center">{{ number_format($familia->cantidad_productos) }}</td>
                                <td style="text-align: right">${{ number_format($familia->monto_total, 2) }}</td>
                                <td style="text-align: center; min-width: 120px;">
                                    <div class="progress" style="height: 24px; background-color: #e9ecef; border-radius: 4px; position: relative;">
                                        <div class="progress-bar" role="progressbar" 
                                            style="width: {{ $totalGeneral > 0 ? ($familia->monto_total / $totalGeneral) * 100 : 0 }}%; 
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
                                                    color: {{ ($familia->monto_total / $totalGeneral) * 100 > 40 ? 'white' : '#212529' }};">
                                            {{ number_format(($familia->monto_total / $totalGeneral) * 100, 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: right">${{ number_format($familia->ticket_promedio, 2) }}</td>
                                <td style="text-align: center">
                                    <a href="{{ route('reportes.ventas.cliente.familia', [
                                        'clienteId' => $cliente->id_Cliente, 
                                        'familiaId' => $familia->num_familia,
                                        'top' => request('top', 'todos'),
                                        'sort_by' => request('sort_by', 'monto_total'),
                                        'filtro_fecha' => request('filtro_fecha', 'este_mes'),
                                        'fecha_inicio' => request('fecha_inicio', $fechaInicio),
                                        'fecha_fin' => request('fecha_fin', $fechaFin),
                                        'indicacion_id' => request('indicacion_id')
                                    ]) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-boxes"></i> Ver Productos
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

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
@endpush
@endsection