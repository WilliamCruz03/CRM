@extends('layouts.app')

@section('title', 'Permisos de Usuario - CRM')
@section('page-title', 'Gestión de Permisos')

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

    <!-- Permisos por Módulo -->
    <div class="card">
        <div class="card-header bg-white">
            <span><i class="bi bi-grid-3x3-gap-fill"></i> Permisos por Submódulo</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%">Submódulo</th>
                            <th class="text-center" style="width: 15%">Mostrar/Ocultar</th>
                            <th class="text-center" style="width: 15%">Ver</th>
                            <th class="text-center" style="width: 15%">Altas</th>
                            <th class="text-center" style="width: 15%">Edición</th>
                            <th class="text-center" style="width: 10%">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $permisos = $usuario->permisos_formateados;
                        @endphp

                        <!-- ============================================ -->
                        <!-- MÓDULO CLIENTES -->
                        <!-- ============================================ -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>📁 CLIENTES</strong></td>
                            <td class="text-center">
                                <span class="badge {{ $permisos['clientes']['mostrar'] ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $permisos['clientes']['mostrar'] ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['clientes']['ver'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['clientes']['altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['clientes']['edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['clientes']['eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-list"></i> Directorio Clientes</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['clientes']['ver'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-heart-pulse"></i> Enfermedades</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['clientes']['enfermedades'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-star"></i> Intereses</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['clientes']['intereses'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>

                        <!-- ============================================ -->
                        <!-- MÓDULO VENTAS -->
                        <!-- ============================================ -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>📈 VENTAS</strong></td>
                            <td class="text-center">
                                <span class="badge {{ ($permisos['ventas']['mostrar'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($permisos['ventas']['mostrar'] ?? false) ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-file-text"></i> Cotizaciones</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['cotizaciones'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['cotizaciones_altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['cotizaciones_edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['cotizaciones_eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-receipt"></i> Pedidos Anticipo</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['pedidos_anticipo'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['pedidos_anticipo_altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['pedidos_anticipo_edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['pedidos_anticipo_eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-arrow-repeat"></i> Seguimiento Ventas</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_ventas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_ventas_altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_ventas_edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_ventas_eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-arrow-repeat"></i> Seguimiento Cotizaciones</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_cotizaciones'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_cotizaciones_altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_cotizaciones_edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['seguimiento_cotizaciones_eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-calendar-event"></i> Agenda Contactos</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['agenda_contactos'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['agenda_contactos_altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['agenda_contactos_edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['ventas']['agenda_contactos_eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>

                        <!-- ============================================ -->
                        <!-- MÓDULO SEGURIDAD -->
                        <!-- ============================================ -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>🔒 SEGURIDAD</strong></td>
                            <td class="text-center">
                                <span class="badge {{ ($permisos['seguridad']['mostrar'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($permisos['seguridad']['mostrar'] ?? false) ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-person-circle"></i> Usuarios</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['seguridad']['usuarios'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['seguridad']['usuarios_altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['seguridad']['usuarios_edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['seguridad']['usuarios_eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-key"></i> Permisos</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['seguridad']['permisos'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-database"></i> Respaldos</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['seguridad']['respaldos'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>

                        <!-- ============================================ -->
                        <!-- MÓDULO REPORTES -->
                        <!-- ============================================ -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>📊 REPORTES</strong></td>
                            <td class="text-center">
                                <span class="badge {{ ($permisos['reportes']['mostrar'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($permisos['reportes']['mostrar'] ?? false) ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-cart"></i> Compras por Cliente</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['reportes']['compras_cliente'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-bar-chart"></i> Frecuencia de Compra</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['reportes']['frecuencia_compra'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-calculator"></i> Montos Promedio</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['reportes']['montos_promedio'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-house-heart"></i> Sucursales Preferidas</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['reportes']['sucursales_preferidas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-file-earmark-ruled"></i> Cotizaciones por Cliente</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['reportes']['cotizaciones_cliente'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-clipboard2-check"></i> Cotizaciones Concretadas</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisos['reportes']['cotizaciones_concretadas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                    </tbody>
                </table>
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