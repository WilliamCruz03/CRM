<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - LBP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/layout_app.css') }}">

<style>
/* Estilos base para el contenedor de toasts */
.toast-container-center {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    max-width: 400px;
}

/* Transición más suave para los toasts */
.toast {
    /*
    opacity: 0;
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    */
    background: white !important;  /* Fondo blanco sólido */
    border: none;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Animación de entrada */
.toast-container-center .toast {
    animation: slideInRight 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes slideInRight {
    0% {
        transform: translateX(120px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Animación de salida más fluida */
.toast-container-center .toast.removing {
    animation: fadeOutRight 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards !important;
}

@keyframes fadeOutRight {
    0% {
        transform: translateX(0);
        opacity: 1;
    }
    100% {
        transform: translateX(60px);
        opacity: 0;
    }
}

/* Estilos para el header del toast */
.toast-header {
    border-bottom: none;
    padding: 0.75rem 1rem;
}

/* Estilos para el body del toast */
.toast-body {
    padding: 0;
}

/* Estilos para la barra de progreso */
.progress {
    height: 5px;
    border-radius: 0;
    overflow: hidden;
    background-color: rgba(0, 0, 0, 0.15);
}

.progress-bar {
    transition: width linear;
    height: 100%;
}

/* Ajuste para el botón de cerrar en toasts de advertencia */
.toast-header.bg-warning .btn-close {
    filter: brightness(0.7);
}
</style>

<style>
    .notification-badge {
        position: relative;
        cursor: pointer;
    }
    .notification-badge .bi-bell {
        font-size: 1.2rem;
        color: #6c757d;
    }
    .notification-badge .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    /* Estilos mejorados para el dropdown de notificaciones */
    .dropdown-menu {
        max-height: 400px;
        overflow-y: auto;
    }
    
    /* Ajustar posición del dropdown de notificaciones */
    #dropdownNotificaciones {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
        transform: translateX(0) !important;
        min-width: 280px;
        max-width: 400px;
        width: auto !important;
    }
    
    /* Los items del dropdown deben tener texto responsive */
    #dropdownNotificaciones .dropdown-item {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-word;
        padding: 12px 16px;
        line-height: 1.4;
    }
    
    /* El contenido debe ocupar todo el ancho disponible */
    #dropdownNotificaciones .flex-grow-1 {
        min-width: 0;
        overflow-wrap: break-word;
    }
    
    /* Ocultar resaltado de dropdown-items */
    .dropdown-item:active {
        background-color: transparent !important;
        color: : inherit !important;
    }

    .dropdown-item:focus {
        background-color: transparent !important;
        color: inherit !important;
        outline: none !important;
    }

    /* Mantener hover sutil */
    .dropdown-item:hover {
        background-color: #f8f9fa !important;
    }
    
    /* Para pantallas pequeñas */
    @media (max-width: 768px) {
        #dropdownNotificaciones {
            right: -20px !important;
            min-width: 260px;
            max-width: 300px;
        }
        
        #dropdownNotificaciones .dropdown-item {
            padding: 10px 12px;
            font-size: 0.85rem;
        }
    }
    
    /* Para pantallas muy pequeñas (móviles) */
    @media (max-width: 480px) {
        #dropdownNotificaciones {
            right: -10px !important;
            min-width: 240px;
            max-width: 280px;
        }
    }

    .highlight-row {
        animation: highlightFade 3s ease-in-out;
        background-color: #fff3cd !important;
    }

    @keyframes highlightFade {
        0% { background-color: #ffc107; }
        100% { background-color: transparent; }
    }
</style>

</head>
<body>    <div class="app-layout">
        <!-- SIDEBAR BACKDROP (MÓVIL) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- SIDEBAR -->
        <aside class="sidebar" id="mainSidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard.index') }}" class="sidebar-brand">
                    <div class="sidebar-brand-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div class="sidebar-brand-text">
                        <span class="sidebar-brand-title">CRM</span>
                        <span class="sidebar-brand-subtitle">La Botica del Pueblo</span>
                    </div>
                </a>
                <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ocultar Menú">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <!-- Menú principal -->
            <div class="sidebar-menu">
                <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                
                <!-- Clientes -->
                @php
                    $submodulosClientes = auth()->user()->submodulosVisibles('clientes') ?? [];
                    $isClientesActive = request()->routeIs('clientes.*') || request()->routeIs('enfermedades.*') || request()->routeIs('intereses.*');
                @endphp
                @if(count($submodulosClientes) > 0)
                    <div class="nav-collapse-toggle {{ $isClientesActive ? 'active' : '' }}" data-target="clientes-menu">
                        <span><i class="bi bi-people"></i> Clientes</span>
                        <i class="bi bi-chevron-down collapse-icon {{ $isClientesActive ? 'rotated' : '' }}"></i>
                    </div>
                    <div class="submenu {{ $isClientesActive ? 'show' : '' }}" id="clientes-menu">
                    @if(in_array('directorio', $submodulosClientes))
                    <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                        <i class="bi bi-list"></i> Directorio Clientes
                    </a>
                    @endif
                    
                    @if(in_array('enfermedades', $submodulosClientes))
                    <a href="{{ route('enfermedades.index') }}" class="nav-link {{ request()->routeIs('enfermedades.*') ? 'active' : '' }}">
                        <i class="bi bi-heart-pulse"></i> Enfermedades
                    </a>
                    @endif

                    @if(in_array('intereses', $submodulosClientes))
                    <a href="{{ route('intereses.index') }}" class="nav-link {{ request()->routeIs('intereses.*') ? 'active' : '' }}">
                        <i class="bi bi-star"></i> Intereses
                    </a>
                    @endif
                </div>
                @endif

                <!-- Ventas -->
                @php
                    $submodulosVentas = auth()->user()->submodulosVisibles('ventas') ?? [];
                    $isVentasActive = request()->routeIs('ventas.*') || request()->routeIs('recorridos.*');
                @endphp
                @if(count($submodulosVentas) > 0)
                <div class="nav-collapse-toggle {{ $isVentasActive ? 'active' : '' }}" data-target="ventas-menu">
                    <span><i class="bi bi-graph-up"></i> Ventas</span>
                    <i class="bi bi-chevron-down collapse-icon {{ $isVentasActive ? 'rotated' : '' }}"></i>
                </div>
                <div class="submenu {{ $isVentasActive ? 'show' : '' }}" id="ventas-menu">
                    @if(in_array('cotizaciones', $submodulosVentas))
                    <a href="{{ route('ventas.cotizaciones.index') }}" class="nav-link {{ request()->routeIs('ventas.cotizaciones.*') ? 'active' : '' }}">
                        <i class="bi bi-file-text"></i> Cotizaciones
                    </a>
                    @endif
                    
                    @if(in_array('pedidos_anticipo', $submodulosVentas))
                    <a href="{{ route('ventas.pedidos.index') }}" class="nav-link {{ request()->routeIs('ventas.pedidos.*') || request()->routeIs('recorridos.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i> Pedidos
                    </a>
                    @endif
                    
                    @if(in_array('agenda_contactos', $submodulosVentas))
                    <a href="{{ route('ventas.agenda_contactos.index') }}" class="nav-link {{ request()->routeIs('ventas.agenda_contactos.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event"></i> Agenda Contactos
                    </a>
                    @endif
                </div>
                @endif

                <!-- Seguridad -->
                @php
                    $submodulosSeguridad = auth()->user()->submodulosVisibles('seguridad') ?? [];
                    $isSeguridadActive = request()->routeIs('seguridad.*');
                @endphp
                @if(count($submodulosSeguridad) > 0)
                <div class="nav-collapse-toggle {{ $isSeguridadActive ? 'active' : '' }}" data-target="seguridad-menu">
                    <span><i class="bi bi-shield-lock"></i> Seguridad</span>
                    <i class="bi bi-chevron-down collapse-icon {{ $isSeguridadActive ? 'rotated' : '' }}"></i>
                </div>
                <div class="submenu {{ $isSeguridadActive ? 'show' : '' }}" id="seguridad-menu">
                    @if(in_array('usuarios', $submodulosSeguridad))
                    <a href="{{ route('seguridad.usuarios.index') }}" class="nav-link {{ request()->routeIs('seguridad.usuarios.*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i> Usuarios
                    </a>
                    @endif
                    
                    @if(in_array('permisos', $submodulosSeguridad))
                    <a href="{{ route('seguridad.permisos.index') }}" class="nav-link {{ request()->routeIs('seguridad.permisos.*') ? 'active' : '' }}">
                        <i class="bi bi-key"></i> Permisos
                    </a>
                    @endif
                    
                    @if(in_array('respaldos', $submodulosSeguridad))
                        <a href="{{ route('seguridad.respaldos.index') }}" class="nav-link {{ request()->routeIs('seguridad.respaldos.*') ? 'active' : '' }}">
                            <i class="bi bi-database"></i> Respaldos
                        </a>
                    @endif
                </div>  
                @endif

                <!-- Reportes -->
                @php
                    $submodulosReportes = auth()->user()->submodulosVisibles('reportes');
                    $ventasReportes = array_intersect(['compras_cliente', 'frecuencia_compra', 'montos_promedio', 'sucursales_preferidas'], $submodulosReportes);
                    $cotizacionesReportes = array_intersect(['cotizaciones_cliente', 'pedidos_cliente'], $submodulosReportes);
                    $isReportesActive = request()->routeIs('reportes.*');
                @endphp

                @if(count($submodulosReportes) > 0)
                <div class="nav-collapse-toggle {{ $isReportesActive ? 'active' : '' }}" data-target="reportes-menu">
                    <span><i class="bi bi-bar-chart"></i> Reportes</span>
                    <i class="bi bi-chevron-down collapse-icon {{ $isReportesActive ? 'rotated' : '' }}"></i>
                </div>
                <div class="submenu {{ $isReportesActive ? 'show' : '' }}" id="reportes-menu">
                    
                    <!-- Submenú Ventas (solo si tiene al menos un submódulo visible) -->
                    @if(count($ventasReportes) > 0)
                    @php
                        $isVentasReportesActive = request()->routeIs('reportes.compras_cliente.*') || request()->routeIs('reportes.sucursales-preferidas*');
                    @endphp
                    <div class="nav-collapse-toggle {{ $isVentasReportesActive ? 'active' : '' }}" data-target="ventas-reportes-submenu">
                        <span><i class="bi bi-graph-up"></i> Ventas</span>
                        <i class="bi bi-chevron-down collapse-icon {{ $isVentasReportesActive ? 'rotated' : '' }}"></i>
                    </div>
                    <div class="submenu {{ $isVentasReportesActive ? 'show' : '' }}" id="ventas-reportes-submenu">
                        @if(in_array('compras_cliente', $submodulosReportes))
                        <a href="{{ route('reportes.compras_cliente.clientes') }}" class="nav-link {{ request()->routeIs('reportes.compras_cliente.clientes*') ? 'active' : '' }}">
                            <i class="bi bi-cart"></i> Compras por Cliente
                        </a>
                        @endif

                        @if(in_array('busqueda_articulo', $submodulosReportes))
                        <a href="{{ route('reportes.compras_cliente.busqueda-articulo.index') }}"
                        class="nav-link {{ request()->routeIs('reportes.compras_cliente.busqueda-articulo.*') ? 'active' : '' }}">
                            <i class="bi bi-upc-scan"></i> Búsqueda por Artículo
                        </a>
                        @endif
                        
                        @if(in_array('frecuencia_compra', $submodulosReportes))
                        <a href="{{ route('reportes.compras_cliente.frecuencia-compra') }}" class="nav-link {{ request()->routeIs('reportes.compras_cliente.frecuencia-compra*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart"></i> Frecuencia de compra por Cliente
                        </a>
                        @endif
                        
                        @if(in_array('montos_promedio', $submodulosReportes))
                        <a href="{{ route('reportes.compras_cliente.montos-promedio') }}" class="nav-link {{ request()->routeIs('reportes.compras_cliente.montos-promedio*') ? 'active' : '' }}">
                            <i class="bi bi-calculator"></i> Montos promedios
                        </a>
                        @endif
                        
                        @if(in_array('sucursales_preferidas', $submodulosReportes))
                        <a href="{{ route('reportes.sucursales-preferidas') }}" class="nav-link {{ request()->routeIs('reportes.sucursales-preferidas*') ? 'active' : '' }}">
                            <i class="bi bi-house-heart"></i> Sucursales Preferidas
                        </a>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Submenú Cotizaciones (solo si tiene al menos un submódulo visible) -->
                    @if(count($cotizacionesReportes) > 0)
                    @php
                        $isCotizacionesReportesActive = request()->routeIs('reportes.cotizaciones-cliente.*') || request()->routeIs('reportes.pedidos-cliente.*');
                    @endphp
                    <div class="nav-collapse-toggle {{ $isCotizacionesReportesActive ? 'active' : '' }}" data-target="cotizaciones-reportes-submenu">
                        <span><i class="bi bi-file-text"></i> Cotizaciones</span>
                        <i class="bi bi-chevron-down collapse-icon {{ $isCotizacionesReportesActive ? 'rotated' : '' }}"></i>
                    </div>
                    <div class="submenu {{ $isCotizacionesReportesActive ? 'show' : '' }}" id="cotizaciones-reportes-submenu">
                        @if(in_array('cotizaciones_cliente', $submodulosReportes))
                        <a href="{{ route('reportes.cotizaciones-cliente.index') }}" class="nav-link {{ request()->routeIs('reportes.cotizaciones-cliente.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-ruled"></i> Cotizaciones Clientes
                        </a>
                        @endif
                        
                        @if(in_array('pedidos_cliente', $submodulosReportes))
                        <a href="{{ route('reportes.pedidos-cliente.index') }}" class="nav-link {{ request()->routeIs('reportes.pedidos-cliente.*') ? 'active' : '' }}">
                            <i class="bi bi-clipboard2-check"></i> Pedidos Clientes
                        </a>
                        @endif
                    </div>
                    @endif
                    
                </div>
                @endif
            </div>

            <!-- PERFIL DE USUARIO (DENTRO DEL SIDEBAR) -->
            @php
                $userName = Auth::user()->nombre_completo ?? 'Usuario';
                $nameParts = array_values(array_filter(explode(' ', trim($userName))));
                $userInitials = 'U';
                if (count($nameParts) >= 2) {
                    $userInitials = mb_strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[1], 0, 1));
                } elseif (count($nameParts) == 1 && !empty($nameParts[0])) {
                    $userInitials = mb_strtoupper(mb_substr($nameParts[0], 0, 2));
                }
                $userProfile = Auth::user()->perfil ?? 'Usuario';
            @endphp
            <div class="sidebar-user">
                <div class="user-profile d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                        <div class="user-avatar-wrapper">
                            <div class="user-avatar">
                                {{ $userInitials }}
                            </div>
                            <span class="user-status-indicator" title="Usuario en línea"></span>
                        </div>
                        <div class="user-info">
                            <div class="user-name" title="{{ $userName }}">{{ $userName }}</div>
                            <div class="user-role">{{ $userProfile }}</div>
                        </div>
                    </div>
                    <a href="#"
                       class="user-logout-btn"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Cerrar Sesión">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>

            <!-- FECHA Y HORA DEL SISTEMA -->
            <div class="sidebar-footer">
                <div class="text-center">
                    <div class="sidebar-fecha" id="sidebarFecha">
                        {{ now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                    </div>
                </div>
            </div>
        </aside>

<style>
/*
Estilos para la fecha en el navbar
*/
.sidebar-footer {
    padding: 12px 15px;
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background-color: rgba(0, 0, 0, 0.1);
}

.sidebar-fecha {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
    margin-bottom: 4px;
}

.sidebar-hora {
    font-size: 1.1rem;
    font-weight: bold;
    color: #fff;
    font-variant-numeric: tabular-nums;
}
</style>

        <!-- CONTENT WRAPPER -->
        <div class="content-wrapper">
            <!-- TOPBAR -->
            <div class="topbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="topbar-toggle-btn" id="sidebarToggleBtn" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Mostrar/Ocultar Menú">
                        <i class="bi bi-list"></i>
                    </button>
                    <h6>@yield('page-title', 'Dashboard')</h6>
                </div>
                <div class="topbar-actions">
                    <!-- Campana de notificaciones -->
                    <div class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="campanaNotificaciones" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative;">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-danger" id="contadorNotificaciones" style="display: none; position: absolute; top: -5px; right: -10px; font-size: 0.7rem;">0</span>
                        </a>
                        <div class="dropdown-menu" id="dropdownNotificaciones" aria-labelledby="campanaNotificaciones" style="min-width: 350px; max-width: 420px; width: auto; max-height: 500px; overflow-y: auto;">
                            <h6 class="dropdown-header" id="dropdownHeaderNotificaciones">
                                Notificaciones
                                <span class="badge bg-primary rounded-pill" id="contadorHistorialNotificaciones" style="display: none; font-size: 0.6rem;">0</span>
                            </h6>
                            
                            <!-- Notificaciones pendientes -->
                            <div id="listaNotificaciones">
                                <div class="dropdown-item text-muted text-center">Cargando...</div>
                            </div>
                            
                            <!-- Historial (últimas 5) -->
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-header bg-light">
                                <small class="text-muted">Historial reciente</small>
                            </div>
                            <div id="listaHistorialNotificaciones" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-center py-2 text-muted small">Cargando historial...</div>
                            </div>
                            
                            <div class="dropdown-divider"></div>
                            <div class="text-center">
                                <a href="#" class="dropdown-item text-primary small" onclick="cargarHistorialNotificaciones(); return false;">
                                    <i class="bi bi-arrow-repeat"></i> Recargar historial
                                </a>
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-primary">CRM v1.0</span>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="main-content">
                @yield('content')
            </div>
        </div> <!-- Cierra content-wrapper -->
    </div>

    <!-- MODALS GLOBALES -->
    @include('clientes.partials.modal-nuevo-cliente')
    @include('clientes.partials.modal-editar-cliente')
    @include('partials.modal-confirmar-eliminar')

    <!-- Overlay para bloqueo de pantalla por sesión caducada -->
    <div id="sessionExpiredOverlay" class="session-expired-overlay" style="display: none;">
        <div class="modal-content">
            <div class="mb-3">
                <i class="bi bi-clock-history" style="font-size: 48px; color: #dc3545;"></i>
            </div>
            <h5 class="mb-3 text-danger" id="overlayTitle">Sesión finalizada</h5>
            <p class="overlay-message" id="overlayMessage">Tu sesión ha caducado.</p>
            <button id="forceLogoutBtn" class="btn btn-danger mt-3">Aceptar</button>
        </div>
    </div>

<script src="{{ asset('js/seguimiento.js') }}"></script>
<script src="{{ asset('js/layout_app.js') }}"></script>

<script>
// ============================================
// SISTEMA DE VERIFICACION DE SESION Y USUARIO - MEJORADO
// ============================================

// ============================================
// SINGLE-FLIGHT PARA VERIFICACIONES DE SESION
// ============================================

let currentStatusCheck = null;
let currentCheckId = 0;
let lastSuccessfulCheck = 0;

// ============================================
// REDIRECCION AUTOMATICA DESDE LA RAIZ
// ============================================

// Si estamos en la raiz y no hay sesion activa, redirigir al login
(function() {
    const isRoot = window.location.pathname === '/' || window.location.pathname === '';
    if (isRoot) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken || csrfToken === '') {
            window.location.href = '/login?expired=1';
        }
    }
})();

