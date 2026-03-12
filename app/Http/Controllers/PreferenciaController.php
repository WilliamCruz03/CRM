<?php

namespace App\Http\Controllers;

use App\Models\Preferencia;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PreferenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $preferencias = Preferencia::with('cliente')
                                  ->orderBy('created_at', 'desc')
                                  ->get();
        
        $clientes = Cliente::where('estado', 'Activo')->orderBy('nombre')->get();
        
        return view('preferencias.index', compact('preferencias', 'clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descripcion' => 'required|string',
            'categoria' => 'nullable|string|max:100'
        ]);

        $preferencia = Preferencia::create([
            'cliente_id' => $validated['cliente_id'],
            'descripcion' => $validated['descripcion'],
            'categoria' => $validated['categoria'] ?? 'General',
            'fecha_registro' => now(),
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preferencia registrada correctamente',
            'data' => $preferencia->load('cliente')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $preferencia = Preferencia::with('cliente')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $preferencia
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $preferencia = Preferencia::findOrFail($id);

        $validated = $request->validate([
            'descripcion' => 'required|string',
            'categoria' => 'nullable|string|max:100'
        ]);

        $preferencia->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Preferencia actualizada correctamente',
            'data' => $preferencia->load('cliente')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $preferencia = Preferencia::findOrFail($id);
        $preferencia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Preferencia eliminada correctamente'
        ]);
    }

    /**
     * Get preferences by client
     */
    public function getByCliente(int $clienteId): JsonResponse
    {
        $preferencias = Preferencia::where('cliente_id', $clienteId)
                                   ->where('activo', true)
                                   ->orderBy('created_at', 'desc')
                                   ->get();
        
        return response()->json([
            'success' => true,
            'data' => $preferencias
        ]);
    }
}