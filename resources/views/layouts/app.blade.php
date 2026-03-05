<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRM Sistema')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
        }
        
        body {
            display: flex;
            height: 100vh;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            overflow-y: auto;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid var(--secondary-color);
            margin-bottom: 20px;
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
        
        .content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            background: #f4f6f9;
        }
        
        .topbar {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .topbar h6 {
            margin: 0;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-badge {
            background: #e9ecef;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .user-badge i {
            margin-right: 5px;
            color: var(--accent-color);
        }
        
        .logout-link {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .logout-link:hover {
            color: #dc3545;
        }
        
        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            background: #f4f6f9;
        }
        
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
        
        .pagination-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h5><i class="bi bi-speedometer2"></i> CRM</h5>
        </div>
        
        <!-- Dashboard -->
        <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        
        <!-- Clientes -->
        <div class="nav-collapse-toggle {{ request()->routeIs('clientes.*') ? 'active' : '' }}" data-target="clientes-menu">
            <span><i class="bi bi-people"></i> Clientes</span>
            <i class="bi bi-chevron-down collapse-icon {{ request()->routeIs('clientes.*') ? 'rotated' : '' }}"></i>
        </div>
        <div class="submenu {{ request()->routeIs('clientes.*') ? 'show' : '' }}" id="clientes-menu">
            <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.index') ? 'active' : '' }}">
                <i class="bi bi-list"></i> Directorio Clientes
            </a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                <i class="bi bi-plus-circle"></i> Nuevo Cliente
            </a>
        </div>

        <!-- Ventas -->
        <div class="nav-collapse-toggle" data-target="ventas-menu">
            <span><i class="bi bi-graph-up"></i> Ventas</span>
            <i class="bi bi-chevron-down collapse-icon"></i>
        </div>
        <div class="submenu" id="ventas-menu">
            <a href="#" class="nav-link">
                <i class="bi bi-file-text"></i> Cotizaciones
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-receipt"></i> Pedidos Anticipo
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-arrow-repeat"></i> Seguimiento Ventas
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-calendar-event"></i> Agenda Contactos
            </a>
        </div>

        <!-- Seguridad -->
        <div class="nav-collapse-toggle" data-target="seguridad-menu">
            <span><i class="bi bi-shield-lock"></i> Seguridad</span>
            <i class="bi bi-chevron-down collapse-icon"></i>
        </div>
        <div class="submenu" id="seguridad-menu">
            <a href="#" class="nav-link">
                <i class="bi bi-person-circle"></i> Usuarios
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-key"></i> Permisos
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-database"></i> Respaldos
            </a>
        </div>

        <!-- Reportes -->
        <div class="nav-collapse-toggle" data-target="reportes-menu">
            <span><i class="bi bi-bar-chart"></i> Reportes</span>
            <i class="bi bi-chevron-down collapse-icon"></i>
        </div>
        <div class="submenu" id="reportes-menu">
            <a href="#" class="nav-link">
                <i class="bi bi-cart"></i> Compras por Cliente
            </a>
        </div>
    </div>

    <!-- CONTENT WRAPPER -->
    <div class="content-wrapper">
        <!-- TOPBAR -->
        <div class="topbar">
            <h6>@yield('page-title', 'Dashboard')</h6>
            <div class="user-info">
                <span class="user-badge">
                    <i class="bi bi-person-circle"></i> José Martínez
                </span>
                <span class="badge bg-secondary">Administrador</span>
                <a href="#" class="logout-link">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>

    <!-- MODAL NUEVO CLIENTE (incluido aquí para acceso global) -->
    @include('partials.modal-nuevo-cliente')

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
    @yield('scripts')
</body>
</html>