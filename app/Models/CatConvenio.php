<?php
// app/Models/CatConvenio.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatConvenio extends Model
{
    protected $table = 'cat_convenios';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'convenio', 'tipo', 'porcentaje_descuento', 'status'
    ];
    
    protected $casts = [
        'porcentaje_descuento' => 'decimal:2',
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
}