@extends('layouts.app')

@section('title', 'Productos del Ticket - ' . $ticket)

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
                        <a href="{{ route('reportes.compras_cliente.montos-promedio-compra.detalle', [
                            'id' => $cliente->id_Cliente,
                            'top' => request('top', 'todos'),
                            'sort_by' => request('sort_by', 'monto_promedio'),
                            'filtro_fecha' => request('filtro_fecha', 'este_ano'),
                            'fecha_inicio' => request('fecha_inicio'),
                            'fecha_fin' => request('fecha_fin'),
                            'search_cliente' => request('search_cliente', $searchCliente ?? '')
                        ]) }}" class="btn btn-secondary btn-sm">
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
                    <h3>{{ $productos->count() }}</h3>
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
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            <tr>
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