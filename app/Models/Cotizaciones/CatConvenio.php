<?php

namespace App\Models\Cotizaciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Reportes\GrupoFamilias;

class CatConvenio extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_convenios';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'convenio',
        'tipo',
        'status',
        'fecha_inicial',
        'fecha_final',
        'notas'
    ];
    
    protected $casts = [
        'status' => 'boolean'
    ];
    
    /**
     * Obtener las familias asociadas al convenio (desde otra base de datos)
     */
    public function getFamiliasConDescuento()
    {
        // Obtener los registros de la tabla pivote (en fp_central_crm)
        $pivote = DB::connection('sqlsrv')
            ->table('cat_convenios_familias')
            ->where('id_convenio', $this->id)
            ->get();
        
        if ($pivote->isEmpty()) {
            return collect();
        }
        
        // Obtener los numfamilia (como strings)
        $numfamilias = $pivote->pluck('numfamilia')->map(function($item) {
            return (string) $item;
        })->toArray();
        
        // Buscar las familias en fp_central_matriz
        $familias = GrupoFamilias::whereIn('numfamilia', $numfamilias)->get();
        
        // Agregar el porcentaje de descuento a cada familia
        foreach ($familias as $familia) {
            $pivotItem = $pivote->firstWhere('numfamilia', (string) $familia->numfamilia);
            $familia->descuento = $pivotItem->porcentaje_descuento ?? 0;
        }
        
        return $familias;
    }
}