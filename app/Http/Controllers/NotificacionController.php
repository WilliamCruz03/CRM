<?php

namespace App\Http\Controllers;

use App\Models\Cotizaciones\Cotizacion;
use App\Models\Configuracion;
use App\Models\Seguimientos\Seguimiento;
use App\Models\Pedidos\OrdenPedido;
use App\Models\AgendaContacto\AgendaContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

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

            // Cada función debe devolver un array, NO JsonResponse
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

            // Ordenar con validación de que cada elemento es un array
            usort($todasLasNotificaciones, function ($a, $b) {
                // Asegurar que ambos son arrays
                if (!is_array($a) || !is_array($b)) {
                    return 0;
                }
                
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
                        ? "Próximo en {$tiempoTexto}"
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
}