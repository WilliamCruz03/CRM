@extends('layouts.app')

@section('title', 'Productos de Cotización - ' . $cotizacion->folio)

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
                        <a href="{{ route('reportes.cotizaciones-cliente.cliente.detalle', [
                            'id' => $cliente->id_Cliente,
                            'filtro_fecha' => $filtroFecha,
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                            'status_filter' => $statusFilter,
                            'top' => $top,
                            'sort_by' => $sortBy,
                            'search_cliente' => request('search_cliente', $searchCliente ?? '')  // ✅ Agregar
                        ]) }}" class="btn btn-secondary btn-sm">
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
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Descuento</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr>
                                <td><small>{{ $producto->codbar }}</small></td>
                                <td>{{ $producto->descripcion }}</td>
                                <td class="text-center">{{ number_format($producto->cantidad) }}</td>
                                <td class="text-end">${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td class="text-end">{{ number_format($producto->descuento, 2) }}%</td>
                                <td class="text-end">${{ number_format($producto->importe, 2) }}</td>
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
                order: [[5, 'desc']],
                pageLength: 25,
                searching: true,
                paging: true,
                info: true
            });
        }
    }
    
    document.addEventListener('DOMContentLoaded', initProductosTable);
</script>
@endpush
@endsection