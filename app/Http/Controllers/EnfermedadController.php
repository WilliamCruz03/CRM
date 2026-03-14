<?php

namespace App\Http\Controllers; // <-- IMPORTANTE: Debe ser este namespace

use App\Models\Patologia;
use App\Models\CategoriaEnfermedad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnfermedadController extends Controller // <-- La clase debe llamarse así
{
    public function index()
    {
        $patologias = Patologia::with('categoria')->get();
        $categorias = CategoriaEnfermedad::activos()->get();
        
        return view('enfermedades.index', compact('patologias', 'categorias'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categoria_enfermedades,id'
        ]);

        $patologia = Patologia::create([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patología creada correctamente',
            'data' => $patologia->load('categoria')
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        try {
            $patologia = Patologia::with('categoria')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $patologia->id,
                    'nombre' => $patologia->nombre,
                    'categoria_id' => $patologia->categoria_id,
                    'categoria' => $patologia->categoria ? [
                        'id' => $patologia->categoria->id,
                        'nombre' => $patologia->categoria->nombre
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la patología: ' . $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categoria_enfermedades,id'
        ]);

        $enfermedad = Enfermedad::findOrFail($id);
        $enfermedad->update([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enfermedad actualizada correctamente',
            'data' => $enfermedad->load('categoria')
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $enfermedad = Enfermedad::findOrFail($id);
            $enfermedad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Enfermedad eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCategoria(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categoria_enfermedades'
        ]);

        $categoria = CategoriaEnfermedad::create([
            'nombre' => $request->nombre,
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada correctamente',
            'data' => $categoria
        ]);
    }

    public function getCategorias(): JsonResponse
    {
        $categorias = CategoriaEnfermedad::activos()->get();
        
        return response()->json([
            'success' => true,
            'data' => $categorias
        ]);
    }

    /**
     * Get all diseases for select (AJAX)
     */
    public function getTodas(): JsonResponse
    {
        try {
            $enfermedades = Enfermedad::with('categoria')
                                    ->where('activo', true)
                                    ->orderBy('nombre')
                                    ->get();
            
            return response()->json([
                'success' => true,
                'data' => $enfermedades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

