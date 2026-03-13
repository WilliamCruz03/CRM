<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patologia extends Model
{
    protected $table = 'crm_cat_patologias';
    protected $primaryKey = 'id_patologia';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'fecha_creacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime'
    ];
}