// ============================================
// MANEJO MEJORADO DE BFCACHE CON NAVIGATION TIMING API V2
// ============================================

(function() {
    // Usar Navigation Timing API v2 (reemplaza performance.navigation deprecado)
    const navEntry = performance.getEntriesByType('navigation')[0];
    const isBackForward = navEntry?.type === 'back_forward';
    const isReload = navEntry?.type === 'reload';
    
    if (isBackForward) {
        window.location.reload(true);
        return;
    }
    
    // Evento pageshow: se dispara cuando la página se muestra
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload(true);
        }
    });
    
    // Evento beforeunload: ayudar a prevenir BFCache
    window.addEventListener('beforeunload', function() {
        // Limpiar cualquier cosa que pueda causar BFCache
    });
    
    // Usar checkUserStatus centralizado en lugar de fetch directo
    setTimeout(() => {
        checkUserStatus();
    }, 500);
})();

let bfcacheHandled = false;

window.addEventListener('pageshow', function(event) {
    if (event.persisted && !bfcacheHandled) {
        bfcacheHandled = true;
        
        setTimeout(() => {
            // Usar checkUserStatus centralizado
            checkUserStatus().then(() => {
                if (lastSuccessfulCheck > 0) {
                    bfcacheHandled = false;
                } else {
                    window.location.href = '/login?expired=1';
                }
            });
        }, 100);
    }
});

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        sessionStorage.setItem('bfcache_check_needed', 'true');
    } else {
        const checkNeeded = sessionStorage.getItem('bfcache_check_needed');
        if (checkNeeded === 'true') {
            sessionStorage.removeItem('bfcache_check_needed');
            // Usar checkUserStatus centralizado
            checkUserStatus();
        }
    }
});

