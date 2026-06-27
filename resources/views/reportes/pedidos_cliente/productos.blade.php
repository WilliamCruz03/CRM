@extends('layouts.app')

@section('title', 'Productos del Pedido - ' . $pedido->folio_pedido)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Productos del Pedido: <strong>{{ $pedido->folio_pedido }}</strong>
                        <br>
                        <small>Cliente: {{ $cliente->nombre_completo }}</small>
                    </h3>
                    <div>
                        <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar a Pedidos
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
                <strong>Total productos:</strong> {{ $productos->count() }}
                <br>
                <strong>Total del pedido:</strong> ${{ number_format($pedido->importe_total, 2) }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($productos->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron productos para este pedido.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr>
                                <td>{{ $producto->codbar ?? $producto->ean }}</td>
                                <td>{{ $producto->descripcion }}</td>
                                <td class="text-center">{{ $producto->cantidad }}</td>
                                <td class="text-end">${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td class="text-end">${{ number_format($producto->importe, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="4" class="text-end">TOTAL:</td>
                                <td class="text-end">${{ number_format($pedido->importe_total, 2) }}</td>
                            </tr>
                        </tfoot>
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