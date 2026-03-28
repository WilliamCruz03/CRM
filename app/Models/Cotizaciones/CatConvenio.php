<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;

class CatConvenio extends Model
{
    protected $table = 'cat_convenios';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'convenio', 'nombre', 'tipo', 'num_familia'
    ];
    
    // Scopes
    public function scopeConvenios($query)
    {
        return $query->where('tipo', 'C');
    }
    
    // Relación con detalle para obtener el descuento
    public function detalles()
    {
        return $this->hasMany(CatConvenioDetalle::class, 'id_convenio', 'id');
    }
}