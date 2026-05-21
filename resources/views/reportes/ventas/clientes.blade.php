{{-- resources/views/reportes/ventas/clientes.blade.php --}}
@extends('layouts.app')

@section('title', 'Reporte de Clientes')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reporte de Clientes</h3>
            <div class="card-tools">
                <div class="btn-group">
                    <a href="{{ route('reportes.ventas.exportar.excel', array_merge(request()->all(), ['tipo' => 'clientes'])) }}" 
                       class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                    <a href="{{ route('reportes.ventas.exportar.pdf', array_merge(request()->all(), ['tipo' => 'clientes'])) }}" 
                       class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label>Top</label>
                        <select name="top" class="form-control">
                            <option value="10" {{ $top == 10 ? 'selected' : '' }}>Top 10</option>
                            <option value="25" {{ $top == 25 ? 'selected' : '' }}>Top 25</option>
                            <option value="50" {{ $top == 50 ? 'selected' : '' }}>Top 50</option>
                            <option value="todos" {{ $top == 'todos' ? 'selected' : '' }}>Todos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Ordenar por</label>
                        <select name="sort_by" class="form-control">
                            @foreach($sortFields as $value => $label)
                                <option value="{{ $value }}" {{ $sortBy == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Buscar cliente</label>
                        <input type="text" name="search_cliente" class="form-control" value="{{ $searchCliente }}" placeholder="Nombre o apellido">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </div>
                @include('reportes.partials.filtros_fecha', ['route' => 'reportes.ventas.clientes'])
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="clientesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Transacciones</th>
                            <th>Monto Total</th>
                            <th>Ticket Promedio</th>
                            <th>Última Compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id_Cliente }}</td>
                            <td>{{ $cliente->Nombre }} {{ $cliente->apPaterno }} {{ $cliente->apMaterno }}</td>
                            <td>{{ number_format($cliente->total_transacciones) }}</td>
                            <td>${{ number_format($cliente->monto_total, 2) }}</td>
                            <td>${{ number_format($cliente->ticket_promedio, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('reportes.ventas.cliente.detalle', ['id' => $cliente->id_Cliente] + request()->except('page')) }}" 
                                   class="btn btn-info btn-sm">
                                    <i class="fas fa-chart-pie"></i> Ver Detalle
                                </a>
                            </td>
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
        $('#clientesTable').DataTable({
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