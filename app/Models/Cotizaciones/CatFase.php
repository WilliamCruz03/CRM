<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;

class CatFase extends Model
{
    protected $table = 'cat_fases';
    protected $primaryKey = 'id_fase';
    public $timestamps = true;
    
    protected $fillable = ['fase', 'descripcion', 'orden', 'activo'];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
}