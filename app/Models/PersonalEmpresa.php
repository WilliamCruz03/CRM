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

    // ============================================
    // MÉTODOS DE PERMISOS
    // ============================================

    /**
     * Método principal que usan las Gates.
     * Verifica si el usuario tiene permiso para una acción en un módulo.
     */
    public function puede($modulo, $accion)
    {
        // Mapear el módulo a la tabla correcta en permisos_personal
        $mapaModulos = [
            'clientes' => 'id_cliente_modulo',
            'enfermedades' => 'id_cliente_modulo',
            'intereses' => 'id_cliente_modulo',
            'cotizaciones' => 'id_ventas_modulo',
            'pedidos_anticipo' => 'id_ventas_modulo',
            'seguimiento_ventas' => 'id_ventas_modulo',
            'seguimiento_cotizaciones' => 'id_ventas_modulo',
            'agenda_contactos' => 'id_ventas_modulo',
            'seguridad' => 'id_seguridad_modulo',
            'usuarios' => 'id_seguridad_modulo',
            'permisos' => 'id_seguridad_modulo',
            'respaldos' => 'id_seguridad_modulo',
            'reportes' => 'id_reportes_modulo',
        ];
        
        $columna = $mapaModulos[$modulo] ?? null;
        if (!$columna) return false;
        
        // Verificar si tiene el permiso
        return $this->permisos()
            ->whereNotNull($columna)
            ->whereHas('accion', function($q) use ($accion) {
                $q->where('nombre', $accion);
            })
            ->where('permitido', true)
            ->exists();
    }

    /**
     * Verifica si puede ver el módulo en el menú lateral.
     * Usado por Gates como {$modulo}.mostrar
     */
    public function puedeVerModulo($modulo)
    {
        $mapa = [
            'clientes' => 'cliente',
            'ventas' => 'ventas',
            'seguridad' => 'seguridad',
            'reportes' => 'reportes',
        ];
        
        $moduloBD = $mapa[$modulo] ?? $modulo;
        
        return $this->permisos()
            ->whereNotNull("id_{$moduloBD}_modulo")
            ->exists();
    }

    /**
     * Verifica si tiene algún permiso en general
     */
    public function tieneAlgunPermiso()
    {
        return $this->permisos()->exists();
    }

    /**
     * Verifica si tiene acceso a un módulo específico (ej: cliente, ventas, etc.)
     */
    public function tieneAccesoAModulo($modulo)
    {
        return $this->permisos()
            ->whereNotNull("id_{$modulo}_modulo")
            ->exists();
    }

    /**
     * Obtiene los módulos a los que el usuario tiene acceso
     */
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