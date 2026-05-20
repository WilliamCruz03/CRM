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
        \Log::info('>>> getNotificaciones llamado - módulo: ' . Request::input('modulo', 'dashboard'));
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
            $contactosNotif = $this->getNotificacionesAgendaContactos($user);
            if (!empty($contactosNotif)) {
                $todasLasNotificaciones = array_merge($todasLasNotificaciones, $contactosNotif);
            }
        }
        
        // Ordenar por prioridad (contactos próximos primero, luego por días)
        usort($todasLasNotificaciones, function($a, $b) {
            // Contactos próximos tienen prioridad
            $tipoA = is_array($a) ? ($a['tipo'] ?? '') : ($a->tipo ?? '');
            $tipoB = is_array($b) ? ($b['tipo'] ?? '') : ($b->tipo ?? '');
            
            if ($tipoA === 'contacto' && $tipoB !== 'contacto') {
                return -1;
            }
            if ($tipoA !== 'contacto' && $tipoB === 'contacto') {
                return 1;
            }
            // Luego ordenar por días (mayor primero para cotizaciones/pedidos)
            $diasA = is_array($a) ? ($a['dias'] ?? 0) : ($a->dias ?? 0);
            $diasB = is_array($b) ? ($b['dias'] ?? 0) : ($b->dias ?? 0);
            return $diasB - $diasA;
        });
        
        $total = count($todasLasNotificaciones);
        
        // Determinar el tipo de header según el módulo actual (solo para el título)
        $tipoHeader = $modulo;
        
        return response()->json([
            'success' => true,
            'data' => array_values($todasLasNotificaciones), // Asegurar índice numérico
            'total' => $total,
            'mensaje_general' => $total === 0 ? 'No hay notificaciones pendientes' : null,
            'tipo' => $tipoHeader
        ]);
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
            
            // Usar configuración específica para pedidos
            $diasAlerta = Configuracion::getValor('dias_alerta_pedidos', 7);
            
            $pedidos = OrdenPedido::with(['cotizacion.cliente', 'seguimientos'])
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