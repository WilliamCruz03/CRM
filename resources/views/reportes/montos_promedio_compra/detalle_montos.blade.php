@extends('layouts.app')

@section('title', 'Historial de Compras - ' . $cliente->nombre_completo)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Historial de Compras: <strong>{{ $cliente->nombre_completo }}</strong>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.compras_cliente.montos-promedio-compra', array_merge(
                            request()->except(['page', 'search_cliente']),
                            [
                                'top' => $top ?? 'todos',
                                'sort_by' => $sortBy ?? 'monto_promedio',
                                'filtro_fecha' => $filtroFecha ?? 'este_ano',
                                'fecha_inicio' => $fechaInicio,
                                'fecha_fin' => $fechaFin,
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
                @if($compras->isEmpty())
                    <br><span class="text-warning"><i class="bi bi-exclamation-triangle text-warning"></i> No hay compras en este período para este cliente.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Resumen del Cliente -->
    <div class="row">
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ number_format($totalCompras) }}</h3>
                    <p>Total de Compras</p>
                </div>
                <div class="icon">
                    <i class="bi bi-receipt text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>${{ number_format($montoTotal, 2) }}</h3>
                    <p>Monto Total</p>
                </div>
                <div class="icon">
                    <i class="bi bi-currency-dollar text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>${{ number_format($montoPromedio, 2) }}</h3>
                    <p>Monto Promedio</p>
                </div>
                <div class="icon">
                    <i class="bi bi-calculator text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</h3>
                    <p>Período de Análisis</p>
                </div>
                <div class="icon">
                    <i class="bi bi-calendar text-primary"></i>
                </div>
            </div>
        </div>
    </div>

<!-- Tabla de Compras (Tickets) -->
<div class="card">
    <div class="card-header">
        <h5>Compras por Ticket</h5>
    </div>
    <div class="card-body">
        @if($compras->isEmpty())
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i>
                No se encontraron compras para este cliente en el período seleccionado.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="ticketsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Ticket</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($compras as $index => $compra)
                        <tr>
                            <td style="text-align: center">{{ $index + 1 }}</td>
                            <td style="text-align: center">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                            <td style="text-align: center">{{ $compra->ticket }}</td>
                            <td style="text-align: right">${{ number_format($compra->monto, 2) }}</td>
                            <td style="text-align: center">
                                <a href="{{ route('reportes.compras_cliente.montos-promedio-compra.productos', [
                                    'clienteId' => $cliente->id_Cliente,
                                    'ticket' => $compra->ticket,
                                    'top' => $top ?? 'todos',
                                    'sort_by' => $sortBy ?? 'monto_promedio',
                                    'filtro_fecha' => $filtroFecha ?? 'este_ano',
                                    'fecha_inicio' => $fechaInicio,
                                    'fecha_fin' => $fechaFin
                                ]) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-box-seam"></i> Ver Productos
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

@push('scripts')
<script>
    function initComprasTable() {
        const table = document.getElementById('comprasTable');
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        if (tbody && tbody.rows.length === 0) return;
        
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#comprasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                order: [[1, 'desc']],
                pageLength: 25,
                searching: true,
                paging: true,
                info: true
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initComprasTable);
    } else {
        initComprasTable();
    }
</script>
@endpush
@endsection