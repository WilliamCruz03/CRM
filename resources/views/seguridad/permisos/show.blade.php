@extends('layouts.app')

@section('title', 'Permisos de Usuario - CRM')
@section('page-title', 'Permisos de Acceso')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-shield-lock"></i> Permisos de Usuario</h3>
        <p class="text-muted">Visualización de permisos asignados al usuario</p>
    </div>

    <!-- Información del Usuario -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <span><i class="bi bi-info-circle text-warning"></i> Datos del Usuario</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-label">Nombre</div>
                    <div class="info-value h5">{{ $usuario->nombre_completo }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Usuario</div>
                    <div class="info-value">{{ $usuario->usuario }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Correo</div>
                    <div class="info-value">{{ $usuario->contacto ?? 'No especificado' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permisos por Módulo con Collapse -->
    <div class="card">
        <div class="card-header bg-white">
            <span><i class="bi bi-grid-3x3-gap-fill"></i> Permisos por Módulo</span>
        </div>
        <div class="card-body p-0">
            @php
                $permisos = $usuario->permisos_formateados;
            @endphp

            <div class="accordion" id="accordionPermisos">
                <!-- MÓDULO CLIENTES -->
                @php
                    $tieneAlgunPermisoClientes = false;
                    foreach ($permisos['clientes'] as $submodulo => $acciones) {
                        if ($acciones['mostrar'] || $acciones['ver'] || $acciones['crear'] || $acciones['editar'] || $acciones['eliminar']) {
                            $tieneAlgunPermisoClientes = true;
                            break;
                        }
                    }
                @endphp
                
                @if($tieneAlgunPermisoClientes)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingClientes">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClientes" aria-expanded="false" aria-controls="collapseClientes">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <span><i class="bi bi-card-checklist me-2"></i> <strong>CLIENTES</strong></span>
                                @php
                                    $algunaAccion = false;
                                    foreach ($permisos['clientes'] as $submodulo => $acciones) {
                                        if ($acciones['mostrar']) {
                                            $algunaAccion = true;
                                            break;
                                        }
                                    }
                                @endphp
                                @if($algunaAccion)
                                    <span class="badge bg-success me-2">Visible en menú</span>
                                @else
                                    <span class="badge bg-secondary me-2">Oculto en menú</span>
                                @endif
                            </div>
                        </button>
                    </h2>
                    <div id="collapseClientes" class="accordion-collapse collapse" aria-labelledby="headingClientes" data-bs-parent="#accordionPermisos">
                        <div class="accordion-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Submódulo</th>
                                        <th class="text-center" style="width: 13%">Mostrar</th>
                                        <th class="text-center" style="width: 13%">Ver</th>
                                        <th class="text-center" style="width: 13%">Crear</th>
                                        <th class="text-center" style="width: 13%">Editar</th>
                                        <th class="text-center" style="width: 13%">Eliminar</th>
                                    </thead>
                                <tbody>
                                    @foreach($permisos['clientes'] as $submodulo => $acciones)
                                    <tr>
                                        <td>
                                            @if($submodulo == 'directorio')
                                                <i class="bi bi-list me-2"></i> Directorio Clientes
                                            @elseif($submodulo == 'enfermedades')
                                                <i class="bi bi-heart-pulse me-2"></i> Enfermedades
                                            @elseif($submodulo == 'intereses')
                                                <i class="bi bi-star me-2"></i> Intereses
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['mostrar'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['ver'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['crear'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['editar'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['eliminar'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                             </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- MÓDULO VENTAS -->
                @php
                    $tieneAlgunPermisoVentas = false;
                    foreach ($permisos['ventas'] as $submodulo => $acciones) {
                        if ($acciones['mostrar'] || $acciones['ver'] || ($acciones['crear'] ?? false) || ($acciones['editar'] ?? false) || ($acciones['eliminar'] ?? false)) {
                            $tieneAlgunPermisoVentas = true;
                            break;
                        }
                    }
                @endphp
                
                @if($tieneAlgunPermisoVentas)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingVentas">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVentas" aria-expanded="false" aria-controls="collapseVentas">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <span><i class="bi bi-graph-up me-2"></i> <strong>VENTAS</strong></span>
                                @php
                                    $algunaAccionVentas = false;
                                    foreach ($permisos['ventas'] as $submodulo => $acciones) {
                                        if ($acciones['mostrar']) {
                                            $algunaAccionVentas = true;
                                            break;
                                        }
                                    }
                                @endphp
                                @if($algunaAccionVentas)
                                    <span class="badge bg-success me-2">Visible en menú</span>
                                @else
                                    <span class="badge bg-secondary me-2">Oculto en menú</span>
                                @endif
                            </div>
                        </button>
                    </h2>
                    <div id="collapseVentas" class="accordion-collapse collapse" aria-labelledby="headingVentas" data-bs-parent="#accordionPermisos">
                        <div class="accordion-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Submódulo</th>
                                        <th class="text-center" style="width: 13%">Mostrar</th>
                                        <th class="text-center" style="width: 13%">Ver</th>
                                        <th class="text-center" style="width: 13%">Crear</th>
                                        <th class="text-center" style="width: 13%">Editar</th>
                                        <th class="text-center" style="width: 13%">Eliminar</th>
                                    </thead>
                                <tbody>
                                    @foreach($permisos['ventas'] as $submodulo => $acciones)
                                    <tr>
                                        <td>
                                            @if($submodulo == 'cotizaciones')
                                                <i class="bi bi-file-text me-2"></i> Cotizaciones
                                            @elseif($submodulo == 'pedidos_anticipo')
                                                <i class="bi bi-receipt me-2"></i> Pedidos Anticipo
                                            @elseif($submodulo == 'seguimiento_ventas')
                                                <i class="bi bi-arrow-repeat me-2"></i> Seguimiento Ventas
                                            @elseif($submodulo == 'seguimiento_cotizaciones')
                                                <i class="bi bi-arrow-repeat me-2"></i> Seguimiento Cotizaciones
                                            @elseif($submodulo == 'agenda_contactos')
                                                <i class="bi bi-calendar-event me-2"></i> Agenda Contactos
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['mostrar'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['ver'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['crear'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['editar'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['eliminar'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- MÓDULO SEGURIDAD -->
                @php
                    $tieneAlgunPermisoSeguridad = false;
                    foreach ($permisos['seguridad'] as $submodulo => $acciones) {
                        if ($acciones['mostrar'] || $acciones['ver'] || ($acciones['crear'] ?? false) || ($acciones['editar'] ?? false) || ($acciones['eliminar'] ?? false)) {
                            $tieneAlgunPermisoSeguridad = true;
                            break;
                        }
                    }
                @endphp
                
                @if($tieneAlgunPermisoSeguridad)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSeguridad">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeguridad" aria-expanded="false" aria-controls="collapseSeguridad">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <span><i class="bi bi-shield-lock me-2"></i> <strong>SEGURIDAD</strong></span>
                                @php
                                    $algunaAccionSeguridad = false;
                                    foreach ($permisos['seguridad'] as $submodulo => $acciones) {
                                        if ($acciones['mostrar']) {
                                            $algunaAccionSeguridad = true;
                                            break;
                                        }
                                    }
                                @endphp
                                @if($algunaAccionSeguridad)
                                    <span class="badge bg-success me-2">Visible en menú</span>
                                @else
                                    <span class="badge bg-secondary me-2">Oculto en menú</span>
                                @endif
                            </div>
                        </button>
                    </h2>
                    <div id="collapseSeguridad" class="accordion-collapse collapse" aria-labelledby="headingSeguridad" data-bs-parent="#accordionPermisos">
                        <div class="accordion-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Submódulo</th>
                                        <th class="text-center" style="width: 13%">Mostrar</th>
                                        <th class="text-center" style="width: 13%">Ver</th>
                                        <th class="text-center" style="width: 13%">Crear</th>
                                        <th class="text-center" style="width: 13%">Editar</th>
                                        <th class="text-center" style="width: 13%">Eliminar</th>
                                    </thead>
                                <tbody>
                                    @foreach($permisos['seguridad'] as $submodulo => $acciones)
                                    <tr>
                                        <td>
                                            @if($submodulo == 'usuarios')
                                                <i class="bi bi-person-circle me-2"></i> Usuarios
                                            @elseif($submodulo == 'permisos')
                                                <i class="bi bi-key me-2"></i> Permisos
                                            @elseif($submodulo == 'respaldos')
                                                <i class="bi bi-database me-2"></i> Respaldos
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['mostrar'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['ver'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['crear'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['editar'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['eliminar'] ?? false)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- MÓDULO REPORTES -->
                @php
                    $tieneAlgunPermisoReportes = false;
                    foreach ($permisos['reportes'] as $submodulo => $acciones) {
                        if ($acciones['mostrar'] || $acciones['ver']) {
                            $tieneAlgunPermisoReportes = true;
                            break;
                        }
                    }
                @endphp
                
                @if($tieneAlgunPermisoReportes)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingReportes">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReportes" aria-expanded="false" aria-controls="collapseReportes">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <span><i class="bi bi-clipboard2-data me-2"></i> <strong>REPORTES</strong></span>
                                @php
                                    $algunaAccionReportes = false;
                                    foreach ($permisos['reportes'] as $submodulo => $acciones) {
                                        if ($acciones['mostrar']) {
                                            $algunaAccionReportes = true;
                                            break;
                                        }
                                    }
                                @endphp
                                @if($algunaAccionReportes)
                                    <span class="badge bg-success me-2">Visible en menú</span>
                                @else
                                    <span class="badge bg-secondary me-2">Oculto en menú</span>
                                @endif
                            </div>
                        </button>
                    </h2>
                    <div id="collapseReportes" class="accordion-collapse collapse" aria-labelledby="headingReportes" data-bs-parent="#accordionPermisos">
                        <div class="accordion-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70%">Reporte</th>
                                        <th class="text-center" style="width: 15%">Mostrar</th>
                                        <th class="text-center" style="width: 15%">Ver</th>
                                    </thead>
                                <tbody>
                                    @foreach($permisos['reportes'] as $submodulo => $acciones)
                                    <tr>
                                        <td>
                                            @if($submodulo == 'compras_cliente')
                                                <i class="bi bi-cart me-2"></i> Compras por Cliente
                                            @elseif($submodulo == 'frecuencia_compra')
                                                <i class="bi bi-bar-chart me-2"></i> Frecuencia de Compra
                                            @elseif($submodulo == 'montos_promedio')
                                                <i class="bi bi-calculator me-2"></i> Montos Promedio
                                            @elseif($submodulo == 'sucursales_preferidas')
                                                <i class="bi bi-house-heart me-2"></i> Sucursales Preferidas
                                            @elseif($submodulo == 'cotizaciones_cliente')
                                                <i class="bi bi-file-earmark-ruled me-2"></i> Cotizaciones por Cliente
                                            @elseif($submodulo == 'cotizaciones_concretadas')
                                                <i class="bi bi-clipboard2-check me-2"></i> Cotizaciones Concretadas
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['mostrar'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($acciones['ver'])
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                @if(!$tieneAlgunPermisoClientes && !$tieneAlgunPermisoVentas && !$tieneAlgunPermisoSeguridad && !$tieneAlgunPermisoReportes)
                <div class="text-center py-5">
                    <i class="bi bi-shield-slash" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Este usuario no tiene permisos asignados</p>
                </div>
                @endif
            </div>
        </div>
        <div class="card-footer bg-white">
            <a href="{{ route('seguridad.usuarios.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado de usuarios
            </a>
        </div>
    </div>
</div>
@endsection