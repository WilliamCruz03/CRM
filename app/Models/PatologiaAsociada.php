<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatologiaAsociada extends Model
{
    //
    protected $table = 'crm_patologia_asociada';
    protected $primaryKey = 'id_patologia_asociada';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente_maestro',
        'patologia',
        'fecha_creacion',
        'id_operador',
        'status'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'status' => 'boolean'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente_maestro', 'id_Cliente');
    }

    public function patologiaInfo()
    {
        return $this->belongsTo(Patologia::class, 'patologia', 'descripcion');
    }
}
