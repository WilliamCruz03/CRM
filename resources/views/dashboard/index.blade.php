@extends('layouts.app')

@section('title', 'Dashboard - CRM')
@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-speedometer2"></i> Dashboard</h3>
        {{--  <p class="text-muted">
            Bienvenido, {{ Auth::user()->nombre_completo }}
        </p>
        --}}
        @if(isset($modulosAcceso) && count($modulosAcceso) > 0)
            <p class="text-muted small">
                Módulos disponibles: 
                @foreach($modulosAcceso as $modulo)
                    <span class="badge bg-info me-1">{{ ucfirst($modulo) }}</span>
                @endforeach
            </p>
        @endif
    </div>

    <!-- Mensajes informativos -->
    @if(!$mostrarCardClientes && !$mostrarCardCotizaciones && isset($tieneAlgunPermiso) && $tieneAlgunPermiso)
    <div class="alert alert-info text-center py-4">
        <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
        <h5 class="mt-3">No hay módulos disponibles en el dashboard</h5>
        <p>Actualmente no tienes acceso a los módulos de <strong>Clientes</strong> o <strong>Cotizaciones</strong>.</p>
        <p class="mb-0">Sin embargo, puedes acceder a otros módulos del sistema desde el menú lateral.</p>
    </div>
    @endif

    @if(!$mostrarCardClientes && !$mostrarCardCotizaciones && (!isset($tieneAlgunPermiso) || !$tieneAlgunPermiso))
    <div class="alert alert-warning text-center py-4">
        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
        <h5 class="mt-3">Actualmente no tienes acceso a ningún módulo del sistema</h5>
        <p>Para poder acceder a las funcionalidades del CRM, necesitas que un administrador te asigne los permisos correspondientes.</p>
    </div>
    @endif

    <!-- Mensaje: Acceso a módulos pero sin cards habilitados -->
    @if($mostrarMensajeSinCards && ($mostrarCardClientes || $mostrarCardCotizaciones))
    <div class="alert alert-warning text-center py-4">
        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
        <h5 class="mt-3">Tienes acceso a módulos, pero no hay cards configurados en tu dashboard</h5>
        <p>
            @if($mostrarCardClientes && $mostrarCardCotizaciones)
                Tienes acceso a <strong>Clientes</strong> y <strong>Cotizaciones</strong>, pero no tienes ningún card habilitado.
            @elseif($mostrarCardClientes)
                Tienes acceso a <strong>Clientes</strong>, pero no tienes ningún card habilitado para este módulo.
            @elseif($mostrarCardCotizaciones)
                Tienes acceso a <strong>Cotizaciones</strong>, pero no tienes ningún card habilitado para este módulo.
            @endif
        </p>
        <p class="mb-0 text-muted small">
            <i class="bi bi-info-circle"></i>
            Para habilitar las visualizaciones, contacta al administrador para que configure tus preferencias de dashboard.
        </p>
    </div>
    @endif

    <!-- ============================================ -->
    <!-- KPI CARDS - Según preferencias del dashboard -->
    <!-- ============================================ -->
    @php
        // Verificar qué cards mostrar según preferencias
        $mostrarKpiTotalClientes = isset($datosCards['kpi_total_clientes']) && $permisosClientes['ver'];
        $mostrarKpiContactosProximos = isset($datosCards['kpi_contactos_proximos']) && $permisosClientes['ver'];
        $mostrarKpiTotalCotizaciones = isset($datosCards['kpi_total_cotizaciones']) && $permisosCotizaciones['ver'];
        $mostrarKpiCotizacionesPendientes = isset($datosCards['kpi_cotizaciones_pendientes']) && $permisosCotizaciones['ver'];
        $mostrarGraficoEstados = isset($datosCards['grafico_estados_cotizaciones']) && $permisosCotizaciones['ver'];
        $mostrarKpiMontoTotalMes = isset($datosCards['kpi_monto_total_mes']) && $permisosCotizaciones['ver'];
        $mostrarTablaUltimosContactos = isset($datosCards['tabla_ultimos_contactos']) && $permisosClientes['ver'];
        $mostrarTablaUltimasCotizaciones = isset($datosCards['tabla_ultimas_cotizaciones']) && $permisosCotizaciones['ver'];
        $mostrarResumenRapido = isset($datosCards['resumen_rapido']) && $permisosClientes['ver'];
        
        // Contar cuántos KPI cards se mostrarán
        $kpiCardsCount = 0;
        if ($mostrarKpiTotalClientes) $kpiCardsCount++;
        if ($mostrarKpiContactosProximos) $kpiCardsCount++;
        if ($mostrarKpiTotalCotizaciones) $kpiCardsCount++;
        if ($mostrarKpiCotizacionesPendientes) $kpiCardsCount++;
        
        $kpiColClass = $kpiCardsCount > 0 ? 'col-lg-' . (12 / $kpiCardsCount) : 'col-lg-3';
    @endphp

    <!-- KPI Cards Row -->
    @if($kpiCardsCount > 0)
    <div class="row mb-4">
        <!-- Total Clientes -->
        @if($mostrarKpiTotalClientes)
        <div class="{{ $kpiColClass }} col-md-6 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Clientes</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($totalClientes) }}</h2>
                            <small class="text-success"><b>Activos</b></small>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Contactos Próximos -->
        @if($mostrarKpiContactosProximos)
        <div class="{{ $kpiColClass }} col-md-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Contactos Próximos</h6>
                            <h2 class="mb-0 fw-bold">{{ $contactosProximos }}</h2>
                            <small class="text-info"><b>Próximos 7 días</b></small>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem;">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Total Cotizaciones -->
        @if($mostrarKpiTotalCotizaciones)
        <div class="{{ $kpiColClass }} col-md-6 mb-3">
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
                                <small class="text-muted"><b>vs mes anterior</b></small>
                            @endif
                        </div>
                        <div class="text-success" style="font-size: 2.5rem;">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Cotizaciones Pendientes -->
        @if($mostrarKpiCotizacionesPendientes)
        <div class="{{ $kpiColClass }} col-md-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Cotizaciones Pendientes</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($cotizacionesPendientes) }}</h2>
                            <small class="text-warning"><b>Requieren atención</b></small>
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

    <!-- Charts and Stats Row -->
    @if($mostrarGraficoEstados || $mostrarKpiMontoTotalMes || $mostrarResumenVentasMensual)
    <div class="row mb-4">
        <!-- Estados de Cotizaciones -->
        @if($mostrarGraficoEstados)
        <div class="col-lg-4 mb-3">
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
        @endif

        <!-- Monto Total del Mes -->
        @if($mostrarKpiMontoTotalMes)
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-currency-dollar text-success me-2"></i>Monto Total por Mes CRM</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Cotizaciones -->
                        <div class="col-6 text-center border-end">
                            <p class="text-muted mb-1 small">Cotizaciones</p>
                            <h3 class="text-primary fw-bold">${{ number_format($montosEsteMesCotizaciones, 2) }}</h3>
                            @if($porcentajeCambioCotizaciones > 0)
                                <span class="badge bg-light text-success">
                                    <i class="bi bi-arrow-up"></i> +{{ number_format($porcentajeCambioCotizaciones, 1) }}%
                                </span>
                            @elseif($porcentajeCambioCotizaciones < 0)
                                <span class="badge bg-light text-danger">
                                    <i class="bi bi-arrow-down"></i> {{ number_format($porcentajeCambioCotizaciones, 1) }}%
                                </span>
                            @else
                                <span class="badge bg-light text-muted">
                                    Sin cambios
                                </span>
                            @endif
                            <small class="d-block text-muted mt-1">vs mes anterior</small>
                        </div>
                        
                        <!-- Pedidos -->
                        <div class="col-6 text-center">
                            <p class="text-muted mb-1 small">Pedidos</p>
                            <h3 class="text-success fw-bold">${{ number_format($montosEsteMesPedidos, 2) }}</h3>
                            @if($porcentajeCambioPedidos > 0)
                                <span class="badge bg-light text-success">
                                    <i class="bi bi-arrow-up"></i> +{{ number_format($porcentajeCambioPedidos, 1) }}%
                                </span>
                            @elseif($porcentajeCambioPedidos < 0)
                                <span class="badge bg-light text-danger">
                                    <i class="bi bi-arrow-down"></i> {{ number_format($porcentajeCambioPedidos, 1) }}%
                                </span>
                            @else
                                <span class="badge bg-light text-muted">
                                    Sin cambios
                                </span>
                            @endif
                            <small class="d-block text-muted mt-1">vs mes anterior</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Resumen de Ventas Mensual -->
        @if($mostrarResumenVentasMensual)
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-graph-up-arrow text-success me-2"></i>Resumen de Ventas Mensual</h6>
                </div>
                <div class="card-body">
                    <!-- Total General con porcentaje de cambio -->
                    <div class="text-center mb-3">
                        <h2 class="text-success fw-bold">${{ number_format($resumenVentasMensual->total_general, 2) }}</h2>
                        <p class="text-muted small">Total Ventas del Mes</p>
                        @if($resumenVentasMensual->porcentaje_cambio > 0)
                            <span class="badge bg-light text-success">
                                <i class="bi bi-arrow-up"></i> +{{ number_format($resumenVentasMensual->porcentaje_cambio, 2) }}% vs mes anterior
                            </span>
                        @elseif($resumenVentasMensual->porcentaje_cambio < 0)
                            <span class="badge bg-light text-danger">
                                <i class="bi bi-arrow-down"></i> {{ number_format($resumenVentasMensual->porcentaje_cambio, 2) }}% vs mes anterior
                            </span>
                        @else
                            <span class="badge bg-light text-muted">
                                Sin cambios vs mes anterior
                            </span>
                        @endif
                        <!-- Mostrar el monto del mes anterior como referencia -->
                        <div class="mt-2">
                            <small class="text-muted">
                                Mes anterior: ${{ number_format($resumenVentasMensual->total_anterior ?? 0, 2) }}
                            </small>
                        </div>
                    </div>
                    
                    <!-- Desglose en 2 columnas -->
                    <div class="row">
                        <div class="col-6">
                            <div class="bg-light p-2 rounded text-center">
                                <p class="text-muted small mb-1">Clientes Registrados</p>
                                <h4 class="text-primary fw-bold mb-0">${{ number_format($resumenVentasMensual->total_registrados, 2) }}</h4>
                                <small class="text-muted">{{ number_format($resumenVentasMensual->porcentaje_registrados, 1) }}%</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-2 rounded text-center">
                                <p class="text-muted small mb-1">Público en General</p>
                                <h4 class="text-info fw-bold mb-0">${{ number_format($resumenVentasMensual->total_publico, 2) }}</h4>
                                <small class="text-muted">{{ number_format($resumenVentasMensual->porcentaje_publico, 1) }}%</small>
                                <div class="small text-muted mt-1">
                                    @foreach($resumenVentasMensual->ids_publico as $id)
                                    <span class="badge bg-light">{{ $id }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Resumen Rápido -->
    @if($mostrarResumenRapido)
    <div class="row mt-2">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Cliente CRM</h6>
                </div>
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-trophy-fill text-warning me-2" title="Cliente con mas compras"></i>
                                <span><strong>Cliente Top:</strong> {{ $clienteTop }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-graph-up-arrow text-success me-2" title="Importe promedio de los tickets generados"></i>
                                <span><strong>Ticket Promedio:</strong> ${{ number_format($ticketPromedio, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock text-info me-2" title="Frecuencia de compra"></i>
                                <span><strong>Frecuencia:</strong> 
                                    @if($frecuenciaPromedio > 0)
                                        Cada {{ $frecuenciaPromedio }} días
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-bar-chart-fill text-primary me-2" title="Cotizaciones que pasaron a ser pedido"></i>
                                <span><strong>Conversión:</strong> {{ number_format($tasaConversion, 1) }}%</span>
                            </div>
                        </div>
                    </div>
                    <!-- Subtítulo informativo -->
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Basado en pedidos completados del mes actual
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Actividad Reciente -->
    <div class="row">
        <!-- Últimos Contactos -->
        @if($mostrarTablaUltimosContactos)
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
                                        {{ $contacto->cliente->nombre ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>{{ $contacto->fecha_contacto->format('d/m/Y') ?? 'N/A' }}</td>
                                <td>
                                    @if($contacto->completado ?? false)
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
                                    <small class="text-muted">Próximamente disponible</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white text-end py-2">
                    <a href="#" class="btn btn-sm btn-outline-primary disabled">Ver todos <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        @endif

        <!-- Últimas Cotizaciones -->
        @if($mostrarTablaUltimasCotizaciones)
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
                                        {{ $cotizacion->cliente->nombre ?? 'N/A' }}
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
        @endif
    </div>
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
.alert-sm {
    font-size: 0.875rem;
}
</style>

<script>
function abrirModalNuevaCotizacion() {
    // Redirigir directamente a la página de cotizaciones
    window.location.href = "{{ route('ventas.cotizaciones.index') }}";
}
</script>

@endsection