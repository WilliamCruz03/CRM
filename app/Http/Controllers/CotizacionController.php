<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    public function index(): View
    {
        // Verificar permiso de VER
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
            abort(403, 'No tienes permiso para ver las cotizaciones');
        }
        
        $permisos = [
            'crear' => auth()->user()->puede('ventas', 'cotizaciones', 'crear'),
            'editar' => auth()->user()->puede('ventas', 'cotizaciones', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'cotizaciones', 'eliminar'),
        ];
        
        return view('ventas.cotizaciones.index', compact('permisos'));
    }
    
    // Otros métodos se agregarán después con sus respectivas verificaciones
}