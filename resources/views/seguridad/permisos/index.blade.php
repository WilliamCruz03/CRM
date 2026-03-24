@extends('layouts.app')

@section('title', 'Permisos de Usuarios - CRM')
@section('page-title', 'Permisos de Acceso por Usuario')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3><i class="bi bi-shield-lock"></i> Permisos de Acceso por Usuario</h3>
                <p class="text-muted mb-0">Visualización de permisos asignados a cada usuario del sistema</p>
            </div>
            <div>
                <button type="button" id="expandirTodosBtn" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bi bi-arrows-expand"></i> Expandir Todos
                </button>
                <button type="button" id="colapsarTodosBtn" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrows-collapse"></i> Colapsar Todos
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span><i class="bi bi-people"></i> Listado de Usuarios</span>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscarUsuario" class="form-control" placeholder="Buscar usuario por nombre...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="accordion" id="accordionPermisosUsuarios">
                @forelse($usuarios as $index => $usuario)
                    @php
                        // Filtrar solo módulos y submódulos que tienen al menos una acción
                        $permisosFiltrados = [];
                        $permisosCompletos = $usuario->permisos_formateados;
                        
                        // Procesar cada módulo
                        foreach ($permisosCompletos as $modulo => $submodulos) {
                            $submodulosFiltrados = [];
                            $tieneAlgunaAccion = false;
                            
                            foreach ($submodulos as $submodulo => $acciones) {
                                // Verificar si tiene al menos una acción activa
                                $tieneAccion = false;
                                foreach ($acciones as $accion => $valor) {
                                    if ($valor === true) {
                                        $tieneAccion = true;
                                        break;
                                    }
                                }
                                
                                if ($tieneAccion) {
                                    $submodulosFiltrados[$submodulo] = $acciones;
                                    $tieneAlgunaAccion = true;
                                }
                            }
                            
                            if ($tieneAlgunaAccion) {
                                $permisosFiltrados[$modulo] = $submodulosFiltrados;
                            }
                        }
                        
                        $tienePermisos = count($permisosFiltrados) > 0;
                        $usuarioId = 'usuario_' . $usuario->id_personal_empresa;
                    @endphp
                    
                    <div class="accordion-item usuario-item" data-nombre="{{ strtolower($usuario->nombre_completo) }} {{ strtolower($usuario->usuario) }}">
                        <h2 class="accordion-header" id="heading{{ $usuario->id_personal_empresa }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $usuario->id_personal_empresa }}" 
                                    aria-expanded="false" 
                                    aria-controls="collapse{{ $usuario->id_personal_empresa }}">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div>
                                        <i class="bi bi-person-circle me-2"></i>
                                        <strong>{{ $usuario->nombre_completo }}</strong>
                                        <span class="text-muted ms-2 small">({{ $usuario->usuario }})</span>
                                    </div>
                                    <div>
                                        @if($tienePermisos)
                                            <span class="badge bg-success me-2">
                                                <i class="bi bi-check-circle"></i> {{ count($permisosFiltrados) }} módulos con permisos
                                            </span>
                                        @else
                                            <span class="badge bg-secondary me-2">
                                                <i class="bi bi-shield-slash"></i> Sin permisos asignados
                                            </span>
                                        @endif
                                        <i class="bi bi-chevron-down collapse-icon ms-2"></i>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $usuario->id_personal_empresa }}" class="accordion-collapse collapse" 
                             aria-labelledby="heading{{ $usuario->id_personal_empresa }}" 
                             data-bs-parent="#accordionPermisosUsuarios">
                            <div class="accordion-body p-0">
                                @if($tienePermisos)
                                    <div class="permisos-container">
                                        @foreach($permisosFiltrados as $modulo => $submodulos)
                                            <div class="modulo-permisos mb-3">
                                                <div class="modulo-header bg-light p-3 border-bottom">
                                                    <h5 class="mb-0">
                                                        @if($modulo == 'clientes')
                                                            <i class="bi bi-card-checklist me-2 text-primary"></i>
                                                        @elseif($modulo == 'ventas')
                                                            <i class="bi bi-graph-up me-2 text-success"></i>
                                                        @elseif($modulo == 'seguridad')
                                                            <i class="bi bi-shield-lock me-2 text-danger"></i>
                                                        @elseif($modulo == 'reportes')
                                                            <i class="bi bi-clipboard2-data me-2 text-warning"></i>
                                                        @endif
                                                        {{ strtoupper($modulo) }}
                                                    </h5>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 35%">Submódulo</th>
                                                                <th class="text-center" style="width: 13%">Mostrar</th>
                                                                <th class="text-center" style="width: 13%">Ver</th>
                                                                @if($modulo != 'reportes')
                                                                    <th class="text-center" style="width: 13%">Crear</th>
                                                                    <th class="text-center" style="width: 13%">Editar</th>
                                                                    <th class="text-center" style="width: 13%">Eliminar</th>
                                                                @else
                                                                    <th class="text-center" style="width: 13%" colspan="3"></th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($submodulos as $submodulo => $acciones)
                                                                <tr>
                                                                    <td>
                                                                        @if($modulo == 'clientes')
                                                                            @if($submodulo == 'directorio')
                                                                                <i class="bi bi-list me-2"></i> Directorio Clientes
                                                                            @elseif($submodulo == 'enfermedades')
                                                                                <i class="bi bi-heart-pulse me-2"></i> Enfermedades
                                                                            @elseif($submodulo == 'intereses')
                                                                                <i class="bi bi-star me-2"></i> Intereses
                                                                            @endif
                                                                        @elseif($modulo == 'ventas')
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
                                                                        @elseif($modulo == 'seguridad')
                                                                            @if($submodulo == 'usuarios')
                                                                                <i class="bi bi-person-circle me-2"></i> Usuarios
                                                                            @elseif($submodulo == 'permisos')
                                                                                <i class="bi bi-key me-2"></i> Permisos
                                                                            @elseif($submodulo == 'respaldos')
                                                                                <i class="bi bi-database me-2"></i> Respaldos
                                                                            @endif
                                                                        @elseif($modulo == 'reportes')
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
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if($acciones['mostrar'] ?? false)
                                                                            <i class="bi bi-check-lg text-success fs-5"></i>
                                                                        @else
                                                                            <i class="bi bi-dash-lg text-secondary fs-5"></i>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if($acciones['ver'] ?? false)
                                                                            <i class="bi bi-check-lg text-success fs-5"></i>
                                                                        @else
                                                                            <i class="bi bi-dash-lg text-secondary fs-5"></i>
                                                                        @endif
                                                                    </td>
                                                                    @if($modulo != 'reportes')
                                                                        <td class="text-center">
                                                                            @if($acciones['crear'] ?? false)
                                                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                                                            @else
                                                                                <i class="bi bi-dash-lg text-secondary fs-5"></i>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($acciones['editar'] ?? false)
                                                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                                                            @else
                                                                                <i class="bi bi-dash-lg text-secondary fs-5"></i>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($acciones['eliminar'] ?? false)
                                                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                                                            @else
                                                                                <i class="bi bi-dash-lg text-secondary fs-5"></i>
                                                                            @endif
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bi bi-shield-slash" style="font-size: 2rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2 mb-0">Este usuario no tiene permisos asignados</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No hay usuarios registrados en el sistema</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-footer bg-white">
            <a href="{{ route('seguridad.usuarios.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Usuarios
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Buscador de usuarios
        $('#buscarUsuario').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            $('.usuario-item').each(function() {
                var nombre = $(this).data('nombre');
                if (nombre.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Expandir todos los acordeones
        $('#expandirTodosBtn').on('click', function() {
            $('.accordion-collapse').each(function() {
                var collapseElement = this;
                var bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseElement);
                bsCollapse.show();
            });
        });
        
        // Colapsar todos los acordeones
        $('#colapsarTodosBtn').on('click', function() {
            $('.accordion-collapse.show').each(function() {
                var collapseElement = this;
                var bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseElement);
                bsCollapse.hide();
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .permisos-container {
        padding: 0;
    }
    
    .modulo-permisos {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        margin: 15px;
    }
    
    .modulo-permisos:last-child {
        margin-bottom: 15px;
    }
    
    .modulo-header {
        background-color: #f8f9fa;
    }
    
    .modulo-header h5 {
        font-weight: 600;
    }
    
    .table th, .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    
    .accordion-button:not(.collapsed) .collapse-icon {
        transform: rotate(180deg);
    }
    
    .collapse-icon {
        transition: transform 0.2s ease;
    }
    
    .accordion-button {
        background-color: white;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0c63e4;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    
    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }
    
    .btn-outline-primary:hover, .btn-outline-secondary:hover {
        transform: translateY(-1px);
    }
    
    .btn-outline-primary, .btn-outline-secondary {
        transition: all 0.2s ease;
    }
</style>
@endpush
@endsection