// ============================================
// MANEJO DE REFRESH DE PAGINA CON SESION ACTIVA
// ============================================

(function() {
    // Usar Navigation Timing API v2
    const navEntry = performance.getEntriesByType('navigation')[0];
    const isReload = navEntry?.type === 'reload';
    
    if (isReload) {
        // Verificar si hay sesion activa
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken && csrfToken.length > 10) {
            // Usar checkUserStatus centralizado
            checkUserStatus().then(() => {
                if (lastSuccessfulCheck === 0) {
                    refreshCsrfToken(true).then(() => {
                        window.location.reload();
                    });
                }
            });
        }
    }
})();

// ============================================
// FUNCIONES EXISTENTES
// ============================================

// Funcion para mostrar overlay de sesion expirada
function showSessionExpiredOverlay(message) {
    let overlay = document.getElementById('sessionExpiredOverlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sessionExpiredOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        `;
        
        overlay.innerHTML = `
            <div style="
                background: white;
                padding: 40px;
                border-radius: 15px;
                max-width: 500px;
                width: 90%;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            ">
                <div style="font-size: 48px; margin-bottom: 20px;">🚫</div>
                <h2 style="color: #dc3545; margin-bottom: 15px;">Usuario Desactivado</h2>
                <p style="color: #6c757d; margin-bottom: 20px;">${message}</p>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p style="color: #6c757d; margin-top: 15px; font-size: 14px;">
                    Redirigiendo al login...
                </p>
            </div>
        `;
        
        document.body.appendChild(overlay);
    }
    
    overlay.style.display = 'flex';
}

// Funcion para manejar usuario desactivado
window.handleInactiveUser = function() {
    if (window._isRedirecting) return;
    window._isRedirecting = true;
    
    if (window.sessionCheckInterval) {
        clearInterval(window.sessionCheckInterval);
    }
    
    showSessionExpiredOverlay('Tu cuenta ha sido desactivada. Contacta al administrador.');
    
    setTimeout(() => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .finally(() => {
            window.location.href = '/login';
        });
    }, 3000);
};

// ============================================
// FUNCION UNIFICADA PARA REFRESCAR CSRF TOKEN
// ============================================

async function refreshCsrfToken(updateInputs = false) {
    try {
        const response = await fetch('/api/refresh-csrf', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            cache: 'no-store',
            credentials: 'same-origin'
        });

        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return false;
        }

        if (response.ok) {
            const data = await response.json();
            if (data.success && data.csrf_token) {
                // Actualizar meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.csrf_token);
                }
                
                if (updateInputs) {
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.csrf_token;
                    });
                }
                return true;
            }
        }
        return false;
    } catch (error) {
        return false;
    }
}

// ============================================
// REDIRECCION DESDE RAIZ CON VERIFICACION
// ============================================

function checkRootRedirect() {
    const isRoot = window.location.pathname === '/' || window.location.pathname === '';
    if (!isRoot) {
        return;
    }
    
    // Usar checkUserStatus centralizado
    checkUserStatus().then(() => {
        if (!lastSuccessfulCheck) {
            window.location.href = '/login?expired=1';
        }
    });
}

// ============================================
// MANEJO DE ERROR 419 (PAGE EXPIRED) EN LOGIN
// ============================================

function handleLoginPage() {
    // Detectar si estamos en la pagina de login por la URL o por el formulario
    const isLoginPage = window.location.pathname === '/login' || 
                        window.location.pathname === '/login/' ||
                        document.querySelector('form[action*="/login"]') !== null;
    
    if (!isLoginPage) {
        return;
    }
    
    // Verificar si venimos de una sesion expirada (por URL o por referencia)
    const urlParams = new URLSearchParams(window.location.search);
    const fromExpired = urlParams.get('expired') === '1';
    const hasLoginForm = document.querySelector('form[action*="/login"]') !== null;
    
    // Siempre refrescar CSRF en la pagina de login, especialmente si hay formulario
    if (hasLoginForm) {
        refreshCsrfToken(true).then((success) => {
            if (success) {
                // Limpiar URL si tiene parametro expired
                if (fromExpired) {
                    window.history.replaceState({}, document.title, '/login');
                }
            }
        });
    }
    
    // Interceptar el formulario de login para prevenir 419
    const loginForm = document.querySelector('form[action*="/login"]');
    if (loginForm) {
        // Remover listeners anteriores para evitar duplicados
        loginForm.removeEventListener('submit', handleLoginSubmit);
        loginForm.addEventListener('submit', handleLoginSubmit);
    }
}

// Funcion para manejar el submit del formulario de login
function handleLoginSubmit(e) {
    const tokenInput = this.querySelector('input[name="_token"]');
    if (!tokenInput) {
        return;
    }
    
    // Si el token esta vacio o es muy corto, refrescar
    if (!tokenInput.value || tokenInput.value.length < 10) {
        e.preventDefault();
        
        refreshCsrfToken(true).then(() => {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (tokenInput && metaTag) {
                tokenInput.value = metaTag.getAttribute('content');
                this.submit();
            }
        });
    }
}

// ============================================
// INTERCEPTOR PARA LOGOUT
// ============================================

function setupLogoutInterceptor() {
    const logoutForm = document.querySelector('form[action*="/logout"]');
    if (!logoutForm) {
        return;
    }
    
    logoutForm.removeEventListener('submit', handleLogoutSubmit);
    logoutForm.addEventListener('submit', handleLogoutSubmit);
}

function handleLogoutSubmit(e) {
    const tokenInput = this.querySelector('input[name="_token"]');
    if (!tokenInput) {
        return;
    }
    
    if (!tokenInput.value || tokenInput.value.length < 10) {
        e.preventDefault();
        
        refreshCsrfToken(true).then(() => {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (tokenInput && metaTag) {
                tokenInput.value = metaTag.getAttribute('content');
                this.submit();
            }
        });
    }
}

// ============================================
// INTERCEPTOR GLOBAL DE FETCH - MEJORADO
// ============================================

