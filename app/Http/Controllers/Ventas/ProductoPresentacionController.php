<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\CatalogoGeneral;
use App\Models\CatSalesPresentacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductoPresentacionController extends Controller
{
    /**
     * Buscar presentaciones por sustancia activa (para autocomplete)
     */
    public function buscarPresentaciones(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        
        if (strlen($termino) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        $presentaciones = CatSalesPresentacion::where('sustancia', 'LIKE', "%{$termino}%")
            ->orWhere('indicacion_terapeutica_1', 'LIKE', "%{$termino}%")
            ->limit(20)
            ->get(['id', 'sustancia', 'concentracion', 'presentacion']);
        
        return response()->json([
            'success' => true,
            'data' => $presentaciones->map(function($p) {
                return [
                    'id' => $p->id,
                    'text' => $this->getNombreCompleto($p),
                    'sustancia' => $p->sustancia,
                    'concentracion' => $p->concentracion,
                    'presentacion' => $p->presentacion,
                ];
            })
        ]);
    }
    
    /**
     * Asociar un producto con una presentación de medicamento
     */
    public function asociar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_catalogo_general' => 'required|exists:catalogo_general,id_catalogo_general',
            'id_presentacion' => 'required|exists:cat_sales_presentacion,id',
        ]);
        
        // Verificar si ya existe
        $existe = DB::table('catalogo_presentacion')
            ->where('id_catalogo_general', $validated['id_catalogo_general'])
            ->where('id_presentacion', $validated['id_presentacion'])
            ->exists();
        
        if (!$existe) {
            DB::table('catalogo_presentacion')->insert([
                'id_catalogo_general' => $validated['id_catalogo_general'],
                'id_presentacion' => $validated['id_presentacion'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $producto = CatalogoGeneral::find($validated['id_catalogo_general']);
        $presentacion = CatSalesPresentacion::find($validated['id_presentacion']);
        
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
        
        DB::table('catalogo_presentacion')
            ->where('id_catalogo_general', $validated['id_catalogo_general'])
            ->where('id_presentacion', $validated['id_presentacion'])
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Asociación eliminada correctamente'
        ]);
    }
    
    /**
     * Obtener todas las asociaciones de un producto
     */
    public function getAsociaciones(int $idProducto): JsonResponse
    {
        $producto = CatalogoGeneral::with('presentaciones')->findOrFail($idProducto);
        
        return response()->json([
            'success' => true,
            'data' => [
                'producto' => [
                    'id' => $producto->id_catalogo_general,
                    'nombre' => $producto->descripcion,
                    'codbar' => $producto->ean,
                ],
                'presentaciones' => $producto->presentaciones->map(function($p) {
                    return [
                        'id' => $p->id,
                        'sustancia' => $p->sustancia,
                        'concentracion' => $p->concentracion,
                        'presentacion' => $p->presentacion,
                        'nombre_completo' => $this->getNombreCompleto($p),
                    ];
                })
            ]
        ]);
    }
    
    /**
     * Obtener nombre completo de la presentación
     */
    private function getNombreCompleto($presentacion): string
    {
        $parts = [];
        if ($presentacion->sustancia) $parts[] = $presentacion->sustancia;
        if ($presentacion->concentracion) $parts[] = $presentacion->concentracion;
        if ($presentacion->presentacion) $parts[] = $presentacion->presentacion;
        return implode(' ', $parts);
    }
}