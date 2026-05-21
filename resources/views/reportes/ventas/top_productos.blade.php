{{-- resources/views/reportes/ventas/top_productos.blade.php --}}
@extends('layouts.app')

@section('title', 'Top Productos')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top Productos más Vendidos</h3>
            <div class="card-tools">
                <div class="btn-group">
                    <a href="{{ route('reportes.ventas.exportar.excel', array_merge(request()->all(), ['tipo' => 'top-productos'])) }}" 
                       class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                    <a href="{{ route('reportes.ventas.exportar.pdf', array_merge(request()->all(), ['tipo' => 'top-productos'])) }}" 
                       class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label>Top</label>
                        <select name="top" class="form-control">
                            <option value="10" {{ $top == 10 ? 'selected' : '' }}>Top 10</option>
                            <option value="25" {{ $top == 25 ? 'selected' : '' }}>Top 25</option>
                            <option value="50" {{ $top == 50 ? 'selected' : '' }}>Top 50</option>
                            <option value="100" {{ $top == 100 ? 'selected' : '' }}>Top 100</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Ordenar por</label>
                        <select name="orden" class="form-control">
                            <option value="monto" {{ $orden == 'monto' ? 'selected' : '' }}>Monto Total</option>
                            <option value="cantidad" {{ $orden == 'cantidad' ? 'selected' : '' }}>Cantidad Vendida</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </div>
                @include('reportes.partials.filtros_fecha', ['route' => 'reportes.ventas.top-productos'])
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="productosTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>EAN</th>
                            <th>Descripción</th>
                            <th>Cantidad Vendida</th>
                            <th>Monto Total</th>
                            <th>Clientes Distintos</th>
                            <th>Ticket Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $index => $producto)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $producto->ean }}</td>
                            <td>{{ $producto->descripcion }}</td>
                            <td>{{ number_format($producto->cantidad_vendida) }}</td>
                            <td>${{ number_format($producto->monto_total, 2) }}</td>
                            <td>{{ number_format($producto->clientes_distintos) }}</td>
                            <td>${{ number_format($producto->monto_total / $producto->cantidad_vendida, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#productosTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            order: [[4, 'desc']],
            pageLength: 25
        });
    });
</script>
@endpush
@endsection