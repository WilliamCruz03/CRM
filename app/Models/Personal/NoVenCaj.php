<?php

declare(strict_types=1);

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla puente entre historial_ventas_matriz (números de vendedor/cajero)
 * y personal_empresa (nombre del vendedor/cajero).
 *
 * Columnas nv_* = número de vendedor por sucursal.
 * Columnas nc_* = número de cajero por sucursal.
 *
 * Ej: nv_mdo → número de vendedor de la sucursal Mazatlán.
 */
final class NoVenCaj extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'no_ven_caj';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_personal_empresa',
        'nv_mdo', 'nv_jdn', 'nv_zac', 'nv_blv', 'nv_smg', 'nv_sfo', 'nv_hum', 'nv_huc',
        'nc_mdo', 'nc_jdn', 'nc_zac', 'nc_blv', 'nc_smg', 'nc_sfo', 'nc_hum', 'nc_huc',
    ];

    /**
     * Mapeo sufijo de sucursal → columna de número de vendedor.
     */
    public const COLUMNAS_VENDEDOR = [
        'MDO' => 'nv_mdo',
        'JDN' => 'nv_jdn',
        'ZAC' => 'nv_zac',
        'BLV' => 'nv_blv',
        'SMG' => 'nv_smg',
        'SFO' => 'nv_sfo',
        'HUM' => 'nv_hum',
        'HUC' => 'nv_huc',
    ];

    /**
     * Mapeo sufijo de sucursal → columna de número de cajero.
     */
    public const COLUMNAS_CAJERO = [
        'MDO' => 'nc_mdo',
        'JDN' => 'nc_jdn',
        'ZAC' => 'nc_zac',
        'BLV' => 'nc_blv',
        'SMG' => 'nc_smg',
        'SFO' => 'nc_sfo',
        'HUM' => 'nc_hum',
        'HUC' => 'nc_huc',
    ];
}