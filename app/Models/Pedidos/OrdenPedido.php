<?php

namespace App\Models\Pedidos;

use App\Models\Cotizaciones\Cotizacion;
use App\Models\PersonalEmpresa;
use Illuminate\Database\Eloquent\Model;

class OrdenPedido extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'orden_pedido';
    protected $primaryKey = 'id_pedido';
    public $timestamps = true;

    protected $fillable = [
        'id_cotizacion',
        'folio_pedido',
        'status',
        'id_repartidor',
        'fecha_pedido',
        'fecha_entrega_sugerida',
        'hora_entrega_sugerida',
        'fecha_entrega_real',
        'comentarios',
        'creado_por',
        'activo',
    ];

    protected $casts = [
        'status' => 'integer',
        'activo' => 'boolean',
        'fecha_pedido' => 'datetime',
        'fecha_entrega_sugerida' => 'date',
        'hora_entrega_sugerida' => 'datetime',
        'fecha_entrega_real' => 'datetime',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion', 'id_cotizacion');
    }

    public function creador()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'creado_por', 'id_personal_empresa');
    }

    public function repartidor()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'id_repartidor', 'id_personal_empresa');
    }

    public function sucursales()
    {
        return $this->hasMany(OrdenPedidoSucursal::class, 'id_pedido', 'id_pedido');
    }

    public function getStatusNombreAttribute()
    {
        return match($this->status) {
            1 => 'No realizada',
            2 => 'En proceso',
            3 => 'Finalizada',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            1 => 'secondary',
            2 => 'warning',
            3 => 'success',
            default => 'secondary'
        };
    }
}