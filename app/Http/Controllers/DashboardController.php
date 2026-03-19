<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Verificar si el usuario tiene algún permiso
        $tienePermisos = $user->tieneAlgunPermiso();
        
        // Si no tiene permisos, mostrar vista de "sin acceso"
        if (!$tienePermisos) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // Aquí irán los datos reales cuando existan en la BD
        // Por ahora, datos de ejemplo que luego se reemplazarán
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

        // Últimos contactos (datos de ejemplo)
        $ultimosContactos = $this->obtenerUltimosContactos();
        
        // Últimas cotizaciones (datos de ejemplo)
        $ultimasCotizaciones = $this->obtenerUltimasCotizaciones();

        // Módulos a los que tiene acceso
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

    private function obtenerUltimosContactos()
    {
        // Aquí irá la lógica real cuando tengas la tabla de contactos
        // Por ahora, datos de ejemplo
        return [
            (object)[
                'cliente' => (object)['nombre' => 'Juan Pérez'],
                'fecha_contacto' => now()->subDays(2),
                'completado' => true
            ],
            (object)[
                'cliente' => (object)['nombre' => 'María López'],
                'fecha_contacto' => now()->subDays(1),
                'completado' => false
            ],
            (object)[
                'cliente' => (object)['nombre' => 'Carlos Ramírez'],
                'fecha_contacto' => now()->subDays(3),
                'completado' => true
            ]
        ];
    }

    private function obtenerUltimasCotizaciones()
    {
        // Aquí irá la lógica real cuando tengas la tabla de cotizaciones
        // Por ahora, datos de ejemplo
        return [
            (object)[
                'id' => 101,
                'cliente' => (object)['nombre' => 'Juan Pérez'],
                'estado' => 'aceptada',
                'total' => 570.00   
            ],
            (object)[
                'id' => 102,
                'cliente' => (object)['nombre' => 'María López'],
                'estado' => 'pendiente',
                'total' => 350.00   
            ],
            (object)[
                'id' => 103,
                'cliente' => (object)['nombre' => 'Carlos Ramírez'],
                'estado' => 'rechazada',
                'total' => 110.00   
            ]
        ];
    }
}