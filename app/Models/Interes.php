<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interes extends Model
{
    protected $table = 'crm_cat_intereses';
    protected $primaryKey = 'id_interes';
    public $timestamps = false;

    protected $fillable = [
        'Descripcion',
        'fecha_creacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime'
    ];

    // Accessor para obtener la descripción
    public function getDescripcionAttribute($value)
    {
        return trim($value);
    }

    // Mutator para guardar en mayúsculas
    public function setDescripcionAttribute($value)
    {
        $this->attributes['Descripcion'] = strtoupper(trim($value));
    }
}