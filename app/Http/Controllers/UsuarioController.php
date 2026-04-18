<?php

namespace App\Http\Controllers;

use App\Models\PersonalEmpresa;
use App\Models\DashboardPreferencia;
use App\Models\PermisoGranular;
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
        
        $usuarios = PersonalEmpresa::orderBy('id_personal_empresa', 'asc')->get();
        
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
            'passw' => 'required|string|min:6',
            'dashboard_cards' => 'nullable|array',
            'dashboard_cards.*' => 'string|in:acceso_clientes,acceso_cotizaciones',
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
            
            // Guardar preferencias del dashboard
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
        $usuario = PersonalEmpresa::with('dashboardPreferencias', 'permisosGranulares')->findOrFail($id);
        
        // Obtener permisos formateados
        $permisos = $usuario->permisos_formateados;
        
        // Obtener cards activos del dashboard
        $dashboardCards = DashboardPreferencia::where('id_personal_empresa', $usuario->id_personal_empresa)
            ->where('mostrar', true)
            ->orderBy('orden')
            ->pluck('card_key')
            ->toArray();
        
        // No enviar los campos de contraseña por seguridad
        $usuario->makeHidden(['password', 'passw']);
        
        return response()->json([
            'success' => true,
            'data' => $usuario,
            'permisos' => $permisos,
            'dashboard_cards' => $dashboardCards  // ← Agregar esto
        ]);
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
            'passw' => 'nullable|string|min:6',
            'dashboard_cards' => 'nullable|array',
            'dashboard_cards.*' => 'string|in:acceso_clientes,acceso_cotizaciones',
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
            // Si no se envía sucursal_asignada o viene vacío, se asigna 0 (CRM)
            'sucursal_asignada' => ($validated['sucursal_asignada'] ?? 0) ?: 0,
            'curp' => $validated['curp'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'usuario' => $validated['usuario'],
        ];

        // Si se envió nueva contraseña
        if (!empty($validated['passw'])) {
            $datosActualizar['passw'] = $validated['passw'];
        }

        DB::beginTransaction();
        
        try {
            // Actualizar usuario
            $usuario->update($datosActualizar);
            
            // Actualizar preferencias del dashboard (eliminar y recrear)
            DashboardPreferencia::where('id_personal_empresa', $usuario->id_personal_empresa)->delete();
            
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
            
            // Actualizar permisos si se enviaron (usando updateOrCreate, no eliminar)
            if ($request->has('permisos_modulos')) {
                $this->actualizarPermisos($usuario->id_personal_empresa, $request->permisos_modulos);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $usuario
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
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
}