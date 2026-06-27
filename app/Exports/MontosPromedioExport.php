<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MontosPromedioExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading
{
    protected $clientes;
    protected $fechas;
    protected $sortBy;

    public function __construct($clientes, $fechas, $sortBy = 'monto_promedio')
    {
        $this->clientes = $clientes;
        $this->fechas = $fechas;
        $this->sortBy = $sortBy;
    }

    public function collection()
    {
        return $this->clientes;
    }

    public function chunkSize(): int
    {
        return 500;
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
        $cliente = (object) $cliente;
        
        return [
            $cliente->id_Cliente ?? '',
            $cliente->Nombre ?? '',
            $cliente->apPaterno ?? '',
            $cliente->apMaterno ?? '',
            $cliente->total_compras ?? 0,
            number_format($cliente->monto_total ?? 0, 2),
            number_format($cliente->monto_promedio ?? 0, 2),
            $cliente->fecha_primera_compra ?? '-',
            number_format($cliente->monto_primera_compra ?? 0, 2),
            $cliente->fecha_ultima_compra ?? '-',
            number_format($cliente->monto_ultima_compra ?? 0, 2)
        ];
    }
}