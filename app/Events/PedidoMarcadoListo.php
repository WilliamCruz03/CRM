<?php

namespace App\Events;

use App\Models\Pedidos\OrdenPedido;
use App\Models\Notificacion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PedidoMarcadoListo implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pedido;
    public $sucursalNombre;
    public $sucursalesListas;
    public $titulo;
    public $mensaje;

    public function __construct(OrdenPedido $pedido, $sucursalNombre, $sucursalesListas)
    {
        $this->pedido = $pedido;
        $this->sucursalNombre = $sucursalNombre;
        $this->sucursalesListas = $sucursalesListas;
        
        if ($pedido->id_repartidor) {
            $this->mensaje = "Pedido listo para entrega";
            $this->titulo = "{$pedido->folio_pedido} Listo";
        } else {
            $this->mensaje = "Puede asignar repartidor";
            $this->titulo = "{$pedido->folio_pedido} Listo";
        }
        
        // GUARDAR NOTIFICACIÓN COMPARTIDA (SOLO SI NO ES EL MISMO USUARIO QUE MARCA LISTO)
        try {
            // Solo guardar notificación compartida (id_usuario = 0) para todos los CRM
            Notificacion::create([
                'id_usuario' => 0,  // 0 = Notificación para todos los CRM
                'tipo' => 'pedido_listo',
                'titulo' => $this->titulo,
                'mensaje' => $this->mensaje,
                'datos_extra' => json_encode([
                    'pedido_id' => $pedido->id_pedido,
                    'folio_pedido' => $pedido->folio_pedido,
                    'sucursales' => $sucursalesListas,
                    'tiene_repartidor' => $pedido->id_repartidor ? true : false,
                    'creado_por' => auth()->id() ?? 0,  // Guardar quién la creó
                ]),
                'leida' => 0,
                'creado_por' => auth()->id() ?? 0,
                'created_at' => now()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al guardar notificación compartida: ' . $e->getMessage());
        }
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('crm-notifications')
        ];
    }

    public function broadcastWith(): array
    {
        $data = [
            'pedido_id' => $this->pedido->id_pedido,
            'folio_pedido' => $this->pedido->folio_pedido,
            'sucursal' => $this->sucursalNombre,
            'sucursales_listas' => $this->sucursalesListas,
            'mensaje' => $this->mensaje,
            'titulo' => $this->titulo,
            'tiene_repartidor' => $this->pedido->id_repartidor ? true : false,
            'timestamp' => now()->toDateTimeString()
        ];
        
        return $data;
    }

    public function broadcastAs(): string
    {
        return 'pedido.marcado.listo';
    }
}