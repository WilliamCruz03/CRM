<?php

namespace App\Models\Clientes;

use Illuminate\Database\Eloquent\Model;

class CatLocalidad extends Model
{

    protected $connection = 'sqlsrvM';
    protected $table = 'cat_localidades';
    public $timestamps = false;

    protected $fillable = ['municipio_id', 'clave', 'nombre', 'latitud', 'longitud', 'altitud', 'ambito', 'poblacion', 'viviendas', 'lat', 'lng', 'status'];
}
