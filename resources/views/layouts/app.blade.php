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
        
        /* ============================================ */
        /* SIDEBAR - BASE                               */
        /* ============================================ */
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
            z-index: 1040;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        /* ============================================ */
        /* SIDEBAR - ESTADO COLAPSADO (DESKTOP/TABLET)  */
        /* ============================================ */
        @media (min-width: 992px) {
            .sidebar.collapsed {
                width: 0 !important;
                min-width: 0 !important;
                overflow: hidden !important;
                border-right: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .sidebar.collapsed > * {
                display: none !important;
            }
        }

        .sidebar-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: rgba(0, 0, 0, 0.12);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
            min-width: 0;
        }

        .sidebar-brand:hover {
            color: white;
        }

        .sidebar-brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .sidebar-brand-title {
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 1px;
            line-height: 1.1;
            color: #ffffff;
        }

        .sidebar-brand-subtitle {
            font-size: 0.65rem;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.55);
        }

        .sidebar-close-btn {
            display: none;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sidebar-close-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
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
            white-space: nowrap;
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
            flex-shrink: 0;
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
            display: inline-block;
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

        .submenu .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
            font-weight: 600;
        }

        .submenu .nav-collapse-toggle {
            padding: 8px 20px;
            font-size: 0.9rem;
        }

        .submenu .nav-collapse-toggle.active {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        /* Perfil de usuario - ahora al final del sidebar */
        .sidebar-user {
            padding: 15px 20px;
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
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: white;
            flex-shrink: 0;
        }
        
        .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .user-avatar-wrapper {
            position: relative;
            flex-shrink: 0;
        }

        .user-avatar-wrapper .user-avatar {
            width: 45px;
            height: 45px;
        }

        .user-status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            border: 2px solid var(--primary-color);
        }

        .user-logout-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ecf0f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            flex-shrink: 0;
        }

        .user-logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.45);
            color: #ef4444;
            transform: scale(1.05);
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

        /* Botón hamburguesa del topbar (visible solo en móvil) */
        .topbar-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            color: var(--primary-color);
            font-size: 1.25rem;
            padding: 4px 10px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .topbar-toggle-btn:hover {
            background: #f4f6f9;
        }

        /* Backdrop del drawer móvil */
        .sidebar-backdrop {
            display: none;
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

        /* ============================================ */
        /* RESPONSIVE - MÓVIL (DRAWER OFF-CANVAS)       */
        /* ============================================ */
        @media (max-width: 991.98px) {
            .app-layout {
                flex-direction: column;
            }

            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: var(--sidebar-width);
                min-height: 100vh;
                height: 100vh;
                transform: translateX(-100%);
                box-shadow: none;
                z-index: 1050;
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.35);
            }

            .sidebar-close-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .topbar-toggle-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1045;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
            }

            .sidebar-backdrop.show {
                opacity: 1;
                pointer-events: auto;
            }

            body.sidebar-open {
                overflow: hidden;
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
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
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

<style>
/* ============================================ */
/* FADE EN FILAS DE TABLA (AGENDA)               */
/* ============================================ */
#contactosTableBody tr:not(#no-results-row) {
    transition: opacity 0.15s ease;
}

#contactosTableBody.filtrando tr:not(#no-results-row) {
    opacity: 0.4;
}

/* ============================================ */
/* MODAL DE REPROGRAMACIÓN - DISEÑO              */
/* ============================================ */

/* Card de producto */
.reprogram-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
    background: #ffffff;
}

.reprogram-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.reprogram-card.completo {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.08);
}

.reprogram-card.incompleto {
    border-color: #f59e0b;
}

.reprogram-card.excedido {
    border-color: #3b82f6;
}

/* Header del card */
.reprogram-card-header {
    background: #f8f9fa;
    padding: 12px 16px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.reprogram-card-header .icon-box {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: rgba(0, 86, 151, 0.1);
    color: #005697;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.reprogram-card-header .title {
    font-weight: 700;
    color: #212529;
    font-size: 0.95rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.reprogram-card-header .remove-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid #dee2e6;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.reprogram-card-header .remove-btn:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.4);
    color: #ef4444;
}

/* Sub-header con contexto */
.reprogram-card-context {
    padding: 8px 16px;
    background: #ffffff;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 0.82rem;
    color: #6c757d;
}

.reprogram-card-context .item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.reprogram-card-context i {
    color: #adb5bd;
}

/* Body del card */
.reprogram-card-body {
    padding: 16px;
}

/* Input de cantidad a reprogramar */
.cantidad-reprogramar-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(0, 86, 151, 0.04);
    border-radius: 10px;
    margin-bottom: 16px;
}

