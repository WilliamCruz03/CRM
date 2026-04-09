<?php

namespace App\Models\Cotizaciones;

use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\PersonalEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonInterface;

class Cotizacion extends Model
{
    protected $table = 'crm_cotizaciones';
    protected $primaryKey = 'id_cotizacion';
    public $timestamps = false;

    protected $fillable = [
        'folio', 'id_cliente', 'id_fase', 'id_clasificacion',
        'id_sucursal_asignada', 'importe_total', 'certeza', 'comentarios',
        'fecha_creacion', 'fecha_ultima_modificacion', 'creado_por',
        'modificado_por', 'activo', 'enviado', 'fecha_envio',
        'fecha_entrega_sugerida', 'cotizacion_origen_id', 'version', 'creado_por', 'modificado_por',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_ultima_modificacion' => 'datetime',
        'importe_total' => 'decimal:2',
        'activo' => 'boolean',
        'certeza' => 'integer',
        'enviado' => 'boolean',
        'fecha_envio' => 'datetime',
        'fecha_entrega_sugerida' => 'date',
        'version' => 'integer',
    ];

    /**
     * Generar folio automático
     * Formato: COT-YYYYMMDD-XXXX
     */
    public static function generarFolio(): string
    {
        $fecha = now();
        $fechaFormato = $fecha->format('Ymd');
        $prefijo = "COT-{$fechaFormato}-";

        $ultimoFolio = self::where('folio', 'LIKE', "{$prefijo}%")
            ->orderBy('folio', 'desc')
            ->first();

        if ($ultimoFolio) {
            $ultimoNumero = (int) substr($ultimoFolio->folio, -4);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }

        return $prefijo . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular fecha de entrega sugerida
     * @param CarbonInterface $fechaCreacion
     * @param bool $stockDisponible (todos los productos tienen stock en la sucursal asignada)
     * @return CarbonInterface
     */
    public static function calcularFechaEntregaSugerida(CarbonInterface $fechaCreacion, bool $stockDisponible): CarbonInterface
    {
        $hora = $fechaCreacion->hour;
        $esAntesDe12 = $hora < 12;

        if ($esAntesDe12) {
            if ($stockDisponible) {
                // Mismo día (fecha actual)
                return $fechaCreacion->copy()->startOfDay();
            } else {
                return self::siguienteDiaHabil($fechaCreacion);
            }
        } else {
            if ($stockDisponible) {
                return self::siguienteDiaHabil($fechaCreacion);
            } else {
                return self::siguienteDiaHabil($fechaCreacion, 2);
            }
        }
    }

    /**
     * Obtener siguiente día hábil (lunes a domingo, horario 7-21)
     * @param CarbonInterface $fecha
     * @param int $diasSumar
     * @return CarbonInterface
     */
    protected static function siguienteDiaHabil(CarbonInterface $fecha, int $diasSumar = 1): CarbonInterface
    {
        return $fecha->copy()->addDays($diasSumar)->startOfDay();
    }

    // Relaciones
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_Cliente') 
            ->whereIn('status', ['CLIENTE', 'PROSPECTO']);
    }

    public function fase(): BelongsTo
    {
        return $this->belongsTo(CatFase::class, 'id_fase', 'id_fase');
    }

    public function clasificacion(): BelongsTo
    {
        return $this->belongsTo(CatClasificacion::class, 'id_clasificacion', 'id_clasificacion');
    }

    public function sucursalAsignada(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_asignada', 'id_sucursal');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(PersonalEmpresa::class, 'creado_por', 'id_personal_empresa');
    }

    public function modificador(): BelongsTo
    {
        return $this->belongsTo(PersonalEmpresa::class, 'modificado_por', 'id_personal_empresa');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CotizacionDetalle::class, 'id_cotizacion', 'id_cotizacion');
    }

    public function versionAnterior(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_origen_id', 'id_cotizacion');
    }

    public function versionesPosteriores(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'cotizacion_origen_id', 'id_cotizacion');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', 1);
    }

    public function scopePrioridad($query)
    {
        return $query->orderByRaw("FIELD(certeza, 3,2,1) DESC")
            ->orderBy('fecha_entrega_sugerida', 'asc')
            ->orderBy('fecha_creacion', 'asc');
    }

    // Accessors
    public function getNombreClienteAttribute()
    {
        return $this->cliente ? $this->cliente->nombre_completo : 'N/A';
    }

    public function getFaseNombreAttribute()
    {
        return $this->fase ? $this->fase->fase : 'N/A';
    }

    public function getCertezaNombreAttribute()
    {
        return match($this->certeza) {
            1 => 'Baja',
            2 => 'Media',
            3 => 'Alta',
            default => 'N/A'
        };
    }

    public function getCertezaColorAttribute()
    {
        return match($this->certeza) {
            1 => 'secondary',
            2 => 'warning',
            3 => 'success',
            default => 'secondary'
        };
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cotizacion) {
            if (empty($cotizacion->folio)) {
                $cotizacion->folio = self::generarFolio();
            }
            if (empty($cotizacion->fecha_creacion)) {
                $cotizacion->fecha_creacion = now();
            }
            if (empty($cotizacion->fecha_ultima_modificacion)) {
                $cotizacion->fecha_ultima_modificacion = now();
            }
            if (empty($cotizacion->creado_por)) {
                $cotizacion->creado_por = auth()->id();
            }
        });

        static::updating(function ($cotizacion) {
            $cotizacion->fecha_ultima_modificacion = now();
            $cotizacion->modificado_por = auth()->id();
        });
    }
}