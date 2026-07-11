<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
        :root {
            --sidebar-width: 260px;
            /*--primary-color: #005BAA; */ /* Azul Principal (Endeavour) */
            /*--secondary-color: #FB6962; */ /* Rojo Principal (Bittersweet) */
            /*--accent-color: #00AAB5; */ /* Bondi Blue (Tono complementario) */


            /* --primary-color: #5170ff; */
            --primary-color: #005697;
            --secondary-color: #34495e;
            --accent-color: #00AAB5;
            /* --accent-color: #3498db; */
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }
        
        /* Layout principal */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar - ahora solo ocupa el alto necesario */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            min-height: 100vh;
            position: sticky;
            top: 0;
            align-self: flex-start;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--secondary-color);
        }
        
        .sidebar-header h5 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .sidebar-header h5 i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        /* Menú principal - crece para ocupar espacio disponible */
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }
        
        .sidebar .nav-link:hover {
            background: var(--secondary-color);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background: var(--accent-color);
            color: white;
            border-left: 4px solid #fff;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            font-size: 1.1rem;
        }
        
        .nav-collapse-toggle {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ecf0f1;
            padding: 12px 20px;
            user-select: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .nav-collapse-toggle:hover {
            background: var(--secondary-color);
        }
        
        .nav-collapse-toggle.active {
            background: var(--accent-color);
        }
        
        .nav-collapse-toggle i:first-child {
            margin-right: 12px;
            width: 20px;
        }
        
        .collapse-icon {
            transition: transform 0.3s ease;
            font-size: 0.8rem;
        }
        
        .collapse-icon.rotated {
            transform: rotate(180deg);
        }
        
        .submenu {
            margin-left: 20px;
            display: none;
            border-left: 2px solid var(--secondary-color);
            padding-left: 10px;
        }
        
        .submenu.show {
            display: block;
        }
        
        .submenu .nav-link {
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        
        /* Perfil de usuario - ahora al final del sidebar */
        .sidebar-user {
            padding: 20px;
            border-top: 2px solid var(--secondary-color);
            background: rgba(0,0,0,0.1);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: #a0aec0;
        }
        
        .user-actions {
            display: flex;
            justify-content: space-around;
            padding-top: 10px;
        }
        
        .user-actions a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        
        .user-actions a:hover {
            background: var(--secondary-color);
        }
        
        /* Content Wrapper - ahora a la derecha del sidebar */
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f4f6f9;
            min-width: 0; /* Previene desbordamiento */
        }
        
        /* Topbar - ahora en la parte superior del content-wrapper */
        .topbar {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .topbar h6 {
            margin: 0;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .main-content {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }
        
        /* Resto de estilos existentes */
        .page-header {
            margin-bottom: 25px;
        }
        
        .page-header h3 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .page-header p {
            margin: 5px 0 0 0;
            color: #6c757d;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            font-weight: 600;
            color: var(--primary-color);
            border-radius: 10px 10px 0 0 !important;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-top: none;
            background: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 15px;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Botones de acción*/
        .btn-action {
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 5px;
        }
        
        .search-box {
            position: relative;
            max-width: 300px;
        }
        
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
        }
        
        .search-box input {
            padding-left: 35px;
            border-radius: 20px;
            border: 1px solid #dee2e6;
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
        }
        
        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 15px 20px;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 15px 20px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .contact-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .contact-info i {
            color: var(--accent-color);
            width: 20px;
            margin-right: 8px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .app-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                min-height: auto;
                position: static;
            }
            
            .content-wrapper {
                width: 100%;
            }
        }

        /* ============================================
        ESTILOS PARA RESULTADOS DE BUSQUEDA
        ============================================ */

        #resultadosBusquedaClientes .list-group-item {
            padding: 12px 15px;
            border-left: none;
            border-right: none;
            transition: background-color 0.2s;
        }

        #resultadosBusquedaClientes .list-group-item:hover {
            background-color: #f8f9fa;
        }

        #resultadosBusquedaClientes .list-group-item:first-child {
            border-top: none;
        }

        #resultadosBusquedaClientes .list-group-item:last-child {
            border-bottom: none;
        }

        /* Estilos para alertas de status */
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        /* Mejorar contraste de badges */
        .badge.bg-warning {
            color: #212529 !important; /* Texto oscuro sobre fondo amarillo */
        }
        .badge.bg-success, .badge.bg-danger, .badge.bg-secondary {
            color: white !important;
        }

        /* Estilo para filas de clientes bloqueados */
        .table-danger {
            background-color: #f8d7da !important;
            opacity: 0.85;
        }

        .table-danger td {
            background-color: #f8d7da !important;
        }

        /* Transición para botones */
        .btn-action {
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        /* Overlay para bloquear la pantalla cuando la sesión expira */
        .session-expired-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1060;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .session-expired-overlay .modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        .session-expired-overlay .modal-content i {
            font-size: 48px;
            color: #dc3545;
        }

        .session-expired-overlay .modal-content h5 {
            margin: 15px 0 10px;
            color: #dc3545;
        }

        .session-expired-overlay .modal-content p {
            margin-bottom: 20px;
            color: #333;
        }

        .sidebar-user .user-profile {
            padding: 5px 0;
        }

        .sidebar-user .user-profile a {
            transition: opacity 0.2s ease;
        }

        .sidebar-user .user-profile a:hover {
            opacity: 0.7;
        }

        /* ============================================ */
        /* AJUSTES DE Z-INDEX PARA MODALES DE BOOTSTRAP */
        /* ============================================ */

        /* Backdrop de los modales */
        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal-backdrop.show {
            z-index: 1040 !important;
        }

        /* Modales principales */
        .modal {
            z-index: 1050 !important;
        }

        .modal.show {
            z-index: 1050 !important;
        }

        /* Contenido del modal */
        .modal-content {
            z-index: 1051 !important;
        }

        /* Para múltiples modales anidados */
        .modal.show .modal {
            z-index: 1060 !important;
        }

        .modal.show .modal-backdrop {
            z-index: 1055 !important;
        }

        /* Asegurar que el modal de confirmación esté por encima de otros modales */
        #modalConfirmar {
            z-index: 1060 !important;
        }
