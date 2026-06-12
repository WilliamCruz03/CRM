<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaisController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Especificar la conexión y tabla correctas
            $paises = \DB::connection('sqlsrvM')  // ← Usar la conexión a fp_central_matriz
                ->table('cat_paises')              // ← Nombre correcto de la tabla
                ->where('status', 1)
                ->orderBy('pais')
                ->get(['id as value', 'pais as text']);
            
            return response()->json($paises);
            
        } catch (\Exception $e) {
            Log::error('Error en PaisController: ' . $e->getMessage());
            
            // Fallback: países por defecto
            return response()->json($this->getDefaultCountries());
        }
    }
    
    private function getDefaultCountries()
    {
        return [
            ['value' => 1, 'text' => 'México'],
            ['value' => 2, 'text' => 'Estados Unidos'],
            ['value' => 3, 'text' => 'Canadá'],
            ['value' => 4, 'text' => 'Guatemala'],
            ['value' => 5, 'text' => 'El Salvador'],
            ['value' => 6, 'text' => 'Honduras'],
            ['value' => 7, 'text' => 'Nicaragua'],
            ['value' => 8, 'text' => 'Costa Rica'],
            ['value' => 9, 'text' => 'Panamá'],
            ['value' => 10, 'text' => 'Colombia'],
        ];
    }
}