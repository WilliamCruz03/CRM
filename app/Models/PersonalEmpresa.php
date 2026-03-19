<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class PersonalEmpresa extends Authenticatable
{
    use Notifiable;

    protected $table = 'personal_empresa';
    protected $primaryKey = 'id_personal_empresa';
    public $timestamps = false;

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
        'password',
        'passw'
    ];

    protected $hidden = [
        'password',
        'passw',
        'remember_token',
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

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'id_personal_empresa';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->passw;
    }

    /**
     * Get the remember token for the user.
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the remember token for the user.
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the remember token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->Nombre . ' ' . $this->ApPaterno . ' ' . $this->ApMaterno);
    }

    // Mutator para hashear passw automáticamente
    public function setPasswAttribute($value)
    {
        $this->attributes['passw'] = Hash::make($value);
    }

    // Scope para usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('Activo', 1);
    }

    // Relaciones con permisos
    public function permisos()
    {
        return $this->hasMany(PermisoPersonal::class, 'id_personal_empresa', 'id_personal_empresa');
    }

    // Verificar permiso para clientes
    public function puedeClientes($submodulo, $accion)
    {
        return $this->permisos()
            ->whereHas('moduloClientes', function($q) use ($submodulo) {
                $q->where($submodulo, true);
            })
            ->whereHas('accion', function($q) use ($accion) {
                $q->where('nombre', $accion);
            })
            ->where('permitido', true)
            ->exists();
    }

    // Verificar permiso para ventas
    public function puedeVentas($submodulo, $accion)
    {
        return $this->permisos()
            ->whereHas('moduloVentas', function($q) use ($submodulo) {
                $q->where($submodulo, true);
            })
            ->whereHas('accion', function($q) use ($accion) {
                $q->where('nombre', $accion);
            })
            ->where('permitido', true)
            ->exists();
    }

    // Verificar permiso para seguridad
    public function puedeSeguridad($submodulo, $accion)
    {
        return $this->permisos()
            ->whereHas('moduloSeguridad', function($q) use ($submodulo) {
                $q->where($submodulo, true);
            })
            ->whereHas('accion', function($q) use ($accion) {
                $q->where('nombre', $accion);
            })
            ->where('permitido', true)
            ->exists();
    }

    // Verificar permiso para reportes
    public function puedeReportes($submodulo, $accion)
    {
        return $this->permisos()
            ->whereHas('moduloReportes', function($q) use ($submodulo) {
                $q->where($submodulo, true);
            })
            ->whereHas('accion', function($q) use ($accion) {
                $q->where('nombre', $accion);
            })
            ->where('permitido', true)
            ->exists();
    }

    // Método genérico para verificar cualquier permiso
    public function puede($modulo, $submodulo, $accion)
    {
        switch($modulo) {
            case 'clientes':
                return $this->puedeClientes($submodulo, $accion);
            case 'ventas':
                return $this->puedeVentas($submodulo, $accion);
            case 'seguridad':
                return $this->puedeSeguridad($submodulo, $accion);
            case 'reportes':
                return $this->puedeReportes($submodulo, $accion);
            default:
                return false;
        }
    }

    // Verificar si tiene algún permiso
    public function tieneAlgunPermiso()
    {
        return $this->permisos()->exists();
    }

    // Verificar si tiene acceso a un módulo específico
    public function tieneAccesoAModulo($modulo)
    {
        return $this->permisos()
            ->whereNotNull("id_{$modulo}_modulo")
            ->exists();
    }

    // Obtener módulos a los que tiene acceso
    public function modulosConAcceso()
    {
        $modulos = [];
        
        if ($this->tieneAccesoAModulo('cliente')) {
            $modulos[] = 'clientes';
        }
        if ($this->tieneAccesoAModulo('ventas')) {
            $modulos[] = 'ventas';
        }
        if ($this->tieneAccesoAModulo('seguridad')) {
            $modulos[] = 'seguridad';
        }
        if ($this->tieneAccesoAModulo('reportes')) {
            $modulos[] = 'reportes';
        }
        
        return $modulos;
    }
}