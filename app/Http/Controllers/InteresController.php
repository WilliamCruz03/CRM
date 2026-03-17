<?php

namespace App\Http\Controllers;

use App\Models\Interes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InteresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $intereses = Interes::orderBy('id_interes', 'asc')->get();
        
        return view('clientes.intereses.index', compact('intereses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Descripcion' => 'required|string|max:100|unique:crm_cat_intereses,Descripcion'
        ]);

        $interes = Interes::create([
            'Descripcion' => $validated['Descripcion'],
            'fecha_creacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interés creado correctamente',
            'data' => $interes
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $interes = Interes::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id_interes' => $interes->id_interes,
                'Descripcion' => $interes->Descripcion
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $interes = Interes::findOrFail($id);

        $validated = $request->validate([
            'Descripcion' => 'required|string|max:100|unique:crm_cat_intereses,Descripcion,' . $id . ',id_interes'
        ]);

        $interes->update([
            'Descripcion' => $validated['Descripcion']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interés actualizado correctamente',
            'data' => $interes
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $interes = Interes::findOrFail($id);
        
        // Aquí podrías verificar si está siendo usado antes de eliminar
        // Por ahora, solo eliminamos
        $interes->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interés eliminado correctamente'
        ]);
    }
}