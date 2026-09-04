<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificacionLeida implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_notificacion;

    public function __construct($id_notificacion)
    {
        $this->id_notificacion = $id_notificacion;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('crm-notifications')
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id_notificacion' => $this->id_notificacion,
            'accion' => 'marcar_leida',
            'timestamp' => now()->toDateTimeString()
        ];
    }

    public function broadcastAs(): string
    {
        return 'notificacion.leida';
    }
}