</style>

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
    opacity: 0;
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    border: none;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-20px);
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}

/* Animación de entrada desde arriba */
.toast-container-center .toast {
    animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Animación de salida más fluida */
.toast-container-center .toast.hiding {
    animation: fadeOutUp 0.4s ease-in-out forwards;
}

@keyframes fadeOutUp {
    0% {
        transform: translateY(0);
        opacity: 1;
    }
    100% {
        transform: translateY(-30px);
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

/* Estilos para la barra de progreso - MÁS VISIBLE */
.progress {
    height: 4px;
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

<style>
/* ============================================
   SIDEBAR - ESTILOS DE NAVEGACIÓN
   ============================================ */

/* Submenús - ocultos por defecto */
.submenu {
    display: none;
    padding-left: 15px;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s ease;
}

.submenu.show {
    display: block;
    max-height: 500px;
}

/* Icono de colapso */
.collapse-icon {
    transition: transform 0.3s ease;
    display: inline-block;
}

.collapse-icon.rotated {
    transform: rotate(180deg);
}

/* Link activo en el sidebar */
.nav-link.active {
    background-color: rgba(255, 255, 255, 0.15) !important;
    border-radius: 4px;
    font-weight: 600 !important;
}

/* Toggle activo (padre del menú) */
.nav-collapse-toggle.active {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}
</style>
</head>
<body>
    <div class="app-layout">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h5><i class="bi bi-speedometer2"></i> CRM</h5>
            </div>
            
            <!-- Menú principal -->
            <div class="sidebar-menu">
                <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                
                <!-- Clientes -->
                @if(auth()->user()->submodulosVisibles('clientes') && count(auth()->user()->submodulosVisibles('clientes')) > 0)
                    <div class="nav-collapse-toggle" data-target="clientes-menu">
                        <span><i class="bi bi-people"></i> Clientes</span>
                        <i class="bi bi-chevron-down collapse-icon"></i>
                    </div>
                    <div class="submenu" id="clientes-menu">
                    @if(in_array('directorio', auth()->user()->submodulosVisibles('clientes')))
                    <a href="{{ route('clientes.index') }}" class="nav-link">
                        <i class="bi bi-list"></i> Directorio Clientes
                    </a>
                    @endif
                    
                    @if(in_array('enfermedades', auth()->user()->submodulosVisibles('clientes')))
                    <a href="{{ route('enfermedades.index') }}" class="nav-link">
                        <i class="bi bi-heart-pulse"></i> Enfermedades
                    </a>
                    @endif

                    @if(in_array('intereses', auth()->user()->submodulosVisibles('clientes')))
                    <a href="{{ route('intereses.index') }}" class="nav-link">
                        <i class="bi bi-star"></i> Intereses
                    </a>
                    @endif
                </div>
                @endif

                <!-- Ventas -->
                @if(auth()->user()->submodulosVisibles('ventas') && count(auth()->user()->submodulosVisibles('ventas')) > 0)
                <div class="nav-collapse-toggle" data-target="ventas-menu">
                    <span><i class="bi bi-graph-up"></i> Ventas</span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="submenu" id="ventas-menu">
                    @if(in_array('cotizaciones', auth()->user()->submodulosVisibles('ventas')))
                    <a href="{{ route('ventas.cotizaciones.index') }}" class="nav-link">
                        <i class="bi bi-file-text"></i> Cotizaciones
                    </a>
                    @endif
                    
                    @if(in_array('pedidos_anticipo', auth()->user()->submodulosVisibles('ventas')))
                    <a href="{{ route('ventas.pedidos.index') }}" class="nav-link">
                        <i class="bi bi-receipt"></i> Pedidos
                    </a>
                    @endif
                    
                    @if(in_array('agenda_contactos', auth()->user()->submodulosVisibles('ventas')))
                    <a href="{{ route('ventas.agenda_contactos.index') }}" class="nav-link">
                        <i class="bi bi-calendar-event"></i> Agenda Contactos
                    </a>
                    @endif
                </div>
                @endif

                <!-- Seguridad -->
                @if(auth()->user()->submodulosVisibles('seguridad') && count(auth()->user()->submodulosVisibles('seguridad')) > 0)
                <div class="nav-collapse-toggle" data-target="seguridad-menu">
                    <span><i class="bi bi-shield-lock"></i> Seguridad</span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="submenu" id="seguridad-menu">
                    @if(in_array('usuarios', auth()->user()->submodulosVisibles('seguridad')))
                    <a href="{{ route('seguridad.usuarios.index') }}" class="nav-link">
                        <i class="bi bi-person-circle"></i> Usuarios
                    </a>
                    @endif
                    
                    @if(in_array('permisos', auth()->user()->submodulosVisibles('seguridad')))
                    <a href="{{ route('seguridad.permisos.index') }}" class="nav-link">
                        <i class="bi bi-key"></i> Permisos
                    </a>
                    @endif
                    
                    @if(in_array('respaldos', auth()->user()->submodulosVisibles('seguridad')))
                        <a href="{{ route('seguridad.respaldos.index') }}" class="nav-link">
                            <i class="bi bi-database"></i> Respaldos
                        </a>
                    @endif
                </div>
                @endif

                <!-- Reportes -->
                @if(auth()->user()->submodulosVisibles('reportes') && count(auth()->user()->submodulosVisibles('reportes')) > 0)
                <div class="nav-collapse-toggle" data-target="reportes-menu">
                    <span><i class="bi bi-bar-chart"></i> Reportes</span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="submenu" id="reportes-menu">
                    
                    <!-- Submenú Ventas (collapse anidado) -->
                    <div class="nav-collapse-toggle" data-target="ventas-reportes-submenu">
                        <span><i class="bi bi-graph-up"></i> Ventas</span>
                        <i class="bi bi-chevron-down collapse-icon"></i>
                    </div>
                    <div class="submenu" id="ventas-reportes-submenu">
                        @if(in_array('compras_cliente', auth()->user()->submodulosVisibles('reportes')))
                        <a href="{{ route('reportes.compras_cliente.clientes') }}" class="nav-link">
                            <i class="bi bi-cart"></i> Compras por Cliente
                        </a>
                        @endif
                        
                        @if(in_array('frecuencia_compra', auth()->user()->submodulosVisibles('reportes')))
                        <a href="{{ route('reportes.compras_cliente.frecuencia-compra') }}" class="nav-link">
                            <i class="bi bi-bar-chart"></i> Frecuencia de compra por Cliente
                        </a>
                        @endif
                        
                        @if(in_array('montos_promedio', auth()->user()->submodulosVisibles('reportes')))
                        <a href="{{ route('reportes.compras_cliente.montos-promedio') }}" class="nav-link">
                            <i class="bi bi-calculator"></i> Montos promedios de compra
                        </a>
                        @endif
                        
                        @if(in_array('sucursales_preferidas', auth()->user()->submodulosVisibles('reportes')))
                        <a href="{{ route('reportes.sucursales-preferidas') }}" class="nav-link">
                            <i class="bi bi-house-heart"></i> Sucursales Preferidas
                        </a>
                        @endif
                    </div>
                    
                    <!-- Submenú Cotizaciones (collapse anidado) -->
                    <div class="nav-collapse-toggle" data-target="cotizaciones-reportes-submenu">
                        <span><i class="bi bi-file-text"></i> Cotizaciones</span>
                        <i class="bi bi-chevron-down collapse-icon"></i>
                    </div>
                    <div class="submenu" id="cotizaciones-reportes-submenu">
                        @if(in_array('cotizaciones_cliente', auth()->user()->submodulosVisibles('reportes')))
                        <a href="{{ route('reportes.cotizaciones-cliente.index') }}" class="nav-link">
                            <i class="bi bi-file-earmark-ruled"></i> Cotizaciones por Cliente
                        </a>
                        @endif
                        
                        @if(in_array('pedidos_cliente', auth()->user()->submodulosVisibles('reportes')))
                        <a href="{{ route('reportes.pedidos-cliente.index') }}" class="nav-link">
                            <i class="bi bi-clipboard2-check"></i> Pedidos por Cliente
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- PERFIL DE USUARIO (DENTRO DEL SIDEBAR) -->
            <div class="sidebar-user">
                <div class="user-profile d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::user()->nombre_completo }}</div>
                            <div class="user-role">Usuario</div>
                        </div>
                    </div>
                    <a href="#" 
                    class="text-white" 
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                    title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </a>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        <!-- CONTENT WRAPPER -->
        <div class="content-wrapper">
            <!-- TOPBAR -->
            <div class="topbar">
                <h6>@yield('page-title', 'Dashboard')</h6>
                <div class="topbar-actions">
                    <!-- Campana de notificaciones -->
                    <div class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="campanaNotificaciones" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative;">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-danger" id="contadorNotificaciones" style="display: none; position: absolute; top: -5px; right: -10px; font-size: 0.7rem;">0</span>
                        </a>
                        <div class="dropdown-menu" id="dropdownNotificaciones" aria-labelledby="campanaNotificaciones" style="min-width: 280px; max-width: 400px; width: auto;">
                            <h6 class="dropdown-header" id="dropdownHeaderNotificaciones">Notificaciones</h6>
                            <div id="listaNotificaciones">
                                <div class="dropdown-item text-muted text-center">Cargando...</div>
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

<script>
    // Sidebar collapse toggle (soporta menús anidados)
    document.querySelectorAll('.nav-collapse-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que el click suba al padre
            
            const targetId = this.getAttribute('data-target');
            const submenu = document.getElementById(targetId);
            const icon = this.querySelector('.collapse-icon');
            
            // Solo cerrar otros menús del MISMO nivel (opcional, para mejor UX)
            // Pero NO cerrar los padres
            
            // Toggle del menú actual
            submenu.classList.toggle('show');
            this.classList.toggle('active');
            icon.classList.toggle('rotated');
        });
    });
</script>

<!-- Función global para toasts -->
<script>
window.mostrarToast = function(mensaje, tipo = 'success') {
    
    // Intentar mostrar con Bootstrap si está disponible
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        let toastContainer = document.querySelector('.toast-container-center');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container-center';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const duration = 3000; // 3 segundos
        
        const bgClass = tipo === 'success' ? 'bg-success' : (tipo === 'warning' ? 'bg-warning' : 'bg-danger');
        const iconClass = tipo === 'success' ? 'bi-check-circle-fill' : (tipo === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill');
        const textColor = tipo === 'warning' ? 'text-dark' : 'text-white';
        const closeBtnClass = tipo === 'warning' ? 'btn-close' : 'btn-close btn-close-white';
        
        const toastHtml = `
            <div id="${toastId}" class="toast fade" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="${duration}">
                <div class="toast-header ${bgClass} ${textColor}">
                    <i class="bi ${iconClass} me-2"></i>
                    <strong class="me-auto">CRM</strong>
                    <small>ahora</small>
                    <button type="button" class="${closeBtnClass}" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body p-0">
                    <div class="p-3 pb-2">${mensaje}</div>
                    <div class="progress">
                        <div class="progress-bar ${bgClass}" role="progressbar" style="width: 100%; transition: width linear ${duration}ms;"></div>
                    </div>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.getElementById(toastId);
        const progressBar = toastElement.querySelector('.progress-bar');
        
        setTimeout(() => {
            if (progressBar) progressBar.style.width = '0%';
        }, 50);
        
        const toast = new bootstrap.Toast(toastElement, { animation: true, autohide: true, delay: duration });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
    } else {
        // Fallback: console log
        console.log(`[${tipo}] ${mensaje}`);
    }
};


// ============================================
// VALIDACIONES GLOBALES EN TIEMPO REAL
// ============================================

window.soloLetras = function(e) {
    // Ignorar completamente las teclas de sistema y modificadores
    const teclasIgnoradas = [
        8, 9, 16, 17, 18, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46,
        112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145
    ];
    
    if (teclasIgnoradas.includes(e.keyCode)) {
        return true;
    }
    
    // Obtener el carácter
    const char = e.key;
    
    // PERMITIR: letras (con tildes y ñ), espacios, punto (.) y asterisco (*)
    if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s*.]$/.test(char)) {
        return true;
    }
    
    e.preventDefault();
    if (window.mostrarToast) {
        window.mostrarToast('Solo se permiten letras, *, . y espacios', 'warning');
    }
    return false;
};

window.soloNumeros = function(e) {
    const teclasIgnoradas = [
        8, 9, 16, 17, 18, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46,
        112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145
    ];
    
    if (teclasIgnoradas.includes(e.keyCode)) {
        return true;
    }
    
    // Obtener el carácter
    const char = e.key;
    
    // Permitir números, +, -, espacio, * (asterisco) y . (punto)
    if (/^[0-9+\-\s*.]$/.test(char)) {
        return true;
    }
    
    e.preventDefault();
    if (window.mostrarToast) {
        window.mostrarToast('Solo se permiten números, +, -, *, . y espacios', 'warning');
    }
    return false;
};

// Convertir a mayúsculas mientras se escribe
window.aMayusculas = function(e) {
    const inicio = e.target.selectionStart;
    const fin = e.target.selectionEnd;
    
    e.target.value = e.target.value.toUpperCase();
    
    // Restaurar la posición del cursor
    e.target.setSelectionRange(inicio, fin);
};


</script>

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
        console.warn('BFCache detectado (navigation.type=back_forward) - Recargando...');
        window.location.reload(true);
        return;
    }
    
    // Evento pageshow: se dispara cuando la página se muestra
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            console.warn('BFCache detectado (event.persisted) - Recargando...');
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
        console.warn('Pagina restaurada desde BFCache (event.persisted)');
        
        setTimeout(() => {
            // Usar checkUserStatus centralizado
            checkUserStatus().then(() => {
                if (lastSuccessfulCheck > 0) {
                    bfcacheHandled = false;
                } else {
                    console.warn('Sesion invalida en BFCache, redirigiendo...');
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
        console.warn('Token CSRF invalido o vacio, refrescando...');
        
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
        console.warn('Logout: Token CSRF invalido, refrescando...');
        
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
                    console.warn('Interceptor: Redirigiendo al login por:', {
                        url: url,
                        status: response.status,
                        needsLogin: needsLogin
                    });
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
                console.warn('Sesion expirada (' + response.status + ') - Intento ' + checkAttempts + '/' + MAX_CHECK_ATTEMPTS);
                
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
            
            console.warn('Problema de conexion:', errorMsg);
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
            console.warn('Heartbeat: Sesion expirada');
        }
    } catch (error) {
        heartbeatAttempts++;
        if (heartbeatAttempts >= 3) {
            console.warn('Heartbeat: Servidor no responde despues de 3 intentos');
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
    
    fetch(`/notificaciones/cotizaciones?modulo=${modulo}`)
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
            
            if (notificaciones.length > 0) {
                if (contadorSpan) {
                    contadorSpan.textContent = notificaciones.length;
                    contadorSpan.style.display = 'inline-block';
                }
                
                let html = '';
                notificaciones.forEach(notif => {
                    let icono = 'bi-bell';
                    let color = 'text-warning';
                    
                    if (notif.tipo === 'cotizacion') {
                        icono = 'bi-file-earmark-text';
                        color = 'text-danger';
                    } else if (notif.tipo === 'contacto') {
                        icono = notif.icono || 'bi-exclamation-triangle';
                        color = `text-${notif.color || 'info'}`;
                    } else if (notif.tipo === 'pedido') {
                        icono = 'bi-box-seam';
                        color = 'text-warning';
                    }
                    
                    let contenidoHtml = '';
                    if (notif.tipo === 'contacto') {
                        contenidoHtml = `
                            <strong>${escapeHtml(notif.cliente)}</strong><br>
                            <small class="text-muted">${escapeHtml(notif.asunto)}</small><br>
                            <small class="${color}">
                                <i class="bi ${notif.icono || 'bi-exclamation-triangle'} me-1"></i>
                                ${escapeHtml(notif.mensaje)}
                            </small>
                        `;
                    } else {
                        contenidoHtml = `
                            <strong>${escapeHtml(notif.folio || notif.cliente)}</strong><br>
                            <small class="${color}">${escapeHtml(notif.mensaje)}</small>
                        `;
                    }
                    
                    html += `
                        <a class="dropdown-item" href="${notif.url || '#'}">
                            <div class="d-flex align-items-start">
                                <i class="bi ${icono} ${color} me-2 mt-1"></i>
                                <div class="flex-grow-1">
                                    ${contenidoHtml}
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    `;
                });
                
                listaNotificaciones.innerHTML = html;
                actualizarHeaderNotificaciones(data.tipo || modulo);
            } else {
                // Sin notificaciones
                if (contadorSpan) contadorSpan.style.display = 'none';
                
                let mensaje = data.mensaje_general || 'No hay notificaciones pendientes';
                listaNotificaciones.innerHTML = `<div class="dropdown-item text-muted text-center">${mensaje}</div>`;
                actualizarHeaderNotificaciones(data.tipo || modulo);
            }
        } else {
            // Datos inválidos
            throw new Error(data.mensaje_general || 'Datos inválidos');
        }
    })
    .catch(error => {
        // ERROR - Reintentar silenciosamente
        console.warn('Error cargando notificaciones:', error.message);
        
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

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Configurar dropdown manualmente
    const campana = document.getElementById('campanaNotificaciones');
    const dropdown = document.getElementById('dropdownNotificaciones');
    
    if (campana && dropdown) {
        campana.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Cargar notificaciones al abrir
            cargarNotificaciones();
            
            // Cerrar otros dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== dropdown) menu.classList.remove('show');
            });
            
            dropdown.classList.toggle('show');
        });
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!campana.contains(e.target) && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    }
    
    // Cargar notificaciones iniciales (sin abrir dropdown)
    cargarNotificaciones();
});
</script>

<script>
// ============================================
// MARCAR SUBMENÚ ACTIVO SEGÚN LA URL ACTUAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (!href || href === '#') return;
        
        // Obtener solo el path del href
        let linkPath = href;
        try {
            const urlObj = new URL(href, window.location.origin);
            linkPath = urlObj.pathname;
        } catch (e) {
            linkPath = href;
        }
        
        if (linkPath === currentPath || (linkPath !== '/' && currentPath.startsWith(linkPath))) {
            link.classList.add('active');
            
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
                    
                    // Moverse al padre del submenu para buscar más niveles
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
    console.warn('El servidor no responde. Intentando reconectar...');
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
                <div style="font-size: 48px; margin-bottom: 20px;">🔌</div>
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
                    console.warn('Error de conexión detectado:', error.message);
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

@stack('scripts')
</body>
</html>