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

    /**
     * Sincroniza los permisos de un usuario desde el arreglo de permisos
     */
    public function sincronizarPermisos(array $permisosModulos)
    {
        try {
            // Iniciar transacción
            \DB::beginTransaction();
            
            // Obtener IDs de acciones
            $acciones = \App\Models\Catalogo\Accion::whereIn('nombre', ['ver', 'altas', 'edicion', 'eliminar'])
                ->pluck('id_accion', 'nombre');
            
            // Eliminar permisos existentes
            $this->permisos()->delete();
            
            // Para cada módulo, crear o reutilizar registros en las tablas cat_modulo_*
            foreach ($permisosModulos as $moduloNombre => $moduloData) {
                $mostrarModulo = $moduloData['mostrar'] ?? false;
                
                if (!$mostrarModulo) {
                    continue;
                }
                
                if ($moduloNombre === 'clientes') {
                    // Buscar si ya existe un módulo de clientes para este usuario
                    $modulo = \App\Models\Catalogo\ModuloClientes::firstOrCreate(
                        ['id_cliente_modulo' => $moduloData['id_modulo'] ?? null],
                        [
                            'clientes' => $moduloData['ver'] ?? false,
                            'enfermedades' => $moduloData['enfermedades'] ?? false,
                            'intereses' => $moduloData['intereses'] ?? false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    
                    // Si es un módulo existente, actualizar valores
                    if ($modulo->wasRecentlyCreated === false) {
                        $modulo->update([
                            'clientes' => $moduloData['ver'] ?? false,
                            'enfermedades' => $moduloData['enfermedades'] ?? false,
                            'intereses' => $moduloData['intereses'] ?? false,
                            'updated_at' => now()
                        ]);
                    }
                    
                    // Insertar permisos para cada acción
                    $accionesPermitidas = ['ver', 'altas', 'edicion', 'eliminar'];
                    foreach ($accionesPermitidas as $accionNombre) {
                        if (isset($moduloData[$accionNombre]) && $moduloData[$accionNombre]) {
                            $this->permisos()->create([
                                'id_cliente_modulo' => $modulo->id_cliente_modulo,
                                'id_accion' => $acciones[$accionNombre],
                                'permitido' => true
                            ]);
                        }
                    }
                } elseif ($moduloNombre === 'ventas') {
                    // Buscar si ya existe un módulo de ventas para este usuario
                    $modulo = \App\Models\Catalogo\ModuloVentas::firstOrCreate(
                        ['id_ventas_modulo' => $moduloData['id_modulo'] ?? null],
                        [
                            'cotizaciones' => $moduloData['cotizaciones'] ?? false,
                            'pedidos_anticipo' => $moduloData['pedidos_anticipo'] ?? false,
                            'seguimiento_ventas' => $moduloData['seguimiento_ventas'] ?? false,
                            'seguimiento_cotizaciones' => $moduloData['seguimiento_cotizaciones'] ?? false,
                            'agenda_contactos' => $moduloData['agenda_contactos'] ?? false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    
                    // Si es un módulo existente, actualizar valores
                    if ($modulo->wasRecentlyCreated === false) {
                        $modulo->update([
                            'cotizaciones' => $moduloData['cotizaciones'] ?? false,
                            'pedidos_anticipo' => $moduloData['pedidos_anticipo'] ?? false,
                            'seguimiento_ventas' => $moduloData['seguimiento_ventas'] ?? false,
                            'seguimiento_cotizaciones' => $moduloData['seguimiento_cotizaciones'] ?? false,
                            'agenda_contactos' => $moduloData['agenda_contactos'] ?? false,
                            'updated_at' => now()
                        ]);
                    }
                    
                    // Insertar permisos para cada submódulo
                    $submodulos = ['cotizaciones', 'pedidos_anticipo', 'seguimiento_ventas', 'seguimiento_cotizaciones', 'agenda_contactos'];
                    foreach ($submodulos as $submodulo) {
                        if ($moduloData[$submodulo] ?? false) {
                            $this->permisos()->create([
                                'id_ventas_modulo' => $modulo->id_ventas_modulo,
                                'id_accion' => $acciones['ver'],
                                'permitido' => true
                            ]);
                        }
                    }
                } elseif ($moduloNombre === 'seguridad') {
                    $modulo = \App\Models\Catalogo\ModuloSeguridad::firstOrCreate(
                        ['id_seguridad_modulo' => $moduloData['id_modulo'] ?? null],
                        [
                            'usuarios' => $moduloData['usuarios'] ?? false,
                            'permisos' => $moduloData['permisos'] ?? false,
                            'respaldos' => $moduloData['respaldos'] ?? false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    
                    if ($modulo->wasRecentlyCreated === false) {
                        $modulo->update([
                            'usuarios' => $moduloData['usuarios'] ?? false,
                            'permisos' => $moduloData['permisos'] ?? false,
                            'respaldos' => $moduloData['respaldos'] ?? false,
                            'updated_at' => now()
                        ]);
                    }
                    
                    if ($moduloData['usuarios'] ?? false) {
                        $this->permisos()->create([
                            'id_seguridad_modulo' => $modulo->id_seguridad_modulo,
                            'id_accion' => $acciones['ver'],
                            'permitido' => true
                        ]);
                    }
                    if ($moduloData['permisos'] ?? false) {
                        $this->permisos()->create([
                            'id_seguridad_modulo' => $modulo->id_seguridad_modulo,
                            'id_accion' => $acciones['ver'],
                            'permitido' => true
                        ]);
                    }
                    if ($moduloData['respaldos'] ?? false) {
                        $this->permisos()->create([
                            'id_seguridad_modulo' => $modulo->id_seguridad_modulo,
                            'id_accion' => $acciones['ver'],
                            'permitido' => true
                        ]);
                    }
                } elseif ($moduloNombre === 'reportes') {
                    $modulo = \App\Models\Catalogo\ModuloReportes::firstOrCreate(
                        ['id_reportes_modulo' => $moduloData['id_modulo'] ?? null],
                        [
                            'compras_cliente' => $moduloData['compras_cliente'] ?? false,
                            'frecuencia_compra' => $moduloData['frecuencia_compra'] ?? false,
                            'montos_promedio' => $moduloData['montos_promedio'] ?? false,
                            'sucursales_preferidas' => $moduloData['sucursales_preferidas'] ?? false,
                            'cotizaciones_cliente' => $moduloData['cotizaciones_cliente'] ?? false,
                            'cotizaciones_concretadas' => $moduloData['cotizaciones_concretadas'] ?? false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    
                    if ($modulo->wasRecentlyCreated === false) {
                        $modulo->update([
                            'compras_cliente' => $moduloData['compras_cliente'] ?? false,
                            'frecuencia_compra' => $moduloData['frecuencia_compra'] ?? false,
                            'montos_promedio' => $moduloData['montos_promedio'] ?? false,
                            'sucursales_preferidas' => $moduloData['sucursales_preferidas'] ?? false,
                            'cotizaciones_cliente' => $moduloData['cotizaciones_cliente'] ?? false,
                            'cotizaciones_concretadas' => $moduloData['cotizaciones_concretadas'] ?? false,
                            'updated_at' => now()
                        ]);
                    }
                    
                    $reportes = ['compras_cliente', 'frecuencia_compra', 'montos_promedio', 'sucursales_preferidas', 'cotizaciones_cliente', 'cotizaciones_concretadas'];
                    foreach ($reportes as $reporte) {
                        if ($moduloData[$reporte] ?? false) {
                            $this->permisos()->create([
                                'id_reportes_modulo' => $modulo->id_reportes_modulo,
                                'id_accion' => $acciones['ver'],
                                'permitido' => true
                            ]);
                        }
                    }
                }
            }
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al sincronizar permisos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los permisos del usuario formateados para los checkboxes
     */
    public function getPermisosFormateadosAttribute()
    {
        $permisos = [
            'clientes' => [
                'mostrar' => false,
                'ver' => false,
                'enfermedades' => false,
                'intereses' => false,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false
            ],
            'ventas' => [
                'mostrar' => false,
                'cotizaciones' => false,
                'cotizaciones_altas' => false,
                'cotizaciones_edicion' => false,
                'cotizaciones_eliminar' => false,
                'pedidos_anticipo' => false,
                'pedidos_anticipo_altas' => false,
                'pedidos_anticipo_edicion' => false,
                'pedidos_anticipo_eliminar' => false,
                'seguimiento_ventas' => false,
                'seguimiento_ventas_altas' => false,
                'seguimiento_ventas_edicion' => false,
                'seguimiento_ventas_eliminar' => false,
                'seguimiento_cotizaciones' => false,
                'seguimiento_cotizaciones_altas' => false,
                'seguimiento_cotizaciones_edicion' => false,
                'seguimiento_cotizaciones_eliminar' => false,
                'agenda_contactos' => false,
                'agenda_contactos_altas' => false,
                'agenda_contactos_edicion' => false,
                'agenda_contactos_eliminar' => false
            ],
            'seguridad' => [
                'mostrar' => false,
                'usuarios' => false,
                'permisos' => false,
                'respaldos' => false,
                'altas' => false,
                'edicion' => false,
                'eliminar' => false
            ],
            'reportes' => [
                'mostrar' => false,
                'compras_cliente' => false,
                'frecuencia_compra' => false,
                'montos_promedio' => false,
                'sucursales_preferidas' => false,
                'cotizaciones_cliente' => false,
                'cotizaciones_concretadas' => false
            ],
        ];
        
        foreach ($this->permisos as $permiso) {
            if ($permiso->id_cliente_modulo) {
                $modulo = $permiso->moduloClientes;
                if ($modulo) {
                    $permisos['clientes']['mostrar'] = true;
                    $accion = $permiso->accion->nombre;
                    
                    // Mapear acciones
                    if ($accion === 'ver') $permisos['clientes']['ver'] = true;
                    if ($accion === 'altas') $permisos['clientes']['altas'] = true;
                    if ($accion === 'edicion') $permisos['clientes']['edicion'] = true;
                    if ($accion === 'eliminar') $permisos['clientes']['eliminar'] = true;
                    
                    // Submódulos específicos
                    $permisos['clientes']['enfermedades'] = $permisos['clientes']['enfermedades'] || $modulo->enfermedades;
                    $permisos['clientes']['intereses'] = $permisos['clientes']['intereses'] || $modulo->intereses;
                }
            } elseif ($permiso->id_ventas_modulo) {
                $modulo = $permiso->moduloVentas;
                if ($modulo) {
                    $permisos['ventas']['mostrar'] = true;
                    $accion = $permiso->accion->nombre;
                    
                    // Mapear permisos por submódulo con sus acciones
                    if ($modulo->cotizaciones) {
                        $permisos['ventas']['cotizaciones'] = true;
                        if ($accion === 'altas') $permisos['ventas']['cotizaciones_altas'] = true;
                        if ($accion === 'edicion') $permisos['ventas']['cotizaciones_edicion'] = true;
                        if ($accion === 'eliminar') $permisos['ventas']['cotizaciones_eliminar'] = true;
                    }
                    if ($modulo->pedidos_anticipo) {
                        $permisos['ventas']['pedidos_anticipo'] = true;
                        if ($accion === 'altas') $permisos['ventas']['pedidos_anticipo_altas'] = true;
                        if ($accion === 'edicion') $permisos['ventas']['pedidos_anticipo_edicion'] = true;
                        if ($accion === 'eliminar') $permisos['ventas']['pedidos_anticipo_eliminar'] = true;
                    }
                    if ($modulo->seguimiento_ventas) {
                        $permisos['ventas']['seguimiento_ventas'] = true;
                        if ($accion === 'altas') $permisos['ventas']['seguimiento_ventas_altas'] = true;
                        if ($accion === 'edicion') $permisos['ventas']['seguimiento_ventas_edicion'] = true;
                        if ($accion === 'eliminar') $permisos['ventas']['seguimiento_ventas_eliminar'] = true;
                    }
                    if ($modulo->seguimiento_cotizaciones) {
                        $permisos['ventas']['seguimiento_cotizaciones'] = true;
                        if ($accion === 'altas') $permisos['ventas']['seguimiento_cotizaciones_altas'] = true;
                        if ($accion === 'edicion') $permisos['ventas']['seguimiento_cotizaciones_edicion'] = true;
                        if ($accion === 'eliminar') $permisos['ventas']['seguimiento_cotizaciones_eliminar'] = true;
                    }
                    if ($modulo->agenda_contactos) {
                        $permisos['ventas']['agenda_contactos'] = true;
                        if ($accion === 'altas') $permisos['ventas']['agenda_contactos_altas'] = true;
                        if ($accion === 'edicion') $permisos['ventas']['agenda_contactos_edicion'] = true;
                        if ($accion === 'eliminar') $permisos['ventas']['agenda_contactos_eliminar'] = true;
                    }
                }
            } elseif ($permiso->id_seguridad_modulo) {
                $modulo = $permiso->moduloSeguridad;
                if ($modulo) {
                    $permisos['seguridad']['mostrar'] = true;
                    $accion = $permiso->accion->nombre;
                    
                    $permisos['seguridad']['usuarios'] = $permisos['seguridad']['usuarios'] || $modulo->usuarios;
                    $permisos['seguridad']['permisos'] = $permisos['seguridad']['permisos'] || $modulo->permisos;
                    $permisos['seguridad']['respaldos'] = $permisos['seguridad']['respaldos'] || $modulo->respaldos;
                    
                    if ($accion === 'altas') $permisos['seguridad']['altas'] = true;
                    if ($accion === 'edicion') $permisos['seguridad']['edicion'] = true;
                    if ($accion === 'eliminar') $permisos['seguridad']['eliminar'] = true;
                }
            } elseif ($permiso->id_reportes_modulo) {
                $modulo = $permiso->moduloReportes;
                if ($modulo) {
                    $permisos['reportes']['mostrar'] = true;
                    $permisos['reportes']['compras_cliente'] = $permisos['reportes']['compras_cliente'] || $modulo->compras_cliente;
                    $permisos['reportes']['frecuencia_compra'] = $permisos['reportes']['frecuencia_compra'] || $modulo->frecuencia_compra;
                    $permisos['reportes']['montos_promedio'] = $permisos['reportes']['montos_promedio'] || $modulo->montos_promedio;
                    $permisos['reportes']['sucursales_preferidas'] = $permisos['reportes']['sucursales_preferidas'] || $modulo->sucursales_preferidas;
                    $permisos['reportes']['cotizaciones_cliente'] = $permisos['reportes']['cotizaciones_cliente'] || $modulo->cotizaciones_cliente;
                    $permisos['reportes']['cotizaciones_concretadas'] = $permisos['reportes']['cotizaciones_concretadas'] || $modulo->cotizaciones_concretadas;
                }
            }
        }
        
        return $permisos;
    }
}