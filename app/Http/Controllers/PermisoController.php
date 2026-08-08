<?php

namespace App\Http\Controllers;

use App\Models\PersonalEmpresa;
use App\Models\DashboardPreferencia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class PermisoController extends Controller
{
    /**
     * Muestra la lista de usuarios para asignar permisos
     */
    public function index(): View
    {
        $puedeVer = auth()->user()->puede('seguridad', 'permisos', 'ver');
        $puedeCrear = auth()->user()->puede('seguridad', 'permisos', 'crear');
        
        if (!$puedeVer && !$puedeCrear) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        $usuarios = PersonalEmpresa::where('Activo', 1)
            ->orderBy('Nombre', 'asc')
            ->orderBy('ApPaterno', 'asc')
            ->paginate(15);
        
        $permisos = [
            'ver' => $puedeVer,
            'crear' => $puedeCrear,
            'editar' => auth()->user()->puede('seguridad', 'permisos', 'editar'),
            'eliminar' => auth()->user()->puede('seguridad', 'permisos', 'eliminar'),
        ];
        
        return view('seguridad.permisos.index', compact('usuarios', 'permisos'));
    }

    /**
     * Retorna los datos del usuario para editar permisos
     */
    public function edit(int $id): JsonResponse
    {
        if (!auth()->user()->puede('seguridad', 'permisos', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar permisos'
            ], 403);
        }
        
        try {
            $usuario = PersonalEmpresa::findOrFail($id);
            
            // Obtener permisos formateados
            $permisos = $usuario->permisos_formateados;
            
            // Obtener dashboard cards
            $dashboardCards = DashboardPreferencia::where('id_personal_empresa', $id)
                ->where('mostrar', true)
                ->orderBy('orden')
                ->pluck('card_key')
                ->toArray();
            
            // Estado de repartidor
            $esRepartidor = $usuario->es_repartidor;
            $tieneHorario = $usuario->tieneHorarioRepartidor();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $usuario->id_personal_empresa,
                    'nombre' => $usuario->nombre_completo,
                    'usuario' => $usuario->usuario,
                    'es_repartidor' => $esRepartidor,
                    'tiene_horario' => $tieneHorario,
                ],
                'permisos' => $permisos,
                'dashboard_cards' => $dashboardCards
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza los permisos del usuario
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Verificar permiso de edición de permisos
        if (!auth()->user()->puede('seguridad', 'permisos', 'editar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar permisos'
            ], 403);
        }
        
        try {
            $usuario = PersonalEmpresa::findOrFail($id);
            
            $validated = $request->validate([
                'dashboard_cards' => 'nullable|array',
                'dashboard_cards.*' => 'string|in:kpi_total_clientes,kpi_contactos_proximos,kpi_total_cotizaciones,kpi_cotizaciones_pendientes,kpi_monto_total_mes,grafico_estados_cotizaciones,tabla_ultimos_contactos,tabla_ultimas_cotizaciones,resumen_rapido,resumen_ventas_mensual',
                'permisos_modulos' => 'nullable|array',
            ]);

            // VALIDAR QUE AL MENOS UN PERFIL ESTÉ SELECCIONADO
            $perfiles = $request->input('permisos_modulos.perfiles', []);
            $tienePerfil = ($perfiles['es_crm'] ?? false) || 
                        ($perfiles['es_sucursal'] ?? false) || 
                        ($perfiles['es_repartidor'] ?? false);
            
            if (!$tienePerfil) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar al menos un perfil (CRM, Sucursal o Repartidor)'
                ], 422);
            }

            DB::beginTransaction();
            
            // Actualizar preferencias del dashboard
            $cardsNoAcceso = $validated['dashboard_cards'] ?? [];
            
            // Desactivar todos los cards existentes
            DashboardPreferencia::where('id_personal_empresa', $usuario->id_personal_empresa)
                ->update(['mostrar' => false]);
            
            // Activar los seleccionados
            $orden = 1;
            foreach ($cardsNoAcceso as $cardKey) {
                DashboardPreferencia::updateOrCreate(
                    [
                        'id_personal_empresa' => $usuario->id_personal_empresa,
                        'card_key' => $cardKey,
                    ],
                    [
                        'mostrar' => true,
                        'orden' => $orden++,
                    ]
                );
            }
            
            // Actualizar permisos granulares
            if ($request->has('permisos_modulos')) {
                $usuario->sincronizarPermisos($request->input('permisos_modulos'));
                $usuario->validarYCorregirPermisos();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Permisos actualizados correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar permisos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un usuario (opcional, con permiso)
     */
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->puede('seguridad', 'permisos', 'eliminar')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar usuarios'
            ], 403);
        }
        
        try {
            $usuario = PersonalEmpresa::findOrFail($id);
            
            // Evitar eliminar al propio usuario
            if (auth()->id() == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario'
                ], 403);
            }
            
            // Eliminar permisos asociados
            $usuario->permisosGranulares()->delete();
            DashboardPreferencia::where('id_personal_empresa', $id)->delete();
            
            // Eliminar usuario
            $usuario->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al eliminar usuario desde permisos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario'
            ], 500);
        }
    }
}