@extends('layouts.app')

@section('title', 'Dashboard - CRM')
@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header Ejecutivo del Dashboard -->
    <div class="page-header">
        <h3><i class="bi bi-speedometer2"></i> Dashboard</h3>
        {{--  <p class="text-muted">
            Bienvenido, {{ Auth::user()->nombre_completo }}
        </p>
        --}}
        @if(isset($modulosAcceso) && count($modulosAcceso) > 0)
            <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted small fw-semibold me-1"><i class="bi bi-check2-circle text-success me-1"></i>Módulos disponibles:</span>
                @foreach($modulosAcceso as $modulo)
                    <span class="badge-soft-primary px-2 py-1 rounded-pill small">{{ ucfirst($modulo) }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Mensajes informativos -->
    @if(!$mostrarCardClientes && !$mostrarCardCotizaciones && isset($tieneAlgunPermiso) && $tieneAlgunPermiso)
    <div class="alert alert-info text-center py-4 rounded-4 shadow-sm border-0 mb-4">
        <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
        <h5 class="mt-3 fw-bold">No hay módulos disponibles en el dashboard</h5>
        <p class="text-muted mb-1">Actualmente no tienes acceso a los módulos de <strong>Clientes</strong> o <strong>Cotizaciones</strong>.</p>
        <p class="mb-0 text-muted small">Sin embargo, puedes acceder a otros módulos del sistema desde el menú lateral.</p>
    </div>
    @endif

    @if(!$mostrarCardClientes && !$mostrarCardCotizaciones && (!isset($tieneAlgunPermiso) || !$tieneAlgunPermiso))
    <div class="alert alert-warning text-center py-4 rounded-4 shadow-sm border-0 mb-4">
        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
        <h5 class="mt-3 fw-bold">Actualmente no tienes acceso a ningún módulo del sistema</h5>
        <p class="text-muted mb-0">Para poder acceder a las funcionalidades del CRM, necesitas que un administrador te asigne los permisos correspondientes.</p>
    </div>
    @endif

    <!-- Mensaje: Acceso a módulos pero sin cards habilitados -->
    @if($mostrarMensajeSinCards && ($mostrarCardClientes || $mostrarCardCotizaciones))
    <div class="alert alert-warning text-center py-4 rounded-4 shadow-sm border-0 mb-4">
        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
        <h5 class="mt-3 fw-bold">Tienes acceso a módulos, pero no hay cards configurados en tu dashboard</h5>
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
        $mostrarTablaUltimosContactos = isset($datosCards['tabla_ultimos_contactos']);
        $mostrarTablaUltimasCotizaciones = isset($datosCards['tabla_ultimas_cotizaciones']);
        $mostrarResumenRapido = isset($datosCards['resumen_rapido']) && $permisosClientes['ver'];

        // Contar cuántos KPI cards se mostrarán
        $kpiCardsCount = 0;
        if ($mostrarKpiTotalClientes) $kpiCardsCount++;
        if ($mostrarKpiContactosProximos) $kpiCardsCount++;
        if ($mostrarKpiTotalCotizaciones) $kpiCardsCount++;
        if ($mostrarKpiCotizacionesPendientes) $kpiCardsCount++;
        if ($mostrarKpiTasaConversion) $kpiCardsCount++;
        if ($mostrarKpiPedidosSucursal) $kpiCardsCount++;
        if ($mostrarKpiVentasVendedor) $kpiCardsCount++;
        
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
                        <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary" style="font-size: 2rem;">
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
                        <div class="kpi-icon-box bg-info bg-opacity-10 text-info" style="font-size: 2rem;">
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
                                <small class="text-success fw-bold">+{{ number_format($porcentajeCotizaciones, 1) }}% vs mes anterior</small>
                            @elseif($porcentajeCotizaciones < 0)
                                <small class="text-danger fw-bold">{{ number_format($porcentajeCotizaciones, 1) }}% vs mes anterior</small>
                            @else
                                <small class="text-muted"><b>vs mes anterior</b></small>
                            @endif
                        </div>
                        <div class="kpi-icon-box bg-success bg-opacity-10 text-success" style="font-size: 2rem;">
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
                        <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning" style="font-size: 2rem;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Fila de Gráficos y Métricas Comerciales del Mes -->
    @if($mostrarGraficoEstados || $mostrarKpiMontoTotalMes || $mostrarResumenVentasMensual)
    <div class="row mb-4">
        <!-- Estados de Cotizaciones -->
        @if($mostrarGraficoEstados)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-white">Estados de Cotizaciones</h6>
                    </div>
                    <span class="px-2 py-1 rounded-pill small fw-semibold text-white">{{ number_format($totalCotizaciones) }} total</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-around">
                    @php
                        $totCot = max($totalCotizaciones, 1);
                        $pctAceptadas = round(($estadosCotizaciones['aceptadas'] / $totCot) * 100, 1);
                        $pctPendientes = round(($estadosCotizaciones['pendientes'] / $totCot) * 100, 1);
                        $pctRechazadas = round(($estadosCotizaciones['rechazadas'] / $totCot) * 100, 1);
                    @endphp
                    <!-- Completadas -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="d-flex align-items-center gap-2 small fw-semibold text-muted">
                                <span class="badge-status-dot bg-success"></span> Completadas
                            </span>
                            <div class="text-end">
                                <strong class="text-dark">{{ $estadosCotizaciones['aceptadas'] }}</strong>
                                <small class="text-muted ms-1">({{ $pctAceptadas }}%)</small>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $pctAceptadas }}%"></div>
                        </div>
                    </div>

                    <!-- En Proceso -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="d-flex align-items-center gap-2 small fw-semibold text-muted">
                                <span class="badge-status-dot bg-warning"></span> En Proceso
                            </span>
                            <div class="text-end">
                                <strong class="text-dark">{{ $estadosCotizaciones['pendientes'] }}</strong>
                                <small class="text-muted ms-1">({{ $pctPendientes }}%)</small>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: {{ $pctPendientes }}%"></div>
                        </div>
                    </div>

                    <!-- Canceladas -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="d-flex align-items-center gap-2 small fw-semibold text-muted">
                                <span class="badge-status-dot bg-danger"></span> Canceladas
                            </span>
                            <div class="text-end">
                                <strong class="text-dark">{{ $estadosCotizaciones['rechazadas'] }}</strong>
                                <small class="text-muted ms-1">({{ $pctRechazadas }}%)</small>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-danger rounded-pill" role="progressbar" style="width: {{ $pctRechazadas }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Monto Total del Mes -->
        @if($mostrarKpiMontoTotalMes)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-success border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon-dot bg-success"></div>
                        <h6 class="mb-0 fw-bold text-white">Monto Total del Mes (CRM)</h6>
                    </div>
                    <span class="px-2 py-1 rounded-pill small fw-semibold text-white">{{ now()->locale('es')->isoFormat('MMMM YYYY') }}</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-around">
                    <div class="row g-3">
                        <!-- Cotizaciones -->
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-light text-center h-100 d-flex flex-column justify-content-center">
                                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Cotizaciones</span>
                                <h3 class="text-primary fw-bolder mt-2 mb-1">${{ number_format($montosEsteMesCotizaciones, 2) }}</h3>
                                <div>
                                    @if($porcentajeCambioCotizaciones > 0)
                                        <span class="badge-soft-success px-2 py-1 rounded-pill small">
                                            <i class="bi bi-arrow-up-short"></i>+{{ number_format($porcentajeCambioCotizaciones, 1) }}%
                                        </span>
                                    @elseif($porcentajeCambioCotizaciones < 0)
                                        <span class="badge-soft-danger px-2 py-1 rounded-pill small">
                                            <i class="bi bi-arrow-down-short"></i>{{ number_format($porcentajeCambioCotizaciones, 1) }}%
                                        </span>
                                    @else
                                        <span class="badge-soft-secondary px-2 py-1 rounded-pill small">0%</span>
                                    @endif
                                </div>
                                <small class="text-muted mt-1" style="font-size: 0.72rem;">vs mes anterior</small>
                            </div>
                        </div>

                        <!-- Pedidos -->
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-light text-center h-100 d-flex flex-column justify-content-center">
                                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Pedidos</span>
                                <h3 class="text-success fw-bolder mt-2 mb-1">${{ number_format($montosEsteMesPedidos, 2) }}</h3>
                                <div>
                                    @if($porcentajeCambioPedidos > 0)
                                        <span class="badge-soft-success px-2 py-1 rounded-pill small">
                                            <i class="bi bi-arrow-up-short"></i>+{{ number_format($porcentajeCambioPedidos, 1) }}%
                                        </span>
                                    @elseif($porcentajeCambioPedidos < 0)
                                        <span class="badge-soft-danger px-2 py-1 rounded-pill small">
                                            <i class="bi bi-arrow-down-short"></i>{{ number_format($porcentajeCambioPedidos, 1) }}%
                                        </span>
                                    @else
                                        <span class="badge-soft-secondary px-2 py-1 rounded-pill small">0%</span>
                                    @endif
                                </div>
                                <small class="text-muted mt-1" style="font-size: 0.72rem;">vs mes anterior</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Resumen de Ventas Mensual -->
        @if($mostrarResumenVentasMensual)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-info border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon-dot bg-info"></div>
                        <h6 class="mb-0 fw-bold text-white">Resumen de Ventas General</h6>
                    </div>
                    <span class="px-2 py-1 rounded-pill small fw-semibold text-white">Global</span>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <span class="text-muted small text-uppercase tracking-wider fw-semibold">Total Facturación Mes</span>
                        <h2 class="text-success fw-bolder my-1">${{ number_format($resumenVentasMensual->total_general, 2) }}</h2>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            @if($resumenVentasMensual->porcentaje_cambio > 0)
                                <span class="badge-soft-success px-2 py-1 rounded-pill small fw-semibold">
                                    <i class="bi bi-arrow-up-right me-1"></i>+{{ number_format($resumenVentasMensual->porcentaje_cambio, 1) }}%
                                </span>
                            @elseif($resumenVentasMensual->porcentaje_cambio < 0)
                                <span class="badge-soft-danger px-2 py-1 rounded-pill small fw-semibold">
                                    <i class="bi bi-arrow-down-right me-1"></i>{{ number_format($resumenVentasMensual->porcentaje_cambio, 1) }}%
                                </span>
                            @else
                                <span class="badge-soft-secondary px-2 py-1 rounded-pill small fw-semibold">Sin cambios</span>
                            @endif
                            <span class="text-muted small">Mes ant: ${{ number_format($resumenVentasMensual->total_anterior ?? 0, 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-light text-center">
                                <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">Clientes Reg.</span>
                                <h5 class="text-primary fw-bold mb-0">${{ number_format($resumenVentasMensual->total_registrados, 2) }}</h5>
                                <small class="text-muted fw-semibold">{{ number_format($resumenVentasMensual->porcentaje_registrados, 1) }}%</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-light text-center">
                                <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">Público Gral.</span>
                                <h5 class="text-info fw-bold mb-0">${{ number_format($resumenVentasMensual->total_publico, 2) }}</h5>
                                <small class="text-muted fw-semibold">{{ number_format($resumenVentasMensual->porcentaje_publico, 1) }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Fila de Métricas Operativas -->
    @if($mostrarKpiTasaConversion || $mostrarKpiPedidosSucursal || $mostrarKpiVentasVendedor)
    <div class="row mb-4">
        <!-- Pedidos por Sucursal -->
        @if($mostrarKpiPedidosSucursal)
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 border-left-primary">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="text-uppercase text-muted fw-bold small tracking-wider">Pedidos por Sucursal</span>
                                <h2 class="mt-2 mb-0 fw-bolder text-dark">{{ number_format($pedidosSucursal->total ?? 0) }}</h2>
                            </div>
                            <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>

                        @if($pedidosSucursal->sucursal_top ?? false)
                            <div class="p-2 mb-3 rounded-3 bg-light border border-light d-flex align-items-center gap-2">
                                <i class="bi bi-trophy-fill text-warning"></i>
                                <div class="small">
                                    <strong class="text-dark">{{ $pedidosSucursal->sucursal_top }}</strong>
                                    <span class="text-muted">({{ $pedidosSucursal->top_count }} pedidos)</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(isset($pedidosSucursal->distribucion) && $pedidosSucursal->distribucion->count() > 0)
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-2">Distribución:</span>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($pedidosSucursal->distribucion as $suc)
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">
                                        {{ $suc->sucursal }}: <strong>{{ $suc->total }}</strong>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Ventas por Vendedor -->
        @if($mostrarKpiVentasVendedor)
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 border-left-info">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="text-uppercase text-muted fw-bold small tracking-wider">Ventas por Vendedor</span>
                                <h2 class="mt-2 mb-0 fw-bolder text-dark">${{ number_format($ventasVendedor->total ?? 0, 2) }}</h2>
                            </div>
                            <div class="kpi-icon-box bg-info bg-opacity-10 text-info">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>

                        @if(isset($ventasVendedor->top_vendedores) && $ventasVendedor->top_vendedores->count() > 0)
                            <div class="mt-2">
                                <span class="text-muted small fw-semibold d-block mb-2">Top Vendedores:</span>
                                <div class="d-flex flex-column gap-2">
                                    @foreach($ventasVendedor->top_vendedores as $index => $vendedor)
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge rounded-circle {{ $index == 0 ? 'bg-warning text-dark' : 'bg-secondary text-white' }}" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                                                    {{ $index + 1 }}
                                                </span>
                                                <span class="small fw-semibold text-dark" style="max-width: 120px;" title="{{ $vendedor->nombre }}">{{ $vendedor->nombre }}</span>
                                            </div>
                                            <span class="small fw-bold text-success">${{ number_format($vendedor->monto_total, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tasa de Conversión -->
        @if($mostrarKpiTasaConversion)
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 border-left-success">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="text-uppercase text-muted fw-bold small tracking-wider">Tasa de Conversión</span>
                                <h2 class="mt-2 mb-0 fw-bolder text-dark">{{ number_format($tasaConversion ?? 0, 1) }}%</h2>
                            </div>
                            <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>
                        </div>
                        <div class="progress rounded-pill mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ min(max($tasaConversion ?? 0, 0), 100) }}%"></div>
                        </div>
                    </div>
                    <div class="p-2 rounded-3 bg-light text-center small text-muted">
                        <span class="fw-bold text-success">{{ $tasaConversionData->convertidas ?? 0 }}</span> de 
                        <span class="fw-bold text-primary">{{ $tasaConversionData->total ?? 0 }}</span> cotizaciones convertidas a pedido
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tiempos Promedio -->
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 border-left-primary">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="text-uppercase text-muted fw-bold small tracking-wider">Tiempos Promedio</span>
                                <h6 class="mt-1 text-muted small">Ciclo de atención comercial</h6>
                            </div>
                            <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Cotiz. &rarr; Pedido</small>
                                    <h4 class="mb-0 fw-bold text-primary">{{ $tiempoPromedioCotizacionAPedido ?? 0 }} <small class="fs-6 text-muted">hrs</small></h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Pedido &rarr; Entrega</small>
                                    <h4 class="mb-0 fw-bold text-success">{{ $tiempoPromedioPedidoAEntrega ?? 0 }} <small class="fs-6 text-muted">hrs</small></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted" style="font-size: 0.74rem;">
                            <i class="bi bi-info-circle me-1"></i>Basado en pedidos cerrados del mes
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Resumen Rápido - Cliente Destacado del Mes -->
    @if($mostrarResumenRapido)
    <div class="card shadow-sm border-0 rounded-4 mb-4 vip-client-card">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="kpi-icon-box bg-white bg-opacity-20 text-warning" style="width: 34px; height: 34px; font-size: 1rem;">
                    <i class="bi bi-trophy-fill text-warning"></i>
                </div>
                <h6 class="mb-0 fw-bold text-dark">Cliente Destacado del Mes</h6>
            </div>
            <span class="badge-soft-warning px-3 py-1 rounded-pill small fw-semibold">
                <i class="bi bi-star-fill me-1"></i>Top CRM
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 text-center">
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-4 bg-white shadow-sm border border-light h-100">
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider mb-1">Cliente Top</div>
                        <h5 class="fw-bold text-dark mb-1" title="{{ $clienteTop ?? 'N/A' }}">{{ $clienteTop ?? 'N/A' }}</h5>
                        @if(isset($clienteTopPedidos) && $clienteTopPedidos > 0)
                            <span class="badge-soft-primary px-2 py-1 rounded-pill small">{{ $clienteTopPedidos }} pedidos</span>
                        @else
                            <span class="badge-soft-secondary px-2 py-1 rounded-pill small">Sin pedidos</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-4 bg-white shadow-sm border border-light h-100">
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider mb-1">Ticket Promedio</div>
                        <h5 class="fw-bold text-success mb-1">${{ number_format($ticketPromedio, 2) }}</h5>
                        <small class="text-muted" style="font-size: 0.74rem;">Por compra generada</small>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-4 bg-white shadow-sm border border-light h-100">
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider mb-1">Frecuencia</div>
                        <h5 class="fw-bold text-info mb-1">
                            @if($frecuenciaPromedio > 0)
                                Cada {{ $frecuenciaPromedio }} días
                            @else
                                N/A
                            @endif
                        </h5>
                        <small class="text-muted" style="font-size: 0.74rem;">Intervalo de compra</small>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-4 bg-white shadow-sm border border-light h-100">
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider mb-1">Conversión</div>
                        <h5 class="fw-bold text-primary mb-1">{{ number_format($tasaConversionClienteTop ?? 0, 1) }}%</h5>
                        <small class="text-muted" style="font-size: 0.74rem;">Cotiz. a pedidos</small>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <small class="text-muted" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i>Métricas calculadas a partir de las transacciones registradas durante el mes actual.
                </small>
            </div>
        </div>
    </div>
    @endif

    <!-- Tablas de Actividad Reciente -->
    <div class="row">
        <!-- Últimos Contactos Agendados -->
        @if($mostrarTablaUltimosContactos)
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon-dot bg-primary"></div>
                        <h6 class="mb-0 fw-bold text-dark">Últimos Contactos Agendados</h6>
                    </div>
                    <span class="badge-soft-primary px-3 py-1 rounded-pill small fw-semibold">{{ count($ultimosContactos) }} registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 small text-uppercase text-muted fw-bold">Cliente</th>
                                <th class="py-3 small text-uppercase text-muted fw-bold">Fecha</th>
                                <th class="pe-4 py-3 small text-uppercase text-muted fw-bold text-end">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosContactos as $contacto)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="client-avatar-mini bg-primary bg-opacity-10 text-primary">
                                            {{ mb_substr($contacto->cliente->nombre ?? 'C', 0, 1) }}
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $contacto->cliente->nombre ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $contacto->fecha_contacto ? $contacto->fecha_contacto->format('d/m/Y') : 'N/A' }}</td>
                                <td class="pe-4 text-end">
                                    @if($contacto->completado ?? false)
                                        <span class="badge-soft-success px-2 py-1 rounded-pill small fw-semibold">
                                            <i class="bi bi-check2 me-1"></i>Completado
                                        </span>
                                    @else
                                        <span class="badge-soft-warning px-2 py-1 rounded-pill small fw-semibold">
                                            <i class="bi bi-clock me-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <div class="empty-state-icon text-muted mb-2">
                                        <i class="bi bi-calendar-x fs-1"></i>
                                    </div>
                                    <p class="mb-0 fw-semibold">No hay contactos agendados recientemente</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Últimas Cotizaciones -->
        @if($mostrarTablaUltimasCotizaciones)
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon-dot bg-success"></div>
                        <h6 class="mb-0 fw-bold text-dark">Últimas Cotizaciones</h6>
                    </div>
                    <span class="badge-soft-success px-3 py-1 rounded-pill small fw-semibold">{{ count($ultimasCotizaciones) }} registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 small text-uppercase text-muted fw-bold">Folio</th>
                                <th class="py-3 small text-uppercase text-muted fw-bold">Cliente</th>
                                <th class="py-3 small text-uppercase text-muted fw-bold">Estado</th>
                                <th class="pe-4 py-3 small text-uppercase text-muted fw-bold text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasCotizaciones as $cotizacion)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        #{{ str_pad($cotizacion->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="client-avatar-mini bg-success bg-opacity-10 text-success">
                                            {{ mb_substr($cotizacion->cliente->nombre ?? 'C', 0, 1) }}
                                        </div>
                                        <span class="fw-semibold text-dark text-truncate" style="max-width: 160px;" title="{{ $cotizacion->cliente->nombre ?? 'N/A' }}">
                                            {{ $cotizacion->cliente->nombre ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($cotizacion->estado == 'aceptada')
                                        <span class="badge-soft-success px-2 py-1 rounded-pill small fw-semibold">
                                            Completada
                                        </span>
                                    @elseif($cotizacion->estado == 'pendiente')
                                        <span class="badge-soft-warning px-2 py-1 rounded-pill small fw-semibold">
                                            En proceso
                                        </span>
                                    @else
                                        <span class="badge-soft-danger px-2 py-1 rounded-pill small fw-semibold">
                                            Cancelada
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <strong class="text-dark font-monospace">${{ number_format($cotizacion->total, 2) }}</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="empty-state-icon text-muted mb-2">
                                        <i class="bi bi-file-earmark-x fs-1"></i>
                                    </div>
                                    <p class="mb-0 fw-semibold">Sin cotizaciones recientes</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.section-icon-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.badge-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}

.client-avatar-mini {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.empty-state-icon {
    opacity: 0.4;
}

/* Tarjeta VIP */
.vip-client-card {
    background: linear-gradient(135deg, #ffffff 0%, #fffcf5 100%);
    border: 1px solid rgba(245, 158, 11, 0.25) !important;
}

/* Progreso */
.progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

/* Tablas */
.table > :not(caption) > * > * {
    padding: 0.75rem 0.75rem;
}

.table tbody tr {
    transition: background-color 0.15s ease;
}

.table tbody tr:hover {
    background-color: rgba(0, 86, 151, 0.02);
}

.font-monospace {
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
}

.card {
    transition: transform 0.2s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>

<script>
function abrirModalNuevaCotizacion() {
    window.location.href = "{{ route('ventas.cotizaciones.index') }}";
}

function abrirModalNuevaAgenda() {
    window.location.href = "{{ route('ventas.agenda_contactos.index') }}";
}
</script>

@endsection