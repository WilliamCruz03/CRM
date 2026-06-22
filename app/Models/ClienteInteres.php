<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteInteres extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'crm_cliente_intereses';
    protected $primaryKey = 'id_cliente_int';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_interes',
        'fecha_asignacion',
        'activo'
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'activo' => 'boolean'
    ];

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_Cliente');
    }

    // Relación con Interes
    public function interes()
    {
        return $this->belongsTo(Interes::class, 'id_interes', 'id_interes');
    }
}