(function() {
    'use strict';

    // Variable para evitar redirecciones multiples
    let isRedirecting = false;

    // Solo confiar en 401/419 y JSON explícito
    async function requiresLogin(response) {
        // Solo 401 y 419 son sesión expirada
        if (response.status === 401 || response.status === 419) {
            return true;
        }

        // 500 es error del servidor, NO sesión
        if (response.status === 500) {
            return false;
        }

        // Solo confiar en JSON con campos explícitos
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            try {
                const data = await response.clone().json();
                return data.requires_login === true ||
                       ['session_expired', 'csrf_invalid', 'user_inactive'].includes(data.reason);
            } catch (e) {
                return false;
            }
        }

        // TODO lo demás (403, 404, 422, HTML) NO fuerza logout
        return false;
    }

    // Guardar referencia al fetch original
    const originalFetch = window.fetch;

    // Sobrescribir fetch
    window.fetch = function(url, options = {}) {
        // EXCEPCIONES UNIFICADAS - No interceptar estas rutas
        if (typeof url === 'string' && (
            url.includes('/reportes/') || 
            url.includes('/ventas/cotizaciones/catalogos') ||
            url.includes('/user/check-status')
        )) {
            return originalFetch(url, options);
        }

        // Agregar headers para AJAX
        if (!options.headers) {
            options.headers = {};
        }

        // Agregar Accept: application/json para peticiones que no son GET
        if (options.method && options.method !== 'GET') {
            options.headers['Accept'] = 'application/json';
            options.headers['X-Requested-With'] = 'XMLHttpRequest';
        }

        // Agregar CSRF token si existe
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken && !options.headers['X-CSRF-TOKEN']) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        // Agregar cache: no-store para evitar BFCache
        if (!options.cache) {
            options.cache = 'no-store';
        }

        // Agregar credentials
        if (!options.credentials) {
            options.credentials = 'same-origin';
        }

        // Ejecutar fetch original
        return originalFetch(url, options)
            .then(async response => {
                // Si es una respuesta de exito, retornarla normalmente
                // Log para depurar respuestas no exitosas
                if (!response.ok) {
                }

                if (response.ok) {
                    return response;
                }

                // Verificar si requiere login
                const needsLogin = await requiresLogin(response);

                if (needsLogin) {
                    // Intentar recuperar CSRF antes de redirigir
                    const refreshed = await refreshCsrfToken(true);
                    if (refreshed) {
                        options.headers['X-CSRF-TOKEN'] = document
                            .querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const retryResponse = await originalFetch(url, options);
                        if (retryResponse.ok) {
                            return retryResponse;
                        }
                        // Si el reintento falla, es realmente una sesión expirada
                    }

                    if (isRedirecting) {
                        throw new Error('Redirigiendo al login...');
                    }

                    isRedirecting = true;
                    window._isRedirecting = true;

                    // Mostrar mensaje al usuario
                    let message = 'Tu sesion ha expirado. Redirigiendo al login...';
                    
                    // Intentar obtener mensaje mas especifico
                    try {
                        const clonedResponse = response.clone();
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            const data = await clonedResponse.json();
                            if (data.message) {
                                message = data.message;
                            }
                        }
                    } catch (e) {}

                    if (window.mostrarToast) {
                        window.mostrarToast(message, 'warning');
                    }

                    // Esperar 1.5 segundos para mostrar el mensaje
                    await new Promise(resolve => setTimeout(resolve, 1500));

                    // Limpiar intervalo de verificacion si existe
                    if (window.sessionCheckInterval) {
                        clearInterval(window.sessionCheckInterval);
                    }
                    if (connectionCheckInterval) {
                        clearInterval(connectionCheckInterval);
                    }
                    if (heartbeatInterval) {
                        clearInterval(heartbeatInterval);
                    }

                    // Redirigir al login con indicador de sesion expirada
                    window.location.href = '/login?expired=1';
                    
                    throw new Error('Sesion expirada - Redirigiendo al login');
                }

                // Si no requiere login pero es error, retornar la respuesta original
                return response;
            })
            .catch(error => {
                // IGNORAR AbortError (son normales al cancelar búsquedas)
                if (error.name === 'AbortError' || error.code === 20) {
                    return;
                }
                
                if (error.message && !error.message.includes('Redirigiendo')) {
                    console.error('Error en fetch interceptado:', error);
                }
                throw error;
            });
    };
})();

// ============================================
// VERIFICACION DE SESION - SINGLE FLIGHT
// ============================================

let checkAttempts = 0;
const MAX_CHECK_ATTEMPTS = 3;

async function checkUserStatus() {
    // Si ya hay una verificación en curso, reutilizarla
    if (currentStatusCheck) {
        return currentStatusCheck;
    }

    const myId = ++currentCheckId;
    const controller = new AbortController();

    currentStatusCheck = fetch('/user/check-status', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache'
        },
        cache: 'no-store',
        credentials: 'same-origin',
        signal: controller.signal
    })
    .then(async response => {
        // Si ya hay una verificación más nueva, ignorar esta respuesta
        if (myId !== currentCheckId) {
            return;
        }

        if (!response.ok) {
            // Si es 401 o 419, intentar refrescar CSRF antes de fallar
            if (response.status === 401 || response.status === 419) {
                checkAttempts++;
                
                const refreshed = await refreshCsrfToken(true);
                if (refreshed) {
                    const retry = await fetch('/user/check-status', {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'Cache-Control': 'no-cache'
                        },
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (retry.ok) {
                        const data = await retry.json();
                        if (data.active !== false) {
                            lastSuccessfulCheck = Date.now();
                            checkAttempts = 0;
                            return;
                        }
                    }
                }
                
                // Solo mostrar toast despues de varios intentos fallidos
                if (checkAttempts >= MAX_CHECK_ATTEMPTS) {
                    if (window.mostrarToast) {
                        window.mostrarToast('Sesion expirada. Recarga la pagina.', 'warning');
                    }
                    checkAttempts = 0;
                }
                return;
            }
            return;
        }

        const data = await response.json();

        // Si el usuario está inactivo (desactivado)
        if (data && data.active === false) {
            if (window.handleInactiveUser) {
                window.handleInactiveUser();
            } else {
                showSessionExpiredOverlay('Tu cuenta ha sido desactivada.');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
            }
            return;
        }

        // Verificación exitosa
        lastSuccessfulCheck = Date.now();
        checkAttempts = 0;
        return;

    })
    .catch(error => {
        if (error.name === 'AbortError') {
            return;
        }
    })
    .finally(() => {
        if (myId === currentCheckId) {
            currentStatusCheck = null;
        }
    });

    return currentStatusCheck;
}

// ============================================
// MONITOREO DE CONEXION AL SERVIDOR
// ============================================

let connectionCheckInterval = null;
let connectionAttempts = 0;
const MAX_CONNECTION_ATTEMPTS = 3;
let lastKnownServerState = true;

async function checkServerConnection() {
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);
        
        // Usar la ruta existente /user/check-status
        const response = await fetch('/user/check-status', {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            cache: 'no-store',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        // Cualquier respuesta (incluso 401 o 403) significa que el servidor esta vivo
        if (response.status !== 0) {
            // Si el servidor estaba desconectado y ahora responde
            if (!lastKnownServerState) {
                if (window.mostrarToast) {
                    window.mostrarToast('Servidor reconectado correctamente', 'success');
                }
                lastKnownServerState = true;
            }
            connectionAttempts = 0;
            return true;
        }
        return false;
    } catch (error) {
        const errorMsg = error.message || '';
        
        // Solo mostrar warning si realmente es un error de conexion
        if (errorMsg.includes('Failed to fetch') || 
            errorMsg.includes('NetworkError') || 
            errorMsg.includes('ERR_CONNECTION') ||
            errorMsg === 'The user aborted a request') {
            
            connectionAttempts++;
            
            // Si el servidor estaba conectado y ahora falla, notificar
            if (lastKnownServerState) {
                lastKnownServerState = false;
                if (window.mostrarToast) {
                    window.mostrarToast('Problema de conexion con el servidor', 'warning');
                }
            }
            
            if (connectionAttempts >= MAX_CONNECTION_ATTEMPTS) {
                if (window.mostrarToast) {
                    window.mostrarToast(
                        'El servidor no responde. Verifica tu conexion de red.', 
                        'danger'
                    );
                }
                connectionAttempts = 0;
            }
        }
        return false;
    }
}

// ============================================
// HEARTBEAT CON KEEP-ALIVE
// ============================================

let heartbeatInterval = null;
let heartbeatAttempts = 0;

async function sendHeartbeat() {
    try {
        // Verificar si estamos autenticados
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            return;
        }
        
        // Agregar headers de cache
        const response = await fetch('/keep-alive', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            cache: 'no-store',
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            // Sesión activa, resetear intentos
            heartbeatAttempts = 0;
        } else if (response.status === 401) {
        }
    } catch (error) {
        heartbeatAttempts++;
        if (heartbeatAttempts >= 3) {
            heartbeatAttempts = 0;
        }
    }
}

// ============================================
// INICIALIZACION PRINCIPAL
// ============================================

// Ejecutar al cargar el DOM
document.addEventListener('DOMContentLoaded', function() {
    
    checkRootRedirect();
    
    setTimeout(checkUserStatus, 1000);

    // Verificar cada 45 segundos (reducido para evitar sobrecarga)
    window.sessionCheckInterval = setInterval(checkUserStatus, 45000);

    // Verificar cuando la pestaña recupera visibilidad
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(checkUserStatus, 500);
        }
    });

    let csrfRefreshInterval = setInterval(function() {
        refreshCsrfToken(false);
    }, 15 * 60 * 1000);

    // Limpiar intervalos cuando la pagina se descarga
    window.addEventListener('beforeunload', function() {
        if (window.sessionCheckInterval) clearInterval(window.sessionCheckInterval);
        if (csrfRefreshInterval) clearInterval(csrfRefreshInterval);
        if (connectionCheckInterval) clearInterval(connectionCheckInterval);
        if (heartbeatInterval) clearInterval(heartbeatInterval);
        if (window.notificacionesInterval) clearInterval(window.notificacionesInterval);
    });
    
    setTimeout(function() {
        checkServerConnection();
        connectionCheckInterval = setInterval(checkServerConnection, 30000);
    }, 10000);
    
    const isAuthenticated = document.querySelector('meta[name="csrf-token"]') !== null;
    if (isAuthenticated) {
        // Desfasar 20s respecto a checkUserStatus para que nunca coincidan
        // en el mismo tick y compitan por escribir la sesión al mismo tiempo
        setTimeout(() => {
            heartbeatInterval = setInterval(sendHeartbeat, 45000);
        }, 20000);
        
        // RECARGAR NOTIFICACIONES CADA 2 MINUTOS
        window.notificacionesInterval = setInterval(() => {
            if (!document.hidden && typeof recargarNotificaciones === 'function') {
                recargarNotificaciones();
            }
        }, 120000); // 2 minutos
    }
    
    setTimeout(handleLoginPage, 100);
    setTimeout(setupLogoutInterceptor, 200);
});

