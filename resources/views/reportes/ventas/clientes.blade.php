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
            <form method="GET" class="mb-4" id="filtrosForm">
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
                    <div class="col-md-3">
                        <label>Ordenar por</label>
                        <select name="sort_by" class="form-control">
                            <option value="monto_total" {{ $sortBy == 'monto_total' ? 'selected' : '' }}>Mayor Monto</option>
                            <option value="monto_total_asc" {{ $sortBy == 'monto_total_asc' ? 'selected' : '' }}>Menor Monto</option>
                            <option value="total_transacciones" {{ $sortBy == 'total_transacciones' ? 'selected' : '' }}>Más Compras</option>
                            <option value="total_transacciones_asc" {{ $sortBy == 'total_transacciones_asc' ? 'selected' : '' }}>Menos Compras</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Buscar cliente</label>
                        <input type="text" name="search_cliente" class="form-control" value="{{ $searchCliente }}" placeholder="Nombre o apellido">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block" name="aplicar" value="1">Aplicar Filtros</button>
                    </div>
                </div>

                <!-- Filtros de Fecha -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h5 class="card-title">Filtros de Fecha</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Rápido:</label>
                                        <select name="filtro_fecha" id="filtroFecha" class="form-control">
                                            <option value="">Seleccione un período</option>
                                            <option value="hoy" {{ request('filtro_fecha') == 'hoy' ? 'selected' : '' }}>Hoy</option>
                                            <option value="esta_semana" {{ request('filtro_fecha') == 'esta_semana' ? 'selected' : '' }}>Esta semana</option>
                                            <option value="este_mes" {{ request('filtro_fecha') == 'este_mes' ? 'selected' : '' }}>Este mes</option>
                                            <option value="este_ano" {{ request('filtro_fecha') == 'este_ano' ? 'selected' : '' }}>Este año</option>
                                            <option value="personalizado" {{ request('filtro_fecha') == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3" id="fechaInicioDiv" style="display: {{ request('filtro_fecha') == 'personalizado' ? 'block' : 'none' }}">
                                        <label>Fecha Inicio:</label>
                                        <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio', $fechaInicio ?? '') }}">
                                    </div>
                                    <div class="col-md-3" id="fechaFinDiv" style="display: {{ request('filtro_fecha') == 'personalizado' ? 'block' : 'none' }}">
                                        <label>Fecha Fin:</label>
                                        <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin', $fechaFin ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-secondary btn-block" name="aplicar" value="1">Aplicar Fechas</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Mostrar mensaje si no hay datos -->
            @if($clientes->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> 
                    No se encontraron ventas en el período seleccionado.
                </div>
            @else
                <!-- Tabla de resultados -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
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
                                <td>{{ trim($cliente->Nombre . ' ' . $cliente->apPaterno . ' ' . ($cliente->apMaterno ?? '')) }}</td>
                                <td>{{ number_format($cliente->total_transacciones) }}</td>
                                <td>${{ number_format($cliente->monto_total, 2) }}</td>
                                <td>${{ number_format($cliente->ticket_promedio, 2) }}</td>
                                <td>{{ $cliente->ultima_compra ? \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') : 'N/A' }}</td>
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
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Mostrar/ocultar campos de fecha personalizada
    const filtroFecha = document.getElementById('filtroFecha');
    const fechaInicioDiv = document.getElementById('fechaInicioDiv');
    const fechaFinDiv = document.getElementById('fechaFinDiv');
    
    if (filtroFecha) {
        filtroFecha.addEventListener('change', function() {
            if (this.value === 'personalizado') {
                fechaInicioDiv.style.display = 'block';
                fechaFinDiv.style.display = 'block';
            } else {
                fechaInicioDiv.style.display = 'none';
                fechaFinDiv.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection