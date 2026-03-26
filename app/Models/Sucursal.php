<?php
// app/Models/Sucursal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';
    protected $primaryKey = 'id_sucursal';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre', 'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
    
    // Relaciones
    public function catalogoGeneral()
    {
        return $this->hasMany(CatalogoGeneral::class, 'id_sucursal', 'id_sucursal');
    }
    
    // Scope
    public function scopeActivas($query)
    {
        return $query->where('activo', 1);
    }
}