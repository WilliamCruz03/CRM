<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoMaestro extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'catalogo_maestro';
    protected $primaryKey = 'id_catalogo_maestro';
    public $timestamps = false;

    protected $fillable = [
        'codArt',
        'EAN',
        'descripcion',
        'id_departamento',
        'id_area',
        'id_categoria',
        'id_subcategoria',
        'id_linea',
        'id_grupo',
        'id_subgrupo',
        'presentacion',
        'id_marca',
        'laboratorio',
        'envasado',
        'precio_maximo',
        'id_grupo_convenio_gob_edo',
        'precio_publico_farmapronto_centro',
        'status',
        'fecha_creacion',
        'cod_prod_srv',
        'numDepto',
        'numFam',
        'fecha_actualizacion',
        'sustancia_activa',
        'notas',
        'fabricante',
        'concentracion',
        'tipocompra',
        'sales_presentacion',
        'precio_mejorado',
        'iva',
        'ieps',
        'descpos',
        'piezasxcaja',
        'sec_jdn',
        'sec_mdo',
        'sec_zac',
        'sec_blv',
        'sec_smg',
        'tipo_proveedor',
        'tipo_producto',
        'grupoSSA',
        'ApPuntos',
        'ExclusionDePuntos',
        'sec_sfo',
        'id_tipouso',
        'id_fragancia',
        'id_tamano',
        'id_fabricante'
    ];

    protected $casts = [
        'precio_maximo' => 'decimal:2',
        'precio_publico_farmapronto_centro' => 'decimal:2',
        'iva' => 'decimal:2',
        'ieps' => 'decimal:2',
        'precio_mejorado' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    /**
     * Relación con CatalogoGeneral por EAN
     */
    public function catalogoGeneral(): BelongsTo
    {
        return $this->belongsTo(CatalogoGeneral::class, 'EAN', 'ean');
    }

    /**
     * Relación con cat_sales_presentacion (sustancias activas)
     */
    public function salesPresentacion(): BelongsTo
    {
        return $this->belongsTo(CatSalesPresentacion::class, 'sales_presentacion', 'id');
    }
}