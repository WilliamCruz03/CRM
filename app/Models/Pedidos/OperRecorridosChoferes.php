<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperRecorridosChoferes extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'oper_recorridos_choferes';
    
    protected $fillable = [
        'id_personal',
        'fecha',
        'folio_pedido',
        'folio_ticket',
        'importeticket',
        'nombrecliente',
        'Domicilio',
        'kminicial',
        'kmfinal',
        'Solicitadoensucursal',
        'hora_salida',
        'hora_regreso',
        'status'
    ];
    
    protected $casts = [
        'fecha' => 'date',
        'importeticket' => 'decimal:2',
        'kminicial' => 'integer',
        'kmfinal' => 'integer',
        'status' => 'integer',
        'hora_salida' => 'datetime:H:i:s',
        'hora_regreso' => 'datetime:H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relación con el repartidor (personal_empresa)
     */
    public function repartidor()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'id_personal', 'id_personal_empresa');
    }
    
    /**
     * Relación con el pedido
     */
    public function pedido()
    {
        return $this->belongsTo(OrdenPedido::class, 'folio_pedido', 'folio_pedido');
    }
}