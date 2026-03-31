<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Verificar autenticación - si no está logueado, redirigir al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Obtener todos los submódulos donde el usuario tiene mostrar = true
        $submodulosVisibles = $user->permisosGranulares()
            ->where('mostrar', true)
            ->get();
        
        // Si no tiene ningún submódulo visible, mostrar sin-acceso
        if ($submodulosVisibles->isEmpty()) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // Verificar si tiene al menos un submódulo con ver = true
        $tieneAlgunVer = $submodulosVisibles->contains(function($permiso) {
            return $permiso->ver === true;
        });
        
        // Si tiene mostrar pero ningún ver, mostrar sin-acceso
        if (!$tieneAlgunVer) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // Verificar permisos específicos para cada card del dashboard
        $mostrarCardClientes = false;
        $mostrarCardCotizaciones = false;
        
        // Buscar el permiso específico para clientes -> directorio
        $permisoDirectorio = $submodulosVisibles->first(function($permiso) {
            return $permiso->modulo === 'clientes' && $permiso->submodulo === 'directorio';
        });
        
        if ($permisoDirectorio && $permisoDirectorio->ver === true) {
            $mostrarCardClientes = true;
        }
        
        // Buscar el permiso específico para ventas -> cotizaciones
        $permisoCotizaciones = $submodulosVisibles->first(function($permiso) {
            return $permiso->modulo === 'ventas' && $permiso->submodulo === 'cotizaciones';
        });
        
        if ($permisoCotizaciones && $permisoCotizaciones->ver === true) {
            $mostrarCardCotizaciones = true;
        }
        
        // Si no tiene ningún card para mostrar, mostrar sin-acceso
        if (!$mostrarCardClientes && !$mostrarCardCotizaciones) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // Variables base (se calculan aunque no se muestren)
        $totalClientes = 142;
        $totalCotizaciones = 58;
        $cotizacionesPendientes = 12;
        $contactosProximos = 8;

        $estadosCotizaciones = [
            "aceptadas" => 18,
            "pendientes" => 12,
            "rechazadas" => 5
        ];

        $montosEsteMes = 20000.00;

        $ultimosContactos = [
            (object)[
                'cliente' => (object)['nombre' => 'Juan Perez'],
                'fecha_contacto' => now()->subDays(2),
                'completado' => true
            ],
            (object)[
                'cliente' => (object)['nombre' => 'Maria Lopez'],
                'fecha_contacto' => now()->subDays(1),
                'completado' => false
            ],
            (object)[
                'cliente' => (object)['nombre' => 'Carlos Ramirez'],
                'fecha_contacto' => now()->subDays(3),
                'completado' => true
            ]
        ];

        $ultimasCotizaciones = [
            (object)[
                'id' => 101,
                'cliente' => (object)['nombre' => 'Juan Perez'],
                'estado' => 'aceptada',
                'total' => 570.00   
            ],
            (object)[
                'id' => 102,
                'cliente' => (object)['nombre' => 'Maria Lopez'],
                'estado' => 'pendiente',
                'total' => 350.00   
            ],
            (object)[
                'id' => 103,
                'cliente' => (object)['nombre' => 'Carlos Ramirez'],
                'estado' => 'rechazada',
                'total' => 110.00   
            ]
        ];

        $modulosAcceso = $user->modulosConAcceso();

        return view("dashboard.index", compact(
            "totalClientes",
            "totalCotizaciones",
            "cotizacionesPendientes",
            "contactosProximos",
            "estadosCotizaciones",
            "montosEsteMes",
            "ultimosContactos",
            "ultimasCotizaciones",
            "modulosAcceso",
            "mostrarCardClientes",
            "mostrarCardCotizaciones"
        ));
    }
}