<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PersonalEmpresa extends Model
{
    //
    protected $table = "personal_empresa";
    protected $primaryKey = "id_personal_empresa";
    public $timestamps = false; //Si no tenemos created_at/updated_at
    protected $fillable = [
        'Nombre',
        'ApPaterno',
        'ApMaterno',
        'Direccion',
        'Localidad',
        'Municipio',
        'TelefonoFijo',
        'TelefonoMovil',
        'contacto',
        'parentescoDeContacto',
        'TelefonoContacto',
        'fecha_ingreso',
        'fecha_alta_sistema',
        'fecha_alta_seguro',
        'Activo',
        'fecha_baja',
        'motivo_baja',
        'sucursal_origen',
        'sucursal_asignada',
        'curp',
        'fecha_nacimiento',
        'usuario',
        'password', //Texto plano
        'passw' //Hash
    ];

    protected $casts = [
        'Activo' => 'boolean',
        'fecha_ingreso' => 'date',
        'fecha_alta_sistema' => 'date',
        'fecha_alta_seguro' => 'date',
        'fecha_baja' => 'date',
        'fecha_nacimiento' => 'date',
        'sucursal_origen' => 'integer',
        'sucursal_asignada' => 'integer',
    ];

    //Accesor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->Nombre . ' ' . $this->ApPaterno . ' ' . $this->ApMaterno);
    }

    //Mutator para hashear passw automáticamente
    public function setPasswAttribute($value)
    {
        $this->attributes['passw'] = Hash::make($value);
    }

    // Scope para usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('Activo', 1);
    }
}
