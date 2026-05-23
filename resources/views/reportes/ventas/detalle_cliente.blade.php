{{-- resources/views/reportes/ventas/detalle_cliente.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle de Compras - ' . $cliente->nombre_completo)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Detalle de Compras: <strong>{{ $cliente->nombre_completo }}</strong>
                    </h3>
                    <div>
                        <a href="{{ route('reportes.ventas.clientes', request()->except('page')) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mostrar los filtros aplicados --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Filtros aplicados:</strong>
                Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                @if($productos->isEmpty())
                    <br><span class="text-warning"><i> </i>No hay ventas en este período para este cliente.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Resumen del Cliente -->
    <div class="row">
        <div class="col-md-3">
            <div>
                <div class="inner">
                    <h3>${{ number_format($totalGeneral, 2) }}</h3>
                    <p>Total General</p>
                </div>
                <div class="icon">
                    <i class="bi bi-graph-up text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div>
                <div class="inner">
                    <h3>{{ $productos->count() }}</h3>
                    <p>Productos Comprados</p>
                </div>
                <div class="icon">
                    <i class="bi bi-box-seam text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div>
                <div class="inner">
                    <h3>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</h3>
                    <p>Período de Análisis</p>
                </div>
                <div class="icon">
                    <i class="bi bi-calendar text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card">
        <div class="card-header">
            <h5>Productos Comprados</h5>
        </div>
        <div class="card-body">
            @if($productos->isEmpty())
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron productos para este cliente en el período seleccionado.
                    <br>
                    <small>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th>Transacciones</th>
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
    console.log('=== DETALLE CLIENTE ===');
    console.log('Cliente ID:', {{ $cliente->id_Cliente }});
    console.log('ID Tarjeta Cliente Frecuente:', '{{ $cliente->idtarjetaclientefrecuente }}');
    console.log('Fecha Inicio:', '{{ $fechaInicio }}');
    console.log('Fecha Fin:', '{{ $fechaFin }}');
    console.log('Total Productos:', {{ $productos->count() }});
    
    @if($productos->count() > 0)
        console.log('Primer producto:', {
            ean: '{{ $productos[0]->ean ?? "N/A" }}',
            descripcion: '{{ $productos[0]->descripcion ?? "N/A" }}',
            monto_total: {{ $productos[0]->monto_total ?? 0 }}
        });
    @else
        console.warn('No se encontraron productos para el período seleccionado');
        console.log('Verificar:');
        console.log('1. Que el cliente tenga ventas en historial_ventas_matriz con IDCLIENTE =', '{{ $cliente->idtarjetaclientefrecuente }}');
        console.log('2. Que las fechas estén entre', '{{ $fechaInicio }}', 'y', '{{ $fechaFin }}');
        console.log('3. Que los productos tengan EAN válido en catalogo_general');
        
        // Consulta de depuración - sugerencia SQL
        console.log('SQL sugerida para depurar:');
        console.log(`SELECT COUNT(*) FROM fp_central_ventas.dbo.historial_ventas_matriz WHERE IDCLIENTE = '{{ $cliente->idtarjetaclientefrecuente }}' AND FECHA_DT BETWEEN '{{ $fechaInicio }}' AND '{{ $fechaFin }}'`);
    @endif
    
    function initProductosTable() {
        const table = document.getElementById('productosTable');
        if (!table) {
            console.error('No se encontró la tabla productosTable');
            return;
        }
        
        const tbody = table.querySelector('tbody');
        if (tbody && tbody.rows.length === 0) {
            console.log('Tabla sin datos, no se inicializa DataTable');
            return;
        }
        
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            console.log('Inicializando DataTable para productos');
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
        } else {
            console.warn('DataTable no disponible, mostrando tabla simple');
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