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
        // GATES DE PERMISOS
        // ============================================

        // Gate para verificar si puede ver un módulo en el menú
        Gate::define('clientes.mostrar', function ($user) {
            return $user->tieneAccesoAModulo('cliente');
        });

        Gate::define('clientes.ver', function ($user) {
            return $user->puede('clientes', 'ver');
        });

        Gate::define('enfermedades.ver', function ($user) {
            return $user->puede('enfermedades', 'ver');
        });

        Gate::define('intereses.ver', function ($user) {
            return $user->puede('intereses', 'ver');
        });

        Gate::define('cotizaciones.ver', function ($user) {
            return $user->puede('cotizaciones', 'ver');
        });

        Gate::define('cotizaciones.mostrar', function ($user) {
            return $user->tieneAccesoAModulo('ventas');
        });

        Gate::define('pedidos_anticipo.ver', function ($user) {
            return $user->puede('pedidos_anticipo', 'ver');
        });

        Gate::define('seguimiento_ventas.ver', function ($user) {
            return $user->puede('seguimiento_ventas', 'ver');
        });

        Gate::define('seguimiento_cotizaciones.ver', function ($user) {
            return $user->puede('seguimiento_cotizaciones', 'ver');
        });

        Gate::define('agenda_contactos.ver', function ($user) {
            return $user->puede('agenda_contactos', 'ver');
        });

        Gate::define('seguridad.ver', function ($user) {
            return $user->puede('seguridad', 'ver');
        });

        Gate::define('seguridad.mostrar', function ($user) {
            return $user->tieneAccesoAModulo('seguridad');
        });

        Gate::define('reportes.mostrar', function ($user) {
            return $user->tieneAccesoAModulo('reportes');
        });

        Gate::define('acceder-seguridad', function ($user) {
            return $user->tieneAccesoAModulo('seguridad');
        });
    }
}