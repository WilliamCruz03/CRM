<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MontosPromedioExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $clientes;
    protected $fechas;

    public function __construct($clientes, $fechas)
    {
        $this->clientes = $clientes;
        $this->fechas = $fechas;
    }

    public function collection()
    {
        return $this->clientes;
    }

    public function headings(): array
    {
        return [
            'ID Cliente',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Total Compras',
            'Monto Total',
            'Monto Promedio',
            'Fecha Primera Compra',
            'Monto Primera Compra',
            'Fecha Última Compra',
            'Monto Última Compra'
        ];
    }

    public function map($cliente): array
    {
        return [
            $cliente->id_Cliente,
            $cliente->Nombre,
            $cliente->apPaterno,
            $cliente->apMaterno ?? '',
            $cliente->total_compras,
            $cliente->monto_total,
            $cliente->monto_promedio,
            $cliente->fecha_primera_compra,
            $cliente->monto_primera_compra ?? 0,
            $cliente->fecha_ultima_compra,
            $cliente->monto_ultima_compra ?? 0
        ];
    }
}