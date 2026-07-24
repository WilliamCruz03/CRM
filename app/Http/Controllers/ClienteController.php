<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Patologia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\CatAgendaTipo;
use App\Models\Clientes\CatPais;
use App\Models\Clientes\CatEstado;
use App\Models\Clientes\CatMunicipio;
use App\Models\Clientes\CatLocalidad;
use App\Models\Clientes\ClienteContacto;
use App\Models\Interes;
use App\Models\ClienteInteres;

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
        
        // Solo obtener clientes si tiene permiso de VER
        $clientes = collect();
        if ($puedeVer) {
            // QUITAR with('patologiasAsociadas') - está en otra BD
            $clientes = Cliente::where('status', '!=', 'BLOQUEADO')
                ->orderBy('id_Cliente', 'asc')
                ->paginate($perPage);
            
            // Cargar patologías desde CRM para cada cliente
            foreach ($clientes as $cliente) {
                $patologias = DB::connection('sqlsrv')
                    ->table('crm_patologia_asociada')
                    ->where('id_cliente_maestro', $cliente->id_Cliente)
                    ->where('status', 1)
                    ->get();
                $cliente->setRelation('patologiasAsociadas', $patologias);
            }
        }

        $patologias = Patologia::all();
        
        // Cargar países SIEMPRE
        $paises = CatPais::where('status', 1)->orderBy('pais')->get();
        
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
                'pagination' => $puedeVer ? (string) $clientes->links() : ''
            ]);
        }

        return view('clientes.index', compact('clientes', 'patologias', 'permisos', 'paises'));
    }

    public function create()
    {
        $paises = CatPais::where('status', 1)->orderBy('pais')->get();
        return view('clientes.create', compact('paises'));
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
                'email1' => 'nullable|email|unique:sqlsrvM.catalogo_cliente_maestro,email1',
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
                'enfermedades.*' => 'exists:sqlsrvM.crm_cat_patologias,id_patologia',
                'contacto_id' => 'nullable|exists:cat_agenda_tipos,id_tipo',
                'intereses' => 'nullable|array',
                'intereses.*' => 'integer|exists:sqlsrvM.crm_cat_intereses,id_interes' 
            ]);

            $maxId = Cliente::max('id_Cliente') ?? 0;
            $nuevoId = $maxId + 1;

            // Convertir pais_id a null si es 0 o vacío
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

            $clienteId = $cliente->id_Cliente;

            // ============================================
            // GUARDAR PATOLOGÍAS
            // ============================================
            if (!empty($validated['enfermedades'])) {
                // OBTENER EL ID DEL CLIENTE RECIÉN CREADO
                $clienteId = $cliente->id_Cliente;
                
                foreach ($validated['enfermedades'] as $patologiaId) {
                    $patologia = Patologia::find($patologiaId);
                    if ($patologia) {
                        DB::table('crm_patologia_asociada')->insert([
                            'id_cliente_maestro' => $clienteId,
                            'patologia' => $patologia->descripcion,
                            'fecha_creacion' => now(),
                            'id_operador' => auth()->id() ?? 0,
                            'status' => 1
                        ]);
                    }
                }
            }

            // ============================================
            // GUARDAR INTERESES
            // ============================================
            if (!empty($validated['intereses'])) {
                foreach ($validated['intereses'] as $idInteres) {
                    DB::connection('sqlsrv')  // Conexión CRM
                        ->table('crm_cliente_intereses')
                        ->insert([
                            'id_cliente' => $clienteId,
                            'id_interes' => $idInteres,
                            'fecha_asignacion' => now(),
                            'activo' => 1
                        ]);
                }
            }

            // Guardar preferencia de contacto
            if (!empty($validated['contacto_id'])) {
                try {
                    ClienteContacto::create([
                        'id_cliente' => $clienteId,
                        'id_tipo_contacto' => $validated['contacto_id']
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error al guardar preferencia: ' . $e->getMessage());
                }
            }

            // Verificar si el usuario tiene permiso de VER para devolver la tabla actualizada
            $puedeVer = auth()->user()->puede('clientes', 'directorio', 'ver');
            $clientes = collect();
            if ($puedeVer) {
                $clientes = Cliente::with('patologiasAsociadas')
                            ->with(['pais', 'estado', 'municipio', 'localidad'])
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
                'data' => $cliente->load('patologiasAsociadas'),
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
        
        $cliente = Cliente::with(['pais', 'estado', 'municipio', 'localidad'])->findOrFail($id);
        
        // ============================================
        // CARGAR NOMBRE DE SUCURSAL ORIGEN
        // ============================================
        $nombreSucursal = 'CRM';
        if ($cliente->sucursal_origen != 0) {
            $sucursal = DB::connection('sqlsrvM')
                ->table('sucursales')
                ->where('id_sucursal', $cliente->sucursal_origen)
                ->first();
            $nombreSucursal = $sucursal->nombre ?? 'Sucursal ' . $cliente->sucursal_origen;
        }
        $cliente->nombre_sucursal_origen = $nombreSucursal;
        
        // Cargar patologías desde CRM
        $patologias = DB::connection('sqlsrv')
            ->table('crm_patologia_asociada')
            ->where('id_cliente_maestro', $cliente->id_Cliente)
            ->where('status', 1)
            ->get();
        $cliente->setRelation('patologiasAsociadas', $patologias);
        
        // ============================================
        // CARGAR INTERESES DEL CLIENTE
        // ============================================
        $interesesIds = DB::connection('sqlsrv')
            ->table('crm_cliente_intereses')
            ->where('id_cliente', $cliente->id_Cliente)
            ->where('activo', 1)
            ->pluck('id_interes')
            ->toArray();
        
        $interesesList = [];
        if (!empty($interesesIds)) {
            $interesesList = DB::connection('sqlsrvM')
                ->table('crm_cat_intereses')
                ->whereIn('id_interes', $interesesIds)
                ->get(['id_interes', 'Descripcion']);
        }
        $cliente->setRelation('interesesAsociados', $interesesList);
        
        $permisos = [
            'editar' => auth()->user()->puede('clientes', 'directorio', 'editar'),
            'eliminar_patologia' => auth()->user()->puede('clientes', 'directorio', 'editar'),
        ];
        
        return view('clientes.show', compact('cliente', 'permisos', 'interesesList'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        try {
            // Verificar permiso de EDITAR
            if (!auth()->user()->puede('clientes', 'directorio', 'editar')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para editar clientes'
                ], 403);
            }
            
            $cliente = Cliente::with('contactoPreferencia')->findOrFail($id);
            $patologias = Patologia::all();
            $intereses = Interes::orderBy('Descripcion')->get();
            
            // Cargar patologías asociadas
            $patologiasAsociadas = DB::connection('sqlsrv')
                ->table('crm_patologia_asociada')
                ->where('id_cliente_maestro', $cliente->id_Cliente)
                ->where('status', 1)
                ->get();
            $cliente->setRelation('patologiasAsociadas', $patologiasAsociadas);
            
            $enfermedadesIds = [];
            foreach ($cliente->patologiasAsociadas as $asociada) {
                $patologia = Patologia::where('descripcion', $asociada->patologia)->first();
                if ($patologia) {
                    $enfermedadesIds[] = $patologia->id_patologia;
                }
            }
            
            // ============================================
            // CARGAR INTERESES DEL CLIENTE
            // ============================================
            // La tabla crm_cliente_intereses está en CRM
            $interesesIds = DB::connection('sqlsrv')
                ->table('crm_cliente_intereses')
                ->where('id_cliente', $cliente->id_Cliente)
                ->where('activo', 1)
                ->pluck('id_interes')
                ->toArray();

            // Obtener los nombres de los intereses (catálogo está en Matriz)
            $interesesNombres = [];
            if (!empty($interesesIds)) {
                $interesesNombres = DB::connection('sqlsrvM')
                    ->table('crm_cat_intereses')
                    ->whereIn('id_interes', $interesesIds)
                    ->pluck('Descripcion', 'id_interes')
                    ->toArray();
            }
            
            // Obtener los nombres de las ubicaciones
            $paisNombre = null;
            $estadoNombre = null;
            $municipioNombre = null;
            $localidadNombre = null;
            
            if ($cliente->pais_id) {
                $pais = CatPais::find($cliente->pais_id);
                $paisNombre = $pais ? $pais->pais : null;
            }
            if ($cliente->estado_id) {
                $estado = CatEstado::find($cliente->estado_id);
                $estadoNombre = $estado ? $estado->nombre : null;
            }
            if ($cliente->municipio_id) {
                $municipio = CatMunicipio::find($cliente->municipio_id);
                $municipioNombre = $municipio ? $municipio->nombre : null;
            }
            if ($cliente->localidad_id) {
                $localidad = CatLocalidad::find($cliente->localidad_id);
                $localidadNombre = $localidad ? $localidad->nombre : null;
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
                    'pais_nombre' => $paisNombre,
                    'estado_nombre' => $estadoNombre,
                    'municipio_nombre' => $municipioNombre,
                    'localidad_nombre' => $localidadNombre,
                    'enfermedades' => $enfermedadesIds,
                    'contacto_id' => $cliente->contactoPreferencia ? $cliente->contactoPreferencia->id_tipo_contacto : null,
                    'intereses' => $interesesIds,
                    'intereses_nombres' => $interesesNombres
                ],
                'patologias' => $patologias,
                'intereses' => $intereses
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en edit cliente: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('clientes', 'directorio', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $cliente = Cliente::findOrFail($id);

            $validated = $request->validate([
                'Nombre' => 'required|string|max:255',
                'apPaterno' => 'required|string|max:255',
                'apMaterno' => 'nullable|string|max:255',
                'titulo' => 'nullable|string|max:20',
                'email1' => 'nullable|email|unique:sqlsrvM.catalogo_cliente_maestro,email1,' . $id . ',id_Cliente',
                'telefono1' => 'nullable|string|max:20',
                'telefono2' => 'nullable|string|max:20',
                'Domicilio' => 'nullable|string|max:500',
                'Sexo' => 'nullable|in:M,F,OTRO',
                'FechaNac' => 'nullable|date',
                'status' => 'nullable|in:CLIENTE,PROSPECTO,BLOQUEADO,INACTIVO',
                'pais_id' => 'nullable|integer',
                'estado_id' => 'nullable|integer',
                'municipio_id' => 'nullable|integer',
                'localidad_id' => 'nullable|integer',
                'enfermedades' => 'nullable|array',
                'enfermedades.*' => 'exists:sqlsrvM.crm_cat_patologias,id_patologia',
                'contacto_id' => 'nullable|exists:cat_agenda_tipos,id_tipo',
                'intereses' => 'nullable|array',
                'intereses.*' => 'integer|exists:sqlsrvM.crm_cat_intereses,id_interes'
            ]);

            // Actualizar datos básicos del cliente
            $cliente->update([
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
                'localidad_id' => $validated['localidad_id'] ?? null
            ]);

            // Actualizar enfermedades
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

            // ============================================
            // ACTUALIZAR INTERESES
            // ============================================
            // Desactivar TODOS los intereses actuales del cliente (CRM)
            DB::connection('sqlsrv')
                ->table('crm_cliente_intereses')
                ->where('id_cliente', $cliente->id_Cliente)
                ->update(['activo' => 0]);

            // Activar/Insertar los nuevos intereses
            if (!empty($validated['intereses'])) {
                foreach ($validated['intereses'] as $idInteres) {
                    $exists = DB::connection('sqlsrv')
                        ->table('crm_cliente_intereses')
                        ->where('id_cliente', $cliente->id_Cliente)
                        ->where('id_interes', $idInteres)
                        ->first();

                    if ($exists) {
                        DB::connection('sqlsrv')
                            ->table('crm_cliente_intereses')
                            ->where('id_cliente', $cliente->id_Cliente)
                            ->where('id_interes', $idInteres)
                            ->update(['activo' => 1]);
                    } else {
                        DB::connection('sqlsrv')
                            ->table('crm_cliente_intereses')
                            ->insert([
                                'id_cliente' => $cliente->id_Cliente,
                                'id_interes' => $idInteres,
                                'fecha_asignacion' => now(),
                                'activo' => 1
                            ]);
                    }
                }
            }

            // Actualizar preferencia de contacto
            if (!empty($validated['contacto_id'])) {
                ClienteContacto::updateOrCreate(
                    ['id_cliente' => $cliente->id_Cliente],
                    ['id_tipo_contacto' => $validated['contacto_id']]
                );
            } else {
                ClienteContacto::where('id_cliente', $cliente->id_Cliente)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'data' => $cliente->load('contactoPreferencia')
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar cliente: ' . $e->getMessage());
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
        
        try {
            DB::beginTransaction();
            
            $cliente = Cliente::findOrFail($id);
            
            // Actualizar status del cliente a BLOQUEADO
            $cliente->status = Cliente::STATUS_BLOQUEADO;
            $cliente->save();
            
            // Actualizar enfermedades asociadas a status = 0
            DB::connection('sqlsrv')
                ->table('crm_patologia_asociada')
                ->where('id_cliente_maestro', $cliente->id_Cliente)
                ->update(['status' => 0]);
            
            // Actualizar intereses asociados a activo = 0
            DB::connection('sqlsrv')
                ->table('crm_cliente_intereses')
                ->where('id_cliente', $cliente->id_Cliente)
                ->update(['activo' => 0]);
            
            // Eliminar preferencia de contacto
            ClienteContacto::where('id_cliente', $cliente->id_Cliente)->delete();
            
            DB::commit();

            $puedeVer = auth()->user()->puede('clientes', 'directorio', 'ver');
            $clientes = collect();
            if ($puedeVer) {
                $clientes = Cliente::with('patologiasAsociadas')
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
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
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
     * Search clients for the modal
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
                // ============================================
                // OBTENER PATOLOGÍAS DIRECTAMENTE DE LA BASE DE DATOS
                // ============================================
                $patologias = DB::connection('sqlsrv')
                    ->table('crm_patologia_asociada')
                    ->where('id_cliente_maestro', $cliente->id_Cliente)
                    ->where('status', 1)
                    ->select('id_patologia_asociada as id', 'patologia as nombre')
                    ->get();

                // ============================================
                // CARGAR INTERESES DEL CLIENTE
                // ============================================
                $interesesIds = DB::connection('sqlsrv')
                    ->table('crm_cliente_intereses')
                    ->where('id_cliente', $cliente->id_Cliente)
                    ->where('activo', 1)
                    ->pluck('id_interes')
                    ->toArray();
                
                $intereses = [];
                if (!empty($interesesIds)) {
                    $intereses = DB::connection('sqlsrvM')
                        ->table('crm_cat_intereses')
                        ->whereIn('id_interes', $interesesIds)
                        ->get(['Descripcion']);
                }
                
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
                    'patologias_asociadas' => $patologias,
                    'email1' => $cliente->email1,
                    'telefono1' => $cliente->telefono1,
                    'telefono2' => $cliente->telefono2,
                    'Domicilio' => $cliente->Domicilio,
                    'intereses' => $intereses,
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

    /**
     * Buscar intereses para el autocompletado
     */
    public function buscarIntereses(Request $request): JsonResponse
    {
        try {
            $term = $request->input('q', '');
            
            $intereses = Interes::where('Descripcion', 'LIKE', "%{$term}%")
                                ->orderBy('Descripcion')
                                ->limit(10)
                                ->get(['id_interes as id', 'Descripcion as text']);
            
            return response()->json([
                'results' => $intereses
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en buscarIntereses: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener intereses de un cliente
     */
    public function getInteresesCliente(int $id): JsonResponse
    {
        try {
            
            $interesesIds = DB::connection('sqlsrv')
                ->table('crm_cliente_intereses')
                ->where('id_cliente', $id)
                ->where('activo', 1)
                ->pluck('id_interes')
                ->toArray();
            
            
            $intereses = [];
            if (!empty($interesesIds)) {
                $intereses = DB::connection('sqlsrvM')
                    ->table('crm_cat_intereses')
                    ->whereIn('id_interes', $interesesIds)
                    ->get(['id_interes', 'Descripcion']);
            }
            
            return response()->json([
                'success' => true,
                'data' => $intereses
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getInteresesCliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar intereses a un cliente
     */
    public function asignarIntereses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:catalogo_cliente_maestro,id_Cliente',
            'id_interes' => 'required|exists:crm_cat_intereses,id_interes',
            'accion' => 'required|in:agregar,quitar'
        ]);

        $cliente = Cliente::findOrFail($validated['id_cliente']);

        if ($validated['accion'] === 'agregar') {
            // Verificar si ya existe
            $existe = ClienteInteres::where('id_cliente', $validated['id_cliente'])
                ->where('id_interes', $validated['id_interes'])
                ->where('activo', 1)
                ->exists();

            if (!$existe) {
                ClienteInteres::create([
                    'id_cliente' => $validated['id_cliente'],
                    'id_interes' => $validated['id_interes'],
                    'fecha_asignacion' => now(),
                    'activo' => true
                ]);
            }
        } else {
            // Quitar interés
            ClienteInteres::where('id_cliente', $validated['id_cliente'])
                ->where('id_interes', $validated['id_interes'])
                ->update(['activo' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => $validated['accion'] === 'agregar' 
                ? 'Interés agregado correctamente' 
                : 'Interés eliminado correctamente'
        ]);
    }

    /**
     * Obtener los tipos de contacto para el select
     */
    public function tiposContacto(): JsonResponse
    {
        
        $tipos = CatAgendaTipo::where('activo', 1)
            ->orderBy('orden')
            ->get(['id_tipo', 'nombre']);
        
        return response()->json([
            'success' => true,
            'data' => $tipos
        ]);
    }

    /**
     * Obtener lista de países activos para selects
     */
    public function getEstados($paisId, Request $request)
    {
        try {
            $query = CatEstado::where('pais_id', $paisId)->where('status', 1);
            
            if ($request->has('q') && !empty($request->q)) {
                $query->where('nombre', 'like', '%' . $request->q . '%');
            }
            
            $estados = $query->orderBy('nombre')->get(['id as value', 'nombre as text']);
            return response()->json($estados);
        } catch (\Exception $e) {
            \Log::error('Error en getEstados: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function getMunicipios($estadoId, Request $request)
    {
        try {
            $query = CatMunicipio::where('estado_id', $estadoId)->where('status', 1);
            
            if ($request->has('q') && !empty($request->q)) {
                $query->where('nombre', 'like', '%' . $request->q . '%');
            }
            
            $municipios = $query->orderBy('nombre')->get(['id as value', 'nombre as text']);
            return response()->json($municipios);
        } catch (\Exception $e) {
            \Log::error('Error en getMunicipios: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function getLocalidades($municipioId, Request $request)
    {
        try {
            $query = CatLocalidad::where('municipio_id', $municipioId)->where('status', 1);
            
            if ($request->has('q') && !empty($request->q)) {
                $query->where('nombre', 'like', '%' . $request->q . '%');
            }
            
            $localidades = $query->orderBy('nombre')->get(['id as value', 'nombre as text']);
            return response()->json($localidades);
        } catch (\Exception $e) {
            \Log::error('Error en getLocalidades: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Actualizar cliente desde el modal de cotizaciones (solo campos básicos)
     */
    public function updateFromCotizacion(Request $request, int $id): JsonResponse
    {
        try {
            $cliente = Cliente::findOrFail($id);
            
            // ============================================
            // CONSTRUIR REGLAS DE VALIDACIÓN DINÁMICAS
            // ============================================
            $rules = [];
            
            if ($request->has('Nombre')) {
                $rules['Nombre'] = 'required|string|max:255';
            }
            if ($request->has('apPaterno')) {
                $rules['apPaterno'] = 'required|string|max:255';
            }
            if ($request->has('apMaterno')) {
                $rules['apMaterno'] = 'nullable|string|max:255';
            }
            if ($request->has('email1')) {
                $rules['email1'] = 'nullable|string|max:255|email';
            }
            if ($request->has('telefono1')) {
                $rules['telefono1'] = 'nullable|string|max:20';
            }
            if ($request->has('telefono2')) {
                $rules['telefono2'] = 'nullable|string|max:20';
            }
            if ($request->has('Domicilio')) {
                $rules['Domicilio'] = 'nullable|string|max:500';
            }
            
            // Si no hay reglas, retornar error
            if (empty($rules)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se enviaron datos para actualizar'
                ], 400);
            }
            
            $validated = $request->validate($rules);
            
            // ============================================
            // ACTUALIZAR SOLO LOS CAMPOS ENVIADOS
            // ============================================
            if ($request->has('Nombre')) {
                $cliente->Nombre = $validated['Nombre'];
            }
            if ($request->has('apPaterno')) {
                $cliente->apPaterno = $validated['apPaterno'];
            }
            if ($request->has('apMaterno')) {
                $cliente->apMaterno = $validated['apMaterno'] ?? null;
            }
            if ($request->has('email1')) {
                $cliente->email1 = $validated['email1'] ?? null;
            }
            if ($request->has('telefono1')) {
                $cliente->telefono1 = $validated['telefono1'] ?? null;
            }
            if ($request->has('telefono2')) {
                $cliente->telefono2 = $validated['telefono2'] ?? null;
            }
            if ($request->has('Domicilio')) {
                $cliente->Domicilio = $validated['Domicilio'] ?? null;
            }
            
            $cliente->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'data' => $cliente
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar cliente desde cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getClienteData(int $id): JsonResponse
    {
        try {
            $cliente = Cliente::findOrFail($id);
            
            // Obtener patologías
            $patologias = DB::connection('sqlsrv')
                ->table('crm_patologia_asociada')
                ->where('id_cliente_maestro', $cliente->id_Cliente)
                ->where('status', 1)
                ->select('id_patologia_asociada as id', 'patologia as nombre')
                ->get();
            
            // Obtener intereses
            $interesesIds = DB::connection('sqlsrv')
                ->table('crm_cliente_intereses')
                ->where('id_cliente', $cliente->id_Cliente)
                ->where('activo', 1)
                ->pluck('id_interes')
                ->toArray();
            
            $intereses = [];
            if (!empty($interesesIds)) {
                $intereses = DB::connection('sqlsrvM')
                    ->table('crm_cat_intereses')
                    ->whereIn('id_interes', $interesesIds)
                    ->get(['Descripcion']);
            }
            
            // Construir HTML de intereses
            $interesesHtml = '';
            if ($intereses && $intereses->count() > 0) {
                $interesesHtml = $intereses->slice(0, 3)->map(function($i) {
                    return '<span class="badge bg-primary">' . e($i->Descripcion) . '</span>';
                })->implode(' ');
                if ($intereses->count() > 3) {
                    $interesesHtml .= ' <span class="badge bg-secondary">+' . ($intereses->count() - 3) . '</span>';
                }
            }
            
            // Construir HTML de patologías
            $patologiasHtml = '';
            if ($patologias && $patologias->count() > 0) {
                $patologiasHtml = $patologias->slice(0, 3)->map(function($p) {
                    return '<span class="badge bg-info">' . e($p->nombre) . '</span>';
                })->implode(' ');
                if ($patologias->count() > 3) {
                    $patologiasHtml .= ' <span class="badge bg-secondary">+' . ($patologias->count() - 3) . '</span>';
                }
            }
            
            // Antes de return
\Log::info('getClienteData - intereses_html para cliente ' . $cliente->id_Cliente . ': ' . $interesesHtml);
\Log::info('getClienteData - patologias_html para cliente ' . $cliente->id_Cliente . ': ' . $patologiasHtml);
            return response()->json([
                'success' => true,
                'data' => [
                    'id_Cliente' => $cliente->id_Cliente,
                    'Nombre' => $cliente->Nombre,
                    'apPaterno' => $cliente->apPaterno,
                    'apMaterno' => $cliente->apMaterno,
                    'titulo' => $cliente->titulo ?? '',
                    'email1' => $cliente->email1,
                    'telefono1' => $cliente->telefono1,
                    'telefono2' => $cliente->telefono2,
                    'Domicilio' => $cliente->Domicilio,
                    'localidad_nombre' => $cliente->localidad_nombre,
                    'intereses_html' => $interesesHtml,
                    'patologias_html' => $patologiasHtml
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }
}