.cantidad-reprogramar-wrapper label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
    white-space: nowrap;
}

.cantidad-reprogramar {
    width: 90px;
    font-size: 1.1rem;
    font-weight: 700;
    text-align: center;
    color: #005697;
    border: 2px solid rgba(0, 86, 151, 0.15);
    border-radius: 8px;
    padding: 6px 10px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.cantidad-reprogramar:focus {
    border-color: #005697;
    box-shadow: 0 0 0 3px rgba(0, 86, 151, 0.1);
    outline: none;
}

.cantidad-reprogramar-wrapper .hint {
    font-size: 0.78rem;
    color: #6c757d;
    margin-left: auto;
}

/* Secciones de sucursales */
.sucursales-section {
    margin-bottom: 12px;
}

.sucursales-section-title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #6c757d;
    margin-bottom: 6px;
    padding-left: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.sucursales-section-title .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
}

.sucursales-section-title.sin-stock .dot {
    background: #adb5bd;
}

/* Fila de sucursal */
.sucursal-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    transition: background 0.15s ease, border-color 0.15s ease;
    border: 1px solid transparent;
    margin-bottom: 4px;
}

.sucursal-row:hover {
    background: #f8f9fa;
}

.sucursal-row.asignada {
    background: rgba(16, 185, 129, 0.06);
    border-color: rgba(16, 185, 129, 0.25);
}

.sucursal-row.sin-stock {
    opacity: 0.75;
}

.sucursal-row.es-original {
    background: rgba(0, 86, 151, 0.04);
}

.sucursal-row.asignada.es-original {
    background: rgba(16, 185, 129, 0.08);
}

/* Indicador de estado (check circle) */
.sucursal-row .status-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    flex-shrink: 0;
    background: #e9ecef;
    color: #adb5bd;
}

.sucursal-row.asignada .status-icon {
    background: #10b981;
    color: #ffffff;
}

/* Nombre de sucursal */
.sucursal-row .sucursal-name {
    flex: 1;
    min-width: 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #212529;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sucursal-row .badge-original {
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(0, 86, 151, 0.12);
    color: #005697;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    flex-shrink: 0;
}

/* Stock */
.sucursal-row .stock-info {
    font-size: 0.78rem;
    color: #6c757d;
    white-space: nowrap;
    min-width: 90px;
    text-align: right;
}

.sucursal-row .stock-info strong {
    color: #10b981;
}

.sucursal-row.sin-stock .stock-info strong {
    color: #adb5bd;
}

/* Input de asignación */
.sucursal-row .asignar-cantidad {
    width: 70px;
    text-align: center;
    font-weight: 700;
    font-size: 0.9rem;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 4px 6px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    flex-shrink: 0;
}

.sucursal-row .asignar-cantidad:focus {
    border-color: #005697;
    box-shadow: 0 0 0 3px rgba(0, 86, 151, 0.1);
    outline: none;
}

.sucursal-row.asignada .asignar-cantidad {
    border-color: rgba(16, 185, 129, 0.4);
    background: #ffffff;
}

/* Barra de progreso */
.reprogram-progress {
    margin-top: 16px;
    padding: 10px 14px;
    background: #f8f9fa;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.reprogram-progress .progress-info {
    font-size: 0.82rem;
    color: #495057;
    white-space: nowrap;
    min-width: 120px;
}

.reprogram-progress .progress-info strong {
    color: #212529;
    font-size: 0.88rem;
}

.reprogram-progress .progress {
    flex: 1;
    height: 10px;
    border-radius: 5px;
    background: #e9ecef;
    overflow: hidden;
}

.reprogram-progress .progress-bar {
    border-radius: 5px;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.reprogram-progress .progress-pct {
    font-size: 0.85rem;
    font-weight: 700;
    min-width: 45px;
    text-align: right;
}

.reprogram-progress .progress-check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
}

.reprogram-progress.completo .progress-check {
    display: inline-flex;
}

