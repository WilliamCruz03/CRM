@extends('layouts.app')

@section('title', 'Productos - ' . ($grupoMadre->descripciongrupomadre ?? 'Grupo Madre'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Productos del grupo madre: <strong>{{ $grupoMadre->descripciongrupomadre ?? 'Grupo Madre' }}</strong>
                        <br>
                        <small>Cliente: {{ $cliente->nombre_completo }}</small>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.compras_cliente.cliente.detalle', [
                            'id' => $cliente->id_Cliente,
                            'top' => $top ?? 'todos',
                            'sort_by' => $sortBy ?? 'monto_total',
                            'filtro_fecha' => $filtroFecha ?? 'este_mes',
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                            'indicacion_id' => $indicacionId ?? request('indicacion_id'),
                            'search_cliente' => request('search_cliente')
                        ]) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar a Grupos Madre
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                <br>
                <strong>Total productos encontrados:</strong> {{ $productos->count() }}
                <br>
                <strong>Total gastado en este grupo madre:</strong> ${{ number_format($totalGeneral, 2) }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($productos->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron productos para este grupo madre en el período seleccionado.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th>Familia</th>
                                <th>Cantidad Vendida</th>
                                <th>Monto Total</th>
                                <th>Última Venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr>
                                <td>{{ $producto->ean }}</td>
                                <td>{{ $producto->descripcion }}</td>
                                <td>{{ $producto->nombre_familia ?? 'Sin Familia' }}</td>
                                <td class="text-center">{{ number_format($producto->cantidad_vendida) }}</td>
                                <td class="text-right">${{ number_format($producto->monto_total, 2) }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($producto->ultima_venta)->format('d/m/Y') }}</td>
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
    function initProductosTable() {
        const table = document.getElementById('productosTable');
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        if (tbody && tbody.rows.length === 0) return;
        
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#productosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                order: [[4, 'desc']], // Ordenar por Monto Total
                pageLength: 25,
                searching: true,
                paging: true,
                info: true
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductosTable);
    } else {
        initProductosTable();
    }
</script>
@endpush
@endsection