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
        
        // Obtener todas las notificaciones de módulos a los que el usuario tiene acceso
        $todasLasNotificaciones = [];
        
        // Verificar permisos y agregar notificaciones de cada módulo
        if ($user->puede('ventas', 'cotizaciones', 'ver')) {
            $cotizaciones = $this->getNotificacionesCotizaciones($user)->getData();
            if (!empty($cotizaciones->data)) {
                $todasLasNotificaciones = array_merge($todasLasNotificaciones, $cotizaciones->data);
            }
        }
        
        if ($user->puede('ventas', 'pedidos', 'ver')) {
            $pedidos = $this->getNotificacionesPedidos($user)->getData();
            if (!empty($pedidos->data)) {
                $todasLasNotificaciones = array_merge($todasLasNotificaciones, $pedidos->data);
            }
        }
        
        if ($user->puede('ventas', 'agenda_contactos', 'ver')) {
            $contactos = $this->getNotificacionesAgendaContactos($user)->getData();
            if (!empty($contactos->data)) {
                $todasLasNotificaciones = array_merge($todasLasNotificaciones, $contactos->data);
            }
        }
        
        // Ordenar por prioridad (contactos próximos primero, luego por días)
        usort($todasLasNotificaciones, function($a, $b) {
            // Contactos próximos tienen prioridad
            if (isset($a->tipo) && $a->tipo === 'contacto' && isset($b->tipo) && $b->tipo !== 'contacto') {
                return -1;
            }
            if (isset($a->tipo) && $a->tipo !== 'contacto' && isset($b->tipo) && $b->tipo === 'contacto') {
                return 1;
            }
            // Luego ordenar por días (mayor primero para cotizaciones/pedidos)
            $diasA = $a->dias ?? 0;
            $diasB = $b->dias ?? 0;
            return $diasB - $diasA;
        });
        
        $total = count($todasLasNotificaciones);
        
        // Determinar el tipo de header según el módulo actual (solo para el título)
        $tipoHeader = $modulo;
        
        return response()->json([
            'success' => true,
            'data' => $todasLasNotificaciones,
            'total' => $total,
            'mensaje_general' => $total === 0 ? 'No hay notificaciones pendientes' : null,
            'tipo' => $tipoHeader
        ]);
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
        
        // Obtener contactos pendientes
        $contactos = AgendaContacto::where('estado', 1)
            ->where('activo', 1)
            ->get();
        
        \Log::info('=== DEBUG NOTIFICACIONES AGENDA ===');
        \Log::info('Total contactos encontrados: ' . $contactos->count());
        
        $notificaciones = [];
        $ahora = now();
        \Log::info('Hora actual: ' . $ahora->format('Y-m-d H:i:s'));
        
        foreach ($contactos as $contacto) {
            $fechaHora = \Carbon\Carbon::parse($contacto->fecha . ' ' . $contacto->hora);
            $recordatorioMinutos = $contacto->recordatorio_minutos ?? 60;
            
            $inicioNotificacion = $fechaHora->copy()->subMinutes($recordatorioMinutos);
            $finNotificacion = $fechaHora->copy()->addMinutes(60);
            
            \Log::info('--- Contacto ID: ' . $contacto->id_agenda_contacto);
            \Log::info('  Fecha/Hora agendada: ' . $fechaHora->format('Y-m-d H:i:s'));
            \Log::info('  Recordatorio minutos: ' . $recordatorioMinutos);
            \Log::info('  Inicio notificación: ' . $inicioNotificacion->format('Y-m-d H:i:s'));
            \Log::info('  Fin notificación: ' . $finNotificacion->format('Y-m-d H:i:s'));
            \Log::info('  ¿En rango? ' . ($ahora >= $inicioNotificacion && $ahora <= $finNotificacion ? 'SI' : 'NO'));
                
                // Calcular el momento en que debe empezar la notificación
                $inicioNotificacion = $fechaHora->copy()->subMinutes($recordatorioMinutos);
                $finNotificacion = $fechaHora->copy()->addMinutes(60); // Tolerancia de 60 minutos después
                
                // Verificar si estamos dentro del rango de notificación
                if ($ahora >= $inicioNotificacion && $ahora <= $finNotificacion) {
                    $minutosDiferencia = $ahora->diffInMinutes($fechaHora, false);
                    $cliente = $contacto->cliente;
                    $nombreCliente = $cliente->nombre_completo ?? 'Cliente';
                    
                    $fecha = $contacto->fecha;
                    $hora = \Carbon\Carbon::parse($contacto->hora)->format('g:i A');
                    
                    // Determinar estado según tiempo
                    if ($minutosDiferencia >= 0) {
                        // Próximo (antes de la hora)
                        $minutosRestantes = $minutosDiferencia;
                        $color = 'warning';
                        $icono = 'bi-clock-history';
                        $mensaje = "⚠️ ¡Próximo! {$contacto->asunto} - En " . $this->formatearTiempo($minutosRestantes);
                    } else {
                        // Atrasado (después de la hora)
                        $minutosAtraso = abs($minutosDiferencia);
                        $color = 'danger';
                        $icono = 'bi-exclamation-triangle';
                        $mensaje = "🔴 ¡Atrasado! {$contacto->asunto} - Debía realizarse a las {$hora}. ({$this->formatearTiempo($minutosAtraso)} de retraso)";
                    }
                    
                    $notificaciones[] = [
                        'id' => $contacto->id_agenda_contacto,
                        'cliente' => $nombreCliente,
                        'asunto' => $contacto->asunto,
                        'fecha' => $fecha,
                        'hora' => $hora,
                        'color' => $color,
                        'icono' => $icono,
                        'mensaje' => $mensaje,
                        'url' => route('ventas.agenda_contactos.index') . '?destacar=' . $contacto->id_agenda_contacto . '&destacar_tipo=contacto',
                        'tipo' => 'contacto'
                    ];
                }
            }
            
            // Ordenar por fecha/hora
            usort($notificaciones, function($a, $b) {
                return strtotime($a['fecha'] . ' ' . $a['hora']) - strtotime($b['fecha'] . ' ' . $b['hora']);
            });
            
            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'total' => count($notificaciones),
                'mensaje_general' => count($notificaciones) === 0 ? 'No hay contactos próximos o atrasados' : null,
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

    private function formatearTiempo(int $minutos): string
    {
        if ($minutos < 60) {
            return "{$minutos} minuto(s)";
        }
        $horas = floor($minutos / 60);
        $resto = $minutos % 60;
        if ($resto > 0) {
            return "{$horas} hora(s) y {$resto} minuto(s)";
        }
        return "{$horas} hora(s)";
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
}