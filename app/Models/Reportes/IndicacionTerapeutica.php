<?php

namespace App\Models\Reportes;

use Illuminate\Database\Eloquent\Model;
use App\Models\CatalogoMaestro;

class IndicacionTerapeutica extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_Indicaciones_Terapeuticas';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = ['IndicacionTerapeutica'];
    
    public function productos()
    {
        return $this->hasMany(CatalogoMaestro::class, 'id_ITerapeutica', 'id');
    }
}