<?php

namespace App\Models;

use App;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\DashboardPreferencia;
use App\Models\PermisoGranular;
use App\Models\Pedidos\OrdenPedidoSucursal;

class PersonalEmpresa extends Authenticatable
{
    use Notifiable;

    protected $connection = 'sqlsrvM';
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

    /**
     * Los accessors que se incluirán automáticamente en las respuestas JSON
     */
    protected $appends = [
        'nombre_completo', 
        'es_repartidor', 
        'es_crm', 
        'es_sucursal', 
        'perfil', 
        'sucursal_asignada_efectiva'
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

    /**
     * Relación con preferencias del dashboard
     */
    public function dashboardPreferencias()
    {
        return $this->hasMany(DashboardPreferencia::class, 'id_personal_empresa', 'id_personal_empresa');
    }

    /**
     * Accesor para obtener cards activos del dashboard
     */
    public function getDashboardCardsActivosAttribute()
    {
        return $this->dashboardPreferencias()
            ->where('mostrar', true)
            ->orderBy('orden')
            ->pluck('card_key')
            ->toArray();
    }

    /**
     * Relación con permisos granulares
     */
    public function permisosGranulares()
    {
        return $this->hasMany(PermisoGranular::class, 'id_personal_empresa', 'id_personal_empresa');
    }

    /**
     * Obtener permisos granulares del usuario (consulta directa a la BD CRM)
     */
    public function obtenerPermisosGranulares()
    {
        return PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)->get();
    }