// Tambien ejecutar handleLoginPage inmediatamente si el DOM ya esta cargado
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(handleLoginPage, 100);
    setTimeout(setupLogoutInterceptor, 200);
}

// Hacer funciones globales
window.checkUserStatus = checkUserStatus;
window.refreshCsrfToken = refreshCsrfToken;
window.checkServerConnection = checkServerConnection;
window.handleLoginPage = handleLoginPage;
window.checkRootRedirect = checkRootRedirect;
window.setupLogoutInterceptor = setupLogoutInterceptor;

</script>

<script>
    // Variable global para controlar notificaciones en tiempo real
    window.notificacionesEnTiempoReal = false;
    window.notificaciones = [];
    window.totalNotificacionesSistema = 0;
</script>

<script>
// ============================================
// NOTIFICACIONES
// ============================================

function getModuloActual() {
    const path = window.location.pathname;
    if (path.includes('/ventas/cotizaciones')) {
        return 'cotizaciones';
    } else if (path.includes('/ventas/pedidos')) {
        return 'pedidos';
    } else if (path.includes('/ventas/agenda-contactos')) {
        return 'agenda_contactos';
    }
    return 'dashboard';
}

function actualizarHeaderNotificaciones(tipo) {
    const header = document.getElementById('dropdownHeaderNotificaciones');
    if (!header) return;
    
    switch(tipo) {
        case 'cotizaciones':
            header.textContent = 'Cotizaciones que requieren atención';
            break;
        case 'pedidos':
            header.textContent = 'Pedidos pendientes';
            break;
        case 'contactos':
            header.textContent = 'Próximos contactos';
            break;
        default:
            header.textContent = 'Notificaciones';
    }
}

let notificacionesTimeout = null;
let notificacionesIntentos = 0;
const MAX_NOTIFICACIONES_INTENTOS = 3;

