<?php

namespace App\Models\Ventas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Models\CatalogoGeneral;
use App\Models\Sucursal;

class HistorialVenta extends Model
{
    protected $connection = 'sqlsrvV';
    protected $table = 'historial_ventas_matriz';
    protected $primaryKey = 'id_historial_ventas';
    public $timestamps = false;

    protected $fillable = [
        'F_FECHA', 'F_HORA', 'F_NUMTICKE', 'F_CODBAR', 
        'F_MONTO', 'IDCLIENTE', 'id_sucursal', 'F_BASCULA', 'PAGADO'
    ];

    protected $casts = [
        'F_FECHA' => 'date',
        'F_MONTO' => 'decimal:2',
        'F_BASCULA' => 'boolean',
        'PAGADO' => 'boolean'
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'IDCLIENTE', 'idtarjetaclientefrecuente');
    }

    public function producto()
    {
        return $this->belongsTo(CatalogoGeneral::class, 'F_CODBAR', 'ean');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    // Scopes para filtros comunes
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('F_FECHA', [$fechaInicio, $fechaFin]);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('IDCLIENTE', $clienteId);
    }

    public function scopePorProducto($query, $ean)
    {
        return $query->where('F_CODBAR', $ean);
    }

    // Accessor para monto como número
    public function getMontoNumericoAttribute()
    {
        return floatval($this->F_MONTO);
    }

    // Query para obtener resumen por cliente
    public static function getResumenClientes($fechaInicio, $fechaFin, $limit = null)
    {
        $query = self::entreFechas($fechaInicio, $fechaFin)
            ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'historial_ventas_matriz.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
            ->select(
                'c.id_Cliente',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                \DB::raw('COUNT(DISTINCT F_NUMTICKE) as total_transacciones'),
                \DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as monto_total'),
                \DB::raw('AVG(CAST(F_MONTO AS DECIMAL(18,2))) as ticket_promedio'),
                \DB::raw('MIN(F_FECHA) as primera_compra'),
                \DB::raw('MAX(F_FECHA) as ultima_compra')
            )
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno')
            ->orderBy('monto_total', 'DESC');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    // Query para obtener KPIs
    public static function getKPIs($fechaInicio, $fechaFin)
    {
        $result = self::entreFechas($fechaInicio, $fechaFin)
            ->select(
                \DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as total_ventas'),
                \DB::raw('COUNT(DISTINCT F_NUMTICKE) as total_transacciones'),
                \DB::raw('COUNT(DISTINCT IDCLIENTE) as clientes_activos')
            )
            ->first();

        if ($result && $result->total_transacciones > 0) {
            $result->ticket_promedio = $result->total_ventas / $result->total_transacciones;
        } else {
            $result->ticket_promedio = 0;
        }

        return $result;
    }
}