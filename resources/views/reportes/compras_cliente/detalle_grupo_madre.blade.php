@extends('layouts.app')

@section('title', 'Productos - ' . ($grupoMadre->descripciongrupomadre ?? 'Grupo Madre'))
@section('page-title', 'Detalle de compras por grupo madre')

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
                        <a href="{{ route('reportes.compras_cliente.cliente.detalle', array_merge(request()->except(['page', 'per_page', 'orden_productos', 'direccion_productos']), ['id' => $cliente->id_Cliente])) }}" class="btn btn-secondary btn-sm">
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
                <strong>Total productos encontrados:</strong> {{ method_exists($productos, 'total') ? $productos->total() : $productos->count() }}
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
                <!-- Tabla -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'ean';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'ean', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">EAN <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'descripcion';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'descripcion', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Descripción <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'nombre_familia';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'nombre_familia', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Familia <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'cantidad_vendida';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'cantidad_vendida', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Cantidad Articulos <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'monto_total';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'monto_total', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Monto Total <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'monto_canceladas';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'monto_canceladas', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Canceladas <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'monto_devoluciones';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'monto_devoluciones', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Devoluciones <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'subtotal';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'subtotal', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Subtotal <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                                @php
                                    $ordenActual = request('orden_productos', 'monto_total');
                                    $direccionActual = request('direccion_productos', 'desc');
                                    $seleccionado = $ordenActual === 'ultima_venta';
                                @endphp
                                <th @if($seleccionado) aria-sort="{{ $direccionActual === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a class="text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['orden_productos' => 'ultima_venta', 'direccion_productos' => $seleccionado && $direccionActual === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">Última Venta <i class="bi {{ $seleccionado ? ($direccionActual === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr data-completadas="{{ $producto->monto_total }}" 
                                data-canceladas="{{ $producto->monto_canceladas }}" 
                                data-devoluciones="{{ $producto->monto_devoluciones }}"
                                data-subtotal="{{ $producto->subtotal }}">
                                <td>{{ $producto->ean }}</td>
                                <td>{{ $producto->descripcion }}</td>
                                <td>{{ $producto->nombre_familia ?? 'Sin Familia' }}</td>
                                <td class="text-center">{{ number_format($producto->cantidad_vendida) }}</td>
                                <td class="text-right text-success fw-bold">${{ number_format($producto->monto_total, 2) }}</td>
                                <td class="text-right text-danger fw-bold">${{ number_format($producto->monto_canceladas, 2) }}</td>
                                <td class="text-right text-secondary fw-bold">${{ number_format($producto->monto_devoluciones, 2) }}</td>
                                <td class="text-right fw-bold text-primary">${{ number_format($producto->subtotal, 2) }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($producto->ultima_venta)->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($productos, 'total'))
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

<script>
    // Guardar estado antes de regresar a detalle_cliente
    function guardarEstadoParaRegresar() {
        const urlParams = new URLSearchParams(window.location.search);
        const estado = {
            filtros: {
                top: urlParams.get('top') || '{{ $top ?? "todos" }}',
                sortBy: urlParams.get('sort_by') || '{{ $sortBy ?? "monto_total" }}',
                filtroFecha: urlParams.get('filtro_fecha') || '{{ $filtroFecha ?? "este_mes" }}',
                fechaInicio: urlParams.get('fecha_inicio') || '{{ $fechaInicio ?? "" }}',
                fechaFin: urlParams.get('fecha_fin') || '{{ $fechaFin ?? "" }}',
                clienteId: urlParams.get('search_cliente') || '{{ $searchCliente ?? "" }}',
                indicacionId: urlParams.get('indicacion_id') || '{{ $indicacionId ?? "" }}'
            },
            desdeDetalle: true
        };
        sessionStorage.setItem('reporte_compras_cliente_estado', JSON.stringify(estado));
        // Dejar que el enlace haga la navegación normalmente
        return true;
    }
</script>
@endpush
@endsection