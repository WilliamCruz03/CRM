<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;

class CatConvenio extends Model
{
    protected $table = 'cat_convenios';
    protected $primaryKey = 'id_convenio';
    public $timestamps = true;
    
    protected $fillable = [
        'convenio', 'nombre', 'tipo', 'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
    
    // Relación con familias a través de la tabla pivote
    public function familias()
    {
        return $this->belongsToMany(
            CatFamilia::class,
            'cat_convenios_familias',
            'id_convenio',
            'id_familia'
        )->withPivot('porcentaje_descuento');
    }
}