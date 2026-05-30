<?php

namespace App\Models\Clientes;

use Illuminate\Database\Eloquent\Model;

class CatMunicipio extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_municipios';
    public $timestamps = false;
    
    protected $fillable = ['estado_id', 'clave', 'nombre', 'status'];
}
