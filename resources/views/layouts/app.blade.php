<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CRM Sistema')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #5170ff;
            --secondary-color: #34495e;
            --accent-color: #3498db;
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
        
        .notification-badge {
            position: relative;
            color: #6c757d;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .notification-badge .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.6rem;
            padding: 3px 5px;
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
                <!-- Dashboard (siempre visible) -->
                <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                
                <!-- Clientes - Solo visible si tiene permiso -->
                @can('clientes.mostrar')
                <div class="nav-collapse-toggle {{ request()->routeIs('clientes.*') ? 'active' : '' }}" data-target="clientes-menu">
                    <span><i class="bi bi-people"></i> Clientes</span>
                    <i class="bi bi-chevron-down collapse-icon {{ request()->routeIs('clientes.*') ? 'rotated' : '' }}"></i>
                </div>
                <div class="submenu {{ request()->routeIs('clientes.*') ? 'show' : '' }}" id="clientes-menu">
                    @can('clientes.ver')
                    <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.index') ? 'active' : '' }}">
                        <i class="bi bi-list"></i> Directorio Clientes
                    </a>
                    @endcan
                    
                    @can('enfermedades.ver')
                    <a href="{{ route('enfermedades.index') }}" class="nav-link {{ request()->routeIs('enfermedades.*') ? 'active' : '' }}">
                        <i class="bi bi-heart-pulse"></i> Enfermedades
                    </a>
                    @endcan

                    @can('intereses.ver')
                    <a href="{{ route('intereses.index') }}" class="nav-link {{ request()->routeIs('intereses.*') ? 'active' : '' }}">
                        <i class="bi bi-star"></i> Intereses
                    </a>
                    @endcan
                </div>
                @endcan

                <!-- Ventas -->
                @can('cotizaciones.mostrar')
                <div class="nav-collapse-toggle" data-target="ventas-menu">
                    <span><i class="bi bi-graph-up"></i> Ventas</span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="submenu" id="ventas-menu">
                    @can('cotizaciones.ver')
                    <a href="{{ route('ventas.cotizaciones.index') }}" class="nav-link {{ request()->routeIs('ventas.cotizaciones.*') ? 'active' : '' }}">
                        <i class="bi bi-file-text"></i> Cotizaciones
                    </a>
                    @endcan
                    
                    @can('pedidos_anticipo.ver')
                    <a href="#" class="nav-link">
                        <i class="bi bi-receipt"></i> Pedidos Anticipo
                    </a>
                    @endcan
                    
                    @can('seguimiento_ventas.ver')
                    <a href="#" class="nav-link">
                        <i class="bi bi-arrow-repeat"></i> Seguimiento Ventas
                    </a>
                    @endcan
                    
                    @can('seguimiento_cotizaciones.ver')
                    <a href="#" class="nav-link">
                        <i class="bi bi-arrow-repeat"></i> Seguimiento Cotizaciones
                    </a>
                    @endcan
                    
                    @can('agenda_contactos.ver')
                    <a href="#" class="nav-link">
                        <i class="bi bi-calendar-event"></i> Agenda Contactos
                    </a>
                    @endcan
                </div>
                @endcan

                <!-- Seguridad -->
                @can('acceder-seguridad')
                <div class="nav-collapse-toggle" data-target="seguridad-menu">
                    <span><i class="bi bi-shield-lock"></i> Seguridad</span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="submenu" id="seguridad-menu">
                    @can('seguridad.ver')
                    <a href="{{ route('seguridad.usuarios.index') }}" class="nav-link {{ request()->routeIs('seguridad.usuarios.*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i> Usuarios
                    </a>
                    @endcan
                    
                    @can('seguridad.ver')
                    <a href="#" class="nav-link">
                        <i class="bi bi-key"></i> Permisos
                    </a>
                    @endcan
                    
                    @can('seguridad.ver')
                    <a href="#" class="nav-link">
                        <i class="bi bi-database"></i> Respaldos
                    </a>
                    @endcan
                </div>
                @endcan

                <!-- Reportes -->
                @can('reportes.mostrar')
                <div class="nav-collapse-toggle" data-target="reportes-menu">
                    <span><i class="bi bi-bar-chart"></i> Reportes</span>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div class="submenu" id="reportes-menu">
                    @can('reportes.compras_cliente')
                    <a href="#" class="nav-link">
                        <i class="bi bi-cart"></i> Compras por Cliente
                    </a>
                    @endcan
                    
                    @can('reportes.frecuencia_compra')
                    <a href="#" class="nav-link">
                        <i class="bi bi-bar-chart"></i> Frecuencia de compra por Cliente
                    </a>
                    @endcan
                    
                    @can('reportes.montos_promedio')
                    <a href="#" class="nav-link">
                        <i class="bi bi-cart"></i> Montos promedios de compra de cliente
                    </a>
                    @endcan
                    
                    @can('reportes.sucursales_preferidas')
                    <a href="#" class="nav-link">
                        <i class="bi bi-house-heart"></i> Sucursales Preferidas
                    </a>
                    @endcan
                    
                    @can('reportes.cotizaciones_cliente')
                    <a href="#" class="nav-link">
                        <i class="bi bi-file-earmark-ruled"></i> Cotizaciones por Cliente
                    </a>
                    @endcan
                    
                    @can('reportes.cotizaciones_concretadas')
                    <a href="#" class="nav-link">
                        <i class="bi bi-clipboard2-check"></i> Cotizaciones concretadas
                    </a>
                    @endcan
                </div>
                @endcan
            </div>

            <!-- PERFIL DE USUARIO -->
        <div class="sidebar-user">
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->nombre_completo }}</div>
                    <div class="user-role">Usuario</div>
                </div>
            </div>
            <div class="user-actions">
                <a href="{{ route('logout') }}" 
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
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
                    <div class="notification-badge">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-danger">3</span>
                    </div>
                    <span class="badge bg-primary">CRM v1.0</span>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- MODALS GLOBALES -->
    @include('clientes.partials.modal-nuevo-cliente')
    @include('clientes.partials.modal-editar-cliente')
    @include('partials.modal-confirmar-eliminar')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        // Sidebar collapse toggle
        document.querySelectorAll('.nav-collapse-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const submenu = document.getElementById(targetId);
                const icon = this.querySelector('.collapse-icon');
                
                // Cerrar otros menús si es necesario
                if (!this.classList.contains('active')) {
                    document.querySelectorAll('.submenu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    document.querySelectorAll('.nav-collapse-toggle').forEach(btn => {
                        btn.classList.remove('active');
                        btn.querySelector('.collapse-icon').classList.remove('rotated');
                    });
                }

                // Toggle current menu
                submenu.classList.toggle('show');
                this.classList.toggle('active');
                icon.classList.toggle('rotated');
            });
        });
    </script>

    <!-- Función global para toasts -->
    <script>
    window.mostrarToast = function(mensaje, tipo = 'success') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = tipo === 'success' ? 'bg-success' : (tipo === 'warning' ? 'bg-warning' : 'bg-danger');
        const iconClass = tipo === 'success' ? 'bi-check-circle-fill' : (tipo === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill');
        
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
                <div class="toast-header ${bgClass} text-white">
                    <i class="bi ${iconClass} me-2"></i>
                    <strong class="me-auto">CRM</strong>
                    <small>ahora</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${mensaje}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.getElementById(toastId);
        new bootstrap.Toast(toastElement).show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
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
        
        // Permitir letras (incluyendo tildes y ñ), espacios, y punto
        if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]$/.test(char)) {
            return true;
        }
        
        e.preventDefault();
        if (window.mostrarToast) {
            window.mostrarToast('Solo se permiten letras y espacios', 'warning');
        }
        return false;
    };

    window.soloNumeros = function(e) {
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
        
        // Permitir números, +, -, espacio
        if (/^[0-9+\-\s]$/.test(char)) {
            return true;
        }
        
        e.preventDefault();
        if (window.mostrarToast) {
            window.mostrarToast('Solo se permiten números, +, - y espacios', 'warning');
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

    window.prevenirPegadoInvalido = function(e, pattern) {
        e.preventDefault();
        const textoPegado = (e.clipboardData || window.clipboardData).getData('text');
        const textoLimpio = textoPegado.split('').filter(char => pattern.test(char)).join('');
        
        const inicio = e.target.selectionStart;
        const fin = e.target.selectionEnd;
        const valorActual = e.target.value;
        const nuevoValor = valorActual.substring(0, inicio) + textoLimpio + valorActual.substring(fin);
        e.target.value = nuevoValor;
        
        if (textoLimpio.length !== textoPegado.length && window.mostrarToast) {
            window.mostrarToast('Se eliminaron caracteres no permitidos', 'warning');
        }
    };
    </script>
    @yield('scripts')
    
    @stack('scripts')
</body>
</html>