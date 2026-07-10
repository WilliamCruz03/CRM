<?php

namespace App\Http\Controllers;

use App\Models\PersonalEmpresa;
use App\Models\DashboardPreferencia;
use App\Models\PermisoGranular;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $puedeVer = auth()->user()->puede('seguridad', 'usuarios', 'ver');
        $puedeCrear = auth()->user()->puede('seguridad', 'usuarios', 'crear');
        
        if (!$puedeVer && !$puedeCrear) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        $hoy = now()->format('Y-m-d');
        
        // Excluir usuarios que tienen horario para hoy
        $usuarios = PersonalEmpresa::where('Activo', 1)
            ->whereNotExists(function($query) use ($hoy) {
                $query->select(DB::raw(1))
                    ->from('rh_personal_servicios_domicilio')
                    ->whereRaw('id_personal = personal_empresa.id_personal_empresa')
                    ->where('fecha', $hoy);
            })
            ->orderBy('id_personal_empresa', 'asc')
            ->paginate(15);
        
        $permisos = [
            'ver' => $puedeVer,
            'crear' => $puedeCrear,
            'editar' => auth()->user()->puede('seguridad', 'usuarios', 'editar'),
            'eliminar' => auth()->user()->puede('seguridad', 'usuarios', 'eliminar'),
        ];
        
        return view('seguridad.usuarios.index', compact('usuarios', 'permisos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Nombre' => 'required|string|max:50',
            'ApPaterno' => 'nullable|string|max:50',
            'ApMaterno' => 'nullable|string|max:50',
            'Direccion' => 'nullable|string|max:100',
            'Localidad' => 'nullable|string|max:80',
            'Municipio' => 'nullable|string|max:60',
            'TelefonoFijo' => 'nullable|string|max:50',
            'TelefonoMovil' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'parentescoDeContacto' => 'nullable|string|max:50',
            'TelefonoContacto' => 'nullable|string|max:50',
            'fecha_ingreso' => 'nullable|date',
            'fecha_alta_sistema' => 'nullable|date',
            'fecha_alta_seguro' => 'nullable|date',
            'Activo' => 'nullable|boolean',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:254',
            'sucursal_origen' => 'nullable|integer',
            'sucursal_asignada' => 'nullable|integer',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'usuario' => 'required|string|max:15|unique:sqlsrvM.personal_empresa,usuario',
            'password' => 'nullable|string|max:30',
            'passw' => 'required|string|min:3',
            'dashboard_cards' => 'nullable|array',
            'dashboard_cards.*' => 'string|in:kpi_total_clientes,kpi_contactos_proximos,kpi_total_cotizaciones,kpi_cotizaciones_pendientes,kpi_monto_total_mes,grafico_estados_cotizaciones,tabla_ultimos_contactos,tabla_ultimas_cotizaciones,resumen_rapido,resumen_ventas_mensual',
            'permisos_modulos' => 'nullable|array',
        ]);

        // Valores por defecto
        $validated['sucursal_origen'] = $validated['sucursal_origen'] ?? 0;
        // Si no se envía sucursal_asignada o viene vacío, se asigna 0 (CRM)
        $validated['sucursal_asignada'] = ($validated['sucursal_asignada'] ?? 0) ?: 0;
        $validated['Activo'] = $validated['Activo'] ?? 1;

        DB::beginTransaction();
        
        try {
            $usuario = PersonalEmpresa::create($validated);
            
            // Guardar preferencias del dashboard (solo cards no acceso)
            if (isset($validated['dashboard_cards']) && !empty($validated['dashboard_cards'])) {
                $orden = 1;
                foreach ($validated['dashboard_cards'] as $cardKey) {
                    DashboardPreferencia::create([
                        'id_personal_empresa' => $usuario->id_personal_empresa,
                        'card_key' => $cardKey,
                        'mostrar' => true,
                        'orden' => $orden++,
                    ]);
                }
            }
            
            // Guardar permisos granulares si se enviaron
            if (isset($validated['permisos_modulos']) && !empty($validated['permisos_modulos'])) {
                $this->guardarPermisos($usuario->id_personal_empresa, $validated['permisos_modulos']);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => $usuario
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método auxiliar para guardar permisos
    private function guardarPermisos($idPersonalEmpresa, $permisosModulos)
    {
        foreach ($permisosModulos as $modulo => $submodulos) {
            foreach ($submodulos as $submodulo => $acciones) {
                PermisoGranular::updateOrCreate(
                    [
                        'id_personal_empresa' => $idPersonalEmpresa,
                        'modulo' => $modulo,
                        'submodulo' => $submodulo,
                    ],
                    [
                        'mostrar' => $acciones['mostrar'] ?? false,
                        'ver' => $acciones['ver'] ?? false,
                        'crear' => $acciones['crear'] ?? false,
                        'editar' => $acciones['editar'] ?? false,
                        'eliminar' => $acciones['eliminar'] ?? false,
                    ]
                );
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        try {
            
            $usuario = PersonalEmpresa::findOrFail($id);
            
            // Cargar sucursales activas para el select
            $sucursales = Sucursal::where('activo', 1)->get(['id_sucursal', 'nombre']);
            
            // Intentar obtener dashboard cards
            try {
                $dashboardCards = DashboardPreferencia::where('id_personal_empresa', $id)
                    ->where('mostrar', true)
                    ->orderBy('orden')
                    ->pluck('card_key')
                    ->toArray();
            } catch (\Exception $e) {
                $dashboardCards = [];
            }
            
            $permisos = $usuario->permisos_formateados;
            
            $usuario->makeHidden(['password', 'passw']);
            
            return response()->json([
                'success' => true,
                'data' => $usuario,
                'permisos' => $permisos,
                'dashboard_cards' => $dashboardCards,
                'sucursales' => $sucursales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $usuario = PersonalEmpresa::findOrFail($id);

        $validated = $request->validate([
            'Nombre' => 'required|string|max:50',
            'ApPaterno' => 'nullable|string|max:50',
            'ApMaterno' => 'nullable|string|max:50',
            'Direccion' => 'nullable|string|max:100',
            'Localidad' => 'nullable|string|max:80',
            'Municipio' => 'nullable|string|max:60',
            'TelefonoFijo' => 'nullable|string|max:50',
            'TelefonoMovil' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'parentescoDeContacto' => 'nullable|string|max:50',
            'TelefonoContacto' => 'nullable|string|max:50',
            'fecha_ingreso' => 'nullable|date',
            'fecha_alta_sistema' => 'nullable|date',
            'fecha_alta_seguro' => 'nullable|date',
            'Activo' => 'nullable|boolean',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:254',
            'sucursal_origen' => 'nullable|integer',
            'sucursal_asignada' => 'nullable|integer',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'usuario' => 'required|string|max:15|unique:sqlsrvM.personal_empresa,usuario,' . $id . ',id_personal_empresa',
            'password' => 'nullable|string|max:30',
            'passw' => 'nullable|string|min:3',
            'dashboard_cards' => 'nullable|array',
            'dashboard_cards.*' => 'string|in:kpi_total_clientes,kpi_contactos_proximos,kpi_total_cotizaciones,kpi_cotizaciones_pendientes,kpi_monto_total_mes,grafico_estados_cotizaciones,tabla_ultimos_contactos,tabla_ultimas_cotizaciones,resumen_rapido,resumen_ventas_mensual',
            'permisos_modulos' => 'nullable|array',
        ]);

        // Preparar datos para actualizar
        $datosActualizar = [
            'Nombre' => $validated['Nombre'],
            'ApPaterno' => $validated['ApPaterno'],
            'ApMaterno' => $validated['ApMaterno'] ?? null,
            'Direccion' => $validated['Direccion'] ?? null,
            'Localidad' => $validated['Localidad'] ?? null,
            'Municipio' => $validated['Municipio'] ?? null,
            'TelefonoFijo' => $validated['TelefonoFijo'] ?? null,
            'TelefonoMovil' => $validated['TelefonoMovil'] ?? null,
            'contacto' => $validated['contacto'] ?? null,
            'parentescoDeContacto' => $validated['parentescoDeContacto'] ?? null,
            'TelefonoContacto' => $validated['TelefonoContacto'] ?? null,
            'fecha_ingreso' => $validated['fecha_ingreso'] ?? null,
            'fecha_alta_sistema' => $validated['fecha_alta_sistema'] ?? null,
            'fecha_alta_seguro' => $validated['fecha_alta_seguro'] ?? null,
            'Activo' => $validated['Activo'] ?? $usuario->Activo,
            'fecha_baja' => $validated['fecha_baja'] ?? null,
            'motivo_baja' => $validated['motivo_baja'] ?? null,
            'sucursal_origen' => $validated['sucursal_origen'] ?? $usuario->sucursal_origen,
            'sucursal_asignada' => ($validated['sucursal_asignada'] ?? 0) ?: 0,
            'curp' => $validated['curp'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'usuario' => $validated['usuario'],
        ];

        // Si se envió nueva contraseña
        if (!empty($validated['passw'])) {
            $datosActualizar['passw'] = $validated['passw'];
        }

        // ============================================
        // VALIDACIÓN: Si sucursal_asignada != 0, eliminar permiso de editar pedidos
        // ============================================
        $sucursalAsignada = ($validated['sucursal_asignada'] ?? 0) ?: 0;
        $permisosModulos = $request->input('permisos_modulos', []);
        
        if ($sucursalAsignada != 0) {
            // Eliminar el permiso de editar pedidos si existe en ventas.pedidos_anticipo.editar
            if (isset($permisosModulos['ventas']['pedidos_anticipo']['editar'])) {
                unset($permisosModulos['ventas']['pedidos_anticipo']['editar']);
            }
            // También eliminar si está en ventas.pedidos.editar (por si acaso)
            if (isset($permisosModulos['ventas']['pedidos']['editar'])) {
                unset($permisosModulos['ventas']['pedidos']['editar']);
            }
        }

        DB::beginTransaction();
        
        try {
            // Actualizar usuario
            $usuario->update($datosActualizar);
            
            // ============================================
            // ACTUALIZAR PREFERENCIAS DEL DASHBOARD
            // ============================================
            $cardsNoAcceso = $validated['dashboard_cards'] ?? [];

            // Obtener cards existentes como modelos Eloquent (usar first() o get() pero asegurar que sean modelos)
            $cardsExistentes = DashboardPreferencia::where('id_personal_empresa', $usuario->id_personal_empresa)->get();

            // Actualizar cards existentes - usar update directo
            foreach ($cardsExistentes as $cardExistente) {
                $nuevoMostrar = in_array($cardExistente->card_key, $cardsNoAcceso);
                if ($cardExistente->mostrar != $nuevoMostrar) {
                    DashboardPreferencia::where('id_dashboard_preferencia', $cardExistente->id_dashboard_preferencia)
                        ->update(['mostrar' => $nuevoMostrar]);
                }
            }

            // Crear nuevos cards que no existían
            $keysExistentes = DashboardPreferencia::where('id_personal_empresa', $usuario->id_personal_empresa)
                ->pluck('card_key')
                ->toArray();
                
            $ordenActual = DashboardPreferencia::where('id_personal_empresa', $usuario->id_personal_empresa)
                ->max('orden') + 1;

            foreach ($cardsNoAcceso as $cardKey) {
                if (!in_array($cardKey, $keysExistentes)) {
                    DashboardPreferencia::create([
                        'id_personal_empresa' => $usuario->id_personal_empresa,
                        'card_key' => $cardKey,
                        'mostrar' => true,
                        'orden' => $ordenActual++,
                    ]);
                }
            }
            
            // ============================================
            // ACTUALIZAR PERMISOS GRANULARES
            // ============================================
            if ($request->has('permisos_modulos')) {
                $usuario->sincronizarPermisos($permisosModulos);
                $usuario->validarYCorregirPermisos();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $usuario
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método auxiliar para actualizar permisos (sin eliminar)
    private function actualizarPermisos($idPersonalEmpresa, $permisosModulos)
    {
        foreach ($permisosModulos as $modulo => $submodulos) {
            foreach ($submodulos as $submodulo => $acciones) {
                // Buscar si existe el permiso
                $permisoExistente = PermisoGranular::where('id_personal_empresa', $idPersonalEmpresa)
                    ->where('modulo', $modulo)
                    ->where('submodulo', $submodulo)
                    ->first();
                
                $datosPermiso = [
                    'mostrar' => $acciones['mostrar'] ?? false,
                    'ver' => $acciones['ver'] ?? false,
                    'crear' => $acciones['crear'] ?? false,
                    'editar' => $acciones['editar'] ?? false,
                    'eliminar' => $acciones['eliminar'] ?? false,
                    'updated_at' => now(),
                ];
                
                if ($permisoExistente) {
                    // Actualizar existente
                    $permisoExistente->update($datosPermiso);
                } else {
                    // Crear nuevo
                    $datosPermiso['id_personal_empresa'] = $idPersonalEmpresa;
                    $datosPermiso['modulo'] = $modulo;
                    $datosPermiso['submodulo'] = $submodulo;
                    $datosPermiso['created_at'] = now();
                    PermisoGranular::create($datosPermiso);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $usuario = PersonalEmpresa::findOrFail($id);
            
            // Evitar eliminar al propio usuario
            if (auth()->id() == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario'
                ], 403);
            }
            
            // Eliminar permisos asociados primero
            $usuario->permisosGranulares()->delete();
            
            // Eliminar usuario
            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario'
            ], 500);
        }
    }

    /**
     * Buscar usuarios por nombre, usuario o email (para el buscador)
     */
    public function buscarUsuarios(Request $request): JsonResponse
    {
        try {
            $termino = $request->input('q', '');
            
            if (empty($termino) || strlen($termino) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $termino = trim($termino);
            
            // Buscar usuarios usando CONCAT para nombre completo
            $usuarios = PersonalEmpresa::where('Activo', 1)
                ->where(function($q) use ($termino) {
                    // Búsqueda por nombre completo (Nombre + ApPaterno + ApMaterno)
                    $q->whereRaw("CONCAT(Nombre, ' ', COALESCE(apPaterno, ''), ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$termino}%"])
                    // Búsqueda por nombre + apellido paterno
                    ->orWhereRaw("CONCAT(Nombre, ' ', COALESCE(apPaterno, '')) LIKE ?", ["%{$termino}%"])
                    // Búsqueda por nombre + apellido materno
                    ->orWhereRaw("CONCAT(Nombre, ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$termino}%"])
                    // Búsqueda por apellido paterno + apellido materno
                    ->orWhereRaw("CONCAT(COALESCE(apPaterno, ''), ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$termino}%"])
                    // Búsqueda por campos individuales
                    ->orWhere('Nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apPaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('apMaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('usuario', 'LIKE', "%{$termino}%")
                    ->orWhere('TelefonoMovil', 'LIKE', "%{$termino}%")
                    ->orWhere('TelefonoFijo', 'LIKE', "%{$termino}%")
                    ->orWhere('contacto', 'LIKE', "%{$termino}%")
                    ->orWhere('id_personal_empresa', 'LIKE', "%{$termino}%");
                })
                ->orderBy('apPaterno', 'asc')
                ->orderBy('apMaterno', 'asc')
                ->orderBy('Nombre', 'asc')
                ->limit(20)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarUsuarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna lista de usuarios en formato JSON (para filtros)
     */
    public function json(): JsonResponse
    {
        $usuarios = PersonalEmpresa::where('Activo', 1)
            ->orderBy('id_personal_empresa', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    }

    /**
     * Retorna lista de repartidores en formato JSON
     * Solo usuarios que tengan horario en rh_personal_servicios_domicilio
     */
    public function repartidoresLista(): JsonResponse
    {
        $hoy = now()->format('Y-m-d');
        
        $repartidores = PersonalEmpresa::where('Activo', 1)
            ->whereIn('id_personal_empresa', function($q) use ($hoy) {
                $q->select('id_personal')
                ->from('rh_personal_servicios_domicilio')
                ->whereRaw('CAST(fecha AS DATE) = ?', [$hoy]);
            })
            ->orderBy('id_personal_empresa', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $repartidores
        ]);
    }
}