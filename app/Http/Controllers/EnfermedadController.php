<?php

namespace App\Http\Controllers; // <-- IMPORTANTE: Debe ser este namespace

use App\Models\Enfermedad;
use App\Models\CategoriaEnfermedad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnfermedadController extends Controller // <-- La clase debe llamarse así
{
    public function index()
    {
        $enfermedades = Enfermedad::with('categoria')->get();
        $categorias = CategoriaEnfermedad::activos()->get();
        
        return view('enfermedades.index', compact('enfermedades', 'categorias'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categoria_enfermedades,id'
        ]);

        $enfermedad = Enfermedad::create([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enfermedad creada correctamente',
            'data' => $enfermedad->load('categoria')
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        try {
            $enfermedad = Enfermedad::with('categoria')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $enfermedad->id,
                    'nombre' => $enfermedad->nombre,
                    'categoria_id' => $enfermedad->categoria_id,
                    'categoria' => $enfermedad->categoria ? [
                        'id' => $enfermedad->categoria->id,
                        'nombre' => $enfermedad->categoria->nombre
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la enfermedad: ' . $e->getMessage()
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
}