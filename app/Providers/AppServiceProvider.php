<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ============================================
        // GATES PARA MÓDULOS (Mostrar/Ocultar en menú)
        // ============================================
        
        Gate::define('clientes.mostrar', function ($user) {
            return $user->puedeVerModulo('clientes');
        });
        
        Gate::define('ventas.mostrar', function ($user) {
            return $user->puedeVerModulo('ventas');
        });
        
        Gate::define('seguridad.mostrar', function ($user) {
            return $user->puedeVerModulo('seguridad');
        });
        
        Gate::define('reportes.mostrar', function ($user) {
            return $user->puedeVerModulo('reportes');
        });
        
        Gate::define('acceder-seguridad', function ($user) {
            return $user->puedeVerModulo('seguridad');
        });
        
        // ============================================
        // GATES PARA CLIENTES - Directorio
        // ============================================
        
        Gate::define('clientes.directorio.mostrar', function ($user) {
            return $user->puede('clientes', 'directorio', 'mostrar');
        });
        
        Gate::define('clientes.directorio.ver', function ($user) {
            return $user->puede('clientes', 'directorio', 'ver');
        });
        
        Gate::define('clientes.directorio.crear', function ($user) {
            return $user->puede('clientes', 'directorio', 'crear');
        });
        
        Gate::define('clientes.directorio.editar', function ($user) {
            return $user->puede('clientes', 'directorio', 'editar');
        });
        
        Gate::define('clientes.directorio.eliminar', function ($user) {
            return $user->puede('clientes', 'directorio', 'eliminar');
        });
        
        // ============================================
        // GATES PARA CLIENTES - Enfermedades
        // ============================================
        
        Gate::define('clientes.enfermedades.mostrar', function ($user) {
            return $user->puede('clientes', 'enfermedades', 'mostrar');
        });
        
        Gate::define('clientes.enfermedades.ver', function ($user) {
            return $user->puede('clientes', 'enfermedades', 'ver');
        });
        
        Gate::define('clientes.enfermedades.crear', function ($user) {
            return $user->puede('clientes', 'enfermedades', 'crear');
        });
        
        Gate::define('clientes.enfermedades.editar', function ($user) {
            return $user->puede('clientes', 'enfermedades', 'editar');
        });
        
        Gate::define('clientes.enfermedades.eliminar', function ($user) {
            return $user->puede('clientes', 'enfermedades', 'eliminar');
        });
        
        // ============================================
        // GATES PARA CLIENTES - Intereses
        // ============================================
        
        Gate::define('clientes.intereses.mostrar', function ($user) {
            return $user->puede('clientes', 'intereses', 'mostrar');
        });
        
        Gate::define('clientes.intereses.ver', function ($user) {
            return $user->puede('clientes', 'intereses', 'ver');
        });
        
        Gate::define('clientes.intereses.crear', function ($user) {
            return $user->puede('clientes', 'intereses', 'crear');
        });
        
        Gate::define('clientes.intereses.editar', function ($user) {
            return $user->puede('clientes', 'intereses', 'editar');
        });
        
        Gate::define('clientes.intereses.eliminar', function ($user) {
            return $user->puede('clientes', 'intereses', 'eliminar');
        });
        
        // ============================================
        // GATES PARA VENTAS - Cotizaciones
        // ============================================
        
        Gate::define('ventas.cotizaciones.mostrar', function ($user) {
            return $user->puede('ventas', 'cotizaciones', 'mostrar');
        });
        
        Gate::define('ventas.cotizaciones.ver', function ($user) {
            return $user->puede('ventas', 'cotizaciones', 'ver');
        });
        
        Gate::define('ventas.cotizaciones.crear', function ($user) {
            return $user->puede('ventas', 'cotizaciones', 'crear');
        });
        
        Gate::define('ventas.cotizaciones.editar', function ($user) {
            return $user->puede('ventas', 'cotizaciones', 'editar');
        });
        
        Gate::define('ventas.cotizaciones.eliminar', function ($user) {
            return $user->puede('ventas', 'cotizaciones', 'eliminar');
        });
        
        // ============================================
        // GATES PARA VENTAS - Pedidos Anticipo
        // ============================================
        
        Gate::define('ventas.pedidos_anticipo.mostrar', function ($user) {
            return $user->puede('ventas', 'pedidos_anticipo', 'mostrar');
        });
        
        Gate::define('ventas.pedidos_anticipo.ver', function ($user) {
            return $user->puede('ventas', 'pedidos_anticipo', 'ver');
        });
        
        Gate::define('ventas.pedidos_anticipo.crear', function ($user) {
            return $user->puede('ventas', 'pedidos_anticipo', 'crear');
        });
        
        Gate::define('ventas.pedidos_anticipo.editar', function ($user) {
            return $user->puede('ventas', 'pedidos_anticipo', 'editar');
        });
        
        Gate::define('ventas.pedidos_anticipo.eliminar', function ($user) {
            return $user->puede('ventas', 'pedidos_anticipo', 'eliminar');
        });
        
        // ============================================
        // GATES PARA VENTAS - Seguimiento Ventas
        // ============================================
        
        Gate::define('ventas.seguimiento_ventas.mostrar', function ($user) {
            return $user->puede('ventas', 'seguimiento_ventas', 'mostrar');
        });
        
        Gate::define('ventas.seguimiento_ventas.ver', function ($user) {
            return $user->puede('ventas', 'seguimiento_ventas', 'ver');
        });
        
        Gate::define('ventas.seguimiento_ventas.editar', function ($user) {
            return $user->puede('ventas', 'seguimiento_ventas', 'editar');
        });
        
        // ============================================
        // GATES PARA VENTAS - Seguimiento Cotizaciones
        // ============================================
        
        Gate::define('ventas.seguimiento_cotizaciones.mostrar', function ($user) {
            return $user->puede('ventas', 'seguimiento_cotizaciones', 'mostrar');
        });
        
        Gate::define('ventas.seguimiento_cotizaciones.ver', function ($user) {
            return $user->puede('ventas', 'seguimiento_cotizaciones', 'ver');
        });
        
        Gate::define('ventas.seguimiento_cotizaciones.editar', function ($user) {
            return $user->puede('ventas', 'seguimiento_cotizaciones', 'editar');
        });
        
        // ============================================
        // GATES PARA VENTAS - Agenda Contactos
        // ============================================
        
        Gate::define('ventas.agenda_contactos.mostrar', function ($user) {
            return $user->puede('ventas', 'agenda_contactos', 'mostrar');
        });
        
        Gate::define('ventas.agenda_contactos.ver', function ($user) {
            return $user->puede('ventas', 'agenda_contactos', 'ver');
        });
        
        Gate::define('ventas.agenda_contactos.crear', function ($user) {
            return $user->puede('ventas', 'agenda_contactos', 'crear');
        });
        
        Gate::define('ventas.agenda_contactos.editar', function ($user) {
            return $user->puede('ventas', 'agenda_contactos', 'editar');
        });
        
        Gate::define('ventas.agenda_contactos.eliminar', function ($user) {
            return $user->puede('ventas', 'agenda_contactos', 'eliminar');
        });
        
        // ============================================
        // GATES PARA SEGURIDAD - Usuarios
        // ============================================
        
        Gate::define('seguridad.usuarios.mostrar', function ($user) {
            return $user->puede('seguridad', 'usuarios', 'mostrar');
        });
        
        Gate::define('seguridad.usuarios.ver', function ($user) {
            return $user->puede('seguridad', 'usuarios', 'ver');
        });
        
        Gate::define('seguridad.usuarios.crear', function ($user) {
            return $user->puede('seguridad', 'usuarios', 'crear');
        });
        
        Gate::define('seguridad.usuarios.editar', function ($user) {
            return $user->puede('seguridad', 'usuarios', 'editar');
        });
        
        Gate::define('seguridad.usuarios.eliminar', function ($user) {
            return $user->puede('seguridad', 'usuarios', 'eliminar');
        });
        
        // ============================================
        // GATES PARA SEGURIDAD - Permisos
        // ============================================
        
        Gate::define('seguridad.permisos.mostrar', function ($user) {
            return $user->puede('seguridad', 'permisos', 'mostrar');
        });
        
        Gate::define('seguridad.permisos.ver', function ($user) {
            return $user->puede('seguridad', 'permisos', 'ver');
        });
        
        // ============================================
        // GATES PARA SEGURIDAD - Respaldos
        // ============================================
        
        Gate::define('seguridad.respaldos.mostrar', function ($user) {
            return $user->puede('seguridad', 'respaldos', 'mostrar');
        });
        
        Gate::define('seguridad.respaldos.ver', function ($user) {
            return $user->puede('seguridad', 'respaldos', 'ver');
        });
        
        // ============================================
        // GATES PARA REPORTES
        // ============================================
        
        Gate::define('reportes.compras_cliente.mostrar', function ($user) {
            return $user->puede('reportes', 'compras_cliente', 'mostrar');
        });
        
        Gate::define('reportes.compras_cliente.ver', function ($user) {
            return $user->puede('reportes', 'compras_cliente', 'ver');
        });
        
        Gate::define('reportes.frecuencia_compra.mostrar', function ($user) {
            return $user->puede('reportes', 'frecuencia_compra', 'mostrar');
        });
        
        Gate::define('reportes.frecuencia_compra.ver', function ($user) {
            return $user->puede('reportes', 'frecuencia_compra', 'ver');
        });
        
        Gate::define('reportes.montos_promedio.mostrar', function ($user) {
            return $user->puede('reportes', 'montos_promedio', 'mostrar');
        });
        
        Gate::define('reportes.montos_promedio.ver', function ($user) {
            return $user->puede('reportes', 'montos_promedio', 'ver');
        });
        
        Gate::define('reportes.sucursales_preferidas.mostrar', function ($user) {
            return $user->puede('reportes', 'sucursales_preferidas', 'mostrar');
        });
        
        Gate::define('reportes.sucursales_preferidas.ver', function ($user) {
            return $user->puede('reportes', 'sucursales_preferidas', 'ver');
        });
        
        Gate::define('reportes.cotizaciones_cliente.mostrar', function ($user) {
            return $user->puede('reportes', 'cotizaciones_cliente', 'mostrar');
        });
        
        Gate::define('reportes.cotizaciones_cliente.ver', function ($user) {
            return $user->puede('reportes', 'cotizaciones_cliente', 'ver');
        });
        
        Gate::define('reportes.cotizaciones_concretadas.mostrar', function ($user) {
            return $user->puede('reportes', 'cotizaciones_concretadas', 'mostrar');
        });
        
        Gate::define('reportes.cotizaciones_concretadas.ver', function ($user) {
            return $user->puede('reportes', 'cotizaciones_concretadas', 'ver');
        });
    }
}