/* Resumen general al pie */
.reprogram-resumen {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 16px;
    border-radius: 10px;
    border-left: 4px solid #dee2e6;
    background: #f8f9fa;
    margin-top: 16px;
    transition: all 0.3s ease;
}

.reprogram-resumen.completo {
    border-left-color: #10b981;
    background: rgba(16, 185, 129, 0.06);
}

.reprogram-resumen.incompleto {
    border-left-color: #f59e0b;
    background: rgba(245, 158, 11, 0.06);
}

.reprogram-resumen .resumen-info {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.85rem;
    color: #495057;
}

.reprogram-resumen .resumen-info i {
    font-size: 1.1rem;
}

.reprogram-resumen.completo .resumen-info i {
    color: #10b981;
}

.reprogram-resumen.incompleto .resumen-info i {
    color: #f59e0b;
}

.reprogram-resumen .resumen-info strong {
    color: #212529;
}

.reprogram-resumen .resumen-totales {
    font-size: 0.85rem;
    color: #6c757d;
    white-space: nowrap;
}

.reprogram-resumen .resumen-totales strong {
    color: #212529;
    font-size: 0.95rem;
}

/* Botón confirmar con badge */
#btnConfirmarReprogramacion {
    min-width: 220px;
    font-weight: 600;
}

#btnConfirmarReprogramacion .badge {
    background: rgba(255, 255, 255, 0.25);
    margin-left: 6px;
}

/* Empty state */
.reprogram-empty {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.reprogram-empty i {
    font-size: 2.5rem;
    color: #dee2e6;
    margin-bottom: 12px;
    display: block;
}

/* Aviso informativo */
.reprogram-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    background: rgba(0, 170, 181, 0.06);
    border-left: 4px solid #00AAB5;
    border-radius: 8px;
    font-size: 0.85rem;
    color: #495057;
    margin-bottom: 16px;
}

.reprogram-alert i {
    color: #00AAB5;
    font-size: 1.1rem;
    flex-shrink: 0;
    margin-top: 1px;
}

/* ============================================ */
/* SOBRE PEDIDO - Sección especial en cotizaciones */
/* ============================================ */

.sobre-pedido-section {
    margin-top: 12px;
    padding: 12px;
    border-radius: 10px;
    border: 1px dashed rgba(245, 158, 11, 0.5);
    background: rgba(245, 158, 11, 0.04);
}

.sobre-pedido-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #d97706;
    margin-bottom: 8px;
}

.sobre-pedido-header i {
    font-size: 1rem;
}

.sobre-pedido-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sobre-pedido-row .form-control,
.sobre-pedido-row .form-select {
    font-size: 0.85rem;
    border-color: rgba(245, 158, 11, 0.4);
}

.sobre-pedido-row .form-control:focus,
.sobre-pedido-row .form-select:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}

.sobre-pedido-row .asignar-cantidad {
    width: 80px;
    text-align: center;
    font-weight: 700;
}

/* ============================================ */
/* ACORDEÓN DE PRODUCTOS                         */
/* ============================================ */

/* Header colapsable */
.reprogram-card-header {
    cursor: pointer;
    user-select: none;
}

.reprogram-card-header:hover {
    background: #f1f3f5;
}

/* Chevron de expansión */
.reprogram-card-header .toggle-chevron {
    margin-left: auto;
    margin-right: 12px;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    color: #adb5bd;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.reprogram-card.expandido .toggle-chevron {
    transform: rotate(180deg);
    color: #005697;
}

/* Body del card colapsado */
.reprogram-card:not(.expandido) .reprogram-card-context,
.reprogram-card:not(.expandido) .reprogram-card-body {
    display: none;
}

