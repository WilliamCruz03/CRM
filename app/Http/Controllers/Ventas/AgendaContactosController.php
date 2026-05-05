<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\AgendaContacto\AgendaContacto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AgendaContactosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        if (!auth()->user()->puede('ventas', 'agenda_contactos', 'ver')) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        $contactos = AgendaContacto::where('activo', true)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
        
        // Enriquecer con datos del cliente (sin usar nombre_completo)
        foreach ($contactos as $contacto) {
            $cliente = DB::connection('sqlsrvM')
                ->table('catalogo_cliente_maestro')
                ->where('id_Cliente', $contacto->id_cliente)
                ->first(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'email1', 'Domicilio']);
            
            // Construir nombre completo manualmente
            $nombreCompleto = trim(($cliente->Nombre ?? '') . ' ' . ($cliente->apPaterno ?? '') . ' ' . ($cliente->apMaterno ?? ''));
            
            $contacto->nombre_cliente = $nombreCompleto ?: 'N/A';
            $contacto->telefono_cliente = $cliente->telefono1 ?? 'N/A';
        }
        
        $permisos = [
            'ver' => auth()->user()->puede('ventas', 'agenda_contactos', 'ver'),
            'crear' => auth()->user()->puede('ventas', 'agenda_contactos', 'crear'),
            'editar' => auth()->user()->puede('ventas', 'agenda_contactos', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'agenda_contactos', 'eliminar'),
        ];
        
        $recordatorios = DB::connection('sqlsrv')
            ->table('crm_configuraciones')
            ->where('modulo_ventas', 1)
            ->where('nombre', 'like', 'recordatorio_%')
            ->where('activo', 1)
            ->get();
        
        return view('ventas.agenda_contactos.index', compact('contactos', 'permisos', 'recordatorios'));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'agenda_contactos', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $validated = $request->validate([
            'id_cliente' => 'required|integer|min:1',
            'asunto' => 'required|string|max:255',
            'tipo' => 'required|integer|in:1,2,3',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'comentario' => 'nullable|string|max:300',
            'recordatorio_minutos' => 'nullable|integer|min:0'
        ]);
        
        try {
            $contacto = AgendaContacto::create([
                'id_cliente' => $validated['id_cliente'],
                'asunto' => $validated['asunto'],
                'tipo' => $validated['tipo'],
                'estado' => AgendaContacto::ESTADO_PENDIENTE,
                'fecha' => $validated['fecha'],
                'hora' => $validated['hora'],
                'comentario' => $validated['comentario'] ?? null,
                'recordatorio_minutos' => $validated['recordatorio_minutos'] ?? null,
                'recordatorio_enviado' => false,
                'creado_por' => auth()->id(),
                'activo' => true,
                'fecha_creacion' => now(),
                'fecha_actualizacion' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Contacto agendado correctamente',
                'data' => $contacto
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al crear contacto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear contacto: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'agenda_contactos', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $contacto = AgendaContacto::findOrFail($id);
        
        $validated = $request->validate([
            'asunto' => 'required|string|max:255',
            'tipo' => 'required|integer|in:1,2,3',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'comentario' => 'nullable|string|max:300',
            'recordatorio_minutos' => 'nullable|integer|min:0'
        ]);
        
        try {
            $contacto->update([
                'asunto' => $validated['asunto'],
                'tipo' => $validated['tipo'],
                'fecha' => $validated['fecha'],
                'hora' => $validated['hora'],
                'comentario' => $validated['comentario'] ?? null,
                'recordatorio_minutos' => $validated['recordatorio_minutos'] ?? null,
                'fecha_actualizacion' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Contacto actualizado correctamente',
                'data' => $contacto
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al actualizar contacto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar contacto: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Edit form data for the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'agenda_contactos', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $contacto = AgendaContacto::findOrFail($id);
        
        // Obtener datos del cliente
        $cliente = DB::connection('sqlsrvM')
            ->table('catalogo_cliente_maestro')
            ->where('id_Cliente', $contacto->id_cliente)
            ->first(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'email1', 'Domicilio']);
        
        $nombreCompleto = trim(($cliente->Nombre ?? '') . ' ' . ($cliente->apPaterno ?? '') . ' ' . ($cliente->apMaterno ?? ''));
        
        return response()->json([
            'success' => true,
            'data' => [
                'id_agenda_contacto' => $contacto->id_agenda_contacto,
                'id_cliente' => $contacto->id_cliente,
                'nombre_cliente' => $nombreCompleto ?: 'N/A',
                'telefono1' => $cliente->telefono1 ?? '',
                'email1' => $cliente->email1 ?? '',
                'domicilio' => $cliente->Domicilio ?? '',
                'asunto' => $contacto->asunto,
                'tipo' => $contacto->tipo,
                'fecha' => $contacto->fecha instanceof \DateTime ? $contacto->fecha->format('Y-m-d') : $contacto->fecha,
                'hora' => $contacto->hora,
                'recordatorio_minutos' => $contacto->recordatorio_minutos,
                'comentario' => $contacto->comentario
            ]
        ]);
    }
    
    /**
     * Cambiar estado del contacto.
     */
    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'agenda_contactos', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $validated = $request->validate([
            'estado' => 'required|integer|in:1,2,3'
        ]);
        
        $contacto = AgendaContacto::findOrFail($id);
        
        try {
            $contacto->update([
                'estado' => $validated['estado'],
                'fecha_actualizacion' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al cambiar estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'agenda_contactos', 'eliminar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $contacto = AgendaContacto::findOrFail($id);
            $contacto->update([
                'activo' => false,
                'fecha_actualizacion' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Contacto eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al eliminar contacto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar contacto: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener contactos próximos para notificaciones (campana).
     */
    public function proximosContactos(): JsonResponse
    {
        try {
            $minutosNotificacion = DB::connection('sqlsrv')
                ->table('crm_configuraciones')
                ->where('nombre', 'notificaciones_minutos')
                ->value('valor') ?? 60;
            
            // Obtener contactos pendientes con fecha_hora en el rango
            $contactos = AgendaContacto::where('estado', AgendaContacto::ESTADO_PENDIENTE)
                ->where('activo', true)
                ->where('recordatorio_enviado', false)
                ->whereRaw("CONVERT(DATETIME, fecha + ' ' + hora) >= GETDATE()")
                ->whereRaw("CONVERT(DATETIME, fecha + ' ' + hora) <= DATEADD(MINUTE, ?, GETDATE())", [$minutosNotificacion])
                ->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->limit(10)
                ->get();
            
            foreach ($contactos as $contacto) {
                $cliente = DB::connection('sqlsrvM')
                    ->table('catalogo_cliente_maestro')
                    ->where('id_Cliente', $contacto->id_cliente)
                    ->first();
                
                $nombreCompleto = $cliente ? trim(($cliente->Nombre ?? '') . ' ' . ($cliente->apPaterno ?? '') . ' ' . ($cliente->apMaterno ?? '')) : 'N/A';
                
                $contacto->nombre_cliente = $nombreCompleto ?: 'N/A';
                $contacto->fecha_hora_formateada = date('d/m/Y H:i', strtotime($contacto->fecha . ' ' . $contacto->hora));
                $contacto->tipo_nombre = $contacto->tipo_nombre;
                $contacto->url = route('ventas.agenda_contactos.index');
            }
            
            return response()->json([
                'success' => true,
                'total' => $contactos->count(),
                'contactos' => $contactos
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al obtener próximos contactos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Marcar recordatorio como enviado.
     */
    public function marcarRecordatorioEnviado(int $id): JsonResponse
    {
        try {
            $contacto = AgendaContacto::findOrFail($id);
            $contacto->update([
                'recordatorio_enviado' => true,
                'fecha_actualizacion' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Recordatorio marcado como enviado'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al marcar recordatorio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar solicitud'
            ], 500);
        }
    }
    
    /**
     * Buscar clientes para autocompletar.
     */
    public function buscarClientes(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        
        if (strlen($termino) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        $clientes = Cliente::whereIn('status', ['CLIENTE', 'PROSPECTO'])
            ->where(function($query) use ($termino) {
                $query->where('id_Cliente', 'LIKE', "%{$termino}%")
                    ->orWhere('Nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apPaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('apMaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono1', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono2', 'LIKE', "%{$termino}%")
                    ->orWhere('email1', 'LIKE', "%{$termino}%")
                    ->orWhere('Domicilio', 'LIKE', "%{$termino}%")
                    ->orWhereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$termino}%"]);
            })
            ->limit(10)
            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2', 'email1', 'Domicilio', 'titulo']);
        
        return response()->json([
            'success' => true,
            'data' => $clientes->map(function($cliente) {
                $nombreCompleto = $cliente->nombre_completo;
                $tituloHtml = '';
                if ($cliente->titulo) {
                    $tituloHtml = "<br><small class='text-muted'>{$cliente->titulo}</small>";
                }
                
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
                
                $direccionHtml = '';
                if ($cliente->Domicilio) {
                    $direccionHtml = "<br><small class='text-muted'><i class='bi bi-geo-alt'></i> {$cliente->Domicilio}</small>";
                }
                
                return [
                    'id_Cliente' => $cliente->id_Cliente,
                    'nombre_completo' => $nombreCompleto,
                    'titulo_html' => $tituloHtml,
                    'contacto_html' => $contactoHtml ?: '<span class="text-muted">Sin contacto</span>',
                    'direccion_html' => $direccionHtml,
                    'telefono1' => $cliente->telefono1,
                    'email1' => $cliente->email1,
                    'domicilio' => $cliente->Domicilio
                ];
            })
        ]);
    }

    /**
     * Obtener configuración de notificaciones.
     */
    public function configNotificaciones(): JsonResponse
    {
        $activas = DB::connection('sqlsrv')
            ->table('crm_configuraciones')
            ->where('nombre', 'notificaciones_activas')
            ->value('valor') == 1;
        
        $intervalo = DB::connection('sqlsrv')
            ->table('crm_configuraciones')
            ->where('nombre', 'notificaciones_intervalo')
            ->value('valor') ?? 60;
        
        return response()->json([
            'success' => true,
            'activas' => $activas,
            'intervalo' => (int)$intervalo
        ]);
    }
}