<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Patologia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = 20;

        $clientes = Cliente::with('enfermedades')
                        ->orderBy('id_Cliente', 'asc')
                        ->paginate($perPage);

        $patologias = Patologia::all(); // Para cargar en los modales

        if ($request->ajax()) {
            return response()->json([
                'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
                'pagination' => (string) $clientes->links()
            ]);
        }

        return view('clientes.index', compact('clientes', 'patologias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Nombre' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apPaterno' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apMaterno' => 'nullable|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'titulo' => 'nullable|string|max:20',
            'email1' => 'required|email|unique:catalogo_cliente_maestro,email1',
            'telefono1' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
            'telefono2' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
            'Domicilio' => 'nullable|string|max:500',
            'Sexo' => 'nullable|in:M,F,OTRO',
            'FechaNac' => 'nullable|date',
            'status' => 'nullable|in:CLIENTE,PROSPECTO,BLOQUEADO',
            'pais_id' => 'nullable|integer',
            'estado_id' => 'nullable|integer',
            'municipio_id' => 'nullable|integer',
            'localidad_id' => 'nullable|integer',
            'enfermedades' => 'nullable|array',
            'enfermedades.*' => 'exists:crm_cat_patologias,id_patologia'
        ]);

        // Crear el cliente
        $cliente = Cliente::create([
            'sucursal_origen' => 0,
            'Nombre' => $validated['Nombre'],
            'apPaterno' => $validated['apPaterno'],
            'apMaterno' => $validated['apMaterno'] ?? null,
            'titulo' => $validated['titulo'] ?? null,
            'email1' => $validated['email1'],
            'telefono1' => $validated['telefono1'] ?? null,
            'telefono2' => $validated['telefono2'] ?? null,
            'Domicilio' => $validated['Domicilio'] ?? null,
            'Sexo' => $validated['Sexo'] ?? null,
            'FechaNac' => $validated['FechaNac'] ?? null,
            'status' => $validated['status'] ?? 'PROSPECTO',
            'pais_id' => $validated['pais_id'] ?? null,
            'estado_id' => $validated['estado_id'] ?? null,
            'municipio_id' => $validated['municipio_id'] ?? null,
            'localidad_id' => $validated['localidad_id'] ?? null,
            'id_operador' => 1, // Temporal, luego con auth()->id()
            'fecha_creacion' => now()
        ]);

        // Sincronizar enfermedades en tabla pivote
        if (!empty($validated['enfermedades'])) {
            foreach ($validated['enfermedades'] as $patologiaId) {
                DB::table('crm_patologia_asociada')->insert([
                    'id_cliente_maestro' => $cliente->id_Cliente,
                    'patologia' => $patologiaId,
                    'fecha_creacion' => now(),
                    'id_operador' => 1,
                    'status' => 'ACTIVO'
                ]);
            }
        }

        // Obtener clientes actualizados para la tabla
        $clientes = Cliente::with('enfermedades')
                          ->orderBy('id_Cliente', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado correctamente',
            'data' => $cliente->load('enfermedades'),
            'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
            'pagination' => (string) $clientes->links()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $cliente = Cliente::with('enfermedades')->findOrFail($id);
        
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $cliente = Cliente::with('enfermedades')->findOrFail($id);
        $patologias = Patologia::all();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id_Cliente' => $cliente->id_Cliente,
                'Nombre' => $cliente->Nombre,
                'apPaterno' => $cliente->apPaterno,
                'apMaterno' => $cliente->apMaterno,
                'titulo' => $cliente->titulo,
                'email1' => $cliente->email1,
                'telefono1' => $cliente->telefono1,
                'telefono2' => $cliente->telefono2,
                'Domicilio' => $cliente->Domicilio,
                'Sexo' => $cliente->Sexo,
                'FechaNac' => $cliente->FechaNac,
                'status' => $cliente->status,
                'pais_id' => $cliente->pais_id,
                'estado_id' => $cliente->estado_id,
                'municipio_id' => $cliente->municipio_id,
                'localidad_id' => $cliente->localidad_id,
                'enfermedades' => $cliente->enfermedades->pluck('id_patologia')
            ],
            'patologias' => $patologias
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'Nombre' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apPaterno' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apMaterno' => 'nullable|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'titulo' => 'nullable|string|max:20',
            'email1' => 'required|email|unique:catalogo_cliente_maestro,email1,' . $id . ',id_Cliente',
            'telefono1' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
            'telefono2' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
            'Domicilio' => 'nullable|string|max:500',
            'Sexo' => 'nullable|in:M,F,OTRO',
            'FechaNac' => 'nullable|date',
            'status' => 'required|in:CLIENTE,PROSPECTO,BLOQUEADO',
            'pais_id' => 'nullable|integer',
            'estado_id' => 'nullable|integer',
            'municipio_id' => 'nullable|integer',
            'localidad_id' => 'nullable|integer',
            'enfermedades' => 'nullable|array',
            'enfermedades.*' => 'exists:crm_cat_patologias,id_patologia'
        ]);

        // Actualizar datos del cliente
        $cliente->update($validated);

        // Actualizar enfermedades en tabla pivote
        // Primero eliminar relaciones existentes
        DB::table('crm_patologia_asociada')
          ->where('id_cliente_maestro', $cliente->id_Cliente)
          ->delete();

        // Insertar nuevas relaciones
        if (!empty($validated['enfermedades'])) {
            foreach ($validated['enfermedades'] as $patologiaId) {
                DB::table('crm_patologia_asociada')->insert([
                    'id_cliente_maestro' => $cliente->id_Cliente,
                    'patologia' => $patologiaId,
                    'fecha_creacion' => now(),
                    'id_operador' => 1,
                    'status' => 'ACTIVO'
                ]);
            }
        }

        $cliente->load('enfermedades');

        // Verificar si la petición viene desde la vista show
        $referer = $request->headers->get('referer');
        $isFromShow = str_contains($referer ?? '', '/clientes/') && !str_contains($referer ?? '', '/edit');

        if ($isFromShow) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'data' => $cliente
            ]);
        } else {
            $page = $request->input('page', 1);
            $clientes = Cliente::with('enfermedades')
                            ->orderBy('id_Cliente', 'desc')
                            ->paginate(20, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'data' => $cliente,
                'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
                'pagination' => (string) $clientes->links()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $cliente = Cliente::findOrFail($id);
        
        // Eliminar relaciones en tabla pivote
        DB::table('crm_patologia_asociada')
          ->where('id_cliente_maestro', $cliente->id_Cliente)
          ->delete();
        
        // Eliminar cliente (soft delete)
        $cliente->delete();

        $clientes = Cliente::with('enfermedades')
                          ->orderBy('id_Cliente', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente',
            'html' => view('clientes.partials.tabla', compact('clientes'))->render(),
            'pagination' => (string) $clientes->links()
        ]);
    }

    /**
     * Search clients for the modal de preferencias
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q', '');
        
        $clientes = Cliente::whereIn('status', ['CLIENTE', 'PROSPECTO'])
                        ->where(function($query) use ($term) {
                            $query->where('Nombre', 'LIKE', "%{$term}%")
                                  ->orWhere('apPaterno', 'LIKE', "%{$term}%")
                                  ->orWhere('apMaterno', 'LIKE', "%{$term}%")
                                  ->orWhere('email1', 'LIKE', "%{$term}%");
                        })
                        ->orderBy('Nombre')
                        ->limit(10)
                        ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'email1', 'titulo']);
        
        return response()->json([
            'success' => true,
            'data' => $clientes
        ]);
    }
}