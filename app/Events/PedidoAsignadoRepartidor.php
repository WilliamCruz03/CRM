<?php

namespace App\Events;

use App\Models\Pedidos\OrdenPedido;
use App\Models\PersonalEmpresa;
use App\Models\Notificacion;
use App\Models\Pedidos\OrdenPedidoSucursal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidoAsignadoRepartidor implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pedido;
    public $repartidorNombre;
    public $sucursalesListas;
    public $titulo;
    public $mensaje;

    public function __construct(OrdenPedido $pedido, PersonalEmpresa $repartidor)
    {
        $this->pedido = $pedido;
        $this->repartidorNombre = $repartidor->nombre_completo;
        
        // Obtener sucursales
        $sucursales = OrdenPedidoSucursal::where('id_pedido', $pedido->id_pedido)
            ->where('status', 1)
            ->with('sucursal')
            ->get()
            ->pluck('sucursal.nombre')
            ->implode(', ');
        
        $this->sucursalesListas = $sucursales ?: 'Sin sucursales asignadas';
        $this->titulo = "{$pedido->folio_pedido} Asignado";
        $this->mensaje = "Se te ha asignado el Pedido {$pedido->folio_pedido}";
        
        // GUARDAR NOTIFICACIÓN PARA EL REPARTIDOR
        try {
            Notificacion::create([
                'id_usuario' => $repartidor->id_personal_empresa, // Solo ese repartidor
                'tipo' => 'pedido_asignado',
                'titulo' => $this->titulo,
                'mensaje' => $this->mensaje,
                'datos_extra' => json_encode([
                    'pedido_id' => $pedido->id_pedido,
                    'folio_pedido' => $pedido->folio_pedido,
                    'sucursales' => $this->sucursalesListas,
                    'repartidor_nombre' => $repartidor->nombre_completo,
                ]),
                'leida' => 0,
                'creado_por' => auth()->id() ?? 0,
                'created_at' => now()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al guardar notificación de asignación: ' . $e->getMessage());
        }
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user-notifications.' . $this->pedido->id_repartidor)
        ];
    }

    public function broadcastWith(): array
    {
        $data = [
            'pedido_id' => $this->pedido->id_pedido,
            'folio_pedido' => $this->pedido->folio_pedido,
            'repartidor' => $this->repartidorNombre,
            'sucursales_listas' => $this->sucursalesListas,
            'mensaje' => $this->mensaje,
            'titulo' => $this->titulo,
            'timestamp' => now()->toDateTimeString()
        ];
        
        return $data;
    }

    public function broadcastAs(): string
    {
        return 'pedido.asignado.repartidor';
    }
}