@extends('layouts.app')

@section('title', 'Productos - ' . ($grupoMadre->descripciongrupomadre ?? 'Grupo Madre'))

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
                        <a href="{{ route('reportes.compras_cliente.cliente.detalle', [
                            'id' => $cliente->id_Cliente,
                            'top' => $top ?? 'todos',
                            'sort_by' => $sortBy ?? 'monto_total',
                            'filtro_fecha' => $filtroFecha ?? 'este_mes',
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                            'search_cliente' => $searchCliente ?? '',
                            'indicacion_id' => $indicacionId ?? ''
                        ]) }}" class="btn btn-secondary btn-sm">
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
                <strong>Total productos encontrados:</strong> {{ $productos->count() }}
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
                <!-- Filtro de ordenamiento -->
                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <span class="text-muted"><i class="bi bi-arrow-up-down"></i> Ordenar por:</span>
                            <select id="ordenarPorProductos" class="form-select w-auto" style="width: auto;">
                                <option value="completadas" selected>Completadas (mayor a menor)</option>
                                <option value="canceladas">Canceladas (mayor a menor)</option>
                                <option value="devoluciones">Devoluciones (mayor a menor)</option>
                                <option value="total">Subtotal (mayor a menor)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead>
                            <tr>
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th>Familia</th>
                                <th>Cantidad</th>
                                <th>Monto Total</th>
                                <th>Canceladas</th>
                                <th>Devoluciones</th>
                                <th>Subtotal</th>
                                <th>Última Venta</th>
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
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('ordenarPorProductos')?.addEventListener('change', function () {
        const valor = this.value;
        const tbody = document.querySelector('#productosTable tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            let aVal = 0, bVal = 0;
            switch (valor) {
                case 'completadas':
                    aVal = parseFloat(a.dataset.completadas) || 0;
                    bVal = parseFloat(b.dataset.completadas) || 0;
                    break;
                case 'canceladas':
                    aVal = parseFloat(a.dataset.canceladas) || 0;
                    bVal = parseFloat(b.dataset.canceladas) || 0;
                    break;
                case 'devoluciones':
                    aVal = parseFloat(a.dataset.devoluciones) || 0;
                    bVal = parseFloat(b.dataset.devoluciones) || 0;
                    break;
                default:
                    aVal = parseFloat(a.dataset.subtotal) || 0;
                    bVal = parseFloat(b.dataset.subtotal) || 0;
            }
            return bVal - aVal;
        });

        rows.forEach(row => tbody.appendChild(row));
    });
</script>

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