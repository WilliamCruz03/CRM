@extends('layouts.app')

@section('title', 'Productos del Ticket - ' . $ticket)
@section('page-title', 'Detalle de productos por ticket')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-receipt"></i> Productos del Ticket: <strong>{{ $ticket }}</strong>
                        <br>
                        <small>Cliente: {{ $cliente->nombre_completo }}</small>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.compras_cliente.montos-promedio-compra.detalle', array_merge(request()->except(['page', 'per_page', 'orden_detalle', 'direccion_detalle']), ['id' => $cliente->id_Cliente])) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros aplicados -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row">
        <div class="col-md-4">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ $productos->total() }}</h3>
                    <p><i class="bi bi-box-seam text-primary"></i> Productos en el Ticket</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box">
                <div class="inner">
                    <h3>${{ number_format($totalMonto, 2) }}</h3>
                    <p><i class="bi bi-currency-dollar text-success"></i> Monto Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box">
                <div class="inner">
                    <h3>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h3>
                    <p><i class="bi bi-calendar text-warning"></i> Fecha del Ticket</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card">
        <div class="card-header">
            <h5>Productos</h5>
        </div>
        <div class="card-body">
            @if($productos->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron productos para este ticket.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                @php
                                $seleccionado = request('orden_detalle', 'subtotal') === 'ean';
                                $direccion = request('direccion_detalle', 'desc');
                            @endphp
                            <th @if($seleccionado) aria-sort="{{ $direccion === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_detalle' => 'ean', 'direccion_detalle' => $seleccionado && $direccion === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">EAN <i class="bi {{ $seleccionado ? ($direccion === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                            </th>
                                @php
                                $seleccionado = request('orden_detalle', 'subtotal') === 'descripcion';
                                $direccion = request('direccion_detalle', 'desc');
                            @endphp
                            <th @if($seleccionado) aria-sort="{{ $direccion === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_detalle' => 'descripcion', 'direccion_detalle' => $seleccionado && $direccion === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Descripción <i class="bi {{ $seleccionado ? ($direccion === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                            </th>
                                @php
                                $seleccionado = request('orden_detalle', 'subtotal') === 'cantidad';
                                $direccion = request('direccion_detalle', 'desc');
                            @endphp
                            <th @if($seleccionado) aria-sort="{{ $direccion === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_detalle' => 'cantidad', 'direccion_detalle' => $seleccionado && $direccion === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Cantidad <i class="bi {{ $seleccionado ? ($direccion === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                            </th>
                                @php
                                $seleccionado = request('orden_detalle', 'subtotal') === 'precio_unitario';
                                $direccion = request('direccion_detalle', 'desc');
                            @endphp
                            <th @if($seleccionado) aria-sort="{{ $direccion === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_detalle' => 'precio_unitario', 'direccion_detalle' => $seleccionado && $direccion === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Precio Unitario <i class="bi {{ $seleccionado ? ($direccion === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                            </th>
                                @php
                                $seleccionado = request('orden_detalle', 'subtotal') === 'subtotal';
                                $direccion = request('direccion_detalle', 'desc');
                            @endphp
                            <th @if($seleccionado) aria-sort="{{ $direccion === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_detalle' => 'subtotal', 'direccion_detalle' => $seleccionado && $direccion === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Subtotal <i class="bi {{ $seleccionado ? ($direccion === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                            </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr>
                                <td>{{ $producto->ean }}</td>
                                <td>{{ $producto->descripcion }}</td>
                                <td class="text-center">{{ number_format($producto->cantidad) }}</td>
                                <td class="text-right">${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td class="text-right">${{ number_format($producto->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($productos, 'total') && $productos->total() > 15)
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 paginacion-bloque">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $productos->firstItem() }}</strong> hasta <strong>{{ $productos->lastItem() }}</strong>
                            de <strong>{{ $productos->total() }}</strong> resultados
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Mostrar</span>
                                <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                                    @foreach([5, 10, 15, 20, 50] as $opcion)
                                        <option value="{{ $opcion }}" @selected($productos->perPage() === $opcion)>{{ $opcion }}</option>
                                    @endforeach
                                </select>
                                <span class="text-muted small">por página</span>
                            </div>
                            {{ $productos->appends(request()->query())->links('pagination.bootstrap-5') }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.inicializarSelectorPerPage?.('perPageSelect');
    });
</script>
@endpush
@endsection