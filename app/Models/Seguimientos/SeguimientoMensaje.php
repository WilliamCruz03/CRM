<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoMensaje extends Model
{
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