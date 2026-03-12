<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Enfermedad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     */
    public function index(Request $request): View
    {
        $perPage = 20; // Clientes por página
        
        $clientes = Cliente::with(['enfermedades.categoria', 'preferencias'])
                          ->orderBy('id', 'asc')
                          ->paginate($perPage);
        
        $enfermedades = Enfermedad::with('categoria')->activos()->get();
        
        // Si es una petición AJAX, devolver solo la tabla
        if ($request->ajax()) {
            return response()->json([
                'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
                'pagination' => (string) $clientes->links()
            ]);
        }
        
        return view('clientes.index', compact('clientes', 'enfermedades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes',
            'telefono' => 'nullable|string|max:20',
            'calle' => 'nullable|string|max:255',
            'colonia' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'enfermedades' => 'nullable|array',
            'enfermedades.*' => 'exists:enfermedades,id'
        ]);

        $cliente = Cliente::create([
            'nombre' => $validated['nombre'],
            'apellidos' => $validated['apellidos'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'calle' => $validated['calle'] ?? null,
            'colonia' => $validated['colonia'] ?? null,
            'ciudad' => $validated['ciudad'] ?? null,
            'estado' => 'Activo'
        ]);

        if (!empty($validated['enfermedades'])) {
            $cliente->enfermedades()->sync($validated['enfermedades']);
        }

        // Obtener la primera página actualizada
        $clientes = Cliente::with(['enfermedades', 'preferencias'])
                          ->orderBy('id', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado correctamente',
            'data' => $cliente->load(['enfermedades', 'preferencias']),
            'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
            'pagination' => (string) $clientes->links()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $cliente = Cliente::with(['enfermedades.categoria', 'preferencias'])
                          ->findOrFail($id);
        
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $cliente = Cliente::with(['enfermedades'])->findOrFail($id);
        $enfermedades = Enfermedad::with('categoria')->activos()->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'apellidos' => $cliente->apellidos,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
                'calle' => $cliente->calle,
                'colonia' => $cliente->colonia,
                'ciudad' => $cliente->ciudad,
                'estado' => $cliente->estado,
                'enfermedades' => $cliente->enfermedades->pluck('id')
            ],
            'enfermedades' => $enfermedades
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes,email,' . $id,
            'telefono' => 'nullable|string|max:20',
            'calle' => 'nullable|string|max:255',
            'colonia' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'estado' => 'required|in:Activo,Inactivo',
            'enfermedades' => 'nullable|array',
            'enfermedades.*' => 'exists:enfermedades,id'
        ]);

        $cliente->update($validated);

        // Sincronizar enfermedades
        if ($request->has('enfermedades')) {
            $cliente->enfermedades()->sync($request->enfermedades);
        } else {
            // Si no se enviaron enfermedades, eliminar todas las relaciones
            $cliente->enfermedades()->sync([]);
        }

        // Obtener la página actual actualizada
        $page = $request->get('page', 1);
        $clientes = Cliente::with(['enfermedades', 'preferencias'])
                        ->orderBy('id', 'desc')
                        ->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente',
            'data' => $cliente->load(['enfermedades', 'preferencias']),
            'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
            'pagination' => (string) $clientes->links()
        ]);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        $clientes = Cliente::with(['enfermedades', 'preferencias'])
                          ->orderBy('id', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente',
            'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
            'pagination' => (string) $clientes->links()
        ]);
    }
}