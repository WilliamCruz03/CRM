<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatAgendaTipo extends Model
{
    protected $table = 'cat_agenda_tipos';
    protected $primaryKey = 'id_tipo';
    public $timestamps = true;
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
}