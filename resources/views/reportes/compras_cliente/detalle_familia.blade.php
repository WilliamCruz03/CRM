@extends('layouts.app')

@section('title', 'Productos - ' . ($familia->descripcionfamilia ?? 'Familia'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Productos de la familia: <strong>{{ $familia->descripcionfamilia ?? 'Familia' }}</strong>
                        <br>
                        <small>Cliente: {{ $cliente->nombre_completo }}</small>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.compras_cliente.cliente.detalle', [
                            'id' => $cliente->id_Cliente,
                            'top' => request('top', 'todos'),
                            'sort_by' => request('sort_by', 'monto_total'),
                            'filtro_fecha' => request('filtro_fecha', 'este_mes'),
                            'fecha_inicio' => request('fecha_inicio'),
                            'fecha_fin' => request('fecha_fin'),
                            'indicacion_id' => request('indicacion_id')
                        ]) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar a Familias
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
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($productos->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron productos para esta familia en el período seleccionado.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th>Ventas</th>
                                <th>Cantidad Vendida</th>
                                <th>Monto Total</th>
                                <th>Precio Promedio</th>
                                <th>Última Venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr>
                                <td>{{ $producto->ean }}</td>
                                <td>{{ $producto->descripcion }}</td>
                                <td style="text-align: center">{{ number_format($producto->transacciones) }}</td>
                                <td style="text-align: center">{{ number_format($producto->cantidad_vendida) }}</td>
                                <td style="text-align: right">${{ number_format($producto->monto_total, 2) }}</td>
                                <td style="text-align: right">${{ number_format($producto->precio_promedio, 2) }}</td>
                                <td style="text-align: center">{{ \Carbon\Carbon::parse($producto->ultima_venta)->format('d/m/Y') }}</td>
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
                order: [[4, 'desc']],
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