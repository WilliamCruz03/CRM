<?php

namespace App\Models\Clientes;

use Illuminate\Database\Eloquent\Model;

class CatPais extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_paises';
    public $timestamps = false;
    
    protected $fillable = ['clave', 'pais', 'abrev', 'status'];
}
