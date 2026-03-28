<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;

class CatFamilia extends Model
{
    protected $table = 'cat_familias';
    protected $primaryKey = 'id_familia';
    public $timestamps = true;
    
    protected $fillable = [
        'num_familia', 'nombre', 'descripcion', 'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
    
    // Relación con convenios a través de la tabla pivote
    public function convenios()
    {
        return $this->belongsToMany(
            CatConvenio::class,
            'cat_convenios_familias',
            'id_familia',
            'id_convenio'
        )->withPivot('porcentaje_descuento');
    }
}