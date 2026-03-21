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
        // Verificar permiso de VER
        if (!auth()->user()->puede('clientes', 'enfermedades', 'ver')) {
            abort(403, 'No tienes permiso para ver el catálogo de enfermedades');
        }
        
        $patologias = Patologia::orderBy('id_patologia', 'asc')->get();
        
        $permisos = [
            'crear' => auth()->user()->puede('clientes', 'enfermedades', 'crear'),
            'editar' => auth()->user()->puede('clientes', 'enfermedades', 'editar'),
            'eliminar' => auth()->user()->puede('clientes', 'enfermedades', 'eliminar'),
        ];
        
        return view('clientes.enfermedades.index', compact('patologias', 'permisos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Verificar permiso de CREAR
        if (!auth()->user()->puede('clientes', 'enfermedades', 'crear')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear enfermedades'
            ], 403);
        }
        
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255|unique:crm_cat_patologias,descripcion'
        ]);

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
        // Verificar permiso de EDITAR
        if (!auth()->user()->puede('clientes', 'enfermedades', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar enfermedades'
            ], 403);
        }
        
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
        // Verificar permiso de EDITAR
        if (!auth()->user()->puede('clientes', 'enfermedades', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar enfermedades'
            ], 403);
        }
        
        $patologia = Patologia::findOrFail($id);

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255|unique:crm_cat_patologias,descripcion,' . $id . ',id_patologia'
        ]);

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
        // Verificar permiso de ELIMINAR
        if (!auth()->user()->puede('clientes', 'enfermedades', 'eliminar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar enfermedades'
            ], 403);
        }
        
        $patologia = Patologia::findOrFail($id);
        
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