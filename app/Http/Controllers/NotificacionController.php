<?php

namespace App\Http\Controllers;

use App\Models\Cotizaciones\Cotizacion;
use App\Models\Configuracion;
use App\Models\Seguimientos\Seguimiento;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Notificacion;
use App\Models\AgendaContacto\AgendaContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as HttpRequest;

class NotificacionController extends Controller
{
    public function getNotificaciones(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Si no hay usuario autenticado, devolver JSON vacío
            if (!$user) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total' => 0,
                    'mensaje_general' => 'No hay notificaciones disponibles',
                    'tipo' => 'sin_notificaciones'
                ]);
            }

            $modulo = Request::input('modulo', 'dashboard');

            // Obtener todas las notificaciones de módulos a los que el usuario tiene acceso
            $todasLasNotificaciones = [];

            // NOTIFICACIONES DE LA TABLA
            $notificacionesTabla = $this->getNotificacionesTabla($user);
            if (!empty($notificacionesTabla)) {
                $todasLasNotificaciones = array_merge($todasLasNotificaciones, $notificacionesTabla);
            }

            // Notificaciones existentes
            if ($user->puede('ventas', 'cotizaciones', 'ver')) {
                $cotizaciones = $this->getNotificacionesCotizacionesArray($user);
                if (!empty($cotizaciones)) {
                    $todasLasNotificaciones = array_merge($todasLasNotificaciones, $cotizaciones);
                }
            }

            if ($user->puede('ventas', 'pedidos', 'ver')) {
                $pedidos = $this->getNotificacionesPedidosArray($user);
                if (!empty($pedidos)) {
                    $todasLasNotificaciones = array_merge($todasLasNotificaciones, $pedidos);
                }
            }

            if ($user->puede('ventas', 'agenda_contactos', 'ver')) {
                $contactos = $this->getNotificacionesAgendaContactos($user);
                if (!empty($contactos)) {
                    $todasLasNotificaciones = array_merge($todasLasNotificaciones, $contactos);
                }
            }

            // Ordenar (las de la tabla ya vienen ordenadas por fecha)
            usort($todasLasNotificaciones, function ($a, $b) {
                // Si una es de la tabla, priorizar por fecha
                if (isset($a['es_persistente']) && isset($b['es_persistente'])) {
                    $fechaA = $a['created_at'] ?? '';
                    $fechaB = $b['created_at'] ?? '';
                    return strtotime($fechaB) - strtotime($fechaA);
                }
                
                // Contactos primero (prioridad existente)
                $tipoA = $a['tipo'] ?? '';
                $tipoB = $b['tipo'] ?? '';

                if ($tipoA === 'contacto' && $tipoB !== 'contacto') {
                    return -1;
                }
                if ($tipoA !== 'contacto' && $tipoB === 'contacto') {
                    return 1;
                }
                
                $diasA = $a['dias'] ?? 0;
                $diasB = $b['dias'] ?? 0;
                return $diasB - $diasA;
            });

            return response()->json([
                'success' => true,
                'data' => array_values($todasLasNotificaciones),
                'total' => count($todasLasNotificaciones),
                'mensaje_general' => count($todasLasNotificaciones) === 0 ? 'No hay notificaciones pendientes' : null,
                'tipo' => $modulo
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificaciones: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'data' => [],
                'total' => 0,
                'mensaje_general' => 'Error al cargar notificaciones',
                'tipo' => 'error'
            ]);
        }
    }
    
    private function getNotificacionesAgendaContactos($user): array
    {
        try {
            if (!$user->puede('ventas', 'agenda_contactos', 'ver')) {
                return [];
            }
            
            // Solo contactos de los últimos 7 días para evitar lentitud
            $contactos = AgendaContacto::where('estado', 1)
                ->where('activo', 1)
                ->where('fecha', '>=', now()->subDays(7))
                ->get();
            
            $notificaciones = [];
            $ahora = now();
            
            foreach ($contactos as $contacto) {
                // Extraer solo la fecha (Y-m-d) y concatenar con la hora
                $soloFecha = substr($contacto->fecha, 0, 10);
                $fechaHora = \Carbon\Carbon::parse($soloFecha . ' ' . $contacto->hora);
                $recordatorioMinutos = $contacto->recordatorio_minutos ?? 60;
                
                $inicioNotificacion = $fechaHora->copy()->subMinutes($recordatorioMinutos);
                $finNotificacion = $fechaHora->copy()->addMinutes(60);
                
                if ($ahora >= $inicioNotificacion && $ahora <= $finNotificacion) {
                    $minutosDiferencia = $ahora->diffInMinutes($fechaHora, false);
                    $cliente = $contacto->cliente;
                    
                    // Construir nombre del cliente manualmente
                    if ($cliente) {
                        $partes = [];
                        if (!empty($cliente->Nombre)) $partes[] = $cliente->Nombre;
                        if (!empty($cliente->apPaterno)) $partes[] = $cliente->apPaterno;
                        if (!empty($cliente->apMaterno)) $partes[] = $cliente->apMaterno;
                        $nombreCliente = implode(' ', $partes);
                        if (empty($nombreCliente)) $nombreCliente = 'Cliente';
                    } else {
                        $nombreCliente = 'Cliente';
                    }
                    
                    $fecha = $contacto->fecha;
                    $hora = \Carbon\Carbon::parse($contacto->hora)->format('g:i A');
                    
                    $color = ($minutosDiferencia >= 0) ? 'warning' : 'danger';  // warning para próximo (amarillo)
                    $icono = ($minutosDiferencia >= 0) ? 'bi-exclamation-triangle' : 'bi-exclamation-triangle';  // mismo icono para ambos
                    $tiempoTexto = $this->formatearTiempo(abs($minutosDiferencia));

                    $mensaje = ($minutosDiferencia >= 0) 
                        ? "Próximo contacto en {$tiempoTexto}"
                        : "Atrasado por {$tiempoTexto}";

                    $notificaciones[] = [
                        'id' => $contacto->id_agenda_contacto,
                        'cliente' => $nombreCliente,
                        'asunto' => $contacto->asunto,
                        'fecha' => $contacto->fecha,
                        'hora' => $hora,
                        'color' => $color,
                        'icono' => $icono,
                        'mensaje' => $mensaje,
                        'url' => route('ventas.agenda_contactos.index') . '?destacar=' . $contacto->id_agenda_contacto . '&destacar_tipo=contacto',
                        'tipo' => 'contacto'
                    ];
                }
            }
            
            return $notificaciones;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesAgendaContactos: ' . $e->getMessage());
            return [];
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
    
    /**
     * Devuelve notificaciones de cotizaciones como ARRAY
     */
    private function getNotificacionesCotizacionesArray($user): array
    {
        try {
            if (!$user->puede('ventas', 'cotizaciones', 'ver')) {
                return [];
            }
            
            $diasAlerta = Configuracion::getValor('dias_sin_contacto_alerta', 7);
            
            $faseEnProceso = \App\Models\Cotizaciones\CatFase::where('fase', 'En proceso')->first();
            
            if (!$faseEnProceso) {
                return [];
            }
            
            $cotizaciones = Cotizacion::with(['cliente', 'seguimientos'])
                ->where('activo', 1)
                ->where('es_pedido', '!=', 1)
                ->where('id_fase', $faseEnProceso->id_fase)
                ->get();
            
            $notificaciones = [];
            
            foreach ($cotizaciones as $cotizacion) {
                $diasSinContacto = $cotizacion->fecha_creacion ? ceil($cotizacion->fecha_creacion->diffInDays(now())) : 0;
                
                // VERIFICAR SEGUIMIENTOS CON TRY-CATCH
                $tieneSeguimientoReciente = false;
                try {
                    $tieneSeguimientoReciente = $cotizacion->seguimientos()
                        ->where('hora_inicio', '>=', now()->subDays($diasAlerta))
                        ->exists();
                } catch (\Exception $e) {
                    \Log::warning('Error al verificar seguimientos para cotización ' . $cotizacion->id_cotizacion . ': ' . $e->getMessage());
                    // Si hay error, asumir que no tiene seguimiento reciente
                    $tieneSeguimientoReciente = false;
                }
                
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
            
            return $notificaciones;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesCotizacionesArray: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Devuelve notificaciones de pedidos como ARRAY
     */
    private function getNotificacionesPedidosArray($user): array
    {
        try {
            if (!$user->puede('ventas', 'pedidos', 'ver')) {
                return [];
            }
            
            // Usar configuración específica para pedidos
            $diasAlerta = Configuracion::getValor('dias_alerta_pedidos', 7);
            
            $pedidos = OrdenPedido::with(['cotizacion.cliente', 'seguimientos'])
                ->where('status', 2)
                ->where('activo', 1)
                ->get();
            
            $notificaciones = [];
            
            foreach ($pedidos as $pedido) {
                $fechaCreacion = $pedido->fecha_pedido ?? $pedido->created_at;
                $diasTranscurridos = $fechaCreacion ? ceil($fechaCreacion->diffInDays(now())) : 0;
                
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
            
            return $notificaciones;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesPedidosArray: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener notificaciones guardadas en la tabla notificaciones
     */
    private function getNotificacionesTabla($user): array
    {
        try {
            $notificaciones = Notificacion::where('id_usuario', $user->id_personal_empresa)
                ->where('leida', 0)  // Solo no leídas
                ->orderBy('created_at', 'DESC')
                ->limit(20)
                ->get();
            
            $resultado = [];
            
            foreach ($notificaciones as $notif) {
                $datosExtra = is_string($notif->datos_extra) ? json_decode($notif->datos_extra, true) : $notif->datos_extra;
                
                // Determinar icono según tipo
                $icono = 'bi-bell';
                $color = 'text-info';
                
                if ($notif->tipo === 'pedido_listo') {
                    $icono = 'bi-box-seam';
                    $color = 'text-success';
                } elseif ($notif->tipo === 'pedido_asignado') {
                    $icono = 'bi-truck';
                    $color = 'text-primary';
                }
                
                // Construir URL para redirigir
                $url = '#';
                if (isset($datosExtra['pedido_id'])) {
                    $url = route('ventas.pedidos.index') . '?destacar=' . $datosExtra['pedido_id'];
                }
                
                $resultado[] = [
                    'id' => $notif->id_notificacion,
                    'titulo' => $notif->titulo,
                    'mensaje' => $notif->mensaje,
                    'tipo' => $notif->tipo,
                    'icono' => $icono,
                    'color' => $color,
                    'leida' => $notif->leida,
                    'url' => $url,
                    'created_at' => $notif->created_at ? $notif->created_at->format('d/m/Y H:i') : '',
                    'datos_extra' => $datosExtra,
                    'es_persistente' => true, // Flag para identificar que viene de la BD
                ];
            }
            
            return $resultado;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesTabla: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Marcar una notificación como leída
     */
    public function marcarComoLeida(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $notificacion = Notificacion::where('id_notificacion', $id)
                ->where('id_usuario', $user->id_personal_empresa)
                ->first();
            
            if (!$notificacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificación no encontrada'
                ], 404);
            }
            
            $notificacion->leida = 1;
            $notificacion->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al marcar notificación como leída: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como leída'
            ], 500);
        }
    }

    /**
     * Obtener historial de notificaciones del usuario (últimas N)
     */
    public function historial(HttpRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            $limit = $request->input('limit', 5);
            
            // SOLO notificaciones LEÍDAS (historial)
            $notificaciones = Notificacion::where('id_usuario', $user->id_personal_empresa)
                ->where('leida', 1)  // Solo leídas
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get()
                ->map(function($notif) {
                    $datosExtra = is_string($notif->datos_extra) ? json_decode($notif->datos_extra, true) : $notif->datos_extra;
                    
                    return [
                        'id' => $notif->id_notificacion,
                        'tipo' => $notif->tipo,
                        'titulo' => $notif->titulo,
                        'mensaje' => $notif->mensaje,
                        'leida' => $notif->leida,
                        'datos_extra' => $datosExtra,
                        'created_at' => $notif->created_at ? $notif->created_at->format('d/m/Y H:i') : '',
                        'fecha_completa' => $notif->created_at ? $notif->created_at->toDateTimeString() : '',
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'total' => $notificaciones->count(),
                'no_leidas' => Notificacion::contarNoLeidas($user->id_personal_empresa)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en historial notificaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el historial'
            ], 500);
        }
    }
}