<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reportes\GrupoFamilia;

class CatConvenio extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_convenios';
    protected $primaryKey = 'id';
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
            GrupoFamilia::class,
            'cat_convenios_familias',
            'id_convenio',
            'numfamilia', // Foreign key en la tabla pivote
            'id', // Local key en cat_convenios
            'numfamilia' // Parent key en grupos_familias (NO la PK)
        )->withPivot('porcentaje_descuento');
    }
}