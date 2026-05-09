<?php

namespace App\Models\Seguimientos;

use Illuminate\Database\Eloquent\Model;

class SeguimientoMensaje extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'crm_seguimiento_mensajes';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;

    protected $fillable = [
        'id_seguimiento',
        'mensaje'
    ];

    public function seguimiento()
    {
        return $this->belongsTo(Seguimiento::class, 'id_seguimiento', 'id_seguimiento');
    }
}