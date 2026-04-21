<?php

namespace App\Models\Pedidos;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Model;

class OrdenPedidoSucursal extends Model
{
    protected $table = 'orden_pedido_sucursal';
    protected $primaryKey = 'id_pedido_sucursal';
    public $timestamps = true;

    protected $fillable = [
        'id_pedido',
        'id_sucursal',
        'status',
        'fecha_asignacion',
        'fecha_completado'
    ];

    protected $casts = [
        'status' => 'boolean',
        'fecha_asignacion' => 'datetime',
        'fecha_completado' => 'datetime'
    ];

    public function pedido()
    {
        return $this->belongsTo(OrdenPedido::class, 'id_pedido', 'id_pedido');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    public function getStatusNombreAttribute()
    {
        return $this->status ? 'Listo' : 'Pendiente';
    }

    public function getStatusColorAttribute()
    {
        return $this->status ? 'success' : 'warning';
    }
}