/* Body del card expandido */
.reprogram-card.expandido .reprogram-card-context,
.reprogram-card.expandido .reprogram-card-body {
    display: block;
    animation: slideDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Resumen compacto en el header (visible solo colapsado) */
.reprogram-card-header .header-summary {
    display: none;
    align-items: center;
    gap: 10px;
    margin-left: auto;
    margin-right: 12px;
    flex-shrink: 0;
}

.reprogram-card:not(.expandido) .reprogram-card-header .header-summary {
    display: flex;
}

.reprogram-card.expandido .reprogram-card-header .header-summary {
    display: none;
}

/* Barra mini dentro del header */
.header-summary .mini-progress {
    width: 80px;
    height: 6px;
    border-radius: 3px;
    background: #e9ecef;
    overflow: hidden;
}

.header-summary .mini-progress .progress-bar {
    border-radius: 3px;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.header-summary .mini-pct {
    font-size: 0.78rem;
    font-weight: 700;
    min-width: 38px;
    text-align: right;
}

.header-summary .mini-check {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

.reprogram-card.completo:not(.expandido) .header-summary .mini-check {
    display: inline-flex;
}

/* Badge contador general en la barra de controles */
.badge-contador {
    background: rgba(0, 86, 151, 0.1);
    color: #005697;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.78rem;
    font-weight: 700;
    margin-left: 8px;
}
</style>

<style>
/* Soft Badges */
.badge-soft-primary {
    background-color: rgba(0, 86, 151, 0.12);
    color: #005697;
    font-weight: 600;
}

.badge-soft-success {
    background-color: rgba(16, 185, 129, 0.12);
    color: #059669;
    font-weight: 600;
}

.badge-soft-warning {
    background-color: rgba(245, 158, 11, 0.15);
    color: #d97706;
    font-weight: 600;
}

.badge-soft-danger {
    background-color: rgba(239, 68, 68, 0.12);
    color: #dc2626;
    font-weight: 600;
}

.badge-soft-info {
    background-color: rgba(0, 170, 181, 0.12);
    color: #008b94;
    font-weight: 600;
}

.badge-soft-secondary {
    background-color: rgba(108, 117, 125, 0.12);
    color: #6c757d;
    font-weight: 600;
}

.tracking-wider {
    letter-spacing: 0.5px;
}

/* ============================================ */
/* DASHBOARD - ESTILOS                          */
/* ============================================ */

.dashboard-header {
    background: #ffffff;
    transition: all 0.3s ease;
}

/* Bordes de color del original, ahora con radio suave */
.kpi-card {
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
}

.border-left-primary {
    border-left: 4px solid var(--primary-color) !important;
}

.border-left-success {
    border-left: 4px solid #10b981 !important;
}

.border-left-warning {
    border-left: 4px solid #f59e0b !important;
}

.border-left-info {
    border-left: 4px solid var(--accent-color) !important;
}

.kpi-icon-box {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
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

    // Control del sidebar: colapsable en desktop, drawer en móvil
    (function() {
        const mainSidebar = document.getElementById('mainSidebar');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const desktopBreakpoint = 992;
        const STORAGE_KEY = 'crm_sidebar_collapsed';

        function isDesktop() {
            return window.innerWidth >= desktopBreakpoint;
        }

        // --- Desktop: colapsar/expandir ---
        function applyCollapsedState(collapsed) {
            if (!mainSidebar) return;
            if (collapsed) {
                mainSidebar.classList.add('collapsed');
            } else {
                mainSidebar.classList.remove('collapsed');
            }
        }

        function toggleCollapsed() {
            if (!mainSidebar) return;
            mainSidebar.classList.toggle('collapsed');
        }

        // Al cargar, el sidebar SIEMPRE inicia expandido (se ignora la preferencia guardada)
        if (isDesktop()) {
            applyCollapsedState(false);
        }

        // --- Móvil: drawer off-canvas ---
        function openMobileSidebar() {
            if (mainSidebar) mainSidebar.classList.add('show');
            if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
            document.body.classList.add('sidebar-open');
        }

        function closeMobileSidebar() {
            if (mainSidebar) mainSidebar.classList.remove('show');
            if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }

        // --- Click en botón de toggle ---
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (isDesktop()) {
                    toggleCollapsed();
                } else {
                    openMobileSidebar();
                }
            });
        }

        if (sidebarCloseBtn) {
            sidebarCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeMobileSidebar();
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', function(e) {
                e.preventDefault();
                closeMobileSidebar();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mainSidebar && mainSidebar.classList.contains('show')) {
                closeMobileSidebar();
            }
        });

        // Si el usuario cambia de tamaño de ventana, limpiar el estado móvil
        let resizeTimeout = null;
        window.addEventListener('resize', function() {
            if (resizeTimeout) clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (isDesktop()) {
                    closeMobileSidebar();
                    // El sidebar mantiene su estado actual al redimensionar
                } else {
                    if (mainSidebar) mainSidebar.classList.remove('collapsed');
                    closeMobileSidebar();
                }
            }, 150);
        });
    })();
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
        
        // Agregar clase de animación de salida al cerrar
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.classList.add('removing');
            setTimeout(() => this.remove(), 400);
        });
        
        // Si el usuario hace clic en cerrar, también animar
        const closeBtn = toastElement.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const toast = this.closest('.toast');
                toast.classList.add('removing');
                setTimeout(() => {
                    const bsToast = bootstrap.Toast.getInstance(toast);
                    if (bsToast) bsToast.hide();
                }, 200);
            });
        }
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

