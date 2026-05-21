<?php
// app/Exports/TopProductosExport.php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TopProductosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $top;

    public function __construct($fechaInicio, $fechaFin, $top = 10)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->top = $top;
    }

    public function collection()
    {
        return DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_general as cg', 'cg.ean', '=', 'h.F_CODBAR')
            ->select(
                'cg.ean',
                'cg.descripcion',
                DB::raw('COUNT(*) as cantidad_vendida'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('COUNT(DISTINCT h.IDCLIENTE) as clientes_distintos')
            )
            ->whereBetween('h.F_FECHA', [$this->fechaInicio, $this->fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('cg.ean', 'cg.descripcion')
            ->orderBy('monto_total', 'DESC')
            ->limit($this->top)
            ->get();
    }

    public function headings(): array
    {
        return [
            'EAN',
            'Descripción',
            'Cantidad Vendida',
            'Monto Total',
            'Clientes Distintos',
            'Ticket Promedio'
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->ean,
            $producto->descripcion,
            $producto->cantidad_vendida,
            $producto->monto_total,
            $producto->clientes_distintos,
            $producto->cantidad_vendida > 0 ? $producto->monto_total / $producto->cantidad_vendida : 0
        ];
    }
}