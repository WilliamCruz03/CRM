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
                            // Obtener permisos del usuario (de la tabla permisos_personal)
                            $permisosUsuario = [];
                            foreach ($usuario->permisos as $permiso) {
                                if ($permiso->id_cliente_modulo) {
                                    $modulo = 'cliente';
                                } elseif ($permiso->id_ventas_modulo) {
                                    $modulo = 'ventas';
                                } elseif ($permiso->id_seguridad_modulo) {
                                    $modulo = 'seguridad';
                                } elseif ($permiso->id_reportes_modulo) {
                                    $modulo = 'reportes';
                                } else {
                                    continue;
                                }
                                
                                $accion = $permiso->accion->nombre ?? 'ver';
                                $permisosUsuario[$modulo][$accion] = $permiso->permitido;
                            }
                        @endphp

                        <!-- Módulo Clientes -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>📁 CLIENTES</strong></td>
                            <td class="text-center">
                                <span class="badge {{ ($permisosUsuario['cliente']['mostrar'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($permisosUsuario['cliente']['mostrar'] ?? false) ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['cliente']['ver'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['cliente']['altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['cliente']['edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['cliente']['eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-list"></i> Directorio Clientes</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['cliente']['clientes'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-heart-pulse"></i> Enfermedades</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['cliente']['enfermedades'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-star"></i> Intereses</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['cliente']['intereses'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>

                        <!-- Módulo Ventas -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>📈 VENTAS</strong></td>
                            <td class="text-center">
                                <span class="badge {{ ($permisosUsuario['ventas']['mostrar'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($permisosUsuario['ventas']['mostrar'] ?? false) ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['ventas']['ver'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['ventas']['altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['ventas']['edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['ventas']['eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-file-text"></i> Cotizaciones</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['ventas']['cotizaciones'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-receipt"></i> Pedidos Anticipo</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['ventas']['pedidos_anticipo'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>

                        <!-- Módulo Seguridad -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>🔒 SEGURIDAD</strong></td>
                            <td class="text-center">
                                <span class="badge {{ ($permisosUsuario['seguridad']['mostrar'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($permisosUsuario['seguridad']['mostrar'] ?? false) ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['seguridad']['ver'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['seguridad']['altas'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['seguridad']['edicion'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['seguridad']['eliminar'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-person-circle"></i> Usuarios</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['seguridad']['usuarios'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-key"></i> Permisos</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['seguridad']['permisos'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>

                        <!-- Módulo Reportes -->
                        <tr style="background-color: #f8f9fa;">
                            <td><strong>📊 REPORTES</strong></td>
                            <td class="text-center">
                                <span class="badge {{ ($permisosUsuario['reportes']['mostrar'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($permisosUsuario['reportes']['mostrar'] ?? false) ? 'Mostrar' : 'Ocultar' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['reportes']['ver'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
                            </td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                            <td class="text-center"><span class="text-muted">-</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4"><i class="bi bi-cart"></i> Compras por Cliente</td>
                            <td class="text-center"><span class="badge bg-secondary">N/A</span></td>
                            <td class="text-center">
                                <i class="bi {{ ($permisosUsuario['reportes']['compras_cliente'] ?? false) ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }} fs-5"></i>
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