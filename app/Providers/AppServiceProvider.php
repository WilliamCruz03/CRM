<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ============================================
        // GATES DE PERMISOS PARA MÓDULOS (Mostrar/Ocultar)
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
        
        Gate::define('clientes.ver', function ($user) {
            return $user->puede('clientes', 'ver');
        });
        
        Gate::define('clientes.altas', function ($user) {
            return $user->puede('clientes', 'altas');
        });
        
        Gate::define('clientes.edicion', function ($user) {
            return $user->puede('clientes', 'edicion');
        });
        
        Gate::define('clientes.eliminar', function ($user) {
            return $user->puede('clientes', 'eliminar');
        });
        
        Gate::define('enfermedades.ver', function ($user) {
            return $user->puede('enfermedades', 'ver');
        });
        
        Gate::define('intereses.ver', function ($user) {
            return $user->puede('intereses', 'ver');
        });
        
        // ============================================
        // GATES DE PERMISOS PARA VENTAS
        // ============================================
        
        Gate::define('cotizaciones.ver', function ($user) {
            return $user->puede('cotizaciones', 'ver');
        });
        
        Gate::define('cotizaciones.altas', function ($user) {
            return $user->puede('cotizaciones', 'altas');
        });
        
        Gate::define('cotizaciones.edicion', function ($user) {
            return $user->puede('cotizaciones', 'edicion');
        });
        
        Gate::define('cotizaciones.eliminar', function ($user) {
            return $user->puede('cotizaciones', 'eliminar');
        });
        
        Gate::define('pedidos_anticipo.ver', function ($user) {
            return $user->puede('pedidos_anticipo', 'ver');
        });
        
        Gate::define('pedidos_anticipo.altas', function ($user) {
            return $user->puede('pedidos_anticipo', 'altas');
        });
        
        Gate::define('pedidos_anticipo.edicion', function ($user) {
            return $user->puede('pedidos_anticipo', 'edicion');
        });
        
        Gate::define('pedidos_anticipo.eliminar', function ($user) {
            return $user->puede('pedidos_anticipo', 'eliminar');
        });
        
        Gate::define('seguimiento_ventas.ver', function ($user) {
            return $user->puede('seguimiento_ventas', 'ver');
        });
        
        Gate::define('seguimiento_ventas.edicion', function ($user) {
            return $user->puede('seguimiento_ventas', 'edicion');
        });
        
        Gate::define('seguimiento_ventas.eliminar', function ($user) {
            return $user->puede('seguimiento_ventas', 'eliminar');
        });
        
        Gate::define('seguimiento_cotizaciones.ver', function ($user) {
            return $user->puede('seguimiento_cotizaciones', 'ver');
        });
        
        Gate::define('seguimiento_cotizaciones.edicion', function ($user) {
            return $user->puede('seguimiento_cotizaciones', 'edicion');
        });
        
        Gate::define('seguimiento_cotizaciones.eliminar', function ($user) {
            return $user->puede('seguimiento_cotizaciones', 'eliminar');
        });
        
        Gate::define('agenda_contactos.ver', function ($user) {
            return $user->puede('agenda_contactos', 'ver');
        });
        
        Gate::define('agenda_contactos.altas', function ($user) {
            return $user->puede('agenda_contactos', 'altas');
        });
        
        Gate::define('agenda_contactos.edicion', function ($user) {
            return $user->puede('agenda_contactos', 'edicion');
        });
        
        Gate::define('agenda_contactos.eliminar', function ($user) {
            return $user->puede('agenda_contactos', 'eliminar');
        });
        
        // ============================================
        // GATES DE PERMISOS PARA SEGURIDAD
        // ============================================
        
        Gate::define('seguridad.usuarios.ver', function ($user) {
            return $user->puede('usuarios', 'ver');
        });
        
        Gate::define('seguridad.usuarios.altas', function ($user) {
            return $user->puede('usuarios', 'altas');
        });
        
        Gate::define('seguridad.usuarios.edicion', function ($user) {
            return $user->puede('usuarios', 'edicion');
        });
        
        Gate::define('seguridad.usuarios.eliminar', function ($user) {
            return $user->puede('usuarios', 'eliminar');
        });
        
        Gate::define('seguridad.permisos.ver', function ($user) {
            return $user->puede('permisos', 'ver');
        });
        
        Gate::define('seguridad.respaldos.ver', function ($user) {
            return $user->puede('respaldos', 'ver');
        });
        
        // ============================================
        // GATES DE PERMISOS PARA REPORTES
        // ============================================
        
        Gate::define('reportes.compras_cliente', function ($user) {
            return $user->puede('reportes', 'compras_cliente');
        });
        
        Gate::define('reportes.frecuencia_compra', function ($user) {
            return $user->puede('reportes', 'frecuencia_compra');
        });
        
        Gate::define('reportes.montos_promedio', function ($user) {
            return $user->puede('reportes', 'montos_promedio');
        });
        
        Gate::define('reportes.sucursales_preferidas', function ($user) {
            return $user->puede('reportes', 'sucursales_preferidas');
        });
        
        Gate::define('reportes.cotizaciones_cliente', function ($user) {
            return $user->puede('reportes', 'cotizaciones_cliente');
        });
        
        Gate::define('reportes.cotizaciones_concretadas', function ($user) {
            return $user->puede('reportes', 'cotizaciones_concretadas');
        });
    }
}