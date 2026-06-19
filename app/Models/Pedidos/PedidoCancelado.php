<?php

namespace App\Models\Pedidos;

use Illuminate\Database\Eloquent\Model;
use App\Models\PersonalEmpresa;

class PedidoCancelado extends Model
{
    protected $table = 'pedido_cancelado';
    protected $primaryKey = 'id_cancelacion';
    public $timestamps = true;

    protected $fillable = [
        'id_pedido',
        'motivo',
        'cancelado_por',
        'fecha_cancelacion'
    ];

    protected $casts = [
        'fecha_cancelacion' => 'datetime'
    ];

    // Relación con el pedido
    public function pedido()
    {
        return $this->belongsTo(OrdenPedido::class, 'id_pedido', 'id_pedido');
    }

    // Relación con el usuario que canceló
    public function usuario()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'cancelado_por', 'id_personal_empresa');
    }
}