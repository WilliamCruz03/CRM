<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cotizaciones\CatFamilia;

class CatConvenioDetalle extends Model
{
    protected $table = 'cat_convenios_detalle';
    public $timestamps = true;
    
    protected $fillable = [
        'id_convenio', 'id_familia', 'porcentaje_descuento'
    ];
    
    protected $casts = [
        'porcentaje_descuento' => 'decimal:2'
    ];
    
    public function convenio()
    {
        return $this->belongsTo(CatConvenio::class, 'id_convenio', 'id');
    }
    
    public function familia()
    {
        return $this->belongsTo(CatFamilia::class, 'id_familia', 'id_familia');
    }
}