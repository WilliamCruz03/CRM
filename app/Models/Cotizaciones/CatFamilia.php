<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;
use App\Models\CatConvenioDetalle;

class CatFamilia extends Model
{
    protected $table = 'cat_familias';
    protected $primaryKey = 'id_familia';
    public $timestamps = true;
    
    protected $fillable = [
        'num_familia',
        'nombre',
        'descripcion',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
    
    // Relación con convenios detalle
    public function conveniosDetalle()
    {
        return $this->hasMany(CatConvenioDetalle::class, 'id_familia', 'id_familia');
    }
}