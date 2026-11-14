<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;

class MensajeTicketSobrePedido extends Model
{
    protected $table = 'dbo.crm_mensajes_ticket_sobre_pedido';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;

    public static function mensajeVigente(string $fecha): ?string
    {
        return self::query()
            ->where('activo', true)
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id_mensaje')
            ->value('mensaje');
    }
}
