<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PersonalEmpresa extends Authenticatable
{
    use Notifiable;

    protected $table = 'personal_empresa';
    protected $primaryKey = 'id_personal_empresa';
    public $timestamps = false;

    protected $fillable = [
        'Nombre', 'ApPaterno', 'ApMaterno', 'Direccion', 'Localidad', 'Municipio',
        'TelefonoFijo', 'TelefonoMovil', 'contacto', 'parentescoDeContacto', 'TelefonoContacto',
        'fecha_ingreso', 'fecha_alta_sistema', 'fecha_alta_seguro', 'Activo', 'fecha_baja',
        'motivo_baja', 'sucursal_origen', 'sucursal_asignada', 'curp', 'fecha_nacimiento',
        'usuario', 'password', 'passw'
    ];

    protected $hidden = ['password', 'passw', 'remember_token'];

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

    public function getAuthIdentifierName()
    {
        return 'id_personal_empresa';
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword()
    {
        return $this->passw;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->Nombre . ' ' . $this->ApPaterno . ' ' . $this->ApMaterno);
    }

    public function setPasswAttribute($value)
    {
        $this->attributes['passw'] = Hash::make($value);
    }

    public function scopeActivos($query)
    {
        return $query->where('Activo', 1);
    }

    // Relación con permisos granulares (NUEVA)
    public function permisosGranulares()
    {
        return $this->hasMany(PermisoGranular::class, 'id_personal_empresa', 'id_personal_empresa');
    }

    /**
     * Obtiene los permisos formateados para el modal de edición
     */
    public function getPermisosFormateadosAttribute()
    {
        // Depuración temporal - eliminar después
        \Log::info('Permisos granulares para usuario ' . $this->id_personal_empresa, [
            'count' => $this->permisosGranulares()->count(),
            'data' => $this->permisosGranulares()->get()->toArray()
        ]);
        
        $permisos = [
            'clientes' => [
                'directorio' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'enfermedades' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'intereses' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false]
            ],
            'ventas' => [
                'cotizaciones' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'pedidos_anticipo' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'seguimiento_ventas' => ['mostrar' => false, 'ver' => false, 'editar' => false],
                'seguimiento_cotizaciones' => ['mostrar' => false, 'ver' => false, 'editar' => false],
                'agenda_contactos' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false]
            ],
            'seguridad' => [
                'usuarios' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'permisos' => ['mostrar' => false, 'ver' => false],
                'respaldos' => ['mostrar' => false, 'ver' => false]
            ],
            'reportes' => [
                'compras_cliente' => ['mostrar' => false, 'ver' => false],
                'frecuencia_compra' => ['mostrar' => false, 'ver' => false],
                'montos_promedio' => ['mostrar' => false, 'ver' => false],
                'sucursales_preferidas' => ['mostrar' => false, 'ver' => false],
                'cotizaciones_cliente' => ['mostrar' => false, 'ver' => false],
                'cotizaciones_concretadas' => ['mostrar' => false, 'ver' => false]
            ]
        ];

        foreach ($this->permisosGranulares as $permiso) {
            $modulo = $permiso->modulo;
            $submodulo = $permiso->submodulo;
            
            if (isset($permisos[$modulo][$submodulo])) {
                $permisos[$modulo][$submodulo]['mostrar'] = $permiso->mostrar;
                $permisos[$modulo][$submodulo]['ver'] = $permiso->ver;
                
                if (isset($permisos[$modulo][$submodulo]['crear'])) {
                    $permisos[$modulo][$submodulo]['crear'] = $permiso->crear;
                }
                if (isset($permisos[$modulo][$submodulo]['editar'])) {
                    $permisos[$modulo][$submodulo]['editar'] = $permiso->editar;
                }
                if (isset($permisos[$modulo][$submodulo]['eliminar'])) {
                    $permisos[$modulo][$submodulo]['eliminar'] = $permiso->eliminar;
                }
            }
        }
        
        // Depuración temporal
        \Log::info('Permisos formateados', ['permisos' => $permisos]);
        
        return $permisos;
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public function puede($modulo, $submodulo, $accion)
    {
        $permiso = $this->permisosGranulares()
            ->where('modulo', $modulo)
            ->where('submodulo', $submodulo)
            ->first();
            
        if (!$permiso) {
            return false;
        }
        
        // Verificar mostrar primero - si no está activado, no puede ver nada
        if (!$permiso->mostrar) {
            return false;
        }
        
        // Si es "ver" y el permiso de ver está activado
        if ($accion === 'ver') {
            return $permiso->ver;
        }
        
        // Para otras acciones
        if ($accion === 'crear' && isset($permiso->crear)) {
            return $permiso->crear;
        }
        if ($accion === 'editar' && isset($permiso->editar)) {
            return $permiso->editar;
        }
        if ($accion === 'eliminar' && isset($permiso->eliminar)) {
            return $permiso->eliminar;
        }
        
        return false;
    }

    /**
     * Verifica si el usuario puede ver el módulo en el menú (al menos un submódulo con mostrar=true)
     */
    public function puedeVerModulo($modulo)
    {
        // Verificar si existe al menos un submódulo con mostrar = true para este módulo
        return $this->permisosGranulares()
            ->where('modulo', $modulo)
            ->where('mostrar', true)
            ->exists();
    }

    /**
     * Obtiene los submódulos que el usuario puede ver para un módulo
     */
    public function submodulosVisibles($modulo)
    {
        return $this->permisosGranulares()
            ->where('modulo', $modulo)
            ->where('mostrar', true)
            ->get()
            ->pluck('submodulo')
            ->toArray();
    }

    /**
     * Verifica si tiene algún permiso en general
     */
    public function tieneAlgunPermiso()
    {
        return $this->permisosGranulares()->exists();
    }

    /**
     * Obtiene los módulos a los que el usuario tiene acceso
     */
    public function modulosConAcceso()
    {
        return $this->permisosGranulares()
            ->where('mostrar', true)
            ->distinct()
            ->pluck('modulo')
            ->toArray();
    }

    /**
     * Sincroniza permisos desde el arreglo del modal
     */
    public function sincronizarPermisos(array $permisosModulos)
    {
        try {
            DB::beginTransaction();
            
            // Eliminar permisos existentes
            $this->permisosGranulares()->delete();
            
            // Definir estructura de permisos por submódulo
            $estructuraPermisos = [
                'clientes' => [
                    'directorio' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar'],
                    'enfermedades' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar'],
                    'intereses' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar']
                ],
                'ventas' => [
                    'cotizaciones' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar'],
                    'pedidos_anticipo' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar'],
                    'seguimiento_ventas' => ['mostrar', 'ver', 'editar'],
                    'seguimiento_cotizaciones' => ['mostrar', 'ver', 'editar'],
                    'agenda_contactos' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar']
                ],
                'seguridad' => [
                    'usuarios' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar'],
                    'permisos' => ['mostrar', 'ver'],
                    'respaldos' => ['mostrar', 'ver']
                ],
                'reportes' => [
                    'compras_cliente' => ['mostrar', 'ver'],
                    'frecuencia_compra' => ['mostrar', 'ver'],
                    'montos_promedio' => ['mostrar', 'ver'],
                    'sucursales_preferidas' => ['mostrar', 'ver'],
                    'cotizaciones_cliente' => ['mostrar', 'ver'],
                    'cotizaciones_concretadas' => ['mostrar', 'ver']
                ]
            ];
            
            foreach ($estructuraPermisos as $modulo => $submodulos) {
                $moduloData = $permisosModulos[$modulo] ?? [];
                
                foreach ($submodulos as $submodulo => $acciones) {
                    $submoduloData = $moduloData[$submodulo] ?? [];
                    
                    $data = [
                        'id_personal_empresa' => $this->id_personal_empresa,
                        'modulo' => $modulo,
                        'submodulo' => $submodulo,
                        'mostrar' => $submoduloData['mostrar'] ?? false,
                        'ver' => $submoduloData['ver'] ?? false,
                        'crear' => $submoduloData['crear'] ?? false,
                        'editar' => $submoduloData['editar'] ?? false,
                        'eliminar' => $submoduloData['eliminar'] ?? false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    DB::table('permisos_granulares')->insert($data);
                }
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al sincronizar permisos: ' . $e->getMessage());
            throw $e;
        }
    }
}