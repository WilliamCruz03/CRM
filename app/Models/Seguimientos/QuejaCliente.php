<?php

namespace App\Models\Seguimientos;

use Illuminate\Database\Eloquent\Model;

class QuejaCliente extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'crm_quejas_cliente';
    protected $primaryKey = 'id_queja_cliente';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente_maestro',
        'queja',
        'notas',
        'fecha_creacion',
        'id_operador'
    ];
}