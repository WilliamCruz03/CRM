<?php

namespace App\Http\Controllers;

use App\Models\Cotizaciones\Cotizacion;
use App\Models\Configuracion;
use App\Models\Seguimientos\Seguimiento;
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
                return $this->getNotificacionesContactos($user);
            default:
                // Dashboard: mostrar un resumen
                return $this->getNotificacionesDashboard($user);
        }
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
                    'url' => route('ventas.cotizaciones.index') . '?buscar=' . $cotizacion->folio,
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
        // Aquí puedes implementar notificaciones para pedidos
        return response()->json([
            'success' => true,
            'data' => [],
            'total' => 0,
            'mensaje_general' => 'No hay pedidos pendientes de seguimiento',
            'tipo' => 'pedidos'
        ]);
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
    
    private function getNotificacionesDashboard($user): JsonResponse
    {
        // Resumen para el dashboard
        $cotizaciones = $this->getNotificacionesCotizaciones($user)->getData();
        
        return response()->json([
            'success' => true,
            'data' => $cotizaciones->data ?? [],
            'total' => $cotizaciones->total ?? 0,
            'mensaje_general' => $cotizaciones->total > 0 ? null : 'No hay notificaciones pendientes',
            'tipo' => 'dashboard'
        ]);
    }
}