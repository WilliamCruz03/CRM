<?php

namespace App\Models\Clientes;

use Illuminate\Database\Eloquent\Model;

class CatEstado extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_estados';
    public $timestamps = false;

    protected $fillable = ['pais_id', 'clave', 'nombre', 'abrev', 'status'];
}
