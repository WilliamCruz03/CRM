<?php

namespace App\Models\Reportes;

use Illuminate\Database\Eloquent\Model;
use App\Models\CatalogoMaestro;

class GrupoFamilia extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'grupos_familias';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'id_grupo',
        'numfamilia',
        'descripciongrupo',
        'descripcionfamilia',
        'id_grupo_madre',
        'descripciongrupomadre'
    ];
    
    // Relación con catalogo_maestro
    public function productos()
    {
        return $this->hasMany(CatalogoMaestro::class, 'numFam', 'numfamilia');
    }
}