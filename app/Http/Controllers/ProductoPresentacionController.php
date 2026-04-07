<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\CatalogoGeneral;
use App\Models\CatSalesPresentacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductoPresentacionController extends Controller
{
    /**
     * Asociar un producto con una presentación de medicamento
     */
    public function asociar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_catalogo_general' => 'required|exists:catalogo_general,id_catalogo_general',
            'id_presentacion' => 'required|exists:cat_sales_presentacion,id',
        ]);
        
        $producto = CatalogoGeneral::find($validated['id_catalogo_general']);
        $presentacion = CatSalesPresentacion::find($validated['id_presentacion']);
        
        if (!$producto->presentaciones()->where('id_presentacion', $presentacion->id)->exists()) {
            $producto->presentaciones()->attach($presentacion->id);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Producto asociado correctamente',
            'data' => [
                'producto' => $producto->descripcion,
                'sustancia' => $presentacion->sustancia
            ]
        ]);
    }
    
    /**
     * Desasociar un producto de una presentación
     */
    public function desasociar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_catalogo_general' => 'required|exists:catalogo_general,id_catalogo_general',
            'id_presentacion' => 'required|exists:cat_sales_presentacion,id',
        ]);
        
        $producto = CatalogoGeneral::find($validated['id_catalogo_general']);
        $producto->presentaciones()->detach($validated['id_presentacion']);
        
        return response()->json([
            'success' => true,
            'message' => 'Asociación eliminada correctamente'
        ]);
    }
    
    /**
     * Buscar presentaciones por sustancia activa (para autocomplete)
     */
    public function buscarPresentaciones(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        
        $presentaciones = CatSalesPresentacion::where('sustancia', 'LIKE', "%{$termino}%")
            ->orWhere('indicacion_terapeutica_1', 'LIKE', "%{$termino}%")
            ->limit(20)
            ->get(['id', 'sustancia', 'concentracion', 'presentacion']);
        
        return response()->json([
            'success' => true,
            'data' => $presentaciones->map(function($p) {
                return [
                    'id' => $p->id,
                    'text' => $p->nombre_completo,
                    'sustancia' => $p->sustancia,
                    'concentracion' => $p->concentracion,
                    'presentacion' => $p->presentacion,
                ];
            })
        ]);
    }
}