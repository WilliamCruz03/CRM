<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Patologia extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'crm_cat_patologias';
    protected $primaryKey = 'id_patologia';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'fecha_creacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime'
    ];

    // Relación con clientes
    public function clientes()
    {
        return $this->belongsToMany(
            Cliente::class,
            'crm_patologia_asociada',
            'patologia',
            'id_cliente_maestro'
        )->withPivot('fecha_creacion', 'id_operador', 'status');
    }
}