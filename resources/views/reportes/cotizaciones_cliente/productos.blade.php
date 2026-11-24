@extends('layouts.app')

@section('title', 'Productos de Cotización - ' . $cotizacion->folio)
@section('page-title', 'Detalle de productos de cotizacion')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-box-seam"></i> Productos de la cotización: <strong>{{ $cotizacion->folio }}</strong>
                        <br>
                        <small>Cliente: {{ $cliente->nombre_completo }}</small>
                    </h3>
                    <div>
                        <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar a Cotizaciones
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
                <strong>Información de la cotización:</strong>
                Fecha: {{ \Carbon\Carbon::parse($cotizacion->fecha_creacion)->format('d/m/Y H:i') }} |
                Importe total: <strong>${{ number_format($cotizacion->importe_total, 2) }}</strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($productos->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i>
                    No hay productos en esta cotización.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                <th><button type="button" class="btn btn-sm btn-link text-reset text-decoration-none p-0" data-orden-detalle="0">Código <i class="bi bi-arrow-down-up" aria-hidden="true"></i></button></th>
                                <th><button type="button" class="btn btn-sm btn-link text-reset text-decoration-none p-0" data-orden-detalle="1">Descripción <i class="bi bi-arrow-down-up" aria-hidden="true"></i></button></th>
                                <th class="text-center"><button type="button" class="btn btn-sm btn-link text-reset text-decoration-none p-0" data-orden-detalle="2">Cantidad <i class="bi bi-arrow-down-up" aria-hidden="true"></i></button></th>
                                <th class="text-end"><button type="button" class="btn btn-sm btn-link text-reset text-decoration-none p-0" data-orden-detalle="3">Precio Unitario <i class="bi bi-arrow-down-up" aria-hidden="true"></i></button></th>
                                <th class="text-end"><button type="button" class="btn btn-sm btn-link text-reset text-decoration-none p-0" data-orden-detalle="4">Descuento <i class="bi bi-arrow-down-up" aria-hidden="true"></i></button></th>
                                <th class="text-end"><button type="button" class="btn btn-sm btn-link text-reset text-decoration-none p-0" data-orden-detalle="5">Importe <i class="bi bi-arrow-down-up" aria-hidden="true"></i></button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr>
                                <td><small>{{ $producto->codbar }}</small></td>
                                <td>{{ $producto->descripcion }}</td>
                                <td class="text-center" data-valor="{{ $producto->cantidad }}">{{ number_format($producto->cantidad) }}</td>
                                <td class="text-end" data-valor="{{ $producto->precio_unitario }}">${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td class="text-end" data-valor="{{ $producto->descuento }}">{{ number_format($producto->descuento, 2) }}%</td>
                                <td class="text-end" data-valor="{{ $producto->importe }}">${{ number_format($producto->importe, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold">${{ number_format($cotizacion->importe_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/reporte-tabla-detalle.js') }}?v={{ filemtime(public_path('js/reporte-tabla-detalle.js')) }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.inicializarTablaDetalleReporte(document.getElementById('productosTable'), {columnaInicial: 5, numericas: [2, 3, 4, 5]});
    });
</script>
@endpush
@endsection