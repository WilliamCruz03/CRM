<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CatConvenioDetalle;

class CatConvenio extends Model
{
    protected $table = 'cat_convenios';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'convenio', 'nombre', 'tipo', 'num_familia', 'status'
    ];
    
    protected $casts = [
        'status' => 'boolean'
    ];
    
    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('status', 1);
    }
    
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