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
        
        // Verificar si el usuario tiene algún permiso
        $tienePermisos = $user->tieneAlgunPermiso();
        
        // Si no tiene permisos, mostrar vista de "sin acceso"
        if (!$tienePermisos) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // Datos de ejemplo
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
            "tienePermisos"
        ));
    }
}