function cargarNotificaciones() {
    const contadorSpan = document.getElementById('contadorNotificaciones');
    const listaNotificaciones = document.getElementById('listaNotificaciones');
    const modulo = getModuloActual();
    
    if (!listaNotificaciones) return;
    
    // Si ya hay un timeout programado, cancelarlo
    if (notificacionesTimeout) {
        clearTimeout(notificacionesTimeout);
        notificacionesTimeout = null;
    }
    
    // Mostrar estado de carga solo si no hay notificaciones actualmente
    const hasContent = listaNotificaciones.children.length > 0;
    if (!hasContent) {
        listaNotificaciones.innerHTML = '<div class="dropdown-item text-muted text-center">Cargando...</div>';
    }
    
    fetch(`/notificaciones?modulo=${modulo}`)
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Respuesta no es JSON');
        }
        if (!response.ok) {
            throw new Error(`Error ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Éxito - Resetear intentos
        notificacionesIntentos = 0;
        
        if (data && data.success) {
            const notificaciones = data.data || [];
            const total = data.total || 0;
            
            if (contadorSpan) {
                if (total > 0) {
                    contadorSpan.textContent = total;
                    contadorSpan.style.display = 'inline-block';
                } else {
                    contadorSpan.style.display = 'none';
                }
            }
            
            if (notificaciones.length > 0) {
                let html = '';
                
                notificaciones.forEach(notif => {
                    let icono = 'bi-bell';
                    let color = 'text-secondary';
                    let badgeText = '';
                    let badgeColor = '';
                    
                    // Determinar tipo y estilo
                    if (notif.tipo === 'contacto') {
                        icono = notif.icono || 'bi-exclamation-triangle';
                        color = `text-${notif.color || 'warning'}`;
                        badgeText = 'Contacto';
                        badgeColor = 'bg-warning';
                    } else if (notif.tipo === 'cotizacion') {
                        icono = 'bi-file-earmark-text';
                        color = 'text-danger';
                        badgeText = 'Cotización';
                        badgeColor = 'bg-danger';
                    } else if (notif.tipo === 'pedido') {
                        icono = 'bi-box-seam';
                        color = 'text-warning';
                        badgeText = 'Pedido';
                        badgeColor = 'bg-warning';
                    } else if (notif.tipo === 'pedido_listo') {
                        icono = 'bi-box-seam';
                        color = 'text-success';
                        badgeText = 'Listo';
                        badgeColor = 'bg-success';
                    } else if (notif.tipo === 'pedido_asignado') {
                        icono = 'bi-truck';
                        color = 'text-primary';
                        badgeText = 'Asignado';
                        badgeColor = 'bg-primary';
                    } else {
                        icono = 'bi-bell';
                        color = 'text-info';
                        badgeText = 'Nueva';
                        badgeColor = 'bg-info';
                    }
                    
                    // Construir contenido
                    let contenidoHtml = '';
                    const idNotificacion = notif.id || null;
                    const esPersistente = notif.es_persistente === true;
                    
                    if (notif.tipo === 'contacto') {
                        contenidoHtml = `
                            <strong>${escapeHtml(notif.cliente)}</strong><br>
                            <small class="text-muted">${escapeHtml(notif.asunto)}</small><br>
                            <small class="${color}">
                                <i class="bi ${notif.icono || 'bi-exclamation-triangle'} me-1"></i>
                                ${escapeHtml(notif.mensaje)}
                            </small>
                        `;
                    } else if (notif.tipo === 'cotizacion' || notif.tipo === 'pedido') {
                        contenidoHtml = `
                            <strong>${escapeHtml(notif.folio || notif.cliente)}</strong>
                            ${badgeText ? `<span class="badge ${badgeColor} ms-1">${badgeText}</span>` : ''}
                            <br>
                            <small class="${color}">${escapeHtml(notif.mensaje)}</small>
                        `;
                    } else if (notif.tipo === 'pedido_listo' || notif.tipo === 'pedido_asignado') {
                        const titulo = notif.titulo || notif.folio || 'Notificación';
                        const mensaje = notif.mensaje || '';
                        const sucursales = notif.datos_extra?.sucursales || '';
                        const fecha = notif.created_at || '';
                        
                        contenidoHtml = `
                            <strong>${escapeHtml(titulo)}</strong>
                            ${badgeText ? `<span class="badge ${badgeColor} ms-1">${badgeText}</span>` : ''}
                            <br>
                            <small class="text-muted">${escapeHtml(mensaje)}</small>
                            ${sucursales ? `<br><small class="text-muted">Sucursales: ${escapeHtml(sucursales)}</small>` : ''}
                            ${fecha ? `<br><small class="text-muted" style="font-size: 0.7rem;">${escapeHtml(fecha)}</small>` : ''}
                        `;
                    } else {
                        contenidoHtml = `
                            <strong>${escapeHtml(notif.titulo || notif.folio || 'Notificación')}</strong>
                            <br>
                            <small class="text-muted">${escapeHtml(notif.mensaje)}</small>
                        `;
                    }
                    
                    // Item sin redirección
                    if (esPersistente && idNotificacion) {
                        // Notificaciones de la tabla: con botón X para marcar como leída
                        html += `
                            <div class="dropdown-item" style="cursor: default;">
                                <div class="d-flex align-items-start">
                                    <i class="bi ${icono} ${color} me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        ${contenidoHtml}
                                    </div>
                                    <button type="button" class="btn-close btn-close-sm" style="font-size: 0.6rem;" 
                                            onclick="event.stopPropagation(); marcarLeida(${idNotificacion}, this)"
                                            title="Marcar como leída"></button>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                        `;
                    } else {
                        // Notificaciones de sistema (agenda, cotizaciones, pedidos)
                        const url = notif.url || '#';
                        html += `
                            <a class="dropdown-item" href="${url}">
                                <div class="d-flex align-items-start">
                                    <i class="bi ${icono} ${color} me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        ${contenidoHtml}
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                        `;
                    }
                });
                
                listaNotificaciones.innerHTML = html;
                actualizarHeaderNotificaciones(data.tipo || modulo);
            } else {
                // Sin notificaciones
                if (contadorSpan) contadorSpan.style.display = 'none';
                listaNotificaciones.innerHTML = `<div class="dropdown-item text-muted text-center">No hay notificaciones pendientes</div>`;
                actualizarHeaderNotificaciones(data.tipo || modulo);
            }
        } else {
            // Datos inválidos
            throw new Error(data.mensaje_general || 'Datos inválidos');
        }
    })
    .catch(error => {
        notificacionesIntentos++;
        
        // Si hay contenido actual, mantenerlo (no sobrescribir)
        if (!hasContent) {
            let mensaje = 'No se pudieron cargar las notificaciones';
            if (notificacionesIntentos < MAX_NOTIFICACIONES_INTENTOS) {
                mensaje += ` (reintentando en 5s...)`;
            }
            listaNotificaciones.innerHTML = `
                <div class="dropdown-item text-muted text-center">
                    <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                    ${mensaje}
                </div>
            `;
        }
        
        // Si no hemos superado el máximo de intentos, reintentar
        if (notificacionesIntentos < MAX_NOTIFICACIONES_INTENTOS) {
            if (notificacionesTimeout) {
                clearTimeout(notificacionesTimeout);
            }
            notificacionesTimeout = setTimeout(() => {
                cargarNotificaciones();
            }, 5000); // Reintentar después de 5 segundos
        } else {
            // Máximo de intentos alcanzado - mostrar mensaje final
            listaNotificaciones.innerHTML = `
                <div class="dropdown-item text-muted text-center">
                    <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                    No se pudieron cargar las notificaciones
                    <br>
                    <small class="text-muted">Recarga la página para intentar de nuevo</small>
                </div>
            `;
            // Resetear contador para futuros intentos
            notificacionesIntentos = 0;
        }
    });
}

// Función para forzar recarga de notificaciones (útil para el heartbeat)
function recargarNotificaciones() {
    // Resetear intentos y forzar recarga
    notificacionesIntentos = 0;
    if (notificacionesTimeout) {
        clearTimeout(notificacionesTimeout);
        notificacionesTimeout = null;
    }
    cargarNotificaciones();
}

function mostrarNotificacionesCombinadas() {
    const listaNotificaciones = document.getElementById('listaNotificaciones');
    const contadorSpan = document.getElementById('contadorNotificaciones');
    
    if (!listaNotificaciones) return;
    
    // Calcular totales
    const totalReverb = window.notificaciones ? window.notificaciones.length : 0;
    
    // Buscar notificaciones de sistema en la lista actual
    const itemsSistema = listaNotificaciones.querySelectorAll('.dropdown-item:not(.text-muted)');
    let totalSistema = 0;
    itemsSistema.forEach(item => {
        // Las de sistema tienen <a> (son cliqueables)
        if (item.querySelector('a')) {
            totalSistema++;
        }
    });
    
    const totalGeneral = totalSistema + totalReverb;
    
    // Actualizar contador
    if (contadorSpan) {
        if (totalGeneral > 0) {
            contadorSpan.textContent = totalGeneral;
            contadorSpan.style.display = 'inline-block';
        } else {
            contadorSpan.style.display = 'none';
        }
    }
    
    // Si no hay Reverb y no hay sistema, mostrar mensaje
    if (totalGeneral === 0) {
        listaNotificaciones.innerHTML = '<div class="dropdown-item text-muted text-center">No hay notificaciones pendientes</div>';
        return;
    }
    
    // Si solo hay Reverb y no hay sistema
    if (totalReverb > 0 && totalSistema === 0) {
        let html = '';
        window.notificaciones.forEach(notif => {
            html += generarHtmlNotificacionReverb(notif);
        });
        listaNotificaciones.innerHTML = html;
        return;
    }
    
    // Si hay sistema y Reverb, combinarlos
    if (totalSistema > 0 && totalReverb > 0) {
        let html = '';
        
        // Mantener las de sistema
        itemsSistema.forEach(item => {
            if (item.querySelector('a')) {
                html += item.outerHTML;
            }
        });
        
        // Agregar las de Reverb
        window.notificaciones.forEach(notif => {
            html += generarHtmlNotificacionReverb(notif);
        });
        
        listaNotificaciones.innerHTML = html;
    }
}

function generarHtmlNotificacionReverb(notif) {
    const icono = notif.tipo === 'pedido' ? 'bi-box-seam' : 'bi-bell';
    const color = 'text-success';
    const badgeText = notif.es_asignacion === true ? 'Asignado' : 'Listo';
    const mensajeMostrar = notif.es_asignacion === true
        ? `Se te ha asignado el Pedido ${notif.folio}`
        : (notif.mensaje || 'Puede asignar repartidor');
    
    return `
        <div class="dropdown-item" data-timestamp="${escapeHtml(notif.timestamp)}" style="cursor: default;">
            <div class="d-flex align-items-start">
                <i class="bi ${icono} ${color} me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>${escapeHtml(notif.folio || 'Pedido')}</strong>
                    <span class="badge bg-success ms-1">${badgeText}</span>
                    <br>
                    <small class="text-muted">${escapeHtml(mensajeMostrar)}</small>
                    ${notif.sucursales ? `<br><small class="text-muted">Sucursales: ${escapeHtml(notif.sucursales)}</small>` : ''}
                    <br>
                    <small class="text-muted" style="font-size: 0.7rem;">
                        ${new Date(notif.timestamp).toLocaleTimeString()}
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-sm" style="font-size: 0.6rem;" 
                        onclick="event.stopPropagation(); eliminarNotificacion('${notif.timestamp}')"></button>
            </div>
        </div>
        <div class="dropdown-divider"></div>
    `;
}

function contarNotificacionesSistema() {
    const lista = document.getElementById('listaNotificaciones');
    if (!lista) return 0;
    
    // Las notificaciones de sistema son enlaces directos con clase dropdown-item
    // Las notificaciones de sistema son cargadas correctamente, ahora necesitamos guardar las notificaciones
    // de cotizacion por vencer para poder marcar como leida, peero esta en discusion porque debemos obligar
    // al operador a dar seguimiento a una cotizacion que no ha finalizado
    return lista.querySelectorAll('a.dropdown-item').length;
}

// ============================================
// MARCAR NOTIFICACIÓN COMO LEÍDA
// ============================================
function marcarLeida(id, btn) {
    if (!id) {
        console.warn('marcarLeida: id vacío');
        return;
    }
    
    // Feedback inmediato
    const item = btn ? btn.closest('.dropdown-item') : null;
    if (item) {
        item.style.opacity = '0.4';
        item.style.pointerEvents = 'none';
    }
    
    fetch(`/notificaciones/${id}/leer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Quitar el item del DOM inmediatamente
            if (item) {
                const divider = item.nextElementSibling;
                if (divider && divider.classList.contains('dropdown-divider')) {
                    divider.remove();
                }
                item.remove();
            }
            
            // Actualizar contador
            actualizarContadorGeneral();
            
            // Recargar historial para reflejar la nueva leída
            if (typeof cargarHistorialNotificaciones === 'function') {
                cargarHistorialNotificaciones();
            }
            
            // Si ya no hay notificaciones pendientes, mostrar mensaje
            const lista = document.getElementById('listaNotificaciones');
            if (lista && lista.querySelectorAll('a.dropdown-item, .dropdown-item[data-timestamp]').length === 0) {
                lista.innerHTML = '<div class="dropdown-item text-muted text-center">No hay notificaciones pendientes</div>';
            }
        } else {
            console.error('Error al marcar como leída:', data.message);
            // Restaurar opacidad
            if (item) {
                item.style.opacity = '';
                item.style.pointerEvents = '';
            }
            if (window.mostrarToast) {
                window.mostrarToast(data.message || 'Error al marcar como leída', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (item) {
            item.style.opacity = '';
            item.style.pointerEvents = '';
        }
        if (window.mostrarToast) {
            window.mostrarToast('Error al marcar como leída', 'danger');
        }
    });
}

// ============================================
// RESALTAR REGISTRO DESDE NOTIFICACIÓN
// ============================================
window.resaltarRegistro = function(tipo, id, selector) {
    let selectorFinal = '';
    let moduloUrl = '';
    
    switch(tipo) {
        case 'cotizacion':
            selectorFinal = `tr[id*="cotizacion-row-${id}"], tr[data-id-cotizacion="${id}"]`;
            moduloUrl = '/ventas/cotizaciones';
            break;
        case 'pedido':
            selectorFinal = `tr[id*="pedido-row-${id}"], tr[data-id-pedido="${id}"]`;
            moduloUrl = '/ventas/pedidos';
            break;
        case 'contacto':
            selectorFinal = `tr[data-id-agenda="${id}"], tr[id*="agenda-row-${id}"]`;
            moduloUrl = '/ventas/agenda-contactos';
            break;
        default:
            selectorFinal = selector || `[data-id="${id}"]`;
            moduloUrl = window.location.pathname;
    }
    
    // Si estamos en el módulo correcto, resaltar
    if (window.location.pathname.includes(moduloUrl) || moduloUrl === window.location.pathname) {
        setTimeout(() => {
            const fila = document.querySelector(selectorFinal);
            
            if (fila) {
                fila.classList.add('table-warning', 'highlight-row');
                fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                setTimeout(() => {
                    fila.classList.remove('table-warning', 'highlight-row');
                }, 3000);
            } else {
                // Si no encuentra la fila, reintentar después de un breve retraso
                setTimeout(() => {
                    const filaReintento = document.querySelector(selectorFinal);
                    if (filaReintento) {
                        filaReintento.classList.add('table-warning', 'highlight-row');
                        filaReintento.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        setTimeout(() => {
                            filaReintento.classList.remove('table-warning', 'highlight-row');
                        }, 3000);
                    }
                }, 1000);
            }
        }, 500);
    }
};

// Verificar si hay un ID destacar en la URL al cargar la página
function verificarDestacarUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const destacarId = urlParams.get('destacar');
    const destacarTipo = urlParams.get('destacar_tipo');
    
    if (destacarId) {
        // Remover los parámetros de la URL sin recargar
        urlParams.delete('destacar');
        urlParams.delete('destacar_tipo');
        const nuevaUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, document.title, nuevaUrl);
        
        // Esperar a que la página esté completamente cargada
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                window.resaltarRegistro(destacarTipo || 'cotizacion', destacarId);
            });
        } else {
            window.resaltarRegistro(destacarTipo || 'cotizacion', destacarId);
        }
    }
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    verificarDestacarUrl();
});

