@extends('layouts.app')

@section('title', 'Dashboard - CRM')
@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-speedometer2"></i> Dashboard</h3>
        <p class="text-muted">
            Bienvenido, {{ Auth::user()->nombre_completo }}
        </p>
        @if(isset($modulosAcceso) && count($modulosAcceso) > 0)
            <p class="text-muted small">
                Módulos disponibles: 
                @foreach($modulosAcceso as $modulo)
                    <span class="badge bg-info me-1">{{ ucfirst($modulo) }}</span>
                @endforeach
            </p>
        @endif
    </div>

    <!-- KPI Cards - Solo si tiene permiso de ver clientes -->
    @if($mostrarCardClientes || $mostrarCardCotizaciones)
    <div class="row mb-4">
        @if($mostrarCardClientes)
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Clientes</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalClientes) }}</h2>
                            <small class="text-success">Activos (excluye bloqueados)</small>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Contactos Próximos</h6>
                            <h2 class="mb-0 fw-bold">{{ $contactosProximos }}</h2>
                            <small class="text-info">Próximos 7 días</small>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem;">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- KPI de Cotizaciones - Solo si tiene permiso de ver cotizaciones -->
        @if($mostrarCardCotizaciones)
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Cotizaciones</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalCotizaciones) }}</h2>
                            @if($porcentajeCotizaciones > 0)
                                <small class="text-success">+{{ number_format($porcentajeCotizaciones, 1) }}% vs mes anterior</small>
                            @elseif($porcentajeCotizaciones < 0)
                                <small class="text-danger">{{ number_format($porcentajeCotizaciones, 1) }}% vs mes anterior</small>
                            @else
                                <small class="text-muted">vs mes anterior</small>
                            @endif
                        </div>
                        <div class="text-success" style="font-size: 2.5rem;">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Cotizaciones Pendientes</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($cotizacionesPendientes) }}</h2>
                            <small class="text-warning">Requieren atención</small>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Charts and Stats Row - Solo si tiene permiso de ver cotizaciones -->
    @if($mostrarCardCotizaciones)
    <div class="row mb-4">
        <!-- Estados de Cotizaciones -->
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Estados de Cotizaciones</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                  <tr>
                                    <td width="40%">
                                        <span class="badge bg-success text-white px-3 py-2 w-100">Completadas</span>
                                     </td>
                                    <td width="20%">
                                        <strong class="fs-5">{{ $estadosCotizaciones['aceptadas'] }}</strong>
                                     </td>
                                     <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: {{ ($estadosCotizaciones['aceptadas'] / max($totalCotizaciones, 1)) * 100 }}%"></div>
                                        </div>
                                     </td>
                                  </tr>
                                  <tr>
                                     <td>
                                        <span class="badge bg-warning text-dark px-3 py-2 w-100">En Proceso</span>
                                     </td>
                                     <td>
                                        <strong class="fs-5">{{ $estadosCotizaciones['pendientes'] }}</strong>
                                     </td>
                                     <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                style="width: {{ ($estadosCotizaciones['pendientes'] / max($totalCotizaciones, 1)) * 100 }}%"></div>
                                        </div>
                                     </td>
                                  </tr>
                                  <tr>
                                     <td>
                                        <span class="badge bg-danger text-white px-3 py-2 w-100">Canceladas</span>
                                     </td>
                                     <td>
                                        <strong class="fs-5">{{ $estadosCotizaciones['rechazadas'] }}</strong>
                                     </td>
                                     <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                style="width: {{ ($estadosCotizaciones['rechazadas'] / max($totalCotizaciones, 1)) * 100 }}%"></div>
                                        </div>
                                     </td>
                                  </tr>
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monto Total del Mes -->
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-currency-dollar text-success me-2"></i>Monto Total por Mes</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-center">
                        <h1 class="text-success fw-bold display-4">${{ number_format($montosEsteMes, 2) }}</h1>
                        <p class="text-muted mb-0">Este mes</p>
                        @if($porcentajeCambio > 0)
                            <span class="badge bg-light text-success mt-2">
                                <i class="bi bi-arrow-up"></i> +{{ number_format($porcentajeCambio, 1) }}% vs mes anterior
                            </span>
                        @elseif($porcentajeCambio < 0)
                            <span class="badge bg-light text-danger mt-2">
                                <i class="bi bi-arrow-down"></i> {{ number_format($porcentajeCambio, 1) }}% vs mes anterior
                            </span>
                        @else
                            <span class="badge bg-light text-muted mt-2">
                                Sin cambios vs mes anterior
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="row">
        <!-- Últimos Contactos -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Últimos Contactos Agendados</h6>
                    <span class="badge bg-primary">{{ count($ultimosContactos) }} registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                              <tr>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                              </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosContactos as $contacto)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle text-primary me-2"></i>
                                        {{ $contacto->cliente->nombre }}
                                    </div>
                                </td>
                                <td>{{ $contacto->fecha_contacto->format('d/m/Y') }}</td>
                                <td>
                                    @if($contacto->completado)
                                        <span class="badge bg-success">Completado</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-x" style="font-size: 1.5rem;"></i>
                                    <p class="mb-0">No hay contactos agendados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                     </table>
                </div>
                <div class="card-footer bg-white text-end py-2">
                    <a href="#" class="btn btn-sm btn-outline-primary">Ver todos <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Últimas Cotizaciones -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Últimas Cotizaciones</h6>
                    <span class="badge bg-primary">{{ count($ultimasCotizaciones) }} registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                              <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Monto</th>
                              </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasCotizaciones as $cotizacion)
                            <tr>
                                <td><strong>#{{ str_pad($cotizacion->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle text-primary me-2"></i>
                                        {{ $cotizacion->cliente->nombre }}
                                    </div>
                                </td>
                                <td>
                                    @if($cotizacion->estado == 'aceptada')
                                        <span class="badge bg-success">Completada</span>
                                    @elseif($cotizacion->estado == 'pendiente')
                                        <span class="badge bg-warning text-dark">En proceso</span>
                                    @else
                                        <span class="badge bg-danger">Cancelada</span>
                                    @endif
                                </td>
                                <td><strong>${{ number_format($cotizacion->total, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-file-earmark-x" style="font-size: 1.5rem;"></i>
                                    <p class="mb-0">Sin cotizaciones</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                     </table>
                </div>
                <div class="card-footer bg-white text-end py-2">
                    <a href="{{ route('ventas.cotizaciones.index') }}" class="btn btn-sm btn-outline-primary">Ver todos <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Resumen Rápido - Solo si tiene permiso de ver clientes -->
    @if($mostrarCardClientes)
    <div class="row mt-2">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-trophy-fill text-warning me-2"></i>
                                <span><strong>Cliente Top:</strong> {{ $clienteTop }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-graph-up-arrow text-success me-2"></i>
                                <span><strong>Ticket Promedio:</strong> ${{ number_format($ticketPromedio, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock text-info me-2"></i>
                                <span><strong>Frecuencia:</strong> {{ $frecuenciaPromedio }} días</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                                <span><strong>Conversión:</strong> {{ number_format($tasaConversion, 1) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff;
}
.border-left-success {
    border-left: 4px solid #28a745;
}
.border-left-warning {
    border-left: 4px solid #ffc107;
}
.border-left-info {
    border-left: 4px solid #17a2b8;
}
.progress {
    background-color: #e9ecef;
    border-radius: 10px;
}
.progress-bar {
    border-radius: 10px;
}
.card {
    transition: transform 0.2s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
.badge {
    font-weight: 500;
    padding: 6px 12px;
}
</style>
@endsection