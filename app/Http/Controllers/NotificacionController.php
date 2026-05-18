<?php

namespace App\Http\Controllers;

use App\Models\Cotizaciones\Cotizacion;
use App\Models\Configuracion;
use App\Models\Seguimientos\Seguimiento;
use App\Models\AgendaContacto\AgendaContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class NotificacionController extends Controller
{
    public function getNotificaciones(): JsonResponse
    {
        $user = Auth::user();
        $modulo = Request::input('modulo', 'dashboard');
        
        // Determinar qué tipo de notificaciones mostrar según el módulo
        switch ($modulo) {
            case 'cotizaciones':
                return $this->getNotificacionesCotizaciones($user);
            case 'pedidos':
                return $this->getNotificacionesPedidos($user);
            case 'agenda_contactos':
                return $this->getNotificacionesAgendaContactos($user);
            default:
                // Dashboard: mostrar resumen de ambos
                return $this->getNotificacionesDashboard($user);
        }
    }
    
    private function getNotificacionesAgendaContactos($user): JsonResponse
    {
        try {
            if (!$user->puede('ventas', 'agenda_contactos', 'ver')) {
                return response()->json([
                    'success' => true, 
                    'data' => [],
                    'total' => 0,
                    'mensaje_general' => 'No hay contactos próximos',
                    'tipo' => 'contactos'
                ]);
            }
            
            // Obtener contactos pendientes (próximas 24 horas)
            $contactos = AgendaContacto::where('estado', 1)
                ->where('activo', 1)
                ->where('fecha', '>=', now()->subDay()->format('Y-m-d'))
                ->where('fecha', '<=', now()->addDay()->format('Y-m-d'))
                ->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->get();
            
            $notificaciones = [];
            
            foreach ($contactos as $contacto) {
                $cliente = $contacto->cliente;
                $nombreCliente = $cliente->nombre_completo ?? 'Cliente';
                
                // Formatear fecha y hora
                $fecha = \Carbon\Carbon::parse($contacto->fecha)->format('d/m/Y');
                $hora = \Carbon\Carbon::parse($contacto->hora)->format('g:i A'); // Ej: 1:27 PM
                
                $notificaciones[] = [
                    'id' => $contacto->id_agenda_contacto,
                    'cliente' => $nombreCliente,
                    'asunto' => $contacto->asunto,
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'mensaje' => "Programado para {$fecha} a las {$hora}",
                    'url' => route('ventas.agenda_contactos.index') . '?destacar=' . $contacto->id_agenda_contacto . '&destacar_tipo=contacto',
                    'tipo' => 'contacto'
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'total' => count($notificaciones),
                'mensaje_general' => count($notificaciones) === 0 ? 'No hay contactos próximos' : null,
                'tipo' => 'contactos'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesAgendaContactos: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [],
                'total' => 0,
                'mensaje_general' => 'No hay contactos próximos',
                'tipo' => 'contactos'
            ]);
        }
    }
    
    private function getMensajeContacto($contacto): string
    {
        $tiempoRestante = now()->diffInMinutes($contacto->fecha_hora, false);
        
        if ($tiempoRestante <= 60) {
            return "⚠️ ¡Próximo! {$contacto->asunto} - En menos de una hora.";
        } elseif ($tiempoRestante <= 180) {
            $horas = round($tiempoRestante / 60, 1);
            return "⏰ Recordatorio: {$contacto->asunto} - En {$horas} horas.";
        } else {
            return "📅 {$contacto->asunto} - Programado para {$contacto->fecha_hora->format('d/m/Y H:i')}.";
        }
    }
    
    private function getNotificacionesDashboard($user): JsonResponse
    {
        $cotizaciones = $this->getNotificacionesCotizaciones($user)->getData();
        $contactos = $this->getNotificacionesAgendaContactos($user)->getData();
        
        $todas = array_merge($cotizaciones->data ?? [], $contactos->data ?? []);
        
        // Ordenar por prioridad: contactos próximos primero
        usort($todas, function($a, $b) {
            if (isset($a->fecha_hora) && isset($b->fecha_hora)) {
                return strtotime($a->fecha_hora) - strtotime($b->fecha_hora);
            }
            return -1;
        });
        
        return response()->json([
            'success' => true,
            'data' => $todas,
            'total' => count($todas),
            'mensaje_general' => count($todas) === 0 ? 'No hay notificaciones pendientes' : null,
            'tipo' => 'dashboard'
        ]);
    }
    
    private function getNotificacionesCotizaciones($user): JsonResponse
    {
        if (!$user->puede('ventas', 'cotizaciones', 'ver')) {
            return response()->json([
                'success' => true, 
                'data' => [],
                'mensaje_general' => 'No hay cotizaciones que requieran seguimiento',
                'tipo' => 'cotizaciones'
            ]);
        }
        
        $diasAlerta = Configuracion::getValor('dias_sin_contacto_alerta', 7);
        
        $faseEnProceso = \App\Models\Cotizaciones\CatFase::where('fase', 'En proceso')->first();
        
        if (!$faseEnProceso) {
            return response()->json([
                'success' => true, 
                'data' => [],
                'mensaje_general' => 'No hay cotizaciones que requieran seguimiento',
                'tipo' => 'cotizaciones'
            ]);
        }
        
        $cotizaciones = Cotizacion::with(['cliente', 'seguimientos'])
            ->where('activo', 1)
            ->where('es_pedido', '!=', 1)
            ->where('id_fase', $faseEnProceso->id_fase)
            ->get();
        
        $notificaciones = [];
        
        foreach ($cotizaciones as $cotizacion) {
            $diasSinContacto = $cotizacion->fecha_creacion ? $cotizacion->fecha_creacion->diffInDays(now()) : 0;
            
            $tieneSeguimientoReciente = $cotizacion->seguimientos()
                ->where('hora_inicio', '>=', now()->subDays($diasAlerta))
                ->exists();
            
            if ($diasSinContacto >= $diasAlerta && !$tieneSeguimientoReciente) {
                $notificaciones[] = [
                    'id' => $cotizacion->id_cotizacion,
                    'folio' => $cotizacion->folio,
                    'cliente' => $cotizacion->nombre_cliente,
                    'dias' => $diasSinContacto,
                    'mensaje' => "¡Requiere seguimiento! No se ha contactado al cliente recientemente. ({$diasSinContacto} días)",
                    'url' => route('ventas.cotizaciones.index') . '?destacar=' . $cotizacion->id_cotizacion . '&destacar_tipo=cotizacion',
                    'tipo' => 'cotizacion'
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $notificaciones,
            'total' => count($notificaciones),
            'mensaje_general' => count($notificaciones) === 0 ? 'No hay cotizaciones que requieran seguimiento' : null,
            'tipo' => 'cotizaciones'
        ]);
    }
    
    private function getNotificacionesPedidos($user): JsonResponse
    {
        try {
            if (!$user->puede('ventas', 'pedidos', 'ver')) {
                return response()->json([
                    'success' => true, 
                    'data' => [],
                    'total' => 0,
                    'mensaje_general' => 'No hay pedidos pendientes de seguimiento',
                    'tipo' => 'pedidos'
                ]);
            }
            
            // Obtener pedidos que requieren atención
            // Ejemplo: pedidos en estado "En proceso" (status = 2) que tienen más de X días sin seguimiento
            $diasAlerta = Configuracion::getValor('dias_sin_contacto_alerta', 7);
            
            $pedidos = \App\Models\Pedidos\OrdenPedido::with(['cotizacion.cliente', 'seguimientos'])
                ->where('status', 2) // En proceso
                ->where('activo', 1)
                ->get();
            
            $notificaciones = [];
            
            foreach ($pedidos as $pedido) {
                $fechaCreacion = $pedido->fecha_pedido ?? $pedido->created_at;
                $diasTranscurridos = $fechaCreacion ? $fechaCreacion->diffInDays(now()) : 0;
                
                // Verificar si tiene seguimiento reciente
                $tieneSeguimientoReciente = $pedido->seguimientos()
                    ->where('hora_inicio', '>=', now()->subDays($diasAlerta))
                    ->exists();
                
                if ($diasTranscurridos >= $diasAlerta && !$tieneSeguimientoReciente) {
                    $cliente = $pedido->cotizacion->cliente ?? null;
                    $nombreCliente = $cliente->nombre_completo ?? 'Cliente';
                    
                    $notificaciones[] = [
                        'id' => $pedido->id_pedido,
                        'folio' => $pedido->folio_pedido,
                        'cliente' => $nombreCliente,
                        'dias' => $diasTranscurridos,
                        'mensaje' => "¡Requiere seguimiento! Pedido sin contacto reciente. ({$diasTranscurridos} días)",
                        'url' => route('ventas.pedidos.index') . '?destacar=' . $pedido->id_pedido . '&destacar_tipo=pedido',
                        'tipo' => 'pedido'
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'total' => count($notificaciones),
                'mensaje_general' => count($notificaciones) === 0 ? 'No hay pedidos pendientes de seguimiento' : null,
                'tipo' => 'pedidos'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesPedidos: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [],
                'total' => 0,
                'mensaje_general' => 'No hay pedidos pendientes de seguimiento',
                'tipo' => 'pedidos'
            ]);
        }
    }
    
    private function getNotificacionesContactos($user): JsonResponse
    {
        // Aquí puedes implementar notificaciones para agenda de contactos
        return response()->json([
            'success' => true,
            'data' => [],
            'total' => 0,
            'mensaje_general' => 'No hay contactos próximos',
            'tipo' => 'contactos'
        ]);
    }
}