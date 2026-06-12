<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clientes\CatPais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaisController extends Controller
{
    /**
     * Obtener lista de países activos
     */
    public function index(Request $request)
    {
        try {
            $query = CatPais::where('status', 1);
            
            // Si hay búsqueda por texto
            if ($request->has('q') && !empty($request->q)) {
                $query->where('pais', 'like', '%' . $request->q . '%');
            }
            
            $paises = $query->orderBy('pais')
                ->get(['id as value', 'pais as text']);
            
            return response()->json($paises);
            
        } catch (\Exception $e) {
            Log::error('Error en PaisController@index: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar países: ' . $e->getMessage()
            ], 500);
        }
    }
}