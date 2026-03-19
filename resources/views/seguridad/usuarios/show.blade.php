@extends('layouts.app')

@section('title', 'Detalle de Usuario - CRM')
@section('page-title', 'Detalle de Usuario')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-person-vcard"></i> Detalle de Usuario</h3>
        <p class="text-muted">Información del usuario y permisos de acceso</p>
    </div>

    <!-- Información del Usuario -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-info-circle text-warning"></i> Datos del Usuario</span>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario">
                <i class="bi bi-pencil"></i> Editar Usuario
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">Usuario</div>
                    <div class="info-value h5">{{ $usuario->usuario }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Nombre completo</div>
                    <div class="info-value">{{ $usuario->nombre_completo }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Correo</div>
                    <div class="info-value">{{ $usuario->contacto ?? 'No especificado' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Estado</div>
                    <div class="info-value">
                        @if($usuario->Activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="info-label">Teléfono Móvil</div>
                    <div class="info-value">{{ $usuario->TelefonoMovil ?? 'No especificado' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Sucursal Origen</div>
                    <div class="info-value">{{ $usuario->sucursal_origen == 0 ? 'CRM' : 'Sucursal ' . $usuario->sucursal_origen }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Sucursal Asignada</div>
                    <div class="info-value">
                        @php
                            $sucursales = [
                                1 => 'Sucursal Mercado',
                                2 => 'Sucursal Jardin',
                                3 => 'Sucursal Zacatipan',
                                4 => 'Sucursal Boulevard',
                                5 => 'Sucursal smg',
                                6 => 'Sucursal sfo',
                                7 => 'Sucursal hug',
                                8 => 'Sucursal huc',
                            ];
                        @endphp
                        {{ $sucursales[$usuario->sucursal_asignada] ?? 'No asignada' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Fecha Ingreso</div>
                    <div class="info-value">{{ $usuario->fecha_ingreso ? \Carbon\Carbon::parse($usuario->fecha_ingreso)->format('d/m/Y') : 'No especificada' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permisos por Módulo -->
    <div class="card">
        <div class="card-header bg-white">
            <span><i class="bi bi-shield-lock"></i> Permisos de Acceso</span>
        </div>
        <div class="card-body">
            @php
                $permisos = $usuario->permisos_modulos ?? App\Models\PersonalEmpresa::getPermisosDefault();
            @endphp

            <!-- Módulo Clientes -->
            <div class="card mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <span><strong>Clientes</strong></span>
                    <span class="badge {{ $permisos['clientes']['mostrar'] ? 'bg-success' : 'bg-secondary' }}">
                        {{ $permisos['clientes']['mostrar'] ? 'Visible' : 'Oculto' }}
                    </span>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-2">
                            <span class="badge {{ $permisos['clientes']['ver'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi {{ $permisos['clientes']['ver'] ? 'bi-eye' : 'bi-eye-slash' }}"></i> Ver Directorio
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['clientes']['altas'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-plus-circle"></i> Altas
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['clientes']['edicion'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-pencil"></i> Edición
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['clientes']['eliminar'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-trash"></i> Eliminar
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['clientes']['enfermedades'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-heart-pulse"></i> Enfermedades
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['clientes']['intereses'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-star"></i> Intereses
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulo Ventas -->
            <div class="card mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <span><strong>Ventas</strong></span>
                    <span class="badge {{ $permisos['ventas']['mostrar'] ?? false ? 'bg-success' : 'bg-secondary' }}">
                        {{ ($permisos['ventas']['mostrar'] ?? false) ? 'Visible' : 'Oculto' }}
                    </span>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-2">
                            <span class="badge {{ $permisos['cotizaciones']['ver'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-file-text"></i> Cotizaciones
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['pedidos_anticipo']['ver'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-receipt"></i> Pedidos Anticipo
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguimiento_ventas']['ver'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-arrow-repeat"></i> Seguimiento Ventas
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguimiento_cotizaciones']['ver'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-arrow-repeat"></i> Seguimiento Cotizaciones
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['agenda_contactos']['ver'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-calendar-event"></i> Agenda Contactos
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulo Seguridad -->
            <div class="card mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <span><strong>Seguridad</strong></span>
                    <span class="badge {{ $permisos['seguridad']['mostrar'] ? 'bg-success' : 'bg-secondary' }}">
                        {{ $permisos['seguridad']['mostrar'] ? 'Visible' : 'Oculto' }}
                    </span>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguridad']['ver'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-person-circle"></i> Usuarios
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguridad']['altas'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-plus-circle"></i> Altas
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguridad']['edicion'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-pencil"></i> Edición
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguridad']['eliminar'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-trash"></i> Eliminar
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguridad']['permisos'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-key"></i> Permisos
                            </span>
                        </div>
                        <div class="col-2">
                            <span class="badge {{ $permisos['seguridad']['respaldos'] ?? false ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                <i class="bi bi-database"></i> Respaldos
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulo Reportes -->
            <div class="card mb-3">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <span><strong>Reportes</strong></span>
                    <span class="badge {{ $permisos['reportes']['mostrar'] ? 'bg-success' : 'bg-secondary' }}">
                        {{ $permisos['reportes']['mostrar'] ? 'Visible' : 'Oculto' }}
                    </span>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-4">
                            <span class="badge {{ $permisos['reportes']['compras_cliente'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                Compras por Cliente
                            </span>
                        </div>
                        <div class="col-4">
                            <span class="badge {{ $permisos['reportes']['frecuencia_compra'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                Frecuencia de Compra
                            </span>
                        </div>
                        <div class="col-4">
                            <span class="badge {{ $permisos['reportes']['montos_promedio'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                Montos Promedio
                            </span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-4">
                            <span class="badge {{ $permisos['reportes']['sucursales_preferidas'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                Sucursales Preferidas
                            </span>
                        </div>
                        <div class="col-4">
                            <span class="badge {{ $permisos['reportes']['cotizaciones_cliente'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                Cotizaciones x Cliente
                            </span>
                        </div>
                        <div class="col-4">
                            <span class="badge {{ $permisos['reportes']['cotizaciones_concretadas'] ? 'bg-success' : 'bg-secondary' }} p-2 w-100">
                                Cotizaciones Concretadas
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <a href="{{ route('seguridad.usuarios.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
@include('seguridad.usuarios.partials.modal-editar-usuario')
@endsection