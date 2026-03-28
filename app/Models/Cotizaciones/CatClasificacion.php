<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;

class CatClasificacion extends Model
{
    protected $table = 'cat_clasificaciones';
    protected $primaryKey = 'id_clasificacion';
    public $timestamps = true;
    
    protected $fillable = ['clasificacion', 'descripcion', 'activo'];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
}