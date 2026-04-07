<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SustanciaActiva extends Model
{
    //
    protected $table = 'cat_sales_presentacion';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'sustancia', 'fecha_mod', 'concentracion', 'envasado', 'presentacion', 'unidad', 'cantidadPresentacion'
    ];

        protected $casts = [
        'fecha_mod' => 'datetime',
        'cantidadPresentacion' => 'decimal'
    ];
}