/**
 * Muestra un spinner de carga dentro de un contenedor de tabla.
 * @param {string} selectorContenedor - Selector CSS del tbody donde se muestra el spinner
 * @param {string} mensaje - Texto opcional a mostrar
 * @param {number} colspan - Cantidad de columnas a cubrir
 */
window.mostrarSpinnerTabla = function(selectorContenedor, mensaje = 'Buscando...', colspan = 10) {
    const contenedor = document.querySelector(selectorContenedor);
    if (!contenedor) return;

    contenedor.innerHTML = `
        <tr>
            <td colspan="${colspan}" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">${mensaje}</span>
                </div>
                <p class="mt-2 text-muted">${mensaje}</p>
            </td>
        </tr>
    `;
};
</script>

<script>
// Actualizar fecha y hora del sidebar en tiempo real
function actualizarHoraSidebar() {
    const ahora = new Date();
    
    // Formatear fecha: "DIA SEMANA, NUMERO DIA, MES Y AÑO"
    const opcionesFecha = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    const fechaFormateada = ahora.toLocaleDateString('es-MX', opcionesFecha);
    // Capitalizar primera letra
    const fechaCapitalizada = fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);
    
    // Formatear hora: "HORA, MINUTOS, SEGUNDOS"
    // Comentado porque el div de la hora está oculto
    // const horaFormateada = ahora.toLocaleTimeString('es-MX', {
    //     hour: '2-digit',
    //     minute: '2-digit',
    //     second: '2-digit',
    //     hour12: false
    // });
    
    const fechaElement = document.getElementById('sidebarFecha');
    // const horaElement = document.getElementById('sidebarHora');
    
    if (fechaElement) fechaElement.textContent = fechaCapitalizada;
    //if (horaElement) horaElement.textContent = horaFormateada;
}

// Actualizar cada segundo
document.addEventListener('DOMContentLoaded', function() {
    actualizarHoraSidebar();
    setInterval(actualizarHoraSidebar, 1000);
});
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
                                            onclick="marcarLeida(${idNotificacion})" 
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
    let mensajeSecundario = notif.mensaje;
    
    return `
        <div class="dropdown-item" style="cursor: default;">
            <div class="d-flex align-items-start">
                <i class="bi ${icono} ${color} me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>${escapeHtml(notif.folio || 'Pedido')}</strong>
                    <span class="badge bg-success ms-1">Listo</span>
                    <br>
                    <small class="text-muted">${escapeHtml(mensajeSecundario)}</small>
                    <br>
                    <small class="text-muted">Sucursales: ${escapeHtml(notif.sucursales || '')}</small>
                    <br>
                    <small class="text-muted" style="font-size: 0.7rem;">
                        ${new Date(notif.timestamp).toLocaleTimeString()}
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-sm" style="font-size: 0.6rem;" 
                        onclick="eliminarNotificacion('${notif.timestamp}')"></button>
            </div>
        </div>
        <div class="dropdown-divider"></div>
    `;
}

function contarNotificacionesSistema() {
    const lista = document.getElementById('listaNotificaciones');
    if (!lista) return 0;
    
    let count = 0;
    const items = lista.querySelectorAll('.dropdown-item:not(.text-muted)');
    items.forEach(item => {
        // Las de sistema tienen <a>
        if (item.querySelector('a')) {
            count++;
        }
    });
    return count;
}

