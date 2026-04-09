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
        // Obtener permisos del usuario para el submódulo 'directorio'
        $puedeVer = auth()->user()->puede('clientes', 'directorio', 'ver');
        $puedeCrear = auth()->user()->puede('clientes', 'directorio', 'crear');
        $puedeEditar = auth()->user()->puede('clientes', 'directorio', 'editar');
        $puedeEliminar = auth()->user()->puede('clientes', 'directorio', 'eliminar');
        
        // Si no tiene ningún permiso, mostrar error 403
        if (!$puedeVer && !$puedeCrear && !$puedeEditar && !$puedeEliminar) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        $perPage = 20;
        $statusFilter = $request->input('status', 'CLIENTE'); // Por defecto mostrar solo CLIENTES
        
        // Solo obtener clientes si tiene permiso de VER
        $clientes = collect(); // Colección vacía por defecto
        if ($puedeVer) {
            $query = Cliente::with('patologiasAsociadas');
            
            // Filtrar por estado
            switch ($statusFilter) {
                case 'CLIENTE':
                    $query->where('status', 'CLIENTE');
                    break;
                case 'PROSPECTO':
                    $query->where('status', 'PROSPECTO');
                    break;
                case 'INACTIVO':
                    $query->where('status', 'INACTIVO');
                    break;
                case 'BLOQUEADO':
                    $query->where('status', 'BLOQUEADO');
                    break;
                case 'TODOS':
                    // No filtrar por status
                    break;
                default:
                    $query->where('status', 'CLIENTE');
                    break;
            }
            
            $clientes = $query->orderBy('id_Cliente', 'asc')->paginate($perPage);
        }

        $patologias = Patologia::all();
        
        $permisos = [
            'ver' => $puedeVer,
            'crear' => $puedeCrear,
            'editar' => $puedeEditar,
            'eliminar' => $puedeEliminar,
            'mostrar' => $puedeVer || $puedeCrear || $puedeEditar || $puedeEliminar,
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('clientes.partials.tabla', compact('clientes', 'permisos'))->render(),
                'pagination' => $puedeVer ? (string) $clientes->links() : '',
                'statusFilter' => $statusFilter
            ]);
        }

        return view('clientes.index', compact('clientes', 'patologias', 'permisos', 'statusFilter'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Verificar permiso de CREAR
        if (!auth()->user()->puede('clientes', 'directorio', 'crear')) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permiso para crear clientes'
            ], 403);
        }
        
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
                'status' => 'nullable|in:CLIENTE,PROSPECTO,BLOQUEADO,INACTIVO',
                'pais_id' => 'nullable|integer',
                'estado_id' => 'nullable|integer',
                'municipio_id' => 'nullable|integer',
                'localidad_id' => 'nullable|integer',
                'enfermedades' => 'nullable|array',
                'enfermedades.*' => 'exists:crm_cat_patologias,id_patologia'
            ]);

            $maxId = Cliente::max('id_Cliente') ?? 0;
            $nuevoId = $maxId + 1;

            // CORRECCIÓN: Convertir pais_id a null si es 0 o vacío
            $paisId = null;
            if (!empty($validated['pais_id']) && $validated['pais_id'] != 0) {
                $paisId = $validated['pais_id'];
            }

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
                'pais_id' => $paisId,
                'estado_id' => $validated['estado_id'] ?? null,
                'municipio_id' => $validated['municipio_id'] ?? null,
                'localidad_id' => $validated['localidad_id'] ?? null,
                'id_operador' => auth()->id() ?? 1,
                'fecha_creacion' => now()
            ]);

            // LOG para depurar
            \Log::info('Cliente creado con ID: ' . $cliente->id_Cliente);

            if (!empty($validated['enfermedades'])) {
                foreach ($validated['enfermedades'] as $patologiaId) {
                    $patologia = Patologia::find($patologiaId);
                    if ($patologia) {
                        DB::table('crm_patologia_asociada')->insert([
                            'id_cliente_maestro' => $cliente->id_Cliente,
                            'patologia' => $patologia->descripcion,
                            'fecha_creacion' => now(),
                            'id_operador' => auth()->id() ?? 0,
                            'status' => 1
                        ]);
                    }
                }
            }

            // Verificar si el usuario tiene permiso de VER para devolver la tabla actualizada
            $puedeVer = auth()->user()->puede('clientes', 'directorio', 'ver');
            $clientes = collect();
            if ($puedeVer) {
                $clientes = Cliente::with('enfermedades')
                            ->orderBy('id_Cliente', 'desc')
                            ->paginate(20);
            }

            $permisos = [
                'ver' => $puedeVer,
                'editar' => auth()->user()->puede('clientes', 'directorio', 'editar'),
                'eliminar' => auth()->user()->puede('clientes', 'directorio', 'eliminar'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado correctamente',
                'data' => $cliente->load('enfermedades'),
                'html' => $puedeVer ? view('clientes.partials.tabla', compact('clientes', 'permisos'))->render() : '',
                'pagination' => $puedeVer ? (string) $clientes->links() : ''
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        // Verificar permiso de VER (para acceder a la vista)
        if (!auth()->user()->puede('clientes', 'directorio', 'ver')) {
            abort(403, 'No tienes permiso para ver los detalles del cliente');
        }
        
        $cliente = Cliente::with('patologiasAsociadas')->findOrFail($id);
        
        $permisos = [
            'editar' => auth()->user()->puede('clientes', 'directorio', 'editar'),
            'eliminar_patologia' => auth()->user()->puede('clientes', 'directorio', 'editar'),
        ];
        
        return view('clientes.show', compact('cliente', 'permisos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        // Verificar permiso de EDITAR
        if (!auth()->user()->puede('clientes', 'directorio', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar clientes'
            ], 403);
        }
        
        $cliente = Cliente::with('patologiasAsociadas')->findOrFail($id);
        $patologias = Patologia::all();
        
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
                'enfermedades' => $enfermedadesIds
            ],
            'patologias' => $patologias
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Verificar permiso de EDITAR
        if (!auth()->user()->puede('clientes', 'directorio', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar clientes'
            ], 403);
        }
        
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
                'status' => 'nullable|in:CLIENTE,PROSPECTO,BLOQUEADO,INACTIVO',
                'pais_id' => 'nullable|integer',
                'estado_id' => 'nullable|integer',
                'municipio_id' => 'nullable|integer',
                'localidad_id' => 'nullable|integer',
                'enfermedades' => 'nullable|array',
                'enfermedades.*' => 'exists:crm_cat_patologias,id_patologia'
            ]);

            $cliente->update($validated);

            DB::table('crm_patologia_asociada')
                ->where('id_cliente_maestro', $cliente->id_Cliente)
                ->delete();

            if (!empty($validated['enfermedades'])) {
                foreach ($validated['enfermedades'] as $patologiaId) {
                    $patologia = Patologia::find($patologiaId);
                    if ($patologia) {
                        DB::table('crm_patologia_asociada')->insert([
                            'id_cliente_maestro' => $cliente->id_Cliente,
                            'patologia' => $patologia->descripcion,
                            'fecha_creacion' => now(),
                            'id_operador' => auth()->id() ?? 1,
                            'status' => 1
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
                $puedeVer = auth()->user()->puede('clientes', 'directorio', 'ver');
                $clientes = collect();
                if ($puedeVer) {
                    $page = $request->input('page', 1);
                    $clientes = Cliente::with('enfermedades')
                                    ->orderBy('id_Cliente', 'desc')
                                    ->paginate(20, ['*'], 'page', $page);
                }

                $permisos = [
                    'ver' => $puedeVer,
                    'editar' => auth()->user()->puede('clientes', 'directorio', 'editar'),
                    'eliminar' => auth()->user()->puede('clientes', 'directorio', 'eliminar'),
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Cliente actualizado correctamente',
                    'data' => $cliente,
                    'html' => $puedeVer ? view('clientes.partials.tabla', compact('clientes', 'permisos'))->render() : '',
                    'pagination' => $puedeVer ? (string) $clientes->links() : ''
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        // Verificar permiso de ELIMINAR
        if (!auth()->user()->puede('clientes', 'directorio', 'eliminar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar clientes'
            ], 403);
        }
        
        $cliente = Cliente::findOrFail($id);
        
        DB::table('crm_patologia_asociada')
          ->where('id_cliente_maestro', $cliente->id_Cliente)
          ->delete();
        
        $cliente->delete();

        $puedeVer = auth()->user()->puede('clientes', 'directorio', 'ver');
        $clientes = collect();
        if ($puedeVer) {
            $clientes = Cliente::with('enfermedades')
                          ->orderBy('id_Cliente', 'desc')
                          ->paginate(20);
        }

        $permisos = [
            'ver' => $puedeVer,
            'editar' => auth()->user()->puede('clientes', 'directorio', 'editar'),
            'eliminar' => auth()->user()->puede('clientes', 'directorio', 'eliminar'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente',
            'html' => $puedeVer ? view('clientes.partials.tabla', compact('clientes', 'permisos'))->render() : '',
            'pagination' => $puedeVer ? (string) $clientes->links() : ''
        ]);
    }

    public function eliminarPatologia(Request $request, int $clienteId): JsonResponse
    {
        // Verificar permiso de EDITAR (modificar enfermedades)
        if (!auth()->user()->puede('clientes', 'directorio', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar enfermedades del cliente'
            ], 403);
        }
        
        $patologiaDescripcion = $request->input('patologia');
        
        DB::table('crm_patologia_asociada')
        ->where('id_cliente_maestro', $clienteId)
        ->where('patologia', 'LIKE', trim($patologiaDescripcion))
        ->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Bloquear o desbloquear un cliente
     */
    public function toggleBlock(int $id): JsonResponse
    {
        // Verificar permiso de EDITAR (para modificar estado del cliente)
        if (!auth()->user()->puede('clientes', 'directorio', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar el estado del cliente'
            ], 403);
        }
        
        try {
            $cliente = Cliente::findOrFail($id);
            
            // Cambiar estado
            $nuevoEstado = $cliente->status === 'BLOQUEADO' ? 'PROSPECTO' : 'BLOQUEADO';
            $cliente->status = $nuevoEstado;
            $cliente->save();
            
            $mensaje = $nuevoEstado === 'BLOQUEADO' 
                ? "Cliente \"{$cliente->nombre_completo}\" bloqueado correctamente"
                : "Cliente \"{$cliente->nombre_completo}\" desbloqueado correctamente";
            
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'status' => $nuevoEstado
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cambiar estado del cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del cliente'
            ], 500);
        }
    }
    
    /**
     * Search clients for the modal de preferencias
     */
    public function search(Request $request): JsonResponse
    {
        if (!auth()->user()->puede('clientes', 'directorio', 'ver')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para buscar clientes'
            ], 403);
        }
        
        try {
            $term = $request->input('q', '');

            // Excluir clientes BLOQUEADOS e INACTIVOS de la búsqueda
            $clientes = Cliente::with('patologiasAsociadas')
                            ->whereNotIn('status', ['BLOQUEADO', 'INACTIVO']) // Excluir ambos
                            ->where(function($query) use ($term) {
                                $query->where('id_Cliente', 'LIKE', "%{$term}%")
                                    ->orWhere('Nombre', 'LIKE', "%{$term}%")
                                    ->orWhere('apPaterno', 'LIKE', "%{$term}%")
                                    ->orWhere('apMaterno', 'LIKE', "%{$term}%")
                                    ->orWhere('titulo', 'LIKE', "%{$term}%")
                                    ->orWhere('email1', 'LIKE', "%{$term}%")
                                    ->orWhere('telefono1', 'LIKE', "%{$term}%")
                                    ->orWhere('telefono2', 'LIKE', "%{$term}%")
                                    ->orWhereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$term}%"]);
                            })
                            ->orderBy('Nombre')
                            ->limit(20)
                            ->get();

            $data = $clientes->map(function($cliente) {
                // CONTACTO: orden prioridad: telefono1, telefono2, email1
                $contactoHtml = '';
                if ($cliente->telefono1) {
                    $contactoHtml .= "<i class='bi bi-telephone'></i> {$cliente->telefono1}<br>";
                }
                if ($cliente->telefono2) {
                    $contactoHtml .= "<i class='bi bi-telephone'></i> {$cliente->telefono2} (secundario)<br>";
                }
                if ($cliente->email1) {
                    $contactoHtml .= "<i class='bi bi-envelope'></i> {$cliente->email1}";
                }
                
                // Si no hay contacto, mostrar mensaje
                if (empty($contactoHtml)) {
                    $contactoHtml = '<span class="text-muted">Sin contacto</span>';
                }
                
                return [
                    'id_Cliente' => $cliente->id_Cliente,
                    'Nombre' => $cliente->Nombre,
                    'apPaterno' => $cliente->apPaterno,
                    'apMaterno' => $cliente->apMaterno,
                    'titulo' => $cliente->titulo,
                    'nombre_completo' => $cliente->nombre_completo,
                    'contacto_html' => $contactoHtml,
                    'status' => $cliente->status,
                    'patologias_asociadas' => $cliente->patologiasAsociadas,
                    'email1' => $cliente->email1,
                    'telefono1' => $cliente->telefono1,
                    'telefono2' => $cliente->telefono2,
                    'Domicilio' => $cliente->Domicilio
                ];
            });

            return response()->json([
                'success' => true, 
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en búsqueda de clientes: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => 'Error al buscar clientes'
            ], 500);
        }
    }
}