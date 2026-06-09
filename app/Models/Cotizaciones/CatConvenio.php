<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;

class CatConvenio extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_convenios';
    protected $primaryKey = 'id';  // ← Cambiar: la PK se llama 'id', no 'id_convenio'
    public $timestamps = true;
    
    protected $fillable = [
        'convenio',
        'tipo',
        'status',
        'fecha_inicial',
        'fecha_final',
        'notas'
    ];
    
    protected $casts = [
        'status' => 'boolean'
    ];
    
    // Relación con familias a través de la tabla pivote
    public function familias()
    {
        return $this->belongsToMany(
            CatFamilia::class,
            'cat_convenios_familias',
            'id_convenio',  // ← Ajustar según el nombre real en la tabla pivote
            'id_familia'
        )->withPivot('porcentaje_descuento');
    }
}