// ============================================
// MARCAR NOTIFICACIÓN COMO LEÍDA
// ============================================
function marcarLeida(id) {
    if (!id) return;
    
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
            // Recargar notificaciones para actualizar la lista
            cargarNotificaciones();
            // Actualizar contador
            actualizarContadorGeneral();
        } else {
            console.error('Error al marcar como leída:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
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
                actualizarListaNotificaciones();
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
        
        if (notificaciones.length === 0) {
            // Si no hay Reverb, mostrar solo sistema o mensaje
            const sistemaCount = contarNotificacionesSistema();
            if (sistemaCount === 0) {
                lista.innerHTML = '<div class="dropdown-item text-muted text-center">No hay notificaciones</div>';
            }
            // Actualizar contador
            actualizarContadorGeneral();
            return;
        }
        
        let html = '';
        notificaciones.forEach(notif => {
            const icono = notif.tipo === 'pedido' ? 'bi-box-seam' : 'bi-bell';
            
            // Ambos tipos usan color success (verde)
            const color = 'text-success';
            const badgeText = notif.es_asignacion === true ? 'Asignado' : 'Listo';
            const badgeColor = notif.es_asignacion === true ? 'bg-success' : 'bg-success';
            
            // Mensaje según el tipo
            let mensajeMostrar = '';
            let sucursalesMostrar = '';
            
            if (notif.es_asignacion === true) {
                // Notificación de asignación a repartidor
                mensajeMostrar = `Se te ha asignado el Pedido ${notif.folio}`;
                sucursalesMostrar = notif.sucursales || '';
            } else {
                // Notificación de pedido listo (CRM)
                mensajeMostrar = notif.mensaje || 'Puede asignar repartidor';
                sucursalesMostrar = notif.sucursales || '';
            }
            
            html += `
                <div class="dropdown-item" style="cursor: default;">
                    <div class="d-flex align-items-start">
                        <i class="bi ${icono} ${color} me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong>${escapeHtml(notif.folio || 'Pedido')}</strong>
                            <span class="badge ${badgeColor} ms-1">${badgeText}</span>
                            <br>
                            <small class="text-muted">${escapeHtml(mensajeMostrar)}</small>
                            ${sucursalesMostrar ? `<br><small class="text-muted">Sucursales: ${escapeHtml(sucursalesMostrar)}</small>` : ''}
                            <br>
                            <small class="text-muted" style="font-size: 0.7rem;">
                                ${new Date(notif.timestamp).toLocaleTimeString()}
                            </small>
                        </div>
                        <button type="button" class="btn-close btn-close-sm" style="font-size: 0.6rem;" 
                                onclick="eliminarNotificacion('${notif.timestamp}')"></button>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
            `;
        });
        
        lista.innerHTML = html;

        actualizarContadorGeneral();
    }

    function actualizarContadorGeneral() {
        const contadorSpan = document.getElementById('contadorNotificaciones');
        if (!contadorSpan) return;
        
        // Contar notificaciones de sistema (agenda, cotizaciones, pedidos)
        const sistemaItems = document.querySelectorAll('#listaNotificaciones .dropdown-item .badge');
        let totalSistema = 0;
        sistemaItems.forEach(item => {
            // Solo contar si no es un badge de "Leída"
            if (!item.closest('.dropdown-item')?.querySelector('.btn-close')) {
                totalSistema++;
            }
        });
        
        // Notificaciones de Reverb en memoria
        const totalReverb = window.notificaciones ? window.notificaciones.length : 0;
        
        // Notificaciones de la tabla (se cuentan desde el fetch)
        // Usamos el total del data de la respuesta del fetch
        const total = totalSistema + totalReverb;
        
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

    // ============================================
    // FUNCIÓN ESCAPE HTML
    // ============================================
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
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

<script>
// ============================================
// SELECTOR DE RESULTADOS POR PÁGINA - GLOBAL
// ============================================

// Valor actual de per_page (compartido entre vistas)
window.perPageActual = 15;

window.inicializarSelectorPerPage = function(selectId, callbackRefrescar = null) {
    const perPageSelect = document.getElementById(selectId);
    if (!perPageSelect) return;

    window.perPageActual = parseInt(perPageSelect.value, 10) || 15;

    // onchange reemplaza el handler anterior (no duplica)
    perPageSelect.onchange = function() {
        const perPage = parseInt(this.value, 10) || 15;
        window.perPageActual = perPage;

        const urlObj = new URL(window.location.href);
        urlObj.searchParams.set('per_page', perPage);
        urlObj.searchParams.delete('page');

        if (typeof callbackRefrescar === 'function') {
            window.history.replaceState({}, '', urlObj);
            callbackRefrescar();
            return;
        }

        window.location.href = urlObj.toString();
    };
};
</script>

@stack('scripts')
</body>
</html>