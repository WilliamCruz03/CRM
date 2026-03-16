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
        $perPage = 20; // Ajustar segun necesidades de paginación

        $clientes = Cliente::with('patologiasAsociadas')
                        ->orderBy('id_Cliente', 'asc')
                        ->paginate($perPage);

        $patologias = Patologia::all();

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
    try {
        $validated = $request->validate([
            'Nombre' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apPaterno' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apMaterno' => 'nullable|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'titulo' => 'nullable|string|max:20',
            'email1' => 'nullable|email|unique:catalogo_cliente_maestro,email1',
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

        // Log de los datos validados
        \Log::info('Datos validados en store:', $validated);

        // Verificar que todos los campos existen en el modelo
        $fillable = (new Cliente())->getFillable();
        \Log::info('Campos fillable:', $fillable);

        // Crear el cliente
        $cliente = Cliente::create([
            'sucursal_origen' => 0,
            'Nombre' => $validated['Nombre'],
            'apPaterno' => $validated['apPaterno'],
            'apMaterno' => $validated['apMaterno'] ?? null,
            'titulo' => $validated['titulo'] ?? null,
            'email1' => $validated['email1'] ?? null,
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
            'id_operador' => 1,
            'fecha_creacion' => now()
        ]);

        \Log::info('Cliente creado con ID: ' . $cliente->id_Cliente);

        // Sincronizar enfermedades
        if (!empty($validated['enfermedades'])) {
            foreach ($validated['enfermedades'] as $patologiaId) {
                $patologia = Patologia::find($patologiaId);
                if ($patologia) {
                    DB::table('crm_patologia_asociada')->insert([
                        'id_cliente_maestro' => $cliente->id_Cliente,
                        'patologia' => $patologia->descripcion,
                        'fecha_creacion' => now(),
                        'id_operador' => 0,
                        'status' => 1 // 1 = ACTIVO, 0 = INACTIVO
                    ]);
                }
            }
        }

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

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Error de validación:', $e->errors());
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Error al crear cliente: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error interno: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString() // Solo para desarrollo, quitar en producción
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $cliente = Cliente::with('patologiasAsociadas')->findOrFail($id);
        
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $cliente = Cliente::with('patologiasAsociadas')->findOrFail($id);
        $patologias = Patologia::all();
        
        // Obtener SOLO los IDs de las patologías del cliente
        $enfermedadesIds = [];
        foreach ($cliente->patologiasAsociadas as $asociada) {
            $patologia = Patologia::where('descripcion', $asociada->patologia)->first();
            if ($patologia) {
                $enfermedadesIds[] = $patologia->id_patologia;
            }
        }
        
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
                'enfermedades' => $enfermedadesIds // IDs para el frontend
            ],
            'patologias' => $patologias
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
{
    try {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'Nombre' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apPaterno' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apMaterno' => 'nullable|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'titulo' => 'nullable|string|max:20',
            'email1' => 'nullable|email|unique:catalogo_cliente_maestro,email1,' . $id . ',id_Cliente',
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

        // Actualizar datos del cliente
        $cliente->update($validated);

        // ELIMINAR todas las relaciones existentes
        DB::table('crm_patologia_asociada')
          ->where('id_cliente_maestro', $cliente->id_Cliente)
          ->delete();

        // INSERTAR las nuevas relaciones
        if (!empty($validated['enfermedades'])) {
            foreach ($validated['enfermedades'] as $patologiaId) {
                $patologia = Patologia::find($patologiaId);
                if ($patologia) {
                    DB::table('crm_patologia_asociada')->insert([
                        'id_cliente_maestro' => $cliente->id_Cliente,
                        'patologia' => $patologia->descripcion,
                        'fecha_creacion' => now(),
                        'id_operador' => 1,
                        'status' => 1 // 1 = ACTIVO, 0 = INACTIVO
                    ]);
                }
            }
        }

        $cliente->load('enfermedades');

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
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        // Esto mostrará el error real en la respuesta
        return response()->json([
            'success' => false,
            'message' => 'Error interno: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
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

    public function eliminarPatologia(Request $request, int $clienteId): JsonResponse
    {
        $patologiaDescripcion = $request->input('patologia');
        
        DB::table('crm_patologia_asociada')
        ->where('id_cliente_maestro', $clienteId)
        ->where('patologia', 'LIKE', trim($patologiaDescripcion))
        ->delete();
        
        return response()->json(['success' => true]);
    }
        /**
     * Search clients for the modal de preferencias
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->input('q', '');
            $terminos = explode(' ', $term);

            // QUITAR EL whereIn para incluir TODOS los status
            $clientes = Cliente::where(function($query) use ($term, $terminos) {
                                // Búsqueda en campos individuales
                                $query->where('id_Cliente', 'LIKE', "%{$term}%")
                                    ->orWhere('Nombre', 'LIKE', "%{$term}%")
                                    ->orWhere('apPaterno', 'LIKE', "%{$term}%")
                                    ->orWhere('apMaterno', 'LIKE', "%{$term}%")
                                    ->orWhere('email1', 'LIKE', "%{$term}%")
                                    ->orWhere('telefono1', 'LIKE', "%{$term}%");
                                
                                // Búsqueda por nombre completo combinado
                                $query->orWhereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$term}%"]);
                                $query->orWhereRaw("CONCAT(Nombre, ' ', apPaterno) LIKE ?", ["%{$term}%"]);
                                
                                // Si hay múltiples palabras, intenta combinaciones
                                if (count($terminos) >= 2) {
                                    $query->orWhere(function($q) use ($terminos) {
                                        $q->where('Nombre', 'LIKE', "%{$terminos[0]}%")
                                        ->where('apPaterno', 'LIKE', "%{$terminos[1]}%");
                                        
                                        if (isset($terminos[2])) {
                                            $q->where('apMaterno', 'LIKE', "%{$terminos[2]}%");
                                        }
                                    });
                                }
                            })
                            ->orderByRaw("CASE 
                                WHEN status = 'CLIENTE' THEN 1 
                                WHEN status = 'PROSPECTO' THEN 2 
                                WHEN status = 'BLOQUEADO' THEN 3 
                                ELSE 4 END")
                            ->orderBy('Nombre')
                            ->limit(20)
                            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'email1', 'telefono1', 'titulo', 'status']);

            return response()->json([
                'success' => true, 
                'data' => $clientes
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en búsqueda: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => 'Error al buscar clientes'
            ], 500);
        }
    }

    public function eliminarPatologiaPorId(int $clienteId, int $patologiaAsociadaId): JsonResponse
    {
        DB::table('crm_patologia_asociada')
        ->where('id_cliente_maestro', $clienteId)
        ->where('id_patologia_asociada', $patologiaAsociadaId)
        ->delete();
        
        return response()->json(['success' => true]);
    }

}