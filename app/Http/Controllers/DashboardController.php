<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // Lógica para mostrar el dashboard
        //Datos de ejemplo para el dashboard
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

        //Ultimos contactos agendados
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

        //Ultimas cotizaciones
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

        return view("dashboard.index", compact(
            "totalClientes",
            "totalCotizaciones",
            "cotizacionesPendientes",
            "contactosProximos",
            "estadosCotizaciones",
            "montosEsteMes",
            "ultimosContactos",
            "ultimasCotizaciones"
        ));
    }
}
