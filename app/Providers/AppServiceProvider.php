<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate genérico para verificar cualquier permiso
        Gate::define('permiso', function ($user, $modulo, $accion) {
            return $user->can($modulo, $accion);
        });

        // Gates para cada módulo
        $modulos = [
            'clientes',
            'enfermedades',
            'intereses',
            'cotizaciones',
            'pedidos_anticipo',
            'seguimiento_ventas',
            'seguimiento_cotizaciones',
            'agenda_contactos',
            'reportes',
            'seguridad'
        ];

        // Definir gates para acciones comunes
        $acciones = ['ver', 'altas', 'edicion', 'eliminar'];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Gate::define("{$modulo}.{$accion}", function ($user) use ($modulo, $accion) {
                    return $user->can($modulo, $accion);
                });
            }
            
            // Gate especial para ver el módulo en el menú
            Gate::define("{$modulo}.mostrar", function ($user) use ($modulo) {
                return $user->canViewModule($modulo);
            });
        }

        // Gates específicos para reportes
        $reportes = [
            'compras_cliente',
            'frecuencia_compra',
            'montos_promedio',
            'sucursales_preferidas',
            'cotizaciones_cliente',
            'cotizaciones_concretadas'
        ];
        
        foreach ($reportes as $reporte) {
            Gate::define("reportes.{$reporte}", function ($user) use ($reporte) {
                return $user->can('reportes', $reporte);
            });
        }

        // Gate para verificar si puede acceder a seguridad (útil para el menú)
        Gate::define('acceder-seguridad', function ($user) {
            return $user->can('seguridad', 'ver') || 
                   $user->can('seguridad', 'altas') ||
                   $user->can('seguridad', 'edicion') ||
                   $user->can('seguridad', 'eliminar');
        });
    }
}