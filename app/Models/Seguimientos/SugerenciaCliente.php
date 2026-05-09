<?php

namespace App\Models\Seguimientos;

use Illuminate\Database\Eloquent\Model;

class SugerenciaCliente extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'crm_sugerencias_cliente';
    protected $primaryKey = 'id_sugerencia_cliente';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente_maestro',
        'sugerencia',
        'notas',
        'fecha_creacion',
        'id_operador',
        'status'
    ];
}