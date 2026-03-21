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
        $puedeVer = auth()->user()->puede('clientes', 'intereses', 'ver');
        $puedeCrear = auth()->user()->puede('clientes', 'intereses', 'crear');
        
        // Si no tiene ni ver ni crear, mostrar 403
        if (!$puedeVer && !$puedeCrear) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        $intereses = Interes::orderBy('id_interes', 'asc')->get();
        
        $permisos = [
            'ver' => $puedeVer,
            'crear' => $puedeCrear,
            'editar' => auth()->user()->puede('clientes', 'intereses', 'editar'),
            'eliminar' => auth()->user()->puede('clientes', 'intereses', 'eliminar'),
        ];
        
        return view('clientes.intereses.index', compact('intereses', 'permisos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Verificar permiso de CREAR
        if (!auth()->user()->puede('clientes', 'intereses', 'crear')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear intereses'
            ], 403);
        }
        
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
        // Verificar permiso de EDITAR
        if (!auth()->user()->puede('clientes', 'intereses', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar intereses'
            ], 403);
        }
        
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
        // Verificar permiso de EDITAR
        if (!auth()->user()->puede('clientes', 'intereses', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar intereses'
            ], 403);
        }
        
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
        // Verificar permiso de ELIMINAR
        if (!auth()->user()->puede('clientes', 'intereses', 'eliminar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar intereses'
            ], 403);
        }
        
        $interes = Interes::findOrFail($id);
        $interes->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interés eliminado correctamente'
        ]);
    }
}