function escapeHtml(str) {
    // Manejar null, undefined, números, etc.
    if (str === null || str === undefined) return '';
    // Convertir a string si es número u otro tipo
    if (typeof str !== 'string') str = String(str);
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Cargar notificaciones iniciales (sin abrir dropdown)
document.addEventListener('DOMContentLoaded', function() {
    cargarNotificaciones();
});
</script>

<script>
// ============================================
// MARCAR SUBMENÚ ACTIVO SEGÚN LA URL ACTUAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const mainSidebar = document.getElementById('mainSidebar');
    const isCollapsed = mainSidebar && mainSidebar.classList.contains('collapsed');

    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (!href || href === '#') return;

        let linkPath = href;
        try {
            const urlObj = new URL(href, window.location.origin);
            linkPath = urlObj.pathname;
        } catch (e) {
            linkPath = href;
        }

        if (linkPath === currentPath || (linkPath !== '/' && currentPath.startsWith(linkPath))) {
            link.classList.add('active');

            // NO abrir submenús si el sidebar está colapsado
            if (isCollapsed) return;

            // Abrir TODOS los submenús padres
            let element = link;
            while (element) {
                const submenu = element.closest('.submenu');
                if (submenu) {
                    const id = submenu.id;
                    const toggle = document.querySelector(`[data-target="${id}"]`);

                    submenu.classList.add('show');

                    if (toggle) {
                        toggle.classList.add('active');
                        const icon = toggle.querySelector('.collapse-icon');
                        if (icon) icon.classList.add('rotated');
                    }

                    element = submenu.parentElement;
                } else {
                    break;
                }
            }
        }
    });
});

// ============================================
// MANEJO DE CAIDA DEL SERVIDOR
// ============================================

let serverDownAttempts = 0;
const MAX_SERVER_DOWN_ATTEMPTS = 5;
let isServerDown = false;

// Función para detectar cuando el servidor está caído
function detectServerDown() {
    isServerDown = true;
    serverDownAttempts++;
    
    if (serverDownAttempts >= MAX_SERVER_DOWN_ATTEMPTS) {
        if (window.mostrarToast) {
            window.mostrarToast(
                'El servidor no responde después de varios intentos. Verifica tu conexión.',
                'danger'
            );
        }
        
        // Mostrar overlay de servidor caído
        showServerDownOverlay();
    }
}

