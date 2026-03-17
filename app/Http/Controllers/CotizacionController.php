<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    public function index(): View
    {
        return view('ventas.cotizaciones.index');
    }
    
    // Otros métodos (create, store, etc.) se agregarán después
}