{{-- resources/views/reportes/ventas/frecuencia_compra.blade.php --}}
@extends('layouts.app')

@section('title', 'Frecuencia de Compra por Cliente')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Frecuencia de Compra por Cliente</h3>
            <div class="card-tools">
                <a href="{{ route('reportes.ventas.exportar.excel', array_merge(request()->all(), ['tipo' => 'frecuencia_compra'])) }}" 
                   class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
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
                            <option value="todos" {{ $top == 'todos' ? 'selected' : '' }}>Todos</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Buscar cliente</label>
                        <input type="text" name="search_cliente" class="form-control" 
                               value="{{ $searchCliente }}" placeholder="Nombre o apellido">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </div>
                @include('reportes.partials.filtros_fecha', ['route' => 'reportes.ventas.frecuencia-compra'])
            </form>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Interpretación:</strong> La frecuencia promedio indica cada cuántos días realiza una compra el cliente. 
                Valores más bajos significan mayor frecuencia de compra.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="frecuenciaTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Total Compras</th>
                            <th>Días con Compra</th>
                            <th>Frecuencia Promedio (días)</th>
                            <th>Monto Total</th>
                            <th>Ticket Promedio</th>
                            <th>Primera Compra</th>
                            <th>Última Compra</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $index => $cliente)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $cliente->Nombre }} {{ $cliente->apPaterno }} {{ $cliente->apMaterno }}</td>
                            <td>{{ number_format($cliente->total_compras) }}</td>
                            <td>{{ number_format($cliente->dias_con_compra) }}</td>
                            <td>
                                @if($cliente->frecuencia_promedio > 0)
                                    {{ number_format($cliente->frecuencia_promedio, 1) }}
                                    <div class="progress mt-1" style="height: 5px;">
                                        @php
                                            $porcentaje = min(100, ($cliente->frecuencia_promedio / 30) * 100);
                                        @endphp
                                        <div class="progress-bar bg-{{ $cliente->frecuencia_promedio <= 7 ? 'success' : ($cliente->frecuencia_promedio <= 15 ? 'warning' : 'danger') }}" 
                                             role="progressbar" style="width: {{ $porcentaje }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        @if($cliente->frecuencia_promedio <= 7)
                                            Frecuente
                                        @elseif($cliente->frecuencia_promedio <= 15)
                                            Regular
                                        @else
                                            Esporádico
                                        @endif
                                    </small>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>${{ number_format($cliente->monto_total, 2) }}</td>
                            <td>${{ number_format($cliente->monto_total / $cliente->total_compras, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($cliente->primera_compra)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}</td>
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
        $('#frecuenciaTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            order: [[4, 'asc']], // Ordenar por frecuencia (menor a mayor)
            pageLength: 25
        });
    });
</script>
@endpush
@endsection