function showServerDownOverlay() {
    let overlay = document.getElementById('serverDownOverlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'serverDownOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        `;
        
        overlay.innerHTML = `
            <div style="
                background: white;
                padding: 40px;
                border-radius: 15px;
                max-width: 500px;
                width: 90%;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            ">
                <div style="font-size: 48px; margin-bottom: 20px;"><i class="bi bi-wifi-off text-danger"></i></div>
                <h2 style="color: #dc3545; margin-bottom: 15px;">Servidor no disponible</h2>
                <p style="color: #6c757d; margin-bottom: 20px;">
                    El servidor no está respondiendo. Por favor, verifica tu conexión de red o contacta al administrador.
                </p>
                <button onclick="manualReconnect()" class="btn btn-primary">
                    Intentar reconectar
                </button>
                <br>
                <small style="color: #6c757d; display: block; margin-top: 15px;">
                    Intentos fallidos: ${serverDownAttempts}
                </small>
            </div>
        `;
        
        document.body.appendChild(overlay);
    }
    
    overlay.style.display = 'flex';
}

function manualReconnect() {
    const overlay = document.getElementById('serverDownOverlay');
    if (overlay) overlay.style.display = 'none';
    
    serverDownAttempts = 0;
    isServerDown = false;
    
    if (window.mostrarToast) {
        window.mostrarToast('Intentando reconectar...', 'info');
    }
    
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

// Modificar el interceptor de fetch para detectar caída del servidor
(function() {
    const originalFetch = window.fetch;
    
    window.fetch = function(url, options = {}) {
        return originalFetch(url, options)
            .catch(error => {
                // Si es un error de conexión
                if (error.message && (
                    error.message.includes('Failed to fetch') ||
                    error.message.includes('NetworkError') ||
                    error.message.includes('ERR_CONNECTION_TIMED_OUT') ||
                    error.message.includes('ERR_CONNECTION_RESET')
                )) {
                    detectServerDown();
                }
                throw error;
            });
    };
})();

// Resetear estado cuando el servidor responde
function resetServerDownState() {
    if (isServerDown) {
        isServerDown = false;
        serverDownAttempts = 0;
        
        const overlay = document.getElementById('serverDownOverlay');
        if (overlay) overlay.style.display = 'none';
        
        if (window.mostrarToast) {
            window.mostrarToast('Servidor reconectado correctamente', 'success');
        }
    }
}

// Modificar checkServerConnection para resetear estado
const originalCheckServerConnection = window.checkServerConnection || function(){};
window.checkServerConnection = async function() {
    try {
        const result = await originalCheckServerConnection();
        if (result) {
            resetServerDownState();
        }
        return result;
    } catch (error) {
        return false;
    }
};
</script>

@auth
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userId = {{ auth()->user()->id_personal_empresa }};
        const esCRM = {{ auth()->user()->es_crm ? 'true' : 'false' }};
        
        // Inicializar array de notificaciones
        window.notificaciones = [];
        
        // Evento al abrir la campana: mostrar notificaciones
        const dropdownElement = document.getElementById('campanaNotificaciones');
        if (dropdownElement) {
            dropdownElement.addEventListener('shown.bs.dropdown', function() {
                // Recargar notificaciones del backend (por si cambiaron)
                if (typeof cargarNotificaciones === 'function') {
                    cargarNotificaciones();
                }
                // Y cargar historial
                cargarHistorialNotificaciones();
            });
        }
        
        if (window.Echo) {
            // SOLO SI ES CRM: suscribir a canales de CRM
            if (esCRM) {
                // ============================================
                // 1. PEDIDO MARCADO COMO LISTO (SOLO CRM)
                // ============================================
                window.Echo.private('crm-notifications')
                    .listen('.pedido.marcado.listo', (e) => {
                        
                        // VERIFICAR SI LA NOTIFICACIÓN YA FUE LEÍDA
                        fetch(`/notificaciones/verificar/${e.pedido_id}`, {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error al verificar notificación');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success && data.leida) {
                                return;
                            }
                            mostrarNotificacionPedidoListo(e);
                        })
                        .catch(error => {
                            console.warn('Error al verificar notificación, mostrando de todas formas:', error);
                            mostrarNotificacionPedidoListo(e);
                        });
                        
                        function mostrarNotificacionPedidoListo(e) {
                            if (window.mostrarToast) {
                                let toastMensaje = `Pedido ${e.folio_pedido} listo`;
                                if (!e.tiene_repartidor) {
                                    toastMensaje += ' - Puede asignar repartidor';
                                }
                                window.mostrarToast(toastMensaje, 'success');
                            }
                            
                            agregarNotificacionCampana({
                                titulo: e.titulo || `${e.folio_pedido} Listo`,
                                mensaje: e.mensaje,
                                folio: e.folio_pedido,
                                pedido_id: e.pedido_id,
                                tipo: 'pedido',
                                url: `/ventas/pedidos/${e.pedido_id}`,
                                timestamp: e.timestamp,
                                sucursales: e.sucursales_listas,
                                tiene_repartidor: e.tiene_repartidor
                            });
                            actualizarContadorGeneral();
                        }
                    })
                    .error((error) => {
                        console.error('Error en canal CRM:', error);
                    });
                
                // ============================================
                // 1.5 NOTIFICACIÓN MARCADA COMO LEÍDA (SOLO CRM)
                // ============================================
                window.Echo.private('crm-notifications')
                    .listen('.notificacion.leida', (e) => {
                        
                        const lista = document.getElementById('listaNotificaciones');
                        if (lista) {
                            const items = lista.querySelectorAll('.dropdown-item');
                            items.forEach(item => {
                                const idAttr = item.getAttribute('data-id');
                                if (idAttr && parseInt(idAttr) === e.id_notificacion) {
                                    const divider = item.nextElementSibling;
                                    if (divider && divider.classList.contains('dropdown-divider')) {
                                        divider.remove();
                                    }
                                    item.remove();
                                    
                                    actualizarContadorGeneral();
                                    
                                    const remainingItems = lista.querySelectorAll('.dropdown-item:not(.text-muted)');
                                    if (remainingItems.length === 0) {
                                        lista.innerHTML = '<div class="dropdown-item text-muted text-center">No hay notificaciones pendientes</div>';
                                        const contador = document.getElementById('contadorNotificaciones');
                                        if (contador) contador.style.display = 'none';
                                    }
                                }
                            });
                        }
                        
                        if (typeof cargarHistorialNotificaciones === 'function') {
                            setTimeout(() => cargarHistorialNotificaciones(), 500);
                        }
                    })
                    .error((error) => {
                        console.error('Error en canal CRM (notificacion.leida):', error);
                    });
            }
            
            // ============================================
            // 2. PEDIDO ASIGNADO A REPARTIDOR (SOLO REPARTIDOR)
            // ============================================
            // Evento: Pedido asignado a repartidor
            window.Echo.private(`user-notifications.${userId}`)
                .listen('.pedido.asignado.repartidor', (e) => {
                    
                    // Toast en verde (success)
                    if (window.mostrarToast) {
                        window.mostrarToast(e.mensaje, 'success');
                    }
                    
                    // Agregar a la campana con flag de asignación
                    agregarNotificacionCampana({
                        titulo: e.titulo,
                        mensaje: e.mensaje,
                        folio: e.folio_pedido,
                        pedido_id: e.pedido_id,
                        tipo: 'pedido',
                        url: `/ventas/pedidos/${e.pedido_id}`,
                        timestamp: e.timestamp,
                        sucursales: e.sucursales_listas,
                        es_asignacion: true
                    });
                })
                .error((error) => {
                    console.error('Error en canal de repartidor:', error);
                });
            
            // ============================================
            // 3. (OPCIONAL) Canal general para otros eventos
            // ============================================
            window.Echo.private('pedidos-notifications')
                .listen('.pedido.nuevo', (e) => {
                    // Para futuros eventos como "Nuevo pedido creado"
                })
                .error((error) => {
                    console.error('Error en canal general:', error);
                });
            
            // Marcar que las notificaciones en tiempo real están activas
            window.notificacionesEnTiempoReal = true;
        } else {
            console.error('Echo no está disponible');
        }
    });
        
    // ============================================
    // CARGAR HISTORIAL DE NOTIFICACIONES
    // ============================================
    function cargarHistorialNotificaciones() {
        const listaHistorial = document.getElementById('listaHistorialNotificaciones');
        const contadorHistorial = document.getElementById('contadorHistorialNotificaciones');
        
        if (!listaHistorial) return;
        
        listaHistorial.innerHTML = '<div class="text-center py-3 text-muted small">Cargando historial...</div>';
        
        fetch('/notificaciones/historial?limit=5', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificaciones = data.data || [];
                const noLeidas = data.no_leidas || 0;
                
                if (contadorHistorial) {
                    contadorHistorial.textContent = noLeidas;
                    contadorHistorial.style.display = noLeidas > 0 ? 'inline-block' : 'none';
                }
                
                if (notificaciones.length === 0) {
                    listaHistorial.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox" style="font-size: 1.5rem;"></i>
                            <p class="mb-0 small">No hay notificaciones en el historial</p>
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                notificaciones.forEach(notif => {
                    // Todas son leídas (historial)
                    const icono = notif.tipo === 'pedido_listo' ? 'bi-box-seam' : 
                                notif.tipo === 'pedido_asignado' ? 'bi-truck' : 'bi-bell';
                    const color = 'text-secondary';
                    const badgeLeida = '<span class="badge bg-secondary ms-1">Leída</span>';
                    const fecha = notif.created_at || '';
                    
                    html += `
                        <div class="historial-item d-flex align-items-start py-2 border-bottom bg-light">
                            <i class="bi ${icono} ${color} me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <div>
                                    <strong>${escapeHtml(notif.titulo)}</strong>
                                    ${badgeLeida}
                                </div>
                                <small class="text-muted">${escapeHtml(notif.mensaje)}</small>
                                <div class="mt-1">
                                    <small class="text-muted" style="font-size: 0.7rem;">${escapeHtml(fecha)}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                listaHistorial.innerHTML = html;
            } else {
                listaHistorial.innerHTML = `
                    <div class="text-center py-3 text-muted small">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                        Error al cargar el historial
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            listaHistorial.innerHTML = `
                <div class="text-center py-3 text-muted small">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    Error de conexión
                </div>
            `;
        });
    }

    // ============================================
    // MARCAR COMO LEÍDA DESDE HISTORIAL
    // ============================================
    function marcarLeidaHistorial(id) {
        fetch(`/notificaciones/${id}/leer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar historial y contador
                cargarHistorialNotificaciones();
                cargarNotificaciones();
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // ============================================
    // FUNCIÓN PARA AGREGAR NOTIFICACIONES A LA CAMPANA
    // ============================================
    function agregarNotificacionCampana(notificacion) {
        const contadorSpan = document.getElementById('contadorNotificaciones');
        
        if (!window.notificaciones) window.notificaciones = [];
        window.notificaciones.unshift(notificacion);
        
        // USAR LA VARIABLE GLOBAL EN LUGAR DE CONTAR EN EL DOM
        if (contadorSpan) {
            const totalSistema = window.totalNotificacionesSistema || 0;
            const totalReverb = window.notificaciones.length;
            const totalGeneral = totalSistema + totalReverb;
            
            
            if (totalGeneral > 0) {
                contadorSpan.textContent = totalGeneral;
                contadorSpan.style.display = 'inline-block';
            } else {
                contadorSpan.style.display = 'none';
            }
        }
        
        // Si la campana está abierta, actualizar la lista
        const dropdown = document.getElementById('campanaNotificaciones');
        if (dropdown && dropdown.classList.contains('show')) {
            actualizarListaNotificaciones();
        }
    }

    // ============================================
    // ACTUALIZAR LISTA DE NOTIFICACIONES
    // ============================================
    function actualizarListaNotificaciones() {
        const lista = document.getElementById('listaNotificaciones');
        if (!lista) return;
        
        const notificaciones = window.notificaciones || [];
        
        // Si no hay notificaciones de Reverb, NO tocar la lista
        // (las de sistema ya están renderizadas por cargarNotificaciones)
        if (notificaciones.length === 0) {
            actualizarContadorGeneral();
            return;
        }
        
        // Obtener las notificaciones de sistema actualmente en la lista
        const sistemaActuales = lista.querySelectorAll('a.dropdown-item');
        
        // Construir el HTML: sistema primero, Reverb después
        let html = '';
        
        sistemaActuales.forEach(item => {
            html += item.outerHTML;
            html += '<div class="dropdown-divider"></div>';
        });
        
        notificaciones.forEach(notif => {
            html += generarHtmlNotificacionReverb(notif);
        });
        
        lista.innerHTML = html;
        actualizarContadorGeneral();
    }

    function actualizarContadorGeneral() {
        const contadorSpan = document.getElementById('contadorNotificaciones');
        if (!contadorSpan) return;
        
        const lista = document.getElementById('listaNotificaciones');
        if (!lista) return;
        
        // Notificaciones de sistema (a.dropdown-item)
        const sistemaItems = lista.querySelectorAll('a.dropdown-item');
        const totalSistema = sistemaItems.length;
        
        // Notificaciones persistentes (badges con botón X)
        const persistentesItems = lista.querySelectorAll('.dropdown-item[data-timestamp]');
        const totalPersistentes = persistentesItems.length;
        
        // Notificaciones de Reverb en memoria
        const totalReverb = window.notificaciones ? window.notificaciones.length : 0;
        
        const total = totalSistema + totalPersistentes + totalReverb;
        
        if (total > 0) {
            contadorSpan.textContent = total;
            contadorSpan.style.display = 'inline-block';
        } else {
            contadorSpan.style.display = 'none';
        }
    }

    // ============================================
    // ELIMINAR NOTIFICACIÓN INDIVIDUAL
    // ============================================
    function eliminarNotificacion(timestamp) {
        // Eliminar del array
        window.notificaciones = window.notificaciones.filter(n => n.timestamp !== timestamp);
        
        // Actualizar contador
        const contadorSpan = document.getElementById('contadorNotificaciones');
        if (contadorSpan) {
            contadorSpan.textContent = window.notificaciones.length;
            contadorSpan.style.display = window.notificaciones.length > 0 ? 'inline-block' : 'none';
        }
        
        // Actualizar lista
        actualizarListaNotificaciones();
    }
</script>

<script>
/**
 * Inicializa el icono de limpiar en un buscador
 * @param {string} inputId - ID del input de búsqueda
 * @param {string} iconId - ID del icono de limpiar
 */
function inicializarLimpiarBuscador(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (!input || !icon) return;
    
    input.addEventListener('input', function() {
        icon.style.display = this.value.length > 0 ? 'block' : 'none';
    });
    
    icon.addEventListener('click', function() {
        input.value = '';
        icon.style.display = 'none';
        input.focus();
        
        // Disparar ambos eventos para cubrir todos los casos
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true }));
    });
}

// Inicializar todos los buscadores al cargar el DOM
document.addEventListener('DOMContentLoaded', function() {
    inicializarLimpiarBuscador('buscarClienteGlobal', 'limpiarBuscarCliente');
    inicializarLimpiarBuscador('buscarPatologia', 'limpiarBuscarPatologia');
    inicializarLimpiarBuscador('buscarInteres', 'limpiarBuscarInteres');
    inicializarLimpiarBuscador('buscarCotizacion', 'limpiarBuscarCotizacion');
    inicializarLimpiarBuscador('buscarPedido', 'limpiarBuscarPedido');
    inicializarLimpiarBuscador('buscarContacto', 'limpiarBuscarContacto');
    inicializarLimpiarBuscador('buscarUsuario', 'limpiarBuscarUsuario');
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            console.warn('Bootstrap no está disponible para inicializar tooltips');
            return;
        }

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl, {
                container: 'body',
                trigger: 'hover',
                delay: { show: 200, hide: 100 }
            });
        });
    });
</script>
@endauth

@stack('scripts')
</body>
</html>