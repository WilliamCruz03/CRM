<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SucursalesPreferidasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $sucursales;
    protected $fechas;

    public function __construct($sucursales, $fechas)
    {
        $this->sucursales = $sucursales;
        $this->fechas = $fechas;
    }

    public function collection()
    {
        return $this->sucursales;
    }

    public function headings(): array
    {
        return [
            'ID Sucursal',
            'Sucursal',
            'Total Ventas',
            'Monto Total',
            'Ticket Promedio',
            'Clientes Atendidos'
        ];
    }

    public function map($sucursal): array
    {
        return [
            $sucursal->id_sucursal,
            $sucursal->nombre,
            $sucursal->total_ventas,
            $sucursal->monto_total,
            $sucursal->ticket_promedio,
            $sucursal->clientes_atendidos
        ];
    }
}