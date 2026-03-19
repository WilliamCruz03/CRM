<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PersonalEmpresa extends Model
{
    protected $table = 'personal_empresa';
    protected $primaryKey = 'id_personal_empresa';
    protected $keyType = 'int';
    public $incrementing = true;
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
        'passw',
        'permisos_modulos'
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
        'permisos_modulos' => 'array',
    ];

    /**
     * Estructura por defecto de permisos
     */
    public static function getPermisosDefault()
    {
        return [
            'clientes' => [
                'mostrar' => true,
                'ver' => true,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false,
            ],
            'enfermedades' => [
                'mostrar' => true,
                'ver' => true,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false,
            ],
            'intereses' => [
                'mostrar' => true,
                'ver' => true,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false,
            ],
            'cotizaciones' => [
                'mostrar' => true,
                'ver' => true,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false,
            ],
            'pedidos_anticipo' => [
                'mostrar' => true,
                'ver' => true,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false,
            ],
            'seguimiento_ventas' => [
                'mostrar' => true,
                'ver' => true,
                'edicion' => false,
            ],
            'seguimiento_cotizaciones' => [
                'mostrar' => true,
                'ver' => true,
                'edicion' => false,
            ],
            'agenda_contactos' => [
                'mostrar' => true,
                'ver' => true,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false,
            ],
            'reportes' => [
                'mostrar' => true,
                'compras_cliente' => false,
                'frecuencia_compra' => false,
                'montos_promedio' => false,
                'sucursales_preferidas' => false,
                'cotizaciones_cliente' => false,
                'cotizaciones_concretadas' => false,
            ],
            'seguridad' => [
                'mostrar' => true,
                'ver' => true,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false,
            ],
        ];
    }

    /**
     * Verificar si tiene permiso en un módulo específico
     */
    public function can($modulo, $accion): bool
    {
        $permisos = $this->permisos_modulos ?? self::getPermisosDefault();
        
        if (!isset($permisos[$modulo])) {
            return false;
        }
        
        // Si el módulo no está marcado como mostrar, no tiene acceso
        if (isset($permisos[$modulo]['mostrar']) && !$permisos[$modulo]['mostrar']) {
            return false;
        }
        
        return $permisos[$modulo][$accion] ?? false;
    }

    /**
     * Verificar si puede ver el módulo en el menú
     */
    public function canViewModule($modulo): bool
    {
        $permisos = $this->permisos_modulos ?? self::getPermisosDefault();
        return $permisos[$modulo]['mostrar'] ?? false;
    }

    /**
     * Verificar si puede realizar cualquier acción de un array
     */
    public function canAny(array $acciones, $modulo): bool
    {
        foreach ($acciones as $accion) {
            if ($this->can($modulo, $accion)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Accessor para nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->Nombre . ' ' . $this->ApPaterno . ' ' . $this->ApMaterno);
    }

    /**
     * Mutator para hashear passw automáticamente
     */
    public function setPasswAttribute($value)
    {
        $this->attributes['passw'] = Hash::make($value);
    }

    /**
     * Scope para usuarios activos
     */
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
}