    /**
     * Obtiene el permiso granular del usuario (si existe)
     */
    public function getPermisoGranularAttribute()
    {
        return PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)
            ->where('modulo', 'ventas')
            ->where('submodulo', 'pedidos_anticipo')
            ->first();
    }

    /**
     * Obtiene los permisos formateados para el modal de edición
     */
    public function getPermisosFormateadosAttribute()
    {
        $permisosUsuario = PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)->get();
        
        $permisos = [
            'clientes' => [
                'directorio' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'enfermedades' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'intereses' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false]
            ],
            'ventas' => [
                'cotizaciones' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'pedidos_anticipo' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'agenda_contactos' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false]
            ],
            'seguridad' => [
                'usuarios' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false],
                'permisos' => ['mostrar' => false, 'ver' => false, 'editar' => false, 'eliminar' => false],
                'respaldos' => ['mostrar' => false, 'ver' => false, 'crear' => false, 'editar' => false, 'eliminar' => false]
            ],
            'reportes' => [
                'compras_cliente' => ['mostrar' => false, 'ver' => false],
                'montos_promedio' => ['mostrar' => false, 'ver' => false],
                'sucursales_preferidas' => ['mostrar' => false, 'ver' => false],
                'cotizaciones_cliente' => ['mostrar' => false, 'ver' => false],
                'pedidos_cliente' => ['mostrar' => false, 'ver' => false]
            ]
        ];

        foreach ($permisosUsuario as $permiso) {
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

        // AGREGAR PERFILES AL ARRAY DE PERMISOS
        $permisoPrincipal = PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)
            ->where('modulo', 'ventas')
            ->where('submodulo', 'pedidos_anticipo')
            ->first();

        $permisos['perfiles'] = [
            'es_crm' => $permisoPrincipal->es_crm ?? false,
            'es_sucursal' => $permisoPrincipal->es_sucursal ?? false,
            'es_repartidor' => $permisoPrincipal->es_repartidor ?? false,
        ];
        
        return $permisos;
    }

    // ATRIBUTOS PARA PERFILES

    /**
     * Indica si el usuario tiene perfil CRM
     */
    public function getEsCrmAttribute(): bool
    {
        $permiso = $this->permiso_granular;
        return $permiso ? (bool) $permiso->es_crm : false;
    }

    /**
     * Indica si el usuario tiene perfil Sucursal
     */
    public function getEsSucursalAttribute(): bool
    {
        $permiso = $this->permiso_granular;
        return $permiso ? (bool) $permiso->es_sucursal : false;
    }

    /**
     * Indica si el usuario es repartidor (permiso activo)
     * El perfil Repartidor es independiente del horario
     */
    public function getEsRepartidorAttribute(): bool
    {
        $permiso = $this->permiso_granular;
        return $permiso ? (bool) $permiso->es_repartidor : false;
    }

    /**
     * Obtiene el perfil principal del usuario (para UI)
     */
    public function getPerfilAttribute(): string
    {
        if ($this->es_crm) {
            return 'CRM';
        }
        if ($this->es_sucursal) {
            return 'Sucursal';
        }
        if ($this->es_repartidor) {
            return 'Repartidor';
        }
        return 'Sin perfil';
    }

    /**
     * Obtiene la sucursal asignada efectiva (según perfil)
     */
    public function getSucursalAsignadaEfectivaAttribute(): int
    {
        // Si es sucursal, usar su sucursal_asignada
        if ($this->es_sucursal && $this->sucursal_asignada) {
            return $this->sucursal_asignada;
        }
        
        // Si es repartidor, obtener su sucursal desde rh
        if ($this->es_repartidor) {
            $horario = DB::connection('sqlsrvM')
                ->table('rh_personal_servicios_domicilio')
                ->where('id_personal', $this->id_personal_empresa)
                ->where('fecha', now()->format('Y-m-d'))
                ->first();
            return $horario->id_sucursal ?? 0;
        }
        
        // Si no tiene perfil Sucursal ni Repartidor, devolver 0
        return 0;
    }

    /**
     * Indica si el usuario tiene horario activo en RH para hoy
     */
    public function tieneHorarioRepartidor(): bool
    {
        $hoy = now()->format('Y-m-d');
        return DB::connection('sqlsrvM')
            ->table('rh_personal_servicios_domicilio')
            ->where('id_personal', $this->id_personal_empresa)
            ->whereRaw('CAST(fecha AS DATE) = ?', [$hoy])
            ->exists();
    }

    // MÉTODOS DE PERMISOS PARA PEDIDOS

    /**
     * Verifica si el usuario puede marcar un pedido como listo
     */
    public function puedeMarcarListoPedido($pedido): bool
    {
        // Sucursal: verificar si tiene un registro en orden_pedido_sucursal
        if ($this->es_sucursal) {
            $sucursalId = $this->sucursal_asignada_efectiva;
            
            // Verificar si existe un registro en orden_pedido_sucursal para esta sucursal y pedido
            $existeRegistro = OrdenPedidoSucursal::where('id_pedido', $pedido->id_pedido)
                ->where('id_sucursal', $sucursalId)
                ->exists();
            
            return $existeRegistro;
        }
        
        // Repartidor: solo pedidos asignados a él
        if ($this->es_repartidor && $pedido->id_repartidor == $this->id_personal_empresa) {
            return true;
        }
        
        return false;
    }

    /**
     * Indica si el usuario puede iniciar recorrido (requiere perfil + horario)
     */
    public function puedeIniciarRecorrido($pedido): bool
    {
        // Primero verificar que tenga el perfil Repartidor
        if (!$this->es_repartidor) {
            return false;
        }
        
        // Luego verificar que tenga horario
        if (!$this->tieneHorarioRepartidor()) {
            return false;
        }
        
        // Finalmente, verificar que el pedido esté asignado a él (o a su sucursal)
        if ($this->es_sucursal && $pedido->id_sucursal_asignada == $this->sucursal_asignada_efectiva) {
            return true;
        }
        
        if ($pedido->id_repartidor == $this->id_personal_empresa) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica si el usuario puede asignar sucursal a un pedido
     */
    public function puedeAsignarSucursal(): bool
    {
        return $this->es_crm || $this->es_sucursal || $this->es_repartidor;
    }

    /**
     * Verifica si el usuario puede asignar repartidor a un pedido
     */
    public function puedeAsignarRepartidor(): bool
    {
        return $this->es_crm;
    }

    /**
     * Verifica si el usuario puede convertir cotización a pedido
     */
    public function puedeConvertirPedido(): bool
    {
        return $this->es_crm;
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public function puede($modulo, $submodulo, $accion)
    {
        $permiso = PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)
            ->where('modulo', $modulo)
            ->where('submodulo', $submodulo)
            ->first();
            
        if (!$permiso) {
            return false;
        }
        
        // Para cualquier acción que no sea "mostrar", primero verificar que mostrar esté activado
        if ($accion !== 'mostrar' && !$permiso->mostrar) {
            return false;
        }
        
        if ($accion === 'mostrar') {
            return $permiso->mostrar;
        }
        
        if ($accion === 'ver') {
            return $permiso->ver;
        }
        
        // Acciones
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
     * Verifica si el usuario puede ver el módulo en el menú
     */
    public function puedeVerModulo($modulo)
    {
        // Verificar si existe al menos un submódulo con algún permiso activo
        return $this->permisosGranulares()
            ->where('modulo', $modulo)
            ->where(function($query) {
                $query->where('mostrar', true)
                    ->orWhere('ver', true)
                    ->orWhere('crear', true)
                    ->orWhere('editar', true)
                    ->orWhere('eliminar', true);
            })
            ->exists();
    }

    /**
     * Obtiene los submódulos que el usuario puede ver para un módulo
     */
    public function submodulosVisibles($modulo)
    {
        return $this->permisosGranulares()
            ->where('modulo', $modulo)
            ->where(function($query) {
                $query->where('mostrar', true)
                    ->orWhere('ver', true)
                    ->orWhere('crear', true)
                    ->orWhere('editar', true)
                    ->orWhere('eliminar', true);
            })
            ->get()
            ->pluck('submodulo')
            ->toArray();
    }

    /**
     * Verifica si tiene algún permiso en general
     */
    public function tieneAlgunPermiso()
    {
        return PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)
            ->where(function($query) {
                $query->where('mostrar', true)
                    ->orWhere('ver', true)
                    ->orWhere('crear', true)
                    ->orWhere('editar', true)
                    ->orWhere('eliminar', true);
            })
            ->exists();
    }

    /**
     * Obtiene los módulos a los que el usuario tiene acceso
     */
    public function modulosConAcceso()
    {
        return PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)
            ->where(function($query) {
                $query->where('mostrar', true)
                    ->orWhere('ver', true)
                    ->orWhere('crear', true)
                    ->orWhere('editar', true)
                    ->orWhere('eliminar', true);
            })
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
                    'agenda_contactos' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar']
                ],
                'seguridad' => [
                    'usuarios' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar'],
                    'permisos' => ['mostrar', 'ver', 'editar', 'eliminar'],
                    'respaldos' => ['mostrar', 'ver', 'crear', 'editar', 'eliminar']
                ],
                'reportes' => [
                    'compras_cliente' => ['mostrar', 'ver'],
                    'montos_promedio' => ['mostrar', 'ver'],
                    'sucursales_preferidas' => ['mostrar', 'ver'],
                    'cotizaciones_cliente' => ['mostrar', 'ver'],
                    'pedidos_cliente' => ['mostrar', 'ver']
                ]
            ];
            
            foreach ($estructuraPermisos as $modulo => $submodulos) {
                $moduloData = $permisosModulos[$modulo] ?? [];
                
                foreach ($submodulos as $submodulo => $acciones) {
                    $submoduloData = $moduloData[$submodulo] ?? [];
                    
                    // Verificar si tiene alguna acción activa
                    $tieneVer = $submoduloData['ver'] ?? false;
                    $tieneCrear = $submoduloData['crear'] ?? false;
                    $tieneEditar = $submoduloData['editar'] ?? false;
                    $tieneEliminar = $submoduloData['eliminar'] ?? false;
                    $tieneAlgunaAccion = $tieneVer || $tieneCrear || $tieneEditar || $tieneEliminar;
                    
                    // Si no tiene ninguna acción activa, mostrar debe ser false
                    $mostrar = ($submoduloData['mostrar'] ?? false) && $tieneAlgunaAccion;
                    
                    $data = [
                        'mostrar' => $mostrar,
                        'ver' => $tieneVer,
                        'crear' => $tieneCrear,
                        'editar' => $tieneEditar,
                        'eliminar' => $tieneEliminar,
                        'updated_at' => now()
                    ];
                    
                    // Buscar si ya existe el registro
                    $permisoExistente = PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)
                        ->where('modulo', $modulo)
                        ->where('submodulo', $submodulo)
                        ->first();
                    
                    if ($permisoExistente) {
                        // Actualizar registro existente
                        $permisoExistente->update($data);
                    } else {
                        // Crear nuevo registro (solo si no existe)
                        $data['id_personal_empresa'] = $this->id_personal_empresa;
                        $data['modulo'] = $modulo;
                        $data['submodulo'] = $submodulo;
                        $data['created_at'] = now();
                        PermisoGranular::create($data);
                    }
                }
            }

            // SINCRONIZAR PERFILES (es_crm, es_sucursal, es_repartidor)
            $perfiles = $permisosModulos['perfiles'] ?? [];
            
            $dataPerfiles = [
                'es_crm' => $perfiles['es_crm'] ?? false,
                'es_sucursal' => $perfiles['es_sucursal'] ?? false,
                'es_repartidor' => $perfiles['es_repartidor'] ?? false,
                'updated_at' => now()
            ];
            
            $permisoPrincipal = PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)
                ->where('modulo', 'ventas')
                ->where('submodulo', 'pedidos_anticipo')
                ->first();
            
            if ($permisoPrincipal) {
                $permisoPrincipal->update($dataPerfiles);
            } else {
                $dataPerfiles['id_personal_empresa'] = $this->id_personal_empresa;
                $dataPerfiles['modulo'] = 'ventas';
                $dataPerfiles['submodulo'] = 'pedidos_anticipo';
                $dataPerfiles['mostrar'] = false;
                $dataPerfiles['ver'] = false;
                $dataPerfiles['crear'] = false;
                $dataPerfiles['editar'] = false;
                $dataPerfiles['eliminar'] = false;
                $dataPerfiles['created_at'] = now();
                PermisoGranular::create($dataPerfiles);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al sincronizar permisos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Valida y corrige permisos: si no hay ningún permiso activo, desactiva mostrar
     */
    public function validarYCorregirPermisos()
    {
        $permisos = PermisoGranular::where('id_personal_empresa', $this->id_personal_empresa)->get();
        
        foreach ($permisos as $permiso) {
            $tieneAlgunPermisoActivo = $permiso->ver || $permiso->crear || $permiso->editar || $permiso->eliminar;
            $mostrarCorrecto = $tieneAlgunPermisoActivo;
            
            if ($permiso->mostrar != $mostrarCorrecto) {
                PermisoGranular::where('id_permiso_granular', $permiso->id_permiso_granular)
                    ->update(['mostrar' => $mostrarCorrecto]);
            }
        }
    }

    /**
     * Filtro de visibilidad de pedidos (para queries)
     */
    public function scopePedidosVisibles($query, $user)
    {
        if ($user->es_crm) {
            return $query;
        }
        
        if ($user->es_sucursal) {
            $sucursalId = $user->sucursal_asignada_efectiva;
            return $query->where('id_sucursal_asignada', $sucursalId);
        }
        
        if ($user->es_repartidor) {
            return $query->where('id_repartidor_asignado', $user->id_personal_empresa);
        }
        
        return $query->whereRaw('1 = 0');
    }
}