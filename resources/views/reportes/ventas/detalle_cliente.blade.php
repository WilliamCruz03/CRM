{{-- resources/views/reportes/ventas/detalle_cliente.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle de Compras - ' . $cliente->nombre_completo)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Detalle de Compras: <strong>{{ $cliente->nombre_completo }}</strong>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('reportes.ventas.clientes', request()->except('page')) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Cliente -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total General</span>
                    <span class="info-box-number">${{ number_format($totalGeneral, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-layer-group"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Áreas Compradas</span>
                    <span class="info-box-number">{{ $familias->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Período</span>
                    <span class="info-box-number">
                        {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Áreas/Familias -->
    <div class="card">
        <div class="card-header">
            <h5>Compras por Área / Familia</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="familiasTable">
                    <thead>
                        <tr>
                            <th>Área / Familia</th>
                            <th>Transacciones</th>
                            <th>Cantidad Productos</th>
                            <th>Monto Total</th>
                            <th>% del Total</th>
                            <th>Ticket Promedio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($familias as $familia)
                        <tr>
                            <td>{{ $familia->area }}</td>
                            <td>{{ number_format($familia->transacciones) }}</td>
                            <td>{{ number_format($familia->cantidad_productos) }}</td>
                            <td>${{ number_format($familia->monto_total, 2) }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $totalGeneral > 0 ? ($familia->monto_total / $totalGeneral) * 100 : 0 }}%"
                                         aria-valuenow="{{ $totalGeneral > 0 ? ($familia->monto_total / $totalGeneral) * 100 : 0 }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format(($familia->monto_total / $totalGeneral) * 100, 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>${{ number_format($familia->monto_promedio, 2) }}</td>
                            <td>
                                <a href="{{ route('reportes.ventas.cliente.familia', [
                                    'clienteId' => $cliente->id_Cliente, 
                                    'familiaId' => $familia->num_familia
                                ]) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-boxes"></i> Ver Productos
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
        $('#familiasTable').DataTable({
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