<?php

use Illuminate\Support\Facades\Route;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Pedidos\OrdenPedido;
use App\Models\AgendaContacto\AgendaContacto;

Route::get('/api/actualizar-tabla', function () {
    $modulo = request('modulo');
    $ultimoId = (int) request('ultimo_id', 0);
    
    $registros = [];
    $ultimoIdActual = $ultimoId;
    
    switch ($modulo) {
        case 'pedidos':
            $pedidos = \App\Models\Pedidos\OrdenPedido::where('id_pedido', '>', $ultimoId)
                ->orderBy('id_pedido', 'desc')
                ->limit(10)
                ->get();
            
            foreach ($pedidos as $pedido) {
                $registros[] = [
                    'id_pedido' => $pedido->id_pedido,
                    'status_color' => $pedido->status == 2 ? 'warning' : ($pedido->status == 3 ? 'success' : 'secondary'),
                    'status_nombre' => $pedido->status == 2 ? 'En proceso' : ($pedido->status == 3 ? 'Completado' : 'Cancelado'),
                    'es_nuevo' => true,
                ];
            }
            
            if ($pedidos->isNotEmpty()) {
                $ultimoIdActual = $pedidos->first()->id_pedido;
            }
            break;
            
        case 'cotizaciones':
            $cotizaciones = \App\Models\Cotizaciones\Cotizacion::where('id_cotizacion', '>', $ultimoId)
                ->with('fase')
                ->orderBy('id_cotizacion', 'desc')
                ->limit(10)
                ->get();
            
            foreach ($cotizaciones as $cotizacion) {
                $faseNombre = $cotizacion->fase->fase ?? 'N/A';
                $faseColor = match($faseNombre) {
                    'En proceso' => 'warning',
                    'Completada' => 'success',
                    'Cancelada' => 'danger',
                    default => 'secondary'
                };
                
                $registros[] = [
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'fase_nombre' => $faseNombre,
                    'fase_color' => $faseColor,
                    'es_nuevo' => true,
                ];
            }
            
            if ($cotizaciones->isNotEmpty()) {
                $ultimoIdActual = $cotizaciones->first()->id_cotizacion;
            }
            break;
            
        case 'agenda':
            $contactos = \App\Models\AgendaContacto\AgendaContacto::where('id_agenda_contacto', '>', $ultimoId)
                ->orderBy('id_agenda_contacto', 'desc')
                ->limit(10)
                ->get();
            
            foreach ($contactos as $contacto) {
                $estadoNombre = $contacto->estado == 1 ? 'Pendiente' : ($contacto->estado == 2 ? 'Realizado' : 'Cancelado');
                $estadoColor = $contacto->estado == 1 ? 'warning' : ($contacto->estado == 2 ? 'success' : 'danger');
                
                $registros[] = [
                    'id_agenda_contacto' => $contacto->id_agenda_contacto,
                    'estado_nombre' => $estadoNombre,
                    'estado_color' => $estadoColor,
                    'es_nuevo' => true,
                ];
            }
            
            if ($contactos->isNotEmpty()) {
                $ultimoIdActual = $contactos->first()->id_agenda_contacto;
            }
            break;
    }
    
    return response()->json([
        'hay_cambios' => !empty($registros),
        'registros' => $registros,
        'ultimo_id' => $ultimoIdActual
    ]);
})->middleware('auth');