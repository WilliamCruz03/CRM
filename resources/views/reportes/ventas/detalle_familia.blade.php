{{-- resources/views/reportes/ventas/detalle_familia.blade.php --}}
@extends('layouts.app')

@section('title', 'Productos - ' . $familia->descripcion)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Productos de <strong>{{ $familia->descripcion }}</strong><br>
                        <small>Cliente: {{ $cliente->nombre_completo }}</small>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('reportes.ventas.cliente.detalle', ['id' => $cliente->id_Cliente] + request()->except('page')) }}" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Regresar a Áreas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="productosTable">
                    <thead>
                        <tr>
                            <th>EAN</th>
                            <th>Descripción</th>
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
                            <td>{{ number_format($producto->cantidad_vendida) }}</td>
                            <td>${{ number_format($producto->monto_total, 2) }}</td>
                            <td>${{ number_format($producto->precio_promedio, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($producto->ultima_venta)->format('d/m/Y') }}</td>
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
            order: [[3, 'desc']],
            pageLength: 25
        });
    });
</script>
@endpush
@endsection