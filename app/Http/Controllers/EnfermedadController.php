<?php

namespace App\Http\Controllers;

use App\Models\Patologia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EnfermedadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Ordenar por ID ascendente
        $patologias = Patologia::orderBy('id_patologia', 'asc')->get();
        
        return view('enfermedades.index', compact('patologias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255|unique:crm_cat_patologias,descripcion'
        ]);

        // Convertir a mayúsculas antes de guardar
        $descripcion = strtoupper(trim($validated['descripcion']));

        $patologia = Patologia::create([
            'descripcion' => $descripcion,
            'fecha_creacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patología creada correctamente',
            'data' => $patologia
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
     public function edit(int $id): JsonResponse
    {
        $patologia = Patologia::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id_patologia' => $patologia->id_patologia,
                'descripcion' => $patologia->descripcion
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, int $id): JsonResponse
    {
        $patologia = Patologia::findOrFail($id);

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255|unique:crm_cat_patologias,descripcion,' . $id . ',id_patologia'
        ]);

        // Convertir a mayúsculas antes de actualizar
        $descripcion = strtoupper(trim($validated['descripcion']));

        $patologia->update([
            'descripcion' => $descripcion
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patología actualizada correctamente',
            'data' => $patologia
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $patologia = Patologia::findOrFail($id);
        
        // Verificar si está siendo usada
        $usos = \DB::table('crm_patologia_asociada')
                  ->where('patologia', $patologia->descripcion)
                  ->count();
        
        if ($usos > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar porque está asociada a ' . $usos . ' cliente(s)'
            ], 422);
        }

        $patologia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patología eliminada correctamente'
        ]);
    }

    /**
     * Get all diseases for select (AJAX)
     */
    public function getTodas(): JsonResponse
    {
        $patologias = Patologia::orderBy('id_patologia', 'asc')->get(['id_patologia', 'descripcion']);
        
        return response()->json([
            'success' => true,
            'data' => $patologias
        ]);
    }
}
