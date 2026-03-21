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
        // GATES DE PERMISOS PARA MÓDULOS (Mostrar/Ocultar en menú)
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
        // GATES DE PERMISOS PARA CLIENTES
        // ============================================
        
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
        // GATES DE PERMISOS PARA VENTAS
        // ============================================
        
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
        
        Gate::define('ventas.seguimiento_ventas.ver', function ($user) {
            return $user->puede('ventas', 'seguimiento_ventas', 'ver');
        });
        
        Gate::define('ventas.seguimiento_ventas.editar', function ($user) {
            return $user->puede('ventas', 'seguimiento_ventas', 'editar');
        });
        
        Gate::define('ventas.seguimiento_cotizaciones.ver', function ($user) {
            return $user->puede('ventas', 'seguimiento_cotizaciones', 'ver');
        });
        
        Gate::define('ventas.seguimiento_cotizaciones.editar', function ($user) {
            return $user->puede('ventas', 'seguimiento_cotizaciones', 'editar');
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
        // GATES DE PERMISOS PARA SEGURIDAD
        // ============================================
        
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
        
        Gate::define('seguridad.permisos.ver', function ($user) {
            return $user->puede('seguridad', 'permisos', 'ver');
        });
        
        Gate::define('seguridad.respaldos.ver', function ($user) {
            return $user->puede('seguridad', 'respaldos', 'ver');
        });
        
        // ============================================
        // GATES DE PERMISOS PARA REPORTES
        // ============================================
        
        Gate::define('reportes.compras_cliente.ver', function ($user) {
            return $user->puede('reportes', 'compras_cliente', 'ver');
        });
        
        Gate::define('reportes.frecuencia_compra.ver', function ($user) {
            return $user->puede('reportes', 'frecuencia_compra', 'ver');
        });
        
        Gate::define('reportes.montos_promedio.ver', function ($user) {
            return $user->puede('reportes', 'montos_promedio', 'ver');
        });
        
        Gate::define('reportes.sucursales_preferidas.ver', function ($user) {
            return $user->puede('reportes', 'sucursales_preferidas', 'ver');
        });
        
        Gate::define('reportes.cotizaciones_cliente.ver', function ($user) {
            return $user->puede('reportes', 'cotizaciones_cliente', 'ver');
        });
        
        Gate::define('reportes.cotizaciones_concretadas.ver', function ($user) {
            return $user->puede('reportes', 'cotizaciones_concretadas', 'ver');
        });
    }
}